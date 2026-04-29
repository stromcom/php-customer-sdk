<?php
declare(strict_types=1);

namespace Stromcom\Sdk\Http;

final class RawResponse {

  /**
   * @param array<string, string> $headers
   */
  public function __construct(
    public readonly int $statusCode,
    public readonly string $body,
    public readonly array $headers = [],
  ) {
  }

}
