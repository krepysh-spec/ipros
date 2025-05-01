<?php

use KrepyshSpec\IPros\GeoTimeClock;
use KrepyshSpec\IPros\Providers\WorldTime\WorldTimeProvider;

require_once 'vendor/autoload.php';

$a = new GeoTimeClock(new WorldTimeProvider());
var_dump($a->now());