<?php
declare(strict_types=1);

namespace Stromcom\Sdk\Model;

/**
 * Combined response of `GET /threads/{code}/messages/`. The API returns
 * the messages and their attachments in two parallel maps; this DTO
 * keeps that grouping (so the caller can render attachments next to
 * their message in O(1)) but exposes a typed surface.
 *
 * `attachmentsByMessage` is keyed by message hash; missing key means
 * "no attachments for that message".
 */
final class MessageList extends AbstractModel {

  /**
   * @param list<Message>                          $messages
   * @param array<string, list<MessageAttachment>> $attachmentsByMessage
   */
  public function __construct(
    public readonly array $messages,
    public readonly array $attachmentsByMessage,
  ) {
  }

  public static function fromArray(array $data): static {
    /** @var list<array<string, mixed>> $rawMessages */
    $rawMessages = (isset($data['messages']) && is_array($data['messages'])) ? array_values($data['messages']) : [];

    $attachmentsByMessage = [];
    if (isset($data['messagesAttachments']) && is_array($data['messagesAttachments'])) {
      /** @var array<string, mixed> $rawMap */
      $rawMap = $data['messagesAttachments'];
      foreach ($rawMap as $messageHash => $rows) {
        if (!is_string($messageHash) || !is_array($rows)) {
          continue;
        }
        /** @var list<array<string, mixed>> $list */
        $list = array_values(array_filter($rows, 'is_array'));
        $attachmentsByMessage[$messageHash] = MessageAttachment::listFromArray($list);
      }
    }

    return new self(
      messages: Message::listFromArray($rawMessages),
      attachmentsByMessage: $attachmentsByMessage,
    );
  }

  /**
   * Return the attachments attached to the given message, or an empty
   * list when there are none.
   *
   * @return list<MessageAttachment>
   */
  public function attachmentsFor(string $messageHash): array {
    return $this->attachmentsByMessage[$messageHash] ?? [];
  }

}
