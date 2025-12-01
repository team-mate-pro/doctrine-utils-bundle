<?php

declare(strict_types=1);

namespace TeamMatePro\DoctrineUtilsBundle\Entity;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Uid\Uuid;
use TeamMatePro\Contracts\Model\FileInterface;
use TeamMatePro\DoctrineUtilsBundle\Trait\UuidIdTrait;

#[ORM\Entity]
#[ORM\Table(name: 'files')]
class File implements FileInterface
{
    use UuidIdTrait;

    #[ORM\Column(type: 'string', length: 255)]
    private readonly string $name;

    #[ORM\Column(type: 'string', length: 100)]
    private readonly string $mime;

    #[ORM\Column(type: 'integer')]
    private readonly int $bytes;

    #[ORM\Column(type: 'string', length: 500)]
    private readonly string $realPath;

    #[ORM\Column(type: 'datetime_immutable')]
    private readonly DateTimeImmutable $createdAt;

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
