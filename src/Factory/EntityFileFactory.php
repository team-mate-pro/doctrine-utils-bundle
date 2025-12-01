<?php

declare(strict_types=1);

namespace TeamMatePro\DoctrineUtilsBundle\Factory;

use TeamMatePro\Contracts\Model\FileInterface;
use TeamMatePro\DoctrineUtilsBundle\Entity\File;

class EntityFileFactory implements EntityFileFactoryInterface
{
    public function createFromInterface(FileInterface $file): FileInterface
    {
        return File::createFromInterface($file);
    }
}
