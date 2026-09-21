<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets\Tests\Architecture;

use Iniznet\Mahout\Assets\Tests\TestCase;

/**
 * The negative proof for the development-mode rule: development mode is a
 * single explicit constant, never a network probe. No production file dials a
 * dev server, opens a socket or fetches a URL.
 *
 * @internal
 */
final class DevModeConstantTest extends TestCase
{
    /** @var list<string> */
    private const PROBES = [
        'localhost',
        '127.0.0.1',
        ':5173',
        'fsockopen',
        'stream_socket_client',
        'curl_init',
        'wp_remote_get',
        'wp_remote_head',
        'http://',
        'https://',
    ];

    public function testNoProductionFileProbesTheNetwork(): void
    {
        $root = \dirname(__DIR__, 2);
        $offenders = [];

        foreach (['src', 'bin'] as $directory) {
            foreach ($this->files($root.'/'.$directory) as $file) {
                $contents = \file_get_contents($file);
                if (false === $contents) {
                    continue;
                }

                foreach (self::PROBES as $probe) {
                    if (\str_contains($contents, $probe)) {
                        $offenders[] = $file.' ('.$probe.')';
                    }
                }
            }
        }

        self::assertSame([], $offenders, 'A network probe exists: '.\implode(', ', $offenders));
    }

    public function testDevelopmentModeReadsOneNamedConstant(): void
    {
        $source = \file_get_contents(\dirname(__DIR__, 2).'/src/DevMode.php');
        self::assertIsString($source);
        self::assertStringContainsString('MAHOUT_ASSETS_DEV', $source);
        self::assertSame(1, \substr_count($source, 'private const string CONSTANT'));
    }

    /**
     * @return list<string>
     */
    private function files(string $root): array
    {
        if (!\is_dir($root)) {
            return [];
        }

        $found = [];
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS),
        );

        foreach ($items as $item) {
            if ($item instanceof \SplFileInfo && $item->isFile()) {
                $found[] = $item->getPathname();
            }
        }

        return $found;
    }
}
