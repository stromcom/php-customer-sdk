<?php
declare(strict_types=1);

namespace Stromcom\Sdk\Tests\Model;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Stromcom\Sdk\Model\User;
use Stromcom\Sdk\Model\UserAccount;
use Stromcom\Sdk\Model\UserCreateResult;

final class UserCreateResultTest extends TestCase {

  #[Test]
  public function hydrates_user_and_account_from_pair(): void {
    $result = UserCreateResult::fromArray([
      'user' => [
        'hash' => 'h1',
        'code' => 'e1',
        'name' => 'Jane',
      ],
      'account' => [
        'hash'  => 'h1',
        'code'  => 'e1',
        'name'  => 'Jane',
        'state' => 'active',
      ],
    ]);

    $this->assertInstanceOf(User::class, $result->user);
    $this->assertInstanceOf(UserAccount::class, $result->account);
    $this->assertSame('h1', $result->user->hash);
    $this->assertSame('active', $result->account->state);
  }

  #[Test]
  public function throws_when_user_is_missing(): void {
    $this->expectException(\UnexpectedValueException::class);
    UserCreateResult::fromArray([
      'account' => ['hash' => 'h1', 'code' => 'e1', 'name' => 'Jane'],
    ]);
  }

  #[Test]
  public function throws_when_account_is_missing(): void {
    $this->expectException(\UnexpectedValueException::class);
    UserCreateResult::fromArray([
      'user' => ['hash' => 'h1', 'code' => 'e1', 'name' => 'Jane'],
    ]);
  }

  #[Test]
  public function throws_when_user_is_not_an_object(): void {
    $this->expectException(\UnexpectedValueException::class);
    UserCreateResult::fromArray([
      'user'    => 'not-an-object',
      'account' => ['hash' => 'h1', 'code' => 'e1', 'name' => 'Jane'],
    ]);
  }

}
