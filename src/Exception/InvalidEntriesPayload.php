<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets\Exception;

/**
 * The mahout/assets/entries filter returned something that is not a list of
 * entries. The enqueue stops; it never skips the bad value.
 */
final class InvalidEntriesPayload extends \UnexpectedValueException implements MahoutException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function notAList(): self
    {
        return new self('The mahout/assets/entries filter must return a list of entries.');
    }

    public static function notAnEntryPoint(): self
    {
        return new self('The mahout/assets/entries filter returned a value that is not an EntryPoint.');
    }
}
