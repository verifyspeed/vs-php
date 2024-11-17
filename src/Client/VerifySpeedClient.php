<?php

namespace VerifySpeed\Client;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use VerifySpeed\Constants\LibraryConstants;
use VerifySpeed\Enums\MethodType;
use VerifySpeed\Enums\VerificationType;
use VerifySpeed\Exceptions\FailedCreateVerificationException;
use VerifySpeed\Exceptions\FailedInitializationException;
use VerifySpeed\Exceptions\FailedVerifyingTokenException;
use VerifySpeed\Models\CreatedVerification;
use VerifySpeed\Models\Initialization;
use VerifySpeed\Models\VerificationResult;
use VerifySpeed\Tools\Convertors;

class VerifySpeedClient implements IVerifySpeedClient
{
    private HttpClient $httpClient;

    public function __construct(private readonly string $serverKey)
    {
        $this->httpClient = new HttpClient([
            'base_uri' => LibraryConstants::API_BASE_URL,
            'headers' => [
                'server-key' => $this->serverKey,
                'Content-Type' => 'application/json',
            ]
        ]);
    }

    public function initialize(string $clientIPv4Address): Initialization
    {
        try {
            $response = $this->httpClient->get('v1/verifications/initialize', [
                'headers' => [
                    LibraryConstants::CLIENT_IPV4_ADDRESS_HEADER_NAME => $clientIPv4Address
                ]
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new FailedInitializationException(
                    "Failed to initialize, reason: {$response->getReasonPhrase()}"
                );
            }

            $content = $response->getBody()->getContents();
            $data = json_decode($content, true, flags: JSON_THROW_ON_ERROR);

            if ($data === null) {
                throw new FailedInitializationException('response content is null');
            }

            return Initialization::fromArray($data);
        } catch (\JsonException $e) {
            throw new FailedInitializationException(
                'Failed to deserialize the initialization response content',
                previous: $e
            );
        } catch (GuzzleException $e) {
            throw new FailedInitializationException(
                'Failed to initialize: ' . $e->getMessage(),
                previous: $e
            );
        }
    }

    public function createVerification(
        string $methodName,
        string $clientIPv4Address,
        VerificationType $verificationType,
        ?string $language = null
    ): CreatedVerification {
        try {
            $verificationTypeValue = Convertors::getVerificationTypeValue($verificationType);

            $response = $this->httpClient->post('v1/verifications/create', [
                'headers' => [
                    LibraryConstants::CLIENT_IPV4_ADDRESS_HEADER_NAME => $clientIPv4Address
                ],
                'json' => array_filter([
                    'methodName' => $methodName,
                    'verificationType' => $verificationTypeValue,
                    'language' => $language
                ], fn($value) => $value !== null)
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new FailedCreateVerificationException(
                    "Failed to create verification, reason: {$response->getReasonPhrase()}"
                );
            }

            $content = $response->getBody()->getContents();
            $data = json_decode($content, true, flags: JSON_THROW_ON_ERROR);

            if ($data === null) {
                throw new FailedCreateVerificationException('response content is null');
            }

            return CreatedVerification::fromArray($data);
        } catch (\JsonException $e) {
            throw new FailedCreateVerificationException(
                'Failed to deserialize the create verification response content',
                previous: $e
            );
        } catch (GuzzleException $e) {
            throw new FailedCreateVerificationException(
                'Failed to create verification: ' . $e->getMessage(),
                previous: $e
            );
        }
    }

    public function createVerificationWithMethodType(
        MethodType $methodType,
        string $clientIPv4Address,
        VerificationType $verificationType,
        ?string $language = null
    ): CreatedVerification {
        $methodName = Convertors::getMethodName($methodType);
        return $this->createVerification($methodName, $clientIPv4Address, $verificationType, $language);
    }

    public function verifyToken(string $token): VerificationResult
    {
        try {
            $response = $this->httpClient->get('v1/verifications/result', [
                'headers' => ['token' => $token]
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new FailedVerifyingTokenException(
                    "Failed to verify token, reason: {$response->getReasonPhrase()}"
                );
            }

            $content = $response->getBody()->getContents();
            $data = json_decode($content, true, flags: JSON_THROW_ON_ERROR);

            if ($data === null) {
                throw new FailedVerifyingTokenException('response content is null');
            }

            return VerificationResult::fromArray($data);
        } catch (\JsonException $e) {
            throw new FailedVerifyingTokenException(
                'Failed to deserialize the verification result response content',
                previous: $e
            );
        } catch (GuzzleException $e) {
            throw new FailedVerifyingTokenException(
                'Failed to verify token: ' . $e->getMessage(),
                previous: $e
            );
        }
    }
} 