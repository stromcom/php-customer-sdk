<?php
declare(strict_types=1);

/**
 * Paginate through every message in a thread.
 *
 * The `lastMessageHash` query parameter is a cursor: pass the hash of
 * the last message you've already seen to get the next batch. The
 * Customer API returns messages in chronological order; the loop
 * below stops when an empty page comes back.
 *
 * Run with:
 *   STROMCOM_TOKEN=… STROMCOM_THREAD=order-1234 php examples/paginate_messages.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Stromcom\Sdk\Client;

$token      = getenv('STROMCOM_TOKEN') ?: '';
$threadCode = getenv('STROMCOM_THREAD') ?: '';
if ($token === '' || $threadCode === '') {
  fwrite(STDERR, "Set STROMCOM_TOKEN and STROMCOM_THREAD.\n"); exit(1);
}

$stromcom = new Client($token);

$cursor = null;
$total  = 0;

do {
  $page  = $stromcom->messages()->list($threadCode, lastMessageHash: $cursor);
  $count = count($page->messages);
  $total += $count;

  foreach ($page->messages as $msg) {
    printf("%-12s %s\n", $msg->hash, strip_tags($msg->message));
  }

  if ($count === 0) {
    break;
  }

  $cursor = $page->messages[$count - 1]->hash;

  // First-page-only single fetch when the API serves the whole thread
  // in one shot — guard against an infinite loop on small threads.
  if ($count < 50) {
    break;
  }
} while (true);

printf("\nTotal messages walked: %d\n", $total);
