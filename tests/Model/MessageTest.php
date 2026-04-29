<?php
declare(strict_types=1);

namespace Stromcom\Sdk\Tests\Model;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Stromcom\Sdk\Model\Message;

final class MessageTest extends TestCase {

  #[Test]
  public function hydrates_public_envelope_shape(): void {
    $message = Message::fromArray([
      'hash'      => 'eVO6SY5kf5',
      'message'   => '<p>Hello</p>',
      'user_hash' => 'qAxL3POptX',
      'userName'  => 'Jane',
      'createdAt' => 1_700_000_000,
    ]);

    $this->assertSame('eVO6SY5kf5', $message->hash);
    $this->assertSame('<p>Hello</p>', $message->message);
    $this->assertSame('qAxL3POptX', $message->userHash);
    $this->assertSame('Jane', $message->userName);
    $this->assertSame(1_700_000_000, $message->createdAt);
    $this->assertNull($message->messagePreview);
    $this->assertNull($message->state);
  }

  #[Test]
  public function hydrates_entity_envelope_shape(): void {
    $message = Message::fromArray([
      'hash'           => 'eVO6SY5kf5',
      'message'        => '<p>Hello</p>',
      'messagePreview' => "<span data-tag='p'>Hello</span>",
      'thread_hash'    => 't-1234',
      'user_hash'      => 'u-1234',
      'state'          => 'message',
    ]);

    $this->assertSame("<span data-tag='p'>Hello</span>", $message->messagePreview);
    $this->assertSame('t-1234', $message->threadHash);
    $this->assertSame('u-1234', $message->userHash);
    $this->assertSame('message', $message->state);
  }

  #[Test]
  public function accepts_camel_case_hash_aliases(): void {
    $message = Message::fromArray([
      'hash'       => 'eVO6SY5kf5',
      'message'    => '<p>Hi</p>',
      'userHash'   => 'u-1',
      'threadHash' => 't-1',
    ]);

    $this->assertSame('u-1', $message->userHash);
    $this->assertSame('t-1', $message->threadHash);
  }

  #[Test]
  public function snake_case_takes_precedence_over_camel_case(): void {
    $message = Message::fromArray([
      'hash'       => 'eVO6SY5kf5',
      'message'    => '<p>Hi</p>',
      'user_hash'  => 'snake',
      'userHash'   => 'camel',
    ]);

    $this->assertSame('snake', $message->userHash);
  }

  #[Test]
  public function throws_when_message_body_is_missing(): void {
    $this->expectException(\UnexpectedValueException::class);
    Message::fromArray(['hash' => 'eVO6SY5kf5']);
  }

  #[Test]
  public function list_from_array_hydrates_each_row(): void {
    $messages = Message::listFromArray([
      ['hash' => 'm1', 'message' => '<p>1</p>'],
      ['hash' => 'm2', 'message' => '<p>2</p>'],
    ]);

    $this->assertCount(2, $messages);
    $this->assertContainsOnlyInstancesOf(Message::class, $messages);
    $this->assertSame('m2', $messages[1]->hash);
  }

}
