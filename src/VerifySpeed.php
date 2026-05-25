<?php

namespace VerifySpeed;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\NumberParseException;

class VerifySpeed
{
    private const OTP_METHODS = ['whatsapp-otp', 'telegram-otp', 'sms-otp'];

    private static ?string $serverKey = null;
    private static string $baseUrl = 'https://api.verifyspeed.com/v1/';
    private static ?HttpClient $httpClient = null;

    private static function getHttpClient(): HttpClient
    {
        if (self::$httpClient === null) {
            self::$httpClient = new HttpClient([
                'base_uri' => self::$baseUrl,
                'headers' => ['Content-Type' => 'application/json'],
            ]);
        }
        return self::$httpClient;
    }

    public static function getServerKey(): ?string
    {
        return self::$serverKey;
    }

    public static function setServerKey(string $serverKey): void
    {
        if (!$serverKey) {
            throw new \InvalidArgumentException('Server key is required');
        }

        self::$serverKey = $serverKey;
    }

    private static function validateServerKey(): void
    {
        if (!self::$serverKey || trim(self::$serverKey) === '') {
            throw new \RuntimeException('Server key not set. Call setServerKey first.');
        }
    }

    private static function isOtpMethod(string $methodName): bool
    {
        return in_array($methodName, self::OTP_METHODS, true);
    }

    private static function normalizePhoneNumber(string $phoneNumber): string
    {
        try {
            $phoneUtil = PhoneNumberUtil::getInstance();
            $parsedNumber = $phoneUtil->parse($phoneNumber);

            if (!$phoneUtil->isValidNumber($parsedNumber)) {
                throw new \InvalidArgumentException(
                    'Invalid phone number format. Please use E.164 format (e.g., +1234567890)'
                );
            }

            return $phoneUtil->format($parsedNumber, \libphonenumber\PhoneNumberFormat::E164);
        } catch (NumberParseException $error) {
            throw new \InvalidArgumentException('Invalid phone number: ' . $error->getMessage());
        }
    }

    public static function createVerification(
        string $methodName,
        string $clientIpv4Address,
        ?string $language = null,
        ?string $phoneNumber = null
    ): VerificationResult {
        self::validateServerKey();

        if (!$methodName) {
            throw new \InvalidArgumentException('Method name is required');
        }

        if (!$clientIpv4Address) {
            throw new \InvalidArgumentException('Client IPv4 address is required');
        }

        if (self::isOtpMethod($methodName)) {
            if (!$phoneNumber || trim($phoneNumber) === '') {
                throw new \InvalidArgumentException(
                    'Phone number is required for OTP verification methods (whatsapp-otp, telegram-otp, sms-otp)'
                );
            }
            $phoneNumber = self::normalizePhoneNumber($phoneNumber);
        }

        $body = ['methodName' => $methodName];
        if ($language !== null) {
            $body['language'] = $language;
        }
        if ($phoneNumber !== null) {
            $body['phoneNumber'] = $phoneNumber;
        }

        try {
            $response = self::getHttpClient()->post('verifications/create', [
                'headers' => [
                    'server-key' => self::$serverKey,
                    'client-ipv4-address' => $clientIpv4Address,
                ],
                'json' => $body,
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new \RuntimeException(
                    "HTTP error! status: {$response->getStatusCode()} {$response->getReasonPhrase()}"
                );
            }

            $data = json_decode($response->getBody()->getContents(), true);
            return VerificationResult::fromArray($data);
        } catch (GuzzleException $error) {
            throw new \RuntimeException("Failed to create verification: {$error->getMessage()}");
        }
    }

    public static function validateOtp(
        string $code,
        string $verificationKey
    ): ValidateOtpResult {
        self::validateServerKey();

        if (trim($code) === '') {
            throw new \InvalidArgumentException('OTP code is required');
        }

        if (strlen($code) > 5) {
            throw new \InvalidArgumentException('OTP code must be at most 5 characters');
        }

        if (trim($verificationKey) === '') {
            throw new \InvalidArgumentException('Verification key is required');
        }

        try {
            $response = self::getHttpClient()->post('verifications/validate-otp', [
                'headers' => [
                    'server-key' => self::$serverKey,
                ],
                'json' => [
                    'code' => $code,
                    'verificationKey' => $verificationKey,
                ],
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new \RuntimeException(
                    "HTTP error! status: {$response->getStatusCode()} {$response->getReasonPhrase()}"
                );
            }

            $data = json_decode($response->getBody()->getContents(), true);
            return ValidateOtpResult::fromArray($data);
        } catch (GuzzleException $error) {
            throw new \RuntimeException("Failed to validate OTP: {$error->getMessage()}");
        }
    }
}

class VerificationResult
{
    public function __construct(
        private readonly string $methodName,
        private readonly string $verificationKey,
        private readonly ?string $deepLink
    ) {}

    public function getMethodName(): string
    {
        return $this->methodName;
    }

    public function getVerificationKey(): string
    {
        return $this->verificationKey;
    }

    public function getDeepLink(): ?string
    {
        return $this->deepLink;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            methodName: $data['methodName'],
            verificationKey: $data['verificationKey'],
            deepLink: $data['deepLink'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'methodName' => $this->methodName,
            'verificationKey' => $this->verificationKey,
            'deepLink' => $this->deepLink,
        ];
    }
}

class ValidateOtpResult
{
    public function __construct(
        private readonly bool $succeed,
        private readonly ?string $token,
        private readonly ?string $errorMessage,
        private readonly ?string $errorCode
    ) {}

    public function getSucceed(): bool
    {
        return $this->succeed;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            succeed: (bool) ($data['succeed'] ?? false),
            token: $data['token'] ?? null,
            errorMessage: $data['errorMessage'] ?? null,
            errorCode: $data['errorCode'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'succeed' => $this->succeed,
            'token' => $this->token,
            'errorMessage' => $this->errorMessage,
            'errorCode' => $this->errorCode,
        ];
    }
}
