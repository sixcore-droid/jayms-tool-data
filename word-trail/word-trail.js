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
    h.split("&").forEach(function (kv) {
      var p = kv.split("="), k = p[0], v = decodeURIComponent(p[1] || "");
      if (k === "w" && /^[HG]\d+$/.test(v)) S.w = v;
      if (k === "g" && /^G\d+$/.test(v)) S.g = v;
      if (k === "view" && /^(uses|lands|written|happened)$/.test(v)) S.view = v;
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
  MOUNT.innerHTML =
    '<nav class="wt-trail" id="wt-trail" aria-label="Words looked at in this session"></nav>' +
    '<div class="wt-find">' +
      '<label for="wt-q" class="screen-reader-text" style="position:absolute;left:-9999px">Find a word</label>' +
      '<input id="wt-q" class="a-search" type="search" autocomplete="off" spellcheck="false" placeholder="A Hebrew or Greek word, a transliteration, an English meaning, or a Strong\'s number (H776, G1093)">' +
      '<p class="wt-hint">Try erets, ruach, chesed, logos, H430, G26.</p>' +
    '</div>' +
    '<div class="wt-sugg" id="wt-sugg" hidden></div>' +
    '<div class="wt-tabs" role="tablist">' +
      '<button class="wt-tab" role="tab" data-view="uses">Every use</button>' +
      '<button class="wt-tab" role="tab" data-view="lands">Where it lands</button>' +
      '<button class="wt-tab" role="tab" data-view="written">When it was written</button>' +
      '<button class="wt-tab" role="tab" data-view="happened">When it happened</button>' +
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
  function go(id, fb, from) { S.from = from || null; S.fb = fb || null; S.w = id; S.g = null; S.v = null; S.book = null; S.ch = null; S.link = null; S.item = null; writeHash(); render(); }

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
  function verseBox(target, label, ref) {
    target.innerHTML = '<div class="a-blabel">' + esc(label) + "</div><div class=\"a-btext\"><span class=\"a-loading\">Loading the NET text…</span></div>";
    net(ref).then(function (t) {
      target.querySelector(".a-btext").innerHTML = t ? esc(t) : '<span class="wt-empty">The NET text did not load. ' + esc(ref) + ".</span>";
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
    data("links.json").then(function (L) {
      var hit = L.links.filter(function (l) { return l[5].indexOf(i) >= 0 && (!CUR.heb || l[4].some(function (o) { return CUR.entry.v.indexOf(o) >= 0; })); })[0]
             || L.links.filter(function (l) { return l[5].indexOf(i) >= 0; })[0];
      if (!hit) { v2.className = "a-box v-explain wt-empty"; v2.innerHTML = '<div class="a-blabel">The Old Testament passage it echoes</div><div class="a-btext">None is linked for this verse yet. The links come from Echoes of Revelation, so only verses in Revelation have one so far.</div>'; return; }
      var otref = runRef(hit[4]);
      verseBox(v2, "The Old Testament passage it echoes · " + otref + " · " + hit[2] + " echo", otref);
      var a = el("div", "wt-links", '<a class="a-lnk" href="' + ECHO + encodeURIComponent(hit[0]) + '/">' + esc(hit[1]) + " ›</a>");
      v2.appendChild(a);
    });
  }

  // ---------- view 2: where it lands ----------
  function viewLands() {
    var c = CUR;
    data("links.json").then(function (L) {
      var hv = {}, gv = {};
      if (c.heb && c.entry) c.entry.v.forEach(function (i) { hv[i] = 1; });
      if (c.gEntry) c.gEntry.v.forEach(function (i) { if (i >= NTS) gv[i] = 1; });
      var word = L.links.filter(function (l) { return l[4].some(function (i) { return hv[i]; }) || l[5].some(function (i) { return gv[i]; }); });
      var list = (S.scope === "word" ? word : L.links).filter(function (l) { return S.grade[l[2]]; });
      var REV = 65, revCh = BOOKS[REV].c.length, H = 39 * 18 + 20, W = 640;
      function yO(b) { return 14 + b * 18; }
      function yR(ch) { return 14 + (ch - 0.5) * (H - 28) / revCh; }
      function chOf(i) { var b = BOOKS[REV], r = i - b.s, cc = 0; while (cc < b.c.length && r >= b.c[cc]) { r -= b.c[cc]; cc++; } return cc + 1; }
      var used = {}; list.forEach(function (l) { used[bookOf(l[4][0])] = 1; });
      var paths = { clear: "", probable: "", possible: "" }, hits = "";
      list.forEach(function (l, n) {
        var y1 = yO(bookOf(l[4][0])), y2 = yR(chOf(l[5][0])), d = "M120 " + y1 + "C" + (W / 2) + " " + y1 + " " + (W / 2) + " " + y2 + " " + (W - 70) + " " + y2;
        paths[l[2]] = (paths[l[2]] || "") + d;
        hits += '<path class="hit" data-i="' + n + '" fill="none" d="' + d + '"></path>';
      });
      var sel = S.link != null ? list.filter(function (l) { return l[0] === S.link; })[0] : null;
      var labs = "";
      for (var b = 0; b < 39; b++) labs += '<text x="112" y="' + (yO(b) + 4) + '" text-anchor="end" class="' + (used[b] ? "" : "dim") + '">' + esc(BOOKS[b].n) + "</text>";
      for (var ch = 1; ch <= revCh; ch++) labs += '<text class="nt" x="' + (W - 62) + '" y="' + (yR(ch) + 4) + '">Rev ' + ch + "</text>";
      var h = '<div class="wt-panel"><p class="wt-stat">' +
        (S.scope === "word" ? "<b>" + fmt(word.length) + "</b> graded links carry this word" : "<b>" + fmt(L.links.length) + "</b> graded links in all") +
        ". Old Testament on the left, Revelation chapter by chapter on the right. The line says how sure the link is.</p>" +
        '<div class="wt-ctl"><button class="wt-lay on" data-scope="1">' + (S.scope === "word" ? "Show every graded link" : "Show only links with this word") + "</button>" +
        ["clear", "probable", "possible"].map(function (g) {
          return '<button class="wt-lay c-' + g + (S.grade[g] ? " on" : "") + '" data-grade="' + g + '"><span class="wt-gline d-' + g + '"></span>' +
            (S.grade[g] ? "Hide " : "Show ") + g + " echoes</button>";
        }).join("") + "</div>" +
        '<div class="wt-flow"><svg viewBox="0 0 ' + W + " " + H + '" role="img" aria-label="Links from Old Testament books to Revelation, styled by grade">' +
        '<path class="l-possible" fill="none" d="' + paths.possible + '"></path><path class="l-probable" fill="none" d="' + paths.probable + '"></path><path class="l-clear" fill="none" d="' + paths.clear + '"></path>' +
        (sel ? '<path class="l-sel" fill="none" d="M120 ' + yO(bookOf(sel[4][0])) + "C" + (W / 2) + " " + yO(bookOf(sel[4][0])) + " " + (W / 2) + " " + yR(chOf(sel[5][0])) + " " + (W - 70) + " " + yR(chOf(sel[5][0])) + '"></path>' : "") +
        hits + labs + "</svg></div></div>" +
        '<div id="wt-linkcard"></div>' +
        '<div class="wt-list">' + (list.length ? list.slice(0, 200).map(function (l) {
          return '<button class="wt-li c-' + l[2] + (sel && sel[0] === l[0] ? " on" : "") + '" data-link="' + esc(l[0]) + '"><span>' + esc(l[1]) + '</span><span class="a-tag">' + esc(l[2]) + "</span></button>";
        }).join("") : '<div class="wt-li wt-empty"><span>No graded link carries this word yet. Echoes of Revelation is the only graded set so far.</span></div>') + "</div>" +
        '<div class="a-box v-explain" style="margin-top:13px"><div class="a-blabel">Where the grades come from</div><div class="a-btext">Every line is an entry in Echoes of Revelation, graded clear, probable or possible there, with its sources named in the entry. ' +
        "A link carries this word when its Old Testament verses use the Hebrew word, or its Revelation verse uses the Greek word being followed. Other New Testament books join as their graded links are built.</div></div>";
      MAIN.innerHTML = h;
      MAIN.querySelector("[data-scope]").addEventListener("click", function () { S.scope = S.scope === "word" ? "all" : "word"; viewLands(); });
      [].forEach.call(MAIN.querySelectorAll("[data-grade]"), function (b) { b.addEventListener("click", function () { var g = b.getAttribute("data-grade"); S.grade[g] = S.grade[g] ? 0 : 1; viewLands(); }); });
      function pick(id) { S.link = id; viewLands(); }
      [].forEach.call(MAIN.querySelectorAll("[data-link]"), function (b) { b.addEventListener("click", function () { pick(b.getAttribute("data-link")); }); });
      [].forEach.call(MAIN.querySelectorAll("path.hit"), function (p) { p.addEventListener("click", function () { pick(list[+p.getAttribute("data-i")][0]); }); });
      if (sel) {
        var lc = MAIN.querySelector("#wt-linkcard");
        lc.innerHTML = '<div class="wt-pair"><div class="a-box v-scripture" id="wt-lo"></div><div class="a-box v-scripture" id="wt-ln"></div></div>' +
          '<div class="wt-links" style="margin:10px 0 13px"><span class="a-badge c-' + sel[2] + '" style="--cc:var(--wt-c)">' + esc(sel[2]) + " echo</span>" +
          '<span class="a-tag">' + esc(String(sel[3] || "").replace(/-/g, " ")) + '</span><a class="a-lnk" href="' + ECHO + encodeURIComponent(sel[0]) + '/">Open the full entry ›</a></div>';
        var oref = runRef(sel[4]);
        verseBox(lc.querySelector("#wt-lo"), "Old Testament · " + oref + " · NET", oref);
        verseBox(lc.querySelector("#wt-ln"), "Revelation · " + refOf(sel[5][0]) + " · NET", refOf(sel[5][0]));
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

  // ---------- view 3: when it was written ----------
  function viewWritten() {
    var c = CUR;
    data("clock.json").then(function (K) {
      DATES = K.datesA.books;
      var ax = clockAxis(-2200, 2060, [1300, 66]), L = S.layers, per = {}, scrollHits = {}, dssv = K.dss.verses;
      if (c.heb && c.entry) uniq(c.entry.v).forEach(function (i) { var b = bookOf(i); per[b] = (per[b] || 0) + 1; if (dssv[i]) scrollHits[b] = (scrollHits[b] || 0) + 1; });
      var max = 1; for (var b in per) max = Math.max(max, per[b]);
      var rows = "";
      for (b = 0; b < 39; b++) {
        var n = BOOKS[b].n, A = K.datesA.books[n], Bd = K.datesB.books[n], has = K.dss.perBook[n];
        rows += '<div class="wt-row"><div class="wt-rl" title="' + esc(n) + '">' + esc(n) + "</div><div class=\"wt-rt2\">" +
          (L.a && A ? bar(ax, A[0], A[1], "a c-a") : "") + (L.b && Bd ? bar(ax, Bd[0], Bd[1], "b c-b") : "") +
          (L.ms && has ? '<span class="wt-tick c-ms" style="left:' + ax.p(-250) + '%" title="Dead Sea Scrolls"></span><span class="wt-tick c-ms" style="left:' + ax.p(68) + '%" title="Dead Sea Scrolls"></span>' + bar(ax, -250, 68, "b c-ms") : "") +
          (L.ms ? '<span class="wt-tick c-ms" style="left:' + ax.p(1008) + '%" title="Leningrad Codex"></span>' : "") +
          '</div><div class="wt-ru' + (L.uses ? "" : " wt-empty") + '">' + (c.heb && per[b] ? '<span class="wt-ubar c-uses" style="width:' + Math.max(3, Math.round(44 * per[b] / max)) + 'px"></span>' + per[b] : "") +
          "</div></div>";
      }
      var wit = K.witness.map(function (w) { return { y: (w.copy[0] + w.copy[1]) / 2, n: w.n, k: "w:" + w.k }; });
      var fnd = K.witness.map(function (w) { return { y: w.found, n: w.n.replace(/ \(.*\)/, ""), k: "f:" + w.k }; });
      var trs = K.translations.map(function (t, j) { return { y: t.y[0], n: t.n, k: "t:" + j }; });
      var h = '<div class="wt-panel"><p class="wt-stat">When each Old Testament book was written, the copies we actually hold, when they came to light, and every translation and print edition down to today.' +
        (c.heb ? " The pale bar on the right of each row is how many verses in that book use <span lang=\"he\">" + esc(c.entry.l) + "</span>." : "") + "</p>" +
        '<div class="wt-ctl">' + layerBtn("a", "c-a", "Hide dates A", "Show dates A") + layerBtn("b", "c-b", "Hide dates B", "Show dates B", !K.datesB.name) +
        layerBtn("ms", "c-ms", "Hide manuscripts", "Show manuscripts") + layerBtn("find", "c-find", "Hide find dates", "Show find dates") +
        layerBtn("tr", "c-tr", "Hide translations and print", "Show translations and print") + (c.heb ? layerBtn("uses", "c-uses", "Hide uses of this word", "Show uses of this word") : "") + "</div>" +
        '<div class="wt-clock"><div class="wt-tl has-u">' + ticks(ax, [-2000, -1500, -1000, -500, 1, 500, 1000, 1300, 1500, 1750, 2000]) +
        '<div class="wt-sec wt-secu"><span>The books</span>' + (c.heb ? '<span class="wt-uh">Verses with the word</span>' : "") + "</div>" + rows +
        '<div class="wt-sec">The copies, and after them</div>' +
        laneHTML(ax, "Key witnesses", "when copied", "c-ms", wit, "ms", trackPx()) + laneHTML(ax, "Found", "came to light", "c-find round", fnd, "find", trackPx()) +
        laneHTML(ax, "Translations", "and print", "c-tr diamond", trs, "tr", trackPx()) +
        "</div></div>" +
        '<div class="wt-legend"><span class="c-a"><i></i>Dates A: ' + esc(K.datesA.name) + '</span><span class="c-b"><i></i>Dates B: ' + esc(K.datesB.name || "no source yet") + '</span><span class="c-ms"><i></i>A surviving copy</span><span class="c-tr"><i></i>Translation or print</span></div></div>' +
        '<div id="wt-item"></div>' +
        '<div class="a-box v-explain"><div class="a-blabel">How to read this</div><div class="a-btext">The gap between a book\'s bar and its first copy is the distance between the writing and the oldest copy anyone holds. ' +
        "Copied and found are different dates: the Leningrad Codex was copied in 1008 and reached St Petersburg in the 1800s; the Dead Sea Scrolls were copied between the third century BC and the first century AD and first found in 1947. " +
        esc(K.datesA.note) + " " + esc(K.datesB.note) + "</div></div>";
      MAIN.innerHTML = h;
      wireLayers(MAIN, viewWritten);
      [].forEach.call(MAIN.querySelectorAll("[data-item]"), function (m) { m.addEventListener("click", function () { S.item = m.getAttribute("data-item"); viewWritten(); }); });
      var box = MAIN.querySelector("#wt-item");
      if (S.item) {
        var p = S.item.split(":"), it;
        if (p[0] === "t") { it = K.translations[+p[1]];
          box.innerHTML = '<div class="a-box v-wording"><div class="a-blabel">Translation or print · ' + esc(span(it.y[0], it.y[1])) + '</div><div class="a-btext"><b>' + esc(it.n) + "</b>. " + esc(it.note) + '.</div><div class="a-bnote">Source: <a href="' + esc(it.src) + '">' + esc(it.src.replace(/^https:\/\//, "")) + "</a></div></div>";
        } else { it = K.witness.filter(function (w) { return w.k === p[1]; })[0];
          var mine = "";
          if (it.k === "dss" && c.heb) { var tot = 0; for (var bb in scrollHits) tot += scrollHits[bb]; mine = " <b>" + fmt(tot) + "</b> of the verses that use this word survive in at least one scroll."; }
          box.innerHTML = '<div class="a-box v-witness"><div class="a-blabel">Manuscript</div><div class="a-btext"><b>' + esc(it.n) + "</b>. Copied " + esc(span(it.copy[0], it.copy[1])) +
            "; found or first known " + esc(yr(it.found)) + (it.foundNote ? " (" + esc(it.foundNote) + ")" : "") + ". " + esc(it.place) + ". Held: " + esc(it.held) + ". " + esc(it.has) + "." + mine +
            '</div><div class="a-bnote">Source: <a href="' + esc(it.src) + '">' + esc(it.src.replace(/^https:\/\//, "")) + "</a></div></div>";
        }
      }
    });
  }

  // ---------- view 4: when it happened ----------
  function viewHappened() {
    var c = CUR;
    data("world.json").then(function (Wd) {
      var ax = clockAxis(-2200, 100), L = S.layers, ex = S.hinge.exodus === "late" ? 196 : 0, eg = S.hinge.egypt === "215" ? 215 : 0;
      // a hinge moves what hangs on it: the late Exodus moves the Exodus generation, the short stay in Egypt moves the patriarchs
      function delta(y) { return y <= -1876 ? ex + eg : (y <= -1406 ? ex : 0); }
      function shift(y, from) { return y + delta(from == null ? y : from); }
      var wv = {}; if (c.entry) c.entry.v.forEach(function (i) { wv[i] = 1; });
      var people = Wd.people.rows.filter(function (p) { return p.a >= -2300 && (!S.onlyWord || p.v.some(function (i) { return wv[i]; })); });
      var rows = people.map(function (p, j) {
        var key = "p:" + p.n, on = S.item === key;
        return '<div class="wt-row' + (L.people ? "" : " off") + '"><div class="wt-rl" title="' + esc(p.n) + '">' + esc(p.n) + '</div><div class="wt-rt2">' +
          '<button class="wt-hit" data-item="' + esc(key) + '" aria-label="' + esc(p.n) + '" style="left:0;right:0"></button>' +
          bar(ax, shift(p.a), shift(p.b, p.a), "mid c-people") + (on ? '<span class="wt-bar2 mid c-sel" style="left:' + ax.p(shift(p.a)) + "%;width:" + Math.max(.4, ax.p(shift(p.b, p.a)) - ax.p(shift(p.a))) + '%"></span>' : "") + "</div></div>";
      }).join("");
      var evs = Wd.events.rows.map(function (e, j) { return { y: shift(e.a), n: e.n, k: "e:" + j, w: e.v.some(function (i) { return wv[i]; }) }; });
      var evRows = laneHTML(ax, "Events", "JAYMS OT Timeline", "c-events diamond", evs, "events", trackPx());
      var lanes = Wd.world.lanes.map(function (ln) {
        return '<div class="wt-row tall' + (L[ln.k] ? "" : " off") + '"><div class="wt-rl">' + esc(ln.n) + '</div><div class="wt-rt2" style="height:30px">' +
          ln.bands.map(function (b) { var l = ax.p(b.a), w = Math.max(.4, ax.p(b.b) - l); return '<span class="wt-band c-world" style="left:' + l + "%;width:" + w + '%" title="' + esc(b.n + ", " + span(b.a, b.b)) + '">' + esc(b.n) + "</span>"; }).join("") + "</div></div>";
      }).join("");
      var fam = laneHTML(ax, "Famous moments", "the world knows", "c-famous round", Wd.famous.map(function (f, j) { return { y: f.y, n: f.n, k: "m:" + j }; }), "famous", trackPx());
      // what is selected, and its year span
      var selSpan = null, selName = "", selNote = "", selSrc = "";
      if (S.item) {
        var p = S.item.split(":");
        if (p[0] === "p") { var pp = Wd.people.rows.filter(function (x) { return x.n === S.item.slice(2); })[0]; if (pp) { selSpan = [shift(pp.a), shift(pp.b, pp.a)]; selName = pp.n; selNote = "Lived " + span(selSpan[0], selSpan[1]) + " by " + Wd.people.source.split(";")[1].replace(/^ Old Testament dates from /, "") + "."; selSrc = Wd.people.src; } }
        if (p[0] === "e") { var ee = Wd.events.rows[+p[1]]; selSpan = [shift(ee.a), shift(ee.b, ee.a)]; selName = ee.n; selNote = span(selSpan[0], selSpan[1]) + ". " + ee.ref + "."; selSrc = Wd.events.src; }
        if (p[0] === "m") { var ff = Wd.famous[+p[1]]; selSpan = [ff.y, ff.y]; selName = ff.n; selNote = ff.d + "."; selSrc = ff.src; }
      }
      var cursor = selSpan ? '<span class="wt-cursor" style="left:calc(var(--wt-lab) + (100% - var(--wt-lab)) * ' + (ax.p((selSpan[0] + selSpan[1]) / 2) / 100) + ')"></span>' : "";
      var lay = '<div class="wt-ctl">' + layerBtn("people", "c-people", "Hide people", "Show people") + layerBtn("events", "c-events", "Hide events", "Show events") +
        layerBtn("famous", "c-famous", "Hide famous moments", "Show famous moments") +
        Wd.world.lanes.map(function (ln) { return layerBtn(ln.k, "c-world", "Hide " + ln.n, "Show " + ln.n); }).join("") +
        '<button class="wt-lay on" data-only="1">' + (S.onlyWord ? "Show every person" : "Show only people where this word stands") + "</button></div>";
      var h = '<div class="wt-panel"><p class="wt-stat">When the people lived and the events happened, with the world around them. Each lane names its own source; where the sources disagree, the hinges below say what moves.</p>' + lay +
        '<div class="wt-clock"><div class="wt-tl" style="position:relative">' + cursor + ticks(ax, [-2000, -1750, -1500, -1250, -1000, -750, -500, -250, 1]) +
        '<div class="wt-sec">People · ' + esc(Wd.people.source.split(";")[0]) + "</div>" + (rows || '<div class="wt-row wt-empty"><div class="wt-rl">No one</div><div class="wt-rt2">No dated person is named in a verse with this word.</div></div>') +
        '<div class="wt-sec">Events</div>' + evRows +
        '<div class="wt-sec">The world · ' + esc(Wd.world.source) + "</div>" + lanes + fam + "</div></div></div>" +
        '<div class="wt-pair">' +
          '<div class="a-box v-explain' + (selSpan ? "" : " wt-empty") + '"><div class="a-blabel">Meanwhile, in the world</div><div class="a-btext" id="wt-mean"></div></div>' +
          '<div class="a-box v-open"><div class="a-blabel">Hinges</div><div class="a-btext" style="font-size:15px">One choice moves every date that hangs on it. Pick one and the bars move with it.</div>' +
          Wd.hinges.map(function (hg) {
            return '<div class="wt-sec" style="margin-top:12px">' + esc(hg.n) + '</div><div class="wt-pick">' + hg.opts.map(function (o) {
              return '<button class="a-chip' + (S.hinge[hg.k] === o.k ? " on" : "") + '" data-hinge="' + hg.k + ":" + o.k + '">' + esc(o.n) + "</button>";
            }).join("") + '</div><div class="a-bnote" style="margin-top:4px">' + esc(hg.opts.filter(function (o) { return o.k === S.hinge[hg.k]; })[0].note) + "</div>";
          }).join("") + "</div></div>" +
        '<div class="a-box v-open"><div class="a-blabel">No date</div><div class="a-btext" style="font-size:15px">Not datable from the text. Listed, not placed on the line.</div><div class="wt-shelf" style="margin-top:9px">' +
          Wd.nodate.map(function (n) { return '<span class="a-tag">' + esc(n.n) + " · " + esc(n.ref) + "</span>"; }).join("") + "</div></div>" +
        '<div class="a-box v-apparatus"><div class="a-blabel">Sources for this clock</div><ol>' +
          "<li>People: " + esc(Wd.people.source) + '. <a href="' + Wd.people.src + '">' + esc(Wd.people.src.replace(/^https:\/\//, "")) + "</a></li>" +
          "<li>Events: " + esc(Wd.events.source) + '. <a href="' + Wd.events.src + '">' + esc(Wd.events.src.replace(/^https:\/\//, "")) + "</a></li>" +
          "<li>The world: " + esc(Wd.world.source) + '. <a href="' + Wd.world.src + '">' + esc(Wd.world.src.replace(/^https:\/\//, "")) + "</a></li>" +
          Wd.famous.map(function (f) { return "<li>" + esc(f.n) + ", " + esc(f.d) + '. <a href="' + f.src + '">' + esc(f.src.replace(/^https:\/\//, "")) + "</a></li>"; }).join("") +
        "</ol></div>";
      MAIN.innerHTML = h;
      wireLayers(MAIN, viewHappened);
      MAIN.querySelector("[data-only]").addEventListener("click", function () { S.onlyWord = !S.onlyWord; viewHappened(); });
      [].forEach.call(MAIN.querySelectorAll("[data-item]"), function (m) { m.addEventListener("click", function () { S.item = m.getAttribute("data-item"); viewHappened(); }); });
      [].forEach.call(MAIN.querySelectorAll("[data-hinge]"), function (b) { b.addEventListener("click", function () { var q = b.getAttribute("data-hinge").split(":"); S.hinge[q[0]] = q[1]; viewHappened(); }); });
      var mean = MAIN.querySelector("#wt-mean");
      if (!selSpan) { mean.textContent = "Pick a person, an event or a moment to see what overlaps it in the layers that are on."; return; }
      var lo = selSpan[0] - 40, hi = selSpan[1] + 40, items = [];
      Wd.world.lanes.forEach(function (ln) { if (!L[ln.k]) return; ln.bands.forEach(function (b) { if (b.a <= hi && b.b >= lo) items.push([ln.n, b.n + ", " + span(b.a, b.b)]); }); });
      if (L.famous) Wd.famous.forEach(function (f) { if (f.y >= lo && f.y <= hi) items.push(["Famous", f.n + ", " + f.d]); });
      if (L.people) Wd.people.rows.forEach(function (q) { var a = shift(q.a), b = shift(q.b, q.a); if (q.n !== selName && a <= selSpan[1] && b >= selSpan[0]) items.push(["Alive then", q.n]); });
      mean.innerHTML = "<b>" + esc(selName) + "</b>. " + esc(selNote) + ' <a href="' + esc(selSrc) + '">Source</a>' +
        '<ul class="wt-meanwhile" style="margin-top:10px">' + (items.length ? items.slice(0, 14).map(function (x) { return '<li><span class="wt-dot c-world"></span><span><small>' + esc(x[0]) + ":</small> " + esc(x[1]) + "</span></li>"; }).join("") : "<li>Nothing in the layers that are on overlaps it.</li>") + "</ul>";
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
    [].forEach.call(MOUNT.querySelectorAll(".wt-tab"), function (b) { var on = b.getAttribute("data-view") === S.view; b.classList.toggle("on", on); b.setAttribute("aria-selected", on ? "true" : "false"); });
    MAIN.innerHTML = '<div class="wt-loading">Loading…</div>';
    load().then(function () {
      renderSide(); addTrail(CUR);
      ({ uses: viewUses, lands: viewLands, written: viewWritten, happened: viewHappened })[S.view]();
    }).catch(function (err) {
      MAIN.innerHTML = '<div class="a-box v-not"><div class="a-blabel">Did not load</div><div class="a-btext">The word data did not load. Reload the page to try again.</div></div>';
      if (window.console) console.warn("[word-trail]", err);
    });
  }
  window.addEventListener("hashchange", function () { var w = S.w, g = S.g, v = S.view, vv = S.v; readHash(); if (w !== S.w || g !== S.g || v !== S.view || vv !== S.v) render(); });
  readHash(); renderFoot(); render();
})();
