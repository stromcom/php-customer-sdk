<?php
declare(strict_types=1);

namespace Stromcom\Sdk\Http;

use Stromcom\Sdk\Exception\TransportException;

final class CurlHttpClient implements HttpClientInterface {

  public function __construct(
    private readonly int $timeout = 10,
  ) {
  }

  public function request(
    string $method,
    string $url,
    array $headers = [],
    ?string $body = null,
  ): RawResponse {
    if ($method === '' || $url === '') {
      throw new TransportException('HTTP method and URL must not be empty.');
    }

    $ch = curl_init();
    if ($ch === false) {
      throw new TransportException('Could not initialize cURL.');
    }

    $headerLines = [];
    foreach ($headers as $name => $value) {
      $headerLines[] = $name . ': ' . $value;
    }

    $responseHeaders = [];

    curl_setopt_array($ch, [
      CURLOPT_URL => $url,
      CURLOPT_CUSTOMREQUEST => $method,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_FOLLOWLOCATION => false,
      CURLOPT_CONNECTTIMEOUT => $this->timeout,
      CURLOPT_TIMEOUT => $this->timeout,
      CURLOPT_HTTPHEADER => $headerLines,
      CURLOPT_HEADERFUNCTION => static function ($_ch, string $line) use (&$responseHeaders): int {
        $trimmed = trim($line);
        if ($trimmed !== '' && str_contains($trimmed, ':')) {
          [$name, $value] = explode(':', $trimmed, 2);
          $responseHeaders[strtolower(trim($name))] = trim($value);
        }
        return strlen($line);
      },
    ]);

    if ($body !== null) {
      curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }

    $rawBody = curl_exec($ch);
    if ($rawBody === false) {
      $error = curl_error($ch) !== '' ? curl_error($ch) : 'unknown cURL error';
      $errno = curl_errno($ch);
      throw new TransportException(
        sprintf('HTTP request failed: %s (errno %d)', $error, $errno),
        $errno,
      );
    }

    $statusCode = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

    return new RawResponse($statusCode, is_string($rawBody) ? $rawBody : '', $responseHeaders);
  }

}
