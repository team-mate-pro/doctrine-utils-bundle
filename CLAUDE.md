# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

A Symfony bundle (`team-mate-pro/doctrine-utils-bundle`) providing Doctrine utilities for PHP 8.3+/Symfony 7.0+/Doctrine ORM 3.0+:
- Automatic file persistence to Flysystem storage
- Automatic entity timestamping
- UUID and auto-increment ID traits
- UUID binary conversion utilities

## Commands

### Development Workflow
```bash
make check     # (or: make c) Run all CI checks: phpcs, phpstan, tests
make check_fast # (or: make cf) Run checks with auto-fix first
make fix       # (or: make f) Auto-fix code style issues
make tests     # (or: make t) Run all tests
```

### Individual Tools
```bash
composer phpunit          # Run PHPUnit tests
composer phpcs            # Check code style (PSR-12)
composer phpcs:fix        # Fix code style
composer phpstan          # Static analysis (level max)
composer phpstan:baseline # Generate PHPStan baseline
```

### Running a Single Test
```bash
./vendor/bin/phpunit tests/Path/To/TestFile.php
./vendor/bin/phpunit --filter testMethodName
```

## Architecture

### Bundle Structure
```
src/
├── TeamMateProDoctrineUtilsBundle.php   # Bundle entry point
├── DependencyInjection/
│   ├── Configuration.php                 # Config tree (team_mate_pro_doctrine_utils)
│   └── TeamMateProDoctrineUtilsExtension.php
├── EventListener/
│   ├── FilePersistenceListener.php      # postPersist/postRemove for files
│   └── TimestampListener.php            # prePersist/preUpdate for timestamps
├── Trait/
│   ├── UuidIdTrait.php                  # Symfony Uuid-based primary key
│   └── AutoIncrementIdTrait.php         # Integer auto-increment primary key
├── Entity/
│   └── FileEntityInterface.php
├── Factory/
│   └── EntityFileFactoryInterface.php
└── Utils/
    └── doctrine-util.php                # binary() and binaryUnwrap() functions
```

### Key Contracts (from team-mate-pro/contracts)
- `FileInterface` - Required for file persistence (getId, getName, getMime, getBytes, getRealPath)
- `TimeStampAbleInterface` - Required for automatic timestamping (timestamp method)

### Configuration Keys
- `enable_file_persistence` (bool, default: false)
- `enable_timestamp_listener` (bool, default: false)
- `storage_service` (string, default: 'defaultStorage')
- `file_entity_class` (string, default: 'App\Entity\File')

## Code Style

- PSR-12 standard (via phpcs.xml)
- PHPStan level max with strict settings
- `declare(strict_types=1)` in all PHP files
- Final classes preferred for non-extensible components
- Readonly classes where appropriate (e.g., FilePersistenceListener)
