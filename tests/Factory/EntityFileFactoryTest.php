<?php

declare(strict_types=1);

namespace TeamMatePro\DoctrineUtilsBundle\Tests\Factory;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TeamMatePro\Contracts\Model\FileInterface;
use TeamMatePro\DoctrineUtilsBundle\Entity\File;
use TeamMatePro\DoctrineUtilsBundle\Factory\EntityFileFactory;

final class EntityFileFactoryTest extends TestCase
{
    private EntityFileFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new EntityFileFactory();
    }

    #[Test]
    public function itCreatesFileEntityFromInterface(): void
    {
        $sourceFile = $this->createMock(FileInterface::class);
        $sourceFile->method('getName')->willReturn('document.pdf');
        $sourceFile->method('getMime')->willReturn('application/pdf');
        $sourceFile->method('getBytes')->willReturn(12345);
        $sourceFile->method('getRealPath')->willReturn('/tmp/document.pdf');
        $sourceFile->method('getFileUrl')->willReturn('https://cdn.example.com/document.pdf');

        $result = $this->factory->createFromInterface($sourceFile);

        self::assertInstanceOf(File::class, $result);
        self::assertSame('document.pdf', $result->getName());
        self::assertSame('application/pdf', $result->getMime());
        self::assertSame(12345, $result->getBytes());
        self::assertSame('/tmp/document.pdf', $result->getRealPath());
        self::assertSame('https://cdn.example.com/document.pdf', $result->getFileUrl());
    }

    #[Test]
    public function itGeneratesNewUuidForCreatedEntity(): void
    {
        $sourceFile = $this->createMock(FileInterface::class);
        $sourceFile->method('getId')->willReturn('original-id');
        $sourceFile->method('getName')->willReturn('test.txt');
        $sourceFile->method('getMime')->willReturn('text/plain');
        $sourceFile->method('getBytes')->willReturn(100);
        $sourceFile->method('getRealPath')->willReturn('/tmp/test.txt');
        $sourceFile->method('getFileUrl')->willReturn(null);

        $result = $this->factory->createFromInterface($sourceFile);

        self::assertNotSame('original-id', $result->getId());
        self::assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $result->getId()
        );
    }

    #[Test]
    public function itHandlesNullName(): void
    {
        $sourceFile = $this->createMock(FileInterface::class);
        $sourceFile->method('getName')->willReturn(null);
        $sourceFile->method('getMime')->willReturn('application/octet-stream');
        $sourceFile->method('getBytes')->willReturn(0);
        $sourceFile->method('getRealPath')->willReturn('/tmp/unknown');
        $sourceFile->method('getFileUrl')->willReturn(null);

        $result = $this->factory->createFromInterface($sourceFile);

        self::assertSame('Unknown', $result->getName());
    }

    #[Test]
    public function itHandlesNullFileUrl(): void
    {
        $sourceFile = $this->createMock(FileInterface::class);
        $sourceFile->method('getName')->willReturn('test.txt');
        $sourceFile->method('getMime')->willReturn('text/plain');
        $sourceFile->method('getBytes')->willReturn(100);
        $sourceFile->method('getRealPath')->willReturn('/tmp/test.txt');
        $sourceFile->method('getFileUrl')->willReturn(null);

        $result = $this->factory->createFromInterface($sourceFile);

        self::assertNull($result->getFileUrl());
    }

    #[Test]
    public function itSetsCreatedAtOnNewEntity(): void
    {
        $sourceFile = $this->createMock(FileInterface::class);
        $sourceFile->method('getName')->willReturn('test.txt');
        $sourceFile->method('getMime')->willReturn('text/plain');
        $sourceFile->method('getBytes')->willReturn(100);
        $sourceFile->method('getRealPath')->willReturn('/tmp/test.txt');
        $sourceFile->method('getFileUrl')->willReturn(null);
        $sourceFile->method('getCreatedAt')->willReturn(new DateTimeImmutable('2020-01-01'));

        $before = new DateTimeImmutable();
        $result = $this->factory->createFromInterface($sourceFile);
        $after = new DateTimeImmutable();

        self::assertGreaterThanOrEqual($before, $result->getCreatedAt());
        self::assertLessThanOrEqual($after, $result->getCreatedAt());
    }
}
