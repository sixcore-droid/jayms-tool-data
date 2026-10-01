window.__REV = ["/* The question is what a reader scans, so it is the loud line: full size,\n   white, set as a sentence rather than a label. The answer follows it a\n   step quieter. It read the other way round before -- the answer was white\n   and larger than the claim it was answering. */\n.jayms-tool-alpha .lay-rail.row-stack .a-clabel{flex:0 0 100%;margin:0 0 2px;\n  font-size:17px;line-height:1.4;text-transform:none;letter-spacing:normal;\n  color:var(--ink-bright)}\n.jayms-tool-alpha .lay-rail.row-stack .a-cmain{flex:1 1 0;min-width:0;\n  /* .jayms-tool-alpha .a-cmain is white !important, from when a row's text\n     was the only text in it. Here it is the answer under a question, so it\n     steps down one to the body cream -- still fully readable, just not\n     competing with the line it answers. */\n  font-size:15.5px;color:var(--ink) !important}", "/* Stacking changes where the two lines sit, and nothing else: the label\n   keeps the gold small-caps it has in every other tool, the text keeps its\n   white. Colour is what already tells them apart. */\n.jayms-tool-alpha .lay-rail.row-stack .a-clabel{flex:0 0 100%;margin:0 0 1px}\n.jayms-tool-alpha .lay-rail.row-stack .a-cmain{flex:1 1 0;min-width:0}"];
window.__revert = async function () {
  var gs = await wp.apiFetch({ path: '/wp/v2/global-styles/90171' });
  var n = gs.styles.css.split(window.__REV[0]).length - 1;
  if (n !== 1) return '90171 SKIPPED count=' + n;
  var css = gs.styles.css.replace(window.__REV[0], function () { return window.__REV[1]; });
  var st = JSON.parse(JSON.stringify(gs.styles)); st.css = css;
  await wp.apiFetch({ path: '/wp/v2/global-styles/90171', method: 'POST', data: { styles: st } });
  return '90171 ok ' + gs.styles.css.length + '->' + css.length;
};
"ready"