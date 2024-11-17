<?php

namespace VerifySpeed\Client;

use VerifySpeed\Enums\MethodType;
use VerifySpeed\Enums\VerificationType;
use VerifySpeed\Models\CreatedVerification;
use VerifySpeed\Models\Initialization;
use VerifySpeed\Models\VerificationResult;

interface IVerifySpeedClient
{
    /**
     * Initialize the verification process
     *
     * @param string $clientIPv4Address The IPv4 address of the client
     * @return Initialization
     * @throws \VerifySpeed\Exceptions\FailedInitializationException
     */
    public function initialize(string $clientIPv4Address): Initialization;

    /**
     * Create a new verification
     *
     * @param string $methodName The name of the verification method
     * @param string $clientIPv4Address The IPv4 address of the client
     * @param VerificationType $verificationType The type of verification
     * @param string|null $language Optional language code
     * @return CreatedVerification
     * @throws \VerifySpeed\Exceptions\FailedCreateVerificationException
     */
    public function createVerification(
        string $methodName,
        string $clientIPv4Address,
        VerificationType $verificationType,
        ?string $language = null
    ): CreatedVerification;

    /**
     * Create a new verification using a method type
     *
     * @param MethodType $methodType The verification method type
     * @param string $clientIPv4Address The IPv4 address of the client
     * @param VerificationType $verificationType The type of verification
     * @param string|null $language Optional language code
     * @return CreatedVerification
     * @throws \VerifySpeed\Exceptions\FailedCreateVerificationException
     */
    public function createVerificationWithMethodType(
        MethodType $methodType,
        string $clientIPv4Address,
        VerificationType $verificationType,
        ?string $language = null
    ): CreatedVerification;

    /**
     * Verify a token
     *
     * @param string $token The token to verify
     * @return VerificationResult
     * @throws \VerifySpeed\Exceptions\FailedVerifyingTokenException
     */
    public function verifyToken(string $token): VerificationResult;
} 