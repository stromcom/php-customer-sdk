<?php
declare(strict_types=1);

/**
 * List the emoji reactions on a message, aggregated by emoji code.
 *
 *   messages()->reactions($threadCode, $messageHash)
 *     → list<MessageReaction>   (code, count, currentUserReacted, users)
 *
 * Read-only: reacting to a message is only available to end users via the
 * App API, not to the customer credential.
 *
 * Run with:
 *   STROMCOM_TOKEN=… STROMCOM_THREAD=order-1234 STROMCOM_MESSAGE=eVO6SY5kf5 \
 *     php examples/message_reactions.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Stromcom\Sdk\Client;

$token       = getenv('STROMCOM_TOKEN') ?: '';
$threadCode  = getenv('STROMCOM_THREAD') ?: '';
$messageHash = getenv('STROMCOM_MESSAGE') ?: '';
if ($token === '' || $threadCode === '' || $messageHash === '') {
  fwrite(STDERR, "Set STROMCOM_TOKEN, STROMCOM_THREAD and STROMCOM_MESSAGE.\n"); exit(1);
}

$stromcom  = new Client($token);
$reactions = $stromcom->messages()->reactions($threadCode, $messageHash);

if ($reactions === []) {
  echo "Message has no reactions.\n";
  exit(0);
}

foreach ($reactions as $reaction) {
  $userNames = implode(', ', array_map(static fn($user) => $user->name, $reaction->users));
  printf(
    "%s x%d%s — %s\n",
    $reaction->code,
    $reaction->count,
    $reaction->currentUserReacted ? ' (you)' : '',
    $userNames,
  );
}
