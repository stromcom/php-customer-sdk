<?php
declare(strict_types=1);

namespace Stromcom\Sdk\Exception;

/** 409 — conflict (duplicate code, limit reached, illegal state transition…). */
final class ConflictException extends ApiException {
}
