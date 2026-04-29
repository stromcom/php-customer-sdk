<?php
declare(strict_types=1);

namespace Stromcom\Sdk\Model;

/**
 * Project user — an employee of the customer who reads and writes
 * messages inside threads. Mirrors the `Entity:User` schema.
 *
 * `readOnly` and `deletedAt` are present only in some API responses
 * (e.g. listing with state filters), hence nullable.
 */
final class User extends AbstractModel {

  public function __construct(
    public readonly string $hash,
    public readonly string $code,
    public readonly string $name,
    public readonly ?string $emailAddress,
    public readonly ?string $avatarURL,
    public readonly ?bool $readOnly,
    public readonly ?int $createdAt,
    public readonly ?int $deletedAt,
  ) {
  }

  public static function fromArray(array $data): static {
    return new self(
      hash: self::requireString($data, 'hash'),
      code: self::requireString($data, 'code'),
      name: self::requireString($data, 'name'),
      emailAddress: self::readString($data, 'emailAddress'),
      avatarURL: self::readString($data, 'avatarURL'),
      readOnly: self::readBool($data, 'readOnly'),
      createdAt: self::readInt($data, 'createdAt'),
      deletedAt: self::readInt($data, 'deletedAt'),
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
