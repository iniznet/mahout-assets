<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets\Tests\Unit;

use Iniznet\Mahout\Assets\DevMode;
use Iniznet\Mahout\Assets\Tests\TestCase;

/**
 * @internal
 */
final class DevModeTest extends TestCase
{
    public function testUndefinedConstantMeansProductionBehaviour(): void
    {
        self::assertFalse(\defined('MAHOUT_ASSETS_DEV'));
        self::assertFalse(DevMode::fromConstant()->enabled);
    }

    public function testTheExplicitFactories(): void
    {
        self::assertTrue(DevMode::enabled()->enabled);
        self::assertFalse(DevMode::disabled()->enabled);
    }

    public function testFromConstantReadsATrueConstantInAnIsolatedProcess(): void
    {
        $autoload = \dirname(__DIR__, 2).'/vendor/autoload.php';
        $code = \sprintf(
            "define('MAHOUT_ASSETS_DEV', true); require %s; echo Iniznet\\Mahout\\Assets\\DevMode::fromConstant()->enabled ? 'enabled' : 'disabled';",
            \var_export($autoload, true),
        );

        $process = \proc_open(
            [PHP_BINARY, '-r', $code],
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            \dirname(__DIR__, 2),
        );
        self::assertIsResource($process, 'Could not start PHP');

        $stdout = (string) \stream_get_contents($pipes[1]);
        \stream_get_contents($pipes[2]);
        \fclose($pipes[1]);
        \fclose($pipes[2]);
        \proc_close($process);

        self::assertSame('enabled', $stdout);
    }
}
