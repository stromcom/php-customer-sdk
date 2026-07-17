# STROMCOM Customer SDK for PHP

Official PHP SDK for the **STROMCOM Customer API** (`/api/customer/v1/`).

STROMCOM is a B2B internal-communication tool — your employees communicate
in threads and messages bound to your business entities (orders, tickets,
deals). This SDK lets your backend talk to that API from PHP in two lines,
with **typed read models**, IDE autocomplete, and PHPStan-level-9 type
safety.

```bash
composer require stromcom/customer-sdk
```

## API access & documentation

- **Reference docs:** <https://www.stromcom.cz/docs/customer/>
- **OpenAPI spec:** <https://www.stromcom.cz/docs/customer/swagger.json>

API access is gated by your STROMCOM subscription — only plans that
explicitly include API access can issue Customer API tokens and call
the endpoints below. If your token returns `401 Unauthorized` or your
project's `allowAPI` flag is `false`, check your current plan in the
STROMCOM console or contact support to upgrade.

## Quick start

```php
use Stromcom\Sdk\Client;

$stromcom = new Client('YOUR_CUSTOMER_API_TOKEN');

// Project (returns Stromcom\Sdk\Model\Project)
$project = $stromcom->project()->get();
echo $project->name;            // string
echo $project->allowAPI;        // bool|null
echo $project->createdAt;       // int|null  (unix timestamp)

// Users (returns list<Stromcom\Sdk\Model\User>)
$users = $stromcom->users()->list(state: 'active');
foreach ($users as $user) {
    echo $user->code, ' — ', $user->name, "\n";
}

// Create — returns UserCreateResult { user: User, account: UserAccount }
$created = $stromcom->users()->create([
    'code'         => 'employee-42',
    'name'         => 'Jane Doe',
    'emailAddress' => 'jane@acme.test',
]);
$hash = $created->user->hash;

$stromcom->users()->update($hash, ['name' => 'Jane Smith']);
$stromcom->users()->delete($hash);   // soft delete, idempotent
$stromcom->users()->restore($hash);

// Create a thread (upsert by code) with an optional initial message.
// The author must be identified via user_hash/user_code when a message/
// attachments are present — the request isn't bound to a logged-in user.
$created = $stromcom->threads()->create([
    'code'      => 'order-1234',
    'name'      => 'Order #1234',
    'message'   => '<p>Your order has shipped.</p>',
    'user_code' => 'employee-42',
]);
$thread = $created['thread'];    // Thread
$message = $created['message'];  // ?Message

$stromcom->threads()->update('order-1234', ['name' => 'Order #1234 (shipped)']);

// Send a follow-up message in the same thread
$stromcom->messages()->create('order-1234', [
    'message'   => '<p>Tracking number: 1Z999AA10123456784</p>',
    'user_code' => 'employee-42',
]);

// Thread + messages + attachments in one round-trip
$result   = $stromcom->threads()->getWithMessages('order-1234');
$thread   = $result['thread'];     // Thread
$messages = $result['messages'];   // MessageList

foreach ($messages->messages as $msg) {
    echo $msg->userName, ': ', strip_tags($msg->message), "\n";
    foreach ($messages->attachmentsFor($msg->hash) as $att) {
        echo '  📎 ', $att->fileName, "\n";
    }
}

// Signed download URL for a single attachment (short-lived)
$download = $stromcom->messages()->attachmentDownload('order-1234', 'msgHash', 'attHash');
file_put_contents('invoice.pdf', file_get_contents($download->url));
```

Read methods return typed objects from `Stromcom\Sdk\Model\*` with
`public readonly` properties. Write methods accept `array<string, mixed>`
payloads (the API validates them server-side). On non-2xx responses the
SDK throws a typed exception — see [Exceptions](#exceptions).

## Examples

The [`examples/`](examples/) directory holds runnable, end-to-end scripts
covering each area:

| File | What it shows |
|---|---|
| [`quickstart.php`](examples/quickstart.php) | Smallest possible round-trip. |
| [`project_settings.php`](examples/project_settings.php) | Read & PATCH project policy flags. |
| [`users_lifecycle.php`](examples/users_lifecycle.php) | Create → fetch → update → delete → restore. |
| [`thread_with_messages.php`](examples/thread_with_messages.php) | Thread + messages + attachments in one call. |
| [`create_thread_and_message.php`](examples/create_thread_and_message.php) | Create/upsert a thread with an initial message, update it, send a follow-up message. |
| [`paginate_messages.php`](examples/paginate_messages.php) | Cursor pagination via `lastMessageHash`. |
| [`download_attachment.php`](examples/download_attachment.php) | Resolve and save attachments to disk. |
| [`message_reactions.php`](examples/message_reactions.php) | List emoji reactions on a message, aggregated by code. |
| [`error_handling.php`](examples/error_handling.php) | Catch the typed exception hierarchy. |
| [`custom_http_client.php`](examples/custom_http_client.php) | Plug a custom `HttpClientInterface`. |

```bash
export STROMCOM_TOKEN="…"
php examples/quickstart.php
```

## Models

All read methods return objects from the `Stromcom\Sdk\Model` namespace.
Every property is `public readonly`. Models are tolerant on hydration:
unknown JSON keys are ignored (forward compatibility), missing optional
keys land as `null`.

| Class | Returned by |
|---|---|
| `Project` | `project()->get()`, `project()->update()` |
| `User` | `users()->list()`, `users()->get()`, `users()->update()`, `users()->restore()` |
| `UserAccount` | (inside `UserCreateResult`) |
| `UserCreateResult` | `users()->create()` — exposes `->user` and `->account` |
| `Thread`, `ThreadAttribute` | `threads()->get()`, `threads()->getWithMessages()` |
| `Message`, `MessageList` | `messages()->list()`, `threads()->getWithMessages()` |
| `MessageAttachment` | `messages()->attachments()`, `MessageList::attachmentsFor()` |
| `AttachmentDownload` | `messages()->attachmentDownload()` |
| `MessageReaction`, `MessageReactionUser` | `messages()->reactions()` |

## Method reference

```php
// Project
Project    $stromcom->project()->get();
Project    $stromcom->project()->update(array $changes);

// Users
list<User>        $stromcom->users()->list(?string $state = null, ?string $code = null);
User              $stromcom->users()->get(string $hash);
UserCreateResult  $stromcom->users()->create(array $payload);
User              $stromcom->users()->update(string $hash, array $changes);
void              $stromcom->users()->delete(string $hash);
User              $stromcom->users()->restore(string $hash);

// Threads
array{thread: Thread, message: ?Message, attachmentsUploadURL: list<string>}  $stromcom->threads()->create(array $payload);
Thread                                                $stromcom->threads()->update(string $code, array $changes);
Thread                                                $stromcom->threads()->get(string $code, array $expand = []);
array{thread: Thread, messages: MessageList}         $stromcom->threads()->getWithMessages(string $code);

// Messages
array{message: Message, attachmentsUploadURL: list<string>}  $stromcom->messages()->create(string $threadCode, array $payload);
MessageList              $stromcom->messages()->list(string $threadCode, ?string $lastMessageHash = null);
void                     $stromcom->messages()->delete(string $threadCode, string $messageHash);
list<MessageAttachment>  $stromcom->messages()->attachments(string $threadCode, string $messageHash);
AttachmentDownload       $stromcom->messages()->attachmentDownload(string $threadCode, string $messageHash, string $attachmentHash);
list<MessageReaction>    $stromcom->messages()->reactions(string $threadCode, string $messageHash);
```

## Configuration

```php
use Stromcom\Sdk\Client;
use Stromcom\Sdk\Configuration;

$client = new Client(
    new Configuration(
        token:     'YOUR_TOKEN',
        baseUrl:   'https://www.stromcom.cz/api/customer/v1/',  // default
        timeout:   10,                                          // seconds
        userAgent: 'stromcom/php-customer-sdk',
    ),
);
```

You can also pass your own HTTP client (anything implementing
`Stromcom\Sdk\Http\HttpClientInterface`) as the second argument — see
[`examples/custom_http_client.php`](examples/custom_http_client.php).

## Exceptions

All SDK exceptions extend `Stromcom\Sdk\Exception\StromcomException`.

| Exception | When |
|---|---|
| `AuthenticationException` | 401 — invalid / missing token |
| `NotFoundException`       | 404 — resource not found |
| `ValidationException`     | 400 / 422 — invalid input |
| `ConflictException`       | 409 — e.g. user code already exists, limit reached |
| `RateLimitException`      | 429 — slow down |
| `ServerException`         | 5xx |
| `TransportException`      | network failure / non-JSON response |
| `ApiException`            | catch-all base for any other API error |

Every API exception exposes:

```php
try {
    $stromcom->users()->create(['code' => '', 'name' => '']);
} catch (\Stromcom\Sdk\Exception\ValidationException $e) {
    $e->getMessage();      // human-readable error
    $e->getApiCode();      // e.g. "INVALID_INPUT"
    $e->getStatusCode();   // 400
    $e->getDetails();      // array<string, mixed> from API "details"
}
```

A worked example of catching every variant lives in
[`examples/error_handling.php`](examples/error_handling.php).

## Requirements

- PHP **8.3** or **8.4**
- ext-curl, ext-json

No other runtime dependencies.

## Development

```bash
composer install
composer ca       # phpstan + cs check
composer test     # phpunit
```

## License

MIT — see [LICENSE](LICENSE).
