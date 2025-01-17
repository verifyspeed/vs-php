<?php

namespace VerifySpeed;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\NumberParseException;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;

class VerifySpeed
{
    private static ?string $clientKey = null;
    private static string $baseUrl = 'https://api.verifyspeed.com/v1/';
    private static ?HttpClient $httpClient = null;

    private static function getHttpClient(): HttpClient
    {
        if (self::$httpClient === null) {
            $headers = ['Content-Type' => 'application/json'];
            if (self::$clientKey) {
                $headers['client-key'] = self::$clientKey;
            }

            self::$httpClient = new HttpClient([
                'base_uri' => self::$baseUrl,
                'headers' => $headers
            ]);
        }
        return self::$httpClient;
    }

    public static function getClientKey(): ?string
    {
        return self::$clientKey;
    }

    public static function setClientKey(string $clientKey): void
    {
        if (!$clientKey) {
            throw new \InvalidArgumentException('Client key is required');
        }

        self::$clientKey = $clientKey;
        self::$httpClient = null;
    }

    private static function validateClientKey(): void
    {
        if (!self::$clientKey || trim(self::$clientKey) === '') {
            throw new \RuntimeException('Client key not set. Call setClientKey first.');
        }
    }

    public static function initialize(): array
    {
        self::validateClientKey();

        try {
            $response = self::getHttpClient()->get('sdk/initialize');
            
            if ($response->getStatusCode() !== 200) {
                throw new \RuntimeException(
                    "HTTP error! status: {$response->getStatusCode()} {$response->getReasonPhrase()}"
                );
            }

            $data = json_decode($response->getBody()->getContents(), true);
            return array_map(
                fn(array $method) => VerificationMethod::fromArray($method),
                $data['availableMethods']
            );
        } catch (GuzzleException $error) {
            throw new \RuntimeException("Failed to initialize: {$error->getMessage()}");
        }
    }

    public static function getVerificationToken(string $verificationKey): string
    {
        try {
            $response = self::getHttpClient()->get('sdk/token', [
                'headers' => [
                    'verification-key' => $verificationKey,
                    'platform' => 'web'
                ]
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new \RuntimeException(
                    "HTTP error! status: {$response->getStatusCode()} {$response->getReasonPhrase()}"
                );
            }

            $data = json_decode($response->getBody()->getContents(), true);
            return $data['token'];
        } catch (GuzzleException $error) {
            throw new \RuntimeException("Failed to get verification token: {$error->getMessage()}");
        }
    }

    public static function convertDeepLinkToQRCode(
        string $deepLink,
        ?QRCodeOptions $options = null
    ): string {
        if (!$deepLink) {
            throw new \InvalidArgumentException('Deep link is required');
        }

        try {
            $options ??= new QRCodeOptions();
      
            // Default colors
            $backgroundColor = [255, 255, 255]; // white
            $foregroundColor = [0, 0, 0]; // black

            try {
                $backgroundColorHex = substr($options->getBackgroundColor(), 1);
                $foregroundColorHex = substr($options->getForegroundColor(), 1);

                // Convert hex to RGB components
                $backgroundColor = [
                    hexdec(substr($backgroundColorHex, 0, 2)),
                    hexdec(substr($backgroundColorHex, 2, 2)), 
                    hexdec(substr($backgroundColorHex, 4, 2))
                ];

                $foregroundColor = [
                    hexdec(substr($foregroundColorHex, 0, 2)),
                    hexdec(substr($foregroundColorHex, 2, 2)),
                    hexdec(substr($foregroundColorHex, 4, 2))
                ];
            } catch (\Exception $e) {
                // Silently ignore color parsing errors and use defaults
            }

            // Create basic QR code
            $qrCode = new QrCode(
                data: $deepLink,
                size: $options->getWidth(),
                margin: $options->getMargin(),
                foregroundColor: new Color(
                    $foregroundColor[0],
                    $foregroundColor[1], 
                    $foregroundColor[2]
                ),
                backgroundColor: new Color(
                    $backgroundColor[0],
                    $backgroundColor[1],
                    $backgroundColor[2]
                )
            );

            $writer = new PngWriter();
            $result = $writer->write($qrCode);

            return $result->getDataUri();
        } catch (\Exception $error) {
            throw new \RuntimeException("Failed to convert deep link to QR code: {$error->getMessage()}");
        }
    }

    public static function sendOTP(string $verificationKey, string $phoneNumber): void
    {
        if (!$verificationKey) {
            throw new \InvalidArgumentException('Verification key is required');
        }
        if (!$phoneNumber) {
            throw new \InvalidArgumentException('Phone number is required');
        }

        try {
            $phoneUtil = PhoneNumberUtil::getInstance();
            $parsedNumber = $phoneUtil->parse($phoneNumber);
            
            if (!$phoneUtil->isValidNumber($parsedNumber)) {
                throw new \InvalidArgumentException(
                    'Invalid phone number format. Please use E.164 format (e.g., +1234567890)'
                );
            }

            $normalizedNumber = $phoneUtil->format($parsedNumber, \libphonenumber\PhoneNumberFormat::E164);

            $response = self::getHttpClient()->post('sdk/send-otp', [
                'headers' => [
                    'verification-key' => $verificationKey
                ],
                'json' => ['phoneNumber' => $normalizedNumber]
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new \RuntimeException(
                    "HTTP error! status: {$response->getStatusCode()} {$response->getReasonPhrase()}"
                );
            }
        } catch (NumberParseException $error) {
            throw new \InvalidArgumentException('Invalid phone number: ' . $error->getMessage());
        } catch (GuzzleException $error) {
            throw new \RuntimeException("Failed to send OTP: {$error->getMessage()}");
        }
    }

    public static function validateOTP(string $verificationKey, string $code): string
    {
        if (!$verificationKey) {
            throw new \RuntimeException('Verification key is required');
        }

        if (!$code) {
            throw new \InvalidArgumentException('OTP code is required');
        }

        try {
            $response = self::getHttpClient()->post('sdk/validate-otp', [
                'headers' => [
                    'verification-key' => $verificationKey
                ],
                'json' => ['code' => $code]
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new \RuntimeException(
                    "HTTP error! status: {$response->getStatusCode()} {$response->getReasonPhrase()}"
                );
            }

            $data = json_decode($response->getBody()->getContents(), true);
            return $data['token'];
        } catch (GuzzleException $error) {
            throw new \RuntimeException("Failed to validate OTP: {$error->getMessage()}");
        }
    }
} 

class VerificationMethod
{
    public function __construct(
        private readonly string $methodName,
        private readonly string $displayName
    ) {}

    public function getMethodName(): string
    {
        return $this->methodName;
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            methodName: $data['methodName'],
            displayName: $data['displayName']
        );
    }

    public function toArray(): array
    {
        return [
            'methodName' => $this->methodName,
            'displayName' => $this->displayName
        ];
    }
} 

class QRCodeOptions
{
    public function __construct(
        private readonly ?int $width = 300,
        private readonly ?int $margin = 2,
        private readonly ?string $foregroundColor = '#000000',
        private readonly ?string $backgroundColor = '#ffffff',
        private readonly ?string $errorCorrectionLevel = 'M',
        private readonly ?array $centerImage = null
    ) {}

    public function getWidth(): int
    {
        return $this->width ?? 300;
    }

    public function getMargin(): int
    {
        return $this->margin ?? 2;
    }

    public function getForegroundColor(): string
    {
        return $this->foregroundColor ?? '#000000';
    }

    public function getBackgroundColor(): string
    {
        return $this->backgroundColor ?? '#ffffff';
    }

    public function getErrorCorrectionLevel(): string
    {
        return $this->errorCorrectionLevel ?? 'M';
    }

    public function getCenterImage(): ?array
    {
        return $this->centerImage;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            width: $data['width'] ?? null,
            margin: $data['margin'] ?? null,
            foregroundColor: $data['foregroundColor'] ?? null,
            backgroundColor: $data['backgroundColor'] ?? null,
            errorCorrectionLevel: $data['errorCorrectionLevel'] ?? null,
            centerImage: $data['centerImage'] ?? null
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'width' => $this->width,
            'margin' => $this->margin,
            'foregroundColor' => $this->foregroundColor,
            'backgroundColor' => $this->backgroundColor,
            'errorCorrectionLevel' => $this->errorCorrectionLevel,
            'centerImage' => $this->centerImage
        ], fn($value) => $value !== null);
    }
} 