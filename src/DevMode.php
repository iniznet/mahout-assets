<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets;

/**
 * The one development-mode switch.
 *
 * A site defines MAHOUT_ASSETS_DEV as true while it is developing. It is a
 * single explicit constant and never a network probe: probing a dev server on
 * every request costs a TCP timeout in production, and a constant costs a
 * defined-check.
 */
final readonly class DevMode
{
    private const string CONSTANT = 'MAHOUT_ASSETS_DEV';

    private function __construct(public bool $enabled)
    {
    }

    /**
     * Read the declared constant. Undefined means production behaviour.
     */
    public static function fromConstant(): self
    {
        $enabled = \defined(self::CONSTANT) && true === \constant(self::CONSTANT);

        return new self($enabled);
    }

    public static function enabled(): self
    {
        return new self(true);
    }

    public static function disabled(): self
    {
        return new self(false);
    }
}
