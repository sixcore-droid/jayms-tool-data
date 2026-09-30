window.__PATCH = {"137": [["        return u ? '<a class=\"a-lnk\" href=\"' + esc(u) + '\" target=\"_blank\" rel=\"noopener\">' +\n          esc(p) + \" &middot; NET + interlinear &rsaquo;</a>\" : \"\";\n", "        // the screen this opens is the original with its glosses, so it is\n        // not any translation's reading and naming one was never right\n        return u ? '<a class=\"a-lnk\" href=\"' + esc(u) + '\" target=\"_blank\" rel=\"noopener\">' +\n          esc(p) + \" &middot; interlinear &rsaquo;</a>\" : \"\";\n"]]};
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