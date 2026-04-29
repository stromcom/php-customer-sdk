<?php
declare(strict_types=1);

/**
 * Plug a custom HTTP transport into the SDK.
 *
 * The SDK does not depend on PSR-18 directly — instead it ships its
 * own minimal `HttpClientInterface`. Anything that implements
 * `request(string $method, string $url, array $headers, ?string $body)
 * : RawResponse` works.
 *
 * Below: a thin wrapper that logs every request before delegating to
 * the default cURL transport. Useful for diagnostics, retries,
 * caching, or wiring up a PSR-18 client behind the scenes.
 *
 * Run with:
 *   STROMCOM_TOKEN=… php examples/custom_http_client.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Stromcom\Sdk\Client;
use Stromcom\Sdk\Configuration;
use Stromcom\Sdk\Http\CurlHttpClient;
use Stromcom\Sdk\Http\HttpClientInterface;
use Stromcom\Sdk\Http\RawResponse;

final class LoggingHttpClient implements HttpClientInterface {

  public function __construct(
    private readonly HttpClientInterface $inner,
  ) {
  }

  public function request(string $method, string $url, array $headers = [], ?string $body = null): RawResponse {
    $started  = microtime(true);
    $response = $this->inner->request($method, $url, $headers, $body);
    $tookMs   = (int) ((microtime(true) - $started) * 1000);

    fprintf(STDERR, "[http] %s %s -> %d (%d ms)\n", $method, $url, $response->statusCode, $tookMs);

    return $response;
  }

}

$token = getenv('STROMCOM_TOKEN') ?: '';
if ($token === '') {
  fwrite(STDERR, "Set STROMCOM_TOKEN.\n"); exit(1);
}

$config = new Configuration(token: $token);
$http   = new LoggingHttpClient(new CurlHttpClient($config->timeout));

$stromcom = new Client($config, $http);

$project = $stromcom->project()->get();
echo "Project: {$project->name}\n";

$users = $stromcom->users()->list(state: 'active');
printf("Active users: %d\n", count($users));
