<?php
declare(strict_types=1);

/**
 * Read a thread bound to your business entity, plus its messages and
 * attachments, in a single round-trip.
 *
 *   threads()->getWithMessages($code)
 *     → ['thread' => Thread, 'messages' => MessageList]
 *
 * `MessageList::attachmentsFor($messageHash)` returns the list of
 * `MessageAttachment` for that message (empty list when there are none),
 * so you can render attachments next to the message in O(1).
 *
 * Run with:
 *   STROMCOM_TOKEN=… STROMCOM_THREAD=order-1234 php examples/thread_with_messages.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Stromcom\Sdk\Client;
use Stromcom\Sdk\Exception\NotFoundException;

$token = getenv('STROMCOM_TOKEN') ?: '';
$threadCode = getenv('STROMCOM_THREAD') ?: '';
if ($token === '' || $threadCode === '') {
  fwrite(STDERR, "Set STROMCOM_TOKEN and STROMCOM_THREAD.\n"); exit(1);
}

$stromcom = new Client($token);

try {
  $result = $stromcom->threads()->getWithMessages($threadCode);
} catch (NotFoundException $e) {
  fwrite(STDERR, "Thread '{$threadCode}' not found.\n"); exit(2);
}

$thread   = $result['thread'];
$messages = $result['messages'];

printf("Thread: %s (%s)\n", $thread->name, $thread->code);
printf("URL   : %s\n", $thread->url ?? '-');
printf("Hash  : %s\n", $thread->hash);

if ($thread->attributes !== []) {
  echo "Attributes:\n";
  foreach ($thread->attributes as $attr) {
    printf("  %s = %s\n", $attr->key, $attr->value);
  }
}

printf("\nMessages (%d):\n", count($messages->messages));
foreach ($messages->messages as $msg) {
  printf("  [%s] %s — %s\n",
    date('Y-m-d H:i', $msg->createdAt ?? 0),
    $msg->userName ?? '?',
    strip_tags($msg->message),
  );
  foreach ($messages->attachmentsFor($msg->hash) as $att) {
    printf("      attachment: %s (%d B, state=%s)\n",
      $att->fileName,
      $att->fileSize ?? 0,
      $att->state ?? '-',
    );
  }
}
