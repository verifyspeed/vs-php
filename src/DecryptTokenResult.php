<?php

namespace VerifySpeed;

class DecryptTokenResult
{
    public function __construct(
        private readonly string $phoneNumber,
        private readonly \DateTimeImmutable $dateOfVerification,
        private readonly string $methodName,
        private readonly string $verificationKey
    ) {}

    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    public function getDateOfVerification(): \DateTimeImmutable
    {
        return $this->dateOfVerification;
    }

    public function getMethodName(): string
    {
        return $this->methodName;
    }

    public function getVerificationKey(): string
    {
        return $this->verificationKey;
    }

    public function toArray(): array
    {
        return [
            'phoneNumber' => $this->phoneNumber,
            'dateOfVerification' => $this->dateOfVerification,
            'methodName' => $this->methodName,
            'verificationKey' => $this->verificationKey,
        ];
    }
}
