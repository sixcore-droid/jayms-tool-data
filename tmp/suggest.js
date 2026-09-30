window.__PATCH = {"137": [["    badge:   (MOUNT.dataset.badge || \"on\").trim() !== \"off\",\n", "    badge:   (MOUNT.dataset.badge || \"on\").trim() !== \"off\",\n    suggest: (MOUNT.dataset.suggest || \"on\").trim() !== \"off\",\n"], ["  function suggestHTML() {\n    var parts = footerParts(\"suggest\");\n    return parts ? '<div class=\"a-suggest\">' + parts + \"</div>\" : \"\";\n  }\n", "  function suggestHTML() {\n    // what it asks for is the tool's own, whether to ask at all is the\n    // page's: data-suggest=\"off\"\n    if (!PAGE.suggest) return \"\";\n    var parts = footerParts(\"suggest\");\n    return parts ? '<div class=\"a-suggest\">' + parts + \"</div>\" : \"\";\n  }\n"]]};
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