<?php

declare(strict_types=1);

namespace TeamMatePro\DoctrineUtilsBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use TeamMatePro\DoctrineUtilsBundle\EventListener\FilePersistenceListener;

/**
 * Extension class that loads and processes bundle configuration.
 *
 * Handles conditional registration of the FilePersistenceListener based on configuration.
 */
final class TeamMateProDoctrineUtilsExtension extends Extension
{
    /**
     * @param array<int, array<string, mixed>> $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // Load base services
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../../config')
        );
        $loader->load('services.yaml');

        // Set parameters for potential use by other services
        $container->setParameter(
            'team_mate_pro_doctrine_utils.enable_file_persistence',
            $config['enable_file_persistence']
        );
        $container->setParameter(
            'team_mate_pro_doctrine_utils.file_entity_class',
            $config['file_entity_class']
        );

        // Conditionally register or remove the file persistence listener
        if ($config['enable_file_persistence']) {
            $definition = $container->getDefinition(FilePersistenceListener::class);
            $definition->setArgument('$fileEntityClass', $config['file_entity_class']);
            $definition->setArgument('$storage', new Reference($config['storage_service']));
        } else {
            // Remove the service entirely if disabled
            $container->removeDefinition(FilePersistenceListener::class);
        }
    }
}
