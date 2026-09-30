window.__PATCH = {"137": [["    return FACTBOOK + \"#v=\" + encodeURIComponent(JSON.stringify({\n      screen: \"reading\", kind: \"witness\", book: book,\n      bookLabel: book, chapterCount: 108, chapter: +chapter\n    }));\n", "    // the Fact Book is the reader for every tool that does not carry its\n    // own, and it reads the same fragment this runner writes\n    return FACTBOOK + \"#read=\" + encodeURIComponent(book) + \"|\" + encodeURIComponent(chapter);\n"]]};
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