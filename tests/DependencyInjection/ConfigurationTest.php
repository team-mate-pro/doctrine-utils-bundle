<?php

declare(strict_types=1);

namespace TeamMatePro\DoctrineUtilsBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;
use TeamMatePro\DoctrineUtilsBundle\DependencyInjection\Configuration;

final class ConfigurationTest extends TestCase
{
    #[Test]
    public function defaultConfigurationValues(): void
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), []);

        self::assertFalse($config['enable_file_persistence']);
        self::assertFalse($config['enable_timestamp_listener']);
        self::assertSame('App\\Entity\\File', $config['file_entity_class']);
        self::assertSame('defaultStorage', $config['storage_service']);
    }

    #[Test]
    public function customConfigurationValues(): void
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [[
            'enable_file_persistence' => true,
            'enable_timestamp_listener' => true,
            'file_entity_class' => 'App\\Entity\\CustomFile',
            'storage_service' => 'custom_storage',
        ]]);

        self::assertTrue($config['enable_file_persistence']);
        self::assertTrue($config['enable_timestamp_listener']);
        self::assertSame('App\\Entity\\CustomFile', $config['file_entity_class']);
        self::assertSame('custom_storage', $config['storage_service']);
    }
}
