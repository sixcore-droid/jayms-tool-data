/**
 * JAYMS Alpha Tool Runner
 *
 * One snippet, every alpha tool. A tool is a row in $TOOLS below plus a JSON
 * document in sixcore-droid/jayms-tool-data. No per-tool PHP, no per-tool JS,
 * no per-tool CSS. Adding a tool is one row and one file.
 *
 * Deliberately self-contained: it does not read, extend or depend on the
 * live Tool Shell (118) or Tool Engine (119), so nothing here can affect
 * the four production tools. Everything is namespaced jaymsAlpha* and
 * scoped under .jayms-tool-alpha.
 */

/**
 * The tool map: page id => data slug. One row here is the whole of adding a
 * tool. It is a function rather than a literal because the entry-URL layer
 * needs the same map, and a second copy of it would be a second thing to
 * forget.
 */
if ( ! function_exists( 'jayms_alpha_tool_map' ) ) {
	function jayms_alpha_tool_map() {
		return array(
		98131 => 'divine-council-alpha',
		98132 => 'gods-of-the-bible-alpha',
		98934 => 'fact-book-alpha',
		98949 => 'myth-checker-alpha',
		98950 => 'fringe-files-alpha',
		98951 => 'translation-differences-alpha',
		96255 => 'word-study-alpha',
		99108 => 'genesis-beginning-alpha',
		99109 => 'lewis-cosmology-alpha',
	);
	}
}


add_action( 'wp_footer', function () {

	$TOOLS = jayms_alpha_tool_map();

	if ( ! is_page() ) {
		return;
	}
	$pid = get_queried_object_id();
	if ( ! isset( $TOOLS[ $pid ] ) ) {
		return;
	}

	$slug = $TOOLS[ $pid ];
	$base = 'https://raw.githubusercontent.com/sixcore-droid/jayms-tool-data/main/';
	$src  = esc_url( $base . $slug . '.json' );

	// The page template does not output a featured image, so setting one in
	// WordPress appeared to do nothing. Hand it to the engine instead.
	$featured = has_post_thumbnail( $pid )
		? get_the_post_thumbnail( $pid, 'large', array( 'class' => 'jayms-tool-featured' ) )
		: '';
	?>

<?php /* The visual system moved to its own snippet (141) so every tool
   shares one stylesheet rather than each growing its own copy. */ ?>

<script id="jayms-alpha-engine">
(function () {
  "use strict";

  var SRC      = <?php echo wp_json_encode( $src ); ?>;
  var SLUG     = <?php echo wp_json_encode( $slug ); ?>;
  var FEATURED = <?php echo wp_json_encode( $featured ); ?>;
  var MOUNT    = document.getElementById("app");
  if (!MOUNT) return;

  // An empty document, not null. The hashchange listener is live from the
  // first line, so a back press during the data fetch would otherwise
  // reach readURL and throw on DOC.filters. Same shape, no content.
  var DOC = { tool: {}, card: {}, detail: {}, search: {},
              filters: [], blocks: [], footer: [], entries: [] };
  var LOADED = false;
  var ENTRIES = [], BY_ID = {};
  var state = { screen: "browse", id: null, q: "", filters: {}, page: 1 };
  var openRows = {};
  var lastCount = "";

  // Everything here is read off the mount div, so the page controls it and a
  // change needs no deploy:
  //   data-filters="all" | "category,group"   which rows show, and their order
  //   data-filters-hide="witness"             all rows except these
  //   data-per-page="12"                      override the dataset's page size
  //   data-search="off"                       drop the search box
  //   data-default-group="council"            land pre-filtered on a value
  // data-filters takes one entry per row, "<field>:<state>", where state is
  //   open    always expanded, every option visible
  //   closed  one line showing the current value, tap to expand
  //   off     not rendered at all, and its tag comes off the cards
  // A row you do not name keeps the dataset's own default. Named rows lead,
  // in the order you wrote them, so the list also reorders. "all" means
  // every row at its default.
  function parseFilterSpec(raw) {
    var spec = { order: [], state: {} };
    raw = (raw || "all").trim();
    if (!raw || raw.toLowerCase() === "all") return spec;
    raw.split(",").forEach(function (part) {
      part = part.trim();
      if (!part) return;
      var bits = part.split(":");
      var name = (bits[0] || "").trim();
      if (!name) return;
      spec.order.push(name);
      var st = (bits[1] || "").trim().toLowerCase();
      if (st) spec.state[name] = st;
    });
    return spec;
  }

  var PAGE = {
    spec:    parseFilterSpec(MOUNT.dataset.filters),
    hide:    (MOUNT.dataset.filtersHide || "").split(",").map(function (s) { return s.trim(); }).filter(Boolean),
    perPage: parseInt(MOUNT.dataset.perPage, 10) || null,
    search:  (MOUNT.dataset.search || "on").trim() !== "off",
    progress: (MOUNT.dataset.progress || "on").trim() !== "off",
    layout:  (MOUNT.dataset.layout || "grid").trim().toLowerCase(),
    columns: (MOUNT.dataset.columns || "auto").trim().toLowerCase(),
    defGroup: (MOUNT.dataset.defaultGroup || "").trim(),

    // How a row is put together, decided by the page rather than by the
    // dataset, because the same data reads differently on different pages
    // and nobody should edit a snippet to change a column. Each of these
    // falls back to the dataset, and the dataset falls back to a default,
    // so a page that says nothing keeps working exactly as before.
    //   data-search  off | on | text | name | both
    //   data-tags    none | "<field>, <field>"
    //   data-badge   on | off
    //   data-label / data-main / data-sub   <field>
    searchMode: (MOUNT.dataset.search || "").trim().toLowerCase(),
    tags:    MOUNT.dataset.tags == null ? null
             : String(MOUNT.dataset.tags).split(",")
                 .map(function (s) { return s.trim(); }).filter(Boolean),
    badge:   (MOUNT.dataset.badge || "on").trim() !== "off",
    suggest: (MOUNT.dataset.suggest || "on").trim() !== "off",
    label:   (MOUNT.dataset.label || "").trim(),
    main:    (MOUNT.dataset.main || "").trim(),
    sub:     (MOUNT.dataset.sub || "").trim(),
    row:     (MOUNT.dataset.row || "columns").trim().toLowerCase()
  };

  // ---------------------------------------------------------- progress
  //
  // Which entries a reader has worked through. Browser only, on purpose.
  // PROGRESS-AND-ACCOUNTS.md in the data repo records what moving this to
  // real accounts would take, and what has to stay true for that to be a
  // copy rather than a rebuild. The shape is deliberately portable:
  //   { "<entry id>": 1 }   stored per tool slug.
  // Every read and write is guarded: storage throws in a private window and
  // returns nothing when a reader has site data blocked.
  var PROGRESS_KEY = "jayms.progress." + SLUG;
  var progress = {};

  function loadProgress() {
    try {
      progress = JSON.parse(localStorage.getItem(PROGRESS_KEY) || "{}") || {};
    } catch (e) { progress = {}; }
  }
  function saveProgress() {
    try { localStorage.setItem(PROGRESS_KEY, JSON.stringify(progress)); } catch (e) {}
  }
  function isDone(id) { return progress[id] === 1; }
  function doneCount() {
    var n = 0;
    for (var i = 0; i < ENTRIES.length; i++) if (isDone(ENTRIES[i].id)) n++;
    return n;
  }
  loadProgress();

  window.jaymsAlphaToggleDone = function (id) {
    if (isDone(id)) delete progress[id]; else progress[id] = 1;
    saveProgress();
    render();
  };

  window.jaymsAlphaResetProgress = function () {
    progress = {};
    saveProgress();
    render();
  };

  // Slots the page may place anywhere. Absent slot, no output, no error.
  function slot(id) { return document.getElementById(id); }
  var PAGE_HERO = document.querySelector(".jayms-tool-hero");

  // ------------------------------------------------------------ text

  function esc(s) {
    return String(s == null ? "" : s)
      .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");
  }

  // Escape FIRST, then apply the small inline markup the schema allows.
  // This is why the stored data can never double-escape itself: nothing in
  // the JSON is HTML, so nothing has to survive a second pass.
  function rich(s) {
    var t = esc(s);
    t = t.replace(/\*\*([^*]+)\*\*/g, "<b>$1</b>");
    t = t.replace(/(^|[\s(])\*([^*\n]+)\*/g, "$1<i>$2</i>");
    var paras = t.split(/\n\s*\n/).filter(function (p) { return p.trim(); });
    return paras.map(function (p) { return "<p>" + p.trim() + "</p>"; }).join("");
  }

  function firstFilter(role) {
    return (DOC.filters || []).filter(function (f) { return f.role === role; })[0] || null;
  }
  function catRow()  { return firstFilter("category"); }

  function rowState(row) {
    var s = PAGE.spec.state;
    return s[row.field] || s[row.role] || null;
  }

  // Rows the page wants, named ones first in the order given, the rest after
  // in dataset order, minus anything switched off.
  function visibleFilters() {
    var all = DOC.filters || [];
    var named = PAGE.spec.order.map(function (name) {
      return all.filter(function (r) { return r.field === name || r.role === name; })[0];
    }).filter(Boolean);
    var rest = all.filter(function (r) { return named.indexOf(r) === -1; });
    return named.concat(rest).filter(function (r) {
      if (rowState(r) === "off") return false;
      // a rail-only row is how the list is grouped, not a filter anyone
      // picks: twenty-six letter chips would drown the rows they index
      if (r.railOnly) return false;
      return PAGE.hide.indexOf(r.field) === -1 && PAGE.hide.indexOf(r.role) === -1;
    });
  }

  // The page wins, then the dataset, then the built-in default: the category
  // row open, everything else folded, which keeps a four-filter tool to four
  // lines without anyone configuring it.
  function rowDisplay(row) {
    var st = rowState(row);
    if (st === "open") return "chips";
    if (st === "closed") return "menu";
    if (row.display) return row.display;
    return row.role === "category" ? "chips" : "menu";
  }

  // How many entries an option would return if you tapped it: every other
  // active filter and the search still applied, this row's own choice ignored.
  function optionCount(row, key) {
    var n = 0;
    for (var i = 0; i < ENTRIES.length; i++) {
      var d = ENTRIES[i];
      if (d[row.field] !== key) continue;
      if (matches(d, row.field)) n++;
    }
    return n;
  }
  function catOf(k) {
    var r = catRow(); if (!r) return null;
    return r.options.filter(function (o) { return o.k === k; })[0] || null;
  }
  function colourVar(c) { return c ? "var(--a-" + c + ")" : "var(--a-muted)"; }

  // ------------------------------------------------------------ routing

  // The tool routes through the fragment, not the query string. Going back
  // to a pushState entry forces a full document reload on this site; going
  // back to a hash-created entry does not. That was measured on a page
  // carrying no tool code at all, so it is site-level rather than ours. The
  // interactive outline was moved first and has run clean since.
  //
  // The two URLs carry the same parameters, so one reader serves both and
  // links shared before the move still resolve.
  var OWN_PARAMS = ["id", "q", "pg"];

  function isOwnParam(k) {
    return OWN_PARAMS.indexOf(k) >= 0 || k.indexOf("f_") === 0;
  }

  // Anything the visitor arrived with that is not tool state -- utm tags and
  // the like -- survives the rewrite below.
  function keptQuery() {
    var p = new URLSearchParams(location.search), out = new URLSearchParams();
    p.forEach(function (v, k) { if (!isOwnParam(k)) out.set(k, v); });
    var s = out.toString();
    return s ? "?" + s : "";
  }

  function hasLegacyParams() {
    var p = new URLSearchParams(location.search), yes = false;
    p.forEach(function (v, k) { if (isOwnParam(k)) yes = true; });
    return yes;
  }

  function viewFrom(p) {
    // a witness text read in full: #read=<book>|<chapter>
    if (p.has("read")) {
      var r = String(p.get("read") || "").split("|");
      if (r[0] && r[1]) {
        return { screen: "reading", book: r[0], chapter: r[1],
                 id: null, q: "", filters: {}, page: 1, trail: [] };
      }
    }
    if (p.has("id")) {
      var v = p.get("id");
      if (v) return { screen: "detail", id: v, q: "", filters: {}, page: 1, trail: [v] };
    }
    var f = {};
    // every f_<field> param, without needing to know the filter list yet
    p.forEach(function (val, key) {
      if (key.indexOf("f_") === 0 && val) f[key.slice(2)] = val;
    });
    return {
      screen: "browse", id: null,
      q: p.get("q") || "",
      filters: f,
      page: parseInt(p.get("pg"), 10) || 1,
      trail: []
    };
  }

  // Reads the URL alone. It must not touch DOC or ENTRIES, because it runs
  // at parse time, before the dataset has been fetched.
  function readURL() {
    var h = location.hash.replace(/^#/, "");
    if (h) return viewFrom(new URLSearchParams(h));
    return viewFrom(new URLSearchParams(location.search));
  }

  // The landing filter a page can request. Needs the dataset, so it runs
  // once the data is in, and only when the URL asked for nothing itself.
  function applyDefaultGroup() {
    if (!PAGE.defGroup) return;
    if (state.screen !== "browse") return;
    if (state.q || Object.keys(state.filters).length) return;
    var rows = visibleFilters();
    var g = rows.filter(function (r) { return r.field === "group"; })[0]
         || rows.filter(function (r) { return r.role !== "category"; })[0];
    if (g && g.options.some(function (o) { return o.k === PAGE.defGroup; })) {
      state.filters[g.field] = PAGE.defGroup;
    }
  }

  function hashFor(s) {
    if (s.screen === "reading") {
      return "#read=" + encodeURIComponent(s.book) + "|" + encodeURIComponent(s.chapter);
    }
    if (s.screen === "detail") return "#id=" + encodeURIComponent(s.id);
    var p = new URLSearchParams();
    Object.keys(s.filters || {}).forEach(function (k) {
      if (s.filters[k]) p.set("f_" + k, s.filters[k]);
    });
    if (s.q) p.set("q", s.q);
    if (s.page && s.page !== 1) p.set("pg", s.page);
    var qs = p.toString();
    // An unfiltered list still needs a fragment of its own, or stepping back
    // to it from a filtered one is not a URL change and no history entry is
    // made. "all" carries no value; the reader ignores keys it does not know.
    return qs ? "#" + qs : "#all";
  }

  var browseY = 0;

  // A hash entry carries no state object, so the trail lives beside the
  // history stack, keyed by the hash it belongs to. Back and forward read it
  // straight back out, which is what keeps the crumbs agreeing with the
  // browser. A cold load of a shared link finds nothing here and falls back
  // to a trail of one, exactly as before.
  var TRAILS = {};
  var hashSelfSet = false;

  function go(patch) {
    var was = state.screen;
    if (was === "browse") browseY = window.scrollY;

    // The trail is the route taken to get here, so following "Reads with"
    // from one entry to the next leaves a path back through both.
    if (patch.screen === "detail" && patch.id) {
      var t = (was === "detail" && Array.isArray(state.trail)) ? state.trail.slice() : [];
      var at = t.indexOf(patch.id);
      if (at >= 0) t = t.slice(0, at);   // stepping back onto a link already walked
      t.push(patch.id);
      patch.trail = t;
    } else if (patch.screen === "browse") {
      patch.trail = [];
    }

    Object.assign(state, patch);
    var h = hashFor(state);
    TRAILS[h] = (state.trail || []).slice();
    if (location.hash !== h) { hashSelfSet = true; location.hash = h; }
    render();
    if (state.screen === "browse" && was !== "browse") window.scrollTo(0, browseY);
    else if (state.screen !== was) MOUNT.scrollIntoView({ behavior: "smooth", block: "start" });
  }
  window.jaymsAlphaGo = go;

  window.jaymsAlphaFilter = function (field, val) {
    var f = Object.assign({}, state.filters);
    if (val === null) delete f[field]; else f[field] = val;
    openRows[field] = false;   // choosing closes the row it came from
    go({ filters: f, page: 1 });
  };

  window.jaymsAlphaToggleRow = function (field) {
    openRows[field] = !openRows[field];
    render();
  };

  window.jaymsAlphaClearSearch = function () { go({ q: "", page: 1 }); };
  window.jaymsAlphaClearAll = function () { go({ q: "", filters: {}, page: 1 }); };

  // ------------------------------------------------------------ browse

  // `ignoreField` lets option counts ask "what if this row were unset"
  function matches(d, ignoreField) {
    var rows = visibleFilters();
    for (var i = 0; i < rows.length; i++) {
      if (ignoreField && rows[i].field === ignoreField) continue;
      var sel = state.filters[rows[i].field];
      if (sel && d[rows[i].field] !== sel) return false;
    }
    if (state.q && !searchScore(d)) return false;
    return true;
  }

  // Searching a list of names is not searching prose. One letter has to
  // mean "names that begin with it", not "every row whose description
  // happens to contain it" -- across three thousand entries that was all
  // of them, and the search did nothing. So the name is scored, and the
  // other fields only widen a query specific enough to mean something.
  function fold(s) {
    s = String(s == null ? "" : s).toLowerCase();
    try { s = s.normalize("NFD").replace(/[̀-ͯ]/g, ""); } catch (e) {}
    return s;
  }

  // every letter of the query in order, not necessarily together: catches a
  // half-remembered spelling without matching the whole dataset
  function subseq(q, t) {
    var i = 0;
    for (var j = 0; j < t.length && i < q.length; j++) {
      if (t.charAt(j) === q.charAt(i)) i++;
    }
    return i === q.length;
  }

  // how many single-letter edits turn one word into the other, bailing out
  // as soon as the two are too far apart in length to be the same word
  function dist(a, b) {
    var m = a.length, n = b.length, prev = [], cur = [], i, j;
    if (Math.abs(m - n) > 2) return 9;
    for (j = 0; j <= n; j++) prev[j] = j;
    for (i = 1; i <= m; i++) {
      cur[0] = i;
      for (j = 1; j <= n; j++) {
        cur[j] = Math.min(prev[j] + 1, cur[j - 1] + 1,
          prev[j - 1] + (a.charAt(i - 1) === b.charAt(j - 1) ? 0 : 1));
      }
      for (j = 0; j <= n; j++) prev[j] = cur[j];
    }
    return prev[n];
  }

  function searchScore(d) {
  // Scoring answers how well a row matches; this answers which match the
  // reader meant. Someone typing "love" wants love, not "lovers", and
  // someone typing "shalom" wants shalom, not "abishalom". Without this a
  // near miss that sorts earlier in the data buries the exact answer.
  var s = searchScoreBase(d);
  var q = fold(String(state.q == null ? "" : state.q).trim());
  if (!q || !s) return s;
  var tf = (DOC.detail && DOC.detail.titleField) || "title";
  var mf = (DOC.card && DOC.card.mainField) || "summary";
  var t = fold(String(d[tf] == null ? "" : d[tf]));
  var m = fold(String(d[mf] == null ? "" : d[mf]));
  if (t === q) return s + 400;
  if (m === q) return s + 300;
  if (t.indexOf(q) === 0) return s + 200;
  if ((" " + t + " ").indexOf(" " + q + " ") >= 0) return s + 150;
  if ((" " + m + " ").indexOf(" " + q + " ") >= 0) return s + 100;
  // A whole word anywhere the dataset lets search look still beats a
  // fragment: the pointed Hebrew copied out of the Interleaved Bible is a
  // whole form of one word, and a letter sequence inside another.
  var fs = (DOC.search && DOC.search.fields) || [];
  for (var i = 0; i < fs.length; i++) {
    var val = d[fs[i]];
    if (!val) continue;
    if ((" " + fold(String(val)) + " ").indexOf(" " + q + " ") >= 0) return s + 60;
  }
  return s;
}
function searchScoreBase(d) {
    var q = fold(state.q).trim();
    if (!q) return 1;
    var titleField = (DOC.detail && DOC.detail.titleField) || "title";
    var t = fold(d[titleField]);
    if (t === q) return 100;
    // Typing is a reader walking the alphabet to a name, so a query is the
    // beginning of a name and nothing else. Matching inside one answers
    // "eno" with Hazar-Enon, AEnon and Ishbi-Benob, which is not what
    // anybody typing "eno" was after.
    if (t.indexOf(q) === 0) return 90;
    // A list of names is walked by its spelling; a set of passages is
    // searched by what it is about. Which one a page is belongs to the
    // page, then to the dataset, then to "text" -- what every tool did
    // before any mode existed.
    //   name  the start of a name, or a name spelled almost right
    //   text  the query anywhere in the fields the tool named
    //   both  either one answers
    var mode = PAGE.searchMode;
    if (!mode || mode === "on" || mode === "off") {
      mode = (DOC.search && DOC.search.mode) || "text";
    }

    var byName = 0;
    if (mode === "name" || mode === "both") {
      // the one exception to prefix: a name spelled almost right.
      // "ezekeil" and "damascas" are what people actually type, and edit
      // distance refuses to stretch to a word of a different length.
      if (q.length >= 4) {
        var dn = dist(q, t);
        if (dn <= (q.length >= 6 ? 2 : 1)) byName = 40 - dn;
      }
    }
    if (mode === "name") return byName;

    var byText = t.indexOf(q) > -1 ? 50 : 0;
    if (!byText) {
      var fields = (DOC.search && DOC.search.fields) || ["title", "summary"];
      for (var k = 0; k < fields.length; k++) {
        var v = d[fields[k]];
        if (fold(Array.isArray(v) ? v.join(" ") : v).indexOf(q) > -1) { byText = 10; break; }
      }
    }
    return Math.max(byName, byText);
  }

  function dotsHTML() {
    var cr = catRow();
    if (!cr) return "";
    return cr.options.map(function (o) {
      return '<span class="a-dot" style="background:' + colourVar(o.c) + '"></span>';
    }).join("");
  }

  // The page owns the hero. This runs only when the page supplies none, so a
  // brand new tool looks right before a word of page content exists.
  function heroHTML() {
    if (PAGE_HERO) return "";
    var t = DOC.tool;
    var flag = t.status ? '<span class="a-flag">' + esc(t.status) + "</span>" : "";
    return '<div class="a-hero"><div class="a-eyebrow">Tool' + flag + "</div>" +
      '<h1 class="a-title">' + esc(t.heroTitle || t.name || "") + "</h1>" +
      (t.heroSubtitle ? '<p class="a-sub">' + esc(t.heroSubtitle) + "</p>" : "") +
      (slot("jayms-tool-dots") ? "" : '<div class="a-dots">' + dotsHTML() + "</div>") +
      "</div>";
  }

  // The page's featured image. Drop a #jayms-tool-image div anywhere to
  // place it yourself; otherwise it goes at the top of the hero, so simply
  // setting a featured image in WordPress is enough.
  function paintFeatured() {
    if (!FEATURED) return;
    var s = slot("jayms-tool-image");
    if (s) {
      if (!s.innerHTML.trim()) s.innerHTML = FEATURED;
      return;
    }
    if (PAGE_HERO && !PAGE_HERO.querySelector(".jayms-tool-featured")) {
      PAGE_HERO.insertAdjacentHTML("afterbegin", FEATURED);
    }
  }

  // Fill whatever slots the page placed, and hide the page hero while an
  // entry is open so a detail screen is not pushed down by it.
  function paintSlots(countText) {
    var d = slot("jayms-tool-dots");
    if (d) d.innerHTML = dotsHTML();
    var c = slot("jayms-tool-count");
    if (c) c.textContent = countText || "";
    if (PAGE_HERO) PAGE_HERO.hidden = (state.screen === "detail");
  }

  // data-layout="list" gives one full-width box per row; "grid" (the
  // default) fills the screen. data-columns pins a grid to 2, 3 or 4,
  // and both collapse to a single column on a phone regardless.
  function cardsClass() {
    var c = "";
    // A row holds two things: what the entry is, and what it says. Side by
    // side suits a short label like a reference; stacked suits a label that
    // is itself a sentence, which a narrow column would set as a ladder.
    if (PAGE.layout === "rail") {
      return " lay-rail" + (PAGE.row === "stack" ? " row-stack" : "");
    }
    if (PAGE.layout === "list") return " lay-list";
    if (["2", "3", "4"].indexOf(PAGE.columns) !== -1) c = " cols-" + PAGE.columns;
    return c;
  }

  function jsArg(v) { return JSON.stringify(v).replace(/"/g, "&quot;"); }

  // One option, shared by both displays: colour dot, label, count, and
  // dimmed-not-hidden when it would return nothing, so the list never jumps.
  function optionChip(row, o, sel) {
    var n = optionCount(row, o.k);
    var dead = n === 0 && sel !== o.k;
    return '<button class="a-chip' + (sel === o.k ? " on" : "") + '"' +
      (dead ? " disabled" : "") +
      ' onclick="jaymsAlphaFilter(' + jsArg(row.field) + "," + jsArg(o.k) + ')">' +
      (o.c ? '<span class="a-cdot" style="background:' + colourVar(o.c) + '"></span>' : "") +
      esc(o.n) + '<span class="a-n">' + n + "</span></button>";
  }

  function allChip(row, sel) {
    return '<button class="a-chip' + (sel ? "" : " on") + '" onclick="jaymsAlphaFilter(' +
      jsArg(row.field) + ',null)">' +
      (row.role === "category" ? '<span class="a-cdot" style="background:var(--a-ink)"></span>' : "") +
      esc(row.allLabel || "All") + "</button>";
  }

  function filtersHTML() {
    var rows = visibleFilters();
    if (!rows.length) return "";

    var out = rows.map(function (row) {
      var sel = state.filters[row.field] || null;
      var opts = [allChip(row, sel)].concat(row.options.map(function (o) {
        return optionChip(row, o, sel);
      })).join("");

      if (rowDisplay(row) === "chips") {
        return '<div class="a-row">' + opts + "</div>";
      }

      // collapsed: one line showing the current value, options on tap
      var chosen = sel ? row.options.filter(function (o) { return o.k === sel; })[0] : null;
      var open = !!openRows[row.field];
      return '<div class="a-frow">' +
        '<button class="a-fctl' + (sel ? " on" : "") + '" aria-expanded="' + open + '"' +
        ' onclick="jaymsAlphaToggleRow(' + jsArg(row.field) + ')">' +
        '<span class="a-fval">' +
          (chosen && chosen.c ? '<span class="a-cdot" style="background:' + colourVar(chosen.c) + '"></span>' : "") +
          "<span>" + esc(chosen ? chosen.n : (row.allLabel || "All")) + "</span></span>" +
        '<span class="a-fcar">▾</span></button>' +
        '<div class="a-fpanel"' + (open ? "" : " hidden") + ">" + opts + "</div></div>";
    }).join("");

    return '<div class="a-filters">' + out + "</div>" + activeHTML(rows);
  }

  // Every active filter stays visible as a removable chip, so nothing is
  // ever hidden inside a row you have collapsed.
  function activeHTML(rows) {
    var on = rows.filter(function (r) { return state.filters[r.field]; });
    if (!on.length && !state.q) return "";
    var chips = on.map(function (r) {
      var o = r.options.filter(function (x) { return x.k === state.filters[r.field]; })[0];
      return '<button class="a-achip" onclick="jaymsAlphaFilter(' + jsArg(r.field) + ',null)">' +
        esc(o ? o.n : state.filters[r.field]) + '<span class="x">×</span></button>';
    });
    if (state.q) {
      chips.push('<button class="a-achip" onclick="jaymsAlphaClearSearch()">' +
        "“" + esc(state.q) + "”" + '<span class="x">×</span></button>');
    }
    return '<div class="a-active"><span class="a-alabel">Filtering by</span>' + chips.join("") +
      (chips.length > 1 ? '<button class="a-clear" onclick="jaymsAlphaClearAll()">Clear all</button>' : "") +
      "</div>";
  }

  function cardHTML(d) {
    var c = catOf(d.category);
    var card = DOC.card || {};
    var labelField = PAGE.label || card.labelField;
    var label = labelField ? d[labelField] : "";
    var main = d[PAGE.main || card.mainField || "summary"];
    // in the rail the group is the heading above the row, so repeating it
    // on every row says nothing
    var railField = (PAGE.layout === "rail" && railRow()) ? railRow().field : null;
    var tags = visibleFilters().filter(function (r) {
      // The page decides which rows become tags. Failing that, a row may
      // be worth filtering by and not worth repeating on every row: the
      // category is already the badge, and the rail is the heading above.
      if (PAGE.tags) {
        return PAGE.tags.indexOf("none") === -1 && PAGE.tags.indexOf(r.field) > -1;
      }
      return r.role !== "category" && r.field !== railField && r.tag !== false;
    })
      .map(function (r) {
        var o = r.options.filter(function (x) { return x.k === d[r.field]; })[0];
        // a row tag has a column to fit; the chip above it has the sentence
        return o ? '<span class="a-tag">' + esc(o.short || o.n) + "</span>" : "";
      }).join("");
    var done = PAGE.progress && isDone(d.id);
    var tick = PAGE.progress
      ? '<button class="a-tick' + (done ? " on" : "") + '" title="' +
        (done ? "Done, tap to clear" : "Mark as done") + '" aria-pressed="' + (!!done) +
        '" onclick="jaymsAlphaToggleDone(' + jsArg(d.id) + ')">\u2713</button>'
      : "";

    return '<div class="a-cardwrap' + (done ? " done" : "") + '">' +
      '<button class="a-card" style="--cc:' + colourVar(c && c.c) + '" onclick="jaymsAlphaGo({screen:\'detail\',id:' +
      jsArg(d.id) + '})">' +
      (label ? '<div class="a-clabel">' + esc(label) + "</div>" : "") +
      '<div class="a-cmain">' + esc(main) + "</div>" +
      '<div class="a-meta">' + tags +
      // the row's own edge already carries the category; a tool whose
      // categories are obvious from the colour can drop the word
      // the badge repeats on every row, so it takes the short form too;
      // the chip above the list carries the whole phrase and the count
      (!PAGE.badge || card.badge === false ? "" :
        '<span class="a-badge" style="--cc:' + colourVar(c && c.c) + '">' +
        esc(c ? (c.short || c.n) : d.category) + "</span>") +
      "</div></button>" + tick + "</div>";
  }

  // The suggest box comes out of the footer and goes on the count row.
  // Under three closing notes nobody ever found it.
  function footerParts(want) {
    var parts = (DOC.footer || []).map(function (f) {
      if (f.type === "note") {
        if (want !== "notes") return "";
        return '<div class="a-note ' + esc(f.class || "") + '">' + esc(f.text) + "</div>";
      }
      if (want === "notes") return "";
      if (f.type === "suggest" && typeof jaymsSuggest !== "undefined" && jaymsSuggest.boxHTML) {
        try {
          // Without this the shared box falls back to the generic kind
          // 'suggestion' and the submission arrives with no tool on it.
          // Every live tool currently misses this; the alphas do not.
          if (jaymsSuggest.registerKind) {
            jaymsSuggest.registerKind(f.kind, f.label || f.kind);
          }
          return '<div class="a-note">' + jaymsSuggest.boxHTML(f.kind, {
            toggleText: f.toggleText, valuePlaceholder: f.valuePlaceholder
          }) + "</div>";
        } catch (e) { return ""; }
      }
      return "";
    }).join("");
    return parts;
  }

  function footerHTML() {
    var parts = footerParts("notes");
    return parts ? '<div class="a-foot">' + parts + "</div>" : "";
  }

  function suggestHTML() {
    // what it asks for is the tool's own, whether to ask at all is the
    // page's: data-suggest="off"
    if (!PAGE.suggest) return "";
    var parts = footerParts("suggest");
    return parts ? '<div class="a-suggest">' + parts + "</div>" : "";
  }

  // ------------------------------------------------------- the rail
  // A layout for the long sets. The groups stand still on the left while
  // the entries scroll past on the right, so a reader keeps their place in
  // two hundred rows the way they do in a fifty-movement book outline.
  // Everything is shown: a rail and a pager answer the same question, and
  // the rail answers it better.

  function railRow() {
    // a row marked railOnly exists for this and nothing else, so it wins
    // even though it never appears among the chips
    var only = (DOC.filters || []).filter(function (r) { return r.railOnly; })[0];
    if (only) return only;
    var vis = visibleFilters();
    var byRole = vis.filter(function (r) { return r.role === "group"; })[0];
    if (byRole) return byRole;
    var notCat = vis.filter(function (r) { return r.role !== "category"; })[0];
    return notCat || catRow();
  }

  function railTitle(row) {
    var t = String(row.allLabel || row.role || "Groups").replace(/^all\s+/i, "");
    return t.charAt(0).toUpperCase() + t.slice(1);
  }

  // The rail indexes the whole filtered set, the body shows one page of it.
  // At thirty-three entries that is every row on one page and the rail is a
  // jump list; at three thousand it is still a jump list, and a click on a
  // group that is not on this page turns to the page it starts on first.
  function groupKey(o) {
    return "ag-" + String(o).replace(/[^A-Za-z0-9]+/g, "_");
  }

  function railGroups(rows, r) {
  // While someone is searching, the rail’s sections would re-sort the
  // answers back into alphabetical order and bury the best one several
  // headings down: typing "shalom" put abishalom above it. A search is
  // one list, in the order the search scored it.
  if (String(state.q == null ? "" : state.q).trim()) {
    return [{ key: "__found", name: "Best matches", rows: rows }];
  }
  return railGroupsBase(rows, r);
}
function railGroupsBase(rows, r) {
    var groups = [];
    r.options.forEach(function (o) {
      var mine = rows.filter(function (d) { return d[r.field] === o.k; });
      if (mine.length) groups.push({ key: groupKey(o.k), name: o.n, rows: mine });
    });
    var loose = rows.filter(function (d) {
      for (var i = 0; i < r.options.length; i++) if (r.options[i].k === d[r.field]) return false;
      return true;
    });
    if (loose.length) groups.push({ key: groupKey("other"), name: "Everything else", rows: loose });
    return groups;
  }

  function railHTML(rows, slice, per) {
    var r = railRow();
    if (!rows.length) {
      return '<p class="a-empty">Nothing matches. Clear a filter and try again.</p>';
    }
    // The rail stays put while you type. Pulling it out mid-search reflows
    // the whole screen under the reader's eyes, which costs more than the
    // ordering gains: the ranking still decides which rows reach the page.
    if (!r) return '<div class="a-cards' + cardsClass() + '">' + slice.map(cardHTML).join("") + "</div>";

    var all = railGroups(rows, r);
    var at = 0;
    all.forEach(function (g) { g.page = Math.floor(at / per) + 1; at += g.rows.length; });

    var here = railGroups(slice, r);

    var nav = '<nav class="a-rail" aria-label="' + esc(railTitle(r)) + '">' +
      '<p class="a-raillabel">' + esc(railTitle(r)) + '</p><div class="a-raillist">' +
      all.map(function (g, i) {
        return '<a href="#' + g.key + '"' +
          (i === 0 ? ' class="on" aria-current="true"' : "") +
          ' onclick="return jaymsAlphaRail(' + g.page + ',&quot;' + g.key + '&quot;)"' +
          '><span>' + esc(g.name) + '</span><span>' + g.rows.length + '</span></a>';
      }).join("") + '</div></nav>';

    var body = here.map(function (g) {
      return '<section class="a-gsec" id="' + g.key + '">' +
        '<div class="a-ghead"><h2 class="a-gh">' + esc(g.name) + '</h2>' +
        '<span class="a-gn">' + g.rows.length + ' entr' + (g.rows.length === 1 ? 'y' : 'ies') +
        '</span></div><div class="a-cards' + cardsClass() + '">' + g.rows.map(cardHTML).join("") +
        '</div></section>';
    }).join("");

    return '<div class="a-railwrap">' + nav + '<div class="a-groups">' + body + '</div></div>';
  }

  // a group already on this page is an ordinary in-page jump; one that is
  // not turns the page first and then goes to it
  window.jaymsAlphaRail = function (page, id) {
    if (document.getElementById(id)) return true;
    state.page = page;
    render();
    var el = document.getElementById(id);
    if (el) el.scrollIntoView();
    return false;
  };

  var railWatch = null;

  function markGroup(id) {
    var links = MOUNT.querySelectorAll(".a-rail a");
    for (var i = 0; i < links.length; i++) {
      var on = links[i].getAttribute("href") === "#" + id;
      links[i].classList.toggle("on", on);
      if (on) {
        links[i].setAttribute("aria-current", "true");
        var list = links[i].parentNode, top = links[i].offsetTop;
        if (list && (top < list.scrollTop || top > list.scrollTop + list.clientHeight - 40)) {
          list.scrollTop = top - list.clientHeight / 2;
        }
      } else {
        links[i].removeAttribute("aria-current");
      }
    }
  }

  // measured on a timer, not queued on a frame: a background tab is given
  // no frames and the rail would sit frozen on the first group
  function wireRail() {
    if (railWatch) { window.removeEventListener("scroll", railWatch); railWatch = null; }
    if (PAGE.layout !== "rail" || state.screen === "detail") return;
    var secs = [].slice.call(MOUNT.querySelectorAll(".a-gsec"));
    if (!secs.length) return;

    var links = MOUNT.querySelectorAll(".a-rail a");
    for (var i = 0; i < links.length; i++) {
      (function (a) {
        a.addEventListener("click", function (e) {
          e.preventDefault();
          var id = a.getAttribute("href").slice(1);
          var n = document.getElementById(id);
          if (n) n.scrollIntoView({ block: "start" });
          markGroup(id);
        });
      })(links[i]);
    }

    var last = 0;
    railWatch = function () {
      var now = Date.now();
      if (now - last < 80) return;
      last = now;
      var best = secs[0], bestTop = -Infinity;
      for (var j = 0; j < secs.length; j++) {
        var t = secs[j].getBoundingClientRect().top;
        if (t <= 140 && t > bestTop) { bestTop = t; best = secs[j]; }
      }
      markGroup(best.id);
    };
    window.addEventListener("scroll", railWatch, { passive: true });
    railWatch();
  }

  function renderBrowse() {
    var rows = ENTRIES.filter(function (d) { return matches(d); });
    // a search puts the closest names first; sort is stable, so rows that
    // score the same keep the order the dataset gave them
    if (state.q) {
      rows = rows.map(function (d) { return { d: d, s: searchScore(d) }; })
        .sort(function (a, b) { return b.s - a.s; })
        .map(function (x) { return x.d; });
    }
    var RAIL = PAGE.layout === "rail";
    var per = PAGE.perPage || (DOC.tool && DOC.tool.perPage) || 20;
    var pages = Math.max(1, Math.ceil(rows.length / per));
    if (state.page > pages) state.page = pages;
    var slice = rows.slice((state.page - 1) * per, state.page * per);
    var filtered = state.q || Object.keys(state.filters).length;
    var countText = rows.length + " of " + ENTRIES.length + (filtered ? " · filtered" : "");
    var nDone = PAGE.progress ? doneCount() : 0;
    if (nDone) countText += " · " + nDone + " done";
    lastCount = countText;

    return heroHTML() +
      (PAGE.search ? '<input class="a-search" id="jaymsAlphaSearch" type="search" placeholder="' +
        esc((DOC.tool && DOC.tool.searchPlaceholder) || "Search…") +
        '" value="' + esc(state.q) + '">' : "") +
      filtersHTML() +
      '<div class="a-countrow">' +
      (slot("jayms-tool-count") ? "" : '<div class="a-count">' + esc(countText) +
        (nDone ? '<button class="a-reset" onclick="jaymsAlphaResetProgress()">reset</button>' : "") +
        "</div>") + suggestHTML() + "</div>" +
      (RAIL ? railHTML(rows, slice, per) :
        '<div class="a-cards' + cardsClass() + '">' + (slice.map(cardHTML).join("") ||
        '<p class="a-empty">Nothing matches. Clear a filter and try again.</p>') + "</div>") +
      (pages > 1 ? '<div class="a-pg">' +
        '<button class="a-pgb"' + (state.page <= 1 ? " disabled" : "") +
          ' onclick="jaymsAlphaGo({page:' + (state.page - 1) + '})">Prev</button>' +
        '<span class="a-pglabel">Page ' + state.page + " of " + pages + "</span>" +
        '<button class="a-pgb"' + (state.page >= pages ? " disabled" : "") +
          ' onclick="jaymsAlphaGo({page:' + (state.page + 1) + '})">Next</button></div>' : "") +
      footerHTML();
  }

  // ------------------------------------------------------------ blocks

  function wsLinkify(h) {
  // A Strong’s number is a doorway wherever it is printed, not only in
  // the lexicon block. Skip anything that already holds a link, so the
  // lexicon block’s own anchors are never nested, and walk the text
  // between tags so a number sitting right after a tag still counts.
  var u = DOC && DOC.wordStudy && DOC.wordStudy.url;
  if (!u || !h || String(h).indexOf("<a") >= 0) return h;
  return String(h).split(/(<[^>]*>)/).map(function (part, i) {
    if (i % 2) return part;                    // the tags themselves
    return part.replace(/(^|[^A-Za-z0-9])([GH])(\d{1,5})(?![0-9])/g,
      function (all, pre, letter, num) {
        var id = (letter + num).toLowerCase();
        return pre + '<a class="a-wsnum" href="' + esc(u.replace("{n}", id)) +
               '">' + letter + num + "</a>";
      });
  }).join("");
}
function box(variant, label, bodyHTML, noteHTML) {
  bodyHTML = wsLinkify(bodyHTML);
  noteHTML = wsLinkify(noteHTML);
  return boxBase(variant, label, bodyHTML, noteHTML);
}
function boxBase(variant, label, bodyHTML, noteHTML) {
    return '<div class="a-box v-' + esc(variant || "gold") + '">' +
      '<div class="a-blabel">' + label + "</div>" +
      '<div class="a-btext">' + bodyHTML + "</div>" +
      (noteHTML ? '<div class="a-bnote">' + noteHTML + "</div>" : "") + "</div>";
  }

  function blockTitle(b, d) {
    var t = esc(b.title || "");
    if (b.titleSuffixField && d[b.titleSuffixField]) t += " &middot; " + esc(d[b.titleSuffixField]);
    if (b.subtitleField && d[b.subtitleField]) t += ": " + esc(d[b.subtitleField]);
    return t;
  }

  // "Exodus 4:27, 4:28, 4:29, 4:30" is one place in the text, not four.
  // A run of verses in the same chapter folds into a range, and a gap only
  // breaks the run when three or more verses are missing: fewer than that
  // and it is still the same passage with a verse or two skipped. Anything
  // that is not a plain book chapter:verse is left exactly as it was.
  function collapseRefs(list) {
    var out = [], run = null;

    function flush() {
      if (!run) return;
      out.push(run.from === run.to
        ? run.book + " " + run.chapter + ":" + run.from
        : run.book + " " + run.chapter + ":" + run.from + "-" + run.to);
      run = null;
    }

    (list || []).forEach(function (raw) {
      var m = String(raw).match(/^(.+?)\s+(\d+):(\d+)$/);
      if (!m) { flush(); out.push(String(raw)); return; }
      var book = m[1], chapter = m[2], v = parseInt(m[3], 10);
      if (run && run.book === book && run.chapter === chapter &&
          v > run.to && v - run.to <= 3) {
        run.to = v;
        return;
      }
      flush();
      run = { book: book, chapter: chapter, from: v, to: v };
    });
    flush();
    return out;
  }

  // ------------------------------------------------------------ lexicon

  // A Strong's number on its own tells a reader nothing. The entry behind
  // it does, so a tool that names a lexicon source gets the word, how it is
  // said, what it means, and how the King James rendered it.
  var lexStore = null;

  function loadLexicon() {
    if (!lexStore && DOC.lexicon && DOC.lexicon.src) {
      lexStore = fetch(DOC.lexicon.src)
        .then(function (r) { return r.ok ? r.json() : null; })
        .catch(function () { return null; });
    }
    return lexStore || Promise.resolve(null);
  }

  function wsNum(n){
  // A Strong’s number is a doorway: the tool that owns it is named by the
  // dataset, never by this file.
  var u = DOC && DOC.wordStudy && DOC.wordStudy.url;
  if (!u) return "<b>" + n + "</b>";
  var id = String(n).toLowerCase().replace(/[^a-z0-9]/g, "");
  return '<a class="a-wsnum" href="' + esc(u.replace("{n}", id)) + '"><b>' + n + "</b></a>";
}
function wsApparatus(list) {
  // Everything the lexicon knows about a word, set out the way a lexicon
  // sets it out: how it parses, where it came from, then the full entry
  // with its senses indented, then who is being quoted. A tool whose
  // lexicon file carries none of this prints nothing extra.
  var out = "";
  list.forEach(function (x) {
    var r = x && (x.e || x);
    if (!r) return;
    var bits = "";
    var rows = [];
    if (r.pos) rows.push(["Part of speech", r.pos]);
    (r.parse || []).forEach(function (p) {
      rows.push([p.f.charAt(0).toUpperCase() + p.f.slice(1),
                 (p.v || []).map(function (o) { return o.n; }).join(", ")]);
    });
    if (rows.length) {
      bits += '<span class="a-lexparse">' + rows.map(function (kv) {
        return '<span class="a-lexrow"><span class="a-lexk">' + esc(kv[0]) +
               '</span><span class="a-lexv">' + esc(kv[1]) + "</span></span>";
      }).join("") + "</span>";
    }
    if (r.derivation) bits += '<span class="a-lexderiv">' + esc(r.derivation) + "</span>";
    if (r.senses && r.senses.length) {
      bits += '<span class="a-lexfull">' + r.senses.map(function (sn) {
        return '<span class="a-lexp" data-d="' + (sn.d || 0) + '">' +
               esc(sn.t).replace(/\n/g, "<br>") + "</span>";
      }).join("") + "</span>";
    }
    if (r.source) {
      bits += '<span class="a-lexsrc">' + esc(r.source) +
              (r.page ? ", page " + esc(r.page) : "") + "</span>";
    }
    out += bits;
  });
  return out;
}
function fillLexicon() {
    var slots = MOUNT.querySelectorAll("[data-lex]");
    if (!slots.length) return;
    loadLexicon().then(function (lex) {
      Array.prototype.forEach.call(slots, function (el) {
        var keys = el.getAttribute("data-lex").split(/[^A-Za-z0-9]+/)
          .map(function (s) { return s.trim(); }).filter(Boolean);
        var got = keys.map(function (k) { return { k: k, e: lex ? lex[k] : null }; })
          .filter(function (x) { return x.e; });
        // no lexicon declared, or nothing matched: show what the entry
        // actually said rather than leaving "Loading" on screen for ever
        if (!got.length) {
          el.classList.remove("a-loading");
          // the field as the entry wrote it -- keys are stripped to ASCII
          // for lookup and a Hebrew or Greek lemma would not survive that
          el.textContent = el.getAttribute("data-lex");
          return;
        }
        el.classList.remove("a-loading");
        el.innerHTML = got.map(function (x) {
          var head = wsNum(esc(x.k));
          if (x.e.lemma) head += " &middot; " + esc(x.e.lemma);
          if (x.e.xlit)  head += " &middot; <i>" + esc(x.e.xlit) + "</i>";
          return head + (x.e.def ? "<br>" + esc(x.e.def) : "");
        }).join("</p><p>") + wsApparatus(got);
        // a Hebrew word and its Greek counterpart usually carry the same
        // King James rendering, and saying "Aaron; Aaron" helps nobody
        var kjv = [];
        got.forEach(function (x) {
          var v = String(x.e.kjv || "").replace(/\.\s*$/, "").trim();
          if (v && kjv.indexOf(v) === -1) kjv.push(v);
        });
        if (kjv.length) {
          citeInto(el, "Rendered in the King James as " + esc(kjv.join("; ")) + ".");
        }
      });
    });
  }

  // ---------------------------------------------------------- map, photo

  // Leaflet is fetched only when a screen actually shows a map, because
  // most entries are people and will never need it.
  var leafletReady = null;

  function loadLeaflet() {
    if (leafletReady) return leafletReady;
    var m = (DOC.map || {});
    var css = m.css || "https://unpkg.com/leaflet@1.9.4/dist/leaflet.css";
    var js  = m.js  || "https://unpkg.com/leaflet@1.9.4/dist/leaflet.js";
    leafletReady = new Promise(function (done, fail) {
      if (window.L) return done(window.L);
      var link = document.createElement("link");
      link.rel = "stylesheet"; link.href = css;
      document.head.appendChild(link);
      var s = document.createElement("script");
      s.src = js;
      s.onload = function () { done(window.L); };
      s.onerror = function () { fail(new Error("leaflet")); };
      document.head.appendChild(s);
    }).catch(function () { return null; });
    return leafletReady;
  }

  function fillMaps() {
    var slots = MOUNT.querySelectorAll("[data-map]");
    if (!slots.length) return;
    loadLeaflet().then(function (L) {
      Array.prototype.forEach.call(slots, function (el) {
        if (!L) { el.innerHTML = '<p class="a-loading">The map could not load.</p>'; return; }
        if (el.dataset.drawn) return;
        el.dataset.drawn = "1";
        var at = el.getAttribute("data-map").split(",");
        var lat = parseFloat(at[0]), lon = parseFloat(at[1]);
        if (isNaN(lat) || isNaN(lon)) return;
        var m = (DOC.map || {});
        var map = L.map(el, { scrollWheelZoom: false, attributionControl: true })
          .setView([lat, lon], parseInt(el.getAttribute("data-zoom"), 10) || 8);
        L.tileLayer(m.tiles || "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
          attribution: m.attribution ||
            '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
          maxZoom: 17
        }).addTo(map);
        L.marker([lat, lon]).addTo(map);
      });
    });
  }

  function renderBlock(b, d) {
    if (b.type === "scripture") return scriptureHTML(b, d);

    // where a place is, for the entries that are places
    if (b.type === "map") {
      var g = d[b.field];
      if (!g || typeof g.lat !== "number" || typeof g.lon !== "number") return "";
      return '<div class="a-box v-' + esc(b.variant || "witness") + '">' +
        '<div class="a-blabel">' + blockTitle(b, d) + "</div>" +
        '<div class="a-map" data-map="' + esc(g.lat + "," + g.lon) +
        '" data-zoom="' + esc(String(b.zoom || 8)) + '"></div>' +
        '<div class="a-bnote">' + esc(g.lat.toFixed(4)) + ", " +
        esc(g.lon.toFixed(4)) + "</div></div>";
    }

    // a picture of it, with the credit the licence requires
    if (b.type === "photo") {
      var p = d[b.field];
      if (!p || !p.imageUrl) return "";
      var credit = [];
      if (p.credit) {
        credit.push(p.creditUrl
          ? '<a href="' + esc(p.creditUrl) + '" target="_blank" rel="noopener">' +
            esc(p.credit) + "</a>"
          : esc(p.credit));
      }
      if (p.license) credit.push(esc(p.license));
      return '<div class="a-box v-' + esc(b.variant || "apparatus") + '">' +
        '<div class="a-blabel">' + blockTitle(b, d) + "</div>" +
        '<img class="a-photo" src="' + esc(p.imageUrl) + '" alt="' +
        esc(p.description || d.title || "") + '" loading="lazy">' +
        (p.description ? '<div class="a-btext"><p>' + esc(p.description) + "</p></div>" : "") +
        (credit.length ? '<div class="a-bnote">' + credit.join(" &middot; ") + "</div>" : "") +
        "</div>";
    }

    if (b.type === "lexicon") {
      var nums = d[b.field];
      nums = Array.isArray(nums) ? nums.join(" ") : (nums || "");
      if (!nums) return "";
      return '<div class="a-box v-' + esc(b.variant || "wording") + '">' +
        '<div class="a-blabel">' + blockTitle(b, d) + "</div>" +
        '<div class="a-btext"><p class="a-loading" data-lex="' + esc(nums) +
        '">Loading…</p></div></div>';
    }

    // A list of references, each one a way into the passage rather than a
    // string to squint at. Any tool with a field of references gets this.
    if (b.type === "out") {
      // Links out to another tool. `linkrow` moves about inside one tool;
      // this one carries its own addresses. Where an item names a reference
      // it also gets the version buttons, because the whole point of the
      // tool it links to is that the versions disagree.
      var outs = d[b.field] || [];
      if (!outs.length) return "";
      var vs = blockVersions(b);
      var dv = pickVersion(vs, b.default);
      return '<div class="a-box v-' + esc(b.variant || "wording") + '">' +
        '<div class="a-blabel">' + blockTitle(b, d) + "</div>" +
        outs.map(function (o) {
          var head = '<a class="a-out-head" href="' + esc(o.u) + '">' +
            '<span class="a-outn">' + esc(o.n) + "</span>" +
            (o.s ? '<span class="a-outs">' + esc(o.s) + "</span>" : "") + "</a>";
          // What the other tool actually says, so a reader does not have to
          // leave to find out whether it is worth leaving for.
          var body = (o.b || []).map(function (sec) {
            return '<div class="a-outsec"><div class="a-outlab">' + esc(sec.l) +
                   "</div>" + rich(sec.t) + "</div>";
          }).join("");
          if (o.src && o.src.length) {
            body += '<div class="a-outsec"><div class="a-outlab">Sources</div><ol class="a-outsrc">' +
              o.src.map(function (x) { return "<li>" + esc(x) + "</li>"; }).join("") +
              "</ol></div>";
          }
          var read = "";
          if (o.r) {
            var pills = vs.map(function (x) {
              return '<button class="a-vb' + (x === dv ? " on" : "") +
                     '" data-ref="' + esc(o.r) + '" data-v="' + esc(x) +
                     '" onclick="jaymsAlphaOutVersion(this)">' + esc(x.toUpperCase()) + "</button>";
            }).join("");
            read = '<div class="a-vsw">' + pills + "</div>" +
              '<div class="a-box v-scripture"><div class="a-blabel">' + esc(o.r) +
              " \u00b7 " + esc(dv.toUpperCase()) +
              '</div><div class="a-btext a-loading">Loading\u2026</div></div>';
          }
          return '<div class="a-out">' + head + body + read + "</div>";
        }).join("") + "</div>";
    }
    if (b.type === "refs") {
      var list = collapseRefs(d[b.field] || []);
      if (!list.length) return "";
      // a book to a line: two hundred references in one paragraph is a wall,
      // and the eye needs the book to change somewhere it can see
      var books = [], byBook = {}, lastBook = null;
      list.forEach(function (ref) {
        var m = String(ref).match(/^(.+?)\s+\d/);
        // A reference that lost its book belongs to the book above it:
        // after "Exodus 28:17-20", "39:10-13" is still Exodus. A value with
        // no number in it is not a reference at all, so it is not a chip and
        // does not invent a book of its own.
        var book;
        if (m) { book = m[1]; lastBook = book; }
        else if (/\d/.test(String(ref)) && lastBook) { book = lastBook; }
        else { return; }
        if (!byBook[book]) { byBook[book] = []; books.push(book); }
        byBook[book].push(ref);
      });
      var rows = books.map(function (book) {
        var chips = byBook[book].map(function (ref) {
          // the book is said once at the head of its line, so the chip
          // carries only what changes: 4:14, 4:27-30, 5:1-4
          var where = String(ref).slice(book.length).trim() || ref;
          var u = typeof window.jaymsInterlinearURL === "function"
            ? window.jaymsInterlinearURL(ref) : null;
          return u ? '<a class="a-lnk" href="' + esc(u) + '">' + esc(where) + "</a>"
                   : '<span class="a-lnk">' + esc(where) + "</span>";
        }).join("");
        return '<div class="a-linkrow">' +
          '<span class="a-reflabel">' + esc(book) + "</span>"  + chips + "</div>";
      }).join("");
      return '<div class="a-box v-' + esc(b.variant || "apparatus") + '">' +
        '<div class="a-blabel">' + blockTitle(b, d) + '<span class="a-bcount">' + (rows.match(/class="a-lnk"/g) || []).length + "</span>" + "</div>" + rows + "</div>";
    }

    if (b.type === "linkrow") {
      var items = d[b.field] || [];
      if (!items.length) return "";
      var btns = items.map(function (id) {
        var t = BY_ID[id];
        if (!t) return "";
        return '<button class="a-lnk" onclick="jaymsAlphaGo({screen:\'detail\',id:' +
          JSON.stringify(id).replace(/"/g, "&quot;") + '})">' + esc(t.title) + "</button>";
      }).join("");
      if (!btns) return "";
      return '<div class="a-box v-apparatus"><div class="a-blabel">' + esc(b.title) +
        '</div><div class="a-linkrow">' + btns + "</div></div>";
    }

    if (b.type === "credit") {
      var lines = (b.lines || []).map(function (l) {
        return d[l.field] ? esc(l.prefix) + esc(d[l.field]) : "";
      }).filter(Boolean).join("<br>");
      var link = "";
      if (b.link) {
        var p = d[b.link.field];
        link = (p && p.url)
          ? '<a href="' + esc(p.url) + '">' + esc(p.title || p.url) + " &rarr;</a>"
          : esc(b.link.empty || "");
      }
      if (!lines && !link) return "";
      return '<div class="a-credit">' + lines + (lines && link ? "<br>" : "") + link + "</div>";
    }

    // box
    // a box may carry prose, a quoted passage, or both; it only disappears
    // when it would have nothing at all in it
    var v = b.field ? d[b.field] : null;
    var q = b.quoteField ? d[b.quoteField] : null;
    var hasV = v && (!Array.isArray(v) || v.length);
    var hasQ = q && (!Array.isArray(q) || q.length);
    if (!hasV && !hasQ) return "";
    var body = "";
    if (hasV && Array.isArray(v)) {
      var tag = b.list === "ordered" ? "ol" : "ul";
      body = "<" + tag + ">" + v.map(function (x) { return "<li>" + esc(x) + "</li>"; }).join("") + "</" + tag + ">";
    } else if (hasV) {
      body = b.quote ? "<p>&ldquo;" + esc(v) + "&rdquo;</p>" : rich(v);
    }
    if (hasQ) body += witnessSlot(q);
    var note = b.noteField && d[b.noteField] ? esc(d[b.noteField]) : "";
    return box(b.variant, blockTitle(b, d), body, note);
  }

  // ------------------------------------------------------- witness texts

  // A citation on its own asks the reader to take the entry's word for it.
  // The passage itself is public domain and already on the site, so it is
  // quoted here instead, in the same translation the Book of the Watchers
  // post and the Fact Book reader use, so the three read alike.
  var WITNESS_SRC = "https://raw.githubusercontent.com/sixcore-droid/jayms-tool-data/main/second-temple/1-enoch-charles-1917.json";
  var WITNESS_LABEL = "R.H. Charles, 1917";
  var FACTBOOK = "https://jayms.com/bible-study-tools-2/bible-entity-explorer/";
  var witnessBook = null;

  function loadWitness() {
    if (!witnessBook) {
      witnessBook = fetch(WITNESS_SRC)
        .then(function (r) { return r.ok ? r.json() : null; })
        .catch(function () { return null; });
    }
    return witnessBook;
  }

  // "1 Enoch 6:1-2" -> {chapter: 6, from: 1, to: 2}. Anything else is a work
  // this store does not carry, and the slot simply does not appear.
  function witnessRef(ref) {
    var m = String(ref || "").match(/^1 Enoch\s+(\d+):(\d+)(?:\s*[-–]\s*(\d+))?$/);
    if (!m) return null;
    return { chapter: m[1], from: +m[2], to: m[3] ? +m[3] : +m[2] };
  }

  // Where a witness text can be read in full. The runner serves it itself
  // when the tool's data declares a reading source; otherwise the link goes
  // to the Fact Book, which is where the reader used to live.
  function chapterURL(chapter, book) {
    book = book || "1 Enoch";
    if (DOC.reading && DOC.reading.src) {
      return location.pathname + keptQuery() +
        "#read=" + encodeURIComponent(book) + "|" + encodeURIComponent(chapter);
    }
    // the Fact Book is the reader for every tool that does not carry its
    // own, and it reads the same fragment this runner writes
    return FACTBOOK + "#read=" + encodeURIComponent(book) + "|" + encodeURIComponent(chapter);
  }

  // filled once the text is in; the slot carries its own reference so a
  // re-render does not have to be coordinated with the fetch
  // Two shapes, one renderer. A tool that stores only the reference has the
  // text fetched for it; a tool that already holds the excerpt hands it
  // over. Both fill the same slot, so the citation lands in the same place.
  function witnessSlot(value) {
    if (Array.isArray(value)) {
      if (!value.length) return "";
      return '<p class="a-loading" data-witness-rows="' +
        esc(JSON.stringify(value)) + '">Loading…</p>';
    }
    return witnessRef(value)
      ? '<p class="a-loading" data-witness="' + esc(value) + '">Loading…</p>'
      : "";
  }

  // the citation belongs with the note, at the note's size, wherever it came
  // from -- see fillWitness for the fetched case
  function citeInto(el, html) {
    var wrap = el.closest(".a-box");
    var note = wrap ? wrap.querySelector(".a-bnote") : null;
    if (!note && wrap) {
      note = document.createElement("div");
      note.className = "a-bnote";
      wrap.appendChild(note);
    }
    if (!note) return;
    var cite = document.createElement("div");
    cite.innerHTML = html;
    note.insertBefore(cite, note.firstChild);
  }

  function fillWitnessRows() {
    var slots = MOUNT.querySelectorAll("[data-witness-rows]");
    Array.prototype.forEach.call(slots, function (el) {
      var rows;
      try { rows = JSON.parse(el.getAttribute("data-witness-rows")); }
      catch (e) { el.parentNode.removeChild(el); return; }
      var quoted = rows.filter(function (w) { return w && w.excerpt; });
      var shown = quoted.slice(0, 3);
      if (!shown.length) { el.parentNode.removeChild(el); return; }
      el.classList.remove("a-loading");
      el.innerHTML = shown.map(function (w) {
        return "&ldquo;" + esc(w.excerpt) + "&rdquo;";
      }).join("</p><p>");

      var ownReader = !!(DOC.reading && DOC.reading.src);
      var cites = shown.map(function (w, i) {
        var book = w.book || readBook(w.source);
        if (!w.chapter) return esc(w.source);
        // A tool that carries the texts opens the chapter underneath, where
        // the quote is: sending a reader to another screen to read four more
        // sentences loses them the entry they were reading.
        if (ownReader) {
          return esc(w.source) + ' &middot; <button class="a-lnk" ' +
            'onclick="jaymsAlphaOpenWitness(this,' + jsArg(book) + ',' +
            jsArg(String(w.chapter)) + ')">read it in full &darr;</button>';
        }
        return esc(w.source) + ' &middot; <a href="' +
          esc(chapterURL(w.chapter, book)) + '">read it in full &rarr;</a>';
      });
      var more = rows.length - shown.length;
      if (more > 0) cites.push("and " + more + " more place" + (more === 1 ? "" : "s"));
      citeInto(el, cites.join("<br>"));
    });
  }

  // opens the chapter in place, under the box it was cited in, and closes
  // again on a second press
  window.jaymsAlphaOpenWitness = function (btn, book, chapter) {
    var box = btn.closest(".a-box");
    if (!box) return;
    var open = box.querySelector(".a-witness-full");
    if (open) { open.parentNode.removeChild(open); btn.innerHTML = "read it in full &darr;"; return; }
    var holder = document.createElement("div");
    holder.className = "a-witness-full a-btext";
    holder.innerHTML = '<p class="a-loading">Loading…</p>';
    box.appendChild(holder);
    btn.innerHTML = "close &uarr;";
    loadReading().then(function (all) {
      var work = all ? all[book] : null;
      var ch = work && work.chapters ? work.chapters[chapter] : null;
      if (!ch || !ch.text) {
        holder.innerHTML = "<p>That chapter is not in this collection.</p>";
        return;
      }
      holder.innerHTML = '<p class="a-blabel">' + esc(book) + " " + esc(chapter) + "</p>" +
        String(ch.text).split(/\n\s*\n/).map(function (p) {
          return p.trim() ? "<p>" + esc(p.trim()) + "</p>" : "";
        }).join("");
    });
  };

  // "1 Enoch 12" names its book in front of the chapter
  function readBook(source) {
    var m = String(source || "").match(/^(.*?)\s+[\d.]+$/);
    return m ? m[1] : source;
  }

  function fillWitness() {
    var slots = MOUNT.querySelectorAll("[data-witness]");
    if (!slots.length) return;
    loadWitness().then(function (book) {
      Array.prototype.forEach.call(slots, function (el) {
        var ref = el.getAttribute("data-witness"), p = witnessRef(ref);
        var ch = book && p ? book[p.chapter] : null;
        if (!ch) { el.parentNode.removeChild(el); return; }
        var parts = [];
        for (var v = p.from; v <= p.to; v++) if (ch[v]) parts.push(ch[v]);
        if (!parts.length) { el.parentNode.removeChild(el); return; }
        el.classList.remove("a-loading");
        el.innerHTML = "&ldquo;" + esc(parts.join(" ")) + "&rdquo;";

        // where the passage came from is a footnote to it, not a second
        // sentence of it, so it goes in the box's note at the note's size
        var wrap = el.closest(".a-box");
        var note = wrap ? wrap.querySelector(".a-bnote") : null;
        if (!note && wrap) {
          note = document.createElement("div");
          note.className = "a-bnote";
          wrap.appendChild(note);
        }
        if (!note) return;
        // the same offer as the other witness shape: open it here when this
        // tool carries the texts, link across when it does not
        var cite = document.createElement("div");
        var how = (DOC.reading && DOC.reading.src)
          ? '<button class="a-lnk" onclick="jaymsAlphaOpenWitness(this,' +
            jsArg("1 Enoch") + ',' + jsArg(String(p.chapter)) + ')">read 1 Enoch ' +
            esc(p.chapter) + " in full &darr;</button>"
          : '<a href="' + esc(chapterURL(p.chapter)) + '">read 1 Enoch ' +
            esc(p.chapter) + " in full &rarr;</a>";
        cite.innerHTML = esc(ref) + " &middot; " + esc(WITNESS_LABEL) + " &middot; " + how;
        note.insertBefore(cite, note.firstChild);
      });
    });
  }

  // ------------------------------------------------------------ scripture

  var verseCache = {};

  var ALL_VERSIONS = ["net", "kjv", "web", "nlt", "esv"];

  function blockVersions(b) {
    return (b && b.versions) || ALL_VERSIONS;
  }

  // Which translation you read in is a preference for the whole site, not a
  // setting belonging to one tool: it lives under a single key, so a reader
  // who picks KJV here is still in KJV in the Interleaved Bible and anywhere
  // else that offers the switch. A block's own default only applies to a
  // reader who has never picked one.
  function sharedVersion() {
    try { return window.localStorage.getItem("jayms.version") || ""; }
    catch (e) { return ""; }
  }
  function setSharedVersion(v) {
    try { window.localStorage.setItem("jayms.version", v); } catch (e) {}
  }
  function pickVersion(list, fallback) {
    var v = sharedVersion();
    return (v && list.indexOf(v) > -1) ? v : (fallback || list[0]);
  }

  function refFor(b, d) {
    return d[b.overrideField || "bibleRefOverride"] || d[b.refField || "bibleRef"] || "";
  }

  function scriptureHTML(b, d) {
    var ref = refFor(b, d);
    if (!ref) return b.emptyText ? box("muted", "No biblical reference", "<p>" + esc(b.emptyText) + "</p>") : "";
    var versions = blockVersions(b);
    var def = pickVersion(versions, b.default);
    var pills = versions.map(function (v) {
      return '<button class="a-vb' + (v === def ? " on" : "") + '" data-v="' + esc(v) +
        '" onclick="jaymsAlphaVersion(' + JSON.stringify(ref).replace(/"/g, "&quot;") +
        ',&quot;' + esc(v) + '&quot;,this)">' + esc(v.toUpperCase()) + "</button>";
    }).join("");

    var links = "";
    if (b.interlinearLink !== false && typeof window.jaymsInterlinearURL === "function") {
      links = ref.split(";").map(function (p) {
        p = p.trim(); if (!p) return "";
        var u = window.jaymsInterlinearURL(p);
        // the screen this opens is the original with its glosses, so it is
        // not any translation's reading and naming one was never right
        return u ? '<a class="a-lnk" href="' + esc(u) + '" target="_blank" rel="noopener">' +
          esc(p) + " &middot; interlinear &rsaquo;</a>" : "";
      }).filter(Boolean).join("");
    }

    return '<div class="a-vsw">' + pills + "</div>" +
      '<div class="a-box v-scripture" id="jaymsAlphaVerse">' +
        '<div class="a-blabel">' + esc(ref) + " &middot; " + esc(def.toUpperCase()) + "</div>" +
        '<div class="a-btext a-loading">Loading…</div></div>' +
      (links ? '<div class="a-linkrow">' + links + "</div>" : "");
  }

  // the shared fetcher signs every verse "(ESV)", and the line above the box
  // already says which translation this is
  function stripSig(t) {
    return String(t == null ? "" : t).replace(/\s*\([A-Z]{2,6}\)\s*$/, "").trim();
  }

  // Verse numbers go in square brackets on purpose: Speechify is set to skip
  // bracketed text, so a passage is heard as prose instead of "one Then God
  // said two So God created".
  function verseNo(n) { return "<sup>[" + esc(n) + "]</sup>"; }

  // One passage, resolved exactly the way the Interleaved Bible resolves one.
  // NET answers a row per verse, so it carries its own numbering. Everything
  // else goes through the shared fetcher, and when that cannot read a range
  // -- the WEB and KJV files are keyed one verse at a time -- the range is
  // walked and stitched back together. Without the walk every ranged
  // reference came back "does not resolve this reference", which was every
  // reference in the Divine Council Index but one.
  function fetchPart(ref, version) {
    // "Jude 14-15" has to become "Jude 1:14-15" before a range can be
    // walked, and "Psalm 82:1" has to become "Psalms 82:1" before the WEB
    // and KJV files can be opened at all
    if (typeof window.jaymsNormRef === "function") ref = window.jaymsNormRef(ref);
    var key = version + "|" + ref;
    if (verseCache[key]) return Promise.resolve(verseCache[key]);

    var direct = version === "net"
      ? fetch("https://labs.bible.org/api/?passage=" + encodeURIComponent(ref) +
              "&type=json&formatting=plain")
          .then(function (r) { return r.json(); })
          .then(function (j) {
            var list = j || [];
            return list.map(function (v) {
              return (list.length > 1 ? verseNo(v.verse) : "") + esc(String(v.text).trim());
            }).join(" ");
          }).catch(function () { return ""; })
      : Promise.resolve("");

    return direct.then(function (out) {
      if (out) return out;
      if (typeof window.jaymsFetchVerse !== "function") return "";
      return window.jaymsFetchVerse(ref, version)
        .then(function (r) { return r && r.text ? esc(stripSig(r.text)) : ""; })
        .catch(function () { return ""; });
    }).then(function (out) {
      if (out) { verseCache[key] = out; return out; }
      var m = ref.match(/^(.+?)\s+(\d+):(\d+)\s*[-–]\s*(\d+)$/);
      if (!m || typeof window.jaymsFetchVerse !== "function") return "";
      var from = parseInt(m[3], 10), to = parseInt(m[4], 10);
      if (to <= from || to - from > 60) return "";
      var jobs = [];
      for (var n = from; n <= to; n++) {
        (function (n) {
          jobs.push(window.jaymsFetchVerse(m[1] + " " + m[2] + ":" + n, version)
            .then(function (r) { return r && r.text ? verseNo(n) + esc(stripSig(r.text)) : ""; })
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

  // "Jude 6; 2 Peter 2:4" is two passages under one heading, so each is
  // fetched on its own and named, and one that does not resolve does not
  // take the other down with it.
  function fetchVerse(ref, version) {
    var parts = String(ref).split(";").map(function (p) { return p.trim(); })
      .filter(Boolean);
    return Promise.all(parts.map(function (p) { return fetchPart(p, version); }))
      .then(function (out) {
        if (!out.filter(Boolean).length) return "";
        return out.map(function (html, i) {
          if (!html) return "";
          return "<p>" +
            (parts.length > 1 ? "<strong>" + esc(parts[i]) + "</strong> " : "") +
            html + "</p>";
        }).join("");
      });
  }

  async function loadVerse(ref, version, sep, boxEl) {
    boxEl = boxEl || document.getElementById("jaymsAlphaVerse");
    if (!boxEl) return;
    var textEl = boxEl.querySelector(".a-btext");
    var labelEl = boxEl.querySelector(".a-blabel");
    // the reference and the translation are this box's title, so the body
    // is the verse text alone rather than repeating the reference
    labelEl.innerHTML = esc(ref) + esc(sep || " · ") + esc(version.toUpperCase());
    textEl.classList.add("a-loading");
    textEl.innerHTML = "Loading…";
    var t = await fetchVerse(ref, version);
    textEl.classList.remove("a-loading");
    textEl.innerHTML = t ||
      '<p class="a-loading">(' + esc(version.toUpperCase()) +
      " does not resolve this reference)</p>";
  }

  window.jaymsAlphaOutVersion = function (btn) {
  var wrap = btn.parentNode;
  Array.prototype.forEach.call(wrap.querySelectorAll(".a-vb"), function (b) {
    b.classList.remove("on");
  });
  btn.classList.add("on");
  var v = btn.getAttribute("data-v");
  setSharedVersion(v);
  var card = btn.closest ? btn.closest(".a-out") : null;
  loadVerse(btn.getAttribute("data-ref"), v, currentSep(),
            card ? card.querySelector(".a-box") : null);
};
window.jaymsAlphaVersion = function (ref, version, btn) {
    var wrap = btn.parentNode;
    Array.prototype.forEach.call(wrap.querySelectorAll(".a-vb"), function (b) { b.classList.remove("on"); });
    btn.classList.add("on");
    setSharedVersion(version);
    var own = btn.closest ? btn.closest(".a-out") : null;
  loadVerse(ref, version, currentSep(), own ? own.querySelector(".a-box") : null);
  };

  function currentSep() {
    var b = (DOC.blocks || []).filter(function (x) { return x.type === "scripture"; })[0];
    return (b && b.separator) || " · ";
  }

  // ------------------------------------------------------------ reading

  // A tool whose data names a reading source can serve the whole text, not
  // just the excerpt on a card. The store is keyed work -> chapters ->
  // {text}, which is the shape the witness collection already had.
  var readingStore = null;

  function loadReading() {
    if (!readingStore && DOC.reading && DOC.reading.src) {
      readingStore = fetch(DOC.reading.src)
        .then(function (r) { return r.ok ? r.json() : null; })
        .catch(function () { return null; });
    }
    return readingStore || Promise.resolve(null);
  }

  function renderReading() {
    var where = esc(state.book) + " " + esc(state.chapter);
    return '<div class="a-crumbs">' +
        '<button class="a-crumb" onclick="jaymsAlphaGo({screen:\'browse\'})">&larr; ' +
        esc((DOC.tool && DOC.tool.backLabel) || "All entries") + "</button>" +
        '<span class="a-crumb-sep">›</span>' +
        '<span class="a-crumb is-here">' + where + "</span></div>" +
      '<h1 class="a-title">' + where + "</h1>" +
      (DOC.reading && DOC.reading.credit
        ? '<p class="a-eyebrow">' + esc(DOC.reading.credit) + "</p>" : "") +
      '<div class="a-box v-witness" id="jaymsAlphaReading">' +
        '<div class="a-btext a-loading">Loading…</div></div>';
  }

  function fillReading() {
    var box = document.getElementById("jaymsAlphaReading");
    if (!box) return;
    loadReading().then(function (all) {
      var el = box.querySelector(".a-btext");
      if (!el) return;
      var work = all ? all[state.book] : null;
      var ch = work && work.chapters ? work.chapters[state.chapter] : null;
      el.classList.remove("a-loading");
      if (!ch || !ch.text) {
        el.innerHTML = "<p>That chapter is not in this collection.</p>";
        return;
      }
      el.innerHTML = String(ch.text).split(/\n\s*\n/).map(function (p) {
        return p.trim() ? "<p>" + esc(p.trim()) + "</p>" : "";
      }).join("");
    });
  }

  // ------------------------------------------------------------ detail

  // Every step walked to get here, each one clickable. The entry you are
  // on is not repeated, because its own title is the next thing on screen.
  function crumbsHTML() {
    var back = esc((DOC.tool && DOC.tool.backLabel) || "All entries");
    var out = ['<button class="a-crumb" onclick="jaymsAlphaGo({screen:\'browse\'})">&larr; ' + back + "</button>"];
    var trail = Array.isArray(state.trail) ? state.trail : [];
    for (var i = 0; i < trail.length - 1; i++) {
      var prev = BY_ID[trail[i]];
      if (!prev) continue;
      out.push('<span class="a-crumb-sep">\u203a</span>');
      out.push('<button class="a-crumb" onclick="jaymsAlphaGo({screen:\'detail\',id:' +
        jsArg(prev.id) + '})">' + esc(prev.title) + "</button>");
    }
    return '<div class="a-crumbs">' + out.join("") + "</div>";
  }

  // Every axis the entry sits on, not just the graded one.
  function detailTagsHTML(d) {
    var out = visibleFilters().map(function (row) {
      var o = row.options.filter(function (x) { return x.k === d[row.field]; })[0];
      if (!o) return "";
      if (row.role === "category") {
        return '<span class="a-badge" style="--cc:' + colourVar(o.c) + '">' + esc(o.n) + "</span>";
      }
      return '<span class="a-tag">' + esc(o.n) + "</span>";
    }).filter(Boolean).join("");
    return out ? '<div class="a-dtags">' + out + "</div>" : "";
  }

  function countHTML(det, d) {
  var f = det && det.countField;
  if (!f) return "";
  var n = d[f];
  if (n === null || n === undefined || n === "") return "";
  var label = det.countLabel ? ' title="' + esc(det.countLabel) + '"' : "";
  return '<div class="a-dcount"' + label + ">" +
         esc(typeof n === "number" ? n.toLocaleString() : String(n)) + "</div>";
}
function renderDetail() {
    var d = BY_ID[state.id];
    if (!d) return '<p class="a-empty">Entry not found. <button class="a-lnk" onclick="jaymsAlphaGo({screen:\'browse\'})">Back to the list</button></p>';
    var det = DOC.detail || {};
    var blocks = (DOC.blocks || []).map(function (b) { return renderBlock(b, d); }).join("");
    return crumbsHTML() +
      countHTML(det, d) + '<h1 class="a-dtitle">' + esc(d[det.titleField || "title"]) + "</h1>" +
      '<p class="a-dsum">' + esc(d[det.headlineField || "summary"]) + "</p>" +
      detailTagsHTML(d) +
      (PAGE.progress
        ? '<button class="a-dtick' + (isDone(d.id) ? " on" : "") + '" aria-pressed="' +
          isDone(d.id) + '" onclick="jaymsAlphaToggleDone(' + jsArg(d.id) + ')">' +
          '<span class="box">' + (isDone(d.id) ? "\u2713" : "") + "</span>" +
          (isDone(d.id) ? "Worked through" : "Mark as worked through") + "</button>"
        : "") +
      blocks;
  }

  function render() {
    if (!LOADED) { MOUNT.innerHTML = '<p class="a-empty">Loading\u2026</p>'; return; }
    MOUNT.innerHTML = state.screen === "reading" ? renderReading()
      : state.screen === "detail" ? renderDetail() : renderBrowse();
    if (state.screen === "reading") fillReading();
    fillWitnessRows();
    fillLexicon();
    fillMaps();
    paintSlots(state.screen === "detail" ? "" : lastCount);
    wireRail();

    if (state.screen === "detail") {
      fillWitness();
      var d = BY_ID[state.id];
      var sb = (DOC.blocks || []).filter(function (x) { return x.type === "scripture"; })[0];
      if (d && sb) {
        var ref = refFor(sb, d);
        if (ref) loadVerse(ref, pickVersion(blockVersions(sb), sb.default), sb.separator);
      }
    }

    var q = document.getElementById("jaymsAlphaSearch");
    if (q) {
      q.addEventListener("input", function (e) {
        state.q = e.target.value;
        state.page = 1;
        var pos = e.target.selectionStart;
        render();
        var n = document.getElementById("jaymsAlphaSearch");
        if (n) { n.focus(); n.setSelectionRange(pos, pos); }
      });
    }
  }

  window.addEventListener("hashchange", function () {
    // go() sets the hash itself and has already rendered; only a real Back,
    // Forward or typed fragment should be handled here.
    if (hashSelfSet) { hashSelfSet = false; return; }
    state = readURL();
    var t = TRAILS[location.hash];
    if (t) state.trail = t.slice();
    render();
  });

  // ------------------------------------------------------------ boot

  // Everything that touches history runs NOW, during parse, not inside the
  // fetch callback. Two reasons, both measured:
  //   * the hashchange listener above has to be live before a reader can
  //     press Back, or the browser falls through to a full page load
  //   * a replaceState made after the document has committed makes the
  //     browser re-fetch that entry on a back traversal, which turned
  //     paging to 2 and pressing Back into a full reload
  // This is the pattern the shared Tool Engine already uses for Word Study:
  // boot with a placeholder, swap the real data in when it arrives.
  state = readURL();

  // A link shared before the move to fragments carries ?id= or ?f_= instead.
  // Rewrite it here, during parse, so everything past this point has one URL
  // shape to reason about. A clean visit is left alone: no reader should be
  // handed a fragment they did not ask for.
  if (!location.hash && hasLegacyParams()) {
    history.replaceState(null, "", location.pathname + keptQuery() + hashFor(state));
  }

  // A shared link that opens straight onto an entry no longer seeds the list
  // beneath itself. Both ways of making that extra entry are worse than not
  // having it: pushState creates one that reloads the whole document when a
  // reader steps back onto it, and a fragment set while the document is
  // still parsing is folded into the current entry instead of adding one --
  // both measured here, not assumed. So Back returns the reader wherever
  // they came from, which is what Back is for, and the crumb is the way to
  // the list.
  TRAILS[location.hash] = (state.trail || []).slice();

  render();   // the loading line, until LOADED flips

  fetch(SRC, { cache: "no-cache" })
    .then(function (r) {
      if (!r.ok) throw new Error("HTTP " + r.status);
      return r.json();
    })
    .then(function (doc) {
      DOC = doc;
      ENTRIES = doc.entries || [];
      ENTRIES.forEach(function (e) { BY_ID[e.id] = e; });
      LOADED = true;

      // a bare integer is an old-style share link, resolvable only now
      if (state.screen === "detail" && !BY_ID[state.id] &&
          /^\d+$/.test(state.id) && ENTRIES[+state.id]) {
        state.id = ENTRIES[+state.id].id;
      }
      applyDefaultGroup();

      paintFeatured();
      render();
    })
    .catch(function (err) {
      LOADED = true;
      MOUNT.innerHTML = '<p class="a-empty">This tool could not load its data. ' + esc(String(err)) + "</p>";
    });

})();

/* ---- Entry URLs ---------------------------------------------------------
   The server prints every entry at a URL of its own, /<tool>/<id>/, and that
   is the address Google holds and the one the printed index links to. The
   runner itself keeps its state in the hash, which is right for a filter or
   a search and is the only notation its history handling understands.

   So the two are not merged. Arriving on an entry URL, this normalises to
   the runner's own notation once, before the runner boots, and then gets out
   of the way. Trying to hold the path in the address bar as well fought the
   runner's own history entries and sent a cross-link click back to the list.

   window.jaymsEntryBase and window.jaymsEntryId are printed into the head by
   the entry-URL snippet; with no base there is nothing to map.
   ------------------------------------------------------------------------ */
(function () {
  var base = window.jaymsEntryBase;
  if (!base) { return; }

  var id = window.jaymsEntryId || "";
  if (!id) {
    var rest = decodeURIComponent(location.pathname.slice(base.length).replace(/\/+$/, ""));
    if (location.pathname.indexOf(base) === 0 && /^[a-z0-9][a-z0-9-]*$/.test(rest)) { id = rest; }
  }
  if (!id) { return; }

  // Before the runner reads the URL, so it simply renders the entry.
  history.replaceState(null, "", base + "#id=" + encodeURIComponent(id));

  // If the runner had already booted, tell it.
  window.addEventListener("load", function () {
    setTimeout(function () {
      var d = document.querySelector("#app .a-dtitle");
      if (!d && typeof jaymsAlphaGo === "function") {
        jaymsAlphaGo({ screen: "detail", id: id });
      }
    }, 0);
  });
})();
</script>
	<?php
}, 20 );
