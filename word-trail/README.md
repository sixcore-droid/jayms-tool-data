# Word Trail

Data and code for the Word Trail tool on jayms.com: follow one word from the Hebrew,
through the Greek Old Testament, into the New Testament, and set it on two clocks.

This folder lives on its own branch, `word-trail`, so nothing on `main` (and no live tool)
changes while it is being built. Nothing here is a snippet.

## Pages

One script and one stylesheet run three draft pages under Bible Study Tools:

| Page | Mount | What it shows |
|---|---|---|
| 99259 Word Trail | `#jayms-word-trail` with `data-views="uses,lands"` | Every use, Where it lands |
| 99433 Word Timelines | `#jayms-word-trail` with `data-views="written,happened"` | When it was written, When it happened |
| 99432 Book Echoes | `#jayms-book-echoes` | One book and every book it ties into, no word involved |

`data-other-url` and `data-other-label` on a Word Trail mount add a link to the sister page that carries the word across.

## Files

| File | What it holds | Source and licence |
|---|---|---|
| `books.json` | The verse grid: 66 books, English numbering, 31,097 verse ids | Built from interlinear-data |
| `words/H0.json` ... `G56.json` | Every Strong's number (13,860) with lemma, transliteration, definition and every verse id it stands in, 100 numbers per file | Macula Hebrew (WLC) and Macula Greek (SBLGNT), Clear Bible, CC BY 4.0, via sixcore-droid/interlinear-data; Strong's definitions, Open Scriptures, CC BY-SA |
| `search.json` | One row per number for the search box | Same |
| `greek-ot.json` | How the Greek Old Testament renders each Hebrew word, and the reverse | Swete's Greek text (First1KGreek), lemmatized by QuantForgeSoftware/lxx, **CC BY-SA 4.0**; verse alignment by STEPBible TVTMS, CC BY 4.0. Shared under CC BY-SA 4.0 |
| `links.json` | The 362 graded links of Echoes of Revelation, as verse ids | JAYMS.COM, Echoes of Revelation |
| `clock.json` | When each book was written (dates A), the surviving copies, find dates, translations and print, Dead Sea Scrolls by verse | Bible Knowledge Commentary dates as Logos's timeline shows them; standard dates, not yet checked in Logos (no outside links: the site never links to Wikipedia); ETCBC/dss (Abegg), CC BY-NC 4.0 |
| `world.json` | People, events, the world's polities, famous moments, hinges | Theographic Bible Metadata, **CC BY-SA 4.0** (people); JAYMS.COM Old Testament Timeline (events); Cliopatria / Seshat, CC BY 4.0 (world); standard dates, not yet checked in Logos |
| `intertext/b0.json` ... `b65.json` | One file per book: every link with one end in that book, as [this start, this end, other start, other end, source, strength, extra]. Sources: q shared wording, e graded echo, x cross reference | UBS Parallel Passages Database, (c) 2023 United Bible Societies, **CC BY-SA 4.0** (github.com/ubsicap/ubs-open-license), Old Testament references moved from Hebrew to English numbering; JAYMS.COM Echoes of Revelation; OpenBible.info cross references, **CC BY** (via scrollmapper/bible_databases), 5 votes or more |
| `written.json` | When it was written: the Second Temple period, the Old Testament written (with the gap to its oldest copy), Second Temple writings (1, 2, 3 Enoch, Jubilees, 4 Ezra and 2 Baruch), the New Testament written and gathered, the other gospels, acts and letters (honest, forged, forged with a different Christ, as the Lost Books drafts sort them), Mishnah, Talmud and Midrash (Strack-Stemberger), the copies we hold, when they came to light, translations, print | Bible Knowledge Commentary dates; this site's Paul, Apostles and Canon timelines; ETCBC/dss (Abegg), CC BY-NC 4.0; standard dates, not yet checked in Logos |
| `chart.json` | When it happened: 24 streams, Creation to 2026 (the line from Adam, Israel, the two kingdoms, the prophets, Jesus, the apostles, the New Testament written and gathered, the Bible copied and translated, the church fathers, the church divides, events, turning points, the nations, famous moments) | Theographic Bible Metadata, **CC BY-SA 4.0** (Floyd Nolen Jones chronology); JAYMS.COM Old Testament, Twelve Apostles, Apostle Paul, NT Canon, Ante-Nicene, Gnostic Crisis and Crusades timelines; the Denominational Map (study notes); Cliopatria, CC BY 4.0; standard dates, not yet checked in Logos. Layout after Adams' Synchronological Chart (1871) |
| `word-trail.css` | The whole stylesheet, every rule scoped to `.jayms-word-trail`, tokens from global styles 90171 only | |
| `word-trail.js` | The whole tool, no library | |
| `build/` | The scripts that made the data. `b2_lxx.py` imports `lxx_map.py` from the study workstation (`work/study-workstation/scripts/`) | |

## Known limits

- The Greek Old Testament counts are statistical (a word-alignment model over verse pairs). Swete's
  transcription has scanning errors and no Ecclesiastes.
- Graded links exist for Revelation only, because Echoes of Revelation is the only graded set so far. Shared wording and cross references cover every book.
- Cross-reference strength is reader votes on OpenBible.info, not a scholar's grade. The page says which source each line comes from.
- Dates B has no source yet; its layer stays greyed.
- interlinear-data gives the one-letter Hebrew prefixes (and, the, to, in, like) the numbers of other
  words (H2050, H1886, H3807, H871, H3509). The build filters them; the source files are untouched.
