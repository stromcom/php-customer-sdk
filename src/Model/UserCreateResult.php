<?php
declare(strict_types=1);

namespace Stromcom\Sdk\Model;

/**
 * Pair returned by `POST /users/` — the public user projection plus
 * the account-side projection. Unwrap whichever you need:
 *   `$result->user->hash` — for the public-facing identity
 *   `$result->account->state` — for the lifecycle state
 */
final class UserCreateResult extends AbstractModel {

  public function __construct(
    public readonly User $user,
    public readonly UserAccount $account,
  ) {
  }

  public static function fromArray(array $data): static {
    if (!isset($data['user']) || !is_array($data['user'])) {
      throw new \UnexpectedValueException('UserCreateResult: missing "user" object.');
    }
    if (!isset($data['account']) || !is_array($data['account'])) {
      throw new \UnexpectedValueException('UserCreateResult: missing "account" object.');
    }
    /** @var array<string, mixed> $userData */
    $userData = $data['user'];
    /** @var array<string, mixed> $accountData */
    $accountData = $data['account'];
    return new self(
      user: User::fromArray($userData),
      account: UserAccount::fromArray($accountData),
    );
  }

}
