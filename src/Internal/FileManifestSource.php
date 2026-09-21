<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets\Internal;

use Iniznet\Mahout\Assets\Contracts\ManifestSource;
use Iniznet\Mahout\Assets\Exception\FileUnreadable;

/**
 * The build manifest read from a file path.
 *
 * @internal
 */
final readonly class FileManifestSource implements ManifestSource
{
    public function __construct(private string $file)
    {
    }

    public function path(): string
    {
        return $this->file;
    }

    public function readable(): bool
    {
        return \is_readable($this->file);
    }

    public function contents(): string
    {
        if (!$this->readable()) {
            throw FileUnreadable::at($this->file);
        }

        $contents = \file_get_contents($this->file);
        if (false === $contents) {
            throw FileUnreadable::at($this->file);
        }

        return $contents;
    }
}
