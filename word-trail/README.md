# Word Trail

Data and code for the Word Trail tool on jayms.com: follow one word from the Hebrew,
through the Greek Old Testament, into the New Testament, and set it on two clocks.

This folder lives on its own branch, `word-trail`, so nothing on `main` (and no live tool)
changes while it is being built. Nothing here is a snippet.

## Files

| File | What it holds | Source and licence |
|---|---|---|
| `books.json` | The verse grid: 66 books, English numbering, 31,097 verse ids | Built from interlinear-data |
| `words/H0.json` ... `G56.json` | Every Strong's number (13,860) with lemma, transliteration, definition and every verse id it stands in, 100 numbers per file | Macula Hebrew (WLC) and Macula Greek (SBLGNT), Clear Bible, CC BY 4.0, via sixcore-droid/interlinear-data; Strong's definitions, Open Scriptures, CC BY-SA |
| `search.json` | One row per number for the search box | Same |
| `greek-ot.json` | How the Greek Old Testament renders each Hebrew word, and the reverse | Swete's Greek text (First1KGreek), lemmatized by QuantForgeSoftware/lxx, **CC BY-SA 4.0**; verse alignment by STEPBible TVTMS, CC BY 4.0. Shared under CC BY-SA 4.0 |
| `links.json` | The 362 graded links of Echoes of Revelation, as verse ids | JAYMS.COM, Echoes of Revelation |
| `clock.json` | When each book was written (dates A), the surviving copies, find dates, translations and print, Dead Sea Scrolls by verse | Bible Knowledge Commentary dates as Logos's timeline shows them; Wikipedia pages cited per item; ETCBC/dss (Abegg), CC BY-NC 4.0 |
| `world.json` | People, events, the world's polities, famous moments, hinges | Theographic Bible Metadata, **CC BY-SA 4.0** (people); JAYMS.COM Old Testament Timeline (events); Cliopatria / Seshat, CC BY 4.0 (world); Wikipedia pages cited per moment |
| `word-trail.css` | The whole stylesheet, every rule scoped to `.jayms-word-trail`, tokens from global styles 90171 only | |
| `word-trail.js` | The whole tool, no library | |
| `build/` | The scripts that made the data. `b2_lxx.py` imports `lxx_map.py` from the study workstation (`work/study-workstation/scripts/`) | |

## Known limits

- The Greek Old Testament counts are statistical (a word-alignment model over verse pairs). Swete's
  transcription has scanning errors and no Ecclesiastes.
- Graded links exist for Revelation only, because Echoes of Revelation is the only graded set so far.
- Dates B has no source yet; its layer stays greyed.
- interlinear-data gives the one-letter Hebrew prefixes (and, the, to, in, like) the numbers of other
  words (H2050, H1886, H3807, H871, H3509). The build filters them; the source files are untouched.
