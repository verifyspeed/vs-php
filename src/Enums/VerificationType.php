<?php

namespace VerifySpeed\Enums;

/**
 * Specifies the different types of verification.
 */
final class VerificationType
{
    /**
     * Represents a verification type using a deep link.
     */
    public const DeepLink = 'deep-link';

    /**
     * Represents a verification type using a QR code.
     */
    public const QRCode = 'qr-code';

    /**
     * Represents a verification type using a one-time password (OTP).
     */
    public const OTP = 'otp';

    private function __construct()
    {
        // Prevent instantiation
    }
} 