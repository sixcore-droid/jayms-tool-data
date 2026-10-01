window.__QA = [".jayms-tool-alpha .lay-rail.row-stack .a-card{flex-wrap:wrap;gap:6px 14px}\n.jayms-tool-alpha .lay-rail.row-stack .a-clabel{flex:0 0 100%;font-size:13px;\n  letter-spacing:.06em;color:var(--ink);margin:0}\n.jayms-tool-alpha .lay-rail.row-stack .a-cmain{flex:1 1 auto}", ".jayms-tool-alpha .lay-rail.row-stack .a-card{flex-wrap:wrap;gap:4px 14px}\n/* The question is what a reader scans, so it is the loud line: full size,\n   white, set as a sentence rather than a label. The answer follows it a\n   step quieter. It read the other way round before -- the answer was white\n   and larger than the claim it was answering. */\n.jayms-tool-alpha .lay-rail.row-stack .a-clabel{flex:0 0 100%;margin:0 0 2px;\n  font-size:17px;line-height:1.4;text-transform:none;letter-spacing:normal;\n  color:var(--ink-bright)}\n.jayms-tool-alpha .lay-rail.row-stack .a-cmain{flex:1 1 0;min-width:0;\n  font-size:15.5px;color:var(--ink-soft)}\n/* and the tags sit at the right edge of the row, on the answer's line,\n   whether the answer runs to one line or three */\n.jayms-tool-alpha .lay-rail .a-meta{margin-left:auto;flex:0 0 auto}"];
window.__qaFix = async function () {
  var gs = await wp.apiFetch({ path: '/wp/v2/global-styles/90171' });
  var n = gs.styles.css.split(window.__QA[0]).length - 1;
  if (n !== 1) return '90171 SKIPPED count=' + n;
  var css = gs.styles.css.replace(window.__QA[0], function () { return window.__QA[1]; });
  var st = JSON.parse(JSON.stringify(gs.styles)); st.css = css;
  await wp.apiFetch({ path: '/wp/v2/global-styles/90171', method: 'POST', data: { styles: st } });
  return '90171 ok ' + gs.styles.css.length + '->' + css.length;
};
"ready"