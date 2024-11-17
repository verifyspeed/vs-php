<?php

namespace VerifySpeed\Models;

class VerificationResult
{
    public function __construct(
        private readonly bool $isVerified,
        private readonly array $metadata
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            isVerified: $data['isVerified'],
            metadata: $data['metadata'] ?? []
        );
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }
} 