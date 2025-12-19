<?php

declare(strict_types=1);

namespace TeamMatePro\DoctrineUtilsBundle\Tests\Entity;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use TeamMatePro\Contracts\Model\FileInterface;
use TeamMatePro\DoctrineUtilsBundle\Entity\File;

final class FileTest extends TestCase
{
    #[Test]
    public function itGeneratesUuidOnConstruction(): void
    {
        $file = $this->createFile();

        self::assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $file->getId()
        );
    }

    #[Test]
    public function itGeneratesUniqueIds(): void
    {
        $file1 = $this->createFile();
        $file2 = $this->createFile();

        self::assertNotSame($file1->getId(), $file2->getId());
    }

    #[Test]
    public function itSetsCreatedAtOnConstruction(): void
    {
        $before = new DateTimeImmutable();
        $file = $this->createFile();
        $after = new DateTimeImmutable();

        self::assertGreaterThanOrEqual($before, $file->getCreatedAt());
        self::assertLessThanOrEqual($after, $file->getCreatedAt());
    }

    #[Test]
    public function itStoresConstructorParameters(): void
    {
        $file = new File(
            name: 'document.pdf',
            mime: 'application/pdf',
            bytes: 12345,
            realPath: '/tmp/document.pdf',
            fileUrl: 'https://cdn.example.com/document.pdf',
        );

        self::assertSame('document.pdf', $file->getName());
        self::assertSame('application/pdf', $file->getMime());
        self::assertSame(12345, $file->getBytes());
        self::assertSame('/tmp/document.pdf', $file->getRealPath());
        self::assertSame('https://cdn.example.com/document.pdf', $file->getFileUrl());
    }

    #[Test]
    public function itDefaultsFileUrlToNull(): void
    {
        $file = new File(
            name: 'test.txt',
            mime: 'text/plain',
            bytes: 100,
            realPath: '/tmp/test.txt',
        );

        self::assertNull($file->getFileUrl());
    }

    #[Test]
    public function itAllowsSettingFileUrl(): void
    {
        $file = $this->createFile();
        $result = $file->setFileUrl('https://example.com/file.pdf');

        self::assertSame($file, $result);
        self::assertSame('https://example.com/file.pdf', $file->getFileUrl());
    }

    #[Test]
    public function itAllowsSettingFileUrlToNull(): void
    {
        $file = new File(
            name: 'test.txt',
            mime: 'text/plain',
            bytes: 100,
            realPath: '/tmp/test.txt',
            fileUrl: 'https://example.com/file.pdf',
        );
        $file->setFileUrl(null);

        self::assertNull($file->getFileUrl());
    }

    #[Test]
    public function itCreatesFromInterface(): void
    {
        $source = $this->createMock(FileInterface::class);
        $source->method('getName')->willReturn('source.pdf');
        $source->method('getMime')->willReturn('application/pdf');
        $source->method('getBytes')->willReturn(5000);
        $source->method('getRealPath')->willReturn('/tmp/source.pdf');
        $source->method('getFileUrl')->willReturn('https://example.com/source.pdf');

        $file = File::createFromInterface($source);

        self::assertSame('source.pdf', $file->getName());
        self::assertSame('application/pdf', $file->getMime());
        self::assertSame(5000, $file->getBytes());
        self::assertSame('/tmp/source.pdf', $file->getRealPath());
        self::assertSame('https://example.com/source.pdf', $file->getFileUrl());
    }

    #[Test]
    public function itCreatesFromInterfaceWithNullName(): void
    {
        $source = $this->createMock(FileInterface::class);
        $source->method('getName')->willReturn(null);
        $source->method('getMime')->willReturn('application/octet-stream');
        $source->method('getBytes')->willReturn(0);
        $source->method('getRealPath')->willReturn('/tmp/unknown');
        $source->method('getFileUrl')->willReturn(null);

        $file = File::createFromInterface($source);

        self::assertSame('Unknown', $file->getName());
    }

    #[Test]
    public function itCreatesFromUploadedFile(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        self::assertNotFalse($tempFile);
        file_put_contents($tempFile, 'test content');

        try {
            $uploadedFile = new UploadedFile(
                path: $tempFile,
                originalName: 'document.pdf',
                mimeType: 'application/pdf',
                test: true,
            );

            $file = File::createFromUploadedFile($uploadedFile);

            self::assertSame('document.pdf', $file->getName());
            self::assertSame('application/pdf', $file->getMime());
            self::assertSame(12, $file->getBytes());
            self::assertSame($tempFile, $file->getRealPath());
            self::assertNull($file->getFileUrl());
        } finally {
            @unlink($tempFile);
        }
    }

    /**
     * @return iterable<string, array{string, bool}>
     */
    public static function webImageMimeTypesProvider(): iterable
    {
        yield 'jpeg' => ['image/jpeg', true];
        yield 'png' => ['image/png', true];
        yield 'gif' => ['image/gif', true];
        yield 'webp' => ['image/webp', true];
        yield 'svg+xml' => ['image/svg+xml', true];
        yield 'pdf' => ['application/pdf', false];
        yield 'text' => ['text/plain', false];
        yield 'tiff' => ['image/tiff', false];
        yield 'bmp' => ['image/bmp', false];
    }

    #[Test]
    #[DataProvider('webImageMimeTypesProvider')]
    public function itDetectsWebImages(string $mimeType, bool $expected): void
    {
        $file = new File(
            name: 'image',
            mime: $mimeType,
            bytes: 1000,
            realPath: '/tmp/image',
        );

        self::assertSame($expected, $file->isWebImage());
    }

    #[Test]
    public function itCreatesFromBase64WithDataUri(): void
    {
        $base64 = $this->getTestImageBase64();
        $dataUri = 'data:image/png;base64,' . $base64;

        $file = File::fromBase64($dataUri);

        self::assertSame('image/png', $file->getMime());
        self::assertGreaterThan(0, $file->getBytes());
        self::assertFileExists($file->getRealPath());
        self::assertNotNull($file->getName());
        self::assertMatchesRegularExpression('/\.png$/', $file->getName());

        @unlink($file->getRealPath());
    }

    #[Test]
    public function itCreatesFromBase64WithoutDataUri(): void
    {
        $base64 = $this->getTestImageBase64();

        $file = File::fromBase64($base64);

        self::assertSame('image/png', $file->getMime());
        self::assertGreaterThan(0, $file->getBytes());
        self::assertFileExists($file->getRealPath());

        @unlink($file->getRealPath());
    }

    #[Test]
    public function itCreatesFromBase64WithCustomName(): void
    {
        $base64 = $this->getTestImageBase64();

        $file = File::fromBase64($base64, 'custom-image.png');

        self::assertSame('custom-image.png', $file->getName());
        self::assertSame('image/png', $file->getMime());

        @unlink($file->getRealPath());
    }

    #[Test]
    public function itThrowsExceptionForInvalidBase64(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid base64 string provided.');

        File::fromBase64('!!!invalid-base64!!!');
    }

    #[Test]
    public function itCalculatesCorrectBytesFromBase64(): void
    {
        $base64 = $this->getTestImageBase64();
        $expectedBytes = strlen(base64_decode($base64));

        $file = File::fromBase64($base64);

        self::assertSame($expectedBytes, $file->getBytes());

        @unlink($file->getRealPath());
    }

    private function getTestImageBase64(): string
    {
        return base64_encode((string) file_get_contents(__DIR__ . '/file-image-test.png'));
    }

    private function createFile(): File
    {
        return new File(
            name: 'test.txt',
            mime: 'text/plain',
            bytes: 100,
            realPath: '/tmp/test.txt',
        );
    }
}
