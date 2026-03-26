<?php

declare(strict_types=1);

namespace TeamMatePro\DoctrineUtilsBundle\Tests\Trait;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TeamMatePro\DoctrineUtilsBundle\Tests\Stubs\UuidIdStub;

final class UuidIdTraitTest extends TestCase
{
    #[Test]
    public function getIdReturnsUuidString(): void
    {
        $stub = new UuidIdStub();

        self::assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $stub->getId()
        );
    }

    #[Test]
    public function eachInstanceHasUniqueId(): void
    {
        $stub1 = new UuidIdStub();
        $stub2 = new UuidIdStub();

        self::assertNotSame($stub1->getId(), $stub2->getId());
    }
}
