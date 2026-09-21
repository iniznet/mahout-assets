<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets\Exception;

/**
 * A file the package needed could not be read. The manifest and every measured
 * asset go through this failure; a missing file is never substituted with an
 * empty one.
 */
final class FileUnreadable extends \RuntimeException implements MahoutException
{
    private function __construct(string $message, private readonly string $path)
    {
        parent::__construct($message);
    }

    public static function at(string $path): self
    {
        return new self(\sprintf('The file "%s" could not be read.', $path), $path);
    }

    public function path(): string
    {
        return $this->path;
    }
}
