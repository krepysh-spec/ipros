<?php

namespace KrepyshSpec\IPros\Tests;

use DateTimeImmutable;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use JsonException;
use Psr\Http\Message\RequestInterface;
use KrepyshSpec\IPros\AbstractProvider;
use KrepyshSpec\IPros\Enums\ProviderRequestMethodEnum;
use KrepyshSpec\IPros\Exceptions\ProviderRequestException;
use KrepyshSpec\IPros\Exceptions\ProviderResponseParseException;
use KrepyshSpec\IPros\Exceptions\ProviderUnexpectedException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AbstractProviderTest extends TestCase
{
    #[Test]
    public function testGetNowTimeReturnsParsedDateTime(): void
    {
        $mockResponse = new Response(
            200,
            [],
            json_encode(['dateTime' => '2024-05-01T12:00:00+00:00'])
        );

        $clientMock = $this->createMock(Client::class);
        $clientMock->expects($this->once())
            ->method('get')
            ->with('https://example.com/api?test=true')
            ->willReturn($mockResponse);

        $provider = new class extends AbstractProvider {
            protected function getApiUrl(): string
            {
                return 'https://example.com/api';
            }

            protected function getRequestMethod(): ProviderRequestMethodEnum
            {
                return ProviderRequestMethodEnum::GET;
            }

            protected function prepareApiUrl(string $apiUrl, array $options): string
            {
                return $apiUrl . '?test=true';
            }

            protected function prepareResponse(array $response): DateTimeImmutable
            {
                return new DateTimeImmutable($response['dateTime']);
            }
        };

        $reflection = new \ReflectionClass(AbstractProvider::class);
        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setAccessible(true);
        $clientProperty->setValue($provider, $clientMock);

        $result = $provider->getNowTime([]);

        $this->assertInstanceOf(DateTimeImmutable::class, $result);
        $this->assertSame(
            '2024-05-01 12:00:00',
            $result->format('Y-m-d H:i:s')
        );
    }

    #[Test]
    public function testGetNowTimeWithNullOptions(): void
    {
        $mockResponse = new Response(
            200,
            [],
            json_encode(['dateTime' => '2024-05-01T12:00:00+00:00'])
        );

        $optionsReceived = null;
        $clientMock = $this->createMock(Client::class);
        $clientMock->expects($this->once())
            ->method('get')
            ->with('https://example.com/api')
            ->willReturn($mockResponse);

        $provider = new class extends AbstractProvider {
            public ?array $capturedOptions = null;

            protected function getApiUrl(): string
            {
                return 'https://example.com/api';
            }

            protected function getRequestMethod(): ProviderRequestMethodEnum
            {
                return ProviderRequestMethodEnum::GET;
            }

            protected function prepareApiUrl(string $apiUrl, array $options): string
            {
                $this->capturedOptions = $options;
                return $apiUrl;
            }

            protected function prepareResponse(array $response): DateTimeImmutable
            {
                return new DateTimeImmutable($response['dateTime']);
            }
        };

        $reflection = new \ReflectionClass(AbstractProvider::class);
        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setAccessible(true);
        $clientProperty->setValue($provider, $clientMock);

        $result = $provider->getNowTime(null);

        $this->assertInstanceOf(DateTimeImmutable::class, $result);
        // Verify that null options were converted to empty array
        $this->assertSame([], $provider->capturedOptions);
    }

    #[Test]
    public function testGetNowTimeThrowsProviderRequestExceptionOnRequestException(): void
    {
        $requestException = new RequestException('Request failed', $this->createMock(RequestInterface::class));

        $clientMock = $this->createMock(Client::class);
        $clientMock->expects($this->once())
            ->method('get')
            ->willThrowException($requestException);

        $provider = new class extends AbstractProvider {
            protected function getApiUrl(): string
            {
                return 'https://example.com/api';
            }

            protected function getRequestMethod(): ProviderRequestMethodEnum
            {
                return ProviderRequestMethodEnum::GET;
            }

            protected function prepareApiUrl(string $apiUrl, array $options): string
            {
                return $apiUrl;
            }

            protected function prepareResponse(array $response): DateTimeImmutable
            {
                return new DateTimeImmutable();
            }
        };

        $reflection = new \ReflectionClass(AbstractProvider::class);
        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setAccessible(true);
        $clientProperty->setValue($provider, $clientMock);

        $this->expectException(ProviderRequestException::class);
        $this->expectExceptionMessage('Error while making request to Provider: Request failed');

        $provider->getNowTime([]);
    }

    #[Test]
    public function testGetNowTimeThrowsProviderRequestExceptionOnGuzzleException(): void
    {
        $guzzleException = new RequestException('Guzzle error', $this->createMock(RequestInterface::class));

        $clientMock = $this->createMock(Client::class);
        $clientMock->expects($this->once())
            ->method('get')
            ->willThrowException($guzzleException);

        $provider = new class extends AbstractProvider {
            protected function getApiUrl(): string
            {
                return 'https://example.com/api';
            }

            protected function getRequestMethod(): ProviderRequestMethodEnum
            {
                return ProviderRequestMethodEnum::GET;
            }

            protected function prepareApiUrl(string $apiUrl, array $options): string
            {
                return $apiUrl;
            }

            protected function prepareResponse(array $response): DateTimeImmutable
            {
                return new DateTimeImmutable();
            }
        };

        $reflection = new \ReflectionClass(AbstractProvider::class);
        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setAccessible(true);
        $clientProperty->setValue($provider, $clientMock);

        $this->expectException(ProviderRequestException::class);
        $this->expectExceptionMessage('Error while making request to Provider: Guzzle error');

        $provider->getNowTime([]);
    }

    #[Test]
    public function testGetNowTimeThrowsProviderResponseParseExceptionOnJsonException(): void
    {
        // Create a response with invalid JSON
        $mockResponse = new Response(
            200,
            [],
            'invalid json {'
        );

        $clientMock = $this->createMock(Client::class);
        $clientMock->expects($this->once())
            ->method('get')
            ->willReturn($mockResponse);

        $provider = new class extends AbstractProvider {
            protected function getApiUrl(): string
            {
                return 'https://example.com/api';
            }

            protected function getRequestMethod(): ProviderRequestMethodEnum
            {
                return ProviderRequestMethodEnum::GET;
            }

            protected function prepareApiUrl(string $apiUrl, array $options): string
            {
                return $apiUrl;
            }

            protected function prepareResponse(array $response): DateTimeImmutable
            {
                return new DateTimeImmutable();
            }
        };

        $reflection = new \ReflectionClass(AbstractProvider::class);
        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setAccessible(true);
        $clientProperty->setValue($provider, $clientMock);

        $this->expectException(ProviderResponseParseException::class);
        $this->expectExceptionMessage('Failed to parse API response:');

        $provider->getNowTime([]);
    }

    #[Test]
    public function testGetNowTimeThrowsProviderUnexpectedExceptionOnGenericException(): void
    {
        $genericException = new Exception('Unexpected error', 123);

        $clientMock = $this->createMock(Client::class);
        $clientMock->expects($this->once())
            ->method('get')
            ->willThrowException($genericException);

        $provider = new class extends AbstractProvider {
            protected function getApiUrl(): string
            {
                return 'https://example.com/api';
            }

            protected function getRequestMethod(): ProviderRequestMethodEnum
            {
                return ProviderRequestMethodEnum::GET;
            }

            protected function prepareApiUrl(string $apiUrl, array $options): string
            {
                return $apiUrl;
            }

            protected function prepareResponse(array $response): DateTimeImmutable
            {
                return new DateTimeImmutable();
            }
        };

        $reflection = new \ReflectionClass(AbstractProvider::class);
        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setAccessible(true);
        $clientProperty->setValue($provider, $clientMock);

        $this->expectException(ProviderUnexpectedException::class);
        $this->expectExceptionMessage('Unexpected error: Unexpected error');

        $provider->getNowTime([]);
    }

    #[Test]
    public function testGetNowTimeHandlesDeeplyNestedJson(): void
    {
        $deeplyNested = [];
        $current = &$deeplyNested;
        for ($i = 0; $i < 100; $i++) {
            $current['nested'] = [];
            $current = &$current['nested'];
        }
        $current['dateTime'] = '2024-05-01T12:00:00+00:00';

        $mockResponse = new Response(
            200,
            [],
            json_encode($deeplyNested)
        );

        $clientMock = $this->createMock(Client::class);
        $clientMock->expects($this->once())
            ->method('get')
            ->willReturn($mockResponse);

        $provider = new class extends AbstractProvider {
            protected function getApiUrl(): string
            {
                return 'https://example.com/api';
            }

            protected function getRequestMethod(): ProviderRequestMethodEnum
            {
                return ProviderRequestMethodEnum::GET;
            }

            protected function prepareApiUrl(string $apiUrl, array $options): string
            {
                return $apiUrl;
            }

            protected function prepareResponse(array $response): DateTimeImmutable
            {
                // Navigate through nested structure
                $current = $response;
                while (isset($current['nested'])) {
                    $current = $current['nested'];
                }
                return new DateTimeImmutable($current['dateTime']);
            }
        };

        $reflection = new \ReflectionClass(AbstractProvider::class);
        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setAccessible(true);
        $clientProperty->setValue($provider, $clientMock);

        $result = $provider->getNowTime([]);

        $this->assertInstanceOf(DateTimeImmutable::class, $result);
        $this->assertSame(
            '2024-05-01 12:00:00',
            $result->format('Y-m-d H:i:s')
        );
    }

}
