<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets\Exception;

/**
 * An entry declaration names a context that does not exist.
 */
final class EntryContextUnknown extends \InvalidArgumentException implements MahoutException
{
    private function __construct(string $message, private readonly string $value)
    {
        parent::__construct($message);
    }

    public static function forValue(string $value, string $handle): self
    {
        return new self(
            \sprintf('The entry "%s" names the unknown context "%s".', $handle, $value),
            $value,
        );
    }

    public function value(): string
    {
        return $this->value;
    }
}
