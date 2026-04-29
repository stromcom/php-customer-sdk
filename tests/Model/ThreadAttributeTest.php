<?php
declare(strict_types=1);

namespace Stromcom\Sdk\Tests\Model;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Stromcom\Sdk\Model\ThreadAttribute;

final class ThreadAttributeTest extends TestCase {

  #[Test]
  public function hydrates_key_value_and_type(): void {
    $attr = ThreadAttribute::fromArray(['key' => 'status', 'value' => 'paid', 'type' => 1]);

    $this->assertSame('status', $attr->key);
    $this->assertSame('paid', $attr->value);
    $this->assertSame(1, $attr->type);
  }

  #[Test]
  public function type_is_nullable_when_omitted(): void {
    $attr = ThreadAttribute::fromArray(['key' => 'status', 'value' => 'paid']);

    $this->assertNull($attr->type);
  }

  #[Test]
  public function throws_when_key_is_missing(): void {
    $this->expectException(\UnexpectedValueException::class);
    ThreadAttribute::fromArray(['value' => 'paid']);
  }

  #[Test]
  public function throws_when_value_is_missing(): void {
    $this->expectException(\UnexpectedValueException::class);
    ThreadAttribute::fromArray(['key' => 'status']);
  }

}
