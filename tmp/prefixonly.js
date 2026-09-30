window.__PATCH = {"137": [["    if (t === q) return 100;\n    if (t.indexOf(q) === 0) return 90;\n    // one or two letters is a reader jumping to a part of the alphabet,\n    // not describing what they want: only the start of the whole name\n    // counts, or \"e\" answers with every name containing an e, and\n    // \"Beer-elim\" turns up under E\n    if (q.length <= 2) return 0;\n    var parts = t.split(/[\\s,()'’–-]+/);\n    for (var i = 0; i < parts.length; i++) {\n      if (parts[i] && parts[i].indexOf(q) === 0) return 70;\n    }\n    if (t.indexOf(q) > -1) return 50;\n    // a letter out of place, or one wrong: \"ezekeil\" and \"damascas\" are\n    // the spellings people actually type\n    if (q.length >= 4) {\n      var d2 = dist(q, t);\n      if (d2 <= (q.length >= 6 ? 2 : 1)) return 40 - d2;\n    }\n    if (subseq(q, t)) return 30;\n    if (q.length >= 3) {\n      var fields = (DOC.search && DOC.search.fields) || [\"title\", \"summary\"];\n      for (var k = 0; k < fields.length; k++) {\n        var v = d[fields[k]];\n        if (fold(Array.isArray(v) ? v.join(\" \") : v).indexOf(q) > -1) return 10;\n      }\n    }\n    return 0;\n  }\n", "    if (t === q) return 100;\n    // Typing is a reader walking the alphabet to a name, so a query is the\n    // beginning of a name and nothing else. Matching inside one answers\n    // \"eno\" with Hazar-Enon, AEnon and Ishbi-Benob, which is not what\n    // anybody typing \"eno\" was after.\n    if (t.indexOf(q) === 0) return 90;\n    // the one exception: a name they have spelled almost right. \"ezekeil\"\n    // and \"damascas\" are the spellings people actually type, and edit\n    // distance refuses to stretch to a word of a different length.\n    if (q.length >= 4) {\n      var d2 = dist(q, t);\n      if (d2 <= (q.length >= 6 ? 2 : 1)) return 40 - d2;\n    }\n    return 0;\n  }\n"]]};
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