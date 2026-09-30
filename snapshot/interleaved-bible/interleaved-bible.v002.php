<?php
/**
 * JAYMS — Interleaved Bible.
 *
 * Rebuilt 2026-09-29. The version this replaces had grown a stylesheet of
 * its own, a second palette, a bulk data load and four layers of override.
 *
 * Two rules hold it together:
 *
 *   1. It ships no components. The card, the verse box, the label, the body
 *      text, the pills and the chips are jayms.com components and live in
 *      global styles 90171. This file may declare layout -- grid, gap,
 *      flow -- and nothing with a visual opinion. No colours, no font
 *      sizes, no borders.
 *   2. It fetches nothing until a reader asks for it. One book when a
 *      passage opens, the lexicon when Lexicon is pressed, a word index
 *      when Word Study is pressed.
 *
 * Traps, all of them paid for once already:
 *   - #app is the mount id on every tool. Never style by it.
 *   - The theme sets font-family on body, p, li with !important. A card
 *     built from <li> will be serif whatever this file says.
 *   - DATA / LEX / WORD_INDEX start as {}, which is truthy. Readiness is a
 *     question of contents.
 *   - Back to a pushState entry reloads the document on this site. Routing
 *     is hash only, and the listener is attached during parse.
 *   - ?page= ?p= ?paged= ?name= are reserved by WordPress.
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
/* Layout only. Anything here that starts describing how something LOOKS
   belongs in global styles 90171 instead. */
.jayms-tool-outline .ib-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:12px}
.jayms-tool-outline .ib-row{display:flex;flex-wrap:wrap;align-items:center;gap:8px}
.jayms-tool-outline .ib-stack{display:flex;flex-direction:column;gap:10px}
.jayms-tool-outline .ib-era{margin:26px 0 0}
.jayms-tool-outline .ib-events{list-style:none;margin:12px 0 0;padding:0;display:flex;flex-direction:column;gap:12px}
.jayms-tool-outline .ib-verse{margin:0 0 14px}
.jayms-tool-outline .ib-words{display:flex;gap:10px;overflow-x:auto;padding:2px 0 10px}
.jayms-tool-outline .ib-word{flex:0 0 200px;display:flex;flex-direction:column;gap:6px}
.jayms-tool-outline .ib-grams{display:flex;flex-direction:column;gap:2px}
.jayms-tool-outline .ib-gram{display:flex;justify-content:space-between;gap:10px}
.jayms-tool-outline .ib-rtl{direction:rtl;text-align:right}
.jayms-tool-outline .ib-hidden{display:none}
.jayms-tool-outline .ib-occ{margin:0 0 14px}
</style>

<script id="jayms-outline-versification">
/* Hebrew and Greek numbering is not English numbering; they part
   company in 120 chapters. Derived by aligning the interlinear's own
   glosses against an English text, not copied from a list.
   [from, to, englishChapter, delta] -- or "fold" where English keeps a
   Hebrew superscription inside verse 1. */
window.JAYMS_VERSIFICATION = {"1 Chronicles": {"5": [[1, 26, 5, 0], [27, 41, 6, -26]], "6": [[1, 66, 6, 15]], "12": [[1, 1, 11, 46], [2, 41, 12, -1]]}, "1 Kings": {"5": [[1, 14, 4, 20], [15, 32, 5, -14]], "22": [[1, 43, 22, 0], [44, 54, 22, -1]]}, "1 Samuel": {"21": [[1, 1, 20, 41], [2, 16, 21, -1]], "24": [[1, 1, 23, 28], [2, 23, 24, -1]]}, "2 Chronicles": {"1": [[1, 17, 1, 0], [18, 18, 2, -17]], "2": [[1, 17, 2, 1]], "13": [[1, 22, 13, 0], [23, 23, 14, -22]], "14": [[1, 14, 14, 1]]}, "2 Kings": {"12": [[1, 1, 11, 20], [2, 22, 12, -1]]}, "2 Samuel": {"19": [[1, 1, 18, 32], [2, 44, 19, -1]]}, "3 John": {"1": [[1, 15, 1, 0]]}, "Daniel": {"3": [[1, 30, 3, 0], [31, 33, 4, -30]], "4": [[1, 34, 4, 3]], "6": [[1, 1, 5, 30], [2, 29, 6, -1]]}, "Deuteronomy": {"13": [[1, 1, 12, 31], [2, 19, 13, -1]], "23": [[1, 1, 22, 29], [2, 26, 23, -1]], "28": [[1, 68, 28, 0], [69, 69, 29, -68]], "29": [[1, 28, 29, 1]]}, "Ecclesiastes": {"4": [[1, 16, 4, 0], [17, 17, 5, -16]], "5": [[1, 19, 5, 1]]}, "Exodus": {"7": [[1, 25, 7, 0], [26, 29, 8, -25]], "8": [[1, 28, 8, 4]], "21": [[1, 36, 21, 0], [37, 37, 22, -36]], "22": [[1, 30, 22, 1]]}, "Ezekiel": {"21": [[1, 5, 20, 44], [6, 37, 21, -5]]}, "Genesis": {"32": [[1, 1, 31, 54], [2, 33, 32, -1]]}, "Hosea": {"2": [[1, 2, 1, 9], [3, 25, 2, -2]], "12": [[1, 1, 11, 11], [2, 15, 12, -1]], "14": [[1, 1, 13, 15], [2, 10, 14, -1]]}, "Isaiah": {"8": [[1, 22, 8, 0], [23, 23, 9, -22]], "9": [[1, 20, 9, 1]], "64": [[1, 11, 64, 1]]}, "Jeremiah": {"8": [[1, 22, 8, 0], [23, 23, 9, -22]], "9": [[1, 25, 9, 1]]}, "Job": {"40": [[1, 24, 40, 0], [25, 32, 41, -24]], "41": [[1, 26, 41, 8]]}, "Joel": {"3": [[1, 5, 2, 27]], "4": [[1, 21, 3, 0]]}, "Jonah": {"2": [[1, 1, 1, 16], [2, 11, 2, -1]]}, "Leviticus": {"5": [[1, 19, 5, 0], [20, 26, 6, -19]], "6": [[1, 23, 6, 7]]}, "Malachi": {"3": [[1, 18, 3, 0], [19, 24, 4, -18]]}, "Micah": {"4": [[1, 13, 4, 0], [14, 14, 5, -13]], "5": [[1, 14, 5, 1]]}, "Nahum": {"2": [[1, 1, 1, 14], [2, 14, 2, -1]]}, "Nehemiah": {"3": [[1, 32, 3, 0], [33, 38, 4, -32]], "4": [[1, 17, 4, 6]], "7": [[1, 72, 7, 0]], "10": [[1, 1, 9, 37], [2, 40, 10, -1]]}, "Numbers": {"17": [[1, 15, 16, 35], [16, 28, 17, -15]], "25": [[1, 18, 25, 0], [19, 19, 26, -18]], "30": [[1, 1, 29, 39], [2, 17, 30, -1]]}, "Psalms": {"3": [[1, 1, 3, "fold"], [2, 9, 3, -1]], "4": [[1, 1, 4, "fold"], [2, 9, 4, -1]], "5": [[1, 1, 5, "fold"], [2, 13, 5, -1]], "6": [[1, 1, 6, "fold"], [2, 11, 6, -1]], "7": [[1, 1, 7, "fold"], [2, 18, 7, -1]], "8": [[1, 1, 8, "fold"], [2, 10, 8, -1]], "9": [[1, 1, 9, "fold"], [2, 21, 9, -1]], "12": [[1, 1, 12, "fold"], [2, 9, 12, -1]], "18": [[1, 1, 18, "fold"], [2, 51, 18, -1]], "19": [[1, 1, 19, "fold"], [2, 15, 19, -1]], "20": [[1, 1, 20, "fold"], [2, 10, 20, -1]], "21": [[1, 1, 21, "fold"], [2, 14, 21, -1]], "22": [[1, 1, 22, "fold"], [2, 32, 22, -1]], "30": [[1, 1, 30, "fold"], [2, 13, 30, -1]], "31": [[1, 1, 31, "fold"], [2, 25, 31, -1]], "34": [[1, 1, 34, "fold"], [2, 23, 34, -1]], "36": [[1, 1, 36, "fold"], [2, 13, 36, -1]], "38": [[1, 1, 38, "fold"], [2, 23, 38, -1]], "39": [[1, 1, 39, "fold"], [2, 14, 39, -1]], "40": [[1, 1, 40, "fold"], [2, 18, 40, -1]], "41": [[1, 1, 41, "fold"], [2, 14, 41, -1]], "42": [[1, 1, 42, "fold"], [2, 12, 42, -1]], "44": [[1, 1, 44, "fold"], [2, 27, 44, -1]], "45": [[1, 1, 45, "fold"], [2, 18, 45, -1]], "46": [[1, 1, 46, "fold"], [2, 12, 46, -1]], "47": [[1, 1, 47, "fold"], [2, 10, 47, -1]], "48": [[1, 1, 48, "fold"], [2, 15, 48, -1]], "49": [[1, 1, 49, "fold"], [2, 21, 49, -1]], "51": [[1, 2, 51, "fold"], [3, 21, 51, -2]], "52": [[1, 2, 52, "fold"], [3, 11, 52, -2]], "53": [[1, 1, 53, "fold"], [2, 7, 53, -1]], "54": [[1, 2, 54, "fold"], [3, 9, 54, -2]], "55": [[1, 1, 55, "fold"], [2, 24, 55, -1]], "56": [[1, 1, 56, "fold"], [2, 14, 56, -1]], "57": [[1, 1, 57, "fold"], [2, 12, 57, -1]], "58": [[1, 1, 58, "fold"], [2, 12, 58, -1]], "59": [[1, 1, 59, "fold"], [2, 18, 59, -1]], "60": [[1, 2, 60, "fold"], [3, 14, 60, -2]], "61": [[1, 1, 61, "fold"], [2, 9, 61, -1]], "62": [[1, 1, 62, "fold"], [2, 13, 62, -1]], "63": [[1, 1, 63, "fold"], [2, 12, 63, -1]], "64": [[1, 1, 64, "fold"], [2, 11, 64, -1]], "65": [[1, 1, 65, "fold"], [2, 14, 65, -1]], "67": [[1, 1, 67, "fold"], [2, 8, 67, -1]], "68": [[1, 1, 68, "fold"], [2, 36, 68, -1]], "69": [[1, 1, 69, "fold"], [2, 37, 69, -1]], "70": [[1, 1, 70, "fold"], [2, 6, 70, -1]], "75": [[1, 1, 75, "fold"], [2, 11, 75, -1]], "76": [[1, 1, 76, "fold"], [2, 13, 76, -1]], "77": [[1, 1, 77, "fold"], [2, 21, 77, -1]], "80": [[1, 1, 80, "fold"], [2, 20, 80, -1]], "81": [[1, 1, 81, "fold"], [2, 17, 81, -1]], "83": [[1, 1, 83, "fold"], [2, 19, 83, -1]], "84": [[1, 1, 84, "fold"], [2, 13, 84, -1]], "85": [[1, 1, 85, "fold"], [2, 14, 85, -1]], "88": [[1, 1, 88, "fold"], [2, 19, 88, -1]], "89": [[1, 1, 89, "fold"], [2, 53, 89, -1]], "92": [[1, 1, 92, "fold"], [2, 16, 92, -1]], "102": [[1, 1, 102, "fold"], [2, 29, 102, -1]], "108": [[1, 1, 108, "fold"], [2, 14, 108, -1]], "140": [[1, 1, 140, "fold"], [2, 14, 140, -1]], "142": [[1, 1, 142, "fold"], [2, 8, 142, -1]]}, "Song of Solomon": {"7": [[1, 1, 6, 12], [2, 14, 7, -1]]}, "Zechariah": {"2": [[1, 4, 1, 17], [5, 17, 2, -4]]}, "Revelation": {"12": [[1, 17, 12, 0], [18, 18, 12, -1]]}};
</script>

<script id="jayms-outline-app">
(function () {
  "use strict";

  var MOUNT = document.getElementById("app");
  if (!MOUNT) return;

  var OUTLINE_BASE = "https://raw.githubusercontent.com/sixcore-droid/jayms-tool-data/main/outline/";
  var GH = "https://raw.githubusercontent.com/sixcore-droid/interlinear-data/main";

  // Names and slugs come from the server (snippet 139) so the picker can
  // paint before anything is fetched.
  var BOOK_SLUGS = window.JAYMS_OUTLINE_BOOKS || {};
  var BOOK_ORDER = window.JAYMS_OUTLINE_ORDER || [];
  var PRELOAD    = window.JAYMS_OUTLINE_PRELOAD || {};

  var OUTLINES = {};   // book -> outline json
  var DATA     = {};   // book -> interlinear json
  var LEX      = null;
  var WORD_INDEX = { hebrew: null, greek: null };

  Object.keys(PRELOAD).forEach(function (b) { OUTLINES[b] = PRELOAD[b]; });

  // ---------------------------------------------------------------- utils

  function esc(s) {
    return String(s == null ? "" : s)
      .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");
  }

  function el(html) {
    var t = document.createElement("template");
    t.innerHTML = html.trim();
    return t.content;
  }

  // ------------------------------------------------------- versification
  // Source numbering in the data, English everywhere a reader or a
  // translation sees it. Derived by aligning the interlinear's own glosses
  // against an English text, not copied from a list. 119 chapters.

  var VERSIFICATION = window.JAYMS_VERSIFICATION || {};

  function toEnglishVerse(book, chapter, verse) {
    var segs = VERSIFICATION[book] && VERSIFICATION[book][chapter];
    if (!segs) return { chapter: chapter, verse: verse };
    for (var i = 0; i < segs.length; i++) {
      var s = segs[i];
      if (verse >= s[0] && verse <= s[1]) {
        return s[3] === "fold"
          ? { chapter: s[2], verse: 1 }
          : { chapter: s[2], verse: verse + s[3] };
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
  // Nothing at boot. Each loader answers a promise, records that it is in
  // flight, and re-renders when it lands. Readiness is contents, never
  // truthiness: every placeholder starts life as {}.

  var inFlight = {};

  function once(key, run) {
    if (inFlight[key]) return inFlight[key];
    inFlight[key] = run().catch(function () { inFlight[key] = null; });
    return inFlight[key];
  }

  function slugFor(book) {
    return BOOK_SLUGS[book] || String(book).toLowerCase().replace(/\s+/g, "-");
  }

  function ensureOutline(book) {
    if (!book || OUTLINES[book]) return Promise.resolve();
    return once("outline:" + book, function () {
      return fetch(OUTLINE_BASE + slugFor(book) + ".json")
        .then(function (r) { return r.json(); })
        .then(function (d) { OUTLINES[book] = d; render(); });
    });
  }

  function ensureBook(book) {
    if (!book || DATA[book]) return Promise.resolve();
    return once("book:" + book, function () {
      return fetch(GH + "/" + String(book).toLowerCase().replace(/\s+/g, "") + ".json")
        .then(function (r) { return r.json(); })
        .then(function (d) { DATA[book] = d; render(); });
    });
  }

  function ensureLexicon() {
    if (LEX && Object.keys(LEX).length) return Promise.resolve();
    return once("lex", function () {
      return fetch(GH + "/lexicon.json")
        .then(function (r) { return r.json(); })
        .then(function (d) { LEX = d; render(); });
    });
  }

  function indexKind(key) { return (key && key[0] === "H") ? "hebrew" : "greek"; }

  function indexReady(key) {
    var i = WORD_INDEX[indexKind(key)];
    return !!(i && Object.keys(i).length);
  }

  function ensureWordIndex(key) {
    if (indexReady(key)) return Promise.resolve();
    var kind = indexKind(key);
    return once("idx:" + kind, function () {
      var file = kind === "hebrew" ? "/word-index-hebrew.json" : "/word-index.json";
      return fetch(GH + file)
        .then(function (r) { return r.json(); })
        .then(function (d) { WORD_INDEX[kind] = d; render(); });
    });
  }

  // --------------------------------------------------------------- state

  var VIEW = { screen: "picker" };
  var selfSetHash = false;

  function hashFor(v) {
    if (v.screen === "book")    return "#book=" + encodeURIComponent(v.book);
    if (v.screen === "passage") return "#read=" + encodeURIComponent(v.book) + "|" + encodeURIComponent(v.ref);
    if (v.screen === "word")    return "#word=" + encodeURIComponent(v.key);
    return "#all";
  }

  function viewFromHash() {
    var h = decodeURIComponent(location.hash.replace(/^#/, ""));
    var m;
    if ((m = h.match(/^book=(.+)$/)))  return { screen: "book", book: m[1] };
    if ((m = h.match(/^read=([^|]+)\|(.+)$/))) return { screen: "passage", book: m[1], ref: m[2] };
    if ((m = h.match(/^word=(.+)$/)))  return { screen: "word", key: m[1] };
    return { screen: "picker" };
  }

  function go(v) {
    VIEW = v;
    var h = hashFor(v);
    if (location.hash !== h) { selfSetHash = true; location.hash = h; }
    render();
    MOUNT.scrollIntoView({ block: "start" });
  }
  window.ibGo = go;

  // Attached during parse. If this waits for a fetch, Back falls through to
  // a full document load.
  window.addEventListener("hashchange", function () {
    if (selfSetHash) { selfSetHash = false; return; }
    VIEW = viewFromHash();
    render();
  });

  // ---------------------------------------------------------------- books
  // name, testament, chapters, genre, has an outline

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
["Matthew","NT",28,"Gospel"],["Mark","NT",16,"Gospel"],["Luke","NT",24,"Gospel"],["John","NT",21,"Gospel"],
["Acts","NT",28,"History"],["Romans","NT",16,"Pauline Epistle"],["1 Corinthians","NT",16,"Pauline Epistle"],
["2 Corinthians","NT",13,"Pauline Epistle"],["Galatians","NT",6,"Pauline Epistle"],
["Ephesians","NT",6,"Pauline Epistle"],["Philippians","NT",4,"Pauline Epistle"],
["Colossians","NT",4,"Pauline Epistle"],["1 Thessalonians","NT",5,"Pauline Epistle"],
["2 Thessalonians","NT",3,"Pauline Epistle"],["1 Timothy","NT",6,"Pauline Epistle"],
["2 Timothy","NT",4,"Pauline Epistle"],["Titus","NT",3,"Pauline Epistle"],
["Philemon","NT",1,"Pauline Epistle"],["Hebrews","NT",13,"General Epistle"],
["James","NT",5,"General Epistle"],["1 Peter","NT",5,"General Epistle"],
["2 Peter","NT",3,"General Epistle"],["1 John","NT",5,"General Epistle"],
["2 John","NT",1,"General Epistle"],["3 John","NT",1,"General Epistle"],
["Jude","NT",1,"General Epistle"],["Revelation","NT",22,"Apocalyptic"]
  ].map(function (b) { return { name: b[0], testament: b[1], chapters: b[2], genre: b[3] }; });

  var GENRES = ["Law","History","Wisdom","Major Prophets","Minor Prophets",
                "Gospel","Pauline Epistle","General Epistle","Apocalyptic"];

  var filterTestament = null;
  var filterGenre = null;
  var filterEvent = null;

  window.ibSetTestament = function (t) { filterTestament = t; render(); };
  window.ibSetGenre     = function (g) { filterGenre = g; render(); };
  window.ibSetEvent     = function (e) { filterEvent = e; render(); };

  function chip(label, on, call) {
    return '<button class="a-chip' + (on ? " on" : "") + '" onclick="' + call + '">' +
      esc(label) + "</button>";
  }

  function crumbs(trail) {
    var out = ['<button class="a-crumb" onclick="ibGo({screen:\'picker\'})">← All books</button>'];
    trail.forEach(function (t) {
      out.push('<span class="a-crumb-sep">›</span>');
      out.push(t.go
        ? '<button class="a-crumb" onclick="' + t.go + '">' + esc(t.label) + "</button>"
        : '<span class="a-crumb">' + esc(t.label) + "</span>");
    });
    return '<div class="a-crumbs">' + out.join("") + "</div>";
  }

  // A reference is written the way the rest of the site writes one, so the
  // LSB tagger links it and it reads the same here as in a post.
  function reference(ref) {
    var en = toEnglishRef(ref);
    return '<span class="tl-ref jayms-bible-ref jayms-lsb-ref" data-lsb-ref="' +
      esc(en) + '">' + esc(en) + "</span>";
  }

  // ---------------------------------------------------------------- picker

  function renderPicker() {
    var shown = BOOKS.filter(function (b) {
      return (!filterTestament || b.testament === filterTestament) &&
             (!filterGenre || b.genre === filterGenre);
    });

    var testaments = '<div class="ib-row">' +
      chip("All", !filterTestament, "ibSetTestament(null)") +
      chip("Old Testament", filterTestament === "OT", "ibSetTestament('OT')") +
      chip("New Testament", filterTestament === "NT", "ibSetTestament('NT')") +
      "</div>";

    var genres = '<div class="ib-row">' +
      chip("All genres", !filterGenre, "ibSetGenre(null)") +
      GENRES.map(function (g) {
        return chip(g, filterGenre === g, "ibSetGenre('" + g.replace(/'/g, "\\'") + "')");
      }).join("") + "</div>";

    function section(t, title) {
      var list = shown.filter(function (b) { return b.testament === t; });
      if (!list.length) return "";
      return '<h2 class="a-h2">' + title + ' <span class="a-count">' + list.length + "</span></h2>" +
        '<div class="ib-grid">' + list.map(tile).join("") + "</div>";
    }

    return '<div class="ib-stack">' + testaments + genres + "</div>" +
      section("OT", "Old Testament") + section("NT", "New Testament");
  }

  function tile(b) {
    return '<button class="a-card" onclick="ibGo({screen:\'book\',book:' +
      JSON.stringify(b.name).replace(/"/g, "&quot;") + '})">' +
      '<span class="a-clabel">' + esc(b.name) + "</span>" +
      '<span class="a-cmain">' + b.chapters + " chapters</span>" +
      '<span class="a-tag">' + esc(b.genre) + "</span></button>";
  }

  // ------------------------------------------------------------------ book

  function renderBook() {
    var book = VIEW.book;
    var doc = OUTLINES[book];
    if (!doc) { ensureOutline(book); return crumbs([{ label: book }]) + "<p>Loading the outline…</p>"; }

    var filters = '<div class="ib-row">' +
      chip("Show all", !filterEvent, "ibSetEvent(null)") +
      chip("Major events", filterEvent === "red", "ibSetEvent('red')") +
      chip("Standard events", filterEvent === "gold", "ibSetEvent('gold')") +
      "</div>";

    var eras = (doc.eras || []).map(function (era) {
      var events = (era.events || []).filter(function (e) {
        return !filterEvent || e.colour === filterEvent;
      });
      if (!events.length) return "";
      return '<section class="ib-era">' +
        '<p class="a-clabel">Era ' + esc(era.number) + "</p>" +
        '<h2 class="a-h2">' + esc(era.name) + "</h2>" +
        (era.dates ? '<p class="a-btext">' + esc(era.dates) + "</p>" : "") +
        '<ul class="ib-events">' + events.map(eventCard).join("") + "</ul></section>";
    }).join("");

    return crumbs([{ label: book }]) +
      '<h1>' + esc(book) + "</h1>" +
      (doc.desc ? '<p class="a-sub">' + esc(doc.desc) + "</p>" : "") +
      filters + eras + attribution();
  }

  function eventCard(ev) {
    var ref = ev.date || "";
    var en = toEnglishRef(ref);
    return '<li class="a-card">' +
      '<div class="ib-row">' + reference(ref) +
        '<span class="a-cmain">' + esc(ev.title) + "</span></div>" +
      (ev.detail ? '<p class="a-cmain">' + esc(ev.detail) + "</p>" : "") +
      '<details class="ib-pass" data-ref="' + esc(en) + '">' +
        "<summary>Full passage</summary>" + versionBox(en) +
      "</details>" +
      '<button class="a-lnk" onclick=\'ibGo({screen:"passage",book:' +
        JSON.stringify(VIEW.book) + ',ref:' + JSON.stringify(ref) + '})\'>' +
        "Read it word by word ›</button></li>";
  }

  // The verse box. Same markup Divine Council uses, so it is the same
  // component from the same definition -- nothing here styles it.
  function versionBox(ref) {
    var pills = ["net", "web", "nlt", "esv"].map(function (v) {
      return '<button class="a-vb' + (v === "net" ? " on" : "") +
        '" data-v="' + v + '" onclick="ibVersion(this)">' + v.toUpperCase() + "</button>";
    }).join("");
    return '<div class="a-vsw">' + pills + "</div>" +
      '<div class="a-box v-gold">' +
        '<div class="a-blabel">' + esc(ref) + " &middot; NET</div>" +
        '<div class="a-btext">Open to read it.</div></div>';
  }

  var verseCache = {};

  window.ibVersion = function (btn) {
    var wrap = btn.closest(".ib-pass");
    wrap.querySelectorAll(".a-vb").forEach(function (b) { b.classList.remove("on"); });
    btn.classList.add("on");
    loadVerse(wrap, btn.dataset.v);
  };

  function loadVerse(wrap, version) {
    var ref = wrap.dataset.ref;
    var label = wrap.querySelector(".a-blabel");
    var body  = wrap.querySelector(".a-btext");
    label.innerHTML = esc(ref) + " &middot; " + version.toUpperCase();
    body.textContent = "Loading…";
    fetchVerse(ref, version).then(function (html) {
      body.innerHTML = html || "(" + version.toUpperCase() + " does not resolve this reference)";
    });
  }

  function fetchVerse(ref, version) {
    var key = version + "|" + ref;
    if (verseCache[key]) return Promise.resolve(verseCache[key]);

    // NET comes straight from labs.bible.org because it answers one row per
    // verse, which is what carries the superscript numbers.
    var direct = version === "net"
      ? fetch("https://labs.bible.org/api/?passage=" + encodeURIComponent(ref) + "&type=json&formatting=plain")
          .then(function (r) { return r.json(); })
          .then(function (j) {
            return (j || []).map(function (v) {
              return "<sup>" + esc(v.verse) + "</sup>" + esc(v.text);
            }).join(" ");
          }).catch(function () { return ""; })
      : Promise.resolve("");

    return direct.then(function (out) {
      if (out) return out;
      if (typeof window.jaymsFetchVerse !== "function") return "";
      return window.jaymsFetchVerse(ref, version)
        .then(function (res) { return res && res.text ? esc(res.text) : ""; })
        .catch(function () { return ""; });
    }).then(function (out) {
      // The shared fetcher cannot parse a range, so a range is walked a
      // verse at a time and stitched back together.
      if (out) { verseCache[key] = out; return out; }
      var m = ref.match(/^(.+?)\s+(\d+):(\d+)\s*[-–]\s*(\d+)$/);
      if (!m || typeof window.jaymsFetchVerse !== "function") return "";
      var from = parseInt(m[3], 10), to = parseInt(m[4], 10);
      if (to <= from || to - from > 60) return "";
      var jobs = [];
      for (var n = from; n <= to; n++) {
        (function (n) {
          jobs.push(window.jaymsFetchVerse(m[1] + " " + m[2] + ":" + n, version)
            .then(function (r) { return r && r.text ? "<sup>" + n + "</sup>" + esc(r.text) : ""; })
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

  // Fetch the passage the first time a reader opens the fold, not before.
  document.addEventListener("toggle", function (e) {
    var d = e.target;
    if (!d.classList || !d.classList.contains("ib-pass") || !d.open || d.dataset.loaded) return;
    d.dataset.loaded = "1";
    loadVerse(d, "net");
  }, true);

  function attribution() {
    return '<p class="a-credit">NET text fetched live from ' +
      '<a href="https://labs.bible.org/" rel="noopener" target="_blank">bible.org’s web service</a>. ' +
      "Hebrew and Greek from the open Macula and SBLGNT datasets.</p>";
  }

  // --------------------------------------------------------------- passage

  function parseRef(ref) {
    var m = String(ref).match(/^(.+?)\s+(\d+):(\d+)(?:\s*[-–]\s*(?:(\d+):)?(\d+))?$/);
    if (!m) return null;
    return {
      book: m[1],
      c1: parseInt(m[2], 10), v1: parseInt(m[3], 10),
      c2: m[4] ? parseInt(m[4], 10) : parseInt(m[2], 10),
      v2: m[5] ? parseInt(m[5], 10) : parseInt(m[3], 10)
    };
  }

  function versesIn(book, p) {
    var out = [], data = DATA[book];
    if (!data || !data.chapters) return out;
    for (var c = p.c1; c <= p.c2; c++) {
      var ch = data.chapters[c];
      if (!ch) continue;
      var from = (c === p.c1) ? p.v1 : 1;
      var keys = Object.keys(ch.verses || {}).map(Number).sort(function (a, b) { return a - b; });
      var to = (c === p.c2) ? p.v2 : (keys.length ? keys[keys.length - 1] : 0);
      for (var v = from; v <= to; v++) if (ch.verses[v]) out.push({ c: c, v: v, words: ch.verses[v].words || [] });
    }
    return out;
  }

  function renderPassage() {
    var book = VIEW.book, ref = VIEW.ref;
    var trail = [{ label: book, go: "ibGo({screen:'book',book:" + JSON.stringify(book).replace(/"/g, "&quot;") + "})" },
                 { label: toEnglishRef(ref) }];
    var head = crumbs(trail) + "<h1>" + esc(toEnglishRef(ref)) + "</h1>";

    var p = parseRef(ref);
    if (!p) return head + "<p>That reference could not be read.</p>";
    if (!DATA[book]) { ensureBook(book); return head + "<p>Loading " + esc(book) + "…</p>"; }

    var rows = versesIn(book, p);
    if (!rows.length) return head + "<p>No interlinear rows for that reference.</p>";

    return head +
      '<p class="a-sub">The original text word by word, with the NET alongside. ' +
      "Verse numbers are the English ones.</p>" +
      rows.map(function (r) { return verseBlock(book, r); }).join("") +
      attribution();
  }

  function verseBlock(book, row) {
    var en = toEnglishVerse(book, row.c, row.v);
    var id = "ib-net-" + row.c + "-" + row.v;
    var isHeb = row.words.length && /[֐-׿]/.test(row.words[0].text || "");
    var line = row.words.map(function (w) { return (w.text || "") + (w.after == null ? " " : w.after); }).join("").trim();

    return '<section class="ib-verse">' +
      '<p class="a-blabel">' + esc(book + " " + en.chapter + ":" + en.verse) + "</p>" +
      '<p class="a-btext' + (isHeb ? " ib-rtl" : "") + '">' + esc(line) + "</p>" +
      '<p class="a-btext" id="' + id + '">Loading the NET…</p>' +
      '<div class="ib-words">' + row.words.map(wordCard).join("") + "</div></section>";
  }

  function wordCard(w) {
    var key = w.strong || "";
    var gram = [];
    ["stem","tense","voice","mood","person","number","gender","case","state"].forEach(function (k) {
      if (w[k]) gram.push('<span class="ib-gram"><span>' + esc(k) + "</span><span>" + esc(w[k]) + "</span></span>");
    });
    // Only the two actions are components. Everything else on the card is
    // plain text -- a card of eight chips reads as eight buttons.
    return '<div class="a-card ib-word">' +
      '<span class="a-lead' + (/[\u0590-\u05FF]/.test(w.text || "") ? " ib-rtl" : "") + '">' + esc(w.text) + "</span>" +
      (w.translit ? "<span><i>" + esc(w.translit) + "</i></span>" : "") +
      '<span class="a-clabel">' + esc(w.gloss || w.english || "") + "</span>" +
      (gram.length ? '<span class="ib-grams">' + gram.join("") + "</span>" : "") +
      (key ? "<span><small>Strong\u2019s " + esc(key) + "</small></span>" : "") +
      (key
        ? '<span class="ib-row">' +
            '<button class="a-lnk" onclick="ibLexicon(\'' + esc(key) + '\')">Lexicon</button>' +
            '<button class="a-lnk" onclick="ibGo({screen:\'word\',key:\'' + esc(key) + '\'})">Word study</button>' +
          "</span>"
        : "") +
      "</div>";
  }

  // The NET line per verse, filled in after paint so the original text is
  // readable straight away.
  function fillPassageNet() {
    if (VIEW.screen !== "passage") return;
    var p = parseRef(VIEW.ref);
    if (!p || !DATA[VIEW.book]) return;
    versesIn(VIEW.book, p).forEach(function (row) {
      var node = document.getElementById("ib-net-" + row.c + "-" + row.v);
      if (!node || node.dataset.filled) return;
      node.dataset.filled = "1";
      // asked for by its own English number, or the two halves of the row
      // drift apart wherever the numbering differs
      var en = toEnglishVerse(VIEW.book, row.c, row.v);
      fetchVerse(VIEW.book + " " + en.chapter + ":" + en.verse, "net").then(function (html) {
        if (!node.isConnected) return;
        node.innerHTML = html || "(NET unavailable for this verse)";
      });
    });
  }

  // ------------------------------------------------------------ word study

  function renderWord() {
    var key = VIEW.key;
    if (!indexReady(key)) { ensureWordIndex(key); return crumbs([{ label: "Word study" }]) + "<p>Loading the word index…</p>"; }
    var entry = WORD_INDEX[indexKind(key)][key];
    if (!entry) return crumbs([{ label: "Word study" }]) + "<p>Nothing recorded for " + esc(key) + ".</p>";

    var occ = entry.occ || [];
    var books = [];
    occ.forEach(function (o) { if (books.indexOf(o.b) < 0) books.push(o.b); });
    var page = occ.slice(0, 50);

    // the books this page of occurrences needs, and only those
    var want = [];
    page.forEach(function (o) { if (want.indexOf(o.b) < 0) want.push(o.b); });
    want.forEach(ensureBook);

    return crumbs([{ label: "Word study" }]) +
      "<h1>" + esc(entry.lemma || key) + "</h1>" +
      '<p class="a-sub">' + esc(entry.gloss || "") + " &middot; " + esc(key) + "</p>" +
      '<p class="a-btext">' + occ.length + " occurrence" + (occ.length === 1 ? "" : "s") +
        " across " + books.length + " book" + (books.length === 1 ? "" : "s") +
        (occ.length > 50 ? ", the first 50 shown" : "") + ".</p>" +
      page.map(occurrence).join("") + attribution();
  }

  function occurrence(o) {
    var en = toEnglishVerse(o.b, o.c, o.v);
    var data = DATA[o.b];
    var verse = data && data.chapters && data.chapters[o.c] && data.chapters[o.c].verses[o.v];
    var line = verse
      ? verse.words.map(function (w) { return (w.text || "") + (w.after == null ? " " : w.after); }).join("").trim()
      : "";
    return '<section class="ib-occ">' +
      '<p class="a-blabel">' + esc(o.b + " " + en.chapter + ":" + en.verse) + "</p>" +
      (line ? '<p class="a-btext ib-rtl">' + esc(line) + "</p>" : "") +
      "</section>";
  }

  // --------------------------------------------------------------- lexicon
  // The panel is a <dialog>, so the browser handles the backdrop, focus and
  // Escape. It sits outside #app, which is why it carries the scope classes
  // itself rather than inheriting them.

  var dialog = null;

  function lexDialog() {
    if (dialog) return dialog;
    dialog = document.createElement("dialog");
    dialog.className = "jayms-tool-alpha jayms-tool-outline";
    dialog.innerHTML = '<div class="a-box v-gold">' +
      '<p class="a-blabel" data-lex-head>Lexicon</p>' +
      '<div class="a-btext" data-lex-body></div>' +
      '<form method="dialog"><button class="a-lnk">Close</button></form></div>';
    document.body.appendChild(dialog);
    return dialog;
  }

  window.ibLexicon = function (key) {
    var d = lexDialog();
    var head = d.querySelector("[data-lex-head]");
    var body = d.querySelector("[data-lex-body]");
    if (!d.open) d.showModal();

    if (!LEX || !LEX[key]) {
      head.textContent = key;
      body.textContent = "Loading the lexicon…";   // 8.8MB on first press
      ensureLexicon().then(function () { if (LEX && LEX[key]) window.ibLexicon(key); });
      return;
    }
    var e = LEX[key], s = e.strong || {};
    head.textContent = (s.lemma || key) + (s.xlit ? " · " + s.xlit : "");
    body.innerHTML =
      (s.pron ? "<p>" + esc(s.pron) + "</p>" : "") +
      (s.strongs_def ? "<p>" + esc(s.strongs_def) + "</p>" : "") +
      (s.kjv_def ? "<p>" + esc(s.kjv_def) + "</p>" : "") +
      (e.classic && e.classic.def ? "<p>" + esc(e.classic.def) + "</p>" : "");
  };

  // ----------------------------------------------------------------- paint

  function render() {
    var html =
      VIEW.screen === "book"    ? renderBook() :
      VIEW.screen === "passage" ? renderPassage() :
      VIEW.screen === "word"    ? renderWord() :
                                  renderPicker();
    MOUNT.innerHTML = html;

    // References are drawn after the LSB tagger has run its own pass, so
    // they have to be handed to it again. It reads plain text as well as
    // marked spans, so only where marked spans exist.
    if (MOUNT.querySelector(".jayms-lsb-ref")) {
      window.dispatchEvent(new Event("lsb-reftagger.trigger-linkify"));
    }
    fillPassageNet();
  }

  // ------------------------------------------------------------------ boot

  VIEW = viewFromHash();
  render();
  if (VIEW.screen === "book" || VIEW.screen === "passage") ensureOutline(VIEW.book);
})();

</script>
	<?php
} );
