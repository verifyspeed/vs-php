<?php

namespace VerifySpeed\Models;

class Initialization
{
    public function __construct(
        private readonly array $availableMethods
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            availableMethods: $data['availableMethods'] ?? []
        );
    }

    public function getAvailableMethods(): array
    {
        return $this->availableMethods;
    }
} 