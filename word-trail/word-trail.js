/* WORD TRAIL — follow one word from the Hebrew, through the Greek Old Testament, into the New Testament.
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
  function lang(s) { return s && s.charAt(0) === "H" ? "he" : "grc"; }

  // ---------- state ----------
  var S = { w: "H776", g: null, view: "uses", v: null, layers: {}, names: true, order: "canon",
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
    });
  }
  function writeHash() {
    var h = "#w=" + S.w + (S.g ? "&g=" + S.g : "") + "&view=" + S.view + (S.v != null ? "&v=" + S.v : "");
    if (location.hash !== h) location.replace(h);
  }

  // ---------- the verse grid ----------
  var BOOKS = null, NV = 0, NTS = 0;
  function bookOf(i) { var lo = 0, hi = BOOKS.length - 1; while (lo < hi) { var m = (lo + hi + 1) >> 1; if (BOOKS[m].s <= i) lo = m; else hi = m - 1; } return lo; }
  function refOf(i) {
    var b = BOOKS[bookOf(i)], r = i - b.s, c = 0;
    while (c < b.c.length && r >= b.c[c]) { r -= b.c[c]; c++; }
    return b.n + " " + (c + 1) + ":" + (r + 1);
  }
  function bookName(i) { return BOOKS[bookOf(i)].n; }

  // ---------- layout ----------
  MOUNT.innerHTML =
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
    '<div class="wt-desk"><aside class="wt-side" id="wt-side"></aside><div class="wt-main" id="wt-main"></div></div>' +
    '<div class="a-foot" id="wt-foot"></div>';
  var SIDE = document.getElementById("wt-side"), MAIN = document.getElementById("wt-main");
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
  Q.addEventListener("focus", ensureSearch);
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
        var p = plain(q), pq = fold(q);
        for (var i = 0; i < rows.length && out.length < 12; i++) {
          var r = rows[i];
          if (r.f == null) r.f = fold(r[2]);
          if (plain(r[1]) === p || (pq && r.f.indexOf(pq) === 0) || (pq.length > 2 && (" " + plain(r[4])).indexOf(" " + p) >= 0)) out.push(r);
        }
      }
      SUGG.innerHTML = out.length ? "" : '<span class="a-empty" style="padding:4px 0">No word matches that.</span>';
      out.slice(0, 12).forEach(function (r) {
        var b = el("button", "a-chip", '<span lang="' + (r[0][0] === "H" ? "he" : "grc") + '">' + esc(r[1]) + "</span> " + esc(r[2]) +
          ' <span class="a-n">' + r[0] + " · " + fmt(r[5]) + "</span>");
        b.title = r[4];
        b.addEventListener("click", function () { go(r[0]); SUGG.hidden = true; Q.value = ""; Q.blur(); });
        SUGG.appendChild(b);
      });
      SUGG.hidden = false;
    }).catch(function () { SUGG.innerHTML = '<span class="a-empty">The word list did not load.</span>'; SUGG.hidden = false; });
  }
  function go(id) { S.w = id; S.g = null; S.v = null; S.link = null; S.item = null; writeHash(); render(); }

  // ---------- the word card ----------
  var CUR = null;   // { w, entry, rend, inv, g, gEntry }
  function load() {
    return Promise.all([data("books.json"), data("greek-ot.json"), word(S.w)]).then(function (r) {
      var books = r[0], bridge = r[1], e = r[2];
      if (!BOOKS) { BOOKS = books.books; NV = books.verses; NTS = BOOKS[39].s; }
      var heb = S.w.charAt(0) === "H";
      var rend = heb ? (bridge.h[S.w] || []) : [], inv = heb ? [] : (bridge.g[S.w] || []);
      if (heb && !S.g) { var first = rend.filter(function (x) { return x[1]; })[0]; S.g = first ? first[1] : null; }
      if (!heb) S.g = S.w;
      return (S.g && S.g !== S.w ? word(S.g) : Promise.resolve(heb ? null : e)).then(function (ge) {
        CUR = { w: S.w, heb: heb, entry: e, rend: rend, inv: inv, g: S.g, gEntry: ge, missing: bridge.missing || [], fn: (bridge.fn || []).indexOf(S.w) >= 0 };
        return CUR;
      });
    });
  }
  function ntUses(ge) { return ge ? ge.v.filter(function (i) { return i >= NTS; }) : []; }
  function bookCount(ids) { var o = {}; ids.forEach(function (i) { o[bookOf(i)] = 1; }); return Object.keys(o).length; }

  function renderSide() {
    var c = CUR, e = c.entry;
    if (!e) { SIDE.innerHTML = '<div class="a-box v-witness"><div class="a-blabel">Not found</div><div class="a-btext">No word has the number ' + esc(c.w) + ".</div></div>"; return; }
    var vs = uniq(e.v), h = "";
    h += '<div class="a-box v-witness"><div class="a-blabel">' + (c.heb ? "The Hebrew word" : "The Greek word") + "</div>" +
      '<div class="wt-word"><span class="wt-lemma" lang="' + lang(c.w) + '">' + esc(e.l) + '</span><span class="wt-gloss">' + esc(e.x) + "</span></div>" +
      '<div class="wt-code">' + esc(c.w) + (e.p ? " · " + esc(e.p) : "") + "</div>" +
      '<p class="wt-def">' + esc(e.d) + "</p>" +
      '<p class="wt-def"><span class="wt-n">' + fmt(e.v.length) + "</span> uses in <span class=\"wt-n\">" + fmt(vs.length) + "</span> verses across <span class=\"wt-n\">" + bookCount(vs) + "</span> books.</p></div>";
    h += '<div class="a-box v-plain wt-empty"><div class="a-blabel">First, in plain English</div><div class="a-btext">Not written yet for this word.</div></div>';
    if (c.heb) {
      h += '<div class="a-box v-wording"><div class="a-blabel">In the Greek Old Testament</div>';
      if (!c.rend.length) h += '<div class="a-btext wt-empty">The Greek Old Testament gives no settled rendering for this word.</div>';
      var tot = c.rend.reduce(function (a, r) { return a + r[2]; }, 0);
      c.rend.forEach(function (r) {
        var on = r[1] && r[1] === c.g;
        h += '<button class="wt-rend' + (on ? " on" : "") + '" data-g="' + (r[1] || "") + '"' + (r[1] ? "" : " disabled") + ">" +
          '<span class="wt-rg" lang="grc">' + esc(r[0]) + "</span>" +
          '<span class="wt-rb"><span class="wt-rt">' + (r[1] ? r[1] + " · " : "not in the New Testament · ") + fmt(r[2]) + " times</span>" +
          '<span class="wt-bar"><span style="width:' + Math.max(2, Math.round(100 * r[2] / tot)) + '%"></span></span></span>' +
          '<span class="wt-ra">' + (on ? "Following" : (r[1] ? "Follow" : "")) + "</span></button>";
      });
      h += '<div class="a-bnote">' + (c.fn ? "This is a grammar word. Its counts mostly follow sentence structure, not translation choice, so read them loosely. " : "") +
        'How often the Greek Old Testament gives each Greek word for this Hebrew word. Pick one to follow it into the New Testament.' +
        (c.missing.length ? " Not counted: " + esc(c.missing.join(", ")) + ", which the open Greek text used here does not have." : "") + "</div></div>";
    } else {
      h += '<div class="a-box v-wording"><div class="a-blabel">Behind it in the Hebrew</div>';
      if (!c.inv.length) h += '<div class="a-btext wt-empty">The Greek Old Testament does not use this word for any Hebrew word often enough to count.</div>';
      var t2 = c.inv.reduce(function (a, r) { return a + r[1]; }, 0);
      c.inv.forEach(function (r) {
        h += '<button class="wt-rend" data-h="' + r[0] + '"><span class="wt-rg" lang="he">' + esc(r[2] || r[0]) + '</span><span class="wt-rb"><span class="wt-rt">' + esc(r[3] || "") + " · " + r[0] + " · " + fmt(r[1]) + " times</span>" +
          '<span class="wt-bar"><span style="width:' + Math.max(2, Math.round(100 * r[1] / t2)) + '%"></span></span></span><span class="wt-ra">Open</span></button>';
      });
      h += '<div class="a-bnote">The Hebrew words the Greek Old Testament translates with this Greek word. Pick one to start from the Hebrew.</div></div>';
    }
    var nt = ntUses(c.gEntry), ntv = uniq(nt);
    h += '<div class="a-box v-witness' + (nt.length ? "" : " wt-empty") + '"><div class="a-blabel">Into the New Testament</div><div class="a-btext">' +
      (c.gEntry ? '<span lang="grc">' + esc(c.gEntry.l) + "</span> stands <span class=\"wt-n\">" + fmt(nt.length) + "</span> times in <span class=\"wt-n\">" + fmt(ntv.length) +
        "</span> verses across <span class=\"wt-n\">" + bookCount(ntv) + "</span> New Testament books." : "No Greek word to follow.") + "</div></div>";
    h += '<div class="wt-links"><a class="a-lnk" href="' + WORDSTUDY + '">Open Word Study</a><a class="a-lnk" href="' + ECHO + '">Open Echoes of Revelation</a><a class="a-lnk" href="' + TDX + '">Why do translations differ?</a></div>';
    SIDE.innerHTML = h;
    [].forEach.call(SIDE.querySelectorAll(".wt-rend[data-g]"), function (b) {
      b.addEventListener("click", function () { var g = b.getAttribute("data-g"); if (!g) return; S.g = g; S.v = null; writeHash(); render(); });
    });
    [].forEach.call(SIDE.querySelectorAll(".wt-rend[data-h]"), function (b) {
      b.addEventListener("click", function () { go(b.getAttribute("data-h")); });
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
  function viewUses() {
    var c = CUR, e = c.entry; if (!e) { MAIN.innerHTML = ""; return; }
    var need = S.order === "date" && !DATES ? data("clock.json").then(function (k) { DATES = k.datesA.books; }) : Promise.resolve();
    need.then(function () {
      var heb = c.heb ? uniq(e.v) : [], nt = uniq(ntUses(c.gEntry)), ax = axisX(S.order), H = 430, B = H - 6;
      var all = heb.concat(nt), center = all.length ? all[0] : 0;
      if (!c.heb) { heb = []; }
      var x0 = ax.x(center), k = (H - 30) / 500;
      function arc(x) { var rx = Math.abs(x - x0) / 2; return "M" + x0.toFixed(1) + " " + B + "A" + rx.toFixed(1) + " " + Math.max(2, rx * k).toFixed(1) + " 0 0 " + (x >= x0 ? 1 : 0) + " " + x.toFixed(1) + " " + B; }
      var dH = S.layers.heb ? heb.map(function (i) { return arc(ax.x(i)); }).join("") : "";
      var dN = S.layers.nt ? nt.map(function (i) { return arc(ax.x(i)); }).join("") : "";
      var sel = S.v != null && all.indexOf(S.v) >= 0 ? S.v : null;
      var h = '<div class="wt-panel">' +
        '<p class="wt-stat">' + (c.heb ? "<b>" + fmt(e.v.length) + "</b> Hebrew uses in <b>" + fmt(heb.length) + "</b> verses" : "") +
        (c.g ? (c.heb ? ", and " : "") + "<b>" + fmt(nt.length) + "</b> New Testament verses with <span lang=\"grc\">" + esc(c.gEntry ? c.gEntry.l : "") + "</span>" : "") +
        ". Every arc starts at the first use, <b>" + esc(refOf(center)) + "</b>.</p>" +
        '<div class="wt-ctl">' +
          (c.heb ? layerBtn("heb", "c-heb", "Hide Hebrew uses", "Show Hebrew uses") : "") +
          layerBtn("nt", "c-nt", "Hide New Testament uses", "Show New Testament uses", !c.g) +
          '<button class="wt-lay on" data-names="1">' + (S.names ? "Hide book names" : "Show book names") + "</button>" +
          '<button class="wt-lay on" data-order="1">' + (S.order === "canon" ? "Order by date written" : "Order by canon") + "</button>" +
        "</div>" +
        '<div class="wt-arcwrap"><svg class="wt-arcs" viewBox="0 0 1000 ' + (H + (S.names ? 64 : 14)) + '" role="img" aria-label="Arc chart of every use of the word">' +
          '<path class="heb" fill="none" stroke-width="1" d="' + dH + '"></path>' +
          '<path class="nt" fill="none" stroke-width="1" d="' + dN + '"></path>' +
          (sel != null ? '<path class="sel" fill="none" d="' + arc(ax.x(sel)) + '"></path><circle class="ctr" cx="' + ax.x(sel).toFixed(1) + '" cy="' + B + '" r="4"></circle>' : "") +
          '<line class="bridge" x1="' + (ax.ow + ax.G / 2) + '" y1="24" x2="' + (ax.ow + ax.G / 2) + '" y2="' + B + '"></line>' +
          '<text class="lbl" x="' + (ax.ow + ax.G / 2) + '" y="16" text-anchor="middle">GREEK OLD TESTAMENT</text>' +
          '<circle class="ctr" cx="' + x0.toFixed(1) + '" cy="' + B + '" r="6"></circle>' +
          '<path class="hover" id="wt-hov" fill="none" d=""></path>' +
          '<g class="wt-axis" transform="translate(0,' + (B + 2) + ')">' + axisSvg(ax) + "</g>" +
          '<rect id="wt-hitbox" x="0" y="0" width="1000" height="' + (H + 14) + '" fill="transparent"></rect>' +
        "</svg></div>" +
        '<div class="wt-legend">' + (c.heb ? '<span class="c-heb"><i></i>Hebrew uses</span>' : "") + '<span class="c-nt"><i></i>New Testament uses through the Greek</span><span class="c-sel"><i></i>Selected</span>' +
        '<span id="wt-hovref"></span></div></div>' +
        '<div class="wt-pair" id="wt-verses"></div>' +
        '<div class="a-box v-explain" style="margin-top:13px"><div class="a-blabel">How to read this</div><div class="a-btext">Each arc is one verse. Tap the chart and the nearest verse opens below. ' +
        "The dashed line is where the Greek Old Testament stands between the two testaments: the gold arcs are the New Testament verses that use the Greek word the Greek Old Testament chose.</div></div>";
      MAIN.innerHTML = h;
      wireLayers(MAIN, viewUses);
      MAIN.querySelector("[data-names]").addEventListener("click", function () { S.names = !S.names; viewUses(); });
      MAIN.querySelector("[data-order]").addEventListener("click", function () { S.order = S.order === "canon" ? "date" : "canon"; viewUses(); });
      var svg = MAIN.querySelector("svg"), hov = MAIN.querySelector("#wt-hov"), hr = MAIN.querySelector("#wt-hovref");
      var shown = (S.layers.heb ? heb : []).concat(S.layers.nt ? nt : []).map(function (i) { return [ax.x(i), i]; }).sort(function (a, b) { return a[0] - b[0]; });
      function nearest(evt) {
        var pt = svg.createSVGPoint(); pt.x = evt.clientX; pt.y = evt.clientY;
        var p = pt.matrixTransform(svg.getScreenCTM().inverse()), best = null, bd = 1e9;
        for (var j = 0; j < shown.length; j++) { var d = Math.abs(shown[j][0] - p.x); if (d < bd) { bd = d; best = shown[j][1]; } }
        return bd < 12 ? best : null;
      }
      svg.addEventListener("mousemove", function (evt) { var i = nearest(evt); hov.setAttribute("d", i == null ? "" : arc(ax.x(i))); hr.textContent = i == null ? "" : refOf(i); });
      svg.addEventListener("mouseleave", function () { hov.setAttribute("d", ""); hr.textContent = ""; });
      svg.addEventListener("click", function (evt) { var i = nearest(evt); if (i == null) return; S.v = i; writeHash(); viewUses(); });
      showVerses(sel != null ? sel : center);
    });
  }
  function axisSvg(ax) {
    var s = "";
    for (var b = 0; b < BOOKS.length; b++) {
      var r = ax.bx(b), cls = (b < 39 ? "ot" : "nt") + (b % 2);
      s += '<rect class="' + cls + '" x="' + r[0].toFixed(2) + '" y="0" width="' + Math.max(.6, r[1] - .4).toFixed(2) + '" height="6"></rect>';
      if (S.names) s += '<text class="' + (b < 39 ? "" : "nt") + '" transform="translate(' + (r[0] + r[1] / 2).toFixed(1) + ',12) rotate(55)">' + esc(BOOKS[b].a) + "</text>";
    }
    return s;
  }
  function showVerses(i) {
    var box = MAIN.querySelector("#wt-verses"); if (!box) return;
    box.innerHTML = '<div class="a-box v-scripture" id="wt-v1"></div><div class="a-box v-scripture" id="wt-v2"></div>';
    var ref = refOf(i);
    verseBox(box.querySelector("#wt-v1"), (S.v != null ? "Selected · " : "First use · ") + ref + " · NET", ref);
    var v2 = box.querySelector("#wt-v2");
    if (i < NTS) { v2.className = "a-box v-explain wt-empty"; v2.innerHTML = '<div class="a-blabel">Behind it</div><div class="a-btext">Pick a gold arc to see a New Testament verse beside the Old Testament text behind it.</div>'; return; }
    data("links.json").then(function (L) {
      var hit = L.links.filter(function (l) { return l[5].indexOf(i) >= 0 && (!CUR.heb || l[4].some(function (o) { return CUR.entry.v.indexOf(o) >= 0; })); })[0]
             || L.links.filter(function (l) { return l[5].indexOf(i) >= 0; })[0];
      if (!hit) { v2.className = "a-box v-explain wt-empty"; v2.innerHTML = '<div class="a-blabel">Behind it</div><div class="a-btext">No graded Old Testament link for this verse yet. Graded links come from Echoes of Revelation, which covers Revelation only so far.</div>'; return; }
      var otref = refOf(hit[4][0]) + (hit[4].length > 1 ? "-" + refOf(hit[4][hit[4].length - 1]).split(":").pop() : "");
      verseBox(v2, "Behind it · " + otref + " · " + hit[2] + " echo", refOf(hit[4][0]) + (hit[4].length > 1 ? "-" + refOf(hit[4][hit[4].length - 1]).split(" ").pop() : ""));
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
        var o0 = refOf(sel[4][0]), o1 = refOf(sel[4][sel[4].length - 1]);
        var oref = sel[4].length > 1 ? (bookName(sel[4][0]) === bookName(sel[4][sel[4].length - 1]) ? o0 + "-" + o1.split(" ").pop() : o0) : o0;
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
        "Copied and found are different dates: the Leningrad Codex was copied in 1008 and reached St Petersburg in the 1800s; the Dead Sea Scrolls were copied before Jesus and found in 1947. " +
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
          '<div class="a-box v-open"><div class="a-blabel">Hinges</div><div class="a-btext" style="font-size:15px">One choice moves every date that hangs on it. Pick one and watch the bars shift.</div>' +
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
      "Those counts are statistical, the same way Hidden Manna's are: they say how often, not that every instance is right. Swete's transcription carries some scanning errors, and it has no Ecclesiastes.</p>" +
      '<p class="a-note">Sources. Hebrew and Greek: Macula Hebrew (WLC) and Macula Greek (SBLGNT), Clear Bible, CC BY 4.0, via jayms interlinear-data. Greek Old Testament: H. B. Swete, The Old Testament in Greek, First1KGreek transcription, lemmatized by QuantForge (Morpheus), CC BY-SA 4.0. ' +
      "Verse numbers: STEPBible TVTMS, CC BY 4.0. Lexicon: Strong's (Open Scriptures, CC BY-SA). Dead Sea Scrolls by verse: ETCBC/dss, Abegg's data, CC BY-NC 4.0. People: Theographic Bible Metadata, CC BY-SA 4.0. World: Cliopatria, Seshat Global History Databank, CC BY 4.0. Verse text: the NET Bible, labs.bible.org. " +
      "The Greek Old Testament counts and the people layer are shared under CC BY-SA 4.0, as their sources require.</p>";
  }

  // ---------- render ----------
  function render() {
    [].forEach.call(MOUNT.querySelectorAll(".wt-tab"), function (b) { var on = b.getAttribute("data-view") === S.view; b.classList.toggle("on", on); b.setAttribute("aria-selected", on ? "true" : "false"); });
    MAIN.innerHTML = '<div class="wt-loading">Loading…</div>';
    load().then(function () {
      renderSide();
      ({ uses: viewUses, lands: viewLands, written: viewWritten, happened: viewHappened })[S.view]();
    }).catch(function (err) {
      MAIN.innerHTML = '<div class="a-box v-not"><div class="a-blabel">Did not load</div><div class="a-btext">The word data did not load. Reload the page to try again.</div></div>';
      if (window.console) console.warn("[word-trail]", err);
    });
  }
  window.addEventListener("hashchange", function () { var w = S.w, g = S.g, v = S.view, vv = S.v; readHash(); if (w !== S.w || g !== S.g || v !== S.view || vv !== S.v) render(); });
  readHash(); renderFoot(); render();
})();
