<?php
declare(strict_types=1);

namespace Stromcom\Sdk\Tests\Model;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Stromcom\Sdk\Model\MessageReaction;

final class MessageReactionTest extends TestCase {

  #[Test]
  public function hydrates_full_payload(): void {
    $reaction = MessageReaction::fromArray([
      'code' => '👍',
      'count' => 2,
      'currentUserReacted' => true,
      'users' => [
        ['hash' => 'u1', 'name' => 'Jane'],
        ['hash' => 'u2', 'name' => 'John'],
      ],
    ]);

    $this->assertSame('👍', $reaction->code);
    $this->assertSame(2, $reaction->count);
    $this->assertTrue($reaction->currentUserReacted);
    $this->assertCount(2, $reaction->users);
    $this->assertSame('u1', $reaction->users[0]->hash);
    $this->assertSame('Jane', $reaction->users[0]->name);
  }

  #[Test]
  public function count_and_current_user_reacted_default_when_missing(): void {
    $reaction = MessageReaction::fromArray(['code' => '👍']);

    $this->assertSame(0, $reaction->count);
    $this->assertFalse($reaction->currentUserReacted);
    $this->assertSame([], $reaction->users);
  }

  #[Test]
  public function throws_when_code_is_missing(): void {
    $this->expectException(\UnexpectedValueException::class);
    MessageReaction::fromArray(['count' => 1]);
  }

  #[Test]
  public function list_from_array_hydrates_each_row(): void {
    $list = MessageReaction::listFromArray([
      ['code' => '👍', 'count' => 1, 'currentUserReacted' => false, 'users' => []],
      ['code' => '🎉', 'count' => 3, 'currentUserReacted' => true, 'users' => []],
    ]);

    $this->assertCount(2, $list);
    $this->assertContainsOnlyInstancesOf(MessageReaction::class, $list);
  }

}
