<?php
declare(strict_types=1);

namespace Stromcom\Sdk\Model;

/**
 * Conversation thread bound to a customer business entity (order,
 * ticket, deal). `code` is the customer-supplied stable id; `hash`
 * is the API-assigned identifier used in URLs and embeddable links.
 */
final class Thread extends AbstractModel {

  /**
   * @param list<ThreadAttribute> $attributes
   */
  public function __construct(
    public readonly string $code,
    public readonly string $hash,
    public readonly string $name,
    public readonly ?string $url,
    public readonly array $attributes,
    public readonly ?int $createdAt,
  ) {
  }

  public static function fromArray(array $data): static {
    $attributes = [];
    if (isset($data['attributes']) && is_array($data['attributes'])) {
      /** @var array<int, mixed> $rawAttributes */
      $rawAttributes = $data['attributes'];
      foreach ($rawAttributes as $row) {
        if (is_array($row)) {
          /** @var array<string, mixed> $row */
          $attributes[] = ThreadAttribute::fromArray($row);
        }
      }
    }

    return new self(
      code: self::requireString($data, 'code'),
      hash: self::requireString($data, 'hash'),
      name: self::requireString($data, 'name'),
      url: self::readString($data, 'url'),
      attributes: $attributes,
      createdAt: self::readInt($data, 'createdAt'),
    );
  }

}
