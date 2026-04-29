<?php
declare(strict_types=1);

namespace Stromcom\Sdk\Model;

abstract class AbstractModel {

  /**
   * Hydrate an instance from an associative API payload.
   *
   * Implementations are tolerant of missing keys (the Customer API omits
   * fields it considers irrelevant for the current state) and ignore
   * unknown keys (forward compatibility — a new server field does not
   * break existing SDK clients).
   *
   * @param array<string, mixed> $data
   */
  abstract public static function fromArray(array $data): static;

  /**
   * @param array<string, mixed> $data
   */
  protected static function readString(array $data, string $key): ?string {
    $value = $data[$key] ?? null;
    return is_string($value) ? $value : null;
  }

  /**
   * @param array<string, mixed> $data
   */
  protected static function requireString(array $data, string $key): string {
    $value = $data[$key] ?? null;
    if (!is_string($value)) {
      throw new \UnexpectedValueException(sprintf(
        '%s: missing or non-string field "%s".',
        static::class,
        $key,
      ));
    }
    return $value;
  }

  /**
   * @param array<string, mixed> $data
   */
  protected static function readBool(array $data, string $key): ?bool {
    $value = $data[$key] ?? null;
    return is_bool($value) ? $value : null;
  }

  /**
   * @param array<string, mixed> $data
   */
  protected static function readInt(array $data, string $key): ?int {
    $value = $data[$key] ?? null;
    if (is_int($value)) {
      return $value;
    }
    if (is_float($value) && (float) (int) $value === $value) {
      return (int) $value;
    }
    return null;
  }

}
