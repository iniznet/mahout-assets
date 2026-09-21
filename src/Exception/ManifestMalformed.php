<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets\Exception;

/**
 * The build manifest is not the documented shape. It is refused whole; a
 * partially understood manifest would serve a wrong URL silently.
 */
final class ManifestMalformed extends \UnexpectedValueException implements MahoutException
{
    private function __construct(string $message, private readonly string $reason)
    {
        parent::__construct($message);
    }

    public static function because(string $reason): self
    {
        return new self('The build manifest could not be parsed: '.$reason, $reason);
    }

    public static function entryNotAnObject(string $source): self
    {
        return new self(\sprintf('The build manifest entry "%s" is not an object.', $source), 'entry not an object');
    }

    public static function missingFile(string $source): self
    {
        return new self(\sprintf('The build manifest entry "%s" declares no file.', $source), 'missing file');
    }

    public static function invalidVersion(string $source): self
    {
        return new self(\sprintf('The build manifest entry "%s" has a non-string version.', $source), 'invalid version');
    }

    public static function invalidStyles(string $source): self
    {
        return new self(\sprintf('The build manifest entry "%s" has an invalid css list.', $source), 'invalid styles');
    }

    public function reason(): string
    {
        return $this->reason;
    }
}
