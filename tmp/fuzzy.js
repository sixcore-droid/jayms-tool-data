window.__PATCH = {"137": [["  function searchScore(d) {\n", "  // how many single-letter edits turn one word into the other, bailing out\n  // as soon as the two are too far apart in length to be the same word\n  function dist(a, b) {\n    var m = a.length, n = b.length, prev = [], cur = [], i, j;\n    if (Math.abs(m - n) > 2) return 9;\n    for (j = 0; j <= n; j++) prev[j] = j;\n    for (i = 1; i <= m; i++) {\n      cur[0] = i;\n      for (j = 1; j <= n; j++) {\n        cur[j] = Math.min(prev[j] + 1, cur[j - 1] + 1,\n          prev[j - 1] + (a.charAt(i - 1) === b.charAt(j - 1) ? 0 : 1));\n      }\n      for (j = 0; j <= n; j++) prev[j] = cur[j];\n    }\n    return prev[n];\n  }\n\n  function searchScore(d) {\n"], ["    if (t === q) return 100;\n    if (t.indexOf(q) === 0) return 90;\n    var parts = t.split(/[\\s,()'’–-]+/);\n    for (var i = 0; i < parts.length; i++) {\n      if (parts[i] && parts[i].indexOf(q) === 0) return 70;\n    }\n    // one or two letters is a reader jumping to a part of the alphabet,\n    // not describing what they want: only the beginning of a name counts,\n    // or \"e\" answers with every name that happens to contain an e\n    if (q.length <= 2) return 0;\n    if (t.indexOf(q) > -1) return 50;\n    if (subseq(q, t)) return 30;\n", "    if (t === q) return 100;\n    if (t.indexOf(q) === 0) return 90;\n    // one or two letters is a reader jumping to a part of the alphabet,\n    // not describing what they want: only the start of the whole name\n    // counts, or \"e\" answers with every name containing an e, and\n    // \"Beer-elim\" turns up under E\n    if (q.length <= 2) return 0;\n    var parts = t.split(/[\\s,()'’–-]+/);\n    for (var i = 0; i < parts.length; i++) {\n      if (parts[i] && parts[i].indexOf(q) === 0) return 70;\n    }\n    if (t.indexOf(q) > -1) return 50;\n    // a letter out of place, or one wrong: \"ezekeil\" and \"damascas\" are\n    // the spellings people actually type\n    if (q.length >= 4) {\n      var d2 = dist(q, t);\n      if (d2 <= (q.length >= 6 ? 2 : 1)) return 40 - d2;\n    }\n    if (subseq(q, t)) return 30;\n"], ["    if (!r) return '<div class=\"a-cards lay-rail\">' + slice.map(cardHTML).join(\"\") + \"</div>\";\n", "    // a search is answered best first, so the alphabet stops organising it:\n    // grouping by letter would put Abel above Enoch for \"enoc\"\n    if (!r || state.q) {\n      return '<div class=\"a-cards lay-rail\">' + slice.map(cardHTML).join(\"\") + \"</div>\";\n    }\n"]]};
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