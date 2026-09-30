window.__PATCH = {"137": [["  function renderBlock(b, d) {\n    if (b.type === \"scripture\") return scriptureHTML(b, d);\n", "  // ------------------------------------------------------------ lexicon\n\n  // A Strong's number on its own tells a reader nothing. The entry behind\n  // it does, so a tool that names a lexicon source gets the word, how it is\n  // said, what it means, and how the King James rendered it.\n  var lexStore = null;\n\n  function loadLexicon() {\n    if (!lexStore && DOC.lexicon && DOC.lexicon.src) {\n      lexStore = fetch(DOC.lexicon.src)\n        .then(function (r) { return r.ok ? r.json() : null; })\n        .catch(function () { return null; });\n    }\n    return lexStore || Promise.resolve(null);\n  }\n\n  function fillLexicon() {\n    var slots = MOUNT.querySelectorAll(\"[data-lex]\");\n    if (!slots.length) return;\n    loadLexicon().then(function (lex) {\n      Array.prototype.forEach.call(slots, function (el) {\n        var keys = el.getAttribute(\"data-lex\").split(/[^A-Za-z0-9]+/)\n          .map(function (s) { return s.trim(); }).filter(Boolean);\n        var got = keys.map(function (k) { return { k: k, e: lex ? lex[k] : null }; })\n          .filter(function (x) { return x.e; });\n        if (!got.length) { el.classList.remove(\"a-loading\"); return; }\n        el.classList.remove(\"a-loading\");\n        el.innerHTML = got.map(function (x) {\n          var head = \"<b>\" + esc(x.k) + \"</b>\";\n          if (x.e.lemma) head += \" &middot; \" + esc(x.e.lemma);\n          if (x.e.xlit)  head += \" &middot; <i>\" + esc(x.e.xlit) + \"</i>\";\n          return head + (x.e.def ? \"<br>\" + esc(x.e.def) : \"\");\n        }).join(\"</p><p>\");\n        var kjv = got.map(function (x) { return x.e.kjv; }).filter(Boolean);\n        if (kjv.length) {\n          citeInto(el, \"Rendered in the King James as \" +\n            esc(kjv.join(\"; \").replace(/\\.$/, \"\")) + \".\");\n        }\n      });\n    });\n  }\n\n  function renderBlock(b, d) {\n    if (b.type === \"scripture\") return scriptureHTML(b, d);\n\n    if (b.type === \"lexicon\") {\n      var nums = d[b.field];\n      nums = Array.isArray(nums) ? nums.join(\" \") : (nums || \"\");\n      if (!nums) return \"\";\n      return '<div class=\"a-box v-' + esc(b.variant || \"wording\") + '\">' +\n        '<div class=\"a-blabel\">' + blockTitle(b, d) + \"</div>\" +\n        '<div class=\"a-btext\"><p class=\"a-loading\" data-lex=\"' + esc(nums) +\n        '\">Loading…</p></div></div>';\n    }\n"], ["    if (state.screen === \"reading\") fillReading();\n    fillWitnessRows();\n", "    if (state.screen === \"reading\") fillReading();\n    fillWitnessRows();\n    fillLexicon();\n"]]};
window.__applyPatch = async function () {
  var log = [];
  for (var sid in window.__PATCH) {
    var s = await wp.apiFetch({ path: '/code-snippets/v1/snippets/' + sid });
    var code = s.code, counts = [], ok = true;
    window.__PATCH[sid].forEach(function (pair) {
      var n = code.split(pair[0]).length - 1;
      counts.push(n);
      if (n !== 1) { ok = false; return; }
      code = code.replace(pair[0], function () { return pair[1]; });
    });
    if (!ok) { log.push(sid + ' SKIPPED counts=' + counts.join(',')); continue; }
    await wp.apiFetch({ path: '/code-snippets/v1/snippets/' + sid, method: 'POST',
      data: { id: +sid, code: code } });
    log.push(sid + ' ok counts=' + counts.join(',') + ' ' + s.code.length + '->' + code.length);
  }
  return log.join(' ||| ');
};
"ready"