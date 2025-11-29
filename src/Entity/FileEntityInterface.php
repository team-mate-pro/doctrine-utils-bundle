<?php

declare(strict_types=1);

namespace TeamMatePro\DoctrineUtilsBundle\Entity;

use TeamMatePro\Contracts\Model\FileInterface;

/**
 * Interface for file entities that work with FilePersistenceListener.
 *
 * Entities implementing this interface will automatically have their files
 * persisted to Flysystem storage on Doctrine lifecycle events.
 *
 * This interface provides a factory method pattern that creates entity instances
 * from the shared FileInterface contract.
 */
interface FileEntityInterface
{
    /**
     * Create a file entity instance from the shared file interface.
     *
     * This factory method allows the bundle to work with the standardized
     * FileInterface from team-mate-pro/contracts without knowing
     * the concrete entity implementation details.
     *
     * @param FileInterface $file The file interface from contracts package
     * @return static New instance of the implementing entity
     */
    public static function createFromInterface(FileInterface $file): static;
}
