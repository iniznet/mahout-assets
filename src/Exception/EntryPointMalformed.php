<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets\Exception;

/**
 * An entry declaration is incomplete in some way other than its context.
 */
final class EntryPointMalformed extends \InvalidArgumentException implements MahoutException
{
    private function __construct(string $message, private readonly string $handle)
    {
        parent::__construct($message);
    }

    public static function missingHandle(): self
    {
        return new self('An entry declaration carries no non-empty handle.', '');
    }

    public static function missingSource(string $handle): self
    {
        return new self(\sprintf('The entry "%s" declares no manifest source.', $handle), $handle);
    }

    public static function invalidDependencies(string $handle): self
    {
        return new self(
            \sprintf('The entry "%s" declares dependencies that are not script module identifiers.', $handle),
            $handle,
        );
    }

    public static function invalidTranslations(string $handle): self
    {
        return new self(
            \sprintf('The entry "%s" declares a text domain without a translations path, or the reverse.', $handle),
            $handle,
        );
    }

    public function handle(): string
    {
        return $this->handle;
    }
}
