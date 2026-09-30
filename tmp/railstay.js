window.__PATCH = {"137": [["    // a search is answered best first, so the alphabet stops organising it:\n    // grouping by letter would put Abel above Enoch for \"enoc\"\n    if (!r || state.q) {\n      return '<div class=\"a-cards lay-rail\">' + slice.map(cardHTML).join(\"\") + \"</div>\";\n    }\n", "    // The rail stays put while you type. Pulling it out mid-search reflows\n    // the whole screen under the reader's eyes, which costs more than the\n    // ordering gains: the ranking still decides which rows reach the page.\n    if (!r) return '<div class=\"a-cards lay-rail\">' + slice.map(cardHTML).join(\"\") + \"</div>\";\n"]]};
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