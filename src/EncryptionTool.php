<?php

namespace VerifySpeed;

class EncryptionTool
{
    private const TOKEN_MAX_AGE_MINUTES = 5;

    /**
     * Verifies and decrypts an encrypted verification token using the specified server key.
     *
     * @throws \InvalidArgumentException When the token is invalid, corrupted, or expired
     * @throws \RuntimeException When decryption fails
     */
    public static function verifyVerificationToken(string $token, string $serverKey): DecryptTokenResult
    {
        return self::decryptToken($token, $serverKey);
    }

    /**
     * Decrypts an encrypted verification token using the specified server key.
     *
     * @throws \InvalidArgumentException When the token is invalid, corrupted, or expired
     * @throws \RuntimeException When decryption fails
     */
    public static function decryptToken(string $token, string $serverKey): DecryptTokenResult
    {
        if (trim($token) === '') {
            throw new \InvalidArgumentException('The verification token cannot be null or empty');
        }

        if (trim($serverKey) === '') {
            throw new \InvalidArgumentException('Server key is required');
        }

        try {
            $decrypted = self::decrypt($token, $serverKey);
        } catch (\RuntimeException $error) {
            throw new \RuntimeException('Failed to decrypt the verification token', 0, $error);
        }

        $parts = explode('|', $decrypted);

        if (count($parts) < 4) {
            throw new \InvalidArgumentException('The token format is invalid or corrupted');
        }

        $phoneNumber = $parts[0] !== '' ? $parts[0] : null;
        if ($phoneNumber === null) {
            throw new \InvalidArgumentException('The phone number part of the token is missing');
        }

        try {
            $dateOfVerification = new \DateTimeImmutable($parts[1]);
        } catch (\Exception) {
            throw new \InvalidArgumentException('The date of verification part of the token is invalid');
        }

        $methodName = $parts[2] !== '' ? $parts[2] : null;
        if ($methodName === null) {
            throw new \InvalidArgumentException('The method name part of the token is missing');
        }

        $verificationKey = $parts[3] !== '' ? $parts[3] : null;
        if ($verificationKey === null) {
            throw new \InvalidArgumentException('The verification key part of the token is missing');
        }

        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        if ($dateOfVerification->modify('+' . self::TOKEN_MAX_AGE_MINUTES . ' minutes') < $now) {
            throw new \InvalidArgumentException('The verification token has expired');
        }

        return new DecryptTokenResult(
            phoneNumber: $phoneNumber,
            dateOfVerification: $dateOfVerification,
            methodName: $methodName,
            verificationKey: $verificationKey
        );
    }

    private static function decrypt(string $token, string $serverKey): string
    {
        $cipherBytes = base64_decode($token, true);
        if ($cipherBytes === false) {
            throw new \RuntimeException('Decryption failed');
        }

        $key = hash('sha256', $serverKey, true);

        if (strlen($cipherBytes) < 16) {
            throw new \RuntimeException('Decryption failed');
        }

        $iv = substr($cipherBytes, 0, 16);
        $encryptedData = substr($cipherBytes, 16);

        $decrypted = openssl_decrypt($encryptedData, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

        if ($decrypted === false) {
            throw new \RuntimeException('Decryption failed');
        }

        return $decrypted;
    }
}
