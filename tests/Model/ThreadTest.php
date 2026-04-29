<?php
declare(strict_types=1);

namespace Stromcom\Sdk\Tests\Model;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Stromcom\Sdk\Model\Thread;
use Stromcom\Sdk\Model\ThreadAttribute;

final class ThreadTest extends TestCase {

  #[Test]
  public function hydrates_full_payload(): void {
    $thread = Thread::fromArray([
      'code'      => 'order-1234',
      'hash'      => 'eVO6SY5kf5',
      'name'      => 'Order 1234',
      'url'       => 'https://shop.example/order/1234/',
      'createdAt' => 1_700_000_000,
      'attributes' => [
        ['key' => 'status', 'value' => 'paid', 'type' => 1],
        ['key' => 'note',   'value' => 'rush', 'type' => 2],
      ],
    ]);

    $this->assertSame('order-1234', $thread->code);
    $this->assertSame('eVO6SY5kf5', $thread->hash);
    $this->assertSame('Order 1234', $thread->name);
    $this->assertSame('https://shop.example/order/1234/', $thread->url);
    $this->assertSame(1_700_000_000, $thread->createdAt);
    $this->assertCount(2, $thread->attributes);
    $this->assertContainsOnlyInstancesOf(ThreadAttribute::class, $thread->attributes);
    $this->assertSame('status', $thread->attributes[0]->key);
    $this->assertSame('rush', $thread->attributes[1]->value);
  }

  #[Test]
  public function url_is_nullable(): void {
    $thread = Thread::fromArray([
      'code' => 'order-1',
      'hash' => 'eVO6SY5kf5',
      'name' => 'Order',
    ]);

    $this->assertNull($thread->url);
  }

  #[Test]
  public function returns_empty_attribute_list_when_omitted(): void {
    $thread = Thread::fromArray([
      'code' => 'order-1',
      'hash' => 'eVO6SY5kf5',
      'name' => 'Order',
    ]);

    $this->assertSame([], $thread->attributes);
  }

  #[Test]
  public function skips_non_array_rows_in_attributes(): void {
    $thread = Thread::fromArray([
      'code' => 'order-1',
      'hash' => 'eVO6SY5kf5',
      'name' => 'Order',
      'attributes' => [
        ['key' => 'status', 'value' => 'paid', 'type' => 1],
        'malformed-row',
        null,
      ],
    ]);

    $this->assertCount(1, $thread->attributes);
    $this->assertSame('status', $thread->attributes[0]->key);
  }

}
