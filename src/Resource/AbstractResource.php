<?php
declare(strict_types=1);

namespace Stromcom\Sdk\Resource;

use Stromcom\Sdk\Client;

abstract class AbstractResource {

  public function __construct(
    protected readonly Client $client,
  ) {
  }

}
