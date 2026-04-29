<?php
declare(strict_types=1);

/**
 * Read project settings, then flip a couple of policy flags.
 *
 * The PATCH /project/ endpoint only accepts a documented whitelist of
 * keys; any other key is silently ignored by the API. Send only what
 * you actually want to change.
 *
 * Run with:
 *   STROMCOM_TOKEN=… php examples/project_settings.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Stromcom\Sdk\Client;

$token = getenv('STROMCOM_TOKEN') ?: '';
if ($token === '') {
  fwrite(STDERR, "Set STROMCOM_TOKEN.\n"); exit(1);
}

$stromcom = new Client($token);

$project = $stromcom->project()->get();

echo "Current project settings:\n";
echo "  name                        : {$project->name}\n";
echo '  state                       : ' . ($project->state ?? '-') . "\n";
echo '  allowAPI                    : ' . var_export($project->allowAPI, true) . "\n";
echo '  allowThreadAttachment       : ' . var_export($project->allowThreadAttachment, true) . "\n";
echo '  allowThreadInlineImages     : ' . var_export($project->allowThreadInlineImages, true) . "\n";
echo '  allowMessageDeleteIn        : ' . var_export($project->allowMessageDeleteIn, true) . "s\n";
echo '  attachmentMaxSize           : ' . var_export($project->attachmentMaxSize, true) . " MB\n";

// Toggle two flags. PATCH returns the freshly updated Project model.
$updated = $stromcom->project()->update([
  'allowThreadInlineImages' => true,
  'allowMessageDeleteIn'    => 300,
]);

echo "\nAfter update:\n";
echo '  allowThreadInlineImages : ' . var_export($updated->allowThreadInlineImages, true) . "\n";
echo "  allowMessageDeleteIn    : {$updated->allowMessageDeleteIn}s\n";
