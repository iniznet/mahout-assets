# Extending

## A new entry

Add one declaration to the host's `EntryList`: handle, source, context,
dependencies, text domain, translations path. The context decides the core
hook the entry enqueues on; every other key is data. Re-run the size gate —
an entry ships within its ceiling or with an explicitly re-declared one.

## A new context

`EntryContext` is the closed `Front` / `Admin` / `Editor` set, each case
mapped to its core hook. A new context is a new enum case plus its hook
mapping plus a test that the entry enqueues on that hook and on no other.

## A custom manifest source

Implement `Contracts\\ManifestSource` (`path()`, `readable()`,
`contents()`) when the build output lives somewhere a plain file read cannot
reach. The enqueuer knows no filesystem — resolution runs entirely through the
contract, so a test doubles it with an in-memory source.

## What this package must never do

Probe the network to detect a dev server; enqueue an entry whose context is
unknown; or read the manifest at construction time. Each refusal is a design
rule, not an omission.
