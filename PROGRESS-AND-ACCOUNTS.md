# Reading progress: how it works now, and what taking it to accounts costs

Decided 2026-09-25. Progress ticks ship browser-only. This is the record of
that decision and the plan for changing it, so the choice can be revisited
on evidence rather than re-argued from scratch.

## Why browser-only, for now

The deciding factor was audience size, not technology. Reach today is tens
of readers. An account system for that many people is a permanent
maintenance surface (signups, resets, spam registrations, support mail) paid
for by very few users, and every logged-in reader also bypasses the page
cache, so the site gets slower for them.

Ship the tick, see whether anyone uses it. People asking for it on their
phone is the signal to add accounts, and that is evidence rather than a
guess.

## How it works now

One key per tool in `localStorage`:

```
jayms.progress.<tool slug>      ->   { "<entry id>": 1, ... }
```

- `<tool slug>` is the same slug the PHP uses to pick the dataset
  (`divine-council-alpha`), so two tools never collide.
- `<entry id>` is the stable slug from the JSON (`psalm-82`), not an array
  position. This is the part that makes a later move cheap.
- The value is `1` and nothing else. Done or absent. No notes, no dates, no
  ordering, by decision.
- Every read and write is wrapped in try/catch: storage throws in a private
  window and returns nothing when a reader blocks site data. A failure
  degrades to "no progress recorded", never to a broken page.
- `data-progress="off"` on the mount div turns the whole feature off per
  page.

### What this costs the reader

Progress is per browser. Tick on a phone, it is not on the laptop. Clearing
site data clears it. That is the known, accepted trade.

### Legal position

No consent banner needed. This is functional state the reader creates by
tapping a control, not tracking, and it never leaves their device.

## Moving to accounts later

The work is a copy from one store to another, not a redesign, **provided the
three rules below stay true.**

### The three rules that keep it cheap

1. **Entry ids stay stable.** They are generated from entry titles by the
   converter. Renaming an entry changes its id and orphans anyone's tick on
   it. If a title has to change, keep the old id explicitly in the markdown
   rather than letting it regenerate.
2. **The value stays a scalar.** The moment progress holds notes or dates,
   it needs a schema, migrations and a conflict rule. Keep it `1`.
3. **Reads and writes stay behind `loadProgress` / `saveProgress` /
   `isDone`.** Nothing else in the engine touches storage directly. Swapping
   the backing store means rewriting those three functions and nothing else.

### The steps, when the time comes

1. **Pick the sign-in.** Magic link over password: no resets, no password
   storage, and it feeds the newsletter list. WordPress can do this with a
   plugin or a small custom route.
2. **Server store.** One row per reader per tool, holding the same object.
   User meta is enough at this size; a custom table only if it grows.
3. **REST route.** `GET` and `POST` on the reader's own progress, nonce
   protected. The Suggestion Box (snippet 65) is the working precedent for a
   tool writing to the server.
4. **Merge on first sign-in, do not overwrite.** A reader will already have
   local ticks. Union the two sets: anything done in either place is done.
   Silently replacing local progress with an empty server record is the
   obvious way to lose someone's work.
5. **Keep writing locally as well.** Local stays the fast path and the
   offline fallback; the server becomes the sync layer. Write both, read
   local first, reconcile on load.
6. **Watch the page cache.** Logged-in readers bypass Batcache. Fetch
   progress client-side after load rather than rendering it server-side, so
   the page itself stays cacheable for everyone.

### What would tell us it is time

- Readers asking for progress on a second device
- The tick actually being used at all, which right now is unknown
- A reason to hold anything per-reader beyond a tick, such as notes or
  saved searches

None of those are true today.
