<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets\Exception;

use Iniznet\Mahout\Assets\EntryContext;

/**
 * The entries filter returned an entry for a context other than the one being
 * enqueued. That would serve one context's assets on another's hook, so it is
 * refused.
 */
final class EntryContextMismatch extends \DomainException implements MahoutException
{
    private function __construct(string $message, private readonly string $handle)
    {
        parent::__construct($message);
    }

    public static function forEntry(string $handle, EntryContext $expected, EntryContext $actual): self
    {
        return new self(
            \sprintf(
                'The entry "%s" belongs to context "%s" but was returned for "%s".',
                $handle,
                $actual->value,
                $expected->value,
            ),
            $handle,
        );
    }

    public function handle(): string
    {
        return $this->handle;
    }
}
