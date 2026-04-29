<?php
declare(strict_types=1);

namespace Stromcom\Sdk\Exception;

/**
 * Thrown when the API returns a non-2xx response with a parseable error
 * envelope. Specific subclasses are thrown for well-known statuses; generic
 * `ApiException` is used as a catch-all when nothing more specific fits.
 */
class ApiException extends StromcomException {

  /**
   * @param array<string, mixed> $details
   */
  public function __construct(
    string $message,
    private readonly int $statusCode,
    private readonly ?string $apiCode = null,
    private readonly array $details = [],
  ) {
    parent::__construct($message, $statusCode);
  }

  public function getStatusCode(): int {
    return $this->statusCode;
  }

  public function getApiCode(): ?string {
    return $this->apiCode;
  }

  /**
   * @return array<string, mixed>
   */
  public function getDetails(): array {
    return $this->details;
  }

}
