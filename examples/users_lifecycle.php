<?php
declare(strict_types=1);

/**
 * Full user lifecycle: create → fetch → update → delete → restore.
 *
 * Demonstrates how the typed models pair up:
 *   - users()->create()  returns UserCreateResult { user, account }
 *   - users()->get()     returns User
 *   - users()->update()  returns User
 *   - users()->restore() returns User
 *   - users()->delete()  returns void (idempotent soft-delete)
 *
 * Run with:
 *   STROMCOM_TOKEN=… php examples/users_lifecycle.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Stromcom\Sdk\Client;
use Stromcom\Sdk\Exception\ConflictException;

$token = getenv('STROMCOM_TOKEN') ?: '';
if ($token === '') {
  fwrite(STDERR, "Set STROMCOM_TOKEN.\n"); exit(1);
}

$stromcom = new Client($token);
$code = 'sdk-example-user-' . bin2hex(random_bytes(3));

try {
  $created = $stromcom->users()->create([
    'code'         => $code,
    'name'         => 'Example User',
    'emailAddress' => 'example@acme.test',
  ]);
} catch (ConflictException $e) {
  fwrite(STDERR, "User code already exists: {$e->getMessage()}\n"); exit(2);
}

printf("Created   : hash=%s code=%s\n", $created->user->hash, $created->user->code);
printf("Account   : state=%s readOnly=%s\n",
  $created->account->state ?? '-',
  var_export($created->account->readOnly, true),
);

$hash = $created->user->hash;

$fetched = $stromcom->users()->get($hash);
printf("Fetched   : %s <%s>\n", $fetched->name, $fetched->emailAddress ?? '');

$renamed = $stromcom->users()->update($hash, ['name' => 'Example User (renamed)']);
printf("Renamed   : %s\n", $renamed->name);

$stromcom->users()->delete($hash);
echo "Deleted   : (soft) — user is now hidden from default list\n";

$restored = $stromcom->users()->restore($hash);
printf("Restored  : %s\n", $restored->name);

// Cleanup so re-runs don't pile up users.
$stromcom->users()->delete($hash);

// Filtered listing — see what state filters do.
$activeCount  = count($stromcom->users()->list(state: 'active'));
$deletedCount = count($stromcom->users()->list(state: 'deleted'));
printf("\nActive users: %d   Deleted users: %d\n", $activeCount, $deletedCount);
