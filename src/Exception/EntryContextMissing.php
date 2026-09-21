<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets\Exception;

/**
 * An entry declaration carries no context. There is no default, so the
 * declaration is refused rather than guessed at.
 */
final class EntryContextMissing extends \InvalidArgumentException implements MahoutException
{
    private function __construct(string $message, private readonly string $handle)
    {
        parent::__construct($message);
    }

    public static function forEntry(string $handle): self
    {
        return new self(
            \sprintf('The entry "%s" declares no context; front, admin or editor is required.', $handle),
            $handle,
        );
    }

    public function handle(): string
    {
        return $this->handle;
    }
}
