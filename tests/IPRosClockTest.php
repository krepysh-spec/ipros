<?php

namespace KrepyshSpec\IPros\Tests;

use DateTimeImmutable;
use KrepyshSpec\IPros\Interfaces\ProviderInterface;
use KrepyshSpec\IPros\IPRosClock;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

class IPRosClockTest extends TestCase
{
    /** @var ProviderInterface&MockObject */
    private ProviderInterface $providerMock;

    protected function setUp(): void
    {
        $this->providerMock = $this->createMock(ProviderInterface::class);
    }

    #[Test]
    public function testReturnsTimeFromProvider(): void
    {
        $expectedDate = new DateTimeImmutable('2024-01-01 12:00:00');

        $this->providerMock
            ->expects($this->once())
            ->method('getNowTime')
            ->with(['ip' => '8.8.8.8'])
            ->willReturn($expectedDate);

        $clock = (new IPRosClock($this->providerMock))
            ->setIp('8.8.8.8');

        $this->assertSame($expectedDate, $clock->now());
    }

    #[Test]
    public function testSetInvalidIpThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Ip address invalid_ip is not valid');

        (new IPRosClock($this->providerMock))
            ->setIp('invalid_ip');
    }

    #[Test]
    public function testSetOptionsMergesCorrectly(): void
    {
        $expectedDate = new DateTimeImmutable('2024-01-01 00:00:00');

        $this->providerMock
            ->expects($this->once())
            ->method('getNowTime')
            ->with([
                'ip' => '1.1.1.1',
                'apiKey' => 'secret',
            ])
            ->willReturn($expectedDate);

        $clock = (new IPRosClock($this->providerMock))
            ->setIp('1.1.1.1')
            ->setOptions(['apiKey' => 'secret']);

        $this->assertSame($expectedDate, $clock->now());
    }
}
