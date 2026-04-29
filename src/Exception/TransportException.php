<?php
declare(strict_types=1);

namespace Stromcom\Sdk\Exception;

/**
 * Network-level failure (DNS, timeout, TLS) or non-JSON response — the request
 * never produced a usable API envelope.
 */
final class TransportException extends StromcomException {
}
