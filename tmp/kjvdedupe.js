window.__PATCH = {"137": [["        var kjv = got.map(function (x) { return x.e.kjv; }).filter(Boolean);\n        if (kjv.length) {\n          citeInto(el, \"Rendered in the King James as \" +\n            esc(kjv.join(\"; \").replace(/\\.$/, \"\")) + \".\");\n        }\n", "        // a Hebrew word and its Greek counterpart usually carry the same\n        // King James rendering, and saying \"Aaron; Aaron\" helps nobody\n        var kjv = [];\n        got.forEach(function (x) {\n          var v = String(x.e.kjv || \"\").replace(/\\.\\s*$/, \"\").trim();\n          if (v && kjv.indexOf(v) === -1) kjv.push(v);\n        });\n        if (kjv.length) {\n          citeInto(el, \"Rendered in the King James as \" + esc(kjv.join(\"; \")) + \".\");\n        }\n"]]};
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