<?php

namespace KrepyshSpec\IPros\Tests;

use DateTimeImmutable;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use KrepyshSpec\IPros\AbstractProvider;
use KrepyshSpec\IPros\Enums\ProviderRequestMethodEnum;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AbstractProviderTest extends TestCase
{
    #[Test]
    public function testGetNowTimeReturnsParsedDateTime(): void
    {
        $mockResponse = new Response(200, [], json_encode(['dateTime' => '2024-05-01T12:00:00+00:00']));

        $clientMock = $this->createMock(Client::class);
        $clientMock->expects($this->once())
            ->method('get')
            ->with('https://example.com/api?test=true')
            ->willReturn($mockResponse);

        $provider = new class($clientMock) extends AbstractProvider {
            public function __construct(private Client $mockClient) {}

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

            protected function getClient(): Client
            {
                return $this->mockClient;
            }
        };

        $reflection = new \ReflectionClass($provider);
        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setAccessible(true);
        $clientProperty->setValue($provider, $clientMock);

        $result = $provider->getNowTime([]);
        $this->assertInstanceOf(DateTimeImmutable::class, $result);
        $this->assertEquals('2024-05-01 12:00:00', $result->format('Y-m-d H:i:s'));
    }
}
