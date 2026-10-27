/* WORD TRAIL: follow one word from the Hebrew, through the Greek Old Testament, into the New Testament.
   Self-contained: no snippet, no library. Mounts on #jayms-word-trail. Data lives in jayms-tool-data,
   branch word-trail, folder word-trail/. Routing is the hash only (#w=H776&g=G1093&view=uses&v=123),
   never pushState: back to a pushState entry reloads the document on this site. */
(function () {
  "use strict";
  var MOUNT = document.getElementById("jayms-word-trail");
  if (!MOUNT || MOUNT.getAttribute("data-wt-ready")) return;
  MOUNT.setAttribute("data-wt-ready", "1");
  var BASE = MOUNT.getAttribute("data-base") ||
    "https://raw.githubusercontent.com/sixcore-droid/jayms-tool-data/word-trail/word-trail/";
  var ECHO = "https://jayms.com/bible-study-tools-2/echoes-of-revelation/";
  var WORDSTUDY = "https://jayms.com/bible-study-tools-2/bible-word-study-tool/";
  var TDX = "https://jayms.com/bible-study-tools-2/bible-translation-differences/";
  var FACTBOOK = "https://jayms.com/bible-study-tools-2/bible-entity-explorer/";
  var GODS = "https://jayms.com/bible-study-tools-2/gods-of-the-bible/";
  var NETAPI = "https://labs.bible.org/api/?type=json&formatting=plain&passage=";
  var SVGNS = "http://www.w3.org/2000/svg";

  // ---------- small helpers ----------
  function esc(s) { return String(s == null ? "" : s).replace(/[&<>"']/g, function (c) { return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]; }); }
  function fmt(n) { return Number(n).toLocaleString("en-US"); }
  function yr(y) { if (y == null) return ""; y = Math.round(y); return y < 0 ? fmt(-y) + " BC" : "AD " + y; }
  function span(a, b) { return a === b ? yr(a) : (a < 0 && b < 0 ? fmt(-a) + " to " + fmt(-b) + " BC" : yr(a) + " to " + yr(b)); }
  function el(tag, cls, html) { var e = document.createElement(tag); if (cls) e.className = cls; if (html != null) e.innerHTML = html; return e; }
  function cache() { var c = {}; return function (url) { if (!c[url]) c[url] = fetch(url).then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); }); return c[url]; }; }
  var getJSON = cache();
  function data(name) { return getJSON(BASE + name); }
  function shardOf(s) { return s.charAt(0) + Math.floor(parseInt(s.slice(1), 10) / 100); }
  function word(s) { return data("words/" + shardOf(s) + ".json").then(function (d) { return d[s] || null; }); }
  function uniq(a) { var o = [], last = -1; for (var i = 0; i < a.length; i++) if (a[i] !== last) { o.push(a[i]); last = a[i]; } return o; }
  // the first unbroken run of verse ids, as one reference: an echo may cite two passages, and the reader shows the first
  function runRef(ids) {
    var a = ids[0], z = a; for (var j = 1; j < ids.length && ids[j] === z + 1; j++) z = ids[j];
    var r0 = refOf(a); if (z === a) return r0;
    var r1 = refOf(z), c0 = r0.split(" ").pop().split(":")[0], c1 = r1.split(" ").pop().split(":");
    return r0 + "-" + (c1[0] === c0 ? c1[1] : c1.join(":"));
  }
  // a source line links only to this site's own pages; anything else is named as not yet checked
  function srcNote(u) { return u && /^https:\/\/jayms\.com\//.test(u) ? '<div class="a-bnote">More: <a href="' + esc(u) + '">' + esc(u.replace(/^https:\/\/jayms\.com/, "jayms.com")) + "</a></div>" : '<div class="a-bnote">Standard date, not yet checked in Logos.</div>'; }
  function lang(s) { return s && s.charAt(0) === "H" ? "he" : "grc"; }

  // ---------- state ----------
  var S = { w: "H776", g: null, view: "uses", v: null, book: null, fb: null, from: null, layers: {}, names: true, order: "canon",
            scope: "word", grade: { clear: 1, probable: 1, possible: 1 }, link: null,
            hinge: { exodus: "early", egypt: "430" }, person: null, onlyWord: false, item: null };
  var DEFAULT_LAYERS = { heb: 1, nt: 1, a: 1, b: 1, ms: 1, find: 1, tr: 1, uses: 1, people: 1, events: 1, famous: 1,
    egypt: 1, meso: 1, anatolia: 0, persia: 0, greece: 0, rome: 0, china: 1, india: 0, africa: 0, americas: 0 };
  for (var k in DEFAULT_LAYERS) S.layers[k] = DEFAULT_LAYERS[k];
  function readHash() {
    var h = (location.hash || "").replace(/^#/, ""); if (!h) return;
    // The address is the whole state. A part it does not name is cleared, not left over from the last word, or going
    // to a word with no Greek chosen would keep following the Greek word of the one before it.
    S.g = null; S.from = null; S.fb = null; S.v = null;
    h.split("&").forEach(function (kv) {
      var p = kv.split("="), k = p[0], v = decodeURIComponent(p[1] || "");
      if (k === "w" && /^[HG]\d+$/.test(v)) S.w = v;
      if (k === "g" && /^G\d+$/.test(v)) S.g = v;
      if (k === "view" && VIEWS.indexOf(v) >= 0) S.view = v;
      if (k === "v" && /^\d+$/.test(v)) S.v = parseInt(v, 10);
      if (k === "fb" && /^[a-z0-9-]+$/.test(v)) S.fb = v;
      if (k === "from" && /^G\d+$/.test(v)) S.from = v;
    });
  }
  function writeHash() {
    var h = "#w=" + S.w + (S.g ? "&g=" + S.g : "") + "&view=" + S.view + (S.v != null ? "&v=" + S.v : "") + (S.fb ? "&fb=" + S.fb : "") + (S.from ? "&from=" + S.from : "");
    if (location.hash !== h) location.replace(h);
  }

  // ---------- the verse grid ----------
  var BOOKS = null, NV = 0, NTS = 0;
  function bookOf(i) { var lo = 0, hi = BOOKS.length - 1; while (lo < hi) { var m = (lo + hi + 1) >> 1; if (BOOKS[m].s <= i) lo = m; else hi = m - 1; } return lo; }
  // chapter number of a verse id, from the book's verses-per-chapter list
  function chOf(i) { var b = BOOKS[bookOf(i)], off = i - b.s, c = 0; while (c < b.c.length - 1 && off >= b.c[c]) { off -= b.c[c]; c++; } return c + 1; }
  function refOf(i) {
    var b = BOOKS[bookOf(i)], r = i - b.s, c = 0;
    while (c < b.c.length && r >= b.c[c]) { r -= b.c[c]; c++; }
    return b.n + " " + (c + 1) + ":" + (r + 1);
  }
  function bookName(i) { return BOOKS[bookOf(i)].n; }

  // ---------- layout ----------
  // which views this page shows. Word Trail carries Every use and Where it lands; Word Timelines carries the two
  // clocks. data-views on the mount names them; data-other-url and data-other-label link to the sister page,
  // carrying the word across.
  var VIEWN = { uses: "Every use", lands: "Where it lands", written: "When it was written", happened: "When it happened" };
  var VIEWS = String(MOUNT.getAttribute("data-views") || "uses,lands,written,happened").split(/\s*,\s*/).filter(function (v) { return VIEWN[v]; });
  if (!VIEWS.length) VIEWS = ["uses"];
  S.view = VIEWS[0];
  var OTHER = MOUNT.getAttribute("data-other-url") || "", OTHERL = MOUNT.getAttribute("data-other-label") || "";
  // data-parts="cards": draw the word cards and stop. Used where the cards sit in one place on a page and the
  // diagram in another, each in its own frame; the frame that is only cards has no use for the diagram.
  var PARTS = String(MOUNT.getAttribute("data-parts") || "");
  MOUNT.innerHTML =
    '<nav class="wt-trail" id="wt-trail" aria-label="Words looked at in this session"></nav>' +
    '<div class="wt-find">' +
      '<label for="wt-q" class="screen-reader-text" style="position:absolute;left:-9999px">Find a word</label>' +
      '<input id="wt-q" class="a-search" type="search" autocomplete="off" spellcheck="false" placeholder="A Hebrew or Greek word, a transliteration, an English meaning, or a Strong\'s number (H776, G1093)">' +
      '<p class="wt-hint">Try erets, ruach, chesed, logos, H430, G26.</p>' +
    '</div>' +
    '<div class="wt-sugg" id="wt-sugg" hidden></div>' +
    '<div class="wt-tabs" role="tablist">' +
      VIEWS.map(function (v) { return '<button class="wt-tab" role="tab" data-view="' + v + '">' + VIEWN[v] + "</button>"; }).join("") +
      (OTHER ? '<a class="wt-other" id="wt-other" href="' + esc(OTHER) + '">' + esc(OTHERL) + " ›</a>" : "") +
    '</div>' +
    '<div id="wt-greek"></div>' +
    '<div class="wt-desk"><aside class="wt-side" id="wt-side"></aside><div class="wt-main" id="wt-main"></div></div>' +
    '<div class="a-foot" id="wt-foot"></div>';
  var SIDE = document.getElementById("wt-side"), MAIN = document.getElementById("wt-main"), GREEK = document.getElementById("wt-greek");
  var Q = document.getElementById("wt-q"), SUGG = document.getElementById("wt-sugg");
  [].forEach.call(MOUNT.querySelectorAll(".wt-tab"), function (b) {
    b.addEventListener("click", function () { S.view = b.getAttribute("data-view"); S.item = null; writeHash(); render(); });
  });

  // ---------- search ----------
  var SEARCH = null;
  function plain(s) { return String(s || "").replace(/[\u00E7\u00C7]/g, "s").normalize("NFD").replace(/[\u0300-\u036f\u0591-\u05C7]/g, "").toLowerCase(); }
  // Strong's spells long vowels with a helper letter (ruwach, chiyl); a reader types ruach, so both are folded
  function fold(s) { return plain(s).replace(/[^a-z ]/g, "").replace(/uw/g, "u").replace(/iy/g, "i").replace(/ow/g, "o").replace(/(.)\1/g, "$1"); }
  function ensureSearch() { if (!SEARCH) SEARCH = data("search.json"); return SEARCH; }

  // Word Study is the one source of truth for each word: its name, gloss, meaning and counts are read live from the
  // same file the Word Study tool reads, so an edit there shows here on the next load. The built search list covers
  // the first seconds; once Word Study arrives its rows replace the word rows (Fact Book rows stay).
  var WSURL = "https://raw.githubusercontent.com/sixcore-droid/jayms-tool-data/main/word-study.json", WSX = null, WSP = null;
  function ensureWS() {
    if (!WSP) WSP = fetch(WSURL, { cache: "no-cache" }).then(function (r) { if (!r.ok) throw 0; return r.json(); }).then(function (j) {
      var x = {};
      (j.entries || []).forEach(function (e) { x[e.strong] = e; });
      WSX = x;
      ensureSearch().then(function (rows) {
        var keep = rows.filter(function (r) { return r[8]; }), have = {};
        rows.forEach(function (r) { if (!r[8]) have[r[0]] = 1; });
        var live = (j.entries || []).filter(function (e) { return have[e.strong]; }).map(function (e) {
          return [e.strong, e.lemma || "", e.title || "", e.gloss || "", (((e.summary || "").slice(0, 60)) + " · " + ((e.kjv || "").slice(0, 70))).replace(/^[ ·]+|[ ·]+$/g, ""),
            e.count || 0, e.xlit || "", /^key/.test(e.band || "") ? 1 : 0];
        });
        SEARCH = Promise.resolve(live.concat(keep).sort(function (a, b) { return (b[7] - a[7]) || ((b[5] || 0) - (a[5] || 0)); }));
        if (Q.value.trim()) runSearch();
      });
      if (CUR) { wsFix(CUR.w, CUR.entry); wsFix(CUR.g, CUR.gEntry); renderSide(); }
      return x;
    }).catch(function () { WSP = null; });
    return WSP;
  }
  // the Fact Book and The Gods of the Bible, read live from the files those tools read, keyed by every Strong's
  // number an entry carries. A word shows a box for each tool it is in, and no box for a tool it is not in.
  var RAW = "https://raw.githubusercontent.com/sixcore-droid/jayms-tool-data/main/", FBX = null, GODX = null;
  function nums(s) { var o = {}; (String(s || "").match(/[HG]\d+/g) || []).forEach(function (n) { o[n] = 1; }); return Object.keys(o); }
  function nrefs(e) { return Array.isArray(e.refs) ? e.refs.length : (String(e.refs || "").split("',").length); }
  function liveIndex(file, row, done) {
    fetch(RAW + file, { cache: "no-cache" }).then(function (r) { if (!r.ok) throw 0; return r.json(); }).then(function (j) {
      var x = {};
      (j.entries || []).forEach(function (e) { var r = row(e); nums(e.strong).forEach(function (n) { (x[n] = x[n] || []).push(r); }); });
      Object.keys(x).forEach(function (n) { x[n].sort(function (a, b) { return b[5] - a[5]; }); });
      done(x);
      if (CUR) renderSide();
    }).catch(function () {});
  }
  liveIndex("fact-book.json", function (e) {
    var nm = String(e.title || "").split(" (")[0];
    return [e.id, nm, String(e.title || "").slice(nm.length).replace(/^[ (]+|[ )]+$/g, ""), e.summary || "", e.kind || e.category || "", nrefs(e)];
  }, function (x) { FBX = x; });
  liveIndex("gods-of-the-bible.json", function (e) {
    return [e.id, e.title || "", "", e.summary || "", e.category || "", nrefs(e)];
  }, function (x) { GODX = x; });

  // lay Word Study's values over a built word entry
  function wsFix(id, en) {
    var w = WSX && id && WSX[id];
    if (!w || !en) return en;
    en.l = w.lemma || en.l; en.x = w.xlit || en.x; en.t = w.title || en.t; en.g = w.gloss || en.g; en.d = w.def || w.summary || en.d;
    en.ws = { n: w.count, v: w.verses, b: w.nbooks };
    return en;
  }
  // the word's own page in Word Study, by the address format Word Study publishes (wordStudy.url, #id={n})
  function wsLink(id, en) {
    var w = WSX && WSX[id], n = w ? w.id : String(id).toLowerCase();
    return '<a class="a-lnk wt-wsl" href="' + WORDSTUDY + "#id=" + encodeURIComponent(n) + '">Open ' + esc((en && (en.t || en.x)) || id) + " in Word Study ›</a>";
  }
  function wsCount(id) { var w = WSX && WSX[id]; return w ? w.count : null; }
  Q.addEventListener("focus", ensureSearch);
  ensureWS();
  var qTimer = null;
  Q.addEventListener("input", function () { clearTimeout(qTimer); qTimer = setTimeout(runSearch, 140); });
  Q.addEventListener("keydown", function (e) {
    if (e.key === "Enter") { var b = SUGG.querySelector("button"); if (b) b.click(); }
    if (e.key === "Escape") { SUGG.hidden = true; }
  });
  function runSearch() {
    var q = Q.value.trim(); if (!q) { SUGG.hidden = true; return; }
    ensureSearch().then(function (rows) {
      var m = q.match(/^([hg])\s*0*(\d+)$/i), out = [];
      if (m) { var id = m[1].toUpperCase() + m[2]; out = rows.filter(function (r) { return r[0] === id; }); }
      else {
        // the same fields Word Study searches: its written-out name, the gloss, the lemma, the transliteration, the meaning.
        // Exact name or gloss first, then the start of a name, then a word inside the gloss or meaning.
        var p = plain(q), pq = fold(q), tiers = [[], [], []];
        for (var i = 0; i < rows.length; i++) {
          var r = rows[i];
          if (r.f == null) { r.f = fold(r[2]); r.x = fold(r[6]); r.gl = " " + plain(r[3]) + " "; r.sm = " " + plain(r[4]).replace(/[^a-z ]/g, " ") + " "; r.lp = plain(r[1]); }
          if (r.f === pq || r.gl.trim() === p || r.lp === p) tiers[0].push(r);
          else if (pq.length > 1 && (r.f.indexOf(pq) === 0 || r.x.indexOf(pq) === 0)) tiers[1].push(r);
          else if (p.length > 2 && (r.gl.indexOf(" " + p) >= 0 || r.sm.indexOf(" " + p) >= 0)) tiers[2].push(r);
        }
        var byCount = function (a, b) { return (b[5] || 0) - (a[5] || 0); };
        out = tiers[0].sort(byCount).concat(tiers[1].sort(byCount), tiers[2]);
      }
      SUGG.innerHTML = out.length ? "" : '<span class="a-empty" style="padding:4px 0">No word matches that.</span>';
      out.slice(0, 16).forEach(function (r) {
        var b = el("button", "a-chip wt-sg " + (r[0][0] === "H" ? "wt-he" : "wt-gr") + (r[0] === S.w && (r[8] || null) === (r[8] ? S.fb : null) ? " on" : ""), '<span class="wt-sgl" lang="' + (r[0][0] === "H" ? "he" : "grc") + '">' + esc(r[1]) + "</span> " + esc(r[2]) + (r[3] ? " · " + esc(r[3]) : "") +
          ' <span class="a-n">' + r[0] + " · " + fmt(r[5]) + "</span>");
        b.title = r[4];
        if (r[8]) b.innerHTML = '<span class="a-n">Fact Book</span> ' + esc(r[2]) + (r[3] ? " · " + esc(r[3]) : "") + ' <span class="a-n">' + r[0] + "</span>";
        // the list stays up after a pick, so the next word is one click away; it changes only on a new search
        b.addEventListener("click", function () {
          [].forEach.call(SUGG.querySelectorAll(".wt-sg"), function (x) { x.classList.remove("on"); }); b.classList.add("on");
          go(r[0], r[8]);
        });
        SUGG.appendChild(b);
      });
      SUGG.hidden = false;
    }).catch(function () { SUGG.innerHTML = '<span class="a-empty">The word list did not load.</span>'; SUGG.hidden = false; });
  }
  function go(id, fb, from) { S.from = from || null; S.fb = fb || null; S.w = id; S.g = null; S.v = null; S.book = null; S.ch = null; S.ntb = null; S.link = null; S.item = null; writeHash(); render(); }

  // ---------- the word card ----------
  var CUR = null;   // { w, entry, rend, inv, g, gEntry }
  function load() {
    return Promise.all([data("books.json"), data("greek-ot.json"), word(S.w)]).then(function (r) {
      var books = r[0], bridge = r[1], e = r[2];
      if (!BOOKS) { BOOKS = books.books; NV = books.verses; NTS = BOOKS[39].s; }
      var heb = S.w.charAt(0) === "H";
      var rend = heb ? (bridge.h[S.w] || []) : [], inv = heb ? [] : (bridge.g[S.w] || []);
      // came here from a Greek word: keep following it, and keep its other Hebrew words on screen
      if (heb && S.from) S.g = S.from;
      if (heb && !S.g) { var first = rend.filter(function (x) { return x[1]; })[0]; S.g = first ? first[1] : null; }
      if (!heb) S.g = S.w;
      return (S.g && S.g !== S.w ? word(S.g) : Promise.resolve(heb ? null : e)).then(function (ge) {
        wsFix(S.w, e); wsFix(S.g, ge);
        CUR = { w: S.w, heb: heb, entry: e, rend: rend, inv: inv, g: S.g, gEntry: ge, missing: bridge.missing || [], fn: (bridge.fn || []).indexOf(S.w) >= 0,
          from: heb && S.from ? S.from : null, sibs: heb && S.from ? (bridge.g[S.from] || []) : [] };
        return CUR;
      });
    });
  }
  function ntUses(ge) { return ge ? ge.v.filter(function (i) { return i >= NTS; }) : []; }
  function bookCount(ids) { var o = {}; ids.forEach(function (i) { o[bookOf(i)] = 1; }); return Object.keys(o).length; }

  // the followed Greek word, laid out the way the Hebrew word is: the word, what it means, how often in the New Testament
  function greekBar(c) {
    var g = c.gEntry;
    if (!g) return '<div class="wt-wb wt-empty"><p class="wt-def wt-wb-def">No Greek word to follow.</p></div>';
    var nt = ntUses(g), ntv = uniq(nt);
    return '<div class="wt-wb wt-gbar"><div class="wt-wb-word"><div class="wt-word"><span class="wt-lemma" lang="grc">' + esc(g.l) + '</span><span class="wt-gloss">' + esc(g.t || g.x) + (g.g ? " · " + esc(g.g) : "") + "</span></div>" +
      '<div class="wt-code">' + esc(c.g) + (g.t ? " · " + esc(g.x) : "") + (g.p ? " · " + esc(g.p) : "") + "</div></div>" +
      '<p class="wt-def wt-wb-def">' + esc(g.d) + "</p>" +
      '<p class="wt-def wt-wb-n"><span class="wt-n">' + fmt(g.ws ? g.ws.n : nt.length) + "</span> uses in <span class=\"wt-n\">" + fmt(g.ws ? g.ws.v : ntv.length) + "</span> verses across <span class=\"wt-n\">" + (g.ws ? g.ws.b : bookCount(ntv)) + "</span> New Testament books.</p>" + wsLink(c.g, g) + "</div>";
  }
  function renderSide() {
    var c = CUR, e = c.entry;
    if (!e) { GREEK.innerHTML = ""; SIDE.innerHTML = '<div class="a-box v-witness"><div class="a-blabel">Not found</div><div class="a-btext">No word has the number ' + esc(c.w) + ".</div></div>"; return; }
    var vs = uniq(e.v), h = "";
    // the word itself, landscape, at the head of the strip above the Greek box
    var wh = '<div class="a-box v-witness wt-strip wt-wordbar ' + (c.heb ? "wt-he" : "wt-gr") + '"><div class="a-blabel">' + (c.heb ? "The Hebrew word" : "The Greek word") + "</div>" +
      '<div class="wt-wb"><div class="wt-wb-word"><div class="wt-word"><span class="wt-lemma" lang="' + lang(c.w) + '">' + esc(e.l) + '</span><span class="wt-gloss">' + esc(e.t || e.x) + (e.g ? " · " + esc(e.g) : "") + "</span></div>" +
      '<div class="wt-code">' + esc(c.w) + (e.t ? " · " + esc(e.x) : "") + (e.p ? " · " + esc(e.p) : "") + "</div></div>" +
      '<p class="wt-def wt-wb-def">' + esc(e.d) + "</p>" +
      '<p class="wt-def wt-wb-n"><span class="wt-n">' + fmt(e.ws ? e.ws.n : e.v.length) + "</span> uses in <span class=\"wt-n\">" + fmt(e.ws ? e.ws.v : vs.length) + "</span> verses across <span class=\"wt-n\">" + (e.ws ? e.ws.b : bookCount(vs)) + "</span> books.</p>" + wsLink(c.w, e) + "</div></div>";
    // every Fact Book entry and every Gods of the Bible entry this word carries, one box per tool, all entries in
    // the box; the one picked in search leads. A tool the word is not in gets no box.
    function refBox(label, tool, base, list, leadId) {
      if (!list || !list.length) return "";
      var lead = list.filter(function (f) { return f[0] === leadId; })[0], fbs = list.slice();
      if (lead) fbs = [lead].concat(fbs.filter(function (f) { return f !== lead; }));
      return '<div class="a-box v-plain"><div class="a-blabel">' + label + (fbs.length > 1 ? " · " + fbs.length + " entries" : "") + "</div>" +
        fbs.map(function (f) {
          return '<div class="wt-fbe' + (f === lead ? " on" : "") + '"><div class="a-btext"><b>' + esc(f[1]) + "</b>" + (f[2] ? ' <span class="wt-fbd">' + esc(f[2]) + "</span>" : "") + (f[3] ? "<br>" + esc(f[3]) : "") + "</div>" +
            '<a class="a-lnk" href="' + base + encodeURIComponent(f[0]) + '/">Open ' + esc(f[1]) + " in " + tool + " ›</a></div>";
        }).join("") + "</div>";
    }
    h += refBox("Fact Book", "the Fact Book", FACTBOOK, FBX ? FBX[c.w] : e.fb, S.fb);
    h += refBox("The Gods of the Bible", "The Gods of the Bible", GODS, GODX ? GODX[c.w] : null, null);
    function hebrewBehind(list, cur, gid) {
      var t2 = list.reduce(function (a, r) { return a + r[1]; }, 0), o = '<div class="a-box v-wording wt-strip wt-he"><div class="a-blabel">The Hebrew behind ' + esc(gid) + '</div><div class="wt-rrow">';
      list.forEach(function (r) {
        var on = r[0] === cur;
        o += '<button class="wt-rend' + (on ? " on" : "") + '" data-h="' + r[0] + '"><span class="wt-rg" lang="he">' + esc(r[2] || r[0]) + "</span>" +
          '<span class="wt-rtop"><span class="wt-rt">' + (wsCount(r[0]) != null ? fmt(wsCount(r[0])) + " uses · " : "") + esc(r[3] || "") + " · " + r[0] + '</span><span class="wt-ra">' + (on ? "Showing" : "Open") + "</span></span>" +
          '<span class="wt-rx">Greek Old Testament: ' + fmt(r[1]) + "× as " + esc(gid) + "</span>" +
          '<span class="wt-bar"><span style="width:' + Math.max(2, Math.round(100 * r[1] / t2)) + '%"></span></span></button>';
      });
      return o + '</div><div class="a-bnote">The Hebrew words the Greek Old Testament translates with ' + esc(gid) + '. Uses come from Word Study; the Greek Old Testament line is how many times that Greek text puts ' + esc(gid) + ' for each one. Pick one to start from the Hebrew; the others stay here.</div></div>';
    }
    var gh = "";
    if (c.heb) {
      if (c.from && c.sibs.length) gh += hebrewBehind(c.sibs, c.w, c.gEntry ? c.gEntry.l : c.from);
      gh += wh;
      gh += '<div class="a-box v-wording wt-strip wt-gr"><div class="a-blabel">The Greek word</div>' + greekBar(c) +
        '<div class="wt-sub">How the Greek Old Testament renders ' + esc(e.t || e.x) + '. Pick one to follow it into the New Testament.</div><div class="wt-rrow">';
      if (!c.rend.length) gh += '<div class="a-btext wt-empty">The Greek Old Testament gives no settled rendering for this word.</div>';
      var tot = c.rend.reduce(function (a, r) { return a + r[2]; }, 0);
      c.rend.forEach(function (r) {
        var on = r[1] && r[1] === c.g;
        gh += '<button class="wt-rend' + (on ? " on" : "") + '" data-g="' + (r[1] || "") + '"' + (r[1] ? "" : " disabled") + ">" +
          '<span class="wt-rg" lang="grc">' + esc(r[0]) + "</span>" +
          '<span class="wt-rtop"><span class="wt-rt">' + (r[1] ? (wsCount(r[1]) != null ? fmt(wsCount(r[1])) + " New Testament uses · " : "") + r[1] : "not in the New Testament") + '</span><span class="wt-ra">' + (on ? "Following" : (r[1] ? "Follow" : "")) + "</span></span>" +
          '<span class="wt-rx">Greek Old Testament: ' + fmt(r[2]) + "× for " + esc(e.t || e.x) + "</span>" +
          '<span class="wt-bar"><span style="width:' + Math.max(2, Math.round(100 * r[2] / tot)) + '%"></span></span></button>';
      });
      gh += '</div><div class="a-bnote">' + (c.fn ? "This is a grammar word. Its counts mostly follow sentence structure, not translation choice, so read them loosely. " : "") +
        'Uses come from Word Study. The Greek Old Testament line is how many times that Greek text puts each Greek word for ' + esc(e.t || e.x) + '.' +
        (c.missing.length ? " Not counted: " + esc(c.missing.join(", ")) + ", which the open Greek text used here does not have." : "") + "</div></div>";
    } else {
      // Hebrew always sits above Greek: the Hebrew words behind this Greek word come first
      if (c.inv.length) gh += hebrewBehind(c.inv, null, e.l);
      else gh += '<div class="a-box v-wording wt-strip wt-he wt-empty"><div class="a-blabel">The Hebrew behind it</div><div class="a-btext">The Greek Old Testament does not use this word for any Hebrew word often enough to count.</div></div>';
      gh += wh;
    }
    h += '<div class="wt-links"><a class="a-lnk" href="' + WORDSTUDY + '">Open Word Study</a><a class="a-lnk" href="' + ECHO + '">Open Echoes of Revelation</a><a class="a-lnk" href="' + TDX + '">Why do translations differ?</a></div>';
    SIDE.innerHTML = h; GREEK.innerHTML = gh;
    [].forEach.call(GREEK.querySelectorAll(".wt-rend[data-g]"), function (b) {
      b.addEventListener("click", function () { var g = b.getAttribute("data-g"); if (!g) return; S.g = g; S.from = null; S.v = null; S.book = null; S.ch = null; writeHash(); render(); });
    });
    [].forEach.call(GREEK.querySelectorAll(".wt-rend[data-h]"), function (b) {
      b.addEventListener("click", function () { go(b.getAttribute("data-h"), null, CUR.heb ? CUR.from : CUR.w); });
    });
  }

  // ---------- shared bits ----------
  function layerBtn(key, cls, onLabel, offLabel, disabled) {
    var on = !!S.layers[key];
    return '<button class="wt-lay ' + cls + (on && !disabled ? " on" : "") + '" data-lay="' + key + '"' + (disabled ? " disabled" : "") +
      '><span class="wt-dot"></span>' + esc(disabled ? offLabel.replace(/^Show /, "") + " (no source yet)" : (on ? onLabel : offLabel)) + "</button>";
  }
  function wireLayers(root, redraw) {
    [].forEach.call(root.querySelectorAll("[data-lay]"), function (b) {
      b.addEventListener("click", function () { var k = b.getAttribute("data-lay"); S.layers[k] = S.layers[k] ? 0 : 1; redraw(); });
    });
  }
  function net(ref) {
    return fetch(NETAPI + encodeURIComponent(ref)).then(function (r) { return r.json(); }).then(function (j) {
      return (j || []).map(function (v) { return String(v.text).trim(); }).join(" ");
    }).catch(function () { return ""; });
  }

  // ---------- the translation ----------
  // One choice for the whole site, under the key the tool runner and the Interleaved Bible read, so a reader who
  // picks KJV here is in KJV there. NET comes from the NET API as it always did; the rest go through the site's
  // shared verse fetcher, which lives on the page. A frame cannot see the page's functions as its own, so it asks
  // the page that holds it.
  var VERS = ["net", "kjv", "web", "nlt", "esv"], VCACHE = {};
  function version() { try { var v = localStorage.getItem("jayms.version"); if (VERS.indexOf(v) > -1) return v; } catch (e) {} return "net"; }
  function setVersion(v) { try { localStorage.setItem("jayms.version", v); } catch (e) {} }
  function shared(name) {
    try {
      if (typeof window[name] === "function") return window[name];
      if (window.parent && window.parent !== window && typeof window.parent[name] === "function") return window.parent[name];
    } catch (e) {}
    return null;
  }
  function unsign(t) { return String(t == null ? "" : t).replace(/\s*\([A-Z]{2,6}\)\s*$/, "").trim(); }
  function verseText(ref, ver) {
    var key = ver + "|" + ref, p;
    if (VCACHE[key]) return Promise.resolve(VCACHE[key]);
    if (ver === "net") { p = net(ref); }
    else {
      var get = shared("jaymsFetchVerse"), norm = shared("jaymsNormRef");
      if (!get) { p = Promise.resolve(""); }
      else {
        var r = norm ? norm(ref) : ref;
        var one = function (q) { return get(q, ver).then(function (x) { return x && x.text ? unsign(x.text) : ""; }).catch(function () { return ""; }); };
        p = one(r).then(function (t) {
          if (t) return t;
          // the KJV and WEB files are keyed one verse at a time, so a range is read verse by verse and put back together
          var m = r.match(/^(.+?)\s+(\d+):(\d+)\s*[-\u2013]\s*(\d+)$/);
          if (!m) return "";
          var a = +m[3], z = +m[4]; if (z <= a || z - a > 60) return "";
          var jobs = []; for (var n = a; n <= z; n++) jobs.push(one(m[1] + " " + m[2] + ":" + n));
          return Promise.all(jobs).then(function (parts) { return parts.filter(Boolean).join(" "); });
        });
      }
    }
    return p.then(function (t) { if (t) VCACHE[key] = t; return t; });
  }
  function versionButtons(ver) {
    var avail = ["net"].concat(shared("jaymsFetchVerse") ? VERS.slice(1) : []);
    if (avail.length < 2) return "";
    return '<div class="wt-vsw" role="group" aria-label="Translation">' + avail.map(function (v) {
      return '<button type="button" class="wt-vb' + (v === ver ? " on" : "") + '" data-v="' + v + '" aria-pressed="' + (v === ver ? "true" : "false") + '">' + v.toUpperCase() + "</button>";
    }).join("") + "</div>";
  }
  // The labels callers pass end in "NET" where they name a translation; that word follows the choice.
  function verseLabel(label, ver) { return String(label).replace(/ \u00b7 NET$/, " \u00b7 " + ver.toUpperCase()); }

  // A verse in a box, in the chosen translation, with the translation buttons in the box. Calling it again on the
  // same box redraws it in place, so anything a caller added to the box afterwards (a link row) survives a switch.
  function verseBox(target, label, ref) {
    var ver = version();
    target.setAttribute("data-ref", ref); target.setAttribute("data-label", label);
    if (!target.querySelector(".a-blabel")) {
      target.innerHTML = '<div class="a-blabel"></div>' + versionButtons(ver) + '<div class="a-btext"></div>';
      [].forEach.call(target.querySelectorAll(".wt-vb"), function (b) {
        b.addEventListener("click", function () {
          var v = b.getAttribute("data-v"); if (v === version()) return;
          setVersion(v);
          [].forEach.call(MOUNT.querySelectorAll(".a-box[data-ref]"), function (x) { verseBox(x, x.getAttribute("data-label"), x.getAttribute("data-ref")); });
        });
      });
    }
    target.querySelector(".a-blabel").textContent = verseLabel(label, ver);
    [].forEach.call(target.querySelectorAll(".wt-vb"), function (b) { var on = b.getAttribute("data-v") === ver; b.classList.toggle("on", on); b.setAttribute("aria-pressed", on ? "true" : "false"); });
    var body = target.querySelector(".a-btext"), want = ver + "|" + ref;
    target.setAttribute("data-want", want);
    body.innerHTML = '<span class="a-loading">Loading the ' + ver.toUpperCase() + " text\u2026</span>";
    verseText(ref, ver).then(function (t) {
      if (target.getAttribute("data-want") !== want) return;   // a later choice has taken the box
      body.innerHTML = t ? esc(t) : '<span class="wt-empty">The ' + ver.toUpperCase() + " text did not load. " + esc(ref) + ".</span>";
    });
  }

  // ---------- view 1: every use ----------
  var DATES = null;   // written-when order needs clock.json
  function axisX(order) {
    // x position per verse id, on a 1000-wide board: the Old Testament first, a gap for the bridge, then the New
    var W = 1000, G = 26, ow = (W - G) * NTS / NV, nw = W - G - ow, seq = [];
    for (var b = 0; b < 39; b++) seq.push(b);
    if (order === "date" && DATES) seq.sort(function (a, b) {
      var A = DATES[BOOKS[a].n], B = DATES[BOOKS[b].n];
      var ma = A ? (A[0] + A[1]) / 2 : 1e9, mb = B ? (B[0] + B[1]) / 2 : 1e9; return ma - mb || a - b;
    });
    var start = {}, acc = 0;
    seq.forEach(function (b) { start[b] = acc; acc += BOOKS[b].c.reduce(function (s, n) { return s + n; }, 0); });
    for (b = 39; b < BOOKS.length; b++) start[b] = BOOKS[b].s - NTS;
    return { W: W, G: G, ow: ow, seq: seq,
      x: function (i) { var bk = bookOf(i), off = i - BOOKS[bk].s; return bk < 39 ? (start[bk] + off + .5) / NTS * ow : ow + G + (start[bk] + off + .5) / (NV - NTS) * nw; },
      bx: function (bk) { var len = BOOKS[bk].c.reduce(function (s, n) { return s + n; }, 0); return bk < 39 ? [start[bk] / NTS * ow, len / NTS * ow] : [ow + G + start[bk] / (NV - NTS) * nw, len / (NV - NTS) * nw]; } };
  }
  function bookOrder(list) {
    if (S.order !== "date" || !DATES) return list;
    return list.slice().sort(function (a, b) {
      var A = DATES[BOOKS[a].n], B = DATES[BOOKS[b].n];
      return (A ? (A[0] + A[1]) / 2 : 1e9) - (B ? (B[0] + B[1]) / 2 : 1e9) || a - b;
    });
  }
  // the stepped pyramid: the word at the top, the books that use it, then one book's verses at the base
  function viewUses() {
    var c = CUR, e = c.entry; if (!e) { MAIN.innerHTML = ""; return; }
    var need = S.order === "date" && !DATES ? data("clock.json").then(function (k) { DATES = k.datesA.books; }) : Promise.resolve();
    need.then(function () {
      var W = 1000, apx = 500, apy = 62, gy = 170, ty = 330, base = 590;
      var heb = c.heb ? uniq(e.v) : [], nt = uniq(ntUses(c.gEntry));
      var byBook = {}; heb.concat(nt).forEach(function (i) { var b = bookOf(i); (byBook[b] = byBook[b] || []).push(i); });
      var ot = bookOrder(Object.keys(byBook).map(Number).filter(function (b) { return b < 39; }).sort(function (a, b) { return a - b; }));
      var ntb = Object.keys(byBook).map(Number).filter(function (b) { return b >= 39; }).sort(function (a, b) { return a - b; });
      if (!S.layers.heb) ot = []; if (!S.layers.nt) ntb = [];
      var max = 1; ot.concat(ntb).forEach(function (b) { max = Math.max(max, byBook[b].length); });
      if (S.book == null || !byBook[S.book] || (S.book < 39 ? ot : ntb).indexOf(S.book) < 0) {
        var pool = ot.length ? ot : ntb; S.book = pool.slice().sort(function (a, b) { return byBook[b].length - byBook[a].length; })[0];
        if (S.v != null && byBook[bookOf(S.v)]) S.book = bookOf(S.v);
      }
      // where the tiers sit: Old Testament books on the left, the Greek word and its New Testament books on the right
      var split = c.heb ? (ntb.length ? 0.68 : 1) : 0, L = 26, R = W - 26, mid = L + (R - L) * split;
      var gx = c.heb ? (mid + 18 + R) / 2 : apx;
      function spread(list, a, z, i) { return list.length === 1 ? (a + z) / 2 : a + (z - a) * i / (list.length - 1); }
      function tierY() { return ty; }
      var nodes = [];
      ot.forEach(function (b, i) { var x = spread(ot, L + 8, mid - 18, i); nodes.push({ b: b, x: x, y: tierY((x - L) / (R - L)), from: [apx, apy], t: "heb" }); });
      ntb.forEach(function (b, i) { var x = spread(ntb, (c.heb ? mid + 18 : L + 8), R - 8, i); nodes.push({ b: b, x: x, y: tierY((x - L) / (R - L)), from: c.heb ? [gx, gy] : [apx, apy], t: "nt" }); });
      var sel = nodes.filter(function (n) { return n.b === S.book; })[0];
      var vs = sel ? byBook[sel.b] : [];
      // a book with more verses than the base can label clearly gets one more step: book, its chapters, then one
      // chapter's verses. A book that fits goes straight to its verses as before.
      var FIT = 32, chs = [], chSel = null, chSvg = "", chLinks = "", from = sel;
      if (sel && vs.length > FIT) {
        var byCh = {}; vs.forEach(function (i) { var cn = chOf(i); (byCh[cn] = byCh[cn] || []).push(i); });
        chs = Object.keys(byCh).map(Number).sort(function (a, b) { return a - b; });
        if (S.v != null && bookOf(S.v) === sel.b) S.ch = chOf(S.v);
        if (S.ch == null || !byCh[S.ch]) S.ch = chs.slice().sort(function (a, b) { return byCh[b].length - byCh[a].length || a - b; })[0];
        var cy = 460, cmax = 1; chs.forEach(function (cn) { cmax = Math.max(cmax, byCh[cn].length); });
        var cgap = chs.length > 1 ? (R - L - 16) / (chs.length - 1) : 999, cstep = Math.max(1, Math.ceil(16 / cgap));
        chs.forEach(function (cn, k) {
          var x = spread(chs, L + 8, R - 8, k), on = cn === S.ch, n = byCh[cn].length, r = 2.5 + 5 * Math.sqrt(n / cmax);
          if (on) chSel = { x: x, y: cy };
          chLinks += '<path class="lk t-' + sel.t + (on ? " on" : "") + '" stroke-width="' + (0.8 + 4 * n / cmax).toFixed(2) + '" d="M' + sel.x.toFixed(1) + " " + sel.y.toFixed(1) + "L" + x.toFixed(1) + " " + cy + '"></path>';
          chSvg += '<g class="bn cn t-' + sel.t + (on ? " on" : "") + '" data-ch="' + cn + '" tabindex="0" role="button" aria-label="' + esc(BOOKS[sel.b].n + " " + cn + ", " + n + (n === 1 ? " verse" : " verses")) + '">' +
            '<circle cx="' + x.toFixed(1) + '" cy="' + cy + '" r="' + Math.max(8, cgap / 2).toFixed(1) + '" class="hit"></circle>' +
            '<circle cx="' + x.toFixed(1) + '" cy="' + cy + '" r="' + r.toFixed(1) + '" class="dot"></circle>' +
            (k % cstep === 0 || on ? '<text class="ct' + (on ? " on" : "") + '" x="' + x.toFixed(1) + '" y="' + (cy - r - 6).toFixed(1) + '" text-anchor="middle">' + cn + "</text>" : "") + "</g>";
        });
        chSvg = '<text class="lbl" x="' + L + '" y="' + (cy - 30) + '">CHAPTERS</text>' + chSvg;
        vs = byCh[S.ch]; from = chSel;
      }
      // verse numbers sit flat and, when the verses are tight, staggered over up to three lines with a thin
      // lead down from each dot, so no number lands on its neighbour
      var gapU = vs.length > 1 ? (R - L) / (vs.length - 1) : 999, LW = chs.length ? 20 : 30;
      var rows = Math.min(3, Math.max(1, Math.ceil(LW / gapU))), step = Math.max(1, Math.ceil(LW / (gapU * rows)));
      var fan = "", dots = "", selLine = "", lead = "", nLab = 0;
      vs.forEach(function (i, k) {
        var x = spread(vs, L, R, k);
        fan += "M" + from.x.toFixed(1) + " " + from.y.toFixed(1) + "L" + x.toFixed(1) + " " + base;
        if (i === S.v) selLine = "M" + from.x.toFixed(1) + " " + from.y.toFixed(1) + "L" + x.toFixed(1) + " " + base;
        if (vs.length <= 220) dots += '<circle class="vd' + (i === S.v ? " on" : "") + '" cx="' + x.toFixed(1) + '" cy="' + base + '" r="' + (i === S.v ? 5 : 2.6) + '"></circle>';
        if (k % step === 0 || i === S.v) {
          var cv = chs.length ? refOf(i).split(":").pop() : refOf(i).split(" ").pop(), row = (i === S.v ? 0 : nLab % rows), ly = base + 18 + row * 15;
          if (i !== S.v) nLab++;
          if (row > 0) lead += "M" + x.toFixed(1) + " " + (base + 5) + "L" + x.toFixed(1) + " " + (ly - 9);
          dots += '<text class="vn' + (i === S.v ? " on" : "") + '" x="' + x.toFixed(1) + '" y="' + ly + '" text-anchor="middle">' + cv + "</text>";
        }
      });
      var links = nodes.map(function (n) {
        var w = (1 + 9 * byBook[n.b].length / max).toFixed(2), on = n.b === S.book;
        return '<path class="lk t-' + n.t + (on ? " on" : "") + '" stroke-width="' + w + '" d="M' + n.from[0].toFixed(1) + " " + n.from[1] + "Q" + n.from[0].toFixed(1) + " " + ((n.from[1] + n.y) / 2).toFixed(1) + " " + n.x.toFixed(1) + " " + n.y.toFixed(1) + '"></path>';
      }).join("");
      var nodeSvg = nodes.map(function (n) {
        var r = (3.5 + 9 * Math.sqrt(byBook[n.b].length / max)), on = n.b === S.book;
        return '<g class="bn t-' + n.t + (on ? " on" : "") + '" data-book="' + n.b + '" tabindex="0" role="button" aria-label="' + esc(BOOKS[n.b].n + ", " + byBook[n.b].length + " verses") + '">' +
          '<circle cx="' + n.x.toFixed(1) + '" cy="' + n.y.toFixed(1) + '" r="' + (r + 6).toFixed(1) + '" class="hit"></circle>' +
          '<circle cx="' + n.x.toFixed(1) + '" cy="' + n.y.toFixed(1) + '" r="' + r.toFixed(1) + '" class="dot"></circle>' +
          '<text class="ab" x="' + n.x.toFixed(1) + '" y="' + (n.y - r - 5).toFixed(1) + '" transform="rotate(-55 ' + n.x.toFixed(1) + " " + (n.y - r - 5).toFixed(1) + ')">' + esc(BOOKS[n.b].a) + "</text>" +
          '<text class="ct" x="' + n.x.toFixed(1) + '" y="' + (n.y + r + 13).toFixed(1) + '" text-anchor="middle">' + byBook[n.b].length + "</text></g>";
      }).join("");
      var greek = c.heb && c.gEntry && ntb.length ? '<path class="lk t-grk" stroke-width="4" d="M' + apx + " " + apy + "Q" + apx + " " + gy + " " + gx.toFixed(1) + " " + gy + '"></path>' +
        '<circle class="gn" cx="' + gx.toFixed(1) + '" cy="' + gy + '" r="9"></circle><text class="gl" x="' + gx.toFixed(1) + '" y="' + (gy - 16) + '" text-anchor="middle" lang="grc">' + esc(c.gEntry.l) + "</text>" +
        '<text class="apg" x="' + (gx + 16).toFixed(1) + '" y="' + (gy + 5) + '">' + esc(c.gEntry.x) + (c.gEntry.g ? " · " + esc(c.gEntry.g) : "") + "</text>" +
        '<text class="lbl" x="' + gx.toFixed(1) + '" y="' + (gy + 30) + '" text-anchor="middle">GREEK OLD TESTAMENT</text>' : "";
      var h = '<div class="wt-panel">' +
        '<p class="wt-stat">' + (c.heb ? "<b>" + fmt(heb.length) + "</b> Old Testament verses in <b>" + Object.keys(byBook).filter(function (b) { return b < 39; }).length + "</b> books" : "") +
        (c.gEntry && nt.length ? (c.heb ? ", and " : "") + "<b>" + fmt(nt.length) + "</b> New Testament verses with <span lang=\"grc\">" + esc(c.gEntry.l) + "</span>" : "") +
        ". Each line from the top is as thick as its book's count. Pick a book and its verses fan out along the bottom; pick a verse to read it.</p>" +
        '<div class="wt-ctl">' +
          (c.heb ? layerBtn("heb", "c-heb", "Hide Old Testament books", "Show Old Testament books") : "") +
          layerBtn("nt", "c-nt", "Hide New Testament books", "Show New Testament books", !c.g) +
          '<button class="wt-lay on" data-order="1">' + (S.order === "canon" ? "Order by date written" : "Order by canon") + "</button>" +
        "</div>" +
        '<div class="wt-arcwrap"><svg class="wt-pyr" viewBox="0 0 ' + W + " " + (base + 40 + rows * 15) + '" role="img" aria-label="The word at the top, a line to each book that uses it, and the chosen book\'s verses along the bottom">' +
          '<path class="fan t-' + (sel ? sel.t : "heb") + '" d="' + fan + '"></path><path class="vlead" d="' + lead + '"></path>' + (selLine ? '<path class="sel" d="' + selLine + '"></path>' : "") +
          links + chLinks + greek + nodeSvg + chSvg + dots +
          '<circle class="apex' + (c.heb ? "" : " gr") + '" cx="' + apx + '" cy="' + apy + '" r="9"></circle>' +
          '<text class="apl' + (c.heb ? "" : " gr") + '" x="' + apx + '" y="' + (apy - 30) + '" text-anchor="middle" lang="' + lang(c.w) + '">' + esc(e.l) + "</text>" +
          '<text class="apg" x="' + (apx + 18) + '" y="' + (apy + 5) + '">' + esc(e.x) + (e.g ? " · " + esc(e.g) : "") + "</text>" +
          '<text class="lbl" x="' + L + '" y="' + (base + 30 + rows * 15) + '">' + esc(sel ? (BOOKS[sel.b].n + (chs.length ? " " + S.ch : "") + " · " + vs.length + (vs.length === 1 ? " verse" : " verses") + (chs.length ? " of " + byBook[sel.b].length + " in the book" : "")).toUpperCase() : "") + "</text>" +
          '<rect class="basehit" x="0" y="' + (base - 16) + '" width="' + W + '" height="60"></rect>' +
        "</svg></div>" +
        '<div class="wt-legend">' + (c.heb ? '<span class="c-heb"><i></i>Old Testament books</span><span class="c-grk"><i></i>Greek Old Testament word</span>' : "") +
        '<span class="c-nt"><i></i>New Testament books</span><span class="c-sel"><i></i>Selected verse</span><span id="wt-hovref"></span></div></div>' +
        '<div class="wt-pair" id="wt-verses"></div>';
      MAIN.innerHTML = h;
      wireLayers(MAIN, viewUses);
      MAIN.querySelector("[data-order]").addEventListener("click", function () { S.order = S.order === "canon" ? "date" : "canon"; viewUses(); });
      [].forEach.call(MAIN.querySelectorAll("g.bn[data-book]"), function (g) {
        function pick() { S.book = +g.getAttribute("data-book"); S.ch = null; S.v = null; writeHash(); viewUses(); }
        g.addEventListener("click", pick);
        g.addEventListener("keydown", function (ev) { if (ev.key === "Enter" || ev.key === " ") { ev.preventDefault(); pick(); } });
      });
      [].forEach.call(MAIN.querySelectorAll("g.cn"), function (g) {
        function pickCh() { S.ch = +g.getAttribute("data-ch"); S.v = null; writeHash(); viewUses(); }
        g.addEventListener("click", pickCh);
        g.addEventListener("keydown", function (ev) { if (ev.key === "Enter" || ev.key === " ") { ev.preventDefault(); pickCh(); } });
      });
      var svg = MAIN.querySelector("svg"), hr = MAIN.querySelector("#wt-hovref"), hit = MAIN.querySelector(".basehit");
      var wrap = MAIN.querySelector(".wt-arcwrap");
      if (wrap.scrollWidth > wrap.clientWidth && sel) wrap.scrollLeft = Math.max(0, sel.x / W * wrap.scrollWidth - wrap.clientWidth / 2);
      function nearest(evt) {
        var pt = svg.createSVGPoint(); pt.x = evt.clientX; pt.y = evt.clientY;
        var p = pt.matrixTransform(svg.getScreenCTM().inverse());
        if (!vs.length) return null; var k = Math.round((p.x - L) / (R - L) * (vs.length - 1)); return vs[Math.max(0, Math.min(vs.length - 1, k))];
      }
      hit.addEventListener("mousemove", function (evt) { var i = nearest(evt); hr.textContent = i == null ? "" : refOf(i); });
      hit.addEventListener("mouseleave", function () { hr.textContent = ""; });
      hit.addEventListener("click", function (evt) { var i = nearest(evt); if (i == null) return; S.v = i; writeHash(); viewUses(); });
      showVerses(S.v != null && byBook[bookOf(S.v)] ? S.v : vs[0]);
    });
  }
  function showVerses(i) {
    var box = MAIN.querySelector("#wt-verses"); if (!box) return;
    box.innerHTML = '<div class="a-box v-scripture" id="wt-v1"></div><div class="a-box v-scripture" id="wt-v2"></div>';
    var ref = refOf(i);
    verseBox(box.querySelector("#wt-v1"), (S.v != null ? "Selected · " : "First in " + bookName(i) + " · ") + ref + " · NET", ref);
    var v2 = box.querySelector("#wt-v2");
    if (i < NTS) { v2.className = "a-box v-explain wt-empty"; v2.innerHTML = '<div class="a-blabel">The Old Testament passage it echoes</div><div class="a-btext">This box is for New Testament verses. Pick a New Testament verse and, when it echoes an Old Testament passage, that passage shows here beside it.</div>'; return; }
    inter(bookOf(i)).then(function (rows) {
      var mine = rows.filter(function (r) { return r[0] <= i && i <= r[1] && r[2] < NTS; });
      var hv = {}; if (CUR.heb && CUR.entry) CUR.entry.v.forEach(function (x) { hv[x] = 1; });
      mine.sort(function (a, b) { return (hitsAny(b[2], b[3], hv) ? 1 : 0) - (hitsAny(a[2], a[3], hv) ? 1 : 0) || b[5] - a[5] || (b[4] === "q" ? 1 : 0) - (a[4] === "q" ? 1 : 0); });
      var hit = mine[0];
      if (!hit) { v2.className = "a-box v-explain wt-empty"; v2.innerHTML = '<div class="a-blabel">The Old Testament passage it echoes</div><div class="a-btext">No shared wording, graded echo or cross reference ties this verse to the Old Testament.</div>'; return; }
      var otref = rng(hit[2], hit[3]);
      verseBox(v2, "The Old Testament passage it echoes · " + otref + " · " + SRC[hit[4]].toLowerCase() + ", " + pairNote(hit), otref);
      if (hit[4] === "e") v2.appendChild(el("div", "wt-links", '<a class="a-lnk" href="' + ECHO + encodeURIComponent(hit[6]) + '/">Open the Echoes of Revelation entry ›</a>'));
    });
  }

  // ---------- view 2: where it lands ----------
  // Three sources, each named on screen: shared wording (UBS Parallel Passages), graded echoes (Echoes of
  // Revelation) and reader-voted cross references (OpenBible.info). Lines take the color of the Old Testament
  // group they start in, and their stroke says how strong the link is.
  var SRC = { q: "Shared wording", e: "Graded echo", x: "Cross reference" };
  var STR = { 3: "Strong", 2: "Medium", 1: "Light" };
  var SRCP = { q: "shared wording", e: "graded echoes", x: "cross references" };
  // a list row names its source only when it is not a plain cross reference, the common case
  function tagOf(r) { return (r[4] === "x" ? "" : SRC[r[4]].toLowerCase() + " · ") + STR[r[5]].toLowerCase(); }
  function grp(b) {
    return b < 5 ? "law" : b < 17 ? "hist" : b < 22 ? "wis" : b < 39 ? "pro" : b < 44 ? "gos" : b < 57 ? "paul" : "gen";
  }
  var GRPN = { law: "Law", hist: "History", wis: "Wisdom and poetry", pro: "Prophets", gos: "Gospels and Acts", paul: "Paul's letters", gen: "Hebrews, the general letters, Revelation" };
  function inter(b) { return data("intertext/b" + b + ".json"); }
  function rng(a, z) { return a === z ? refOf(a) : (function () { var r0 = refOf(a), r1 = refOf(z), c0 = r0.split(" ").pop().split(":")[0], c1 = r1.split(" ").pop().split(":"); return r0 + "-" + (c1[0] === c0 ? c1[1] : c1.join(":")); })(); }
  function pairNote(r) {
    return r[4] === "e" ? r[5] === 3 ? "graded clear" : r[5] === 2 ? "graded probable" : "graded possible"
      : r[4] === "x" ? fmt(r[6]) + " reader votes" : r[6] + "% of the words match";
  }
  function hitsAny(a, z, set) { for (var i = a; i <= z; i++) if (set[i]) return true; return false; }
  function viewLands() {
    var c = CUR;
    if (!S.src) S.src = { q: 1, e: 1, x: 1 };
    if (!S.st) S.st = { 3: 1, 2: 1, 1: 0 };
    var hv = {}, gv = {}, perBook = {};
    if (c.heb && c.entry) c.entry.v.forEach(function (i) { hv[i] = 1; });
    if (c.gEntry) c.gEntry.v.forEach(function (i) { if (i >= NTS) { gv[i] = 1; var b = bookOf(i); perBook[b] = (perBook[b] || 0) + 1; } });
    if (S.ntb == null || S.ntb < 39) {
      var best = Object.keys(perBook).map(Number).sort(function (a, b) { return perBook[b] - perBook[a]; })[0];
      S.ntb = best != null ? best : 65;
    }
    var NB = S.ntb;
    inter(NB).then(function (rows) {
      var ot = rows.filter(function (r) { return r[2] < NTS; });
      var word = ot.filter(function (r) { return hitsAny(r[2], r[3], hv) || hitsAny(r[0], r[1], gv); });
      var list = (S.scope === "word" ? word : ot).filter(function (r) { return S.src[r[4]] && S.st[r[5]]; });
      list.sort(function (a, b) { return b[5] - a[5] || (a[4] === "q" ? -1 : 0) - (b[4] === "q" ? -1 : 0) || a[0] - b[0]; });
      var nCh = BOOKS[NB].c.length, H = Math.max(39 * 18 + 20, nCh * 14 + 28), W = 640;
      function yO(b) { return 14 + b * (H - 28) / 38; }
      function yN(ch) { return 14 + (ch - 0.5) * (H - 28) / nCh; }
      var used = {}; list.forEach(function (r) { used[bookOf(r[2])] = 1; });
      var CAP = 1500, paths = {}, hits = "";
      list.slice(0, CAP).forEach(function (r, n) {
        var y1 = yO(bookOf(r[2])), y2 = yN(chOf(r[0])), d = "M120 " + y1.toFixed(1) + "C" + (W / 2) + " " + y1.toFixed(1) + " " + (W / 2) + " " + y2.toFixed(1) + " " + (W - 70) + " " + y2.toFixed(1);
        var k = "g-" + grp(bookOf(r[2])) + " s" + r[5]; paths[k] = (paths[k] || "") + d;
        if (n < 400) hits += '<path class="hit" data-i="' + n + '" fill="none" d="' + d + '"></path>';
      });
      var sel = S.link != null ? list.filter(function (r) { return r.join() === S.link; })[0] : null;
      var labs = "";
      for (var b = 0; b < 39; b++) labs += '<text x="112" y="' + (yO(b) + 4).toFixed(1) + '" text-anchor="end" class="g-' + grp(b) + (used[b] ? "" : " dim") + '">' + esc(BOOKS[b].n) + "</text>";
      var chStep = nCh > 40 ? 5 : nCh > 20 ? 2 : 1;
      for (var ch = 1; ch <= nCh; ch++) if (ch === 1 || ch % chStep === 0 || ch === nCh) labs += '<text class="nt" x="' + (W - 62) + '" y="' + (yN(ch) + 4).toFixed(1) + '">' + esc(BOOKS[NB].a) + " " + ch + "</text>";
      var layers = Object.keys(paths).sort(function (a, b) { return a.slice(-1) - b.slice(-1); }).map(function (k) { return '<path class="ln ' + k + '" d="' + paths[k] + '"></path>'; }).join("");
      var chips = "";
      for (b = 39; b < BOOKS.length; b++) chips += '<button class="a-chip wt-ntb' + (b === NB ? " on" : "") + (perBook[b] ? "" : " none") + '" data-ntb="' + b + '">' + esc(BOOKS[b].a) + (perBook[b] ? ' <span class="a-n">' + perBook[b] + "</span>" : "") + "</button>";
      var h = '<div class="wt-panel">' +
        '<div class="wt-sec">Pick a New Testament book' + (c.gEntry ? ' <span class="wt-secn">the number is how many of its verses use <span lang="grc">' + esc(c.gEntry.l) + "</span></span>" : "") + '</div><div class="wt-ntrow">' + chips + "</div>" +
        '<p class="wt-stat">' + (S.scope === "word" ? "<b>" + fmt(word.length) + "</b> links between the Old Testament and " + esc(BOOKS[NB].n) + " carry this word" : "<b>" + fmt(ot.length) + "</b> links between the Old Testament and " + esc(BOOKS[NB].n) + " in all") +
        (list.length !== (S.scope === "word" ? word.length : ot.length) ? ", <b>" + fmt(list.length) + "</b> shown with these filters" : "") + ". Old Testament on the left, " + esc(BOOKS[NB].n) + " chapter by chapter on the right." +
        (list.length > CAP ? " The strongest " + fmt(CAP) + " are drawn." : "") + "</p>" +
        '<div class="wt-ctl"><button class="wt-lay on" data-scope="1">' + (S.scope === "word" ? "Show every link" : "Show only links with this word") + "</button></div>" +
        '<div class="wt-ctl">' + ["q", "e", "x"].map(function (k) { return '<button class="wt-lay' + (S.src[k] ? " on" : "") + '" data-src="' + k + '">' + (S.src[k] ? "Hide " : "Show ") + SRCP[k] + "</button>"; }).join("") +
        [3, 2, 1].map(function (k) { return '<button class="wt-lay' + (S.st[k] ? " on" : "") + '" data-st="' + k + '"><span class="wt-gline s' + k + '"></span>' + (S.st[k] ? "Hide " : "Show ") + STR[k].toLowerCase() + "</button>"; }).join("") + "</div>" +
        '<div class="wt-flow"><svg viewBox="0 0 ' + W + " " + H + '" role="img" aria-label="Links from Old Testament books to ' + esc(BOOKS[NB].n) + ', colored by Old Testament group">' +
        layers + (sel ? '<path class="l-sel" fill="none" d="M120 ' + yO(bookOf(sel[2])).toFixed(1) + "C" + (W / 2) + " " + yO(bookOf(sel[2])).toFixed(1) + " " + (W / 2) + " " + yN(chOf(sel[0])).toFixed(1) + " " + (W - 70) + " " + yN(chOf(sel[0])).toFixed(1) + '"></path>' : "") +
        hits + labs + "</svg></div>" +
        '<div class="wt-legend">' + ["law", "hist", "wis", "pro"].map(function (g) { return '<span class="g-' + g + '"><i></i>' + GRPN[g] + "</span>"; }).join("") + "</div></div>" +
        '<div id="wt-linkcard"></div>' +
        '<div class="wt-list">' + (list.length ? list.slice(0, 200).map(function (r) {
          return '<button class="wt-li g-' + grp(bookOf(r[2])) + (sel && sel.join() === r.join() ? " on" : "") + '" data-link="' + r.join() + '"><span>' + esc(rng(r[0], r[1]) + " ← " + rng(r[2], r[3])) + '</span><span class="a-tag">' + esc(tagOf(r)) + "</span></button>";
        }).join("") + (list.length > 200 ? '<div class="wt-li wt-empty"><span>The first 200 of ' + fmt(list.length) + ' are listed, strongest first.</span></div>' : "") : '<div class="wt-li wt-empty"><span>No link with these filters. Try showing light links, or every link.</span></div>') + "</div>" +
        '<div class="a-box v-explain" style="margin-top:13px"><div class="a-blabel">Where the links come from</div><div class="a-btext">' +
        "<b>Shared wording</b> is the UBS Parallel Passages Database: passages whose words match, word by word, which is where the New Testament quotes the Old. Strong means at least 40 percent of the words in one of the two verses match. " +
        "<b>Graded echoes</b> are the entries of Echoes of Revelation, graded clear (strong), probable (medium) or possible (light) there, with their sources named. " +
        "<b>Cross references</b> are OpenBible.info's, where readers vote on each one: 50 votes or more is strong, 15 to 49 medium, 5 to 14 light. " +
        "A link carries this word when its Old Testament verses use the Hebrew word or its New Testament verse uses the Greek word being followed.</div></div>";
      MAIN.innerHTML = h;
      MAIN.querySelector("[data-scope]").addEventListener("click", function () { S.scope = S.scope === "word" ? "all" : "word"; S.link = null; viewLands(); });
      [].forEach.call(MAIN.querySelectorAll("[data-src]"), function (b) { b.addEventListener("click", function () { var k = b.getAttribute("data-src"); S.src[k] = S.src[k] ? 0 : 1; viewLands(); }); });
      [].forEach.call(MAIN.querySelectorAll("[data-st]"), function (b) { b.addEventListener("click", function () { var k = b.getAttribute("data-st"); S.st[k] = S.st[k] ? 0 : 1; viewLands(); }); });
      [].forEach.call(MAIN.querySelectorAll("[data-ntb]"), function (b) { b.addEventListener("click", function () { S.ntb = +b.getAttribute("data-ntb"); S.link = null; viewLands(); }); });
      function pick(id) { S.link = id; viewLands(); }
      [].forEach.call(MAIN.querySelectorAll("[data-link]"), function (b) { b.addEventListener("click", function () { pick(b.getAttribute("data-link")); }); });
      [].forEach.call(MAIN.querySelectorAll("path.hit"), function (p) { p.addEventListener("click", function () { pick(list[+p.getAttribute("data-i")].join()); }); });
      if (sel) {
        var lc = MAIN.querySelector("#wt-linkcard"), oref = rng(sel[2], sel[3]), nref = rng(sel[0], sel[1]);
        lc.innerHTML = '<div class="wt-pair"><div class="a-box v-scripture" id="wt-lo"></div><div class="a-box v-scripture" id="wt-ln"></div></div>' +
          '<div class="wt-links" style="margin:10px 0 13px"><span class="a-tag">' + esc(SRC[sel[4]] + " · " + STR[sel[5]].toLowerCase()) + '</span><span class="a-tag">' + esc(pairNote(sel)) + "</span>" +
          (sel[4] === "e" ? '<a class="a-lnk" href="' + ECHO + encodeURIComponent(sel[6]) + '/">Open the full entry ›</a>' : "") + "</div>";
        verseBox(lc.querySelector("#wt-lo"), "Old Testament · " + oref + " · NET", oref);
        verseBox(lc.querySelector("#wt-ln"), BOOKS[NB].n + " · " + nref + " · NET", nref);
        lc.scrollIntoView({ block: "nearest" });
      }
    });
  }

  // ---------- the clocks: a shared axis ----------
  function clockAxis(lo, hi, knot) {
    // knot = [year, percent]: an optional bend, so the crowded centuries after the last copy get room
    return { lo: lo, hi: hi, p: function (y) {
      var v;
      if (knot) v = y <= knot[0] ? (y - lo) / (knot[0] - lo) * knot[1] : knot[1] + (y - knot[0]) / (hi - knot[0]) * (100 - knot[1]);
      else v = (y - lo) / (hi - lo) * 100;
      return Math.max(0, Math.min(100, v)); } };
  }
  function ticks(ax, list) { return '<div class="wt-ticks">' + list.map(function (y) { return '<span style="left:' + ax.p(y) + '%">' + (y === 1 ? "AD 1" : yr(y)) + "</span>"; }).join("") + "</div>"; }
  function bar(ax, a, b, cls) { var l = ax.p(a), r = ax.p(b); return '<span class="wt-bar2 ' + cls + '" style="left:' + l + "%;width:" + Math.max(.4, r - l) + '%"></span>'; }
  function mark(ax, y, label, cls, data, top) {
    var p = ax.p(y);
    return '<button class="wt-mark ' + cls + (p > 78 ? " end" : "") + (S.item === data ? " on" : "") + '" style="left:' + p + "%;top:" + (top || 0) + 'px" data-item="' + esc(data) + '" title="' + esc(label) + '"><i></i><span>' + esc(label) + "</span></button>";
  }

  // labels on a lane are packed into as many lines as they need, so none sits on another
  function pack(ax, items, px) {
    var rows = [];
    items.slice().sort(function (a, b) { return a.y - b.y; }).forEach(function (it) {
      var x = ax.p(it.y) / 100 * px, w = 22 + Math.min(String(it.n).length, 20) * 6.4, a = x > px * .78 ? x - w : x, z = a + w, r = 0;
      while (rows[r] != null && rows[r] > a - 6) r++;
      rows[r] = z; it.row = r;
    });
    return Math.max(1, rows.length);
  }
  function laneHTML(ax, title, sub, cls, items, key, px) {
    var n = pack(ax, items, px);
    return '<div class="wt-row tall' + (S.layers[key] ? "" : " off") + '"><div class="wt-rl">' + esc(title) + (sub ? "<small>" + esc(sub) + "</small>" : "") +
      '</div><div class="wt-rt2" style="height:' + (n * 22 + 6) + 'px">' +
      items.map(function (it) { return mark(ax, it.y, it.n, cls, it.k, it.row * 22 + 4); }).join("") + "</div>" + (S.view === "written" ? '<div class="wt-ru"></div>' : "") + "</div>";
  }
  function trackPx() { return Math.max(560, (MAIN.clientWidth || 900) - 36 - 150 - (S.view === "written" ? 96 : 0)); }

  // ---------- view 4: when it happened ----------
  // A synchronological chart, after Adams' Chart of History: time runs left to right, each people is a stream,
  // and Israel runs as one stream until it splits in two. No word is involved, so the word search and the word
  // boxes are hidden on this view. Drag to pan, zoom with the buttons or ctrl and the wheel, jump by era.
  // the same chart draws both clocks: When it was written (written.json) and When it happened (chart.json)
  var CHH = { ppy: 0, x0: null, sel: null, q: "", off: { china: 1, india: 1, africa: 1, americas: 1 } };
  var CHW = { ppy: 0, x0: null, sel: null, q: "", off: {} };
  function viewHappened() { chartView("chart.json", CHH, viewHappened); }
  function viewWritten() { chartView("written.json", CHW, viewWritten); }
  function chartView(FILE, CH, self) {
    data(FILE).then(function (K) {
      var A = K.range[0], Z = K.range[1];
      var wrapW = CH.wrap || Math.max(320, (MAIN.clientWidth || 900) - 200);
      if (!CH.ppy || CH.fit) CH.ppy = wrapW / (Z - A);
      var ppy = CH.ppy, W = Math.ceil((Z - A) * ppy) + 40;
      function X(y) { return 20 + (y - A) * ppy; }
      function yl(y) { y = Math.round(y); return y < 0 ? fmt(-y) + " BC" : (y === 0 ? "1 BC" : "AD " + y); }
      function sp2(a, b) { return Math.round(a) === Math.round(b) ? yl(a) : yl(a) + " to " + yl(b); }
      // pack each stream's items into rows, by the room each needs at this zoom, its label included when it fits
      var span = (wrapW + 40) / ppy, cx0 = CH.x0 != null ? CH.x0 : A + span / 2, VA = cx0 - span * 1.6, VZ = cx0 + span * 1.6;
      CH.drawn = cx0;
      var LH = 18, PAD = 6, y = 34, out = "", labs = "", rows = [], q = CH.q.trim().toLowerCase(), found = [];
      // search runs over the whole chart, not only the years on screen
      if (q) K.streams.forEach(function (st) { if (CH.off[st.k]) return; st.items.forEach(function (it, ii) { if (it.n.toLowerCase().indexOf(q) >= 0) found.push({ o: { id: st.k + ":" + ii }, st: st }); }); });
      K.streams.forEach(function (st, si) {
        if (CH.off[st.k]) return;
        var RMAX = /^(events|turning|famous)$/.test(st.k) ? 4 : 6;
        // only what is near the screen is laid out, so a stream with nothing in these years shrinks to one thin row
        var ends = [], items = st.items.map(function (it, ii) { return [it, ii]; }).filter(function (p2) { return p2[0].b >= VA && p2[0].a <= VZ; }).map(function (p2) {
          var it = p2[0], ii = p2[1];
          var x0 = X(it.a), x1 = Math.max(X(it.b), x0 + (it.t === "pt" || it.t === "flag" ? 0 : 2)), lw = it.n.length * 6.4 + 10;
          var inside = it.t !== "pt" && it.t !== "flag" && x1 - x0 > lw, need = inside ? x1 : Math.max(x1, x0 + (it.t === "pt" || it.t === "flag" ? 8 : 4) + lw);
          // a stream keeps a few rows at most; when no row has room for the label, the item goes in the row
          // that frees up first and its label waits for a closer zoom (it still shows on hover and when picked)
          var isPt = it.t === "pt" || it.t === "flag", r = 0, label = true;
          while (r < RMAX && ends[r] != null && ends[r] > x0 - 3) r++;
          if (r >= RMAX) {
            label = false;
            if (isPt) { r = 0; for (var k2 = 1; k2 < RMAX; k2++) if (ends[k2] < ends[r]) r = k2; need = Math.max(ends[r], x0 + 4); }
            else if (x1 - x0 < 8) { r = 0; for (var k3 = 1; k3 < RMAX; k3++) if (ends[k3] < ends[r]) r = k3; need = Math.max(ends[r], x1 + 2); }   // a sliver at this zoom may share
            else { r = 0; need = x1 + 2; while (ends[r] != null && ends[r] > x0 - 1) r++; }   // a real bar never overlaps: it takes a new row
          }
          ends[r] = need;
          return { it: it, x0: x0, x1: x1, r: r, inside: inside, label: label || inside, id: st.k + ":" + ii };
        });
        var nr = Math.max(1, ends.length), h = (items.length ? nr * LH + PAD * 2 : 14), top = y;
        out += '<rect class="hp-band' + (rows.length % 2 ? " odd" : "") + ' c-' + st.c + '" x="0" y="' + top + '" width="' + W + '" height="' + h + '"></rect>';
        items.forEach(function (o) {
          var it = o.it, yy = top + PAD + o.r * LH, on = CH.sel === o.id, hit = q && it.n.toLowerCase().indexOf(q) >= 0;

          var cls = "hp-it c-" + st.c + " t-" + it.t + (on ? " on" : "") + (hit ? " hit" : "");
          var g = '<g class="' + cls + '" data-id="' + o.id + '" tabindex="0" role="button" aria-label="' + esc(it.n + ", " + sp2(it.a, it.b)) + '">';
          if (it.t === "pt" || it.t === "flag") {
            g += '<circle cx="' + o.x0.toFixed(1) + '" cy="' + (yy + LH / 2) + '" r="' + (it.t === "flag" ? 4.5 : 3.2) + '"></circle>' +
              (o.label || on || hit ? '<text x="' + (o.x0 + 8).toFixed(1) + '" y="' + (yy + LH / 2 + 4) + '">' + esc(it.n) + "</text>" : "");
          } else {
            g += '<rect x="' + o.x0.toFixed(1) + '" y="' + (yy + 2) + '" width="' + (o.x1 - o.x0).toFixed(1) + '" height="' + (LH - 4) + '" rx="2"></rect>' +
              (o.label || on || hit ? '<text class="' + (o.inside ? "in" : "out") + '"' + (o.inside ? ' data-a="' + o.x0.toFixed(1) + '" data-z="' + o.x1.toFixed(1) + '"' : "") + ' x="' + ((o.inside ? o.x0 + 5 : o.x1 + 5)).toFixed(1) + '" y="' + (yy + LH / 2 + 4) + '">' + esc(it.n) + "</text>" : "");
          }
          out += g + "<title>" + esc(it.n + ", " + sp2(it.a, it.b)) + "</title></g>";
        });
        rows.push({ st: st, top: top, h: h });
        y += h + 3;
      });
      var H = y + 30;
      // the split: one stream for Israel until 974 BC, then two
      var rIs = rows.filter(function (r) { return r.st.k === "israel"; })[0], rN = rows.filter(function (r) { return r.st.k === "north"; })[0], rS = rows.filter(function (r) { return r.st.k === "south"; })[0];
      var split = "";
      if (rIs && rN && rS) {
        var xs = X(-975), mid = rIs.top + rIs.h / 2;
        split = '<path class="hp-split" d="M' + (xs - 30).toFixed(1) + " " + mid + "C" + xs.toFixed(1) + " " + mid + " " + (xs - 20).toFixed(1) + " " + (rN.top + rN.h / 2) + " " + (xs + 6).toFixed(1) + " " + (rN.top + rN.h / 2) +
          "M" + (xs - 30).toFixed(1) + " " + mid + "C" + xs.toFixed(1) + " " + mid + " " + (xs - 20).toFixed(1) + " " + (rS.top + rS.h / 2) + " " + (xs + 6).toFixed(1) + " " + (rS.top + rS.h / 2) + '"></path>';
      }
      // the year grid: a line every century, or every 10 or 50 years when zoomed in
      // the grid step is the smallest that leaves room for its year label: at least 64px apart
      var stepY = [10, 25, 50, 100, 250, 500, 1000].filter(function (st2) { return st2 * ppy >= 64; })[0] || 1000, grid = "", rul = "";
      for (var yr0 = Math.ceil(A / stepY) * stepY; yr0 <= Z; yr0 += stepY) {
        var gx = X(yr0), big = yr0 % (stepY >= 250 ? 1000 : stepY * 5) === 0;
        grid += '<line class="hp-grid' + (big ? " big" : "") + '" x1="' + gx.toFixed(1) + '" y1="24" x2="' + gx.toFixed(1) + '" y2="' + (H - 20) + '"></line>';
        var t = yr0 === 0 ? "AD 1" : yl(yr0);
        rul += '<text class="hp-yr" x="' + gx.toFixed(1) + '" y="16" text-anchor="middle">' + t + '</text><text class="hp-yr" x="' + gx.toFixed(1) + '" y="' + (H - 6) + '" text-anchor="middle">' + t + "</text>";
      }
      var side = rows.map(function (r, i) { return '<div class="hp-lab' + (i % 2 ? " odd" : "") + (r.h < 20 ? " thin" : "") + ' c-' + r.st.c + '" style="top:' + r.top + "px;height:" + r.h + 'px"><span>' + esc(r.st.n) + "</span></div>"; }).join("");
      var eras = K.eras.map(function (e, k) { return '<button class="wt-lay hp-era" data-era="' + k + '">' + (k ? k + " · " : "") + esc(e[0]) + "</button>"; }).join("");
      var toggles = ["china", "india", "africa", "americas"].map(function (k) {
        var st = K.streams.filter(function (s2) { return s2.k === k; })[0]; return st ? '<button class="wt-lay' + (CH.off[k] ? "" : " on") + '" data-off="' + k + '">' + (CH.off[k] ? "Show " : "Hide ") + esc(st.n) + "</button>" : "";
      }).join("");
      MAIN.innerHTML = '<div class="wt-panel hp">' +
        '<div class="hp-bar"><div class="wt-ctl hp-eras">' + eras + "</div></div>" +
        '<div class="hp-bar"><input class="a-search hp-q" id="hp-q" type="search" placeholder="' + esc(K.find || "Find a person, a king, an event or a nation") + '" value="' + esc(CH.q) + '">' +
          '<span class="hp-found">' + (q ? fmt(found.length) + (found.length === 1 ? " match" : " matches") : "") + "</span>" +
          '<button class="wt-lay" data-zoom="-1" aria-label="Zoom out">−</button><button class="wt-lay" data-zoom="1" aria-label="Zoom in">+</button>' + toggles + "</div>" +
        '<div class="hp-chart"><div class="hp-side" style="height:' + H + 'px">' + side + '</div><div class="hp-scroll" id="hp-scroll"><svg class="hp-svg" width="' + W + '" height="' + H + '" viewBox="0 0 ' + W + " " + H + '">' +
          grid + out + split + rul + "</svg></div></div>" +
        '<p class="a-note hp-hint">Drag the chart to move through time. Zoom with the buttons, or hold ctrl and scroll. Pick anything to read about it.</p></div>' +
        '<div id="hp-card"></div>' +
        '<div class="a-box v-apparatus"><div class="a-blabel">Sources</div><div class="a-btext" style="font-size:15px">' + esc(K.note) + "</div><ol>" +
          (K.sources || []).map(function (x) { return "<li>" + esc(x[0]) + (x[1] ? ' <a href="' + esc(x[1]) + '">' + esc(x[1].replace(/^https:\/\/(www\.)?/, "").replace(/\/$/, "")) + "</a>" : "") + "</li>"; }).join("") + "</ol></div>";
      var sc = MAIN.querySelector("#hp-scroll");
      // a long band keeps its name in view while the chart scrolls, the way the names ride along Adams' streams
      var rides = [].slice.call(sc.querySelectorAll("text.in[data-a]")).map(function (t) { return { t: t, a: +t.getAttribute("data-a"), z: +t.getAttribute("data-z"), w: t.getComputedTextLength ? t.getComputedTextLength() : 60 }; });
      function ride() { var L0 = sc.scrollLeft + 6; rides.forEach(function (r) { var x = Math.min(Math.max(r.a + 5, L0), r.z - r.w - 5); if (x < r.a + 5) x = r.a + 5; r.t.setAttribute("x", x.toFixed(1)); }); }
      sc.addEventListener("scroll", ride, { passive: true });
      // when the reader has moved most of a screen from where the chart was laid out, lay it out again there
      var relay = null;
      sc.addEventListener("scroll", function () { clearTimeout(relay); relay = setTimeout(function () { if (drag) return; var c2 = A + (sc.scrollLeft + sc.clientWidth / 2 - 20) / ppy; if (Math.abs(c2 - CH.drawn) > span * 0.7) { CH.x0 = c2; self(); } }, 260); }, { passive: true });
      // first draw: measure the real width of the scroll area, and fit the whole span to it once
      if (!CH.measured) { CH.measured = 1; var real = sc.clientWidth - 40; if (real > 200 && Math.abs(real - wrapW) > 4) { CH.wrap = real; CH.ppy = real / (Z - A); self(); return; } }
      if (CH.x0 != null) sc.scrollLeft = X(CH.x0) - sc.clientWidth / 2;
      ride();
      function center() { return A + (sc.scrollLeft + sc.clientWidth / 2 - 20) / ppy; }
      function zoomTo(f, yc) { CH.x0 = yc == null ? center() : yc; CH.ppy = Math.max(wrapW / (Z - A), Math.min(30, CH.ppy * f)); self(); }
      [].forEach.call(MAIN.querySelectorAll("[data-zoom]"), function (b) { b.addEventListener("click", function () { zoomTo(+b.getAttribute("data-zoom") > 0 ? 1.8 : 1 / 1.8); }); });
      [].forEach.call(MAIN.querySelectorAll("[data-era]"), function (b) { b.addEventListener("click", function () { var e = K.eras[+b.getAttribute("data-era")]; CH.ppy = wrapW / (e[2] - e[1]); CH.x0 = (e[1] + e[2]) / 2; self(); }); });
      [].forEach.call(MAIN.querySelectorAll("[data-off]"), function (b) { b.addEventListener("click", function () { var k = b.getAttribute("data-off"); CH.off[k] = CH.off[k] ? 0 : 1; CH.x0 = center(); self(); }); });
      sc.addEventListener("wheel", function (ev) { if (!ev.ctrlKey) return; ev.preventDefault(); var r = sc.getBoundingClientRect(); var yc = A + (sc.scrollLeft + ev.clientX - r.left - 20) / ppy; zoomTo(ev.deltaY < 0 ? 1.25 : 0.8, yc); }, { passive: false });
      // drag to pan; a drag that moves does not count as a pick
      var drag = null, moved = false;
      sc.addEventListener("pointerdown", function (ev) { if (ev.pointerType === "touch") return; drag = { x: ev.clientX, s: sc.scrollLeft }; moved = false; });
      window.addEventListener("pointermove", function (ev) { if (!drag) return; var dx = ev.clientX - drag.x; if (Math.abs(dx) > 4) moved = true; sc.scrollLeft = drag.s - dx; });
      window.addEventListener("pointerup", function () { drag = null; });
      var qi = MAIN.querySelector("#hp-q"), qt = null;
      qi.addEventListener("input", function () { clearTimeout(qt); qt = setTimeout(function () { CH.q = qi.value; CH.x0 = center(); var pos = qi.selectionStart; self(); var n = MAIN.querySelector("#hp-q"); n.focus(); try { n.setSelectionRange(pos, pos); } catch (x) {} }, 250); });
      qi.addEventListener("keydown", function (ev) { if (ev.key === "Enter" && found.length) { pick(found[0].o.id); } });
      function pick(id) {
        CH.sel = id; var p = id.split(":"), st = K.streams.filter(function (s2) { return s2.k === p[0]; })[0], it = st.items[+p[1]];
        CH.x0 = (it.a + it.b) / 2; if (CH.ppy < 1.2) CH.ppy = 1.2; self();
      }
      [].forEach.call(MAIN.querySelectorAll(".hp-it"), function (g) {
        g.addEventListener("click", function () { if (moved) return; pick(g.getAttribute("data-id")); });
        g.addEventListener("keydown", function (ev) { if (ev.key === "Enter" || ev.key === " ") { ev.preventDefault(); pick(g.getAttribute("data-id")); } });
      });
      if (CH.sel) {
        var p = CH.sel.split(":"), st = K.streams.filter(function (s2) { return s2.k === p[0]; })[0], it = st && st.items[+p[1]];
        if (it) {
          // what else was going on: everything in the other streams that overlaps it
          var lo = it.a, hi = Math.max(it.b, it.a), meanwhile = [];
          K.streams.forEach(function (s2) { s2.items.forEach(function (o) { if (o !== it && o.t !== "pt" && o.a <= hi && o.b >= lo) meanwhile.push([s2.n, o.n + ", " + sp2(o.a, o.b)]); }); });
          var card = MAIN.querySelector("#hp-card");
          card.innerHTML = '<div class="wt-pair"><div class="a-box v-witness"><div class="a-blabel">' + esc(st.n) + "</div><div class=\"a-btext\"><b>" + esc(it.n) + "</b><br>" + esc(it.d || sp2(it.a, it.b)) +
            (it.b - it.a >= 1 ? " · " + fmt(Math.round(it.b - it.a)) + " years" : "") + (it.ref ? "<br>" + esc(it.ref) : "") + (it.more ? '<br><span class="hp-more">' + esc(it.more) + "</span>" : "") + "</div>" +
            (it.url ? '<a class="a-lnk" href="' + esc(it.url) + '">' + (it.lab ? esc(it.lab) + " ›" : /jayms\.com\/bible-study-tools/.test(it.url) ? "Open it on the timeline ›" : "Read the post ›") + "</a>" : "") +
            '<div class="a-bnote">Dates: ' + esc(it.note ? (/^vault: /.test(it.note) ? it.note.replace(/^vault: /, "") + ", in the study notes" : it.note) : st.src) + "</div>" + (it.ref ? '<div class="a-btext" id="hp-v" style="margin-top:10px"></div>' : "") + "</div>" +
            '<div class="a-box v-explain"><div class="a-blabel">Meanwhile</div><ul class="wt-meanwhile">' + (meanwhile.length ? meanwhile.slice(0, 16).map(function (m) { return "<li><span><small>" + esc(m[0]) + ":</small> " + esc(m[1]) + "</span></li>"; }).join("") : "<li>Nothing else on the chart overlaps it.</li>") + "</ul></div></div>";
          if (it.ref) net(it.ref).then(function (t) { var v = card.querySelector("#hp-v"); if (v) v.textContent = t ? t + " (" + it.ref + ", NET)" : ""; });
        }
      }
    });
  }

  // ---------- footer: method and sources ----------
  function renderFoot() {
    document.getElementById("wt-foot").innerHTML =
      '<p class="a-note method">The method. The Hebrew Old Testament and the Greek New Testament are counted word by word from the Macula Hebrew and Greek texts. ' +
      "The Greek Old Testament column comes from Swete's Greek text, lined up verse by verse with the Hebrew; a word-alignment model then learns which Greek word answers which Hebrew word. " +
      "Those counts are statistical: they say how often, not that every instance is right. Swete's transcription carries some scanning errors, and it has no Ecclesiastes.</p>" +
      '<p class="a-note">Sources. Hebrew and Greek: Macula Hebrew (WLC) and Macula Greek (SBLGNT), Clear Bible, CC BY 4.0, via jayms interlinear-data. Greek Old Testament: H. B. Swete, The Old Testament in Greek, First1KGreek transcription, lemmatized by QuantForge (Morpheus), CC BY-SA 4.0. ' +
      "Verse numbers: STEPBible TVTMS, CC BY 4.0. Lexicon: Strong's (Open Scriptures, CC BY-SA). Dead Sea Scrolls by verse: ETCBC/dss, Abegg's data, CC BY-NC 4.0. People: Theographic Bible Metadata, CC BY-SA 4.0. World: Cliopatria, Seshat Global History Databank, CC BY 4.0. Verse text: the NET Bible, labs.bible.org. " +
      "The Greek Old Testament counts and the people layer are shared under CC BY-SA 4.0, as their sources require.</p>";
  }

  // ---------- render ----------
  // ---------- the trail: every word looked at in this visit, oldest first ----------
  var TRAIL = [];
  try { TRAIL = JSON.parse(sessionStorage.getItem("jayms.wordtrail.trail") || "[]") || []; } catch (x) { TRAIL = []; }
  function addTrail(c) {
    if (!c.entry) return;
    var item = { w: c.w, l: c.entry.l, n: c.entry.t || c.entry.x, fb: S.fb || null, from: c.from || null };
    TRAIL = TRAIL.filter(function (t) { return !(t.w === item.w && (t.fb || null) === item.fb); });
    TRAIL.push(item); if (TRAIL.length > 24) TRAIL = TRAIL.slice(-24);
    try { sessionStorage.setItem("jayms.wordtrail.trail", JSON.stringify(TRAIL)); } catch (x) {}
    drawTrail();
  }
  function drawTrail() {
    var nav = document.getElementById("wt-trail");
    if (!TRAIL.length) { nav.innerHTML = ""; return; }
    nav.innerHTML = '<span class="wt-trl">Your trail</span>' + TRAIL.map(function (t, i) {
      var on = t.w === S.w && (t.fb || null) === (S.fb || null);
      return (i ? '<span class="wt-trs" aria-hidden="true">›</span>' : "") + '<button class="wt-tri ' + (t.w.charAt(0) === "H" ? "wt-he" : "wt-gr") + (on ? " on" : "") + '" data-i="' + i + '"' + (on ? ' aria-current="true"' : "") + '>' +
        '<span lang="' + (t.w.charAt(0) === "H" ? "he" : "grc") + '">' + esc(t.l) + "</span> " + esc(t.n) + "</button>";
    }).join("") + '<button class="a-clear wt-trc">Clear the trail</button>';
    [].forEach.call(nav.querySelectorAll(".wt-tri"), function (b) {
      b.addEventListener("click", function () { var t = TRAIL[+b.getAttribute("data-i")]; go(t.w, t.fb, t.from); });
    });
    nav.querySelector(".wt-trc").addEventListener("click", function () { TRAIL = []; try { sessionStorage.removeItem("jayms.wordtrail.trail"); } catch (x) {} drawTrail(); });
  }
  function render() {
    MOUNT.classList.toggle("wt-wide", S.view === "uses");
    MOUNT.classList.toggle("wt-noword", S.view === "happened" || S.view === "written");
    var oa = document.getElementById("wt-other");
    if (oa) oa.href = OTHER + "#w=" + S.w + (S.g ? "&g=" + S.g : "") + (S.from ? "&from=" + S.from : "");
    [].forEach.call(MOUNT.querySelectorAll(".wt-tab"), function (b) { var on = b.getAttribute("data-view") === S.view; b.classList.toggle("on", on); b.setAttribute("aria-selected", on ? "true" : "false"); });
    MAIN.innerHTML = '<div class="wt-loading">Loading…</div>';
    if (S.view === "happened" || S.view === "written") { ({ happened: viewHappened, written: viewWritten })[S.view](); return; }   // no word on the clocks
    load().then(function () {
      renderSide(); addTrail(CUR);
      if (PARTS === "cards") { MAIN.innerHTML = ""; return; }
      ({ uses: viewUses, lands: viewLands, written: viewWritten, happened: viewHappened })[S.view]();
    }).catch(function (err) {
      MAIN.innerHTML = '<div class="a-box v-not"><div class="a-blabel">Did not load</div><div class="a-btext">The word data did not load. Reload the page to try again.</div></div>';
      if (window.console) console.warn("[word-trail]", err);
    });
  }
  window.addEventListener("hashchange", function () { var w = S.w, g = S.g, v = S.view, vv = S.v; readHash(); if (w !== S.w || g !== S.g || v !== S.view || vv !== S.v) render(); });
  readHash(); renderFoot(); render();
})();

/* BOOK ECHOES: one book, and every place in the rest of the Bible it is tied to. No word involved.
   Mounts on #jayms-book-echoes. Same data folder as Word Trail (intertext/b0.json ... b65.json, books.json).
   Keeps its own state and never touches the hash, so it cannot disturb Word Trail on the same page. */
(function () {
  "use strict";
  var MOUNT = document.getElementById("jayms-book-echoes");
  if (!MOUNT || MOUNT.getAttribute("data-be-ready")) return;
  MOUNT.setAttribute("data-be-ready", "1");
  var BASE = MOUNT.getAttribute("data-base") || "https://raw.githubusercontent.com/sixcore-droid/jayms-tool-data/word-trail/word-trail/";
  var ECHO = "https://jayms.com/bible-study-tools-2/echoes-of-revelation/";
  var NETAPI = "https://labs.bible.org/api/?type=json&formatting=plain&passage=";
  function esc(s) { return String(s == null ? "" : s).replace(/[&<>"']/g, function (c) { return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]; }); }
  function fmt(n) { return Number(n).toLocaleString("en-US"); }
  var C = {};
  function data(n) { if (!C[n]) C[n] = fetch(BASE + n).then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); }); return C[n]; }
  var BOOKS = null;
  function bookOf(i) { var lo = 0, hi = BOOKS.length - 1; while (lo < hi) { var m = (lo + hi + 1) >> 1; if (BOOKS[m].s <= i) lo = m; else hi = m - 1; } return lo; }
  function refOf(i) { var b = BOOKS[bookOf(i)], r = i - b.s, c = 0; while (c < b.c.length && r >= b.c[c]) { r -= b.c[c]; c++; } return b.n + " " + (c + 1) + ":" + (r + 1); }
  function rng(a, z) { if (a === z) return refOf(a); var r0 = refOf(a), r1 = refOf(z), c0 = r0.split(" ").pop().split(":")[0], c1 = r1.split(" ").pop().split(":"); return r0 + "-" + (c1[0] === c0 ? c1[1] : c1.join(":")); }
  function grp(b) { return b < 5 ? "law" : b < 17 ? "hist" : b < 22 ? "wis" : b < 39 ? "pro" : b < 44 ? "gos" : b < 57 ? "paul" : "gen"; }
  var GRPN = { law: "Law", hist: "History", wis: "Wisdom and poetry", pro: "Prophets", gos: "Gospels and Acts", paul: "Paul's letters", gen: "Hebrews to Revelation" };
  var SRC = { q: "Shared wording", e: "Graded echo", x: "Cross reference" }, SRCP = { q: "shared wording", e: "graded echoes", x: "cross references" };
  var STR = { 3: "Strong", 2: "Medium", 1: "Light" };
  // a list row names its source only when it is not a plain cross reference, the common case
  function tagOf(r) { return (r[4] === "x" ? "" : SRC[r[4]].toLowerCase() + " · ") + STR[r[5]].toLowerCase(); }
  function note(r) { return r[4] === "e" ? (r[5] === 3 ? "graded clear" : r[5] === 2 ? "graded probable" : "graded possible") : r[4] === "x" ? fmt(r[6]) + " reader votes" : r[6] + "% of the words match"; }
  var E = { b: 0, t: null, pair: null, src: { q: 1, e: 1, x: 1 }, st: { 3: 1, 2: 1, 1: 0 }, more: 0 };
  try { var saved = JSON.parse(sessionStorage.getItem("jayms.bookechoes") || "null"); if (saved && saved.b != null) { E.b = saved.b; E.t = saved.t; } } catch (e) {}
  function save() { try { sessionStorage.setItem("jayms.bookechoes", JSON.stringify({ b: E.b, t: E.t })); } catch (e) {} }

  MOUNT.innerHTML = '<div class="be-head"><div class="wt-ctl" id="be-ctl"></div></div>' +
    '<p class="wt-stat" id="be-stat"></p><div class="be-wrap"><div class="be-books" id="be-books" hidden></div><div class="be-circle" id="be-circle"></div><div class="be-side" id="be-side"></div></div>' +
    '<div id="be-pair"></div><div class="wt-list" id="be-list"></div>' +
    '<div class="a-box v-explain" style="margin-top:13px"><div class="a-blabel">Where the links come from</div><div class="a-btext">' +
    "<b>Shared wording</b> is the UBS Parallel Passages Database: passages whose words match word by word, quotations among them. Strong means at least 40 percent of the words in one of the two verses match. " +
    "<b>Graded echoes</b> are the entries of Echoes of Revelation, graded clear (strong), probable (medium) or possible (light) there. " +
    "<b>Cross references</b> are OpenBible.info's, where readers vote on each one: 50 votes or more is strong, 15 to 49 medium, 5 to 14 light. " +
    "Each ribbon runs from the book you picked to another book, as wide as the number of links between them. Links inside one book are left out.</div></div>";
  var SEL = MOUNT.querySelector("#be-books"), CIRC = MOUNT.querySelector("#be-circle"), SIDE = MOUNT.querySelector("#be-side"),
      STAT = MOUNT.querySelector("#be-stat"), LIST = MOUNT.querySelector("#be-list"), PAIR = MOUNT.querySelector("#be-pair"), CTL = MOUNT.querySelector("#be-ctl");

  data("books.json").then(function (bk) {
    BOOKS = bk.books;
    // every book down the left, in canon order under its group, the same look as the Most links list
    var h = "", last = null;
    BOOKS.forEach(function (b, k) {
      var g = grp(k); if (g !== last) { h += '<div class="be-grp">' + esc(GRPN[g]) + "</div>"; last = g; }
      h += '<button class="a-chip be-top be-bk g-' + g + '" data-b="' + k + '"><i></i>' + esc(b.n) + "</button>";
    });
    SEL.innerHTML = h;
    [].forEach.call(SEL.querySelectorAll(".be-bk"), function (b) { b.addEventListener("click", function () { pickBook(+b.getAttribute("data-b")); }); });
    draw();
  });
  function pickBook(b) { E.b = b; E.t = null; E.pair = null; E.more = 0; save(); draw(); }

  // arcs: each book gets a share of the circle by its size (verses to the 0.6 power, so short books stay visible)
  var CX = 500, CY = 500, R = 390, GAP = 0.004 * Math.PI * 2;
  function layout() {
    var w = BOOKS.map(function (b) { return Math.pow(b.c.reduce(function (s, n) { return s + n; }, 0), 0.6); }), tot = w.reduce(function (s, x) { return s + x; }, 0);
    var free = Math.PI * 2 - GAP * BOOKS.length, a = -Math.PI / 2 + GAP / 2, out = [];
    w.forEach(function (x) { var d = free * x / tot; out.push([a, a + d]); a += d + GAP; });
    return out;
  }
  function pt(a, r) { return (CX + r * Math.cos(a)).toFixed(1) + " " + (CY + r * Math.sin(a)).toFixed(1); }
  function arcPath(a0, a1, r0, r1) {
    var lg = a1 - a0 > Math.PI ? 1 : 0;
    return "M" + pt(a0, r1) + "A" + r1 + " " + r1 + " 0 " + lg + " 1 " + pt(a1, r1) + "L" + pt(a1, r0) + "A" + r0 + " " + r0 + " 0 " + lg + " 0 " + pt(a0, r0) + "Z";
  }
  function ribbon(s0, s1, t0, t1) {
    var c = CX + " " + CY;
    return "M" + pt(s0, R) + "A" + R + " " + R + " 0 0 1 " + pt(s1, R) + "Q" + c + " " + pt(t0, R) + "A" + R + " " + R + " 0 0 1 " + pt(t1, R) + "Q" + c + " " + pt(s0, R) + "Z";
  }

  function draw() {
    [].forEach.call(SEL.querySelectorAll(".be-bk"), function (b) { var on = +b.getAttribute("data-b") === E.b; b.classList.toggle("on", on); if (on) b.setAttribute("aria-current", "true"); else b.removeAttribute("aria-current");
      // keep the picked book in view inside the list, without moving the page
      if (on && (b.offsetTop < SEL.scrollTop || b.offsetTop > SEL.scrollTop + SEL.clientHeight - b.offsetHeight)) SEL.scrollTop = b.offsetTop - SEL.clientHeight / 2; });
    CTL.innerHTML = ["q", "e", "x"].map(function (k) { return '<button class="wt-lay' + (E.src[k] ? " on" : "") + '" data-src="' + k + '">' + (E.src[k] ? "Hide " : "Show ") + SRCP[k] + "</button>"; }).join("") +
      [3, 2, 1].map(function (k) { return '<button class="wt-lay' + (E.st[k] ? " on" : "") + '" data-st="' + k + '"><span class="wt-gline s' + k + '"></span>' + (E.st[k] ? "Hide " : "Show ") + STR[k].toLowerCase() + "</button>"; }).join("");
    [].forEach.call(CTL.querySelectorAll("[data-src]"), function (b) { b.onclick = function () { var k = b.getAttribute("data-src"); E.src[k] = E.src[k] ? 0 : 1; E.more = 0; draw(); }; });
    [].forEach.call(CTL.querySelectorAll("[data-st]"), function (b) { b.onclick = function () { var k = b.getAttribute("data-st"); E.st[k] = E.st[k] ? 0 : 1; E.more = 0; draw(); }; });
    CIRC.innerHTML = '<div class="a-loading">Loading ' + esc(BOOKS[E.b].n) + "…</div>";
    data("intertext/b" + E.b + ".json").then(function (rows) {
      var list = rows.filter(function (r) { return E.src[r[4]] && E.st[r[5]]; });
      var by = {}; list.forEach(function (r) { var t = bookOf(r[2]); (by[t] = by[t] || []).push(r); });
      var targets = Object.keys(by).map(Number).sort(function (a, b) { return a - b; });
      if (E.t != null && !by[E.t]) E.t = null;
      var A = layout(), src = A[E.b], span = src[1] - src[0], n = list.length || 1, svg = "";
      // ribbons first, so the arcs and labels sit on top; the source arc is shared out in circle order, starting
      // after the source book, so neighbouring ribbons do not cross
      var order = targets.slice().sort(function (a, b) { return ((a - E.b + 66) % 66) - ((b - E.b + 66) % 66); });
      var cur = src[1], maxN = 0; order.forEach(function (t) { maxN = Math.max(maxN, by[t].length); });
      order.slice().reverse().forEach(function () {});
      order.forEach(function (t) {
        var d = span * by[t].length / n, s1 = cur, s0 = cur - d; cur = s0;
        var ta = A[t], tw = Math.max(0.004, Math.min((ta[1] - ta[0]) * 0.9, (ta[1] - ta[0]) * Math.sqrt(by[t].length / maxN))), tm = (ta[0] + ta[1]) / 2;
        svg += '<path class="be-rib g-' + grp(t) + (E.t === t ? " on" : "") + (E.t != null && E.t !== t ? " dim" : "") + '" data-t="' + t + '" d="' + ribbon(s0, s1, tm - tw / 2, tm + tw / 2) + '"><title>' + esc(BOOKS[E.b].n + " and " + BOOKS[t].n + ": " + fmt(by[t].length) + " links") + "</title></path>";
      });
      BOOKS.forEach(function (b, k) {
        var a = A[k], has = !!by[k] || k === E.b, mid = (a[0] + a[1]) / 2, deg = mid * 180 / Math.PI, flip = Math.cos(mid) < 0;
        svg += '<g class="be-arc g-' + grp(k) + (k === E.b ? " src" : "") + (E.t === k ? " on" : "") + (E.t != null && E.t !== k && k !== E.b ? " dim2" : "") + (has ? "" : " none") + '" data-b="' + k + '" tabindex="0" role="button" aria-label="' + esc(b.n + (by[k] ? ", " + by[k].length + " links" : "")) + '">' +
          '<path class="hit" d="' + arcPath(a[0] - GAP / 2, a[1] + GAP / 2, R - 4, R + 46) + '"></path>' +
          '<path class="seg" d="' + arcPath(a[0], a[1], R + 2, R + (k === E.b ? 20 : 14)) + '"></path>' +
          '<text x="' + pt(mid, R + 26).split(" ")[0] + '" y="' + pt(mid, R + 26).split(" ")[1] + '" transform="rotate(' + (flip ? deg + 180 : deg).toFixed(1) + " " + pt(mid, R + 26) + ')" text-anchor="' + (flip ? "end" : "start") + '" dominant-baseline="middle">' + esc(b.a) + "</text></g>";
      });
      CIRC.innerHTML = '<svg class="be-svg" viewBox="0 0 1000 1000" role="img" aria-label="' + esc(BOOKS[E.b].n) + ' and every book it is tied to">' + svg + "</svg>";
      [].forEach.call(CIRC.querySelectorAll(".be-rib"), function (p) { p.addEventListener("click", function () { E.t = +p.getAttribute("data-t"); E.pair = null; E.more = 0; save(); draw(); }); });
      [].forEach.call(CIRC.querySelectorAll(".be-arc"), function (g) {
        // a book on the ring starts the chart from that book; a ribbon or Most links picks the book to compare
        function go() { var k = +g.getAttribute("data-b"); if (k !== E.b) pickBook(k); }
        g.addEventListener("click", go);
        g.addEventListener("keydown", function (ev) { if (ev.key === "Enter" || ev.key === " ") { ev.preventDefault(); go(); } });
      });
      STAT.innerHTML = "<b>" + esc(BOOKS[E.b].n) + "</b> is tied to <b>" + targets.length + "</b> other books in <b>" + fmt(list.length) + "</b> links with these filters. " +
        "Pick any book on the ring to start from it. Pick a ribbon, or a book in Most links, to list the links between the two.";
      var top = targets.slice().sort(function (a, b) { return by[b].length - by[a].length; });
      SIDE.innerHTML = '<div class="wt-sec">Most links</div><div class="be-tops">' + top.map(function (t) {
        return '<button class="a-chip be-top g-' + grp(t) + (E.t === t ? " on" : "") + '" data-t="' + t + '"><i></i>' + esc(BOOKS[t].n) + ' <span class="a-n">' + fmt(by[t].length) + "</span></button>";
      }).join("") + "</div>" + '<div class="wt-legend be-legend">' + Object.keys(GRPN).map(function (g) { return '<span class="g-' + g + '"><i></i>' + GRPN[g] + "</span>"; }).join("") + "</div>";
      [].forEach.call(SIDE.querySelectorAll(".be-top"), function (b) { b.addEventListener("click", function () { var t = +b.getAttribute("data-t"); E.t = E.t === t ? null : t; E.pair = null; E.more = 0; save(); draw(); }); });
      // the list: one other book's links, or the strongest links from this book to all of them
      var rowsT = (E.t != null ? by[E.t] : list).slice().sort(function (a, b) { return b[5] - a[5] || (a[4] === "q" ? -1 : 0) - (b[4] === "q" ? -1 : 0) || a[0] - b[0]; });
      var lim = 150 + E.more * 300;
      LIST.innerHTML = '<div class="wt-li wt-head"><span>' + (E.t != null ? esc(BOOKS[E.b].n + " and " + BOOKS[E.t].n) + " · " + fmt(rowsT.length) + " links, strongest first" : "The strongest links from " + esc(BOOKS[E.b].n) + ", all books") + "</span></div>" +
        rowsT.slice(0, lim).map(function (r, k) {
          return '<button class="wt-li g-' + grp(bookOf(r[2])) + (E.pair === r.join() ? " on" : "") + '" data-k="' + k + '"><span>' + esc(rng(r[0], r[1]) + "  ·  " + rng(r[2], r[3])) + '</span><span class="a-tag">' + esc(tagOf(r)) + "</span></button>";
        }).join("") + (rowsT.length > lim ? '<button class="wt-li be-more"><span>Show more (' + fmt(rowsT.length - lim) + " left)</span></button>" : "");
      [].forEach.call(LIST.querySelectorAll("[data-k]"), function (b) { b.addEventListener("click", function () { var r = rowsT[+b.getAttribute("data-k")]; E.pair = r.join(); showPair(r); [].forEach.call(LIST.querySelectorAll(".wt-li.on"), function (x) { x.classList.remove("on"); }); b.classList.add("on"); }); });
      var mb = LIST.querySelector(".be-more"); if (mb) mb.addEventListener("click", function () { E.more++; draw(); });
      var sp = E.pair && rowsT.filter(function (r) { return r.join() === E.pair; })[0]; if (sp) showPair(sp); else PAIR.innerHTML = "";
    });
  }
  function net(ref, box) {
    box.querySelector(".a-btext").innerHTML = '<span class="a-loading">Loading the NET text…</span>';
    fetch(NETAPI + encodeURIComponent(ref)).then(function (r) { return r.json(); }).then(function (j) {
      box.querySelector(".a-btext").textContent = (j || []).map(function (v) { return String(v.text).trim(); }).join(" ") || "The NET text did not load.";
    }).catch(function () { box.querySelector(".a-btext").textContent = "The NET text did not load. " + ref + "."; });
  }
  function showPair(r) {
    var a = rng(r[0], r[1]), b = rng(r[2], r[3]);
    PAIR.innerHTML = '<div class="wt-pair"><div class="a-box v-scripture" id="be-a"><div class="a-blabel">' + esc(a) + ' · NET</div><div class="a-btext"></div></div>' +
      '<div class="a-box v-scripture" id="be-b"><div class="a-blabel">' + esc(b) + ' · NET</div><div class="a-btext"></div></div></div>' +
      '<div class="wt-links" style="margin:10px 0 13px"><span class="a-tag">' + esc(SRC[r[4]] + " · " + STR[r[5]].toLowerCase()) + '</span><span class="a-tag">' + esc(note(r)) + "</span>" +
      (r[4] === "e" ? '<a class="a-lnk" href="' + ECHO + encodeURIComponent(r[6]) + '/">Open the Echoes of Revelation entry ›</a>' : "") + "</div>";
    net(a, PAIR.querySelector("#be-a")); net(b, PAIR.querySelector("#be-b"));
    PAIR.scrollIntoView({ block: "nearest" });
  }
})();
