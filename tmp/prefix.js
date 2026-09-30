window.__PATCH = {"137": [["    if (t.indexOf(q) > -1) return 50;\n    if (q.length >= 3 && subseq(q, t)) return 30;\n", "    // one or two letters is a reader jumping to a part of the alphabet,\n    // not describing what they want: only the beginning of a name counts,\n    // or \"e\" answers with every name that happens to contain an e\n    if (q.length <= 2) return 0;\n    if (t.indexOf(q) > -1) return 50;\n    if (subseq(q, t)) return 30;\n"]]};
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