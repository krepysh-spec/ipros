<?php

namespace KrepyshSpec\IPros\Tests;

use DateTimeImmutable;
use KrepyshSpec\IPros\AbstractProvider;
use KrepyshSpec\IPros\IPRosClock;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

class IPRosClockTest extends TestCase
{
    /** @var AbstractProvider&MockObject */
    private AbstractProvider $providerMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a mock of AbstractProvider
        $this->providerMock = $this->createMock(AbstractProvider::class);
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

        $clock = (new IPRosClock($this->providerMock))->setIp('8.8.8.8');

        $this->assertEquals($expectedDate, $clock->now());
    }

    #[Test]
    public function testSetInvalidIpThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Ip address invalid_ip is not valid');

        $clock = new IPRosClock($this->providerMock);
        $clock->setIp('invalid_ip');
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
                'apiKey' => 'secret'
            ])
            ->willReturn($expectedDate);

        $clock = (new IPRosClock($this->providerMock))
            ->setIp('1.1.1.1')
            ->setOptions(['apiKey' => 'secret']);

        $this->assertEquals($expectedDate, $clock->now());
    }
}
