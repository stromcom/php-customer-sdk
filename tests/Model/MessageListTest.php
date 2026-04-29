<?php
declare(strict_types=1);

namespace Stromcom\Sdk\Tests\Model;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Stromcom\Sdk\Model\Message;
use Stromcom\Sdk\Model\MessageAttachment;
use Stromcom\Sdk\Model\MessageList;

final class MessageListTest extends TestCase {

  #[Test]
  public function hydrates_messages_and_grouped_attachments(): void {
    $list = MessageList::fromArray([
      'messages' => [
        ['hash' => 'm1', 'message' => '<p>1</p>'],
        ['hash' => 'm2', 'message' => '<p>2</p>'],
      ],
      'messagesAttachments' => [
        'm1' => [
          ['hash' => 'a1', 'message_hash' => 'm1', 'fileName' => 'doc.pdf'],
        ],
        'm2' => [
          ['hash' => 'a2', 'message_hash' => 'm2', 'fileName' => 'pic.png'],
          ['hash' => 'a3', 'message_hash' => 'm2', 'fileName' => 'note.txt'],
        ],
      ],
    ]);

    $this->assertCount(2, $list->messages);
    $this->assertContainsOnlyInstancesOf(Message::class, $list->messages);

    $this->assertCount(1, $list->attachmentsFor('m1'));
    $this->assertCount(2, $list->attachmentsFor('m2'));
    $this->assertContainsOnlyInstancesOf(MessageAttachment::class, $list->attachmentsFor('m2'));
    $this->assertSame('doc.pdf', $list->attachmentsFor('m1')[0]->fileName);
  }

  #[Test]
  public function attachments_for_unknown_message_is_empty(): void {
    $list = MessageList::fromArray([
      'messages'            => [['hash' => 'm1', 'message' => '<p>1</p>']],
      'messagesAttachments' => [],
    ]);

    $this->assertSame([], $list->attachmentsFor('not-there'));
  }

  #[Test]
  public function tolerates_missing_top_level_keys(): void {
    $list = MessageList::fromArray([]);

    $this->assertSame([], $list->messages);
    $this->assertSame([], $list->attachmentsByMessage);
  }

  #[Test]
  public function skips_malformed_attachment_groups(): void {
    $list = MessageList::fromArray([
      'messages' => [],
      'messagesAttachments' => [
        'm1' => [['hash' => 'a1', 'message_hash' => 'm1', 'fileName' => 'doc.pdf']],
        // non-array value is ignored
        'm2' => 'malformed',
        // non-string key is ignored
        7 => [['hash' => 'a2', 'message_hash' => 'm2', 'fileName' => 'x.pdf']],
      ],
    ]);

    $this->assertArrayHasKey('m1', $list->attachmentsByMessage);
    $this->assertArrayNotHasKey('m2', $list->attachmentsByMessage);
    $this->assertArrayNotHasKey(7, $list->attachmentsByMessage);
    $this->assertCount(1, $list->attachmentsByMessage);
  }

  #[Test]
  public function filters_non_array_attachment_rows(): void {
    $list = MessageList::fromArray([
      'messages' => [],
      'messagesAttachments' => [
        'm1' => [
          ['hash' => 'a1', 'message_hash' => 'm1', 'fileName' => 'ok.pdf'],
          'malformed-row',
          null,
        ],
      ],
    ]);

    $this->assertCount(1, $list->attachmentsFor('m1'));
    $this->assertSame('ok.pdf', $list->attachmentsFor('m1')[0]->fileName);
  }

}
