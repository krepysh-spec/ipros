<?php

use KrepyshSpec\IPros\IPRosClock;
use KrepyshSpec\IPros\Providers\IpApi\IpApiProvider;

require_once 'vendor/autoload.php';

$clock = new IPRosClock(
    new IpApiProvider()
);

//$clock->setIp('128.0.0.1');

var_dump($clock->now());