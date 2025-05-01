<?php

namespace KrepyshSpec\IPros;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;

class GeoTimeClock implements ClockInterface
{
    private readonly ?string $ip;

    public function __construct(private readonly AbstractProvider $provider)
    {
    }

    public function setIp(string $ip): GeoTimeClock
    {
        $this->ip = $ip;

        return $this;
    }

    public function now(): DateTimeImmutable
    {
        return $this->provider->getNowTime();
    }
}