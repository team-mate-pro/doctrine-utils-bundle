<?php

declare(strict_types=1);

namespace TeamMatePro\DoctrineUtilsBundle\Tests\EventListener;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;
use TeamMatePro\DoctrineUtilsBundle\EventListener\TimestampListener;
use TeamMatePro\DoctrineUtilsBundle\Tests\Stubs\TimeStampAbleStub;

final class TimestampListenerTest extends TestCase
{
    private TimestampListener $listener;

    protected function setUp(): void
    {
        $this->listener = new TimestampListener();
    }

    #[Test]
    public function prePersistTimestampsEntity(): void
    {
        $entity = new TimeStampAbleStub();
        $args = $this->createArgs($entity);

        $this->listener->prePersist($args);

        self::assertTrue($entity->timestamped);
        self::assertNotNull($entity->getCreatedAt());
    }

    #[Test]
    public function preUpdateTimestampsEntity(): void
    {
        $entity = new TimeStampAbleStub();
        $args = $this->createArgs($entity);

        $this->listener->preUpdate($args);

        self::assertTrue($entity->timestamped);
        self::assertNotNull($entity->getUpdatedAt());
    }

    #[Test]
    public function prePersistIgnoresNonTimestampableEntity(): void
    {
        $entity = new stdClass();
        $args = $this->createArgs($entity);

        $this->listener->prePersist($args);

        self::assertObjectNotHasProperty('timestamped', $entity);
    }

    #[Test]
    public function preUpdateIgnoresNonTimestampableEntity(): void
    {
        $entity = new stdClass();
        $args = $this->createArgs($entity);

        $this->listener->preUpdate($args);

        self::assertObjectNotHasProperty('timestamped', $entity);
    }

    /**
     * @return LifecycleEventArgs<ObjectManager>
     */
    private function createArgs(object $entity): LifecycleEventArgs
    {
        return new LifecycleEventArgs($entity, $this->createMock(ObjectManager::class));
    }
}
