# Team Mate Pro Doctrine Utils Bundle

A Symfony bundle providing Doctrine utilities for file persistence with Flysystem storage backends.

## Features

- Automatic file upload to Flysystem storage on entity persist
- Automatic file deletion from storage on entity removal
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
team_mate_pro_doctrine_utils:
    enable_file_persistence: true
    storage_service: 'defaultStorage'  # Your Flysystem service ID
```

### Configuration Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `enable_file_persistence` | boolean | `false` | Enable/disable the file persistence listener |
| `storage_service` | string | `'defaultStorage'` | Flysystem storage service ID |

## Setup

### 1. Implement the FileInterface

Your file entity must implement `TeamMatePro\Contracts\Model\FileInterface`:

```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use TeamMatePro\Contracts\Model\FileInterface;

#[ORM\Entity]
class File implements FileInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(type: 'string')]
    private string $name;

    #[ORM\Column(type: 'string')]
    private string $mime;

    #[ORM\Column(type: 'integer')]
    private int $bytes;

    #[ORM\Column(type: 'string')]
    private string $realPath;

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMime(): string
    {
        return $this->mime;
    }

    public function getBytes(): int
    {
        return $this->bytes;
    }

    public function getRealPath(): string
    {
        return $this->realPath;
    }
}
```

### 2. Implement the EntityFileFactory

Create a factory that implements `EntityFileFactoryInterface`:

```php
<?php

namespace App\Factory;

use TeamMatePro\Contracts\Model\FileInterface;
use TeamMatePro\DoctrineUtilsBundle\Factory\EntityFileFactoryInterface;

class EntityFileFactory implements EntityFileFactoryInterface
{
    public function createFromInterface(FileInterface $file): FileInterface
    {
        // Add validation, transformation, or simply return the file
        return $file;
    }
}
```

### 3. Register the Factory

In `config/services.yaml`:

```yaml
services:
    App\Factory\EntityFileFactory: ~

    TeamMatePro\DoctrineUtilsBundle\Factory\EntityFileFactoryInterface:
        alias: App\Factory\EntityFileFactory
```

### 4. Configure Flysystem Storage

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
// Upload - file is automatically stored when entity is persisted
$file = new File();
$file->setId(Uuid::uuid4()->toString());
$file->setName('document.pdf');
$file->setMime('application/pdf');
$file->setBytes(filesize('/tmp/upload.pdf'));
$file->setRealPath('/tmp/upload.pdf');

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
