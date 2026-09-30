window.__PATCH = {"137": [["        if (!got.length) { el.classList.remove(\"a-loading\"); return; }\n", "        // no lexicon declared, or nothing matched: show what the entry\n        // actually said rather than leaving \"Loading\" on screen for ever\n        if (!got.length) {\n          el.classList.remove(\"a-loading\");\n          el.textContent = keys.join(\" · \") || el.getAttribute(\"data-lex\");\n          return;\n        }\n"]]};
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