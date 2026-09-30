window.__PATCH = {"116": [["  window.jaymsFetchVerse = function(reference, version){\n", "  // exposed so a caller that has to walk a range verse by verse -- the\n  // Alpha runner and the Interleaved Bible both do -- can put the reference\n  // into the same shape first, instead of keeping its own book table\n  window.jaymsNormRef = normRef;\n\n  window.jaymsFetchVerse = function(reference, version){\n"]], "137": [["  function fetchPart(ref, version) {\n    var key = version + \"|\" + ref;\n", "  function fetchPart(ref, version) {\n    // \"Jude 14-15\" has to become \"Jude 1:14-15\" before a range can be\n    // walked, and \"Psalm 82:1\" has to become \"Psalms 82:1\" before the WEB\n    // and KJV files can be opened at all\n    if (typeof window.jaymsNormRef === \"function\") ref = window.jaymsNormRef(ref);\n    var key = version + \"|\" + ref;\n"]]};
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