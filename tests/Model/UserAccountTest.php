<?php
declare(strict_types=1);

namespace Stromcom\Sdk\Tests\Model;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Stromcom\Sdk\Model\UserAccount;

final class UserAccountTest extends TestCase {

  #[Test]
  public function hydrates_account_projection(): void {
    $account = UserAccount::fromArray([
      'hash'             => 'a1',
      'code'             => 'employee-1',
      'name'             => 'Jane',
      'emailAddress'     => 'jane@example.com',
      'avatarURL'        => null,
      'state'            => 'active',
      'readOnly'         => false,
      'settingReadRule'  => 'onView',
      'createdAt'        => 1_700_000_000,
    ]);

    $this->assertSame('active', $account->state);
    $this->assertFalse($account->readOnly);
    $this->assertSame('onView', $account->settingReadRule);
    $this->assertSame(1_700_000_000, $account->createdAt);
  }

  #[Test]
  public function tolerates_missing_optional_fields(): void {
    $account = UserAccount::fromArray([
      'hash' => 'a1',
      'code' => 'employee-1',
      'name' => 'Jane',
    ]);

    $this->assertNull($account->state);
    $this->assertNull($account->settingReadRule);
    $this->assertNull($account->readOnly);
  }

}
