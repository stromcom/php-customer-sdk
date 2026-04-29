<?php
declare(strict_types=1);

namespace Stromcom\Sdk\Resource;

use Stromcom\Sdk\Model\AttachmentDownload;
use Stromcom\Sdk\Model\MessageAttachment;
use Stromcom\Sdk\Model\MessageList;

/**
 * `/threads/{code}/messages/` — read messages and attachments, admin-delete.
 */
final class MessageResource extends AbstractResource {

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

}
