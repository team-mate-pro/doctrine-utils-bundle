<?php

declare(strict_types=1);

namespace TeamMatePro\DoctrineUtilsBundle\Factory;

use TeamMatePro\Contracts\Model\FileInterface;

interface EntityFileFactoryInterface
{
    public function createFromInterface(FileInterface $file): FileInterface;
}
