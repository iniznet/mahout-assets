<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets\Tests\Fixtures;

use Iniznet\Mahout\Assets\Contracts\ManifestSource;
use Iniznet\Mahout\Assets\Exception\FileUnreadable;

/**
 * A manifest source a test can make readable or unreadable at will.
 *
 * @internal
 */
final readonly class InMemoryManifestSource implements ManifestSource
{
    public function __construct(
        private string $path,
        private string $contents,
        private bool $readable = true,
    ) {
    }

    public function path(): string
    {
        return $this->path;
    }

    public function readable(): bool
    {
        return $this->readable;
    }

    public function contents(): string
    {
        if (!$this->readable) {
            throw FileUnreadable::at($this->path);
        }

        return $this->contents;
    }
}
