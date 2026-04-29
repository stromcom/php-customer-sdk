<?php
declare(strict_types=1);

namespace Stromcom\Sdk\Model;

/**
 * File attached to a message. `state` is the storage-layer state
 * (`StorageItemStateEnum` in the spec) — left as a free-form string
 * so new states added server-side do not break SDK consumers.
 */
final class MessageAttachment extends AbstractModel {

  public function __construct(
    public readonly string $hash,
    public readonly string $messageHash,
    public readonly ?string $state,
    public readonly string $fileName,
    public readonly ?int $fileSize,
    public readonly ?int $createdAt,
  ) {
  }

  public static function fromArray(array $data): static {
    return new self(
      hash: self::requireString($data, 'hash'),
      messageHash: self::requireString($data, 'message_hash'),
      state: self::readString($data, 'state'),
      fileName: self::requireString($data, 'fileName'),
      fileSize: self::readInt($data, 'fileSize'),
      createdAt: self::readInt($data, 'createdAt'),
    );
  }

  /**
   * @param list<array<string, mixed>> $items
   *
   * @return list<self>
   */
  public static function listFromArray(array $items): array {
    return array_map(static fn(array $row): self => self::fromArray($row), $items);
  }

}
