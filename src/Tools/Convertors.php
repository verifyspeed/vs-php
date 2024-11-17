<?php

namespace VerifySpeed\Tools;

use VerifySpeed\Constants\MethodNames;
use VerifySpeed\Enums\MethodType;
use VerifySpeed\Enums\VerificationType;

/**
 * Provides methods for converting between enums and their corresponding string values.
 */
class Convertors
{
    /**
     * Converts a verification type to its corresponding string value.
     *
     * @param string $verificationType The verification type to convert
     * @return string A string representing the verification type
     * @throws \InvalidArgumentException Thrown when an unsupported verification type is provided
     */
    public static function getVerificationTypeValue(string $verificationType): string
    {
        return match ($verificationType) {
            'DeepLink' => VerificationType::DeepLink,
            'QRCode' => VerificationType::QRCode,
            'OTP' => VerificationType::OTP,
            default => throw new \InvalidArgumentException("Invalid verification type provided: {$verificationType}")
        };
    }

    /**
     * Converts a string value to its corresponding verification type.
     *
     * @param string $value The string value to convert
     * @return string The corresponding verification type
     * @throws \InvalidArgumentException Thrown when an invalid verification value is provided
     */
    public static function getVerificationType(string $value): string
    {
        return match ($value) {
            VerificationType::DeepLink => 'DeepLink',
            VerificationType::QRCode => 'QRCode',
            VerificationType::OTP => 'OTP',
            default => throw new \InvalidArgumentException("Invalid verification value provided: {$value}")
        };
    }

    /**
     * Converts a method type to its corresponding string value.
     *
     * @param string $methodType The method type to convert
     * @return string A string representing the method type
     * @throws \InvalidArgumentException Thrown when an unsupported method type is provided
     */
    public static function getMethodName(string $methodType): string
    {
        return match ($methodType) {
            'TelegramMessage' => MethodNames::TelegramMessage,
            'WhatsAppMessage' => MethodNames::WhatsAppMessage,
            'SmsOtp' => MethodNames::SmsOtp,
            default => throw new \InvalidArgumentException("Invalid method type provided: {$methodType}")
        };
    }

    /**
     * Converts a string value to its corresponding method type.
     *
     * @param string $value The string value to convert
     * @return string The corresponding method type
     * @throws \InvalidArgumentException Thrown when an invalid method name value is provided
     */
    public static function getMethodType(string $value): string
    {
        return match ($value) {
            MethodNames::TelegramMessage => 'TelegramMessage',
            MethodNames::WhatsAppMessage => 'WhatsAppMessage',
            MethodNames::SmsOtp => 'SmsOtp',
            default => throw new \InvalidArgumentException("Invalid method name value provided: {$value}")
        };
    }
} 