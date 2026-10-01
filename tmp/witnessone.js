window.__PATCH = {"137": [["        var cite = document.createElement(\"div\");\n        cite.innerHTML = esc(ref) + \" &middot; \" + esc(WITNESS_LABEL) + \" &middot; \" +\n          '<a href=\"' + esc(chapterURL(p.chapter)) + '\">read 1 Enoch ' +\n          esc(p.chapter) + \" in full &rarr;</a>\";\n        note.insertBefore(cite, note.firstChild);\n", "        // the same offer as the other witness shape: open it here when this\n        // tool carries the texts, link across when it does not\n        var cite = document.createElement(\"div\");\n        var how = (DOC.reading && DOC.reading.src)\n          ? '<button class=\"a-lnk\" onclick=\"jaymsAlphaOpenWitness(this,' +\n            jsArg(\"1 Enoch\") + ',' + jsArg(String(p.chapter)) + ')\">read 1 Enoch ' +\n            esc(p.chapter) + \" in full &darr;</button>\"\n          : '<a href=\"' + esc(chapterURL(p.chapter)) + '\">read 1 Enoch ' +\n            esc(p.chapter) + \" in full &rarr;</a>\";\n        cite.innerHTML = esc(ref) + \" &middot; \" + esc(WITNESS_LABEL) + \" &middot; \" + how;\n        note.insertBefore(cite, note.firstChild);\n"]]};
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