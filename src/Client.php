<?php
declare(strict_types=1);

namespace Stromcom\Sdk;

use Stromcom\Sdk\Exception\ApiException;
use Stromcom\Sdk\Exception\AuthenticationException;
use Stromcom\Sdk\Exception\ConflictException;
use Stromcom\Sdk\Exception\NotFoundException;
use Stromcom\Sdk\Exception\RateLimitException;
use Stromcom\Sdk\Exception\ServerException;
use Stromcom\Sdk\Exception\TransportException;
use Stromcom\Sdk\Exception\ValidationException;
use Stromcom\Sdk\Http\CurlHttpClient;
use Stromcom\Sdk\Http\HttpClientInterface;
use Stromcom\Sdk\Resource\MessageResource;
use Stromcom\Sdk\Resource\ProjectResource;
use Stromcom\Sdk\Resource\ThreadResource;
use Stromcom\Sdk\Resource\UserResource;

/**
 * Main entry point for the STROMCOM Customer API.
 *
 * @example
 *   $stromcom = new Client('YOUR_TOKEN');
 *   $project  = $stromcom->project()->get();
 */
final class Client {

  public readonly Configuration $configuration;
  private readonly HttpClientInterface $httpClient;

  private ?ProjectResource $project = null;
  private ?UserResource $users = null;
  private ?ThreadResource $threads = null;
  private ?MessageResource $messages = null;

  public function __construct(
    Configuration|string $configuration,
    ?HttpClientInterface $httpClient = null,
  ) {
    $this->configuration = is_string($configuration)
      ? new Configuration($configuration)
      : $configuration;
    $this->httpClient = $httpClient ?? new CurlHttpClient($this->configuration->timeout);
  }

  public function project(): ProjectResource {
    return $this->project ??= new ProjectResource($this);
  }

  public function users(): UserResource {
    return $this->users ??= new UserResource($this);
  }

  public function threads(): ThreadResource {
    return $this->threads ??= new ThreadResource($this);
  }

  public function messages(): MessageResource {
    return $this->messages ??= new MessageResource($this);
  }

  /**
   * Perform a request and return the decoded `data` portion of the API
   * envelope. Non-2xx responses are translated into a typed exception.
   *
   * @param array<string, scalar|null> $query
   * @param array<string, mixed>|null  $body  Encoded as JSON when non-null.
   *
   * @return array<string, mixed>|list<mixed>|null
   *
   * @throws ApiException
   * @throws TransportException
   */
  public function request(string $method, string $path, array $query = [], ?array $body = null): array|null {
    $url = $this->configuration->baseUrl . ltrim($path, '/');
    if ($query !== []) {
      $filtered = array_filter($query, static fn($v) => $v !== null);
      if ($filtered !== []) {
        $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query($filtered);
      }
    }

    $headers = [
      'Authorization' => 'Bearer ' . $this->configuration->token,
      'Accept' => 'application/json',
      'User-Agent' => $this->configuration->userAgent,
    ];

    $payload = null;
    if ($body !== null) {
      $headers['Content-Type'] = 'application/json';
      $encoded = json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
      if ($encoded === false) {
        throw new TransportException('Could not JSON-encode request body: ' . json_last_error_msg());
      }
      $payload = $encoded;
    }

    $response = $this->httpClient->request($method, $url, $headers, $payload);

    return $this->decodeEnvelope($response->statusCode, $response->body);
  }

  /**
   * @return array<string, mixed>|list<mixed>|null
   */
  private function decodeEnvelope(int $statusCode, string $body): array|null {
    if ($body === '') {
      $decoded = null;
    } else {
      $raw = json_decode($body, true);
      if (!is_array($raw)) {
        throw new TransportException(sprintf(
          'Unexpected non-JSON response (status %d): %s',
          $statusCode,
          substr($body, 0, 200),
        ));
      }
      /** @var array<string, mixed> $decoded */
      $decoded = $raw;
    }

    if ($statusCode >= 200 && $statusCode < 300) {
      if ($decoded === null) {
        return null;
      }
      if (array_key_exists('data', $decoded)) {
        /** @var array<string, mixed>|list<mixed>|null $data */
        $data = $decoded['data'];
        return $data;
      }
      return $decoded;
    }

    $errorCode = null;
    $errorMessage = sprintf('HTTP %d', $statusCode);
    $details = [];

    if ($decoded !== null && isset($decoded['error']) && is_array($decoded['error'])) {
      $error = $decoded['error'];
      if (isset($error['code']) && is_string($error['code'])) {
        $errorCode = $error['code'];
      }
      if (isset($error['message']) && is_string($error['message']) && $error['message'] !== '') {
        $errorMessage = $error['message'];
      }
      if (isset($error['details']) && is_array($error['details'])) {
        /** @var array<string, mixed> $details */
        $details = $error['details'];
      }
    }

    throw $this->buildApiException($statusCode, $errorMessage, $errorCode, $details);
  }

  /**
   * @param array<string, mixed> $details
   */
  private function buildApiException(int $statusCode, string $message, ?string $apiCode, array $details): ApiException {
    return match (true) {
      $statusCode === 401             => new AuthenticationException($message, $statusCode, $apiCode, $details),
      $statusCode === 404             => new NotFoundException($message, $statusCode, $apiCode, $details),
      $statusCode === 400,
      $statusCode === 422             => new ValidationException($message, $statusCode, $apiCode, $details),
      $statusCode === 409             => new ConflictException($message, $statusCode, $apiCode, $details),
      $statusCode === 429             => new RateLimitException($message, $statusCode, $apiCode, $details),
      $statusCode >= 500              => new ServerException($message, $statusCode, $apiCode, $details),
      default                         => new ApiException($message, $statusCode, $apiCode, $details),
    };
  }

}
