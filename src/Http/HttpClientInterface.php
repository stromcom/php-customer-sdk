<?php
declare(strict_types=1);

namespace Stromcom\Sdk\Http;

use Stromcom\Sdk\Exception\TransportException;

interface HttpClientInterface {

  /**
   * Perform an HTTP request and return the raw response.
   *
   * @param string                $method  Uppercase HTTP method (GET, POST, PATCH, DELETE…).
   * @param string                $url     Fully-qualified URL.
   * @param array<string, string> $headers Header name => value (case-insensitive on the wire).
   * @param string|null           $body    Already-serialized request body (JSON / form / null).
   *
   * @throws TransportException On network-level failures (DNS, timeout, TLS…).
   */
  public function request(
    string $method,
    string $url,
    array $headers = [],
    ?string $body = null,
  ): RawResponse;

}
