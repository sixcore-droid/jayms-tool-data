window.__TYPO = [".a-linkrow .a-reflabel{flex:0 0 130px;align-self:center;\n  font-family:\"Archivo\",Arial,sans-serif;font-size:11px;text-transform:uppercase;\n  letter-spacing:.08em;color:var(--muted);padding-right:6px}\n@media(max-width:700px){.a-linkrow .a-reflabel{flex:0 0 100%;padding:0 0 2px}}", ".a-linkrow .a-reflabel{flex:0 0 100%;margin:4px 0 2px;\n  font-family:\"Archivo\",Arial,sans-serif;font-size:12px;text-transform:uppercase;\n  letter-spacing:.09em;color:var(--ink)}\n\n/* Long-form reading, wherever it turns up: the face, size and measure of\n   the Interleaved Bible's reader, which is Archivo. Cormorant is a display\n   serif -- its thin strokes hold up in a heading and disappear across a\n   chapter of prose on a dark ground. */\n.jayms-tool-alpha .a-box .a-witness-full,\n.jayms-tool-alpha #jaymsAlphaReading .a-btext{\n  font-family:\"Archivo\",system-ui,-apple-system,Arial,sans-serif;\n  font-size:18px;line-height:1.7;color:var(--ink)}\n.jayms-tool-alpha .a-box .a-witness-full p,\n.jayms-tool-alpha #jaymsAlphaReading .a-btext p{margin:0 0 15px;max-width:76ch}\n.jayms-tool-alpha .a-box .a-witness-full .a-blabel{margin:0 0 12px}"];
window.__typoFix = async function () {
  var gs = await wp.apiFetch({ path: '/wp/v2/global-styles/90171' });
  var n = gs.styles.css.split(window.__TYPO[0]).length - 1;
  if (n !== 1) return '90171 SKIPPED count=' + n;
  var css = gs.styles.css.replace(window.__TYPO[0], function () { return window.__TYPO[1]; });
  var st = JSON.parse(JSON.stringify(gs.styles)); st.css = css;
  await wp.apiFetch({ path: '/wp/v2/global-styles/90171', method: 'POST', data: { styles: st } });
  return '90171 ok ' + gs.styles.css.length + '->' + css.length;
};
"ready"