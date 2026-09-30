window.__TYPO2 = [".jayms-tool-alpha .a-box .a-witness-full,\n.jayms-tool-alpha #jaymsAlphaReading .a-btext{\n  font-family:\"Archivo\",system-ui,-apple-system,Arial,sans-serif;\n  font-size:18px;line-height:1.7;color:var(--ink)}\n.jayms-tool-alpha .a-box .a-witness-full p,\n.jayms-tool-alpha #jaymsAlphaReading .a-btext p{margin:0 0 15px;max-width:76ch}", ".jayms-tool-alpha .a-box .a-witness-full,\n.jayms-tool-alpha .a-box .a-witness-full p,\n.jayms-tool-alpha #jaymsAlphaReading .a-btext,\n.jayms-tool-alpha #jaymsAlphaReading .a-btext p{\n  /* the site sets \"body, p, li\" with !important, so a paragraph is hit\n     directly and never inherits this -- it has to be answered in kind */\n  font-family:\"Archivo\",system-ui,-apple-system,Arial,sans-serif !important;\n  font-size:18px;line-height:1.7;color:var(--ink)}\n.jayms-tool-alpha .a-box .a-witness-full p,\n.jayms-tool-alpha #jaymsAlphaReading .a-btext p{margin:0 0 15px;max-width:76ch}"];
window.__typo2 = async function () {
  var gs = await wp.apiFetch({ path: '/wp/v2/global-styles/90171' });
  var n = gs.styles.css.split(window.__TYPO2[0]).length - 1;
  if (n !== 1) return '90171 SKIPPED count=' + n;
  var css = gs.styles.css.replace(window.__TYPO2[0], function () { return window.__TYPO2[1]; });
  var st = JSON.parse(JSON.stringify(gs.styles)); st.css = css;
  await wp.apiFetch({ path: '/wp/v2/global-styles/90171', method: 'POST', data: { styles: st } });
  return '90171 ok ' + gs.styles.css.length + '->' + css.length;
};
"ready"