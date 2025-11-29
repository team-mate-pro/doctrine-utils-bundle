# Installation Guide for team-mate-pro/doctrine-utils-bundle

This guide walks through the installation steps for the Doctrine Utils Bundle.

## Prerequisites

Before installing, ensure you have:
- ✅ Symfony 7.0+ application
- ✅ Doctrine ORM installed and configured
- ✅ Flysystem installed and configured
- ✅ `team-mate-pro/contracts` package installed
- ✅ File entity implementing `TeamMatePro\Contracts\Model\FileInterface`

## Installation Checklist

⏳ Step 1: Install the bundle via Composer
⏳ Step 2: **REQUIRED** - Implement `EntityFileFactoryInterface`
⏳ Step 3: Register the factory as a service
⏳ Step 4: Configure the bundle
⏳ Step 5: Verify installation

## Step 1: Install the Bundle via Composer

Once Docker containers are running, execute:

```bash
docker compose exec app composer require team-mate-pro/doctrine-utils-bundle:@dev
```

This will:
- Update `composer.lock` with the new bundle
- Symlink the local bundle into `vendor/`
- Make the bundle classes autoloadable

## Step 2: **REQUIRED** - Implement EntityFileFactoryInterface

**This step is mandatory.** The bundle will not work without a factory implementation.

Create `src/Factory/EntityFileFactory.php`:

```php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\Core\Entity\File;
use TeamMatePro\Contracts\Model\FileInterface;
use TeamMatePro\DoctrineUtilsBundle\Factory\EntityFileFactoryInterface;

final readonly class EntityFileFactory implements EntityFileFactoryInterface
{
    public function createFromInterface(FileInterface $file): FileInterface
    {
        // If your File entity already implements FileInterface,
        // you can return it directly (type casting/validation)
        if ($file instanceof File) {
            return $file;
        }

        // Otherwise, throw an exception or handle conversion
        throw new \InvalidArgumentException(
            sprintf(
                'Expected instance of %s, got %s',
                File::class,
                get_class($file)
            )
        );
    }
}
```

**Why is this required?**
- The bundle works with `TeamMatePro\Contracts\Model\FileInterface` from the contracts package
- Your factory bridges the gap between the contract and your specific entity implementation
- This keeps the bundle decoupled from your application's entity structure

## Step 3: Register the Factory as a Service

Add the factory to your `config/services.yaml`:

```yaml
services:
    # ... other services

    # Register your factory
    App\Factory\EntityFileFactory: ~

    # Alias the interface to your implementation
    TeamMatePro\DoctrineUtilsBundle\Factory\EntityFileFactoryInterface:
        alias: App\Factory\EntityFileFactory
```

**This service registration is mandatory.** The bundle's `FilePersistenceListener` depends on this factory being available.

## Step 3a: Update Your File Entity (If Needed)

Ensure your `File` entity implements `TeamMatePro\Contracts\Model\FileInterface`:

```php
<?php

namespace App\Core\Entity;

use TeamMatePro\Contracts\Model\FileInterface;
// ... other imports

#[Entity]
class File implements FileInterface
{
    // Must implement all FileInterface methods:
    // - getId(): string
    // - getRealPath(): string
    // - getName(): string
    // - getMime(): string
    // - getBytes(): int
}
```

## Step 4: Configure the Bundle

Create `config/packages/team_mate_pro_doctrine_utils.yaml`:

```yaml
team_mate_pro_doctrine_utils:
    # Enable file persistence
    enable_file_persistence: true

    # Flysystem storage service ID
    storage_service: 'defaultStorage'
```

## Step 5: Clear Cache

```bash
docker compose exec app php bin/console cache:clear
```

## Step 6: Verify Installation

### Check the bundle is loaded:

```bash
docker compose exec app php bin/console debug:container TeamMatePro\\DoctrineUtilsBundle\\EventListener\\FilePersistenceListener
```

Expected output:
```
Information for Service "TeamMatePro\DoctrineUtilsBundle\EventListener\FilePersistenceListener"
===============================================================================

 Service ID  TeamMatePro\DoctrineUtilsBundle\EventListener\FilePersistenceListener
 Class       TeamMatePro\DoctrineUtilsBundle\EventListener\FilePersistenceListener
```

### **CRITICAL**: Verify your factory is registered:

```bash
docker compose exec app php bin/console debug:container TeamMatePro\\DoctrineUtilsBundle\\Factory\\EntityFileFactoryInterface
```

Expected output should show your factory implementation:
```
Information for Service "TeamMatePro\DoctrineUtilsBundle\Factory\EntityFileFactoryInterface"
===============================================================================

 Service ID  TeamMatePro\DoctrineUtilsBundle\Factory\EntityFileFactoryInterface
 Class       App\Factory\EntityFileFactory
```

**If this command fails, the bundle will not work.** Go back to Step 3 and ensure the factory is properly registered.

### Verify Doctrine listeners:

```bash
docker compose exec app php bin/console debug:event-dispatcher doctrine.event_listener
```

Should show `FilePersistenceListener` registered for `postPersist` and `postRemove` events.

### Check configuration:

```bash
docker compose exec app php bin/console debug:config team_mate_pro_doctrine_utils
```

Expected output:
```yaml
team_mate_pro_doctrine_utils:
    enable_file_persistence: true
    storage_service: 'defaultStorage'
```

## Step 6: Run Tests

```bash
make tests_unit
make tests_integration
```

Ensure all tests pass, especially those involving file uploads.

## Troubleshooting

### Issue: EntityFileFactoryInterface not found

**Error**: `Cannot autowire service "FilePersistenceListener": argument "$factory" of method "__construct()" references interface "EntityFileFactoryInterface" but no such service exists.`

**Solution**: You haven't implemented the factory. Go back to **Step 2** and create `EntityFileFactory`, then **Step 3** to register it.

### Issue: Factory not registered

**Error**: When running `debug:container EntityFileFactoryInterface`, you get "Service not found"

**Solution**:
1. Verify you created `src/Factory/EntityFileFactory.php`
2. Check `config/services.yaml` contains the interface alias:
   ```yaml
   TeamMatePro\DoctrineUtilsBundle\Factory\EntityFileFactoryInterface:
       alias: App\Factory\EntityFileFactory
   ```
3. Clear cache: `php bin/console cache:clear`

### Issue: Entity doesn't implement FileInterface

**Error**: `Argument #1 ($file) must be of type TeamMatePro\Contracts\Model\FileInterface, App\Core\Entity\File given`

**Solution**: Make your File entity implement `FileInterface`:
```php
class File implements FileInterface
{
    // Implement all required methods
}
```

### Issue: Bundle class not found

**Solution**: Ensure Docker is running and composer installed the bundle:
```bash
docker compose exec app ls -la vendor/team-mate-pro/
```

### Issue: Configuration not loading

**Solution**: Clear cache and check bundle registration:
```bash
docker compose exec app php bin/console cache:clear
docker compose exec app php bin/console debug:container --parameters | grep team_mate_pro
```

### Issue: Files not uploading

**Solution**: Check Flysystem storage is configured:
```bash
docker compose exec app php bin/console debug:container defaultStorage
```

## Configuration Options

You can customize the bundle behavior in `config/packages/team_mate_pro_doctrine_utils.yaml`:

```yaml
team_mate_pro_doctrine_utils:
    # Disable file persistence without removing the bundle
    enable_file_persistence: false

    # Use S3 instead of local storage
    storage_service: 's3Storage'
```

## Next Steps

After installation:

1. **Test file uploads** - Create/delete File entities and verify they appear in storage
2. **Monitor logs** - Check for any errors related to file persistence
3. **Update documentation** - Document the new bundle in your project's README
4. **Consider CI/CD** - Update deployment scripts if needed

## Rolling Back

If you need to revert:

1. Remove bundle from `composer.json`:
```bash
docker compose exec app composer remove team-mate-pro/doctrine-utils-bundle
```

2. Remove bundle registration from `config/bundles.php`

3. Delete configuration file:
```bash
rm config/packages/team_mate_pro_doctrine_utils.yaml
```

4. Restore original `DoctrinePostPersistListener.php` from git history

## Support

For issues or questions, contact the Team Mate Pro development team.
