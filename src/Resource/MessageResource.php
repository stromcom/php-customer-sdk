<?php
declare(strict_types=1);

namespace Stromcom\Sdk\Resource;

use Stromcom\Sdk\Model\AttachmentDownload;
use Stromcom\Sdk\Model\Message;
use Stromcom\Sdk\Model\MessageAttachment;
use Stromcom\Sdk\Model\MessageList;
use Stromcom\Sdk\Model\MessageReaction;

/**
 * `/threads/{code}/messages/` — create, read messages and attachments, admin-delete.
 */
final class MessageResource extends AbstractResource {

  /**
   * POST /threads/{code}/messages/ — create a message in an existing thread.
   * The author is identified via `user_hash` or `user_code` in $payload
   * (requests are not bound to a logged-in user). Attachments are uploaded
   * in two steps: pass file names in `attachments`, then PUT each file's
   * bytes to the matching `attachmentsUploadURL` entry.
   *
   * @param array<string, mixed> $payload
   *
   * @return array{message: Message, attachmentsUploadURL: list<string>}
   */
  public function create(string $threadCode, array $payload): array {
    /** @var array<string, mixed> $data */
    $data = $this->client->request('POST', "threads/{$threadCode}/messages/", body: $payload) ?? [];

    /** @var array<string, mixed> $messageData */
    $messageData = isset($data['message']) && is_array($data['message']) ? $data['message'] : [];
    /** @var list<string> $uploadURLs */
    $uploadURLs = isset($data['attachmentsUploadURL']) && is_array($data['attachmentsUploadURL'])
      ? array_values($data['attachmentsUploadURL'])
      : [];

    return [
      'message' => Message::fromArray($messageData),
      'attachmentsUploadURL' => $uploadURLs,
    ];
  }

  /**
   * GET /threads/{code}/messages/
   *
   * @param string|null $lastMessageHash Cursor — return messages from this hash (inclusive).
   */
  public function list(string $threadCode, ?string $lastMessageHash = null): MessageList {
    /** @var array<string, mixed> $data */
    $data = $this->client->request('GET', "threads/{$threadCode}/messages/", query: [
      'lastMessageHash' => $lastMessageHash,
    ]) ?? [];
    return MessageList::fromArray($data);
  }

  /**
   * DELETE /threads/{code}/messages/{messageHash}/ — admin-delete a message.
   */
  public function delete(string $threadCode, string $messageHash): void {
    $this->client->request('DELETE', "threads/{$threadCode}/messages/{$messageHash}/");
  }

  /**
   * GET /threads/{code}/messages/{messageHash}/attachments/
   *
   * @return list<MessageAttachment>
   */
  public function attachments(string $threadCode, string $messageHash): array {
    /** @var list<array<string, mixed>> $data */
    $data = $this->client->request('GET', "threads/{$threadCode}/messages/{$messageHash}/attachments/") ?? [];
    return MessageAttachment::listFromArray($data);
  }

  /**
   * GET /threads/{code}/messages/{messageHash}/attachments/{attachmentHash}/download/
   *
   * Returns a signed download URL — short-lived, do not cache.
   */
  public function attachmentDownload(string $threadCode, string $messageHash, string $attachmentHash): AttachmentDownload {
    /** @var array<string, mixed> $data */
    $data = $this->client->request(
      'GET',
      "threads/{$threadCode}/messages/{$messageHash}/attachments/{$attachmentHash}/download/",
    ) ?? [];
    return AttachmentDownload::fromArray($data);
  }

  /**
   * GET /threads/{code}/messages/{messageHash}/reactions/ — emoji reactions
   * aggregated by code. Read-only: reacting is only available to end users
   * via the App API, not to the customer credential.
   *
   * @return list<MessageReaction>
   */
  public function reactions(string $threadCode, string $messageHash): array {
    /** @var array<string, mixed> $data */
    $data = $this->client->request('GET', "threads/{$threadCode}/messages/{$messageHash}/reactions/") ?? [];
    /** @var list<array<string, mixed>> $rawReactions */
    $rawReactions = isset($data['reactions']) && is_array($data['reactions']) ? array_values($data['reactions']) : [];
    return MessageReaction::listFromArray($rawReactions);
  }

}
