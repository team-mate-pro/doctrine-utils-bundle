<?php

declare(strict_types=1);

namespace TeamMatePro\DoctrineUtilsBundle\Tests\Stubs;

use DateTimeImmutable;
use DateTimeInterface;
use TeamMatePro\Contracts\Model\TimeStampAbleInterface;

final class TimeStampAbleStub implements TimeStampAbleInterface
{
    public bool $timestamped = false;
    private ?DateTimeInterface $createdAt = null;
    private ?DateTimeInterface $updatedAt = null;

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function timestamp(): void
    {
        $now = new DateTimeImmutable();
        $this->createdAt ??= $now;
        $this->updatedAt = $now;
        $this->timestamped = true;
    }
}
