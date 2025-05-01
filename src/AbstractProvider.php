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

abstract class AbstractProvider
{
    /**
     *  HTTP client for API requests.
     *
     * @var Client
     */
    private readonly Client $client;

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

    abstract protected function getRequestMethod(): ProviderRequestMethodEnum;

    /**
     * Prepares the API URL by inserting any required query parameters.
     *
     * @param string $apiUrl Base API URL.
     * @param array $options Additional parameters.
     * @return string|null Final API URL or null if cannot be prepared.
     */
    abstract protected function prepareApiUrl(string $apiUrl, array $options): string;

    /**
     * Parses the decoded API response into a DateTimeImmutable object.
     *
     * @param array $response Decoded JSON response.
     * @return DateTimeImmutable Parsed datetime.
     */
    abstract protected function prepareResponse(array $response): DateTimeImmutable;

    public function getNowTime(?array $options): DateTimeImmutable
    {
        try {

            $apiUrl = $this->getApiUrl();
            $apiUrl = $this->prepareApiUrl($apiUrl, $options);
            $requestMethod = $this->getRequestMethod()->value;

            $response = $this->client->{$requestMethod}($apiUrl);
            $data = $this->parseResponse($response);

            return $this->prepareResponse($data);

        } catch (RequestException $e) {
            throw new Exception('HTTP error from API: ' . $e->getMessage(), $e->getCode(), $e);
        } catch (GuzzleException $e) {
            // Інші помилки Guzzle (проблеми з мережею тощо)
            throw new Exception('Network error while fetching time: ' . $e->getMessage(), $e->getCode(), $e);
        } catch (Exception $e) {
            // Інші загальні помилки
            throw new RuntimeException('Unexpected error: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Decodes and validates the API response JSON.
     *
     * @param ResponseInterface $response Raw HTTP response.
     * @throws Exception If JSON is invalid.
     * @return array Decoded JSON as associative array.
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
