<?php
declare(strict_types=1);

namespace Stromcom\Sdk\Tests\Resource;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Stromcom\Sdk\Client;
use Stromcom\Sdk\Configuration;
use Stromcom\Sdk\Tests\Http\MockHttpClient;

final class MessageResourceTest extends TestCase {

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
  public function create_sends_payload_and_hydrates_message(): void {
    $this->http->enqueueJson(201, [
      'status' => 'success',
      'data' => [
        'message' => ['hash' => 'm1', 'message' => '<p>Hi</p>', 'userName' => 'Jane'],
        'attachmentsUploadURL' => ['https://upload.example/invoice.pdf'],
      ],
    ]);

    $result = $this->client->messages()->create('order-1', [
      'message' => '<p>Hi</p>',
      'user_code' => 'u1',
      'attachments' => ['invoice.pdf'],
    ]);

    $this->assertSame('m1', $result['message']->hash);
    $this->assertSame('Jane', $result['message']->userName);
    $this->assertSame(['https://upload.example/invoice.pdf'], $result['attachmentsUploadURL']);

    $call = $this->http->lastCall();
    $this->assertSame('POST', $call['method']);
    $this->assertSame(self::BASE_URL . 'threads/order-1/messages/', $call['url']);
    $this->assertSame(
      ['message' => '<p>Hi</p>', 'user_code' => 'u1', 'attachments' => ['invoice.pdf']],
      json_decode((string) $call['body'], true),
    );
  }

  #[Test]
  public function create_defaults_upload_urls_to_empty_list(): void {
    $this->http->enqueueJson(201, [
      'status' => 'success',
      'data' => ['message' => ['hash' => 'm1', 'message' => '<p>Hi</p>']],
    ]);

    $result = $this->client->messages()->create('order-1', ['message' => '<p>Hi</p>']);

    $this->assertSame([], $result['attachmentsUploadURL']);
  }

  #[Test]
  public function list_returns_messages_and_attachments(): void {
    $this->http->enqueueJson(200, [
      'status' => 'success',
      'data' => [
        'messages' => [['hash' => 'm1', 'message' => '<p>hi</p>', 'userName' => 'Jane']],
        'messagesAttachments' => [
          'm1' => [
            ['hash' => 'a1', 'message_hash' => 'm1', 'fileName' => 'doc.pdf', 'fileSize' => 1024],
          ],
        ],
      ],
    ]);

    $result = $this->client->messages()->list('order-1');

    $this->assertCount(1, $result->messages);
    $this->assertSame('m1', $result->messages[0]->hash);
    $this->assertSame('Jane', $result->messages[0]->userName);
    $this->assertCount(1, $result->attachmentsFor('m1'));
    $this->assertSame('doc.pdf', $result->attachmentsFor('m1')[0]->fileName);
    $this->assertSame(1024, $result->attachmentsFor('m1')[0]->fileSize);
    $this->assertSame([], $result->attachmentsFor('m-missing'));
    $this->assertSame(self::BASE_URL . 'threads/order-1/messages/', $this->http->lastCall()['url']);
  }

  #[Test]
  public function list_passes_cursor_via_query(): void {
    $this->http->enqueueJson(200, [
      'status' => 'success',
      'data' => ['messages' => [], 'messagesAttachments' => []],
    ]);

    $this->client->messages()->list('order-1', lastMessageHash: 'cur42');

    $this->assertSame(
      self::BASE_URL . 'threads/order-1/messages/?lastMessageHash=cur42',
      $this->http->lastCall()['url'],
    );
  }

  #[Test]
  public function delete_uses_http_delete(): void {
    $this->http->enqueueJson(200, ['status' => 'success', 'data' => ['deleted' => true]]);

    $this->client->messages()->delete('order-1', 'm1');

    $call = $this->http->lastCall();
    $this->assertSame('DELETE', $call['method']);
    $this->assertSame(self::BASE_URL . 'threads/order-1/messages/m1/', $call['url']);
  }

  #[Test]
  public function attachments_lists_attachments(): void {
    $this->http->enqueueJson(200, [
      'status' => 'success',
      'data' => [
        ['hash' => 'a1', 'message_hash' => 'm1', 'fileName' => 'invoice.pdf', 'fileSize' => 12_345],
      ],
    ]);

    $list = $this->client->messages()->attachments('order-1', 'm1');

    $this->assertSame('a1', $list[0]->hash);
    $this->assertSame('invoice.pdf', $list[0]->fileName);
    $this->assertSame(12_345, $list[0]->fileSize);
    $this->assertSame(
      self::BASE_URL . 'threads/order-1/messages/m1/attachments/',
      $this->http->lastCall()['url'],
    );
  }

  #[Test]
  public function attachment_download_returns_signed_url_payload(): void {
    $this->http->enqueueJson(200, [
      'status' => 'success',
      'data' => ['url' => 'https://signed.example/file.pdf?sig=abc'],
    ]);

    $payload = $this->client->messages()->attachmentDownload('order-1', 'm1', 'a1');

    $this->assertSame('https://signed.example/file.pdf?sig=abc', $payload->url);
    $this->assertSame(
      self::BASE_URL . 'threads/order-1/messages/m1/attachments/a1/download/',
      $this->http->lastCall()['url'],
    );
  }

  #[Test]
  public function reactions_returns_aggregated_reactions(): void {
    $this->http->enqueueJson(200, [
      'status' => 'success',
      'data' => [
        'reactions' => [
          [
            'code' => '👍',
            'count' => 2,
            'currentUserReacted' => true,
            'users' => [
              ['hash' => 'u1', 'name' => 'Jane'],
              ['hash' => 'u2', 'name' => 'John'],
            ],
          ],
        ],
      ],
    ]);

    $reactions = $this->client->messages()->reactions('order-1', 'm1');

    $this->assertCount(1, $reactions);
    $this->assertSame('👍', $reactions[0]->code);
    $this->assertSame(2, $reactions[0]->count);
    $this->assertTrue($reactions[0]->currentUserReacted);
    $this->assertCount(2, $reactions[0]->users);
    $this->assertSame('u1', $reactions[0]->users[0]->hash);
    $this->assertSame('Jane', $reactions[0]->users[0]->name);
    $this->assertSame(
      self::BASE_URL . 'threads/order-1/messages/m1/reactions/',
      $this->http->lastCall()['url'],
    );
  }

  #[Test]
  public function reactions_returns_empty_list_when_message_has_none(): void {
    $this->http->enqueueJson(200, [
      'status' => 'success',
      'data' => ['reactions' => []],
    ]);

    $this->assertSame([], $this->client->messages()->reactions('order-1', 'm1'));
  }

}
