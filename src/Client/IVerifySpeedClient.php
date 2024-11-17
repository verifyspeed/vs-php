<?php

namespace VerifySpeed\Client;

use VerifySpeed\Models\Initialization;

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
} 