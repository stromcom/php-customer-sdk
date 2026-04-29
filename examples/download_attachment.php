<?php
declare(strict_types=1);

/**
 * Download a message attachment to disk.
 *
 *   messages()->attachments($threadCode, $messageHash)
 *     → list<MessageAttachment>   (metadata: hash, fileName, size, state)
 *
 *   messages()->attachmentDownload($threadCode, $messageHash, $attachmentHash)
 *     → AttachmentDownload         (signed, short-lived URL — do not cache)
 *
 * Run with:
 *   STROMCOM_TOKEN=… STROMCOM_THREAD=order-1234 STROMCOM_MESSAGE=eVO6SY5kf5 \
 *     php examples/download_attachment.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Stromcom\Sdk\Client;

$token       = getenv('STROMCOM_TOKEN') ?: '';
$threadCode  = getenv('STROMCOM_THREAD') ?: '';
$messageHash = getenv('STROMCOM_MESSAGE') ?: '';
if ($token === '' || $threadCode === '' || $messageHash === '') {
  fwrite(STDERR, "Set STROMCOM_TOKEN, STROMCOM_THREAD and STROMCOM_MESSAGE.\n"); exit(1);
}

$stromcom    = new Client($token);
$attachments = $stromcom->messages()->attachments($threadCode, $messageHash);

if ($attachments === []) {
  echo "Message has no attachments.\n";
  exit(0);
}

$outDir = __DIR__ . '/../tmp-downloads';
if (!is_dir($outDir) && !mkdir($outDir, 0o755, true) && !is_dir($outDir)) {
  fwrite(STDERR, "Could not create {$outDir}.\n"); exit(2);
}

foreach ($attachments as $att) {
  printf("Fetching %s (%d bytes)…\n", $att->fileName, $att->fileSize ?? 0);

  $download = $stromcom->messages()->attachmentDownload($threadCode, $messageHash, $att->hash);

  $body = file_get_contents($download->url);
  if ($body === false) {
    fwrite(STDERR, "  download failed.\n"); continue;
  }

  $target = $outDir . '/' . $att->fileName;
  file_put_contents($target, $body);
  printf("  saved to %s\n", $target);
}
