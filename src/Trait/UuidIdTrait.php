<?php

declare(strict_types=1);

namespace TeamMatePro\DoctrineUtilsBundle\Trait;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

/**
 * Provides UUID-based ID functionality for Doctrine entities.
 *
 * Usage:
 * ```php
 * use TeamMatePro\DoctrineUtilsBundle\Trait\UuidIdTrait;
 *
 * #[ORM\Entity]
 * class MyEntity
 * {
 *     use UuidIdTrait;
 *
 *     public function __construct()
 *     {
 *         $this->id = Uuid::v4();
 *     }
 * }
 * ```
 */
trait UuidIdTrait
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    protected Uuid $id;

    public function getId(): string
    {
        return $this->id->toString();
    }
}
