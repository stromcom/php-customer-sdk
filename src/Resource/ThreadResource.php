<?php
declare(strict_types=1);

namespace Stromcom\Sdk\Resource;

use Stromcom\Sdk\Model\Message;
use Stromcom\Sdk\Model\MessageList;
use Stromcom\Sdk\Model\Thread;

/**
 * `/threads/` — create, read and update threads bound to your business
 * entities by `code`.
 */
final class ThreadResource extends AbstractResource {

  /**
   * POST /threads/ — create a thread (upsert by `code` — an existing thread
   * with the same code is updated). Optionally include an initial `message`;
   * when `message` or `attachments` are present in $payload, the author must
   * be identified via `user_hash` or `user_code`.
   *
   * @param array<string, mixed> $payload
   *
   * @return array{thread: Thread, message: ?Message, attachmentsUploadURL: list<string>}
   */
  public function create(array $payload): array {
    /** @var array<string, mixed> $data */
    $data = $this->client->request('POST', 'threads/', body: $payload) ?? [];

    /** @var array<string, mixed> $threadData */
    $threadData = isset($data['thread']) && is_array($data['thread']) ? $data['thread'] : [];
    /** @var array<string, mixed>|null $messageData */
    $messageData = isset($data['message']) && is_array($data['message']) ? $data['message'] : null;
    /** @var list<string> $uploadURLs */
    $uploadURLs = isset($data['attachmentsUploadURL']) && is_array($data['attachmentsUploadURL'])
      ? array_values($data['attachmentsUploadURL'])
      : [];

    return [
      'thread' => Thread::fromArray($threadData),
      'message' => $messageData !== null ? Message::fromArray($messageData) : null,
      'attachmentsUploadURL' => $uploadURLs,
    ];
  }

  /**
   * PUT /threads/{code}/ — update thread metadata (`name`, `url`,
   * `attributes`). Only the keys present in $changes are written.
   *
   * @param array<string, mixed> $changes
   */
  public function update(string $code, array $changes): Thread {
    /** @var array<string, mixed> $data */
    $data = $this->client->request('PUT', "threads/{$code}/", body: $changes) ?? [];
    return Thread::fromArray($this->extractThread($data));
  }

  /**
   * GET /threads/{code}/
   *
   * `expand` does not change the return type — the inline messages are
   * fetched via {@see ThreadResource::getWithMessages()} when you need
   * a typed surface for both. If you only need the thread metadata,
   * leave `expand` empty.
   *
   * @param list<string> $expand Optional. Pass `['messages']` to include
   *                             the messages payload server-side; for
   *                             a typed result use `getWithMessages()`.
   */
  public function get(string $code, array $expand = []): Thread {
    /** @var array<string, mixed> $data */
    $data = $this->client->request('GET', "threads/{$code}/", query: [
      'expand' => $expand !== [] ? implode(',', $expand) : null,
    ]) ?? [];

    return Thread::fromArray($this->extractThread($data));
  }

  /**
   * GET /threads/{code}/?expand=messages
   *
   * Returns a tuple-like object: the thread plus its messages and
   * attachments. The Customer API ships the same payload as a single
   * round-trip — preferable to two separate calls when you need both.
   *
   * @return array{thread: Thread, messages: MessageList}
   */
  public function getWithMessages(string $code): array {
    /** @var array<string, mixed> $data */
    $data = $this->client->request('GET', "threads/{$code}/", query: [
      'expand' => 'messages',
    ]) ?? [];

    /** @var array<string, mixed> $threadData */
    $threadData = $this->extractThread($data);

    /** @var array<string, mixed> $messagesPayload */
    $messagesPayload = (isset($data['messages']) && is_array($data['messages']))
      ? ['messages' => $data['messages'], 'messagesAttachments' => $data['messagesAttachments'] ?? []]
      : ['messages' => [], 'messagesAttachments' => []];

    return [
      'thread'   => Thread::fromArray($threadData),
      'messages' => MessageList::fromArray($messagesPayload),
    ];
  }

  /**
   * The endpoint sometimes wraps the thread in `{thread: …}` (when
   * `expand=messages`) and sometimes returns it flat. Normalise.
   *
   * @param array<string, mixed> $data
   *
   * @return array<string, mixed>
   */
  private function extractThread(array $data): array {
    if (isset($data['thread']) && is_array($data['thread'])) {
      /** @var array<string, mixed> $thread */
      $thread = $data['thread'];
      return $thread;
    }
    return $data;
  }

}
