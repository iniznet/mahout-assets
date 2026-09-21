<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets\Exception;

/**
 * A script module translation declaration has an empty domain or an empty path.
 */
final class InvalidScriptModuleTranslations extends \InvalidArgumentException implements MahoutException
{
    private function __construct(string $message, private readonly string $field)
    {
        parent::__construct($message);
    }

    public static function emptyDomain(): self
    {
        return new self('A script module text domain must not be empty.', 'domain');
    }

    public static function emptyPath(): self
    {
        return new self('A script module translations path must not be empty.', 'path');
    }

    public function field(): string
    {
        return $this->field;
    }
}
