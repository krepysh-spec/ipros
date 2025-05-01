# IPRosClock — External Time Provider via IP Address

[![PHP](https://img.shields.io/badge/PHP-8.1%2B-blue)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)


IPRosClock is a PHP library that implements `Psr\Clock\ClockInterface` and retrieves the current time using external time or geo-IP providers such as [ipapi.co](https://ipapi.co/).

## 🚀 Features

- PSR-compatible ClockInterface
- Get current time based on IP address
- Easily switchable external providers
- Strict IP validation
- Extendable provider abstraction

---

## 📦 Installation

```bash
composer require krepysh-spec/ipros
```

## 🧑‍💻 Usage

### 1. Create a Provider

You can use a built-in provider like IpApiProvider, or create your own by extending AbstractProvider.
```php
 
use KrepyshSpec\IPros\IPRosClock;
use KrepyshSpec\IPros\Providers\IpApi\IpApiProvider;

$clock = new IPRosClock(
    new IpApiProvider()
);
 
```
### 2. Get Time for Current IP

```php
$now = $clock->now();
echo $now->format('Y-m-d H:i:s');
````

### 3. Set Custom IP Address

```php
$clock->setIp('127.0.0.1');
echo $clock->now()->format(DateTimeInterface::RFC3339);
````

### 4. Set Custom Options (e.g. API Key, Region)
You can pass any custom options required by your provider using setOptions():


```php
$clock->setOptions([
    'ip' => '8.8.8.8',
    'apiKey' => 'your_api_key_here',
    'lang' => 'en'
]);

echo $clock->now()->format('c');
````

## 🧩 Providers
You can define a custom provider like this:

```php
use KrepyshSpec\IPros\AbstractProvider;
use KrepyshSpec\IPros\Enums\ProviderRequestMethodEnum;
use DateTimeImmutable;

class MyProvider extends AbstractProvider
{
    protected function getApiUrl(): string
    {
        return 'https://my-api.com/time';
    }

    protected function getRequestMethod(): ProviderRequestMethodEnum
    {
        return ProviderRequestMethodEnum::GET;
    }

    protected function prepareApiUrl(string $apiUrl, array $data): ?string
    {
        return $apiUrl . '?ip=' . ($data['ip'] ?? '');
    }

    protected function prepareResponse(array $response): DateTimeImmutable
    {
        return new DateTimeImmutable($response['dateTime']);
    }
}
```

## ✅ Requirements

- PHP 8.1+
- Composer
