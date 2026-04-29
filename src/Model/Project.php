<?php
declare(strict_types=1);

namespace Stromcom\Sdk\Model;

/**
 * The project bound to the current API token.
 *
 * Source schema: `Entity:Project` in the Customer API OpenAPI spec.
 * Boolean and numeric "allow*" fields mirror the project policy that
 * the Customer API enforces server-side; the SDK exposes them so that
 * customer code can branch on them (e.g. show/hide the attachment UI).
 */
final class Project extends AbstractModel {

  /**
   * @param string|null $state             `active`, `suspended`, … (server-defined enum).
   * @param string|null $subscriptionState Subscription state from billing layer.
   * @param int|null    $createdAt         Unix timestamp.
   */
  public function __construct(
    public readonly string $name,
    public readonly ?string $state,
    public readonly ?string $subscriptionState,
    public readonly ?string $customCSSPath,
    public readonly ?bool $allowAPI,
    public readonly ?bool $allowThreadSubscribe,
    public readonly ?bool $allowThreadAlert,
    public readonly ?bool $allowThreadAttachment,
    public readonly ?bool $allowThreadInlineImages,
    public readonly ?bool $allowMessageFullDelete,
    public readonly ?int $allowMessageDeleteIn,
    public readonly ?int $attachmentMaxSize,
    public readonly ?bool $allowUserAutoCreate,
    public readonly ?bool $allowUserImmediatelyCreate,
    public readonly ?bool $allowUserAutoUpdate,
    public readonly ?bool $isActive,
    public readonly ?bool $isDeleted,
    public readonly ?int $createdAt,
  ) {
  }

  public static function fromArray(array $data): static {
    return new self(
      name: self::requireString($data, 'name'),
      state: self::readString($data, 'state'),
      subscriptionState: self::readString($data, 'subscriptionState'),
      customCSSPath: self::readString($data, 'customCSSPath'),
      allowAPI: self::readBool($data, 'allowAPI'),
      allowThreadSubscribe: self::readBool($data, 'allowThreadSubscribe'),
      allowThreadAlert: self::readBool($data, 'allowThreadAlert'),
      allowThreadAttachment: self::readBool($data, 'allowThreadAttachment'),
      allowThreadInlineImages: self::readBool($data, 'allowThreadInlineImages'),
      allowMessageFullDelete: self::readBool($data, 'allowMessageFullDelete'),
      allowMessageDeleteIn: self::readInt($data, 'allowMessageDeleteIn'),
      attachmentMaxSize: self::readInt($data, 'attachmentMaxSize'),
      allowUserAutoCreate: self::readBool($data, 'allowUserAutoCreate'),
      allowUserImmediatelyCreate: self::readBool($data, 'allowUserImmediatelyCreate'),
      allowUserAutoUpdate: self::readBool($data, 'allowUserAutoUpdate'),
      isActive: self::readBool($data, 'isActive'),
      isDeleted: self::readBool($data, 'isDeleted'),
      createdAt: self::readInt($data, 'createdAt'),
    );
  }

}
