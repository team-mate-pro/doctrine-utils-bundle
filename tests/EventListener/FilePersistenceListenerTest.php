<?php

declare(strict_types=1);

namespace TeamMatePro\DoctrineUtilsBundle\Tests\EventListener;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\ObjectManager;
use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use TeamMatePro\Contracts\Model\FileInterface;
use TeamMatePro\DoctrineUtilsBundle\EventListener\FilePersistenceListener;
use TeamMatePro\DoctrineUtilsBundle\Factory\EntityFileFactoryInterface;

final class FilePersistenceListenerTest extends TestCase
{
    private MockObject&FilesystemOperator $storage;
    private FilePersistenceListener $listener;

    protected function setUp(): void
    {
        $this->storage = $this->createMock(FilesystemOperator::class);
        $factory = $this->createMock(EntityFileFactoryInterface::class);
        $this->listener = new FilePersistenceListener($this->storage, $factory);
    }

    #[Test]
    public function postPersistWritesFileToStorage(): void
    {
        // Given
        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        self::assertNotFalse($tempFile);
        file_put_contents($tempFile, 'file content');

        try {
            $entity = $this->createMock(FileInterface::class);
            $entity->method('getId')->willReturn('file-id-123');
            $entity->method('getRealPath')->willReturn($tempFile);
            $entity->method('getName')->willReturn('document.pdf');
            $entity->method('getMime')->willReturn('application/pdf');

            // Then
            $this->storage->expects(self::once())
                ->method('write')
                ->with('file-id-123', 'file content');

            // When
            $this->listener->postPersist($this->createArgs($entity));
        } finally {
            @unlink($tempFile);
        }
    }

    #[Test]
    public function postPersistIgnoresNonFileEntity(): void
    {
        $this->storage->expects(self::never())->method('write');

        $this->listener->postPersist($this->createArgs(new stdClass()));
    }

    #[Test]
    public function postRemoveDeletesFileFromStorage(): void
    {
        $entity = $this->createMock(FileInterface::class);
        $entity->method('getId')->willReturn('file-id-123');

        $this->storage->expects(self::once())
            ->method('delete')
            ->with('file-id-123');

        $this->listener->postRemove($this->createArgs($entity));
    }

    #[Test]
    public function postRemoveIgnoresNonFileEntity(): void
    {
        $this->storage->expects(self::never())->method('delete');

        $this->listener->postRemove($this->createArgs(new stdClass()));
    }

    /**
     * @return LifecycleEventArgs<ObjectManager>
     */
    private function createArgs(object $entity): LifecycleEventArgs
    {
        return new LifecycleEventArgs($entity, $this->createMock(ObjectManager::class));
    }
}
