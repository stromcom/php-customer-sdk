<?php
declare(strict_types=1);

namespace Stromcom\Sdk\Resource;

use Stromcom\Sdk\Model\User;
use Stromcom\Sdk\Model\UserCreateResult;

/**
 * `/users/` — manage project users (employees who communicate inside threads).
 */
final class UserResource extends AbstractResource {

  /**
   * GET /users/
   *
   * @param string|null $state One of: `active`, `suspended`, `deleted`, `demo`, `all`. Default: `active`.
   * @param string|null $code  Exact code lookup. Takes precedence over `$state`.
   *
   * @return list<User>
   */
  public function list(?string $state = null, ?string $code = null): array {
    /** @var list<array<string, mixed>> $data */
    $data = $this->client->request('GET', 'users/', query: [
      'state' => $state,
      'code'  => $code,
    ]) ?? [];
    return User::listFromArray($data);
  }

  /**
   * GET /users/{hash}/
   */
  public function get(string $hash): User {
    /** @var array<string, mixed> $data */
    $data = $this->client->request('GET', "users/{$hash}/") ?? [];
    return User::fromArray($data);
  }

  /**
   * POST /users/ — create a project user. Required: `code`, `name`.
   *
   * @param array<string, mixed> $payload
   */
  public function create(array $payload): UserCreateResult {
    /** @var array<string, mixed> $data */
    $data = $this->client->request('POST', 'users/', body: $payload) ?? [];
    return UserCreateResult::fromArray($data);
  }

  /**
   * PATCH /users/{hash}/
   *
   * @param array<string, mixed> $changes
   */
  public function update(string $hash, array $changes): User {
    /** @var array<string, mixed> $data */
    $data = $this->client->request('PATCH', "users/{$hash}/", body: $changes) ?? [];
    return User::fromArray($data);
  }

  /**
   * DELETE /users/{hash}/ — soft delete. Idempotent. The API returns
   * a small operation envelope that is not interesting to typed
   * consumers; ignore the body.
   */
  public function delete(string $hash): void {
    $this->client->request('DELETE', "users/{$hash}/");
  }

  /**
   * POST /users/{hash}/restore/ — bring a deleted/suspended user back
   * to active and return the refreshed user projection.
   */
  public function restore(string $hash): User {
    /** @var array<string, mixed> $data */
    $data = $this->client->request('POST', "users/{$hash}/restore/") ?? [];
    if (isset($data['user']) && is_array($data['user'])) {
      /** @var array<string, mixed> $userData */
      $userData = $data['user'];
      return User::fromArray($userData);
    }
    return User::fromArray($data);
  }

}
