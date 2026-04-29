<?php
declare(strict_types=1);

namespace Stromcom\Sdk\Tests\Http;

use Stromcom\Sdk\Http\HttpClientInterface;
use Stromcom\Sdk\Http\RawResponse;

/**
 * In-memory `HttpClientInterface` implementation for tests.
 *
 * Records every call and replays a queue of canned responses.
 */
final class MockHttpClient implements HttpClientInterface {

  /** @var list<RawResponse> */
  private array $responses = [];

  /** @var list<array{method: string, url: string, headers: array<string, string>, body: ?string}> */
  public array $calls = [];

  /**
   * @param array<string, mixed>  $body
   * @param array<string, string> $headers
   */
  public function enqueueJson(int $statusCode, array $body, array $headers = []): void {
    $encoded = json_encode($body);
    if ($encoded === false) {
      throw new \RuntimeException('MockHttpClient: could not encode body.');
    }
    $this->responses[] = new RawResponse($statusCode, $encoded, $headers + ['content-type' => 'application/json']);
  }

  public function enqueueRaw(RawResponse $response): void {
    $this->responses[] = $response;
  }

  public function request(string $method, string $url, array $headers = [], ?string $body = null): RawResponse {
    $this->calls[] = [
      'method' => $method,
      'url' => $url,
      'headers' => $headers,
      'body' => $body,
    ];

    if ($this->responses === []) {
      throw new \RuntimeException('MockHttpClient: no canned response queued for ' . $method . ' ' . $url);
    }
    return array_shift($this->responses);
  }

  /**
   * @return array{method: string, url: string, headers: array<string, string>, body: ?string}
   */
  public function lastCall(): array {
    if ($this->calls === []) {
      throw new \RuntimeException('MockHttpClient: no calls recorded.');
    }
    return $this->calls[array_key_last($this->calls)];
  }

}
