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

add_action( 'wp_footer', function () {

	$TOOLS = array(
		98131 => 'divine-council-alpha',
		98132 => 'gods-of-the-bible-alpha',
	);

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
	?>

<style id="jayms-alpha-css">
.jayms-tool-alpha{
  /* The locked palette. Colour carries meaning, the same meaning on every
     alpha tool. A variant outside this list fails the dataset build. */
  --a-rust:#d4664f;      /* the correction: what is wrong, what got changed */
  --a-gold:#d4a85c;      /* the primary text under discussion */
  --a-lavender:#b299c9;  /* language and translation */
  --a-aramaic:#9fb8a8;   /* outside corroboration */
  --a-blue:#8fb4d9;      /* live disagreement, open question */
  --a-muted:#847a6a;     /* apparatus, not argument */
  --a-ink:#ede4d3;
  --a-ink-soft:#c4b9a3;
  --a-line:#3a342c;
  --a-panel:#1d1916;
  --a-panel-2:#191512;
  max-width:1100px;margin:0 auto;padding:0 16px;
  font-family:"Archivo",system-ui,-apple-system,Arial,sans-serif;
  color:var(--a-ink);
}
.jayms-tool-alpha *{box-sizing:border-box}

/* hero */
.jayms-tool-alpha .a-hero{padding:8px 0 4px}
.jayms-tool-alpha .a-eyebrow{font-size:12px;letter-spacing:.18em;text-transform:uppercase;color:var(--a-gold);margin-bottom:10px}
.jayms-tool-alpha .a-eyebrow .a-flag{display:inline-block;margin-left:8px;padding:2px 7px;border:1px solid var(--a-rust);color:var(--a-rust);border-radius:3px;font-size:10px;letter-spacing:.14em}
.jayms-tool-alpha h1.a-title{font-size:clamp(30px,6vw,52px);line-height:1.08;margin:0 0 12px;font-weight:600}
.jayms-tool-alpha .a-sub{font-size:18px;line-height:1.55;color:var(--a-ink-soft);margin:0 0 18px;max-width:64ch}
.jayms-tool-alpha .a-dots{display:flex;gap:8px;margin-bottom:18px}
.jayms-tool-alpha .a-dot{width:11px;height:11px;border-radius:50%}

/* search + filters */
.jayms-tool-alpha .a-search{width:100%!important;padding:13px 15px!important;font-size:16px!important;
  background:var(--a-panel)!important;color:var(--a-ink)!important;border:1px solid var(--a-line)!important;
  border-radius:7px!important;margin:0 0 14px!important;font-family:inherit!important}
.jayms-tool-alpha .a-search::placeholder{color:var(--a-muted)!important}
.jayms-tool-alpha .a-search:focus{outline:none!important;border-color:var(--a-gold)!important}
.jayms-tool-alpha .a-filters{background:var(--a-panel);border:1px solid var(--a-line);border-radius:9px;padding:11px;margin-bottom:11px}
.jayms-tool-alpha .a-row{display:flex;flex-wrap:wrap;gap:7px}
.jayms-tool-alpha .a-row + .a-row{margin-top:9px;padding-top:9px;border-top:1px solid var(--a-line)}
.jayms-tool-alpha .a-chip{display:inline-flex;align-items:center;gap:7px;padding:7px 13px;border-radius:999px;
  background:transparent;border:1px solid transparent;color:var(--a-ink-soft);font-size:14px;cursor:pointer;
  font-family:inherit;line-height:1.3}
.jayms-tool-alpha .a-chip:hover{color:var(--a-ink)}
.jayms-tool-alpha .a-chip.on{border-color:var(--a-gold);color:var(--a-ink)}
.jayms-tool-alpha .a-cdot{width:9px;height:9px;border-radius:50%;flex:0 0 auto}
.jayms-tool-alpha .a-count{font-size:12px;letter-spacing:.14em;text-transform:uppercase;color:var(--a-muted);margin:14px 0 10px}

/* cards */
.jayms-tool-alpha .a-cards{display:grid;grid-template-columns:repeat(auto-fill,minmax(290px,1fr));gap:13px}
.jayms-tool-alpha .a-card{text-align:left;background:var(--a-panel);border:1px solid var(--a-line);
  border-left:3px solid var(--cc,var(--a-line));border-radius:9px;padding:16px;cursor:pointer;
  font-family:inherit;color:inherit;display:flex;flex-direction:column;gap:9px}
.jayms-tool-alpha .a-card:hover{border-color:var(--a-gold);border-left-color:var(--cc,var(--a-gold))}
.jayms-tool-alpha .a-card .a-clabel{font-size:12px;letter-spacing:.13em;text-transform:uppercase;color:var(--a-gold)}
.jayms-tool-alpha .a-card .a-cmain{font-size:16px;line-height:1.45;color:var(--a-ink)}
.jayms-tool-alpha .a-meta{display:flex;flex-wrap:wrap;gap:7px;margin-top:auto;padding-top:4px}
.jayms-tool-alpha .a-tag{font-size:10.5px;letter-spacing:.11em;text-transform:uppercase;color:var(--a-muted);
  border:1px solid var(--a-line);border-radius:3px;padding:3px 7px}
.jayms-tool-alpha .a-badge{font-size:10.5px;letter-spacing:.11em;text-transform:uppercase;color:var(--cc,var(--a-muted));
  border:1px solid var(--cc,var(--a-line));border-radius:3px;padding:3px 7px}
.jayms-tool-alpha .a-empty{color:var(--a-muted);padding:26px 0}

/* pagination */
.jayms-tool-alpha .a-pg{display:flex;align-items:center;justify-content:center;gap:16px;margin:24px 0}
.jayms-tool-alpha .a-pgb{background:transparent;border:1px solid var(--a-line);color:var(--a-ink-soft);
  border-radius:6px;padding:8px 16px;cursor:pointer;font-family:inherit;font-size:13px;
  letter-spacing:.11em;text-transform:uppercase}
.jayms-tool-alpha .a-pgb:hover:not(:disabled){border-color:var(--a-gold);color:var(--a-ink)}
.jayms-tool-alpha .a-pgb:disabled{opacity:.35;cursor:default}
.jayms-tool-alpha .a-pglabel{font-size:12px;letter-spacing:.13em;text-transform:uppercase;color:var(--a-muted)}

/* detail */
.jayms-tool-alpha .a-back{background:transparent;border:0;color:var(--a-gold);cursor:pointer;padding:6px 0;
  font-family:inherit;font-size:13px;letter-spacing:.12em;text-transform:uppercase;margin-bottom:14px}
.jayms-tool-alpha h1.a-dtitle{font-size:clamp(25px,4.6vw,40px);line-height:1.14;margin:10px 0 10px;font-weight:600}
.jayms-tool-alpha .a-dsum{font-size:19px;line-height:1.6;color:var(--a-ink-soft);margin:0 0 22px;max-width:70ch}

/* every piece of content is a box, and every box has a title */
.jayms-tool-alpha .a-box{background:var(--a-panel);border:1px solid var(--a-line);
  border-left:3px solid var(--bc,var(--a-gold));border-radius:9px;padding:15px 18px;margin:0 0 13px}
.jayms-tool-alpha .a-box > .a-blabel{font-size:12px;letter-spacing:.14em;text-transform:uppercase;
  color:var(--bc,var(--a-gold));margin-bottom:9px}
.jayms-tool-alpha .a-box > .a-btext{font-size:17px;line-height:1.66;color:var(--a-ink)}
.jayms-tool-alpha .a-box > .a-btext p{margin:0 0 11px}
.jayms-tool-alpha .a-box > .a-btext p:last-child{margin-bottom:0}
.jayms-tool-alpha .a-box > .a-btext b,
.jayms-tool-alpha .a-box > .a-btext strong{color:var(--a-gold);font-weight:600}
.jayms-tool-alpha .a-box > .a-btext i,
.jayms-tool-alpha .a-box > .a-btext em{font-style:italic;color:var(--a-ink-soft)}
.jayms-tool-alpha .a-bnote{margin-top:10px;padding-top:9px;border-top:1px solid var(--a-line);
  font-size:14px;line-height:1.6;color:var(--a-muted)}
.jayms-tool-alpha .a-box ol,.jayms-tool-alpha .a-box ul{margin:0;padding-left:20px}
.jayms-tool-alpha .a-box li{font-size:14.5px;line-height:1.62;color:var(--a-ink-soft);margin-bottom:6px}
.jayms-tool-alpha .a-box li:last-child{margin-bottom:0}

/* the seven locked variants */
.jayms-tool-alpha .a-box.v-rust{--bc:var(--a-rust)}
.jayms-tool-alpha .a-box.v-gold{--bc:var(--a-gold)}
.jayms-tool-alpha .a-box.v-lavender{--bc:var(--a-lavender)}
.jayms-tool-alpha .a-box.v-aramaic{--bc:var(--a-aramaic)}
.jayms-tool-alpha .a-box.v-blue{--bc:var(--a-blue)}
.jayms-tool-alpha .a-box.v-muted{--bc:var(--a-muted)}
.jayms-tool-alpha .a-box.v-plain{--bc:var(--a-ink-soft);background:var(--a-panel-2)}
.jayms-tool-alpha .a-box.v-plain > .a-btext{font-size:19px;line-height:1.75}
.jayms-tool-alpha .a-box.v-plain > .a-btext b{color:var(--a-gold);font-style:italic}

/* scripture block */
.jayms-tool-alpha .a-vsw{display:flex;gap:7px;margin:0 0 11px;flex-wrap:wrap}
.jayms-tool-alpha .a-vb{background:transparent;border:1px solid var(--a-line);color:var(--a-ink-soft);
  border-radius:999px;padding:5px 14px;cursor:pointer;font-family:inherit;font-size:12px;
  letter-spacing:.11em;text-transform:uppercase}
.jayms-tool-alpha .a-vb.on{background:var(--a-gold);border-color:var(--a-gold);color:#1d1916;font-weight:600}
.jayms-tool-alpha .a-linkrow{display:flex;flex-wrap:wrap;gap:8px;margin:0 0 13px}
.jayms-tool-alpha .a-lnk{display:inline-block;background:transparent;border:1px solid var(--a-line);
  color:var(--a-gold);border-radius:999px;padding:6px 14px;cursor:pointer;font-family:inherit;
  font-size:13px;text-decoration:none;line-height:1.35}
.jayms-tool-alpha .a-lnk:hover{border-color:var(--a-gold)}
.jayms-tool-alpha .a-loading{color:var(--a-muted);font-style:italic}

/* credit + footer */
.jayms-tool-alpha .a-credit{margin:18px 0 0;padding-top:13px;border-top:1px solid var(--a-line);
  font-size:14px;line-height:1.7;color:var(--a-muted)}
.jayms-tool-alpha .a-credit a{color:var(--a-gold)}
.jayms-tool-alpha .a-foot{margin-top:34px;padding-top:18px;border-top:1px solid var(--a-line)}
.jayms-tool-alpha .a-note{font-size:14.5px;line-height:1.7;color:var(--a-muted);margin-bottom:14px}
.jayms-tool-alpha .a-note.method{color:var(--a-ink-soft)}

@media(max-width:640px){
  .jayms-tool-alpha .a-cards{grid-template-columns:1fr}
  .jayms-tool-alpha .a-box{padding:13px 15px}
  .jayms-tool-alpha .a-box > .a-btext{font-size:16px}
}
</style>

<script id="jayms-alpha-engine">
(function () {
  "use strict";

  var SRC   = <?php echo wp_json_encode( $src ); ?>;
  var MOUNT = document.getElementById("app");
  if (!MOUNT) return;

  var DOC = null, ENTRIES = [], BY_ID = {};
  var state = { screen: "browse", id: null, q: "", filters: {}, page: 1 };

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
  function catOf(k) {
    var r = catRow(); if (!r) return null;
    return r.options.filter(function (o) { return o.k === k; })[0] || null;
  }
  function colourVar(c) { return c ? "var(--a-" + c + ")" : "var(--a-muted)"; }

  // ------------------------------------------------------------ routing

  function readURL() {
    var p = new URLSearchParams(location.search);
    if (p.has("id")) {
      var v = p.get("id");
      // slugs are the real ids; a bare integer is an old-style share link
      var id = BY_ID[v] ? v : (/^\d+$/.test(v) && ENTRIES[+v] ? ENTRIES[+v].id : null);
      if (id) return { screen: "detail", id: id, q: "", filters: {}, page: 1 };
    }
    var f = {}, q = p.get("q") || "", pg = parseInt(p.get("page"), 10) || 1;
    (DOC.filters || []).forEach(function (row) {
      var v = p.get("f_" + row.field);
      if (v) f[row.field] = v;
    });
    return { screen: "browse", id: null, q: q, filters: f, page: pg };
  }

  function writeURL(s) {
    if (s.screen === "detail") return "?id=" + encodeURIComponent(s.id);
    var p = new URLSearchParams();
    Object.keys(s.filters || {}).forEach(function (k) {
      if (s.filters[k]) p.set("f_" + k, s.filters[k]);
    });
    if (s.q) p.set("q", s.q);
    if (s.page && s.page !== 1) p.set("page", s.page);
    var qs = p.toString();
    return qs ? "?" + qs : location.pathname;
  }

  var browseY = 0;
  function go(patch) {
    var was = state.screen;
    if (was === "browse") browseY = window.scrollY;
    Object.assign(state, patch);
    history.pushState(state, "", writeURL(state));
    render();
    if (state.screen === "browse" && was !== "browse") window.scrollTo(0, browseY);
    else if (state.screen !== was) MOUNT.scrollIntoView({ behavior: "smooth", block: "start" });
  }
  window.jaymsAlphaGo = go;

  window.jaymsAlphaFilter = function (field, val) {
    var f = Object.assign({}, state.filters);
    if (val === null) delete f[field]; else f[field] = val;
    go({ filters: f, page: 1 });
  };

  // ------------------------------------------------------------ browse

  function matches(d) {
    var rows = DOC.filters || [];
    for (var i = 0; i < rows.length; i++) {
      var sel = state.filters[rows[i].field];
      if (sel && d[rows[i].field] !== sel) return false;
    }
    if (state.q) {
      var fields = (DOC.search && DOC.search.fields) || ["title", "summary"];
      var hay = fields.map(function (f) {
        var v = d[f];
        return Array.isArray(v) ? v.join(" ") : (v || "");
      }).join(" ").toLowerCase();
      if (hay.indexOf(state.q.toLowerCase()) === -1) return false;
    }
    return true;
  }

  function heroHTML() {
    var t = DOC.tool, cr = catRow();
    var dots = cr ? cr.options.map(function (o) {
      return '<span class="a-dot" style="background:' + colourVar(o.c) + '"></span>';
    }).join("") : "";
    var flag = t.status ? '<span class="a-flag">' + esc(t.status) + "</span>" : "";
    return '<div class="a-hero"><div class="a-eyebrow">Tool' + flag + "</div>" +
      '<h1 class="a-title">' + esc(t.heroTitle) + "</h1>" +
      '<p class="a-sub">' + esc(t.heroSubtitle) + "</p>" +
      '<div class="a-dots">' + dots + "</div></div>";
  }

  function filtersHTML() {
    var out = (DOC.filters || []).map(function (row) {
      var sel = state.filters[row.field] || null;
      var btns = ['<button class="a-chip' + (sel ? "" : " on") + '" onclick="jaymsAlphaFilter(' +
        JSON.stringify(row.field).replace(/"/g, "&quot;") + ',null)">' +
        (row.role === "category" ? '<span class="a-cdot" style="background:var(--a-ink)"></span>' : "") +
        esc(row.allLabel || "All") + "</button>"];
      row.options.forEach(function (o) {
        btns.push('<button class="a-chip' + (sel === o.k ? " on" : "") + '" onclick="jaymsAlphaFilter(' +
          JSON.stringify(row.field).replace(/"/g, "&quot;") + "," +
          JSON.stringify(o.k).replace(/"/g, "&quot;") + ')">' +
          (o.c ? '<span class="a-cdot" style="background:' + colourVar(o.c) + '"></span>' : "") +
          esc(o.n) + "</button>");
      });
      return '<div class="a-row">' + btns.join("") + "</div>";
    }).join("");
    return '<div class="a-filters">' + out + "</div>";
  }

  function cardHTML(d) {
    var c = catOf(d.category);
    var card = DOC.card || {};
    var label = card.labelField ? d[card.labelField] : "";
    var main = d[card.mainField || "summary"];
    var tags = (DOC.filters || []).filter(function (r) { return r.role !== "category"; })
      .map(function (r) {
        var o = r.options.filter(function (x) { return x.k === d[r.field]; })[0];
        return o ? '<span class="a-tag">' + esc(o.n) + "</span>" : "";
      }).join("");
    return '<button class="a-card" style="--cc:' + colourVar(c && c.c) + '" onclick="jaymsAlphaGo({screen:\'detail\',id:' +
      JSON.stringify(d.id).replace(/"/g, "&quot;") + '})">' +
      (label ? '<div class="a-clabel">' + esc(label) + "</div>" : "") +
      '<div class="a-cmain">' + esc(main) + "</div>" +
      '<div class="a-meta">' + tags +
      '<span class="a-badge" style="--cc:' + colourVar(c && c.c) + '">' + esc(c ? c.n : d.category) + "</span>" +
      "</div></button>";
  }

  function footerHTML() {
    var parts = (DOC.footer || []).map(function (f) {
      if (f.type === "note") {
        return '<div class="a-note ' + esc(f.class || "") + '">' + esc(f.text) + "</div>";
      }
      if (f.type === "suggest" && typeof jaymsSuggest !== "undefined" && jaymsSuggest.boxHTML) {
        try {
          return '<div class="a-note">' + jaymsSuggest.boxHTML(f.kind, {
            toggleText: f.toggleText, valuePlaceholder: f.valuePlaceholder
          }) + "</div>";
        } catch (e) { return ""; }
      }
      return "";
    }).join("");
    return '<div class="a-foot">' + parts + "</div>";
  }

  function renderBrowse() {
    var rows = ENTRIES.filter(matches);
    var per = (DOC.tool && DOC.tool.perPage) || 20;
    var pages = Math.max(1, Math.ceil(rows.length / per));
    if (state.page > pages) state.page = pages;
    var slice = rows.slice((state.page - 1) * per, state.page * per);
    var filtered = state.q || Object.keys(state.filters).length;

    return heroHTML() +
      '<input class="a-search" id="jaymsAlphaSearch" type="search" placeholder="' +
        esc((DOC.tool && DOC.tool.searchPlaceholder) || "Search…") +
        '" value="' + esc(state.q) + '">' +
      filtersHTML() +
      '<div class="a-count">' + rows.length + " of " + ENTRIES.length +
        (filtered ? " &middot; filtered" : "") + "</div>" +
      '<div class="a-cards">' + (slice.map(cardHTML).join("") ||
        '<p class="a-empty">Nothing matches. Clear a filter and try again.</p>') + "</div>" +
      (pages > 1 ? '<div class="a-pg">' +
        '<button class="a-pgb"' + (state.page <= 1 ? " disabled" : "") +
          ' onclick="jaymsAlphaGo({page:' + (state.page - 1) + '})">Prev</button>' +
        '<span class="a-pglabel">Page ' + state.page + " of " + pages + "</span>" +
        '<button class="a-pgb"' + (state.page >= pages ? " disabled" : "") +
          ' onclick="jaymsAlphaGo({page:' + (state.page + 1) + '})">Next</button></div>' : "") +
      footerHTML();
  }

  // ------------------------------------------------------------ blocks

  function box(variant, label, bodyHTML, noteHTML) {
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

  function renderBlock(b, d) {
    if (b.type === "scripture") return scriptureHTML(b, d);

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
      return '<div class="a-box v-muted"><div class="a-blabel">' + esc(b.title) +
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
    var v = d[b.field];
    if (!v || (Array.isArray(v) && !v.length)) return "";
    var body;
    if (Array.isArray(v)) {
      var tag = b.list === "ordered" ? "ol" : "ul";
      body = "<" + tag + ">" + v.map(function (x) { return "<li>" + esc(x) + "</li>"; }).join("") + "</" + tag + ">";
    } else {
      body = b.quote ? "<p>&ldquo;" + esc(v) + "&rdquo;</p>" : rich(v);
    }
    var note = b.noteField && d[b.noteField] ? esc(d[b.noteField]) : "";
    return box(b.variant, blockTitle(b, d), body, note);
  }

  // ------------------------------------------------------------ scripture

  var verseCache = {};

  function refFor(b, d) {
    return d[b.overrideField || "bibleRefOverride"] || d[b.refField || "bibleRef"] || "";
  }

  function scriptureHTML(b, d) {
    var ref = refFor(b, d);
    if (!ref) return b.emptyText ? box("muted", "No biblical reference", "<p>" + esc(b.emptyText) + "</p>") : "";
    var versions = b.versions || ["net", "web", "nlt", "esv"];
    var def = b.default || versions[0];
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
        return u ? '<a class="a-lnk" href="' + esc(u) + '" target="_blank" rel="noopener">' +
          esc(p) + " &middot; NET + interlinear &rsaquo;</a>" : "";
      }).filter(Boolean).join("");
    }

    return '<div class="a-vsw">' + pills + "</div>" +
      '<div class="a-box v-gold" id="jaymsAlphaVerse">' +
        '<div class="a-blabel">' + esc(ref) + " &middot; " + esc(def.toUpperCase()) + "</div>" +
        '<div class="a-btext a-loading">Loading…</div></div>' +
      (links ? '<div class="a-linkrow">' + links + "</div>" : "");
  }

  async function fetchVerse(ref, version) {
    var key = version + "|" + ref;
    if (verseCache[key]) return verseCache[key];
    var out = "";
    try {
      if (typeof window.jaymsFetchVerse === "function") {
        out = await window.jaymsFetchVerse(ref, version);
      }
    } catch (e) { out = ""; }
    if (!out && version === "net") {
      try {
        var r = await fetch("https://labs.bible.org/api/?passage=" +
          encodeURIComponent(ref) + "&type=json&formatting=plain");
        var j = await r.json();
        out = (j || []).map(function (v) {
          return '<sup>' + esc(v.verse) + "</sup>" + esc(v.text);
        }).join(" ");
      } catch (e2) { out = ""; }
    }
    if (out) verseCache[key] = out;
    return out;
  }

  async function loadVerse(ref, version, sep) {
    var boxEl = document.getElementById("jaymsAlphaVerse");
    if (!boxEl) return;
    var textEl = boxEl.querySelector(".a-btext");
    var labelEl = boxEl.querySelector(".a-blabel");
    labelEl.innerHTML = esc(ref) + esc(sep || " · ") + esc(version.toUpperCase());
    textEl.classList.add("a-loading");
    textEl.innerHTML = "Loading…";
    var t = await fetchVerse(ref, version);
    textEl.classList.remove("a-loading");
    textEl.innerHTML = t
      ? "<p><b>" + esc(ref) + "</b>" + esc(sep || " · ") + t + "</p>"
      : '<p class="a-loading">(' + esc(version.toUpperCase()) + " unavailable)</p>";
  }

  window.jaymsAlphaVersion = function (ref, version, btn) {
    var wrap = btn.parentNode;
    Array.prototype.forEach.call(wrap.querySelectorAll(".a-vb"), function (b) { b.classList.remove("on"); });
    btn.classList.add("on");
    loadVerse(ref, version, currentSep());
  };

  function currentSep() {
    var b = (DOC.blocks || []).filter(function (x) { return x.type === "scripture"; })[0];
    return (b && b.separator) || " · ";
  }

  // ------------------------------------------------------------ detail

  function renderDetail() {
    var d = BY_ID[state.id];
    if (!d) return '<p class="a-empty">Entry not found. <button class="a-lnk" onclick="jaymsAlphaGo({screen:\'browse\'})">Back to the list</button></p>';
    var c = catOf(d.category);
    var det = DOC.detail || {};
    var blocks = (DOC.blocks || []).map(function (b) { return renderBlock(b, d); }).join("");
    return '<button class="a-back" onclick="jaymsAlphaGo({screen:\'browse\'})">&larr; ' +
        esc((DOC.tool && DOC.tool.backLabel) || "All entries") + "</button>" +
      '<span class="a-badge" style="--cc:' + colourVar(c && c.c) + '">' + esc(c ? c.n : d.category) + "</span>" +
      '<h1 class="a-dtitle">' + esc(d[det.titleField || "title"]) + "</h1>" +
      '<p class="a-dsum">' + esc(d[det.headlineField || "summary"]) + "</p>" +
      blocks;
  }

  // ------------------------------------------------------------ render

  function render() {
    MOUNT.innerHTML = state.screen === "detail" ? renderDetail() : renderBrowse();

    if (state.screen === "detail") {
      var d = BY_ID[state.id];
      var sb = (DOC.blocks || []).filter(function (x) { return x.type === "scripture"; })[0];
      if (d && sb) {
        var ref = refFor(sb, d);
        if (ref) loadVerse(ref, sb.default || "net", sb.separator);
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

  window.addEventListener("popstate", function (e) {
    state = e.state || readURL();
    render();
  });

  // ------------------------------------------------------------ boot

  MOUNT.innerHTML = '<p class="a-empty">Loading…</p>';

  fetch(SRC, { cache: "no-cache" })
    .then(function (r) {
      if (!r.ok) throw new Error("HTTP " + r.status);
      return r.json();
    })
    .then(function (doc) {
      DOC = doc;
      ENTRIES = doc.entries || [];
      ENTRIES.forEach(function (e) { BY_ID[e.id] = e; });
      state = readURL();
      history.replaceState(state, "", writeURL(state));
      render();
    })
    .catch(function (err) {
      MOUNT.innerHTML = '<p class="a-empty">This tool could not load its data. ' + esc(String(err)) + "</p>";
    });
})();
</script>
	<?php
}, 20 );
