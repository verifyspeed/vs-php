<?php

namespace VerifySpeed\Client;

use GuzzleHttp\Client as HttpClient;
use VerifySpeed\Constants\LibraryConstants;
use VerifySpeed\Exceptions\FailedInitializationException;
use VerifySpeed\Models\Initialization;

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
            $data = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new FailedInitializationException(
                    'Failed to deserialize the initialization response content'
                );
            }

            return Initialization::fromArray($data);
        } catch (\Exception $e) {
            throw new FailedInitializationException(
                'Failed to initialize: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }
} 