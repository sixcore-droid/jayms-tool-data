window.__PATCH = {"137": [["  function renderBlock(b, d) {\n    if (b.type === \"scripture\") return scriptureHTML(b, d);\n", "  // \"Exodus 4:27, 4:28, 4:29, 4:30\" is one place in the text, not four.\n  // A run of verses in the same chapter folds into a range, and a gap only\n  // breaks the run when three or more verses are missing: fewer than that\n  // and it is still the same passage with a verse or two skipped. Anything\n  // that is not a plain book chapter:verse is left exactly as it was.\n  function collapseRefs(list) {\n    var out = [], run = null;\n\n    function flush() {\n      if (!run) return;\n      out.push(run.from === run.to\n        ? run.book + \" \" + run.chapter + \":\" + run.from\n        : run.book + \" \" + run.chapter + \":\" + run.from + \"-\" + run.to);\n      run = null;\n    }\n\n    (list || []).forEach(function (raw) {\n      var m = String(raw).match(/^(.+?)\\s+(\\d+):(\\d+)$/);\n      if (!m) { flush(); out.push(String(raw)); return; }\n      var book = m[1], chapter = m[2], v = parseInt(m[3], 10);\n      if (run && run.book === book && run.chapter === chapter &&\n          v > run.to && v - run.to <= 3) {\n        run.to = v;\n        return;\n      }\n      flush();\n      run = { book: book, chapter: chapter, from: v, to: v };\n    });\n    flush();\n    return out;\n  }\n\n  function renderBlock(b, d) {\n    if (b.type === \"scripture\") return scriptureHTML(b, d);\n"], ["    if (b.type === \"refs\") {\n      var list = d[b.field] || [];\n      if (!list.length) return \"\";\n      var chips = list.map(function (ref) {\n", "    if (b.type === \"refs\") {\n      var list = collapseRefs(d[b.field] || []);\n      if (!list.length) return \"\";\n      var chips = list.map(function (ref) {\n"]]};
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