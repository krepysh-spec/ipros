<?php

namespace KrepyshSpec\IPros;

use DateTimeImmutable;
use InvalidArgumentException;
use Psr\Clock\ClockInterface;

/**
 * Custom clock implementation that gets time from an external provider.
 */
class IPRosClock implements ClockInterface
{
    /**
     * User-defined options (e.g., ['ip' => '8.8.8.8']).
     *
     * @var array
     */
    private ?array $options = [];

    /**
     * @param AbstractProvider $provider
     */
    public function __construct(private readonly AbstractProvider $provider)
    {
    }

    /**
     * Set a specific IP address to get time for.
     *
     * @param string $ip
     * @return $this
     */
    public function setIp(string $ip): IPRosClock
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new InvalidArgumentException(
                sprintf('Ip address %s is not valid', $ip)
            );
        }

        $this->options['ip'] = $ip;

        return $this;
    }

    /**
     * Set additional options (overwrites existing ones).
     *
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options): self
    {
        $this->options = array_merge($this->options, $options);

        return $this;
    }

    /**
     * Returns the current time based on the provider and options.
     *
     * @return DateTimeImmutable
     */
    public function now(): DateTimeImmutable
    {
        return $this->provider->getNowTime($this->options);
    }
}
