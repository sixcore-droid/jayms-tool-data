window.__PATCH = {"137": [["    if (!r) return '<div class=\"a-cards lay-rail\">' + slice.map(cardHTML).join(\"\") + \"</div>\";\n", "    if (!r) return '<div class=\"a-cards' + cardsClass() + '\">' + slice.map(cardHTML).join(\"\") + \"</div>\";\n"], ["        '</span></div><div class=\"a-cards lay-rail\">' + g.rows.map(cardHTML).join(\"\") +\n", "        '</span></div><div class=\"a-cards' + cardsClass() + '\">' + g.rows.map(cardHTML).join(\"\") +\n"]]};
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