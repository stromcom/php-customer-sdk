<?php
declare(strict_types=1);

namespace Stromcom\Sdk\Tests\Model;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Stromcom\Sdk\Model\AttachmentDownload;

final class AttachmentDownloadTest extends TestCase {

  #[Test]
  public function exposes_signed_url(): void {
    $download = AttachmentDownload::fromArray([
      'url' => 'https://signed.example/file.pdf?sig=abc',
    ]);

    $this->assertSame('https://signed.example/file.pdf?sig=abc', $download->url);
  }

  #[Test]
  public function throws_when_url_is_missing(): void {
    $this->expectException(\UnexpectedValueException::class);
    AttachmentDownload::fromArray([]);
  }

  #[Test]
  public function throws_when_url_is_not_a_string(): void {
    $this->expectException(\UnexpectedValueException::class);
    AttachmentDownload::fromArray(['url' => 42]);
  }

}
