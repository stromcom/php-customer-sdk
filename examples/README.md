# Examples

Runnable scripts that demonstrate the SDK end-to-end. Every example
expects a valid Customer API token in the `STROMCOM_TOKEN` environment
variable; some examples need additional variables (the file header
documents which).

```bash
export STROMCOM_TOKEN="…"
# optional — point at a non-production instance
export STROMCOM_BASE_URL="http://localhost:7000/api/customer/v1/"
php examples/quickstart.php
```

| File | What it shows |
|---|---|
| [`quickstart.php`](quickstart.php) | Smallest possible round-trip — read the project and list users. |
| [`project_settings.php`](project_settings.php) | Read project settings, then PATCH a couple of policy flags. |
| [`users_lifecycle.php`](users_lifecycle.php) | Full user lifecycle: create → fetch → update → delete → restore. |
| [`thread_with_messages.php`](thread_with_messages.php) | Single-call fetch of a thread, its messages, and grouped attachments. |
| [`create_thread_and_message.php`](create_thread_and_message.php) | Create/upsert a thread with an initial message, update it, send a follow-up message. |
| [`paginate_messages.php`](paginate_messages.php) | Incremental sync of new messages via the inclusive `lastMessageHash` cursor. |
| [`download_attachment.php`](download_attachment.php) | Resolve attachment metadata, fetch a signed URL, save to disk. |
| [`message_reactions.php`](message_reactions.php) | List emoji reactions on a message, aggregated by code. |
| [`error_handling.php`](error_handling.php) | Catch the typed exception hierarchy (`NotFound`, `Validation`, …). |
| [`custom_http_client.php`](custom_http_client.php) | Plug a custom `HttpClientInterface` (logging wrapper here). |

All examples return typed read models from `Stromcom\Sdk\Model\*` —
e.g. `$project->name` instead of `$project['name']`. See the package
[`README.md`](../README.md) for the full type reference.
