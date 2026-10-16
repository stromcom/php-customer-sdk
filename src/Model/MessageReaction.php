<?php
declare(strict_types=1);

namespace Stromcom\Sdk\Model;

/**
 * Emoji reactions on a message, aggregated by emoji code. `currentUserReacted`
 * reflects the credential the SDK authenticates as — read-only for customers,
 * reacting itself is only available to end users via the App API.
 */
final class MessageReaction extends AbstractModel {

  /** @param list<MessageReactionUser> $users */
  public function __construct(
    public readonly string $code,
    public readonly int $count,
    public readonly bool $currentUserReacted,
    public readonly array $users,
  ) {
  }

  public static function fromArray(array $data): static {
    /** @var list<array<string, mixed>> $rawUsers */
    $rawUsers = isset($data['users']) && is_array($data['users']) ? array_values($data['users']) : [];

    return new self(
      code: self::requireString($data, 'code'),
      count: self::readInt($data, 'count') ?? 0,
      currentUserReacted: self::readBool($data, 'currentUserReacted') ?? false,
      users: MessageReactionUser::listFromArray($rawUsers),
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
