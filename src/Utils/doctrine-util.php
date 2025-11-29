<?php

declare(strict_types=1);

namespace TeamMatePro\DoctrineUtilsBundle\Utils;

use Symfony\Component\Uid\Uuid;

/**
 * Convert UUID string(s) to binary format for Doctrine storage.
 *
 * @param string|string[] $ids UUID string or array of UUID strings
 * @return string|string[] Binary representation(s)
 */
function binary(string|array $ids): string|array
{
    if (is_string($ids)) {
        return Uuid::fromString($ids)->toBinary();
    }

    return array_map(
        static fn (string $id): string => Uuid::fromString($id)->toBinary(),
        $ids
    );
}

/**
 * Convert binary UUID back to string format.
 *
 * @param string $bin Binary UUID
 * @return string UUID string representation
 */
function binaryUnwrap(string $bin): string
{
    return Uuid::fromBinary($bin)->toString();
}
