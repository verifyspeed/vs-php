<?php

namespace VerifySpeed\Enums;

/**
 * Specifies the different types of verification methods.
 */
final class MethodType
{
    /**
     * Represents a Telegram message verification method.
     */
    public const TelegramMessage = 'telegram-message';

    /**
     * Represents a WhatsApp message verification method.
     */
    public const WhatsAppMessage = 'whatsapp-message';

    /**
     * Represents an SMS OTP verification method.
     */
    public const SmsOtp = 'sms-otp';

    private function __construct()
    {
        // Prevent instantiation
    }
} 