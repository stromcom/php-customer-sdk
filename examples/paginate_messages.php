<?php
declare(strict_types=1);

/**
 * Incremental sync of a thread's messages via the `lastMessageHash` cursor.
 *
 * How the cursor works on the Customer API:
 *  - messages come back oldest-first;
 *  - `lastMessageHash` is INCLUSIVE — the first returned item is the
 *    cursor message itself;
 *  - there is no page size — the server returns every message from the
 *    cursor onward (bounded only by your plan's history window).
 *
 * So there is nothing to "page through": fetch once without a cursor,
 * remember the hash of the newest message, and on the next sync fetch
 * from that hash and drop the first item (you already have it).
 *
 * Run with:
 *   STROMCOM_TOKEN=… STROMCOM_THREAD=order-1234 php examples/paginate_messages.php
 *   STROMCOM_TOKEN=… STROMCOM_THREAD=order-1234 STROMCOM_CURSOR=<hash> php examples/paginate_messages.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Stromcom\Sdk\Client;
use Stromcom\Sdk\Configuration;
use Stromcom\Sdk\Model\Message;

$token      = getenv('STROMCOM_TOKEN') ?: '';
$threadCode = getenv('STROMCOM_THREAD') ?: '';
$baseUrl    = getenv('STROMCOM_BASE_URL') ?: 'https://www.stromcom.cz/api/customer/v1/';
if ($token === '' || $threadCode === '') {
  fwrite(STDERR, "Set STROMCOM_TOKEN and STROMCOM_THREAD.\n"); exit(1);
}

$stromcom = new Client(new Configuration(token: $token, baseUrl: $baseUrl));

/**
 * Return messages newer than $cursor (all messages when $cursor is null).
 *
 * @return list<Message>
 */
function fetchNewMessages(Client $stromcom, string $threadCode, ?string $cursor): array {
  $messages = $stromcom->messages()->list($threadCode, lastMessageHash: $cursor)->messages;

  if ($cursor !== null && $messages !== [] && $messages[0]->hash === $cursor) {
    array_shift($messages);
  }

  return $messages;
}

// In a real integration the cursor lives in your database, per thread.
$cursor = getenv('STROMCOM_CURSOR') ?: null;

$newMessages = fetchNewMessages($stromcom, $threadCode, $cursor);

foreach ($newMessages as $message) {
  printf("%-12s %s\n", $message->hash, strip_tags($message->message));
}

if ($newMessages !== []) {
  $cursor = $newMessages[array_key_last($newMessages)]->hash;
}

printf("\nNew messages: %d\nNext cursor: %s\n", count($newMessages), $cursor ?? '(none)');
