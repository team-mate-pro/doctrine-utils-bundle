<?php

declare(strict_types=1);

namespace TeamMatePro\DoctrineUtilsBundle\Tests\Trait;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use TeamMatePro\DoctrineUtilsBundle\Tests\Stubs\AutoIncrementIdStub;

final class AutoIncrementIdTraitTest extends TestCase
{
    #[Test]
    public function getIdReturnsNaWhenNull(): void
    {
        $stub = new AutoIncrementIdStub();

        self::assertSame('N/A', $stub->getId());
    }

    #[Test]
    public function getIdReturnsStringRepresentationOfInt(): void
    {
        $stub = new AutoIncrementIdStub();

        $ref = new ReflectionProperty($stub, 'id');
        $ref->setValue($stub, 42);

        self::assertSame('42', $stub->getId());
    }
}
