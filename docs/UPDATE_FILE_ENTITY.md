# Implementing the Factory Pattern for File Entities

The bundle uses a **factory pattern** to decouple from your specific entity implementation. This guide explains how to implement the required components.

## Architecture Overview

```
FilePersistenceListener (bundle)
        ↓
    (uses)
        ↓
EntityFileFactoryInterface ← YOU MUST IMPLEMENT THIS
        ↓
    (returns)
        ↓
FileInterface (from team-mate-pro/contracts)
        ↓
    (implemented by)
        ↓
Your File Entity (e.g., App\Core\Entity\File)
```

## Step 1: Ensure File Entity Implements FileInterface

Your `File` entity **must** implement `TeamMatePro\Contracts\Model\FileInterface`:

```php
<?php

declare(strict_types=1);

namespace App\Core\Entity;

use TeamMatePro\Contracts\Model\FileInterface;
// ... other imports

#[Entity]
class File implements FileInterface // REQUIRED
{
    // Must implement these methods:
    public function getId(): string { /* ... */ }
    public function getRealPath(): string { /* ... */ }
    public function getName(): string { /* ... */ }
    public function getMime(): string { /* ... */ }
    public function getBytes(): int { /* ... */ }
}
```

**Note**: You do NOT need to implement `FileEntityInterface` (deprecated). Only `FileInterface` from contracts.

## Step 2: Create the EntityFileFactory

Create a **separate factory class** at `src/Factory/EntityFileFactory.php`:

```php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\Core\Entity\File;
use TeamMatePro\Contracts\Model\FileInterface;
use TeamMatePro\DoctrineUtilsBundle\Factory\EntityFileFactoryInterface;

final readonly class EntityFileFactory implements EntityFileFactoryInterface
{
    /**
     * Converts a FileInterface to your specific File entity.
     *
     * In most cases, if your File entity already implements FileInterface,
     * you can just return it directly (with optional validation).
     */
    public function createFromInterface(FileInterface $file): FileInterface
    {
        // Option A: Direct return (most common)
        // If your entity already implements FileInterface, just validate and return
        if ($file instanceof File) {
            return $file;
        }

        // Option B: Type checking/validation
        throw new \InvalidArgumentException(
            sprintf(
                'Expected instance of %s, got %s',
                File::class,
                $file::class
            )
        );

        // Option C: Entity conversion (if you have different implementations)
        // return new File(
        //     mime: $file->getMime(),
        //     createdBy: null, // Handle as needed
        //     name: $file->getName(),
        //     bytes: $file->getBytes(),
        //     realPath: $file->getRealPath(),
        // );
    }
}
```

### When to Use Each Option

- **Option A** (Direct return): When your entity already implements `FileInterface` and you just need to pass it through
- **Option B** (Validation): When you want to ensure type safety
- **Option C** (Conversion): When you have multiple `FileInterface` implementations and need to convert between them

## Step 3: Register the Factory as a Service

Add to your `config/services.yaml`:

```yaml
services:
    # ... other services

    # Register your factory implementation
    App\Factory\EntityFileFactory: ~

    # CRITICAL: Alias the interface to your implementation
    TeamMatePro\DoctrineUtilsBundle\Factory\EntityFileFactoryInterface:
        alias: App\Factory\EntityFileFactory
```

**This service registration is mandatory.** Without it, the bundle's `FilePersistenceListener` cannot be instantiated.

## Step 4: Verify Implementation

### Check Factory Registration

```bash
php bin/console debug:container TeamMatePro\\DoctrineUtilsBundle\\Factory\\EntityFileFactoryInterface
```

Expected output:
```
Information for Service "TeamMatePro\DoctrineUtilsBundle\Factory\EntityFileFactoryInterface"
===============================================================================

 Service ID  TeamMatePro\DoctrineUtilsBundle\Factory\EntityFileFactoryInterface
 Class       App\Factory\EntityFileFactory
```

### Check Listener Registration

```bash
php bin/console debug:container TeamMatePro\\DoctrineUtilsBundle\\EventListener\\FilePersistenceListener
```

Should show the listener is registered and has the factory injected.

## How It Works

1. **Entity Persisted**: Your code persists a `File` entity (which implements `FileInterface`)
   ```php
   $file = new File(/* ... */);
   $entityManager->persist($file);
   $entityManager->flush(); // Triggers postPersist event
   ```

2. **Listener Activated**: The `FilePersistenceListener` receives the `postPersist` event

3. **Interface Check**: Listener checks `$entity instanceof FileInterface`

4. **Factory Called**: Listener calls `$this->factory->createFromInterface($entity)`

5. **File Upload**: The returned `FileInterface` is used to read file data and upload to storage

## Why This Pattern?

The factory pattern provides:
1. **Decoupling**: Bundle doesn't know about your specific `File` entity class
2. **Flexibility**: You control entity creation/validation logic
3. **Type Safety**: Factory ensures correct types are passed
4. **Extensibility**: Easy to support multiple file entity types in future

## Common Mistakes

❌ **Implementing `createFromInterface()` on the entity itself**
```php
class File implements FileInterface
{
    public static function createFromInterface(FileInterface $file): static // WRONG
    {
        // ...
    }
}
```

✅ **Creating a separate factory class**
```php
class EntityFileFactory implements EntityFileFactoryInterface // CORRECT
{
    public function createFromInterface(FileInterface $file): FileInterface
    {
        // ...
    }
}
```

❌ **Forgetting to register the service alias**
```yaml
services:
    App\Factory\EntityFileFactory: ~ # Not enough!
```

✅ **Registering both the class AND the interface alias**
```yaml
services:
    App\Factory\EntityFileFactory: ~
    TeamMatePro\DoctrineUtilsBundle\Factory\EntityFileFactoryInterface:
        alias: App\Factory\EntityFileFactory # REQUIRED
```
