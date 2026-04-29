<?php
declare(strict_types=1);

namespace Stromcom\Sdk\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Stromcom\Sdk\Configuration;

final class ConfigurationTest extends TestCase {

  #[Test]
  public function defaults_are_applied(): void {
    $config = new Configuration('tok');

    $this->assertSame('tok', $config->token);
    $this->assertSame(Configuration::DEFAULT_BASE_URL, $config->baseUrl);
    $this->assertSame(Configuration::DEFAULT_TIMEOUT, $config->timeout);
    $this->assertSame(Configuration::DEFAULT_USER_AGENT, $config->userAgent);
  }

  #[Test]
  public function base_url_is_normalised_with_trailing_slash(): void {
    $config = new Configuration('tok', baseUrl: 'https://example.test/api/customer/v1');

    $this->assertSame('https://example.test/api/customer/v1/', $config->baseUrl);
  }

  #[Test]
  public function empty_token_is_rejected(): void {
    $this->expectException(\InvalidArgumentException::class);
    new Configuration('');
  }

  #[Test]
  public function non_positive_timeout_is_rejected(): void {
    $this->expectException(\InvalidArgumentException::class);
    new Configuration('tok', timeout: 0);
  }

}
