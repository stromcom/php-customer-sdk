<?php
declare(strict_types=1);

namespace Stromcom\Sdk\Exception;

/** 5xx — server-side failure. Usually safe to retry with backoff. */
final class ServerException extends ApiException {
}
