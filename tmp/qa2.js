window.__QA2 = [".jayms-tool-alpha .lay-rail.row-stack .a-cmain{flex:1 1 0;min-width:0;\n  font-size:15.5px;color:var(--ink-soft)}", ".jayms-tool-alpha .lay-rail.row-stack .a-cmain{flex:1 1 0;min-width:0;\n  /* .jayms-tool-alpha .a-cmain is white !important, from when a row's text\n     was the only text in it. Here it is the answer under a question, so it\n     steps down one to the body cream -- still fully readable, just not\n     competing with the line it answers. */\n  font-size:15.5px;color:var(--ink) !important}"];
window.__qa2 = async function () {
  var gs = await wp.apiFetch({ path: '/wp/v2/global-styles/90171' });
  var n = gs.styles.css.split(window.__QA2[0]).length - 1;
  if (n !== 1) return '90171 SKIPPED count=' + n;
  var css = gs.styles.css.replace(window.__QA2[0], function () { return window.__QA2[1]; });
  var st = JSON.parse(JSON.stringify(gs.styles)); st.css = css;
  await wp.apiFetch({ path: '/wp/v2/global-styles/90171', method: 'POST', data: { styles: st } });
  return '90171 ok ' + gs.styles.css.length + '->' + css.length;
};
"ready"