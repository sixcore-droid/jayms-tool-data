window.__ROWFIX = {"css": [".jayms-tool-alpha .lay-rail .a-meta{flex:0 0 auto;margin:0}", ".jayms-tool-alpha .lay-rail .a-meta{flex:0 0 auto;margin:0}\n/* the row gives the summary three lines and then stops; a row is an index\n   entry, not the entry itself */\n.jayms-tool-alpha .lay-rail .a-cmain{display:-webkit-box;-webkit-box-orient:vertical;\n  -webkit-line-clamp:3;overflow:hidden;white-space:normal}\n/* and the tags sit in columns, so the eye reads down them */\n.jayms-tool-alpha .lay-rail .a-meta{display:flex;align-items:center;gap:8px}\n.jayms-tool-alpha .lay-rail .a-meta .a-tag{min-width:5.4em;text-align:center}"], "js": ["        return o ? '<span class=\"a-tag\">' + esc(o.n) + \"</span>\" : \"\";\n", "        // a row tag has a column to fit; the chip above it has the sentence\n        return o ? '<span class=\"a-tag\">' + esc(o.short || o.n) + \"</span>\" : \"\";\n"]};
window.__rowFix = async function () {
  var log = [];
  var s = await wp.apiFetch({ path: '/code-snippets/v1/snippets/137' });
  var n = s.code.split(window.__ROWFIX.js[0]).length - 1;
  if (n !== 1) { log.push('137 SKIPPED ' + n); }
  else {
    var code = s.code.replace(window.__ROWFIX.js[0], function () { return window.__ROWFIX.js[1]; });
    await wp.apiFetch({ path: '/code-snippets/v1/snippets/137', method: 'POST', data: { id: 137, code: code } });
    log.push('137 ok ' + s.code.length + '->' + code.length);
  }
  var gs = await wp.apiFetch({ path: '/wp/v2/global-styles/90171' });
  var m = gs.styles.css.split(window.__ROWFIX.css[0]).length - 1;
  if (m !== 1) { log.push('90171 SKIPPED ' + m); return log.join(' ||| '); }
  var css = gs.styles.css.replace(window.__ROWFIX.css[0], function () { return window.__ROWFIX.css[1]; });
  var st = JSON.parse(JSON.stringify(gs.styles)); st.css = css;
  await wp.apiFetch({ path: '/wp/v2/global-styles/90171', method: 'POST', data: { styles: st } });
  log.push('90171 ok ' + gs.styles.css.length + '->' + css.length);
  return log.join(' ||| ');
};
"ready"