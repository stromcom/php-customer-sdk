<?php
declare(strict_types=1);

namespace Stromcom\Sdk\Tests\Http;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Stromcom\Sdk\Exception\TransportException;
use Stromcom\Sdk\Http\CurlHttpClient;

final class CurlHttpClientTest extends TestCase {

  #[Test]
  #[TestWith(['', 'https://www.stromcom.cz/api/customer/v1/project/'])]
  #[TestWith(['GET', ''])]
  public function empty_method_or_url_is_rejected_before_curl(string $method, string $url): void {
    $this->expectException(TransportException::class);

    (new CurlHttpClient())->request($method, $url);
  }

}
