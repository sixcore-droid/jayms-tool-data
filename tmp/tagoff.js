window.__PATCH = {"137": [["    var tags = visibleFilters().filter(function (r) {\n      return r.role !== \"category\" && r.field !== railField;\n    })\n", "    var tags = visibleFilters().filter(function (r) {\n      // a row may be worth filtering by and not worth repeating on every\n      // row; the category is already the badge, and the rail is the heading\n      return r.role !== \"category\" && r.field !== railField && r.tag !== false;\n    })\n"]]};
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