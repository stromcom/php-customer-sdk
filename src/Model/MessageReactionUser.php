<?php
declare(strict_types=1);

namespace Stromcom\Sdk\Model;

/**
 * A single user who reacted with a given emoji on a message.
 */
final class MessageReactionUser extends AbstractModel {

  public function __construct(
    public readonly string $hash,
    public readonly string $name,
  ) {
  }

  public static function fromArray(array $data): static {
    return new self(
      hash: self::requireString($data, 'hash'),
      name: self::requireString($data, 'name'),
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
