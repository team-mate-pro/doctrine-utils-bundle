<?php

declare(strict_types=1);

namespace TeamMatePro\DoctrineUtilsBundle\Tests\Stubs;

use Symfony\Component\Uid\Uuid;
use TeamMatePro\DoctrineUtilsBundle\Trait\UuidIdTrait;

/**
 * Stub class for PHPStan analysis of UuidIdTrait.
 *
 * @internal
 */
final class UuidIdStub
{
    use UuidIdTrait;

    public function __construct()
    {
        $this->id = Uuid::v4();
    }
}
