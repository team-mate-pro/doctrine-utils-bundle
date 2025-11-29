<?php

declare(strict_types=1);

namespace TeamMatePro\DoctrineUtilsBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Team Mate Pro Doctrine Utils Bundle.
 *
 * Provides Doctrine utilities including automatic file persistence to Flysystem storage.
 *
 * Configuration is handled by TeamMateProDoctrineUtilsExtension which allows
 * enabling/disabling features and configuring entity classes and storage services.
 */
final class TeamMateProDoctrineUtilsBundle extends Bundle
{
}
