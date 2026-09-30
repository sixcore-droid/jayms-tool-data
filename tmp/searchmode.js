window.__PATCH = {"137": [["    // the one exception: a name they have spelled almost right. \"ezekeil\"\n    // and \"damascas\" are the spellings people actually type, and edit\n    // distance refuses to stretch to a word of a different length.\n    if (q.length >= 4) {\n      var d2 = dist(q, t);\n      if (d2 <= (q.length >= 6 ? 2 : 1)) return 40 - d2;\n    }\n    return 0;\n  }\n", "    // A list of names is walked by its spelling; a set of passages is\n    // searched by what it is about. Which of the two a tool is belongs in\n    // its data, not in here. \"text\" stays the default because it is what\n    // every tool did before either mode existed.\n    if (((DOC.search && DOC.search.mode) || \"text\") === \"name\") {\n      // the one exception to prefix: a name spelled almost right.\n      // \"ezekeil\" and \"damascas\" are what people actually type, and edit\n      // distance refuses to stretch to a word of a different length.\n      if (q.length >= 4) {\n        var d2 = dist(q, t);\n        if (d2 <= (q.length >= 6 ? 2 : 1)) return 40 - d2;\n      }\n      return 0;\n    }\n\n    if (t.indexOf(q) > -1) return 50;\n    var fields = (DOC.search && DOC.search.fields) || [\"title\", \"summary\"];\n    for (var k = 0; k < fields.length; k++) {\n      var v = d[fields[k]];\n      if (fold(Array.isArray(v) ? v.join(\" \") : v).indexOf(q) > -1) return 10;\n    }\n    return 0;\n  }\n"]]};
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