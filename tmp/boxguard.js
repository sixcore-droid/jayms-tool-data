window.__PATCH = {"137": [["    var v = d[b.field];\n    if (!v || (Array.isArray(v) && !v.length)) return \"\";\n    var body;\n    if (Array.isArray(v)) {\n      var tag = b.list === \"ordered\" ? \"ol\" : \"ul\";\n      body = \"<\" + tag + \">\" + v.map(function (x) { return \"<li>\" + esc(x) + \"</li>\"; }).join(\"\") + \"</\" + tag + \">\";\n    } else {\n      body = b.quote ? \"<p>&ldquo;\" + esc(v) + \"&rdquo;</p>\" : rich(v);\n    }\n    if (b.quoteField && d[b.quoteField]) body += witnessSlot(d[b.quoteField]);\n", "    // a box may carry prose, a quoted passage, or both; it only disappears\n    // when it would have nothing at all in it\n    var v = b.field ? d[b.field] : null;\n    var q = b.quoteField ? d[b.quoteField] : null;\n    var hasV = v && (!Array.isArray(v) || v.length);\n    var hasQ = q && (!Array.isArray(q) || q.length);\n    if (!hasV && !hasQ) return \"\";\n    var body = \"\";\n    if (hasV && Array.isArray(v)) {\n      var tag = b.list === \"ordered\" ? \"ol\" : \"ul\";\n      body = \"<\" + tag + \">\" + v.map(function (x) { return \"<li>\" + esc(x) + \"</li>\"; }).join(\"\") + \"</\" + tag + \">\";\n    } else if (hasV) {\n      body = b.quote ? \"<p>&ldquo;\" + esc(v) + \"&rdquo;</p>\" : rich(v);\n    }\n    if (hasQ) body += witnessSlot(q);\n"]]};
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