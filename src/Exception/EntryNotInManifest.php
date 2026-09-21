<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets\Exception;

/**
 * A declared entry names a manifest source the build did not emit.
 */
final class EntryNotInManifest extends \OutOfRangeException implements MahoutException
{
    private function __construct(string $message, private readonly string $source)
    {
        parent::__construct($message);
    }

    public static function forSource(string $source): self
    {
        return new self(
            \sprintf('The build manifest declares no entry for "%s".', $source),
            $source,
        );
    }

    public function source(): string
    {
        return $this->source;
    }
}
