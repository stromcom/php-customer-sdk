<?php
declare(strict_types=1);

namespace Stromcom\Sdk\Tests\Resource;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Stromcom\Sdk\Client;
use Stromcom\Sdk\Configuration;
use Stromcom\Sdk\Tests\Http\MockHttpClient;

final class ThreadResourceTest extends TestCase {

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
  public function create_upserts_thread_without_message(): void {
    $this->http->enqueueJson(201, [
      'status' => 'success',
      'data' => [
        'thread' => ['code' => 'order-1', 'hash' => 'eVO6SY5kf5', 'name' => 'Order 1'],
        'message' => null,
        'attachmentsUploadURL' => [],
      ],
    ]);

    $result = $this->client->threads()->create(['code' => 'order-1', 'name' => 'Order 1']);

    $this->assertSame('order-1', $result['thread']->code);
    $this->assertNull($result['message']);
    $this->assertSame([], $result['attachmentsUploadURL']);

    $call = $this->http->lastCall();
    $this->assertSame('POST', $call['method']);
    $this->assertSame(self::BASE_URL . 'threads/', $call['url']);
    $this->assertSame(['code' => 'order-1', 'name' => 'Order 1'], json_decode((string) $call['body'], true));
  }

  #[Test]
  public function create_with_initial_message_hydrates_message_and_upload_urls(): void {
    $this->http->enqueueJson(201, [
      'status' => 'success',
      'data' => [
        'thread' => ['code' => 'order-1', 'hash' => 'eVO6SY5kf5', 'name' => 'Order 1'],
        'message' => ['hash' => 'm1', 'message' => '<p>Hi</p>', 'userName' => 'Jane'],
        'attachmentsUploadURL' => ['https://upload.example/invoice.pdf'],
      ],
    ]);

    $result = $this->client->threads()->create([
      'code' => 'order-1',
      'message' => '<p>Hi</p>',
      'user_code' => 'u1',
      'attachments' => ['invoice.pdf'],
    ]);

    $this->assertSame('m1', $result['message']?->hash);
    $this->assertSame(['https://upload.example/invoice.pdf'], $result['attachmentsUploadURL']);
  }

  #[Test]
  public function update_sends_changes_and_returns_thread(): void {
    $this->http->enqueueJson(200, [
      'status' => 'success',
      'data' => ['code' => 'order-1', 'hash' => 'eVO6SY5kf5', 'name' => 'Renamed'],
    ]);

    $thread = $this->client->threads()->update('order-1', ['name' => 'Renamed']);

    $this->assertSame('Renamed', $thread->name);

    $call = $this->http->lastCall();
    $this->assertSame('PUT', $call['method']);
    $this->assertSame(self::BASE_URL . 'threads/order-1/', $call['url']);
    $this->assertSame(['name' => 'Renamed'], json_decode((string) $call['body'], true));
  }

  #[Test]
  public function get_without_expand_omits_query_string(): void {
    $this->http->enqueueJson(200, [
      'status' => 'success',
      'data' => ['code' => 'order-1', 'hash' => 'eVO6SY5kf5', 'name' => 'Order 1'],
    ]);

    $thread = $this->client->threads()->get('order-1');

    $this->assertSame('order-1', $thread->code);
    $this->assertSame('eVO6SY5kf5', $thread->hash);
    $this->assertSame('Order 1', $thread->name);
    $this->assertSame(self::BASE_URL . 'threads/order-1/', $this->http->lastCall()['url']);
  }

  #[Test]
  public function get_hydrates_attributes(): void {
    $this->http->enqueueJson(200, [
      'status' => 'success',
      'data' => [
        'code' => 'order-1',
        'hash' => 'eVO6SY5kf5',
        'name' => 'Order 1',
        'attributes' => [
          ['key' => 'status', 'value' => 'paid', 'type' => 1],
        ],
      ],
    ]);

    $thread = $this->client->threads()->get('order-1');

    $this->assertCount(1, $thread->attributes);
    $this->assertSame('status', $thread->attributes[0]->key);
    $this->assertSame('paid', $thread->attributes[0]->value);
    $this->assertSame(1, $thread->attributes[0]->type);
  }

  #[Test]
  public function get_with_expand_appends_query(): void {
    $this->http->enqueueJson(200, [
      'status' => 'success',
      'data' => ['code' => 'order-1', 'hash' => 'eVO6SY5kf5', 'name' => 'Order 1'],
    ]);

    $this->client->threads()->get('order-1', expand: ['messages']);

    $this->assertSame(
      self::BASE_URL . 'threads/order-1/?expand=messages',
      $this->http->lastCall()['url'],
    );
  }

  #[Test]
  public function get_with_messages_unwraps_thread_envelope(): void {
    $this->http->enqueueJson(200, [
      'status' => 'success',
      'data' => [
        'thread' => ['code' => 'order-1', 'hash' => 'eVO6SY5kf5', 'name' => 'Order 1'],
        'messages' => [
          ['hash' => 'm1', 'message' => '<p>Hi</p>', 'userName' => 'Jane'],
        ],
        'messagesAttachments' => [
          'm1' => [
            ['hash' => 'a1', 'message_hash' => 'm1', 'fileName' => 'doc.pdf'],
          ],
        ],
      ],
    ]);

    $result = $this->client->threads()->getWithMessages('order-1');

    $this->assertSame('order-1', $result['thread']->code);
    $this->assertCount(1, $result['messages']->messages);
    $this->assertSame('Jane', $result['messages']->messages[0]->userName);
    $this->assertCount(1, $result['messages']->attachmentsFor('m1'));
    $this->assertSame('doc.pdf', $result['messages']->attachmentsFor('m1')[0]->fileName);
    $this->assertSame(
      self::BASE_URL . 'threads/order-1/?expand=messages',
      $this->http->lastCall()['url'],
    );
  }

}
