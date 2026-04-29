<?php
declare(strict_types=1);

namespace Stromcom\Sdk\Model;

/**
 * Single message in a thread. The Customer API exposes both a public
 * envelope (`Response:Model:Message`, with `userName`) and the entity
 * shape (`Entity:Message`, with `state` + `messagePreview`); this DTO
 * unifies both — fields the current endpoint did not return are null.
 *
 * Hash field naming on the wire is inconsistent (`user_hash` vs
 * `userHash`); both spellings are accepted on hydration.
 */
final class Message extends AbstractModel {

  public function __construct(
    public readonly string $hash,
    public readonly string $message,
    public readonly ?string $messagePreview,
    public readonly ?string $userHash,
    public readonly ?string $userName,
    public readonly ?string $threadHash,
    public readonly ?string $state,
    public readonly ?int $createdAt,
  ) {
  }

  public static function fromArray(array $data): static {
    $userHash = self::readString($data, 'user_hash') ?? self::readString($data, 'userHash');
    $threadHash = self::readString($data, 'thread_hash') ?? self::readString($data, 'threadHash');

    return new self(
      hash: self::requireString($data, 'hash'),
      message: self::requireString($data, 'message'),
      messagePreview: self::readString($data, 'messagePreview'),
      userHash: $userHash,
      userName: self::readString($data, 'userName'),
      threadHash: $threadHash,
      state: self::readString($data, 'state'),
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
