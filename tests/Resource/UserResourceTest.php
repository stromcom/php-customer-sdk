<?php
declare(strict_types=1);

namespace Stromcom\Sdk\Tests\Resource;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Stromcom\Sdk\Client;
use Stromcom\Sdk\Configuration;
use Stromcom\Sdk\Tests\Http\MockHttpClient;

final class UserResourceTest extends TestCase {

  private const string BASE_URL = 'https://api.example.test/customer/v1/';

  private MockHttpClient $http;
  private Client $client;

  protected function setUp(): void {
    $this->http = new MockHttpClient();
    $this->client = new Client(
      new Configuration(token: 'tok', baseUrl: self::BASE_URL),
      $this->http,
    );
  }

  #[Test]
  public function list_uses_default_state_filter_implicitly(): void {
    $this->http->enqueueJson(200, [
      'status' => 'success',
      'data' => [
        ['hash' => 'h1', 'code' => 'e1', 'name' => 'Jane'],
        ['hash' => 'h2', 'code' => 'e2', 'name' => 'Joe'],
      ],
    ]);

    $users = $this->client->users()->list();

    $this->assertCount(2, $users);
    $this->assertSame('Jane', $users[0]->name);
    $this->assertSame(self::BASE_URL . 'users/', $this->http->lastCall()['url']);
  }

  #[Test]
  public function list_passes_state_and_code_filters(): void {
    $this->http->enqueueJson(200, ['status' => 'success', 'data' => []]);

    $this->client->users()->list(state: 'all', code: 'employee-1');

    $this->assertSame(
      self::BASE_URL . 'users/?state=all&code=employee-1',
      $this->http->lastCall()['url'],
    );
  }

  #[Test]
  public function get_hits_user_detail_endpoint(): void {
    $this->http->enqueueJson(200, [
      'status' => 'success',
      'data' => ['hash' => 'h1', 'code' => 'e1', 'name' => 'Jane'],
    ]);

    $user = $this->client->users()->get('h1');

    $this->assertSame('Jane', $user->name);
    $this->assertSame('h1', $user->hash);
    $this->assertSame('e1', $user->code);
    $this->assertSame(self::BASE_URL . 'users/h1/', $this->http->lastCall()['url']);
  }

  #[Test]
  public function create_posts_payload_and_returns_user_account_pair(): void {
    $this->http->enqueueJson(201, [
      'status' => 'success',
      'data' => [
        'user' => ['hash' => 'h1', 'code' => 'e1', 'name' => 'Jane'],
        'account' => ['hash' => 'a1', 'code' => 'e1', 'name' => 'Jane', 'state' => 'active'],
      ],
    ]);

    $created = $this->client->users()->create([
      'code' => 'e1',
      'name' => 'Jane',
    ]);

    $this->assertSame('h1', $created->user->hash);
    $this->assertSame('a1', $created->account->hash);
    $this->assertSame('active', $created->account->state);
    $call = $this->http->lastCall();
    $this->assertSame('POST', $call['method']);
    $this->assertSame('{"code":"e1","name":"Jane"}', $call['body']);
  }

  #[Test]
  public function update_sends_patch_with_changed_fields(): void {
    $this->http->enqueueJson(200, [
      'status' => 'success',
      'data' => ['hash' => 'h1', 'code' => 'e1', 'name' => 'Jane Smith'],
    ]);

    $this->client->users()->update('h1', ['name' => 'Jane Smith']);

    $call = $this->http->lastCall();
    $this->assertSame('PATCH', $call['method']);
    $this->assertSame(self::BASE_URL . 'users/h1/', $call['url']);
    $this->assertSame('{"name":"Jane Smith"}', $call['body']);
  }

  #[Test]
  public function delete_uses_http_delete(): void {
    $this->http->enqueueJson(200, ['status' => 'success', 'data' => ['deleted' => true]]);

    $this->client->users()->delete('h1');

    $this->assertSame('DELETE', $this->http->lastCall()['method']);
  }

  #[Test]
  public function restore_posts_to_restore_endpoint(): void {
    $this->http->enqueueJson(200, [
      'status' => 'success',
      'data' => ['restored' => true, 'user' => ['hash' => 'h1', 'code' => 'e1', 'name' => 'Jane']],
    ]);

    $user = $this->client->users()->restore('h1');
    $this->assertSame('h1', $user->hash);

    $call = $this->http->lastCall();
    $this->assertSame('POST', $call['method']);
    $this->assertSame(self::BASE_URL . 'users/h1/restore/', $call['url']);
  }

}
