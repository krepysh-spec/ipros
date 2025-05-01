<?php

namespace KrepyshSpec\IPros\Providers\IpApi;

use DateTimeImmutable;
use DateTimeZone;
use KrepyshSpec\IPros\AbstractProvider;
use KrepyshSpec\IPros\Enums\ProviderRequestMethodEnum;
use RuntimeException;

class IpApiProvider extends AbstractProvider
{
    /**
     * @return string
     */
    protected function getApiUrl(): string
    {
        return "https://ipapi.co/";
    }

    /**
     * @return ProviderRequestMethodEnum
     */
    protected function getRequestMethod(): ProviderRequestMethodEnum
    {
        return ProviderRequestMethodEnum::GET;
    }

    /**
     * @param string $apiUrl
     * @param array $options
     * @return string
     */
    protected function prepareApiUrl(string $apiUrl, array $options): string
    {
        if (isset($options['ip'])) {
            return sprintf("$apiUrl/%s/json", $options['ip']);
        }

        return $apiUrl . 'json';
    }

    /**
     * @param array $response
     * @return DateTimeImmutable
     */
    protected function prepareResponse(array $response): DateTimeImmutable
    {
        $timezone = $response['timezone'] ?? null;
        if (!$timezone) {
            throw new RuntimeException('Timezone not found in response.');
        }

        return new DateTimeImmutable('now', new DateTimeZone($timezone));
    }
}
