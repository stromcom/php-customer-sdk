# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.1.0] - 2026-10-16


### Added
- Initial release of the STROMCOM Customer SDK for PHP.
- `Client` covering Project, Users, Threads, Messages and Attachment endpoints.
- Pluggable `HttpClientInterface` with built-in `CurlHttpClient`.
- Typed exceptions: Authentication, NotFound, Validation, Conflict, RateLimit,
  Server, Transport, Api.
- Typed read models in `Stromcom\Sdk\Model\*` (`Project`, `User`,
  `UserAccount`, `UserCreateResult`, `Thread`, `ThreadAttribute`,
  `Message`, `MessageAttachment`, `MessageList`, `AttachmentDownload`)
  hydrated from API responses via `fromArray()` named constructors.
  All resource read methods now return these objects with
  `public readonly` properties instead of associative arrays.
- `ThreadResource::getWithMessages()` — single round-trip fetch of a
  thread with its messages and attachments, returned as typed objects.
- `examples/` — runnable, end-to-end demos for project settings, user
  lifecycle, threads + messages, message pagination, attachment
  download, error handling, and a custom HTTP client (`examples/README.md`).
- PHPUnit 11 test suite (78 tests, 210 assertions), PHPStan level 9,
  php-cs-fixer config.
- GitHub Actions CI for PHP 8.3 and 8.4.
- `MessageResource::reactions()` — list emoji reactions on a message,
  aggregated by code (`MessageReaction`, `MessageReactionUser` models).
- `ThreadResource::create()` — upsert a thread by code, optionally with an
  initial message and attachments.
- `ThreadResource::update()` — update thread `name` / `url` / `attributes`.
- `MessageResource::create()` — send a message (with attachments) in an
  existing thread.

### Fixed

- `CurlHttpClient` rejects an empty HTTP method or URL with `TransportException` instead of passing it to cURL.
- README: OpenAPI spec link now points to `/docs/customer/openapi.json` (was `swagger.json`).
- `examples/paginate_messages.php` rewritten as incremental sync — the `lastMessageHash` cursor is inclusive and the API has no page size.

### Documentation

- README: how the `lastMessageHash` cursor behaves (oldest-first, inclusive, no page size).
- README: 402 plan-limit and 403 `ACTION_FORBIDDEN` errors surface as `ApiException`; branch on `getApiCode()`.
- README: required token type (`customer` / `both`, server-side only) and where to create it.
- README: known limitation — response `meta` (warnings, `meta.updated`) is not exposed.
