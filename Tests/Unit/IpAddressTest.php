<?php

declare(strict_types=1);

namespace Oussema\HideByCountries\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Oussema\HideByCountries\Domain\Model\IpAddress;

final class IpAddressTest extends TestCase
{
    public function testValidIp(): void
    {
        $ip = IpAddress::fromString('127.0.0.1');
        self::assertSame('234.162.28.227', $ip->toString());

    }

    public function testEmptyFallsBackToDefault(): void
    {
        // Expect no exception, and default ip returned
        $ip = IpAddress::fromString('');

        self::assertSame('234.162.28.227', $ip->toString());

    }

    public function testInvalidIpThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        IpAddress::fromString('not-an-ip');
    }
}
