<?php

namespace KrepyshSpec\IPros;

use DateTimeImmutable;
use GuzzleHttp\Client;
use Psr\Clock\ClockInterface;

abstract class AbstractProvider implements ClockInterface
{
    private Client $client;
    protected abstract function apiUrl();
    protected abstract function apiUrl();
    protected abstract function prepareRequest();
    protected abstract function prepareResponse();

    public function getNowTime(): DateTimeImmutable
    {
        return new DateTimeImmutable(date('d.m.Y'));
    }
}