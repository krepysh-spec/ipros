<?php

namespace KrepyshSpec\IPros;

use DateTimeImmutable;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use KrepyshSpec\IPros\Enums\ProviderRequestMethodEnum;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

/**
 * AbstractProvider is a base class for implementing time providers that fetch
 * the current time from external HTTP APIs based on IP address or other parameters.
 */
abstract class AbstractProvider
{
    /**
     *  HTTP client for API requests.
     *
     * @var Client
     */
    protected Client $client;

    /**
     * Constructor with Guzzle client injection.
     */
    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * Returns the base URL for the API.
     *
     * @return string
     */
    abstract protected function getApiUrl(): string;

    /**
     * Returns the HTTP method (GET, POST, etc.) used for the API request.
     *
     * @return ProviderRequestMethodEnum
     */
    abstract protected function getRequestMethod(): ProviderRequestMethodEnum;

    /**
     * Constructs the full API URL using base URL and additional request options.
     *
     * @param string $apiUrl Base API URL.
     * @param array $options Additional parameters like 'ip' or 'apiKey'.
     * @return string Final API URL with query or path parameters.
     */
    abstract protected function prepareApiUrl(string $apiUrl, array $options): string;

    /**
     * Converts the decoded JSON API response into a DateTimeImmutable object.
     *
     * @param array $response Parsed API response as associative array.
     * @return DateTimeImmutable Parsed date and time.
     */
    abstract protected function prepareResponse(array $response): DateTimeImmutable;

    /**
     * Gets the current time by sending a request to the external API.
     *
     * @param array|null $options Optional parameters such as IP address or API key.
     * @throws Exception|RuntimeException If the request fails or response is invalid.
     * @return DateTimeImmutable The current time returned by the provider.
     */
    public function getNowTime(?array $options): DateTimeImmutable
    {
        try {

            $apiUrl = $this->getApiUrl();
            $apiUrl = $this->prepareApiUrl($apiUrl, $options);
            $requestMethod = $this->getRequestMethod()->value;

            // Dynamically call the method (e.g., $client->get($url))
            $response = $this->client->{$requestMethod}($apiUrl);
            $data = $this->parseResponse($response);

            return $this->prepareResponse($data);

        } catch (RequestException $e) {
            throw new Exception('HTTP error from API: ' . $e->getMessage(), $e->getCode(), $e);
        } catch (GuzzleException $e) {
            throw new Exception('Network error while fetching time: ' . $e->getMessage(), $e->getCode(), $e);
        } catch (Exception $e) {
            throw new RuntimeException('Unexpected error: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Decodes the JSON response body into an associative array.
     *
     * @param ResponseInterface $response HTTP response from the API.
     * @throws Exception If JSON cannot be parsed.
     * @return array Decoded JSON data.
     */
    private function parseResponse(ResponseInterface $response): array
    {
        $body = (string) $response->getBody();

        $decoded = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response from API: ' . json_last_error_msg());
        }

        return $decoded;
    }
}
