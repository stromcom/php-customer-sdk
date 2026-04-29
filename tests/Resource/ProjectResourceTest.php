<?php
declare(strict_types=1);

namespace Stromcom\Sdk\Tests\Resource;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Stromcom\Sdk\Client;
use Stromcom\Sdk\Configuration;
use Stromcom\Sdk\Tests\Http\MockHttpClient;

final class ProjectResourceTest extends TestCase {

  private MockHttpClient $http;
  private Client $client;

  protected function setUp(): void {
    $this->http = new MockHttpClient();
    $this->client = new Client(
      new Configuration(token: 'tok', baseUrl: 'https://api.example.test/customer/v1/'),
      $this->http,
    );
  }

  #[Test]
  public function get_returns_project_data(): void {
    $this->http->enqueueJson(200, [
      'status' => 'success',
      'data' => ['name' => 'Acme', 'state' => 'active'],
    ]);

    $project = $this->client->project()->get();

    $this->assertSame('Acme', $project->name);
    $this->assertSame('active', $project->state);
    $this->assertSame('GET', $this->http->lastCall()['method']);
  }

  #[Test]
  public function update_sends_patch_with_body(): void {
    $this->http->enqueueJson(200, [
      'status' => 'success',
      'data' => ['name' => 'Acme Inc.'],
      'meta' => ['updated' => ['name']],
    ]);

    $this->client->project()->update(['name' => 'Acme Inc.']);

    $call = $this->http->lastCall();
    $this->assertSame('PATCH', $call['method']);
    $this->assertSame('{"name":"Acme Inc."}', $call['body']);
  }

}
