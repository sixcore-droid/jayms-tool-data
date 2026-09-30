<?php
/**
 * JAYMS — Interleaved Bible.
 *
 * Built 2026-09-29 to the design agreed on the canvas, and built to two rules.
 *
 *   1. It ships no components. The card, the label, the pill, the chip and
 *      the body text are jayms.com components and live in global styles
 *      90171. What is below is layout only -- grid, flow, gaps -- and the
 *      few things only this tool has: the word rail, the fold, the panel.
 *      No colour is chosen here; every one is a token from that stylesheet.
 *   2. It fetches nothing until a reader asks. One book when a passage
 *      opens, the lexicon when a word is opened, the word index with it.
 *
 * Traps, each paid for once already:
 *   - #app is the mount id on every tool. Never style by it.
 *   - The theme sets font-family on body, p, li with !important.
 *   - DATA / LEX / WORD_INDEX start as {}, which is truthy. Readiness is a
 *     question of contents.
 *   - Back to a pushState entry reloads the document here. Hash only, and
 *     the listener is attached during parse.
 */

add_filter( 'the_content', function ( $content ) {
	if ( ! is_page( 96261 ) ) {
		return $content;
	}
	return $content . '<div class="wrap jayms-tool-alpha jayms-tool-outline" id="app"></div>';
}, 20 );

add_action( 'wp_footer', function () {
	if ( ! is_page( 96261 ) ) {
		return;
	}
	?>
<style id="jayms-outline-layout">
/* Layout, and the three things only this tool has. Any line here that
   starts deciding how something LOOKS belongs in global styles 90171. */
.jayms-tool-outline .ib-row{display:flex;flex-wrap:wrap;align-items:center;gap:8px}
.jayms-tool-outline .ib-filters{margin:18px 0 0}
.jayms-tool-outline .ib-legend{display:flex;flex-wrap:wrap;align-items:center;gap:14px;
  border-top:1px solid var(--line);border-bottom:1px solid var(--line);padding:12px 0;margin:14px 0 22px}
.jayms-tool-outline .ib-key{display:inline-flex;align-items:center;gap:7px;background:none;border:0;
  padding:4px 2px;cursor:pointer;font-family:inherit;color:var(--ink-soft);font-size:13px}
.jayms-tool-outline .ib-key.on{color:var(--ink)}
.jayms-tool-outline .ib-swatch{width:3px;height:15px;border-radius:2px;display:inline-block}
.jayms-tool-outline .ib-note{color:var(--muted);font-size:13px}

.jayms-tool-outline .ib-sec{margin:28px 0 0}
.jayms-tool-outline .ib-sub{margin:18px 0 8px;display:flex;gap:8px}
.jayms-tool-outline .ib-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:10px}
.jayms-tool-outline .ib-tile{gap:2px}
.jayms-tool-outline .ib-tile-name{font-size:21px;line-height:1.25;color:var(--ink)}
.jayms-tool-outline .ib-tile-meta{font-size:12px;color:var(--muted)}

.jayms-tool-outline .ib-book{display:flex;gap:40px;align-items:flex-start;margin-top:22px}
.jayms-tool-outline .ib-rail{position:sticky;top:20px;flex:0 0 200px;display:flex;flex-direction:column;gap:2px;
  border-right:1px solid var(--line);padding-right:20px}
.jayms-tool-outline .ib-rail a{display:flex;justify-content:space-between;gap:10px;padding:7px 9px;
  border-radius:7px;text-decoration:none;font-size:13px;color:var(--ink-soft)}
.jayms-tool-outline .ib-rail a:hover{background:var(--paper-deep);color:var(--ink)}
.jayms-tool-outline .ib-eras{flex:1 1 auto;min-width:0}
.jayms-tool-outline .ib-era{margin:0 0 30px}
.jayms-tool-outline .ib-era-head{display:flex;flex-wrap:wrap;align-items:baseline;gap:12px}
.jayms-tool-outline .ib-era-note{margin:8px 0 0;max-width:72ch}
.jayms-tool-outline .ib-rows{display:flex;flex-direction:column;gap:1px;background:var(--line);
  border:1px solid var(--line);border-radius:10px;overflow:hidden;margin-top:14px}

.jayms-tool-outline .ib-fold{background:var(--paper-deep)}
.jayms-tool-outline .ib-fold.turning{border-left:3px solid var(--rust)}
.jayms-tool-outline .ib-fold > summary{display:flex;align-items:baseline;gap:18px;padding:15px 18px;
  list-style:none;cursor:pointer;min-height:44px;box-sizing:border-box}
.jayms-tool-outline .ib-fold > summary::-webkit-details-marker{display:none}
.jayms-tool-outline .ib-ref{flex:0 0 132px}
.jayms-tool-outline .ib-title{flex:1 1 auto;min-width:0}
.jayms-tool-outline .ib-flag{margin-left:8px}
.jayms-tool-outline .ib-caret{flex:0 0 auto;color:var(--gold)}
.jayms-tool-outline .ib-fold[open] > summary .ib-caret{transform:rotate(90deg);display:inline-block}
.jayms-tool-outline .ib-fold-body{padding:0 22px 18px 150px;display:flex;flex-direction:column;gap:12px}
.jayms-tool-outline .ib-detail{margin:0;max-width:66ch}
.jayms-tool-outline .ib-passage{margin:0;max-width:62ch}
.jayms-tool-outline .ib-vn{font-size:.62em;vertical-align:super;color:var(--muted);padding-right:2px}

.jayms-tool-outline .ib-readref{margin:4px 0 0}
.jayms-tool-outline .ib-read{display:flex;gap:0;align-items:flex-start;margin-top:22px}
.jayms-tool-outline .ib-text{flex:1 1 auto;min-width:0;padding-right:40px}
.jayms-tool-outline .ib-verse{margin:0 0 26px}
.jayms-tool-outline .ib-orig{margin:10px 0 0;max-width:62ch;line-height:1.95}
.jayms-tool-outline .ib-rtl{direction:rtl;text-align:right}
.jayms-tool-outline .ib-w{background:none;border:0;padding:0 3px;margin:0;font:inherit;color:inherit;
  cursor:pointer;border-radius:3px}
.jayms-tool-outline .ib-w:hover{background:var(--paper-deeper)}
.jayms-tool-outline .ib-w.on{background:var(--gold);color:var(--paper-deep)}
.jayms-tool-outline .ib-panel{position:sticky;top:20px;flex:0 0 330px;background:var(--paper-deep);
  border-left:1px solid var(--line);padding:24px 26px;display:flex;flex-direction:column;gap:14px}
.jayms-tool-outline .ib-panel-word{margin:0;font-size:2em;line-height:1.25}
.jayms-tool-outline .ib-panel-translit{margin:0;font-style:italic;color:var(--ink-soft)}
.jayms-tool-outline .ib-panel-gloss{margin:0;color:var(--gold);font-size:1.25em}
.jayms-tool-outline .ib-parse{margin:0;display:grid;grid-template-columns:auto 1fr;gap:6px 18px;font-size:13px}
.jayms-tool-outline .ib-parse dt{margin:0;color:var(--muted)}
.jayms-tool-outline .ib-parse dd{margin:0;color:var(--ink)}
.jayms-tool-outline .ib-def{border-top:1px solid var(--line);padding-top:14px;display:flex;
  flex-direction:column;gap:7px}
.jayms-tool-outline .ib-def p{margin:0}

@media (max-width: 860px) {
  .jayms-tool-outline .ib-book,
  .jayms-tool-outline .ib-read{flex-direction:column}
  .jayms-tool-outline .ib-rail{position:static;flex:1 1 auto;border-right:0;
    border-bottom:1px solid var(--line);padding:0 0 12px}
  .jayms-tool-outline .ib-panel{position:static;flex:1 1 auto;border-left:0;
    border-top:1px solid var(--line)}
  .jayms-tool-outline .ib-text{padding-right:0}
  .jayms-tool-outline .ib-fold-body{padding-left:18px}
}
</style>

<script id="jayms-outline-versification">
/* Hebrew and Greek numbering is not English numbering; they part company in
   120 chapters. Derived by aligning the interlinear's own glosses against an
   English text, not copied from a list. [from, to, englishChapter, delta],
   or "fold" where English keeps a Hebrew superscription inside verse 1. */
window.JAYMS_VERSIFICATION = {"1 Chronicles": {"5": [[1, 26, 5, 0], [27, 41, 6, -26]], "6": [[1, 66, 6, 15]], "12": [[1, 1, 11, 46], [2, 41, 12, -1]]}, "1 Kings": {"5": [[1, 14, 4, 20], [15, 32, 5, -14]], "22": [[1, 43, 22, 0], [44, 54, 22, -1]]}, "1 Samuel": {"21": [[1, 1, 20, 41], [2, 16, 21, -1]], "24": [[1, 1, 23, 28], [2, 23, 24, -1]]}, "2 Chronicles": {"1": [[1, 17, 1, 0], [18, 18, 2, -17]], "2": [[1, 17, 2, 1]], "13": [[1, 22, 13, 0], [23, 23, 14, -22]], "14": [[1, 14, 14, 1]]}, "2 Kings": {"12": [[1, 1, 11, 20], [2, 22, 12, -1]]}, "2 Samuel": {"19": [[1, 1, 18, 32], [2, 44, 19, -1]]}, "3 John": {"1": [[1, 15, 1, 0]]}, "Daniel": {"3": [[1, 30, 3, 0], [31, 33, 4, -30]], "4": [[1, 34, 4, 3]], "6": [[1, 1, 5, 30], [2, 29, 6, -1]]}, "Deuteronomy": {"13": [[1, 1, 12, 31], [2, 19, 13, -1]], "23": [[1, 1, 22, 29], [2, 26, 23, -1]], "28": [[1, 68, 28, 0], [69, 69, 29, -68]], "29": [[1, 28, 29, 1]]}, "Ecclesiastes": {"4": [[1, 16, 4, 0], [17, 17, 5, -16]], "5": [[1, 19, 5, 1]]}, "Exodus": {"7": [[1, 25, 7, 0], [26, 29, 8, -25]], "8": [[1, 28, 8, 4]], "21": [[1, 36, 21, 0], [37, 37, 22, -36]], "22": [[1, 30, 22, 1]]}, "Ezekiel": {"21": [[1, 5, 20, 44], [6, 37, 21, -5]]}, "Genesis": {"32": [[1, 1, 31, 54], [2, 33, 32, -1]]}, "Hosea": {"2": [[1, 2, 1, 9], [3, 25, 2, -2]], "12": [[1, 1, 11, 11], [2, 15, 12, -1]], "14": [[1, 1, 13, 15], [2, 10, 14, -1]]}, "Isaiah": {"8": [[1, 22, 8, 0], [23, 23, 9, -22]], "9": [[1, 20, 9, 1]], "64": [[1, 11, 64, 1]]}, "Jeremiah": {"8": [[1, 22, 8, 0], [23, 23, 9, -22]], "9": [[1, 25, 9, 1]]}, "Job": {"40": [[1, 24, 40, 0], [25, 32, 41, -24]], "41": [[1, 26, 41, 8]]}, "Joel": {"3": [[1, 5, 2, 27]], "4": [[1, 21, 3, 0]]}, "Jonah": {"2": [[1, 1, 1, 16], [2, 11, 2, -1]]}, "Leviticus": {"5": [[1, 19, 5, 0], [20, 26, 6, -19]], "6": [[1, 23, 6, 7]]}, "Malachi": {"3": [[1, 18, 3, 0], [19, 24, 4, -18]]}, "Micah": {"4": [[1, 13, 4, 0], [14, 14, 5, -13]], "5": [[1, 14, 5, 1]]}, "Nahum": {"2": [[1, 1, 1, 14], [2, 14, 2, -1]]}, "Nehemiah": {"3": [[1, 32, 3, 0], [33, 38, 4, -32]], "4": [[1, 17, 4, 6]], "7": [[1, 72, 7, 0]], "10": [[1, 1, 9, 37], [2, 40, 10, -1]]}, "Numbers": {"17": [[1, 15, 16, 35], [16, 28, 17, -15]], "25": [[1, 18, 25, 0], [19, 19, 26, -18]], "30": [[1, 1, 29, 39], [2, 17, 30, -1]]}, "Psalms": {"3": [[1, 1, 3, "fold"], [2, 9, 3, -1]], "4": [[1, 1, 4, "fold"], [2, 9, 4, -1]], "5": [[1, 1, 5, "fold"], [2, 13, 5, -1]], "6": [[1, 1, 6, "fold"], [2, 11, 6, -1]], "7": [[1, 1, 7, "fold"], [2, 18, 7, -1]], "8": [[1, 1, 8, "fold"], [2, 10, 8, -1]], "9": [[1, 1, 9, "fold"], [2, 21, 9, -1]], "12": [[1, 1, 12, "fold"], [2, 9, 12, -1]], "18": [[1, 1, 18, "fold"], [2, 51, 18, -1]], "19": [[1, 1, 19, "fold"], [2, 15, 19, -1]], "20": [[1, 1, 20, "fold"], [2, 10, 20, -1]], "21": [[1, 1, 21, "fold"], [2, 14, 21, -1]], "22": [[1, 1, 22, "fold"], [2, 32, 22, -1]], "30": [[1, 1, 30, "fold"], [2, 13, 30, -1]], "31": [[1, 1, 31, "fold"], [2, 25, 31, -1]], "34": [[1, 1, 34, "fold"], [2, 23, 34, -1]], "36": [[1, 1, 36, "fold"], [2, 13, 36, -1]], "38": [[1, 1, 38, "fold"], [2, 23, 38, -1]], "39": [[1, 1, 39, "fold"], [2, 14, 39, -1]], "40": [[1, 1, 40, "fold"], [2, 18, 40, -1]], "41": [[1, 1, 41, "fold"], [2, 14, 41, -1]], "42": [[1, 1, 42, "fold"], [2, 12, 42, -1]], "44": [[1, 1, 44, "fold"], [2, 27, 44, -1]], "45": [[1, 1, 45, "fold"], [2, 18, 45, -1]], "46": [[1, 1, 46, "fold"], [2, 12, 46, -1]], "47": [[1, 1, 47, "fold"], [2, 10, 47, -1]], "48": [[1, 1, 48, "fold"], [2, 15, 48, -1]], "49": [[1, 1, 49, "fold"], [2, 21, 49, -1]], "51": [[1, 2, 51, "fold"], [3, 21, 51, -2]], "52": [[1, 2, 52, "fold"], [3, 11, 52, -2]], "53": [[1, 1, 53, "fold"], [2, 7, 53, -1]], "54": [[1, 2, 54, "fold"], [3, 9, 54, -2]], "55": [[1, 1, 55, "fold"], [2, 24, 55, -1]], "56": [[1, 1, 56, "fold"], [2, 14, 56, -1]], "57": [[1, 1, 57, "fold"], [2, 12, 57, -1]], "58": [[1, 1, 58, "fold"], [2, 12, 58, -1]], "59": [[1, 1, 59, "fold"], [2, 18, 59, -1]], "60": [[1, 2, 60, "fold"], [3, 14, 60, -2]], "61": [[1, 1, 61, "fold"], [2, 9, 61, -1]], "62": [[1, 1, 62, "fold"], [2, 13, 62, -1]], "63": [[1, 1, 63, "fold"], [2, 12, 63, -1]], "64": [[1, 1, 64, "fold"], [2, 11, 64, -1]], "65": [[1, 1, 65, "fold"], [2, 14, 65, -1]], "67": [[1, 1, 67, "fold"], [2, 8, 67, -1]], "68": [[1, 1, 68, "fold"], [2, 36, 68, -1]], "69": [[1, 1, 69, "fold"], [2, 37, 69, -1]], "70": [[1, 1, 70, "fold"], [2, 6, 70, -1]], "75": [[1, 1, 75, "fold"], [2, 11, 75, -1]], "76": [[1, 1, 76, "fold"], [2, 13, 76, -1]], "77": [[1, 1, 77, "fold"], [2, 21, 77, -1]], "80": [[1, 1, 80, "fold"], [2, 20, 80, -1]], "81": [[1, 1, 81, "fold"], [2, 17, 81, -1]], "83": [[1, 1, 83, "fold"], [2, 19, 83, -1]], "84": [[1, 1, 84, "fold"], [2, 13, 84, -1]], "85": [[1, 1, 85, "fold"], [2, 14, 85, -1]], "88": [[1, 1, 88, "fold"], [2, 19, 88, -1]], "89": [[1, 1, 89, "fold"], [2, 53, 89, -1]], "92": [[1, 1, 92, "fold"], [2, 16, 92, -1]], "102": [[1, 1, 102, "fold"], [2, 29, 102, -1]], "108": [[1, 1, 108, "fold"], [2, 14, 108, -1]], "140": [[1, 1, 140, "fold"], [2, 14, 140, -1]], "142": [[1, 1, 142, "fold"], [2, 8, 142, -1]]}, "Song of Solomon": {"7": [[1, 1, 6, 12], [2, 14, 7, -1]]}, "Zechariah": {"2": [[1, 4, 1, 17], [5, 17, 2, -4]]}, "Revelation": {"12": [[1, 17, 12, 0], [18, 18, 12, -1]]}};
</script>

<script id="jayms-outline-app">
(function () {
  "use strict";

  var MOUNT = document.getElementById("app");
  if (!MOUNT) return;

  var OUTLINE_BASE = "https://raw.githubusercontent.com/sixcore-droid/jayms-tool-data/main/outline/";
  var GH = "https://raw.githubusercontent.com/sixcore-droid/interlinear-data/main";

  var BOOK_SLUGS = window.JAYMS_OUTLINE_BOOKS || {};
  var PRELOAD    = window.JAYMS_OUTLINE_PRELOAD || {};
  var VERSIFICATION = window.JAYMS_VERSIFICATION || {};

  var OUTLINES = {};
  var DATA = {};
  var LEX = null;
  var WORD_INDEX = { hebrew: null, greek: null };

  Object.keys(PRELOAD).forEach(function (b) { OUTLINES[b] = PRELOAD[b]; });

  // name, testament, chapters, genre
  var BOOKS = [
["Genesis","OT",50,"Law"],["Exodus","OT",40,"Law"],["Leviticus","OT",27,"Law"],
["Numbers","OT",36,"Law"],["Deuteronomy","OT",34,"Law"],["Joshua","OT",24,"History"],
["Judges","OT",21,"History"],["Ruth","OT",4,"History"],["1 Samuel","OT",31,"History"],
["2 Samuel","OT",24,"History"],["1 Kings","OT",22,"History"],["2 Kings","OT",25,"History"],
["1 Chronicles","OT",29,"History"],["2 Chronicles","OT",36,"History"],["Ezra","OT",10,"History"],
["Nehemiah","OT",13,"History"],["Esther","OT",10,"History"],["Job","OT",42,"Wisdom"],
["Psalms","OT",150,"Wisdom"],["Proverbs","OT",31,"Wisdom"],["Ecclesiastes","OT",12,"Wisdom"],
["Song of Solomon","OT",8,"Wisdom"],["Isaiah","OT",66,"Major Prophets"],
["Jeremiah","OT",52,"Major Prophets"],["Lamentations","OT",5,"Major Prophets"],
["Ezekiel","OT",48,"Major Prophets"],["Daniel","OT",12,"Major Prophets"],
["Hosea","OT",14,"Minor Prophets"],["Joel","OT",3,"Minor Prophets"],["Amos","OT",9,"Minor Prophets"],
["Obadiah","OT",1,"Minor Prophets"],["Jonah","OT",4,"Minor Prophets"],["Micah","OT",7,"Minor Prophets"],
["Nahum","OT",3,"Minor Prophets"],["Habakkuk","OT",3,"Minor Prophets"],["Zephaniah","OT",3,"Minor Prophets"],
["Haggai","OT",2,"Minor Prophets"],["Zechariah","OT",14,"Minor Prophets"],["Malachi","OT",4,"Minor Prophets"],
["Matthew","NT",28,"Gospels"],["Mark","NT",16,"Gospels"],["Luke","NT",24,"Gospels"],["John","NT",21,"Gospels"],
["Acts","NT",28,"History"],["Romans","NT",16,"Letters"],["1 Corinthians","NT",16,"Letters"],
["2 Corinthians","NT",13,"Letters"],["Galatians","NT",6,"Letters"],["Ephesians","NT",6,"Letters"],
["Philippians","NT",4,"Letters"],["Colossians","NT",4,"Letters"],["1 Thessalonians","NT",5,"Letters"],
["2 Thessalonians","NT",3,"Letters"],["1 Timothy","NT",6,"Letters"],["2 Timothy","NT",4,"Letters"],
["Titus","NT",3,"Letters"],["Philemon","NT",1,"Letters"],["Hebrews","NT",13,"Letters"],
["James","NT",5,"Letters"],["1 Peter","NT",5,"Letters"],["2 Peter","NT",3,"Letters"],
["1 John","NT",5,"Letters"],["2 John","NT",1,"Letters"],["3 John","NT",1,"Letters"],
["Jude","NT",1,"Letters"],["Revelation","NT",22,"Apocalyptic"]
  ].map(function (b) { return { name: b[0], testament: b[1], chapters: b[2], genre: b[3] }; });

  // Colour says genre, never selection. Every token is one the site already has.
  var GENRE_TOKEN = {
    "Law": "--gold", "History": "--olive", "Wisdom": "--aramaic",
    "Major Prophets": "--blue", "Minor Prophets": "--lavender",
    "Gospels": "--rust", "Letters": "--gold", "Apocalyptic": "--lavender"
  };
  var GENRE_ORDER_OT = ["Law","History","Wisdom","Major Prophets","Minor Prophets"];
  var GENRE_ORDER_NT = ["Gospels","History","Letters","Apocalyptic"];

  function esc(s) {
    return String(s == null ? "" : s)
      .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
  }
  function attr(s) { return JSON.stringify(String(s)).replace(/"/g, "&quot;"); }

  // ------------------------------------------------------- versification

  function toEnglishVerse(book, chapter, verse) {
    var segs = VERSIFICATION[book] && VERSIFICATION[book][chapter];
    if (!segs) return { chapter: chapter, verse: verse };
    for (var i = 0; i < segs.length; i++) {
      var s = segs[i];
      if (verse >= s[0] && verse <= s[1]) {
        return s[3] === "fold" ? { chapter: s[2], verse: 1 } : { chapter: s[2], verse: verse + s[3] };
      }
    }
    return { chapter: chapter, verse: verse };
  }

  function toEnglishRef(ref) {
    var m = String(ref).match(/^(.+?)\s+(\d+):(\d+)(?:\s*[-–]\s*(?:(\d+):)?(\d+))?$/);
    if (!m) return ref;
    var book = m[1], ch = parseInt(m[2], 10);
    var a = toEnglishVerse(book, ch, parseInt(m[3], 10));
    if (!m[5]) return book + " " + a.chapter + ":" + a.verse;
    var endCh = m[4] ? parseInt(m[4], 10) : ch;
    var b = toEnglishVerse(book, endCh, parseInt(m[5], 10));
    if (b.chapter === a.chapter) return book + " " + a.chapter + ":" + a.verse + "-" + b.verse;
    return book + " " + a.chapter + ":" + a.verse + "-" + b.chapter + ":" + b.verse;
  }

  // ------------------------------------------------------------- loading
  // Nothing at boot. Readiness is contents, never truthiness: the
  // placeholders all start life as {}.

  var inFlight = {};
  function once(key, run) {
    if (inFlight[key]) return inFlight[key];
    inFlight[key] = run().catch(function () { inFlight[key] = null; });
    return inFlight[key];
  }
  function outlineSlug(b) { return BOOK_SLUGS[b] || String(b).toLowerCase().replace(/\s+/g, "-"); }
  function dataSlug(b) { return String(b).toLowerCase().replace(/\s+/g, ""); }

  function ensureOutline(book) {
    if (!book || OUTLINES[book]) return Promise.resolve();
    return once("o:" + book, function () {
      return fetch(OUTLINE_BASE + outlineSlug(book) + ".json")
        .then(function (r) { return r.json(); })
        .then(function (d) { OUTLINES[book] = d; render(); });
    });
  }
  function ensureBook(book) {
    if (!book || DATA[book]) return Promise.resolve();
    return once("b:" + book, function () {
      return fetch(GH + "/" + dataSlug(book) + ".json")
        .then(function (r) { return r.json(); })
        .then(function (d) { DATA[book] = d; render(); });
    });
  }
  function ensureLexicon() {
    if (LEX && Object.keys(LEX).length) return Promise.resolve();
    return once("lex", function () {
      return fetch(GH + "/lexicon.json").then(function (r) { return r.json(); })
        .then(function (d) { LEX = d; render(); });
    });
  }
  function idxKind(key) { return (key && key[0] === "H") ? "hebrew" : "greek"; }
  function idxReady(key) { var i = WORD_INDEX[idxKind(key)]; return !!(i && Object.keys(i).length); }
  function ensureWordIndex(key) {
    if (idxReady(key)) return Promise.resolve();
    var kind = idxKind(key);
    return once("i:" + kind, function () {
      return fetch(GH + (kind === "hebrew" ? "/word-index-hebrew.json" : "/word-index.json"))
        .then(function (r) { return r.json(); })
        .then(function (d) { WORD_INDEX[kind] = d; render(); });
    });
  }

  // --------------------------------------------------------------- state

  var VIEW = { screen: "picker" };
  var selfSetHash = false;

  var uiTestament = null;
  var uiGenre = null;
  var uiTurningOnly = false;
  var uiVersion = "net";
  var uiHebrew = true;
  var uiWord = null;      // { book, c, v, i }
  var openRow = null;     // which passage fold is open on the book screen

  function hashFor(v) {
    if (v.screen === "book")    return "#book=" + encodeURIComponent(v.book);
    if (v.screen === "read")    return "#read=" + encodeURIComponent(v.book) + "|" + encodeURIComponent(v.ref);
    return "#books";
  }
  function viewFromHash() {
    var h = decodeURIComponent(location.hash.replace(/^#/, "")), m;
    if ((m = h.match(/^book=(.+)$/)))            return { screen: "book", book: m[1] };
    if ((m = h.match(/^read=([^|]+)\|(.+)$/)))   return { screen: "read", book: m[1], ref: m[2] };
    return { screen: "picker" };
  }
  function go(v) {
    VIEW = v;
    uiWord = null;
    var h = hashFor(v);
    if (location.hash !== h) { selfSetHash = true; location.hash = h; }
    render();
    MOUNT.scrollIntoView({ block: "start" });
  }
  window.ibGo = go;

  // Attached during parse: a listener that waits on a fetch lets Back fall
  // through to a full document load.
  window.addEventListener("hashchange", function () {
    if (selfSetHash) { selfSetHash = false; return; }
    VIEW = viewFromHash();
    uiWord = null;
    render();
  });

  window.ibTestament = function (t) { uiTestament = t; render(); };
  window.ibGenre     = function (g) { uiGenre = g; render(); };
  window.ibTurning   = function (b) { uiTurningOnly = b; render(); };
  window.ibHebrew    = function () { uiHebrew = !uiHebrew; render(); };
  window.ibVersion   = function (v) { uiVersion = v; verseNodes = {}; render(); };

  // ------------------------------------------------------------- verses

  var verseCache = {};
  var verseNodes = {};

  function fetchVerse(ref, version) {
    var key = version + "|" + ref;
    if (verseCache[key]) return Promise.resolve(verseCache[key]);

    // NET comes straight from labs.bible.org: it answers one row per verse,
    // which is what carries the superscript numbers.
    var direct = version === "net"
      ? fetch("https://labs.bible.org/api/?passage=" + encodeURIComponent(ref) + "&type=json&formatting=plain")
          .then(function (r) { return r.json(); })
          .then(function (j) {
            return (j || []).map(function (v) {
              return '<sup class="ib-vn">' + esc(v.verse) + "</sup>" + esc(String(v.text).trim());
            }).join(" ");
          }).catch(function () { return ""; })
      : Promise.resolve("");

    return direct.then(function (out) {
      if (out) return out;
      if (typeof window.jaymsFetchVerse !== "function") return "";
      return window.jaymsFetchVerse(ref, version)
        .then(function (r) { return r && r.text ? esc(r.text) : ""; })
        .catch(function () { return ""; });
    }).then(function (out) {
      if (out) { verseCache[key] = out; return out; }
      // the shared fetcher cannot parse a range, so a range is walked a
      // verse at a time and stitched back together
      var m = ref.match(/^(.+?)\s+(\d+):(\d+)\s*[-–]\s*(\d+)$/);
      if (!m || typeof window.jaymsFetchVerse !== "function") return "";
      var from = parseInt(m[3], 10), to = parseInt(m[4], 10);
      if (to <= from || to - from > 60) return "";
      var jobs = [];
      for (var n = from; n <= to; n++) {
        (function (n) {
          jobs.push(window.jaymsFetchVerse(m[1] + " " + m[2] + ":" + n, version)
            .then(function (r) { return r && r.text ? '<sup class="ib-vn">' + n + "</sup>" + esc(r.text) : ""; })
            .catch(function () { return ""; }));
        })(n);
      }
      return Promise.all(jobs).then(function (parts) {
        var joined = parts.filter(Boolean).join(" ");
        if (joined) verseCache[key] = joined;
        return joined;
      });
    });
  }

  function fillVerse(id, ref) {
    var node = document.getElementById(id);
    if (!node || node.dataset.v === uiVersion) return;
    node.dataset.v = uiVersion;
    node.textContent = "Loading…";
    fetchVerse(ref, uiVersion).then(function (html) {
      if (!node.isConnected) return;
      node.innerHTML = html || "(" + uiVersion.toUpperCase() + " does not resolve this reference)";
    });
  }

  // -------------------------------------------------------------- picker

  var VERSION_LABEL = { net: "NET", web: "WEB", nlt: "NLT", esv: "ESV" };

  function pill(label, on, call) {
    return '<button type="button" class="ib-pill' + (on ? " on" : "") +
      '" aria-pressed="' + (on ? "true" : "false") + '" onclick="' + call + '">' + esc(label) + "</button>";
  }

  function renderPicker() {
    var shown = BOOKS.filter(function (b) {
      return (!uiTestament || b.testament === uiTestament) && (!uiGenre || b.genre === uiGenre);
    });
    var genres = [];
    BOOKS.forEach(function (b) { if (genres.indexOf(b.genre) < 0) genres.push(b.genre); });

    var controls =
      '<div class="ib-row">' +
        pill("All 66", !uiTestament, "ibTestament(null)") +
        pill("Old Testament", uiTestament === "OT", "ibTestament('OT')") +
        pill("New Testament", uiTestament === "NT", "ibTestament('NT')") +
      "</div>";

    var legend = '<div class="ib-legend"><span class="a-clabel">Genre</span>' +
      genres.map(function (g) {
        var on = uiGenre === g;
        return '<button type="button" class="ib-key' + (on ? " on" : "") + '" aria-pressed="' + (on ? "true" : "false") +
          '" onclick="ibGenre(' + (on ? "null" : attr(g)) + ')">' +
          '<span class="ib-swatch" aria-hidden="true" style="background:var(' + GENRE_TOKEN[g] + ')"></span>' +
          esc(g) + "</button>";
      }).join("") + "</div>";

    function section(t, title, order) {
      var list = shown.filter(function (b) { return b.testament === t; });
      if (!list.length) return "";
      var out = '<h2 class="a-h2 ib-sec">' + title + ' <span class="a-count">' + list.length + " books</span></h2>";
      order.forEach(function (g) {
        var run = list.filter(function (b) { return b.genre === g; });
        if (!run.length) return;
        out += '<h3 class="a-clabel ib-sub">' + esc(g) + " <span>" + run.length + "</span></h3>" +
          '<div class="ib-grid">' + run.map(tile).join("") + "</div>";
      });
      return out;
    }

    return controls + legend +
      section("OT", "Old Testament", GENRE_ORDER_OT) +
      section("NT", "New Testament", GENRE_ORDER_NT);
  }

  function tile(b) {
    return '<button type="button" class="a-card ib-tile" style="border-left-color:var(' + GENRE_TOKEN[b.genre] + ')"' +
      ' onclick="ibGo({screen:\'book\',book:' + attr(b.name) + "})\">" +
      '<span class="ib-tile-name">' + esc(b.name) + "</span>" +
      '<span class="ib-tile-meta">' + b.chapters + " chapter" + (b.chapters === 1 ? "" : "s") + "</span></button>";
  }

  // ---------------------------------------------------------------- book

  function crumbs(trail) {
    var out = ['<button type="button" class="a-crumb" onclick="ibGo({screen:\'picker\'})">← All books</button>'];
    trail.forEach(function (t) {
      out.push('<span class="a-crumb-sep">›</span>');
      out.push(t.go ? '<button type="button" class="a-crumb" onclick="' + t.go + '">' + esc(t.label) + "</button>"
                    : '<span class="a-crumb">' + esc(t.label) + "</span>");
    });
    return '<nav class="a-crumbs" aria-label="Breadcrumb">' + out.join("") + "</nav>";
  }

  function versionBar() {
    return '<div class="ib-legend"><span class="a-clabel" id="ib-tr">Translation</span>' +
      '<span role="group" aria-labelledby="ib-tr" class="ib-row">' +
      ["net","web","nlt","esv"].map(function (v) {
        return pill(VERSION_LABEL[v], uiVersion === v, "ibVersion('" + v + "')");
      }).join("") + "</span>" +
      '<span class="ib-note">Applies to every passage you open on this page.</span></div>';
  }

  function renderBook() {
    var book = VIEW.book, doc = OUTLINES[book];
    if (!doc) { ensureOutline(book); return crumbs([{ label: book }]) + "<p class=\"ib-note\">Loading the outline…</p>"; }

    var eras = (doc.eras || []).map(function (era, i) {
      var events = (era.events || []).filter(function (e) { return !uiTurningOnly || e.colour === "red"; });
      return { era: era, events: events, id: "era" + i };
    }).filter(function (x) { return x.events.length; });

    var rail = '<nav class="ib-rail" aria-label="Movements"><p class="a-clabel">Movements</p>' +
      eras.map(function (x) {
        var range = String(x.era.label || "").split("·").pop().trim();
        return '<a href="#' + x.id + '"><span>' + esc(x.era.name) + "</span><span>" + esc(range) + "</span></a>";
      }).join("") + "</nav>";

    var body = eras.map(function (x) {
      return '<section id="' + x.id + '" class="ib-era">' +
        '<div class="ib-era-head"><h2 class="a-h2">' + esc(x.era.name) + "</h2>" +
        '<span class="a-clabel">' + esc(x.era.label || "") + "</span></div>" +
        (x.era.dates ? '<p class="ib-era-note">' + esc(x.era.dates) + "</p>" : "") +
        '<div class="ib-rows">' + x.events.map(eventRow).join("") + "</div></section>";
    }).join("");

    return crumbs([{ label: book }]) +
      "<h1>" + esc(book) + "</h1>" +
      (doc.desc ? '<p class="a-sub">' + esc(doc.desc) + "</p>" : "") +
      '<div class="ib-row ib-filters">' +
        pill("All events", !uiTurningOnly, "ibTurning(false)") +
        pill("Turning points", uiTurningOnly, "ibTurning(true)") +
      "</div>" +
      versionBar() +
      '<div class="ib-book">' + rail + '<div class="ib-eras">' + body + "</div></div>" +
      attribution();
  }

  function eventRow(ev) {
    var ref = ev.date || "", en = toEnglishRef(ref);
    var id = "p" + en.replace(/[^A-Za-z0-9]/g, "");
    var turning = ev.colour === "red";
    return '<details class="ib-fold' + (turning ? " turning" : "") + '" data-ref="' + esc(en) + '" data-node="' + id + '">' +
      "<summary>" +
        '<span class="ib-ref">' + esc(en) + "</span>" +
        '<span class="ib-title">' + esc(ev.title) + (turning ? ' <span class="ib-flag">Turning point</span>' : "") + "</span>" +
        '<span class="ib-caret" aria-hidden="true">›</span>' +
      "</summary>" +
      '<div class="ib-fold-body">' +
        (ev.detail ? '<p class="ib-detail">' + esc(ev.detail) + "</p>" : "") +
        '<p class="ib-passage" id="' + id + '">Open to read it.</p>' +
        '<div class="ib-row">' +
          '<button type="button" class="a-lnk" onclick="ibGo({screen:\'read\',book:' + attr(VIEW.book) + ",ref:" + attr(ref) + '})">Read it word by word ›</button>' +
          '<span class="ib-note">' + esc(VERSION_LABEL[uiVersion]) + " · fetched live</span>" +
        "</div>" +
      "</div></details>";
  }

  // a fold only fetches when a reader actually opens it
  document.addEventListener("toggle", function (e) {
    var d = e.target;
    if (!d.classList || !d.classList.contains("ib-fold") || !d.open) return;
    fillVerse(d.dataset.node, d.dataset.ref);
  }, true);

  function attribution() {
    return '<p class="a-credit">NET text fetched live from ' +
      '<a href="https://labs.bible.org/" rel="noopener" target="_blank">bible.org’s web service</a>. ' +
      "Hebrew and Greek from the open Macula and SBLGNT datasets.</p>";
  }

  // ---------------------------------------------------------------- read

  function parseRef(ref) {
    var m = String(ref).match(/^(.+?)\s+(\d+):(\d+)(?:\s*[-–]\s*(?:(\d+):)?(\d+))?$/);
    if (!m) return null;
    return { c1: parseInt(m[2], 10), v1: parseInt(m[3], 10),
             c2: m[4] ? parseInt(m[4], 10) : parseInt(m[2], 10),
             v2: m[5] ? parseInt(m[5], 10) : parseInt(m[3], 10) };
  }

  function versesIn(book, p) {
    var out = [], data = DATA[book];
    if (!data || !data.chapters) return out;
    for (var c = p.c1; c <= p.c2; c++) {
      var ch = data.chapters[c];
      if (!ch) continue;
      var keys = Object.keys(ch.verses || {}).map(Number).sort(function (a, b) { return a - b; });
      var from = (c === p.c1) ? p.v1 : 1;
      var to = (c === p.c2) ? p.v2 : (keys.length ? keys[keys.length - 1] : 0);
      for (var v = from; v <= to; v++) if (ch.verses[v]) out.push({ c: c, v: v, words: ch.verses[v].words || [] });
    }
    return out;
  }

  function eventTitleFor(book, ref) {
    var doc = OUTLINES[book];
    if (!doc) return null;
    var hit = null;
    (doc.eras || []).forEach(function (era) {
      (era.events || []).forEach(function (e) { if (e.date === ref) hit = e; });
    });
    return hit;
  }

  window.ibWord = function (c, v, i) { uiWord = { c: c, v: v, i: i }; render(); };

  function renderRead() {
    var book = VIEW.book, ref = VIEW.ref, en = toEnglishRef(ref);
    var ev = eventTitleFor(book, ref);
    var head = crumbs([
      { label: book, go: "ibGo({screen:'book',book:" + attr(book) + "})" },
      { label: en }
    ]) +
      "<h1>" + esc(ev && ev.title ? ev.title : en) + "</h1>" +
      '<p class="a-clabel ib-readref">' + esc(en) + " · " + esc(VERSION_LABEL[uiVersion]) + "</p>";

    var p = parseRef(ref);
    if (!p) return head + "<p class=\"ib-note\">That reference could not be read.</p>";
    if (!DATA[book]) { ensureBook(book); return head + '<p class="ib-note">Loading ' + esc(book) + "…</p>"; }

    var rows = versesIn(book, p);
    if (!rows.length) return head + "<p class=\"ib-note\">No original-language rows for that reference.</p>";

    var controls = '<div class="ib-row ib-filters">' +
      ["net","web","nlt","esv"].map(function (v) { return pill(VERSION_LABEL[v], uiVersion === v, "ibVersion('" + v + "')"); }).join("") +
      pill(uiHebrew ? "Original on" : "Original off", uiHebrew, "ibHebrew()") + "</div>";

    return head + controls +
      '<div class="ib-read">' +
        '<main class="ib-text">' + rows.map(function (r) { return verseBlock(book, r); }).join("") + attribution() + "</main>" +
        wordPanel(book) +
      "</div>";
  }

  function verseBlock(book, row) {
    var en = toEnglishVerse(book, row.c, row.v);
    var id = "v" + row.c + "-" + row.v;
    var rtl = row.words.length && /[֐-׿]/.test(row.words[0].text || "");
    var words = row.words.map(function (w, i) {
      var on = uiWord && uiWord.c === row.c && uiWord.v === row.v && uiWord.i === i;
      return '<button type="button" class="ib-w' + (on ? " on" : "") + '" onclick="ibWord(' +
        row.c + "," + row.v + "," + i + ')">' + esc(w.text) + "</button>";
    }).join(" ");

    return '<section class="ib-verse">' +
      '<p class="a-clabel">' + esc(book + " " + en.chapter + ":" + en.verse) + "</p>" +
      '<p class="ib-passage" id="' + id + '">Loading…</p>' +
      (uiHebrew ? '<p class="ib-orig' + (rtl ? " ib-rtl" : "") + '" lang="' + (rtl ? "he" : "grc") + '"' +
        (rtl ? ' dir="rtl"' : "") + ">" + words + "</p>" : "") +
      "</section>";
  }

  function fillReadVerses() {
    if (VIEW.screen !== "read") return;
    var p = parseRef(VIEW.ref);
    if (!p || !DATA[VIEW.book]) return;
    versesIn(VIEW.book, p).forEach(function (row) {
      var en = toEnglishVerse(VIEW.book, row.c, row.v);
      // asked for by its own English number, or the two halves of the row
      // drift apart wherever the numbering differs
      fillVerse("v" + row.c + "-" + row.v, VIEW.book + " " + en.chapter + ":" + en.verse);
    });
  }

  function wordPanel(book) {
    if (!uiWord) {
      return '<aside class="ib-panel" aria-label="Word detail"><p class="ib-note">' +
        "Tap any word in the original to open it here.</p></aside>";
    }
    var data = DATA[book];
    var ch = data && data.chapters && data.chapters[uiWord.c];
    var w = ch && ch.verses[uiWord.v] && ch.verses[uiWord.v].words[uiWord.i];
    if (!w) return '<aside class="ib-panel" aria-label="Word detail"><p class="ib-note">Not found.</p></aside>';

    var key = w.strong || "";
    var rtl = /[֐-׿]/.test(w.text || "");
    var rows = [];
    [["stem","Stem"],["tense","Tense"],["voice","Voice"],["mood","Mood"],["person","Person"],
     ["number","Number"],["gender","Gender"],["case","Case"],["state","State"]].forEach(function (pair) {
      if (w[pair[0]]) rows.push("<dt>" + esc(pair[1]) + "</dt><dd>" + esc(w[pair[0]]) + "</dd>");
    });
    if (key) rows.push("<dt>Strong’s</dt><dd>" + esc(key) + "</dd>");

    var def = "", kjv = "", deriv = "";
    if (key) {
      if (!LEX || !Object.keys(LEX).length) { ensureLexicon(); def = "Loading the lexicon…"; }
      else {
        // the lexicon's own field names are def, kjv, derivation
        var e = LEX[key], s = e && e.strong;
        def = (s && s.def) ? s.def : "No lexicon entry for " + key + ".";
        kjv = (s && s.kjv) ? s.kjv : "";
        deriv = (s && s.derivation) ? s.derivation : "";
      }
    }

    var occ = "";
    if (key) {
      if (!idxReady(key)) { ensureWordIndex(key); occ = "Counting occurrences…"; }
      else {
        var entry = WORD_INDEX[idxKind(key)][key];
        if (entry && entry.occ) {
          var books = [];
          entry.occ.forEach(function (o) { if (books.indexOf(o.b) < 0) books.push(o.b); });
          occ = entry.occ.length + " time" + (entry.occ.length === 1 ? "" : "s") +
                " across " + books.length + " book" + (books.length === 1 ? "" : "s") + ".";
        } else occ = "Not in the word index.";
      }
    }

    return '<aside class="ib-panel" aria-label="Word detail">' +
      '<p class="ib-panel-word' + (rtl ? " ib-rtl" : "") + '" lang="' + (rtl ? "he" : "grc") + '">' + esc(w.text) + "</p>" +
      (w.translit ? '<p class="ib-panel-translit">' + esc(w.translit) + "</p>" : "") +
      '<p class="ib-panel-gloss">' + esc(w.gloss || w.english || "") + "</p>" +
      (rows.length ? '<dl class="ib-parse">' + rows.join("") + "</dl>" : "") +
      (def ? '<div class="ib-def"><p class="a-clabel">Definition</p><p>' + esc(def) + "</p>" +
             (kjv ? '<p class="ib-note">Rendered in the King James as ' + esc(kjv) + "</p>" : "") +
             (deriv ? '<p class="ib-note">' + esc(deriv) + "</p>" : "") +
             (occ ? '<p class="ib-note">' + esc(occ) + "</p>" : "") + "</div>" : "") +
      "</aside>";
  }

  // ----------------------------------------------------------------- paint

  function render() {
    MOUNT.innerHTML =
      VIEW.screen === "book" ? renderBook() :
      VIEW.screen === "read" ? renderRead() :
                               renderPicker();
    fillReadVerses();
  }

  // ------------------------------------------------------------------ boot

  VIEW = viewFromHash();
  render();
  if (VIEW.screen === "book" || VIEW.screen === "read") ensureOutline(VIEW.book);
})();
</script>
	<?php
} );
