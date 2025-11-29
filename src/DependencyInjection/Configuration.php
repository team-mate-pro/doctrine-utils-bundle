<?php

declare(strict_types=1);

namespace TeamMatePro\DoctrineUtilsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Bundle configuration definition.
 *
 * Defines the configuration tree structure for team_mate_pro_doctrine_utils bundle.
 */
final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('team_mate_pro_doctrine_utils');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->booleanNode('enable_file_persistence')
                    ->defaultFalse()
                    ->info('Enable automatic file persistence on Doctrine entities')
                ->end()
                ->scalarNode('file_entity_class')
                    ->defaultValue('App\\Entity\\File')
                    ->info('FQCN of the File entity class to watch for persistence events')
                ->end()
                ->scalarNode('storage_service')
                    ->defaultValue('defaultStorage')
                    ->info('Flysystem storage service ID to use for file persistence')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
