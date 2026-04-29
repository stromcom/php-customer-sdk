<?php
declare(strict_types=1);

/**
 * Stromcom Customer SDK — quickstart.
 *
 * The smallest possible round-trip: print the project bound to your
 * token and the active users in it. Read this first, then move on to
 * the focused examples in this directory.
 *
 * Run with:
 *   STROMCOM_TOKEN=… php examples/quickstart.php
 *
 * Optional:
 *   STROMCOM_BASE_URL=http://localhost:7000/api/customer/v1/ php examples/quickstart.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Stromcom\Sdk\Client;
use Stromcom\Sdk\Configuration;
use Stromcom\Sdk\Exception\ApiException;
use Stromcom\Sdk\Exception\TransportException;

$token = getenv('STROMCOM_TOKEN');
if (!is_string($token) || $token === '') {
  fwrite(STDERR, "Set STROMCOM_TOKEN to run this example.\n");
  exit(1);
}

$baseUrl = getenv('STROMCOM_BASE_URL');
$config  = (is_string($baseUrl) && $baseUrl !== '')
  ? new Configuration(token: $token, baseUrl: $baseUrl)
  : new Configuration(token: $token);

$stromcom = new Client($config);

try {
  $project = $stromcom->project()->get();
  printf("Project: %s (state=%s)\n", $project->name, $project->state ?? '-');

  $users = $stromcom->users()->list(state: 'active');
  printf("Active users: %d\n", count($users));
  foreach ($users as $user) {
    printf("  - %s | %s <%s>\n", $user->code, $user->name, $user->emailAddress ?? '');
  }
} catch (ApiException $e) {
  fwrite(STDERR, sprintf(
    "API error %d (%s): %s\n",
    $e->getStatusCode(),
    $e->getApiCode() ?? 'unknown',
    $e->getMessage(),
  ));
  exit(2);
} catch (TransportException $e) {
  fwrite(STDERR, 'Network error: ' . $e->getMessage() . "\n");
  exit(3);
}
