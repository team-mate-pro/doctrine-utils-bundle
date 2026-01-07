<?php

declare(strict_types=1);

namespace TeamMatePro\DoctrineUtilsBundle\Entity;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use finfo;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Uid\Uuid;
use TeamMatePro\Contracts\Model\FileInterface;
use TeamMatePro\DoctrineUtilsBundle\Trait\UuidIdTrait;

#[ORM\Entity]
#[ORM\Table(name: 'files')]
class File implements FileInterface
{
    use UuidIdTrait;

    /**
     * Note: Not using `readonly` to maintain compatibility with Doctrine ORM 3.x
     * lazy ghost objects which hydrate properties via reflection after construction.
     */
    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 100)]
    private string $mime;

    #[ORM\Column(type: 'integer')]
    private int $bytes;

    #[ORM\Column(type: 'string', length: 500)]
    private string $realPath;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $fileUrl;

    public function __construct(
        string $name,
        string $mime,
        int $bytes,
        string $realPath,
        ?string $fileUrl = null,
    ) {
        $this->id = Uuid::v4();
        $this->createdAt = new DateTimeImmutable();
        $this->name = $name;
        $this->mime = $mime;
        $this->bytes = $bytes;
        $this->realPath = $realPath;
        $this->fileUrl = $fileUrl;
    }

    public static function createFromInterface(FileInterface $file): self
    {
        return new self(
            name: $file->getName() ?? 'Unknown',
            mime: $file->getMime(),
            bytes: $file->getBytes(),
            realPath: $file->getRealPath(),
            fileUrl: $file->getFileUrl(),
        );
    }

    public static function createFromUploadedFile(UploadedFile $uploadedFile): self
    {
        return new self(
            name: $uploadedFile->getClientOriginalName(),
            mime: $uploadedFile->getClientMimeType(),
            bytes: $uploadedFile->getSize() ?: 0,
            realPath: $uploadedFile->getRealPath() ?: '/',
        );
    }

    /**
     * Create a File from a base64 encoded string.
     *
     * Supports both plain base64 and data URI format (e.g., "data:image/png;base64,...")
     *
     * @param string $base64 The base64 encoded file content (with or without data URI prefix)
     * @param string|null $name Optional filename (defaults to generated UUID-based name)
     */
    public static function fromBase64(string $base64, ?string $name = null): self
    {
        $mime = null;

        // Check if it's a data URI format
        if (preg_match('/^data:([a-zA-Z0-9\/\-\+\.]+);base64,/', $base64, $matches)) {
            $mime = $matches[1];
            $base64 = substr($base64, strlen($matches[0]));
        }

        $decodedContent = base64_decode($base64, true);
        if ($decodedContent === false) {
            throw new \InvalidArgumentException('Invalid base64 string provided.');
        }

        $bytes = strlen($decodedContent);

        // Detect MIME type from content if not provided in data URI
        if ($mime === null) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $detectedMime = $finfo->buffer($decodedContent);
            $mime = $detectedMime !== false ? $detectedMime : 'application/octet-stream';
        }

        // Create temporary file
        $tempPath = sys_get_temp_dir() . '/' . Uuid::v4()->toRfc4122();
        file_put_contents($tempPath, $decodedContent);

        // Generate filename if not provided
        if ($name === null) {
            $extension = self::getExtensionFromMime($mime);
            $name = Uuid::v4()->toRfc4122() . ($extension !== '' ? '.' . $extension : '');
        }

        return new self(
            name: $name,
            mime: $mime,
            bytes: $bytes,
            realPath: $tempPath,
        );
    }

    private static function getExtensionFromMime(string $mime): string
    {
        $mimeToExtension = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
            'image/bmp' => 'bmp',
            'image/tiff' => 'tiff',
            'application/pdf' => 'pdf',
            'application/json' => 'json',
            'application/xml' => 'xml',
            'text/plain' => 'txt',
            'text/html' => 'html',
            'text/css' => 'css',
            'text/javascript' => 'js',
            'application/zip' => 'zip',
            'application/gzip' => 'gz',
            'video/mp4' => 'mp4',
            'video/webm' => 'webm',
            'audio/mpeg' => 'mp3',
            'audio/wav' => 'wav',
        ];

        return $mimeToExtension[$mime] ?? '';
    }

    /** @phpstan-ignore return.unusedType (required by NameAware interface) */
    public function getName(): ?string
    {
        return $this->name;
    }

    public function getMime(): string
    {
        return $this->mime;
    }

    public function getBytes(): int
    {
        return $this->bytes;
    }

    public function getRealPath(): string
    {
        return $this->realPath;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function isWebImage(): bool
    {
        return in_array($this->mime, [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/svg+xml',
        ], true);
    }

    public function getFileUrl(): ?string
    {
        return $this->fileUrl;
    }

    public function setFileUrl(?string $fileUrl): self
    {
        $this->fileUrl = $fileUrl;

        return $this;
    }
}
