<?php

declare(strict_types=1);

namespace TeamMatePro\DoctrineUtilsBundle\Tests\Utils;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

use function TeamMatePro\DoctrineUtilsBundle\Utils\binary;
use function TeamMatePro\DoctrineUtilsBundle\Utils\binaryUnwrap;

final class DoctrineUtilTest extends TestCase
{
    #[Test]
    public function binaryConvertsSingleUuidToBytes(): void
    {
        $uuid = Uuid::v4()->toString();

        $result = binary($uuid);

        self::assertIsString($result);
        self::assertSame(16, strlen($result));
    }

    #[Test]
    public function binaryConvertsArrayOfUuidsToBytes(): void
    {
        $uuids = [Uuid::v4()->toString(), Uuid::v4()->toString()];

        $result = binary($uuids);

        self::assertIsArray($result);
        self::assertCount(2, $result);
        self::assertSame(16, strlen($result[0]));
        self::assertSame(16, strlen($result[1]));
    }

    #[Test]
    public function binaryUnwrapConvertsBinaryBackToString(): void
    {
        $uuid = Uuid::v4()->toString();
        $bin = binary($uuid);
        self::assertIsString($bin);

        $result = binaryUnwrap($bin);

        self::assertSame($uuid, $result);
    }

    #[Test]
    public function binaryRoundTripsCorrectly(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $bin = binary($uuid);
        self::assertIsString($bin);

        self::assertSame($uuid, binaryUnwrap($bin));
    }
}
