<?php
declare(strict_types=1);

namespace Stromcom\Sdk\Model;

/**
 * Custom attribute attached to a thread (max 20 per thread).
 * `type` is a server-defined integer code — kept as int to avoid
 * coupling the SDK to an enum that the API may extend.
 */
final class ThreadAttribute extends AbstractModel {

  public function __construct(
    public readonly string $key,
    public readonly string $value,
    public readonly ?int $type,
  ) {
  }

  public static function fromArray(array $data): static {
    return new self(
      key: self::requireString($data, 'key'),
      value: self::requireString($data, 'value'),
      type: self::readInt($data, 'type'),
    );
  }

}
