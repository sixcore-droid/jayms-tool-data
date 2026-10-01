window.__PATCH = {"137": [["      (!PAGE.badge || card.badge === false ? \"\" :\n        '<span class=\"a-badge\" style=\"--cc:' + colourVar(c && c.c) + '\">' +\n        esc(c ? c.n : d.category) + \"</span>\") +\n", "      // the badge repeats on every row, so it takes the short form too;\n      // the chip above the list carries the whole phrase and the count\n      (!PAGE.badge || card.badge === false ? \"\" :\n        '<span class=\"a-badge\" style=\"--cc:' + colourVar(c && c.c) + '\">' +\n        esc(c ? (c.short || c.n) : d.category) + \"</span>\") +\n"]]};
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