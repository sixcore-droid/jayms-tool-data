window.__PATCH = {"137": [["        el.classList.remove(\"a-loading\");\n        el.innerHTML = \"&ldquo;\" + esc(parts.join(\" \")) + \"&rdquo;\";\n        var cite = document.createElement(\"p\");\n        cite.innerHTML = esc(ref) + \" &middot; \" + esc(WITNESS_LABEL) + \" &middot; \" +\n          '<a href=\"' + esc(chapterURL(p.chapter)) + '\">read 1 Enoch ' +\n          esc(p.chapter) + \" in full &rarr;</a>\";\n        el.parentNode.insertBefore(cite, el.nextSibling);\n", "        el.classList.remove(\"a-loading\");\n        el.innerHTML = \"&ldquo;\" + esc(parts.join(\" \")) + \"&rdquo;\";\n\n        // where the passage came from is a footnote to it, not a second\n        // sentence of it, so it goes in the box's note at the note's size\n        var wrap = el.closest(\".a-box\");\n        var note = wrap ? wrap.querySelector(\".a-bnote\") : null;\n        if (!note && wrap) {\n          note = document.createElement(\"div\");\n          note.className = \"a-bnote\";\n          wrap.appendChild(note);\n        }\n        if (!note) return;\n        var cite = document.createElement(\"div\");\n        cite.innerHTML = esc(ref) + \" &middot; \" + esc(WITNESS_LABEL) + \" &middot; \" +\n          '<a href=\"' + esc(chapterURL(p.chapter)) + '\">read 1 Enoch ' +\n          esc(p.chapter) + \" in full &rarr;</a>\";\n        note.insertBefore(cite, note.firstChild);\n"]]};
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