<?php
declare(strict_types=1);

/**
 * Create a thread (upsert by code) with an initial message, then update the
 * thread name and send a follow-up message.
 *
 *   threads()->create($payload)
 *     → ['thread' => Thread, 'message' => ?Message, 'attachmentsUploadURL' => list<string>]
 *   threads()->update($code, $changes)
 *     → Thread
 *   messages()->create($threadCode, $payload)
 *     → ['message' => Message, 'attachmentsUploadURL' => list<string>]
 *
 * Requests are not bound to a logged-in user — whenever a message or
 * attachments are present, the author must be identified via `user_hash`
 * or `user_code`.
 *
 * Run with:
 *   STROMCOM_TOKEN=… STROMCOM_THREAD=order-1234 STROMCOM_USER_CODE=employee-42 \
 *     php examples/create_thread_and_message.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Stromcom\Sdk\Client;

$token = getenv('STROMCOM_TOKEN') ?: '';
$threadCode = getenv('STROMCOM_THREAD') ?: '';
$userCode = getenv('STROMCOM_USER_CODE') ?: '';
if ($token === '' || $threadCode === '' || $userCode === '') {
  fwrite(STDERR, "Set STROMCOM_TOKEN, STROMCOM_THREAD and STROMCOM_USER_CODE.\n"); exit(1);
}

$stromcom = new Client($token);

$created = $stromcom->threads()->create([
  'code' => $threadCode,
  'name' => "Order {$threadCode}",
  'message' => '<p>Your order has shipped.</p>',
  'user_code' => $userCode,
]);
printf("Thread created/updated: %s (%s)\n", $created['thread']->name, $created['thread']->hash);
if ($created['message'] !== null) {
  printf("Initial message: %s\n", $created['message']->hash);
}

$updated = $stromcom->threads()->update($threadCode, ['name' => "Order {$threadCode} (shipped)"]);
printf("Thread renamed to: %s\n", $updated->name);

$sent = $stromcom->messages()->create($threadCode, [
  'message' => '<p>Tracking number: 1Z999AA10123456784</p>',
  'user_code' => $userCode,
]);
printf("Follow-up message: %s\n", $sent['message']->hash);
