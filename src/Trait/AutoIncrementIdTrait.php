<?php

declare(strict_types=1);

namespace TeamMatePro\DoctrineUtilsBundle\Trait;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;

use function is_null;

/**
 * Provides auto-incrementing integer ID functionality for Doctrine entities.
 *
 * Usage:
 * ```php
 * use TeamMatePro\DoctrineUtilsBundle\Trait\AutoIncrementIdTrait;
 *
 * #[ORM\Entity]
 * class MyEntity
 * {
 *     use AutoIncrementIdTrait;
 * }
 * ```
 */
trait AutoIncrementIdTrait
{
    #[Id]
    #[GeneratedValue(strategy: 'AUTO')]
    #[Column]
    protected ?int $id = null;

    public function getId(): string
    {
        return is_null($this->id) ? 'N/A' : (string) $this->id;
    }
}
