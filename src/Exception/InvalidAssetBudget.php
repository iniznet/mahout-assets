<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets\Exception;

/**
 * An asset budget is zero or negative. A budget of zero is not a strict budget;
 * it is a declaration error.
 */
final class InvalidAssetBudget extends \OutOfRangeException implements MahoutException
{
    private function __construct(string $message, private readonly string $kind)
    {
        parent::__construct($message);
    }

    public static function forKind(string $kind, int $bytes): self
    {
        return new self(
            \sprintf('The %s budget must be a positive number of bytes, got %d.', $kind, $bytes),
            $kind,
        );
    }

    public function kind(): string
    {
        return $this->kind;
    }
}
