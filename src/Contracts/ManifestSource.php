<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets\Contracts;

/**
 * Where the build manifest lives. The package reads it through this boundary so
 * the missing-manifest behaviour is provable without a real build directory.
 */
interface ManifestSource
{
    /** The path or identifier reported when the manifest cannot be read. */
    public function path(): string;

    /** Whether the manifest can be read at all. */
    public function readable(): bool;

    /**
     * The manifest's bytes.
     *
     * @throws \Iniznet\Mahout\Assets\Exception\FileUnreadable when unreadable
     */
    public function contents(): string;
}
