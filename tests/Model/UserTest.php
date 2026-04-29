<?php
declare(strict_types=1);

namespace Stromcom\Sdk\Tests\Model;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Stromcom\Sdk\Model\User;

final class UserTest extends TestCase {

  #[Test]
  public function hydrates_full_payload(): void {
    $user = User::fromArray([
      'hash'         => 'qAxL3POptX',
      'code'         => 'employee-1',
      'name'         => 'Jane Doe',
      'emailAddress' => 'jane@example.com',
      'avatarURL'    => 'https://gravatar.com/avatar/abc',
      'readOnly'     => false,
      'createdAt'    => 1_700_000_000,
      'deletedAt'    => null,
    ]);

    $this->assertSame('qAxL3POptX', $user->hash);
    $this->assertSame('employee-1', $user->code);
    $this->assertSame('Jane Doe', $user->name);
    $this->assertSame('jane@example.com', $user->emailAddress);
    $this->assertSame('https://gravatar.com/avatar/abc', $user->avatarURL);
    $this->assertFalse($user->readOnly);
    $this->assertSame(1_700_000_000, $user->createdAt);
    $this->assertNull($user->deletedAt);
  }

  #[Test]
  public function tolerates_missing_email_avatar_and_lifecycle_fields(): void {
    $user = User::fromArray([
      'hash' => 'qAxL3POptX',
      'code' => 'employee-1',
      'name' => 'Jane Doe',
    ]);

    $this->assertNull($user->emailAddress);
    $this->assertNull($user->avatarURL);
    $this->assertNull($user->readOnly);
    $this->assertNull($user->createdAt);
    $this->assertNull($user->deletedAt);
  }

  #[Test]
  public function throws_when_required_field_is_missing(): void {
    $this->expectException(\UnexpectedValueException::class);
    User::fromArray(['hash' => 'qAxL3POptX', 'name' => 'Jane Doe']);
  }

  #[Test]
  public function list_from_array_hydrates_each_row(): void {
    $users = User::listFromArray([
      ['hash' => 'h1', 'code' => 'e1', 'name' => 'Jane'],
      ['hash' => 'h2', 'code' => 'e2', 'name' => 'Joe'],
    ]);

    $this->assertCount(2, $users);
    $this->assertSame('Jane', $users[0]->name);
    $this->assertSame('Joe', $users[1]->name);
    $this->assertContainsOnlyInstancesOf(User::class, $users);
  }

  #[Test]
  public function list_from_array_handles_empty_input(): void {
    $this->assertSame([], User::listFromArray([]));
  }

}
