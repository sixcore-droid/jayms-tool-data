window.__REFS = {"css": [".a-linkrow{display:flex;flex-wrap:wrap;gap:8px;margin:0 0 13px}", ".a-linkrow{display:flex;flex-wrap:wrap;gap:8px;margin:0 0 13px}\n/* a book says its name once, at the head of its own line, and the chapter\n   and verse follow until the book changes */\n.a-linkrow .a-reflabel{flex:0 0 130px;align-self:center;\n  font-family:\"Archivo\",Arial,sans-serif;font-size:11px;text-transform:uppercase;\n  letter-spacing:.08em;color:var(--muted);padding-right:6px}\n@media(max-width:700px){.a-linkrow .a-reflabel{flex:0 0 100%;padding:0 0 2px}}"], "js": ["      var rows = books.map(function (book) {\n        var chips = byBook[book].map(function (ref) {\n          var u = typeof window.jaymsInterlinearURL === \"function\"\n            ? window.jaymsInterlinearURL(ref) : null;\n          return u ? '<a class=\"a-lnk\" href=\"' + esc(u) + '\">' + esc(ref) + \"</a>\"\n                   : '<span class=\"a-lnk\">' + esc(ref) + \"</span>\";\n        }).join(\"\");\n        return '<div class=\"a-linkrow\">' + chips + \"</div>\";\n      }).join(\"\");\n", "      var rows = books.map(function (book) {\n        var chips = byBook[book].map(function (ref) {\n          // the book is said once at the head of its line, so the chip\n          // carries only what changes: 4:14, 4:27-30, 5:1-4\n          var where = String(ref).slice(book.length).trim() || ref;\n          var u = typeof window.jaymsInterlinearURL === \"function\"\n            ? window.jaymsInterlinearURL(ref) : null;\n          return u ? '<a class=\"a-lnk\" href=\"' + esc(u) + '\">' + esc(where) + \"</a>\"\n                   : '<span class=\"a-lnk\">' + esc(where) + \"</span>\";\n        }).join(\"\");\n        return '<div class=\"a-linkrow\">' +\n          '<span class=\"a-reflabel\">' + esc(book) + \"</span>\" + chips + \"</div>\";\n      }).join(\"\");\n"]};
window.__refsFix = async function () {
  var log = [];
  var s = await wp.apiFetch({ path: '/code-snippets/v1/snippets/137' });
  var n = s.code.split(window.__REFS.js[0]).length - 1;
  if (n !== 1) { log.push('137 SKIPPED ' + n); }
  else {
    var code = s.code.replace(window.__REFS.js[0], function () { return window.__REFS.js[1]; });
    await wp.apiFetch({ path: '/code-snippets/v1/snippets/137', method: 'POST', data: { id: 137, code: code } });
    log.push('137 ok ' + s.code.length + '->' + code.length);
  }
  var gs = await wp.apiFetch({ path: '/wp/v2/global-styles/90171' });
  var m = gs.styles.css.split(window.__REFS.css[0]).length - 1;
  if (m !== 1) { log.push('90171 SKIPPED ' + m); return log.join(' ||| '); }
  var css = gs.styles.css.replace(window.__REFS.css[0], function () { return window.__REFS.css[1]; });
  var st = JSON.parse(JSON.stringify(gs.styles)); st.css = css;
  await wp.apiFetch({ path: '/wp/v2/global-styles/90171', method: 'POST', data: { styles: st } });
  log.push('90171 ok ' + gs.styles.css.length + '->' + css.length);
  return log.join(' ||| ');
};
"ready"