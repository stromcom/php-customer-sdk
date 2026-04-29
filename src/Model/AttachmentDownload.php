<?php
declare(strict_types=1);

namespace Stromcom\Sdk\Model;

/**
 * Signed download payload returned by
 * `GET /threads/{code}/messages/{hash}/attachments/{hash}/download/`.
 *
 * The URL is short-lived; do not cache it.
 */
final class AttachmentDownload extends AbstractModel {

  public function __construct(
    public readonly string $url,
  ) {
  }

  public static function fromArray(array $data): static {
    return new self(
      url: self::requireString($data, 'url'),
    );
  }

}
