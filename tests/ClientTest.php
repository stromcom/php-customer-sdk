<?php
declare(strict_types=1);

namespace Stromcom\Sdk\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Stromcom\Sdk\Client;
use Stromcom\Sdk\Configuration;
use Stromcom\Sdk\Exception\ApiException;
use Stromcom\Sdk\Exception\AuthenticationException;
use Stromcom\Sdk\Exception\ConflictException;
use Stromcom\Sdk\Exception\NotFoundException;
use Stromcom\Sdk\Exception\RateLimitException;
use Stromcom\Sdk\Exception\ServerException;
use Stromcom\Sdk\Exception\TransportException;
use Stromcom\Sdk\Exception\ValidationException;
use Stromcom\Sdk\Http\RawResponse;
use Stromcom\Sdk\Tests\Http\MockHttpClient;

final class ClientTest extends TestCase {

  private const string TOKEN = 'test-token';
  private const string BASE_URL = 'https://api.example.test/customer/v1/';

  private MockHttpClient $http;
  private Client $client;

  protected function setUp(): void {
    $this->http = new MockHttpClient();
    $this->client = new Client(
      new Configuration(token: self::TOKEN, baseUrl: self::BASE_URL),
      $this->http,
    );
  }

  #[Test]
  public function constructor_accepts_token_string(): void {
    $client = new Client('abc');

    $this->assertSame('abc', $client->configuration->token);
    $this->assertSame(Configuration::DEFAULT_BASE_URL, $client->configuration->baseUrl);
  }

  #[Test]
  public function get_request_sends_authorization_header_and_returns_data(): void {
    $this->http->enqueueJson(200, [
      'status' => 'success',
      'data' => ['id' => 42, 'name' => 'Project'],
      'meta' => [],
    ]);

    $data = $this->client->request('GET', 'project/');

    $this->assertSame(['id' => 42, 'name' => 'Project'], $data);
    $call = $this->http->lastCall();
    $this->assertSame('GET', $call['method']);
    $this->assertSame(self::BASE_URL . 'project/', $call['url']);
    $this->assertSame('Bearer ' . self::TOKEN, $call['headers']['Authorization']);
    $this->assertSame('application/json', $call['headers']['Accept']);
    $this->assertNull($call['body']);
  }

  #[Test]
  public function post_request_sends_json_body(): void {
    $this->http->enqueueJson(201, [
      'status' => 'success',
      'data' => ['user' => ['hash' => 'h'], 'account' => ['hash' => 'a']],
    ]);

    $this->client->request('POST', 'users/', body: ['code' => 'u1', 'name' => 'User']);

    $call = $this->http->lastCall();
    $this->assertSame('application/json', $call['headers']['Content-Type']);
    $this->assertSame('{"code":"u1","name":"User"}', $call['body']);
  }

  #[Test]
  public function query_parameters_are_appended_and_nulls_dropped(): void {
    $this->http->enqueueJson(200, ['status' => 'success', 'data' => []]);

    $this->client->request('GET', 'users/', query: ['state' => 'active', 'code' => null]);

    $this->assertSame(self::BASE_URL . 'users/?state=active', $this->http->lastCall()['url']);
  }

  #[Test]
  public function status_401_throws_authentication_exception(): void {
    $this->http->enqueueJson(401, [
      'status' => 'error',
      'error' => ['code' => 'UNAUTHORIZED', 'message' => 'Bad token', 'details' => []],
    ]);

    $this->expectException(AuthenticationException::class);
    $this->expectExceptionMessage('Bad token');

    $this->client->request('GET', 'project/');
  }

  #[Test]
  public function status_404_throws_not_found_exception(): void {
    $this->http->enqueueJson(404, [
      'status' => 'error',
      'error' => ['code' => 'NOT_FOUND', 'message' => 'Missing'],
    ]);

    $this->expectException(NotFoundException::class);

    $this->client->request('GET', 'users/abc/');
  }

  #[Test]
  public function status_409_throws_conflict_exception_with_details(): void {
    $this->http->enqueueJson(409, [
      'status' => 'error',
      'error' => [
        'code' => 'USER_ALREADY_EXISTS',
        'message' => "User with code 'foo' already exists",
        'details' => ['userCode' => 'foo'],
      ],
    ]);

    try {
      $this->client->request('POST', 'users/', body: ['code' => 'foo', 'name' => 'X']);
      $this->fail('Expected ConflictException.');
    } catch (ConflictException $e) {
      $this->assertSame(409, $e->getStatusCode());
      $this->assertSame('USER_ALREADY_EXISTS', $e->getApiCode());
      $this->assertSame(['userCode' => 'foo'], $e->getDetails());
    }
  }

  #[Test]
  public function status_400_throws_validation_exception(): void {
    $this->http->enqueueJson(400, [
      'status' => 'error',
      'error' => ['code' => 'INVALID_INPUT', 'message' => 'Bad'],
    ]);

    $this->expectException(ValidationException::class);

    $this->client->request('POST', 'users/');
  }

  #[Test]
  public function status_429_throws_rate_limit_exception(): void {
    $this->http->enqueueJson(429, [
      'status' => 'error',
      'error' => ['code' => 'RATE_LIMITED', 'message' => 'slow down'],
    ]);

    $this->expectException(RateLimitException::class);

    $this->client->request('GET', 'project/');
  }

  #[Test]
  public function status_500_throws_server_exception(): void {
    $this->http->enqueueJson(500, [
      'status' => 'error',
      'error' => ['code' => 'INTERNAL', 'message' => 'boom'],
    ]);

    $this->expectException(ServerException::class);

    $this->client->request('GET', 'project/');
  }

  #[Test]
  public function unknown_status_throws_generic_api_exception(): void {
    $this->http->enqueueJson(418, [
      'status' => 'error',
      'error' => ['code' => 'TEAPOT', 'message' => 'short and stout'],
    ]);

    try {
      $this->client->request('GET', 'project/');
      $this->fail('Expected ApiException.');
    } catch (ApiException $e) {
      $this->assertSame(418, $e->getStatusCode());
      $this->assertSame('TEAPOT', $e->getApiCode());
      $this->assertNotInstanceOf(NotFoundException::class, $e);
      $this->assertNotInstanceOf(ServerException::class, $e);
    }
  }

  #[Test]
  public function non_json_response_body_throws_transport_exception(): void {
    $this->http->enqueueRaw(new RawResponse(200, '<html>oops</html>'));

    $this->expectException(TransportException::class);

    $this->client->request('GET', 'project/');
  }

  #[Test]
  public function empty_body_with_2xx_returns_null(): void {
    $this->http->enqueueRaw(new RawResponse(204, ''));

    $this->assertNull($this->client->request('DELETE', 'users/abc/'));
  }

}
