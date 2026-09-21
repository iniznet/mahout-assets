<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets;

/**
 * Every declared entry, in declaration order. It is built once from the site's
 * one explicit list; nothing scans the filesystem and nothing globs.
 */
final readonly class EntryList
{
    /**
     * @param list<EntryPoint> $entries
     */
    public function __construct(private array $entries)
    {
    }

    /**
     * @param list<array<string, mixed>> $declarations
     */
    public static function fromDeclarations(array $declarations): self
    {
        $entries = [];
        foreach ($declarations as $declaration) {
            $entries[] = EntryPoint::fromArray($declaration);
        }

        return new self($entries);
    }

    /** @return list<EntryPoint> */
    public function all(): array
    {
        return $this->entries;
    }

    /** @return list<EntryPoint> */
    public function forContext(EntryContext $context): array
    {
        return \array_values(\array_filter(
            $this->entries,
            static fn (EntryPoint $entry): bool => $entry->context === $context,
        ));
    }
}
