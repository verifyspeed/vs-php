<?php

namespace VerifySpeed\Models;

class CreatedVerification
{
    public function __construct(
        private readonly string $id,
        private readonly string $methodName,
        private readonly string $verificationType,
        private readonly ?string $language,
        private readonly array $metadata
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            methodName: $data['methodName'],
            verificationType: $data['verificationType'],
            language: $data['language'] ?? null,
            metadata: $data['metadata'] ?? []
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMethodName(): string
    {
        return $this->methodName;
    }

    public function getVerificationType(): string
    {
        return $this->verificationType;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }
} 