<?php declare(strict_types=1);

namespace KrepyshSpec\IPros\Providers\IpApi;

use DateTimeImmutable;
use DateTimeZone;
use KrepyshSpec\IPros\AbstractProvider;
use KrepyshSpec\IPros\Enums\ProviderRequestMethodEnum;
use RuntimeException;

/**
 * IpApiProvider retrieves the current time based on IP address
 * using the ipapi.co API service.
 *
 * Example API call:
 * - https://ipapi.co/json/
 * - https://ipapi.co/8.8.8.8/json/
 */
final class IpApiProvider extends AbstractProvider
{
    /**
     * Returns the base API URL.
     *
     * @return string Base endpoint URL for ipapi.co
     */
    protected function getApiUrl(): string
    {
        return "https://ipapi.co/";
    }

    /**
     * Returns the HTTP method used for API requests.
     *
     * @return ProviderRequestMethodEnum HTTP method (GET)
     */
    protected function getRequestMethod(): ProviderRequestMethodEnum
    {
        return ProviderRequestMethodEnum::GET;
    }

    /**
     * Constructs the final API URL using the provided IP address (if available).
     *
     * @param string $apiUrl Base API URL.
     * @param array $options Request options such as 'ip'.
     * @return string Final API URL with or without IP address.
     */
    protected function prepareApiUrl(string $apiUrl, array $options): string
    {
        if (isset($options['ip'])) {
            return sprintf("$apiUrl/%s/json", $options['ip']);
        }

        return $apiUrl . 'json';
    }

    /**
     * Converts the API response into a DateTimeImmutable object using the returned timezone.
     *
     * @param array $response Decoded JSON response from the API.
     * @throws RuntimeException If timezone is not found in the response.
     * @return DateTimeImmutable Current time in the specified timezone.
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
