/**
 * JAYMS — Word Trail panel
 *
 * The "Where it appears" box in a tool listed every verse as a row of chips.
 * Word Trail already draws the same thing as a picture: the word at the top,
 * every Old Testament book it sits in as a branch, the Greek word and its New
 * Testament books beside it. This puts that picture in the box, and puts a
 * button beside it that swaps back to the book list the box always had.
 *
 * It is a component, not a patch. It does not touch the tool runner, the entry
 * pages or Word Trail's own files.
 *
 *   - It watches the tool page for any box titled "Where it appears" (the list
 *     of titles is TITLES below) and upgrades it in place. The runner redraws
 *     the page on every move, so it re-attaches each time.
 *   - The word comes from the entry's own "The word" box (the first Hebrew
 *     Strong's number, else the first Greek one), or the id in the address on
 *     Word Study (#id=g4458).
 *   - Word Trail runs in a sealed frame. It reads and rewrites the page address
 *     as it works, and the runner keeps its place in the same address, so the
 *     two cannot share a page. The frame has its own address and its own
 *     session storage, so it cannot move the entry or fill your Word Trail
 *     history.
 *   - If the word is unknown to Word Trail, the diagram does not draw, or the
 *     frame is slow, the original book list simply stays. No button appears.
 *
 * Which build of Word Trail it loads is read from word-trail-embed.json in the
 * jayms-tool-data repo, so shipping a Word Trail edit means changing that one
 * file, never this snippet.
 *
 * Reusable by hand:  jaymsWordTrailPanel.mount( element, 'H776' )
 */
add_action( 'wp_footer', function () {
	if ( ! is_page() ) { return; }
	$pid     = (int) get_queried_object_id();
	$is_tool = function_exists( 'jayms_alpha_tool_map' ) && isset( jayms_alpha_tool_map()[ $pid ] );
	if ( ! $is_tool && 91449 !== (int) wp_get_post_parent_id( $pid ) ) { return; }
	?>
<script id="jayms-wt-panel">
(function () {
  "use strict";
  if (window.jaymsWordTrailPanel) { return; }

  // Boxes with one of these titles get the panel. Add a title here to add a box.
  var TITLES = [/^where it appears$/i];
  var POINTER = "https://raw.githubusercontent.com/sixcore-droid/jayms-tool-data/main/word-trail-embed.json";
  var FALLBACK = "https://cdn.jsdelivr.net/gh/sixcore-droid/jayms-tool-data@6906736e110f88b1ac94a3f9a7dd63098143f0a0/word-trail/";
  var STORE = "jayms.wtpanel";
  var GIVE_UP = 25000;
  var live = [];
  var basePromise = null;

  // ------------------------------------------------------------------ small things
  function remembered(v) {
    try { if (v === undefined) { return localStorage.getItem(STORE); } localStorage.setItem(STORE, v); } catch (e) {}
    return null;
  }
  function esc(s) { return String(s == null ? "" : s).replace(/[&<>"']/g, function (c) { return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]; }); }
  function el(tag, cls) { var e = document.createElement(tag); if (cls) { e.className = cls; } return e; }
  function firstText(node) { var n = node && node.childNodes && node.childNodes[0]; return n ? String(n.nodeValue || n.textContent || "").trim() : ""; }

  // ------------------------------------------------------------------ which word
  // Every Hebrew or Greek Strong's number in a piece of text, in order, once each.
  function strongsIn(text) {
    var out = [], seen = {}, re = /\b([HG])0*(\d{1,4})\b/g, m, id;
    while ((m = re.exec(String(text || "")))) {
      id = m[1] + m[2];
      if (!seen[id]) { seen[id] = 1; out.push(id); }
    }
    return out;
  }
  // The Hebrew word leads, because Word Trail follows it into the Greek. A Greek-only entry leads with itself.
  function pickWord(ids) {
    var i;
    for (i = 0; i < ids.length; i++) { if (ids[i].charAt(0) === "H") { return ids[i]; } }
    return ids[0] || "";
  }
  function wordFor(app, hash) {
    var boxes = app.querySelectorAll(".a-box"), i, lab, ids, m;
    for (i = 0; i < boxes.length; i++) {
      lab = boxes[i].querySelector(".a-blabel");
      if (lab && /^the word/i.test(firstText(lab))) {
        ids = strongsIn(boxes[i].innerText || boxes[i].textContent);
        if (ids.length) { return pickWord(ids); }
      }
    }
    m = /[#&]id=([hg])0*(\d{1,4})(?:&|$)/i.exec(hash || "");
    return m ? m[1].toUpperCase() + m[2] : "";
  }

  // ------------------------------------------------------------------ the sealed frame
  function getBase() {
    if (!basePromise) {
      basePromise = fetch(POINTER).then(function (r) { return r.ok ? r.json() : null; })
        .then(function (j) { return j && /^https:\/\//.test(j.base || "") ? j.base : FALLBACK; })
        .catch(function () { return FALLBACK; });
    }
    return basePromise;
  }

  // What the frame hides: the search box, the history strip, the tabs, the side column, the two word cards
  // and the Greek chooser, the footer. The entry's own "The word" box already says what the word is, so
  // the frame is the picture and the verses it opens.
  var EMBED_CSS =
    "html,body{margin:0!important;padding:0!important;background:transparent!important;min-height:0!important}" +
    ".jayms-word-trail{max-width:none!important;margin:0!important;padding:0!important}" +
    ".jayms-word-trail .wt-trail,.jayms-word-trail .wt-find,.jayms-word-trail .wt-sugg,.jayms-word-trail .wt-tabs," +
    ".jayms-word-trail .wt-side,.jayms-word-trail #wt-greek,.jayms-word-trail .a-foot{display:none!important}";

  function frameDocument(base) {
    var css = [].map.call(document.head.querySelectorAll("link[rel=stylesheet],style"), function (n) { return n.outerHTML; }).join("\n");
    return "<!doctype html><html lang=\"en\"><head><meta charset=\"utf-8\"><base target=\"_top\">" + css +
      "<link rel=\"stylesheet\" href=\"" + esc(base) + "word-trail.css\"><style>" + EMBED_CSS + "</style></head>" +
      "<body class=\"" + esc(document.body.className) + "\">" +
      // Word Trail keeps a "words you looked at" strip in session storage. That is shared with the page, so give the frame its own empty one.
      "<script>try{Object.defineProperty(window,\"sessionStorage\",{value:{getItem:function(){return null},setItem:function(){},removeItem:function(){}}})}catch(e){}<\/script>" +
      "<div class=\"jayms-tool-alpha jayms-word-trail\" id=\"jayms-word-trail\" data-views=\"uses\"></div>" +
      "<script src=\"" + esc(base) + "word-trail.js\"><\/script></body></html>";
  }

  function openFrame(host, word, title, done) {
    getBase().then(function (base) {
      var url = URL.createObjectURL(new Blob([frameDocument(base)], { type: "text/html" }));
      var f = el("iframe", "jwtp-frame");
      f.setAttribute("title", title);
      f.setAttribute("loading", "eager");
      f.src = url + "#w=" + encodeURIComponent(word) + "&view=uses";
      host.appendChild(f);
      live.push({ host: host, url: url });
      watch(f, function (ok, d) { if (ok) { autosize(f, d); } done(ok, f); });
    });
  }

  // Ready means the book circles are drawn. Failed means it finished and there is nothing to show, or it never finished.
  function watch(f, finish) {
    var t0 = Date.now(), over = false;
    (function tick() {
      if (over) { return; }
      var d = null, main;
      try { d = f.contentDocument; } catch (e) { d = null; }
      main = d && d.getElementById("wt-main");
      if (main && main.children.length && !main.querySelector(".wt-loading")) {
        over = true;
        return finish(!!main.querySelector("g.bn[data-book]"), d);
      }
      if (Date.now() - t0 > GIVE_UP) { over = true; return finish(false, d); }
      setTimeout(tick, 250);
    })();
  }

  function autosize(f, d) {
    function set() { var h = Math.ceil(d.body.getBoundingClientRect().height); if (h > 0) { f.style.height = h + "px"; } }
    set();
    try { new f.contentWindow.ResizeObserver(set).observe(d.body); }
    catch (e) { var iv = setInterval(function () { if (!document.body.contains(f)) { clearInterval(iv); return; } set(); }, 700); }
  }

  function prune() {
    live = live.filter(function (x) {
      if (document.body.contains(x.host)) { return true; }
      try { URL.revokeObjectURL(x.url); } catch (e) {}
      return false;
    });
  }

  // ------------------------------------------------------------------ the box
  function attach(box, app) {
    if (box.getAttribute("data-wtp")) { return; }
    box.setAttribute("data-wtp", "1");
    var word = wordFor(app, location.hash);
    var label = box.querySelector(".a-blabel");
    if (!word || !label) { return; }

    // everything the box already held goes into one wrapper, untouched, so it can be hidden and shown again
    var list = el("div", "jwtp-list"), kids = [].slice.call(box.children), i;
    for (i = 0; i < kids.length; i++) { if (kids[i] !== label) { list.appendChild(kids[i]); } }
    var bar = el("div", "jwtp-bar"), note = el("span", "jwtp-note"), btn = el("button", "jwtp-toggle");
    btn.type = "button"; btn.hidden = true;
    var pane = el("div", "jwtp-pane jwtp-wait");
    bar.appendChild(note); bar.appendChild(btn);
    box.appendChild(bar); box.appendChild(pane); box.appendChild(list);
    if (getComputedStyle(box).position === "static") { box.style.position = "relative"; }

    var mode = remembered() === "list" ? "list" : "trail";
    var state = "idle";   // idle, loading, ready, failed

    function apply() {
      var trail = mode === "trail" && state === "ready";
      pane.classList.toggle("jwtp-wait", state !== "ready");
      pane.hidden = !trail && state === "ready";
      list.hidden = trail;
      // while the diagram waits to scroll into view the book list is the whole box, so there is nothing to switch to yet
      btn.hidden = state === "failed" || state === "loading" || (state === "idle" && mode === "trail");
      btn.textContent = trail ? "Show the book list" : "Show the word trail";
      note.textContent = state === "loading" ? "Loading the word trail…" : "";
    }
    function start() {
      if (state !== "idle") { return; }
      state = "loading"; apply();
      openFrame(pane, word, "Word Trail: every use of " + word, function (ok) {
        state = ok ? "ready" : "failed";
        if (!ok) { pane.parentNode && pane.parentNode.removeChild(pane); mode = "list"; }
        apply();
      });
    }
    btn.addEventListener("click", function () {
      mode = mode === "trail" ? "list" : "trail";
      remembered(mode);
      if (mode === "trail" && state === "idle") { start(); } else { apply(); }
    });

    apply();
    if (mode === "list") { return; }   // chose the list last time: load nothing until asked
    if (typeof IntersectionObserver !== "function") { start(); return; }
    var io = new IntersectionObserver(function (es) {
      if (es.some(function (e) { return e.isIntersecting; })) { io.disconnect(); start(); }
    }, { rootMargin: "300px 0px" });
    io.observe(box);
  }

  // For a page that wants one by hand: no box, no button, just the picture for one word.
  function mount(host, word) {
    return new Promise(function (resolve) {
      openFrame(host, word, "Word Trail: every use of " + word, function (ok) { resolve(ok); });
    });
  }

  function scan(app) {
    prune();
    var boxes = app.querySelectorAll(".a-box"), i, lab, t, j;
    for (i = 0; i < boxes.length; i++) {
      lab = boxes[i].querySelector(".a-blabel");
      if (!lab) { continue; }
      t = firstText(lab);
      for (j = 0; j < TITLES.length; j++) { if (TITLES[j].test(t)) { attach(boxes[i], app); break; } }
    }
  }

  function style() {
    var s = el("style"); s.id = "jwtp-css";
    s.textContent =
      ".jwtp-bar{display:flex;align-items:center;justify-content:flex-end;gap:12px;margin:4px 0 12px;min-height:34px}" +
      ".jwtp-note{font-size:13px;color:var(--ink-soft,inherit)}" +
      ".jwtp-toggle{font:inherit;font-size:13px;line-height:1;padding:8px 15px;border-radius:999px;border:1px solid var(--gold,#cba96e);background:transparent;color:var(--gold,#cba96e);cursor:pointer}" +
      ".jwtp-toggle:hover{background:var(--paper-deeper,rgba(255,255,255,.06));color:var(--ink,#fff)}" +
      ".jwtp-toggle:focus-visible{outline:2px solid var(--gold,#cba96e);outline-offset:2px}" +
      ".jwtp-pane{width:100%}" +
      ".jwtp-pane[hidden],.jwtp-list[hidden]{display:none}" +
      ".jwtp-wait{position:absolute;left:0;right:0;top:0;height:720px;visibility:hidden;pointer-events:none;overflow:hidden}" +
      ".jwtp-frame{display:block;width:100%;height:560px;border:0;background:transparent}";
    document.head.appendChild(s);
  }

  function boot() {
    var app = document.getElementById("app");
    if (!app) { return; }
    style();
    var pending = false;
    function schedule() {
      if (pending) { return; }
      pending = true;
      requestAnimationFrame(function () { pending = false; try { scan(app); } catch (e) {} });
    }
    new MutationObserver(schedule).observe(app, { childList: true, subtree: true });
    scan(app);
  }

  window.jaymsWordTrailPanel = { mount: mount, scan: scan, wordFor: wordFor, strongsIn: strongsIn, pickWord: pickWord };
  if (document.readyState === "loading") { document.addEventListener("DOMContentLoaded", boot); } else { boot(); }
})();
</script>
	<?php
}, 30 );
