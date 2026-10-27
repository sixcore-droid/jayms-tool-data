/**
 * JAYMS — Word Trail panel
 *
 * Two boxes in a tool entry are the same thing drawn twice, badly: "The word"
 * names one Hebrew word and one Greek word, and "Where it appears" lists every
 * verse as a row of chips. Word Trail already does both properly. It knows that
 * a Hebrew word can be rendered by several Greek words (erets by ge, chora and
 * oikoumene) and a Greek word can stand for several Hebrew ones, and it draws
 * every use as a picture. This puts each in its box:
 *
 *   - "The word" shows Word Trail's Hebrew and Greek word cards, with the choice
 *     of which Greek word to follow.
 *   - "Where it appears" shows the diagram for the word and Greek word chosen,
 *     with a button that swaps back to the book list the box always had.
 *
 * It is a component, not a patch. It does not touch the tool runner, the entry
 * pages or Word Trail's own files.
 *
 *   - It watches the tool page for the boxes (TITLES and WORD_TITLE below) and
 *     upgrades them in place. The runner redraws the page on every move, so it
 *     re-attaches each time.
 *   - The word comes from the entry itself: the "The word" box (the first Hebrew
 *     Strong's number, else the first Greek one), or the id in the address on
 *     Word Study (#id=g4458).
 *   - Word Trail runs in sealed frames. It reads and rewrites the page address
 *     as it works, and the runner keeps its place in the same address, so the
 *     two cannot share a page. Each frame has its own address and its own
 *     session storage, so neither can move the entry or fill your Word Trail
 *     history. The two frames are kept in step here: choosing a Greek word in
 *     the cards makes the diagram follow it.
 *   - If the word is unknown to Word Trail, nothing draws, or a frame is slow,
 *     the original box stays exactly as it was.
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

  // Boxes with one of these titles get the diagram. The box titled WORD_TITLE gets the word cards.
  var TITLES = [/^where it appears$/i];
  var WORD_TITLE = /^the word/i;
  var POINTER = "https://raw.githubusercontent.com/sixcore-droid/jayms-tool-data/main/word-trail-embed.json";
  var FALLBACK = "https://cdn.jsdelivr.net/gh/sixcore-droid/jayms-tool-data@6906736e110f88b1ac94a3f9a7dd63098143f0a0/word-trail/";
  var STORE = "jayms.wtpanel";
  var GIVE_UP = 25000;
  var live = [];
  var basePromise = null;
  // What the two frames of the entry on screen share: the word, and the last address the word cards were left at.
  var CTX = { key: null, word: "", hash: "", diagram: null };

  // ------------------------------------------------------------------ small things
  function remembered(v) {
    try { if (v === undefined) { return localStorage.getItem(STORE); } localStorage.setItem(STORE, v); } catch (e) {}
    return null;
  }
  function esc(s) { return String(s == null ? "" : s).replace(/[&<>"']/g, function (c) { return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]; }); }
  function el(tag, cls) { var e = document.createElement(tag); if (cls) { e.className = cls; } return e; }
  function firstText(node) { var n = node && node.childNodes && node.childNodes[0]; return n ? String(n.nodeValue || n.textContent || "").trim() : ""; }
  function whenVisible(node, go) {
    if (typeof IntersectionObserver !== "function") { go(); return; }
    var io = new IntersectionObserver(function (es) {
      if (es.some(function (e) { return e.isIntersecting; })) { io.disconnect(); go(); }
    }, { rootMargin: "300px 0px" });
    io.observe(node);
  }

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
    var boxes = app.querySelectorAll(".a-box"), i, j, lab, slots, ids, m;
    for (i = 0; i < boxes.length; i++) {
      lab = boxes[i].querySelector(".a-blabel");
      if (lab && WORD_TITLE.test(firstText(lab))) {
        // The Fact Book prints its numbers into a slot and fills the box a moment later, so the slot is the first
        // place to look. It is there from the first draw.
        slots = boxes[i].querySelectorAll("[data-lex]");
        for (j = 0; j < slots.length; j++) {
          ids = strongsIn(slots[j].getAttribute("data-lex"));
          if (ids.length) { return pickWord(ids); }
        }
        ids = strongsIn(boxes[i].innerText || boxes[i].textContent);
        if (ids.length) { return pickWord(ids); }
      }
    }
    m = /[#&]id=([hg])0*(\d{1,4})(?:&|$)/i.exec(hash || "");
    return m ? m[1].toUpperCase() + m[2] : "";
  }

  // ------------------------------------------------------------------ the sealed frames
  function getBase() {
    if (!basePromise) {
      basePromise = fetch(POINTER, { cache: "no-cache" }).then(function (r) { return r.ok ? r.json() : null; })
        .then(function (j) { return j && /^https:\/\//.test(j.base || "") ? j.base : FALLBACK; })
        .catch(function () { return FALLBACK; });
    }
    return basePromise;
  }

  // Both frames hide the search box, the history strip, the tabs, the side column and the footer: the entry already
  // names the word. The diagram frame also hides the word cards, which live in "The word" box. The cards frame hides
  // the diagram.
  var CSS_BASE =
    "html,body{margin:0!important;padding:0!important;background:transparent!important;min-height:0!important}" +
    ".jayms-word-trail{max-width:none!important;margin:0!important;padding:0!important}" +
    ".jayms-word-trail .wt-trail,.jayms-word-trail .wt-find,.jayms-word-trail .wt-sugg,.jayms-word-trail .wt-tabs," +
    ".jayms-word-trail .wt-side,.jayms-word-trail .a-foot{display:none!important}";
  var CSS_PARTS = {
    diagram: ".jayms-word-trail #wt-greek{display:none!important}",
    cards: ".jayms-word-trail #wt-main{display:none!important}"
  };

  function frameDocument(base, parts) {
    var css = [].map.call(document.head.querySelectorAll("link[rel=stylesheet],style"), function (n) { return n.outerHTML; }).join("\n");
    return "<!doctype html><html lang=\"en\"><head><meta charset=\"utf-8\"><base target=\"_top\">" + css +
      "<link rel=\"stylesheet\" href=\"" + esc(base) + "word-trail.css\"><style>" + CSS_BASE + CSS_PARTS[parts] + "</style></head>" +
      "<body class=\"" + esc(document.body.className) + "\">" +
      // Word Trail keeps a "words you looked at" strip in session storage. That is shared with the page, so give the frame its own empty one.
      "<script>try{Object.defineProperty(window,\"sessionStorage\",{value:{getItem:function(){return null},setItem:function(){},removeItem:function(){}}})}catch(e){}<\/script>" +
      "<div class=\"jayms-tool-alpha jayms-word-trail\" id=\"jayms-word-trail\" data-views=\"uses\"" + (parts === "cards" ? " data-parts=\"cards\"" : "") + "></div>" +
      "<script src=\"" + esc(base) + "word-trail.js\"><\/script></body></html>";
  }

  // hash is the whole address Word Trail starts from, e.g. "#w=H776&g=G5561&view=uses".
  function openFrame(host, hash, title, parts, done) {
    getBase().then(function (base) {
      var url = URL.createObjectURL(new Blob([frameDocument(base, parts)], { type: "text/html" }));
      var f = el("iframe", "jwtp-frame");
      f.setAttribute("title", title);
      f.setAttribute("loading", "eager");
      f.src = url + hash;
      host.appendChild(f);
      live.push({ host: host, url: url });
      watch(f, parts, function (ok, d) { if (ok) { autosize(f, d); } done(ok, f); });
    });
  }

  // Ready means what the frame is for has drawn: the book circles, or the word cards. Failed means it finished and
  // there is nothing to show, or it never finished.
  function watch(f, parts, finish) {
    var t0 = Date.now(), over = false;
    (function tick() {
      if (over) { return; }
      var d = null, main, greek, side;
      try { d = f.contentDocument; } catch (e) { d = null; }
      if (d) {
        main = d.getElementById("wt-main"); greek = d.getElementById("wt-greek"); side = d.getElementById("wt-side");
        if (parts === "cards") {
          if (greek && greek.querySelector(".wt-wb")) { over = true; return finish(true, d); }
          if ((side && /No word has the number/.test(side.textContent)) || (main && main.querySelector(".v-not"))) { over = true; return finish(false, d); }
        } else if (main && main.children.length && !main.querySelector(".wt-loading")) {
          over = true;
          return finish(!!main.querySelector("g.bn[data-book]"), d);
        }
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

  // ------------------------------------------------------------------ keeping the two frames in step
  // The word, the Greek word being followed, and where the word came from: the parts of Word Trail's address that
  // decide what is on screen. The rest (the verse picked, the view) belongs to one frame.
  function followed(h) {
    var keep = String(h || "").replace(/^#/, "").split("&").filter(function (p) { return /^(w|g|from|fb)=/.test(p); });
    keep.push("view=uses");
    return "#" + keep.join("&");
  }
  function startHash() { return CTX.hash ? followed(CTX.hash) : "#w=" + CTX.word + "&view=uses"; }
  function relay(h) {
    var df = CTX.diagram;
    if (!df || !df.isConnected) { return; }
    var next = followed(h);
    try { if (df.contentWindow.location.hash !== next) { df.contentWindow.location.hash = next; } } catch (e) {}
  }

  // ------------------------------------------------------------------ "The word" box: the word cards
  function attachCards(wbox, word) {
    if (wbox.getAttribute("data-wtc")) {
      if (wbox.querySelector(".jwtp-cards")) { return; }
      wbox.removeAttribute("data-wtc");   // the page rewrote the box under us: start again
    }
    var label = wbox.querySelector(".a-blabel");
    if (!label) { return; }
    wbox.setAttribute("data-wtc", "1");

    // what the box already held goes into one wrapper, untouched: the Fact Book is still filling its slot
    var old = el("div", "jwtp-oldword"), kids = [].slice.call(wbox.children), i;
    for (i = 0; i < kids.length; i++) { if (kids[i] !== label) { old.appendChild(kids[i]); } }
    var bar = el("div", "jwtp-wordbar"), back = el("button", "jwtp-toggle jwtp-back");
    back.type = "button"; back.hidden = true; back.textContent = "Back to " + word;
    bar.appendChild(back);
    var pane = el("div", "jwtp-pane jwtp-cards jwtp-wait");
    wbox.appendChild(bar); wbox.appendChild(pane); wbox.appendChild(old);
    if (getComputedStyle(wbox).position === "static") { wbox.style.position = "relative"; }

    function hook(f) {
      var win = f.contentWindow;
      win.addEventListener("hashchange", function () {
        var h = win.location.hash;
        CTX.hash = h;
        var w = (/[#&]w=([HG]\d+)/.exec(h) || [])[1] || "";
        back.hidden = !w || w === CTX.word;
        relay(h);
      });
    }
    back.addEventListener("click", function () {
      var f = pane.querySelector("iframe");
      if (f) { try { f.contentWindow.location.hash = "#w=" + CTX.word + "&view=uses"; } catch (e) {} }
    });

    whenVisible(wbox, function () {
      openFrame(pane, startHash(), "Word Trail: the word cards for " + word, "cards", function (ok, f) {
        if (!ok) { if (pane.parentNode) { pane.parentNode.removeChild(pane); } old.hidden = false; return; }
        pane.classList.remove("jwtp-wait");
        old.hidden = true;
        hook(f);
        if (!CTX.hash) { CTX.hash = f.contentWindow.location.hash; }
      });
    });
  }

  // ------------------------------------------------------------------ "Where it appears": the diagram
  function attach(box, word) {
    if (box.getAttribute("data-wtp")) { return; }
    var label = box.querySelector(".a-blabel");
    if (!word || !label) { return; }
    box.setAttribute("data-wtp", "1");

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
      openFrame(pane, startHash(), "Word Trail: every use of " + word, "diagram", function (ok, f) {
        state = ok ? "ready" : "failed";
        if (ok) { CTX.diagram = f; }
        else { if (pane.parentNode) { pane.parentNode.removeChild(pane); } mode = "list"; }
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
    whenVisible(box, start);
  }

  // For a page that wants one by hand: no box, no button, just the picture for one word.
  function mount(host, word) {
    return new Promise(function (resolve) {
      openFrame(host, "#w=" + encodeURIComponent(word) + "&view=uses", "Word Trail: every use of " + word, "diagram", function (ok) { resolve(ok); });
    });
  }

  function scan(app) {
    prune();
    var key = location.hash;
    if (CTX.key !== key) { CTX = { key: key, word: "", hash: "", diagram: null }; }
    // Once the word box is replaced its old text is hidden and unreadable, so the word is kept from the first look.
    var word = wordFor(app, key) || CTX.word;
    if (!word) { return; }
    CTX.word = word;
    var boxes = app.querySelectorAll(".a-box"), i, j, lab, t, wbox = null, wheres = [];
    for (i = 0; i < boxes.length; i++) {
      lab = boxes[i].querySelector(".a-blabel");
      if (!lab) { continue; }
      t = firstText(lab);
      if (WORD_TITLE.test(t)) { if (!wbox) { wbox = boxes[i]; } continue; }
      for (j = 0; j < TITLES.length; j++) { if (TITLES[j].test(t)) { wheres.push(boxes[i]); break; } }
    }
    if (wbox) { attachCards(wbox, word); }
    for (i = 0; i < wheres.length; i++) { attach(wheres[i], word); }
  }

  function style() {
    var s = el("style"); s.id = "jwtp-css";
    s.textContent =
      // the count beside "Where it appears" counts passages, the diagram counts verses, and the two never agree
      ".a-box[data-wtp]>.a-blabel .a-bcount{display:none}" +
      ".jwtp-bar{display:flex;align-items:center;justify-content:flex-end;gap:12px;margin:4px 0 12px;min-height:34px}" +
      ".jwtp-wordbar{display:flex;justify-content:flex-end;margin:0}" +
      ".jwtp-wordbar:has(.jwtp-back[hidden]){display:none}" +
      ".jwtp-note{font-size:13px;color:var(--ink-soft,inherit)}" +
      ".jwtp-toggle{font:inherit;font-size:13px;line-height:1;padding:8px 15px;border-radius:999px;border:1px solid var(--gold,#cba96e);background:transparent;color:var(--gold,#cba96e);cursor:pointer}" +
      ".jwtp-back{margin:2px 0 10px}" +
      ".jwtp-toggle:hover{background:var(--paper-deeper,rgba(255,255,255,.06));color:var(--ink,#fff)}" +
      ".jwtp-toggle:focus-visible{outline:2px solid var(--gold,#cba96e);outline-offset:2px}" +
      ".jwtp-pane{width:100%}" +
      ".jwtp-pane[hidden],.jwtp-list[hidden],.jwtp-oldword[hidden]{display:none}" +
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

  window.jaymsWordTrailPanel = { mount: mount, scan: scan, wordFor: wordFor, strongsIn: strongsIn, pickWord: pickWord, followed: followed };
  if (document.readyState === "loading") { document.addEventListener("DOMContentLoaded", boot); } else { boot(); }
})();
</script>
	<?php
}, 30 );
