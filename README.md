# Team Mate Pro Doctrine Utils Bundle

A Symfony bundle providing Doctrine utilities for file persistence with Flysystem storage backends.

## Features

- Automatic file upload to Flysystem storage on entity persist
- Automatic file deletion from storage on entity removal
- Automatic entity timestamping (createdAt/updatedAt)
- Configurable storage backends (local, S3, Azure, etc.)
- Enable/disable functionality via configuration
- Factory pattern for flexible entity integration
- Reusable entity traits (AutoIncrementIdTrait, UuidIdTrait)

## Requirements

- PHP 8.3+
- Symfony 7.0+
- Doctrine ORM 3.0+
- League Flysystem 3.0+

## Installation

```bash
composer require team-mate-pro/doctrine-utils-bundle
```

### Register the Bundle

If you're not using Symfony Flex, add the bundle to `config/bundles.php`:

```php
return [
    // ...
    TeamMatePro\DoctrineUtilsBundle\TeamMateProDoctrineUtilsBundle::class => ['all' => true],
];
```

## Configuration

Create `config/packages/team_mate_pro_doctrine_utils.yaml`:

```yaml
# Full configuration (copy-paste ready)
team_mate_pro_doctrine_utils:
    # Enable automatic file persistence on Doctrine entities
    enable_file_persistence: false

    # Flysystem storage service ID to use for file persistence
    storage_service: 'defaultStorage'

    # Enable automatic timestamping on entities implementing TimeStampAbleInterface
    enable_timestamp_listener: false
```

### Configuration Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `enable_file_persistence` | boolean | `false` | Enable/disable the file persistence listener |
| `storage_service` | string | `'defaultStorage'` | Flysystem storage service ID |
| `enable_timestamp_listener` | boolean | `false` | Enable/disable the timestamp listener |

## Setup

### 1. Use the Built-in File Entity (Recommended)

The bundle provides a ready-to-use `File` entity with UUID-based IDs:

```php
use TeamMatePro\DoctrineUtilsBundle\Entity\File;

$file = new File(
    name: 'document.pdf',
    mime: 'application/pdf',
    bytes: filesize('/tmp/upload.pdf'),
    realPath: '/tmp/upload.pdf',
);

$entityManager->persist($file);
$entityManager->flush();
```

The built-in `File` entity:
- Uses `UuidIdTrait` for UUID-based primary keys
- Implements `FileInterface` from `team-mate-pro/contracts`
- Includes `isWebImage()` helper method
- Supports optional `fileUrl` for public URLs (S3, CDN, etc.)
- Provides `File::createFromUploadedFile(UploadedFile $file)` for Symfony file uploads
- Provides `File::createFromInterface(FileInterface $file)` for copying from other implementations

#### Register Doctrine Mapping for the Built-in Entity

To use the built-in `File` entity, you need to register its mapping in your Doctrine configuration.

Add to `config/packages/doctrine.yaml`:

```yaml
doctrine:
    orm:
        mappings:
            TeamMateProDoctrineUtilsBundle:
                is_bundle: false
                type: attribute
                dir: '%kernel.project_dir%/vendor/team-mate-pro/doctrine-utils-bundle/src/Entity'
                prefix: 'TeamMatePro\DoctrineUtilsBundle\Entity'
                alias: DoctrineUtilsBundle
```

Then generate and run the migration:

```bash
# Generate migration for the files table
php bin/console doctrine:migrations:diff

# Review the generated migration, then run it
php bin/console doctrine:migrations:migrate
```

The migration will create the `files` table with the following structure:

| Column | Type | Description |
|--------|------|-------------|
| `id` | uuid (binary) | Primary key (UUID v4) |
| `name` | varchar(255) | Original filename |
| `mime` | varchar(100) | MIME type |
| `bytes` | integer | File size in bytes |
| `real_path` | varchar(500) | Path to the file on disk |
| `created_at` | datetime_immutable | Creation timestamp |
| `file_url` | varchar(500), nullable | Public URL (CDN, S3, etc.) |

### 2. Custom File Entity (Optional)

If you need a custom entity, implement `TeamMatePro\Contracts\Model\FileInterface`:

```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use TeamMatePro\Contracts\Model\FileInterface;

#[ORM\Entity]
class CustomFile implements FileInterface
{
    // Implement required methods: getId(), getName(), getMime(),
    // getBytes(), getRealPath(), getCreatedAt(), isWebImage(), getFileUrl()
}
```

Then create a custom factory implementing `EntityFileFactoryInterface`:

```php
<?php

namespace App\Factory;

use App\Entity\CustomFile;
use TeamMatePro\Contracts\Model\FileInterface;
use TeamMatePro\DoctrineUtilsBundle\Factory\EntityFileFactoryInterface;

class CustomFileFactory implements EntityFileFactoryInterface
{
    public function createFromInterface(FileInterface $file): FileInterface
    {
        return new CustomFile(
            // map properties from $file
        );
    }
}
```

Register your custom factory in `config/services.yaml`:

```yaml
services:
    App\Factory\CustomFileFactory: ~

    TeamMatePro\DoctrineUtilsBundle\Factory\EntityFileFactoryInterface:
        alias: App\Factory\CustomFileFactory
```

### 3. Configure Flysystem Storage

Using `league/flysystem-bundle`:

```yaml
# config/packages/flysystem.yaml
flysystem:
    storages:
        defaultStorage:
            adapter: 'local'
            options:
                directory: '%kernel.project_dir%/var/storage'
```

Or define your own Flysystem service:

```yaml
# config/services.yaml
services:
    defaultStorage:
        class: League\Flysystem\Filesystem
        arguments:
            - '@League\Flysystem\Local\LocalFilesystemAdapter'
```

## Usage

Once configured, the bundle automatically handles file persistence:

```php
use TeamMatePro\DoctrineUtilsBundle\Entity\File;

// Upload - file is automatically stored when entity is persisted
$file = new File(
    name: 'document.pdf',
    mime: 'application/pdf',
    bytes: filesize('/tmp/upload.pdf'),
    realPath: '/tmp/upload.pdf',
);

$entityManager->persist($file);
$entityManager->flush();
// File is now in your Flysystem storage with key = $file->getId()

// Delete - file is automatically removed when entity is deleted
$entityManager->remove($file);
$entityManager->flush();
// File is removed from storage
```

## Disabling File Persistence

To disable the listener without removing the bundle:

```yaml
# config/packages/team_mate_pro_doctrine_utils.yaml
team_mate_pro_doctrine_utils:
    enable_file_persistence: false
```

When disabled, the service is completely removed from the container (zero overhead).

## Using Different Storage Backends

### AWS S3

```yaml
team_mate_pro_doctrine_utils:
    storage_service: 's3Storage'
```

### Azure Blob Storage

```yaml
team_mate_pro_doctrine_utils:
    storage_service: 'azureStorage'
```

## Testing

```php
public function testFileUpload(): void
{
    $file = new File();
    // ... set properties

    $this->entityManager->persist($file);
    $this->entityManager->flush();

    $storage = self::getContainer()->get('defaultStorage');
    $this->assertTrue($storage->fileExists($file->getId()));
}
```

## Timestamp Listener

The bundle provides automatic timestamping for entities implementing `TimeStampAbleInterface`.

### Setup

1. Enable the listener in configuration:

```yaml
team_mate_pro_doctrine_utils:
    enable_timestamp_listener: true
```

2. Implement `TimeStampAbleInterface` on your entity:

```php
<?php

namespace App\Entity;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use TeamMatePro\Contracts\Entity\TimeStampAbleInterface;

#[ORM\Entity]
class MyEntity implements TimeStampAbleInterface
{
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeInterface $updatedAt = null;

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function timestamp(): void
    {
        $now = new DateTimeImmutable();

        if ($this->createdAt === null) {
            $this->createdAt = $now;
        }

        $this->updatedAt = $now;
    }
}
```

The listener automatically calls `timestamp()` on:
- `prePersist` - When entity is first persisted
- `preUpdate` - When entity is updated

## Entity Traits

### AutoIncrementIdTrait

Provides auto-incrementing integer ID functionality for Doctrine entities.

```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use TeamMatePro\DoctrineUtilsBundle\Trait\AutoIncrementIdTrait;

#[ORM\Entity]
class MyEntity
{
    use AutoIncrementIdTrait;

    #[ORM\Column(type: 'string')]
    private string $name;

    // ... other properties
}
```

The trait provides:
- `protected ?int $id` - Auto-incrementing primary key
- `getId(): string` - Returns the ID as string (or `'N/A'` if not yet persisted)

### UuidIdTrait

Provides UUID-based ID functionality for Doctrine entities using Symfony UID component.

```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use TeamMatePro\DoctrineUtilsBundle\Trait\UuidIdTrait;

#[ORM\Entity]
class MyEntity
{
    use UuidIdTrait;

    #[ORM\Column(type: 'string')]
    private string $name;

    public function __construct()
    {
        $this->id = Uuid::v4();
    }

    // ... other properties
}
```

The trait provides:
- `protected Uuid $id` - UUID primary key (requires manual initialization in constructor)
- `getId(): string` - Returns the UUID as string

## Helper Functions

The bundle provides utility functions for UUID binary conversion:

```php
use function TeamMatePro\DoctrineUtilsBundle\Utils\binary;
use function TeamMatePro\DoctrineUtilsBundle\Utils\binaryUnwrap;

// Convert UUID string to binary (for Doctrine queries)
$binaryId = binary('550e8400-e29b-41d4-a716-446655440000');

// Convert multiple UUIDs to binary
$binaryIds = binary(['uuid1', 'uuid2', 'uuid3']);

// Convert binary back to UUID string
$uuidString = binaryUnwrap($binaryId);
```

### Usage in Doctrine Queries

```php
use function TeamMatePro\DoctrineUtilsBundle\Utils\binary;

$qb = $entityManager->createQueryBuilder();
$qb->select('e')
   ->from(Entity::class, 'e')
   ->where('e.id IN (:ids)')
   ->setParameter('ids', binary($uuidStrings));
```

## Troubleshooting

### Verify Factory Registration

```bash
php bin/console debug:container EntityFileFactoryInterface
```

### Check Listener Registration

```bash
php bin/console debug:event-dispatcher doctrine.event_listener
```

### Clear Cache

```bash
php bin/console cache:clear
```

## License

See [LICENSE](LICENSE) file.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.
