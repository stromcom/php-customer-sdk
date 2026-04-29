<?php
declare(strict_types=1);

namespace Stromcom\Sdk\Tests\Model;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Stromcom\Sdk\Model\Project;

final class ProjectTest extends TestCase {

  #[Test]
  public function hydrates_full_payload(): void {
    $project = Project::fromArray([
      'name'                       => 'Acme',
      'state'                      => 'active',
      'subscriptionState'          => 'paying',
      'customCSSPath'              => '//cdn.example/style.css',
      'allowAPI'                   => true,
      'allowThreadSubscribe'       => true,
      'allowThreadAlert'           => false,
      'allowThreadAttachment'      => true,
      'allowThreadInlineImages'    => true,
      'allowMessageFullDelete'     => false,
      'allowMessageDeleteIn'       => 120,
      'attachmentMaxSize'          => 25,
      'allowUserAutoCreate'        => true,
      'allowUserImmediatelyCreate' => false,
      'allowUserAutoUpdate'        => true,
      'isActive'                   => true,
      'isDeleted'                  => false,
      'createdAt'                  => 1_700_000_000,
    ]);

    $this->assertSame('Acme', $project->name);
    $this->assertSame('active', $project->state);
    $this->assertSame('paying', $project->subscriptionState);
    $this->assertSame('//cdn.example/style.css', $project->customCSSPath);
    $this->assertTrue($project->allowAPI);
    $this->assertFalse($project->allowThreadAlert);
    $this->assertSame(120, $project->allowMessageDeleteIn);
    $this->assertSame(25, $project->attachmentMaxSize);
    $this->assertSame(1_700_000_000, $project->createdAt);
  }

  #[Test]
  public function leaves_optional_fields_null_when_missing(): void {
    $project = Project::fromArray(['name' => 'Acme']);

    $this->assertSame('Acme', $project->name);
    $this->assertNull($project->state);
    $this->assertNull($project->subscriptionState);
    $this->assertNull($project->customCSSPath);
    $this->assertNull($project->allowAPI);
    $this->assertNull($project->createdAt);
    $this->assertNull($project->isActive);
  }

  #[Test]
  public function ignores_unknown_keys_for_forward_compatibility(): void {
    $project = Project::fromArray([
      'name'                  => 'Acme',
      'someFieldFromTheFuture' => ['nested' => true],
      'anotherUnknown'         => 42,
    ]);

    $this->assertSame('Acme', $project->name);
  }

  #[Test]
  public function coerces_integer_typed_floats_to_int(): void {
    $project = Project::fromArray([
      'name'              => 'Acme',
      'attachmentMaxSize' => 25.0,
    ]);

    $this->assertSame(25, $project->attachmentMaxSize);
  }

  #[Test]
  public function rejects_non_integer_floats(): void {
    $project = Project::fromArray([
      'name'              => 'Acme',
      'attachmentMaxSize' => 25.5,
    ]);

    $this->assertNull($project->attachmentMaxSize);
  }

  #[Test]
  public function throws_when_name_is_missing(): void {
    $this->expectException(\UnexpectedValueException::class);
    Project::fromArray(['state' => 'active']);
  }

}
