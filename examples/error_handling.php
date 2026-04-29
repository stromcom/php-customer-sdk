<?php
declare(strict_types=1);

/**
 * Demonstrates the typed exception hierarchy.
 *
 * Catch specifically what you care about (e.g. ConflictException for
 * "code already exists") and let everything else bubble — or catch
 * `ApiException` as a wide net and inspect `getStatusCode()`.
 *
 *   StromcomException
 *     ├─ TransportException    network failure / non-JSON response
 *     └─ ApiException          base for any HTTP-level error
 *          ├─ AuthenticationException   401
 *          ├─ ValidationException       400 / 422
 *          ├─ NotFoundException         404
 *          ├─ ConflictException         409
 *          ├─ RateLimitException        429
 *          └─ ServerException           5xx
 *
 * Run with:
 *   STROMCOM_TOKEN=… php examples/error_handling.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Stromcom\Sdk\Client;
use Stromcom\Sdk\Exception\ApiException;
use Stromcom\Sdk\Exception\AuthenticationException;
use Stromcom\Sdk\Exception\ConflictException;
use Stromcom\Sdk\Exception\NotFoundException;
use Stromcom\Sdk\Exception\RateLimitException;
use Stromcom\Sdk\Exception\TransportException;
use Stromcom\Sdk\Exception\ValidationException;

$token = getenv('STROMCOM_TOKEN') ?: '';
if ($token === '') {
  fwrite(STDERR, "Set STROMCOM_TOKEN.\n"); exit(1);
}

$stromcom = new Client($token);

// 1) NotFoundException — typical case for a missing thread/user.
try {
  $stromcom->users()->get('does-not-exist-1234567890');
} catch (NotFoundException $e) {
  echo "Not found: {$e->getMessage()} (api code: " . ($e->getApiCode() ?? '?') . ")\n";
}

// 2) ValidationException — server rejects a missing required field.
try {
  $stromcom->users()->create(['code' => '']);
} catch (ValidationException $e) {
  echo "Validation failed: {$e->getMessage()}\n";
  print_r($e->getDetails());
}

// 3) ConflictException — a duplicate code.
try {
  $code = 'duplicate-' . bin2hex(random_bytes(2));
  $stromcom->users()->create(['code' => $code, 'name' => 'A']);
  $stromcom->users()->create(['code' => $code, 'name' => 'B']);  // 409
} catch (ConflictException $e) {
  echo "Conflict: {$e->getMessage()}\n";
}

// 4) Catch-all — handle anything else generically.
try {
  $stromcom->project()->get();
} catch (AuthenticationException $e) {
  fwrite(STDERR, "Bad token.\n");
} catch (RateLimitException $e) {
  fwrite(STDERR, "Slow down — try again in a bit.\n");
} catch (ApiException $e) {
  fwrite(STDERR, "API error {$e->getStatusCode()}: {$e->getMessage()}\n");
} catch (TransportException $e) {
  fwrite(STDERR, "Network problem: {$e->getMessage()}\n");
}
