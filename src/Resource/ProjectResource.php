<?php
declare(strict_types=1);

namespace Stromcom\Sdk\Resource;

use Stromcom\Sdk\Model\Project;

/**
 * `/project/` — read and update the project bound to the current credential.
 */
final class ProjectResource extends AbstractResource {

  /**
   * GET /project/
   */
  public function get(): Project {
    /** @var array<string, mixed> $data */
    $data = $this->client->request('GET', 'project/') ?? [];
    return Project::fromArray($data);
  }

  /**
   * PATCH /project/ — partial update. Fields outside the subscription
   * whitelist are silently ignored by the API.
   *
   * @param array<string, mixed> $changes
   */
  public function update(array $changes): Project {
    /** @var array<string, mixed> $data */
    $data = $this->client->request('PATCH', 'project/', body: $changes) ?? [];
    return Project::fromArray($data);
  }

}
