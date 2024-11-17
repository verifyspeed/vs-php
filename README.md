# VerifySpeed PHP SDK

Official PHP SDK for VerifySpeed API.

## Requirements

- PHP 8.1 or higher
- Composer

## Installation

You can install the package via composer: 

bash
composer require verifyspeed/php-sdk

## Usage

php
use VerifySpeed\Client\VerifySpeedClient;
use VerifySpeed\Enums\VerificationType;
$client = new VerifySpeedClient('your-api-key');
// Create a verification
$verification = $client->createVerification(
'method-name',
'127.0.0.1',
VerificationType::DeepLink
);

## Documentation

For detailed documentation, please visit [docs.verifyspeed.com](https://docs.verifyspeed.com)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.