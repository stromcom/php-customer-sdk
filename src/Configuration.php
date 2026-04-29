<?php
declare(strict_types=1);

namespace Stromcom\Sdk;

final class Configuration {

  public const string DEFAULT_BASE_URL = 'https://www.stromcom.cz/api/customer/v1/';
  public const int DEFAULT_TIMEOUT = 10;
  public const string DEFAULT_USER_AGENT = 'stromcom-customer-sdk-php/1.0';

  public readonly string $token;
  public readonly string $baseUrl;
  public readonly int $timeout;
  public readonly string $userAgent;

  public function __construct(
    string $token,
    string $baseUrl = self::DEFAULT_BASE_URL,
    int $timeout = self::DEFAULT_TIMEOUT,
    string $userAgent = self::DEFAULT_USER_AGENT,
  ) {
    if ($token === '') {
      throw new \InvalidArgumentException('API token cannot be empty.');
    }
    if ($timeout < 1) {
      throw new \InvalidArgumentException('Timeout must be a positive integer (seconds).');
    }

    $this->token     = $token;
    $this->baseUrl   = rtrim($baseUrl, '/') . '/';
    $this->timeout   = $timeout;
    $this->userAgent = $userAgent;
  }

}
