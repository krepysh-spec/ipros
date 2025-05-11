<?php declare(strict_types=1);

namespace KrepyshSpec\IPros\Interfaces;

use DateTimeImmutable;

interface ProviderInterface
{
    public function getNowTime(?array $options): DateTimeImmutable;
}