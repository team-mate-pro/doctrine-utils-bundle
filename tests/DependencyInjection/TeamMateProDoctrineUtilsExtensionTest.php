<?php

declare(strict_types=1);

namespace TeamMatePro\DoctrineUtilsBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use TeamMatePro\DoctrineUtilsBundle\DependencyInjection\TeamMateProDoctrineUtilsExtension;
use TeamMatePro\DoctrineUtilsBundle\EventListener\FilePersistenceListener;
use TeamMatePro\DoctrineUtilsBundle\EventListener\TimestampListener;

final class TeamMateProDoctrineUtilsExtensionTest extends TestCase
{
    #[Test]
    public function loadWithDefaultConfigRemovesBothListeners(): void
    {
        $container = new ContainerBuilder();
        $extension = new TeamMateProDoctrineUtilsExtension();

        $extension->load([], $container);

        self::assertFalse($container->hasDefinition(FilePersistenceListener::class));
        self::assertFalse($container->hasDefinition(TimestampListener::class));
    }

    #[Test]
    public function loadWithFilePersistenceEnabledKeepsListener(): void
    {
        $container = new ContainerBuilder();
        $extension = new TeamMateProDoctrineUtilsExtension();

        $extension->load([[
            'enable_file_persistence' => true,
            'storage_service' => 'my_storage',
            'file_entity_class' => 'App\\Entity\\MyFile',
        ]], $container);

        self::assertTrue($container->hasDefinition(FilePersistenceListener::class));

        $definition = $container->getDefinition(FilePersistenceListener::class);
        self::assertSame('App\\Entity\\MyFile', $definition->getArgument('$fileEntityClass'));
    }

    #[Test]
    public function loadWithTimestampListenerEnabledKeepsListener(): void
    {
        $container = new ContainerBuilder();
        $extension = new TeamMateProDoctrineUtilsExtension();

        $extension->load([[
            'enable_timestamp_listener' => true,
        ]], $container);

        self::assertTrue($container->hasDefinition(TimestampListener::class));
    }

    #[Test]
    public function loadSetsContainerParameters(): void
    {
        $container = new ContainerBuilder();
        $extension = new TeamMateProDoctrineUtilsExtension();

        $extension->load([[
            'enable_file_persistence' => true,
            'file_entity_class' => 'App\\Entity\\CustomFile',
            'storage_service' => 'custom_storage',
        ]], $container);

        self::assertTrue(
            $container->getParameter('team_mate_pro_doctrine_utils.enable_file_persistence')
        );
        self::assertSame(
            'App\\Entity\\CustomFile',
            $container->getParameter('team_mate_pro_doctrine_utils.file_entity_class')
        );
    }
}
