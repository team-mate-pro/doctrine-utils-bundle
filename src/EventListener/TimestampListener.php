<?php

declare(strict_types=1);

namespace TeamMatePro\DoctrineUtilsBundle\EventListener;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use TeamMatePro\Contracts\Entity\TimeStampAbleInterface;

/**
 * Doctrine event listener that automatically timestamps entities.
 *
 * This listener handles:
 * - prePersist: Sets createdAt and updatedAt timestamps
 * - preUpdate: Updates the updatedAt timestamp
 *
 * Entities must implement TimeStampAbleInterface from team-mate-pro/contracts.
 */
final class TimestampListener
{
    /**
     * @param LifecycleEventArgs<\Doctrine\Persistence\ObjectManager> $args
     */
    public function prePersist(LifecycleEventArgs $args): void
    {
        $this->handleTimestamp($args);
    }

    /**
     * @param LifecycleEventArgs<\Doctrine\Persistence\ObjectManager> $args
     */
    public function preUpdate(LifecycleEventArgs $args): void
    {
        $this->handleTimestamp($args);
    }

    /**
     * @param LifecycleEventArgs<\Doctrine\Persistence\ObjectManager> $args
     */
    private function handleTimestamp(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof TimeStampAbleInterface) {
            return;
        }

        $entity->timestamp();
    }
}
