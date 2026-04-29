<?php
declare(strict_types=1);

namespace Stromcom\Sdk\Tests\Model;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Stromcom\Sdk\Model\MessageAttachment;

final class MessageAttachmentTest extends TestCase {

  #[Test]
  public function hydrates_full_payload(): void {
    $attachment = MessageAttachment::fromArray([
      'hash'         => 'd972d36742e555d09501a01afadd17dd',
      'message_hash' => 'PgGt86YqZv',
      'state'        => 'ready',
      'fileName'     => 'invoice.pdf',
      'fileSize'     => 190_938,
      'createdAt'    => 1_700_000_000,
    ]);

    $this->assertSame('d972d36742e555d09501a01afadd17dd', $attachment->hash);
    $this->assertSame('PgGt86YqZv', $attachment->messageHash);
    $this->assertSame('ready', $attachment->state);
    $this->assertSame('invoice.pdf', $attachment->fileName);
    $this->assertSame(190_938, $attachment->fileSize);
    $this->assertSame(1_700_000_000, $attachment->createdAt);
  }

  #[Test]
  public function file_size_and_state_are_nullable(): void {
    $attachment = MessageAttachment::fromArray([
      'hash'         => 'a1',
      'message_hash' => 'm1',
      'fileName'     => 'doc.pdf',
    ]);

    $this->assertNull($attachment->state);
    $this->assertNull($attachment->fileSize);
    $this->assertNull($attachment->createdAt);
  }

  #[Test]
  public function throws_when_message_hash_is_missing(): void {
    $this->expectException(\UnexpectedValueException::class);
    MessageAttachment::fromArray(['hash' => 'a1', 'fileName' => 'x.pdf']);
  }

  #[Test]
  public function list_from_array_hydrates_each_row(): void {
    $list = MessageAttachment::listFromArray([
      ['hash' => 'a1', 'message_hash' => 'm1', 'fileName' => '1.pdf'],
      ['hash' => 'a2', 'message_hash' => 'm1', 'fileName' => '2.pdf'],
    ]);

    $this->assertCount(2, $list);
    $this->assertContainsOnlyInstancesOf(MessageAttachment::class, $list);
  }

}
