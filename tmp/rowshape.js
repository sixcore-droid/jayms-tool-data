window.__ROW = {"js": [["    sub:     (MOUNT.dataset.sub || \"\").trim()\n  };\n", "    sub:     (MOUNT.dataset.sub || \"\").trim(),\n    row:     (MOUNT.dataset.row || \"columns\").trim().toLowerCase()\n  };\n"], ["  function cardsClass() {\n    var c = \"\";\n    if (PAGE.layout === \"rail\") return \" lay-rail\";\n", "  function cardsClass() {\n    var c = \"\";\n    // A row holds two things: what the entry is, and what it says. Side by\n    // side suits a short label like a reference; stacked suits a label that\n    // is itself a sentence, which a narrow column would set as a ladder.\n    if (PAGE.layout === \"rail\") {\n      return \" lay-rail\" + (PAGE.row === \"stack\" ? \" row-stack\" : \"\");\n    }\n"]], "css": [".jayms-tool-alpha .lay-rail .a-clabel{flex:0 0 172px}", ".jayms-tool-alpha .lay-rail .a-clabel{flex:0 0 172px}\n/* data-row=\"stack\": the label is a question, so it takes the line and the\n   answer sits under it */\n.jayms-tool-alpha .lay-rail.row-stack .a-card{flex-wrap:wrap;gap:6px 14px}\n.jayms-tool-alpha .lay-rail.row-stack .a-clabel{flex:0 0 100%;font-size:13px;\n  letter-spacing:.06em;color:var(--ink);margin:0}\n.jayms-tool-alpha .lay-rail.row-stack .a-cmain{flex:1 1 auto}"]};
window.__rowShape = async function () {
  var log = [];
  var s = await wp.apiFetch({ path: '/code-snippets/v1/snippets/137' });
  var code = s.code, counts = [], ok = true;
  window.__ROW.js.forEach(function (p) {
    var n = code.split(p[0]).length - 1; counts.push(n);
    if (n !== 1) { ok = false; return; }
    code = code.replace(p[0], function () { return p[1]; });
  });
  if (!ok) { log.push('137 SKIPPED ' + counts.join(',')); }
  else {
    await wp.apiFetch({ path: '/code-snippets/v1/snippets/137', method: 'POST', data: { id: 137, code: code } });
    log.push('137 ok ' + counts.join(',') + ' ' + s.code.length + '->' + code.length);
  }
  var gs = await wp.apiFetch({ path: '/wp/v2/global-styles/90171' });
  var m = gs.styles.css.split(window.__ROW.css[0]).length - 1;
  if (m !== 1) { log.push('90171 SKIPPED ' + m); return log.join(' ||| '); }
  var css = gs.styles.css.replace(window.__ROW.css[0], function () { return window.__ROW.css[1]; });
  var st = JSON.parse(JSON.stringify(gs.styles)); st.css = css;
  await wp.apiFetch({ path: '/wp/v2/global-styles/90171', method: 'POST', data: { styles: st } });
  log.push('90171 ok ' + gs.styles.css.length + '->' + css.length);
  return log.join(' ||| ');
};
"ready"