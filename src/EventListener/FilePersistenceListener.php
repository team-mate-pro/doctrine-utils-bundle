<?php

declare(strict_types=1);

namespace TeamMatePro\DoctrineUtilsBundle\EventListener;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use TeamMatePro\Contracts\Model\FileInterface;
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
class FilePersistenceListener
{
    /**
     * @param FilesystemOperator $storage Flysystem storage for persisting files
     * @param EntityFileFactoryInterface $factory Factory to create file entities
     * @param class-string|null $fileEntityClass Optional: FQCN of file entity class (for filtering)
     */
    public function __construct(
        private FilesystemOperator $storage,
        private EntityFileFactoryInterface $factory,
        private ?string $fileEntityClass = null,
    ) {
    }

    /**
     * @param LifecycleEventArgs<\Doctrine\Persistence\ObjectManager> $args
     */
    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof FileInterface) {
            return;
        }

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
