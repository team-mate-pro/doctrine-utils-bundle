<?php

declare(strict_types=1);

namespace TeamMatePro\DoctrineUtilsBundle\EventListener;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use League\Flysystem\FilesystemOperator;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use TeamMatePro\Contracts\Model\FileInterface;
use TeamMatePro\DoctrineUtilsBundle\Entity\FileEntityInterface;
use TeamMatePro\DoctrineUtilsBundle\Factory\EntityFileFactoryInterface;

/**
 * Doctrine event listener that automatically persists file entities to Flysystem storage.
 *
 * This listener handles:
 * - postPersist: Uploads file content to storage when entity is persisted
 * - postRemove: Deletes file from storage when entity is removed
 *
 * The entity class MUST implement FileEntityInterface.
 */
final readonly class FilePersistenceListener
{
    public function __construct(
        private FilesystemOperator $storage,
        private EntityFileFactoryInterface $factory,
    ) {
    }

    /**
     * @param LifecycleEventArgs<\Doctrine\Persistence\ObjectManager> $args
     */
    public function postPersist(LifecycleEventArgs $args): void
    {
        $object = $args->getObject();

        if (!$object instanceof FileInterface) {
            return;
        }

        $entity = $this->factory->createFromInterface($object);

        $this->storage->write(
            location: $entity->getId(),
            contents: (new UploadedFile(
                path: $entity->getRealPath(),
                originalName: $entity->getName() ?? '',
                mimeType: $entity->getMime(),
                error: null,
                test: true
            ))->getContent(),
        );
    }

    /**
     * @param LifecycleEventArgs<\Doctrine\Persistence\ObjectManager> $args
     */
    public function postRemove(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof FileInterface) {
            return;
        }

        $this->storage->delete($entity->getId());
    }
}
