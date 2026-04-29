<?php
declare(strict_types=1);

namespace Stromcom\Sdk\Model;

/**
 * Account-side view of a user — returned alongside `User` from
 * `POST /users/`. Carries the lifecycle/state fields that are not
 * exposed on the public `User` projection.
 */
final class UserAccount extends AbstractModel {

  public function __construct(
    public readonly string $hash,
    public readonly string $code,
    public readonly string $name,
    public readonly ?string $emailAddress,
    public readonly ?string $avatarURL,
    public readonly ?string $state,
    public readonly ?bool $readOnly,
    public readonly ?string $settingReadRule,
    public readonly ?int $createdAt,
  ) {
  }

  public static function fromArray(array $data): static {
    return new self(
      hash: self::requireString($data, 'hash'),
      code: self::requireString($data, 'code'),
      name: self::requireString($data, 'name'),
      emailAddress: self::readString($data, 'emailAddress'),
      avatarURL: self::readString($data, 'avatarURL'),
      state: self::readString($data, 'state'),
      readOnly: self::readBool($data, 'readOnly'),
      settingReadRule: self::readString($data, 'settingReadRule'),
      createdAt: self::readInt($data, 'createdAt'),
    );
  }

}
