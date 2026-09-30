window.__CSSPATCH = [["--edge-text: var(--gold);", "--edge-open: var(--blue);       /* other voices, and what stays open */\n\t--edge-apparatus: var(--muted);  /* sources and cross-references */\n\t--edge-text: var(--gold);"], ["--edge-modern: var(--blue);", "--edge-modern: var(--lavender);"], [".a-box.v-gold{--bc:var(--gold)}", "/* One edge, one meaning. The colour answers a single question: what kind\n   of thing is in this box? Name the meaning -- the colour names still\n   work, so nothing already written breaks. */\n.a-box.v-scripture,.a-box.v-gold{--bc:var(--edge-text)}\n.a-box.v-witness,.a-box.v-aramaic{--bc:var(--edge-original)}\n.a-box.v-wording,.a-box.v-lavender{--bc:var(--edge-modern)}\n.a-box.v-explain,.a-box.v-olive{--bc:var(--edge-note)}\n.a-box.v-open,.a-box.v-blue{--bc:var(--edge-open)}\n.a-box.v-not,.a-box.v-rust{--bc:var(--edge-alert)}\n.a-box.v-apparatus,.a-box.v-muted{--bc:var(--edge-apparatus)}"], [".a-box.v-aramaic{--bc:var(--aramaic)}", ""], [".a-box.v-blue{--bc:var(--blue)}", ""], [".a-box.v-lavender{--bc:var(--lavender)}", ""], [".a-box.v-muted{--bc:var(--muted)}", ""], [".a-box.v-rust{--bc:var(--rust)}", ""]];
window.__S118 = [[".versebox.rust{border-left-color:var(--rust)}", ".versebox.rust{border-left-color:var(--edge-alert)}\n.versebox.olive{border-left-color:var(--edge-note)}\n.versebox.muted{border-left-color:var(--edge-apparatus)}"], [".versebox.green{border-left-color:var(--aramaic)}", ".versebox.green{border-left-color:var(--edge-original)}"], [".versebox.lav{border-left-color:var(--lavender)}", ".versebox.lav{border-left-color:var(--edge-modern)}"], [".versebox.blue{border-left-color:var(--blue)}", ".versebox.blue{border-left-color:var(--edge-open)}"], [".versebox.rust .ref{color:var(--rust)}", ".versebox.rust .ref{color:var(--edge-alert)}\n.versebox.olive .ref{color:var(--edge-note)}\n.versebox.muted .ref{color:var(--edge-apparatus)}"], [".versebox.green .ref{color:var(--aramaic)}", ".versebox.green .ref{color:var(--edge-original)}"], [".versebox.lav .ref{color:var(--lavender)}", ".versebox.lav .ref{color:var(--edge-modern)}"], [".versebox.blue .ref{color:var(--blue)}", ".versebox.blue .ref{color:var(--edge-open)}"]];
window.__applyColours = async function () {
  var log = [];

  var gs = await wp.apiFetch({ path: '/wp/v2/global-styles/90171' });
  var css = gs.styles.css, counts = [], ok = true;
  window.__CSSPATCH.forEach(function (p) {
    var n = css.split(p[0]).length - 1;
    counts.push(n);
    if (n !== 1) { ok = false; return; }
    css = css.replace(p[0], function () { return p[1]; });
  });
  if (!ok) { log.push('90171 SKIPPED ' + counts.join(',')); }
  else {
    var styles = JSON.parse(JSON.stringify(gs.styles));
    styles.css = css;
    await wp.apiFetch({ path: '/wp/v2/global-styles/90171', method: 'POST', data: { styles: styles } });
    log.push('90171 ok ' + counts.join(',') + ' ' + gs.styles.css.length + '->' + css.length);
  }

  var s = await wp.apiFetch({ path: '/code-snippets/v1/snippets/118' });
  var code = s.code, c2 = [], ok2 = true;
  window.__S118.forEach(function (p) {
    var n = code.split(p[0]).length - 1;
    c2.push(n);
    if (n !== 1) { ok2 = false; return; }
    code = code.replace(p[0], function () { return p[1]; });
  });
  if (!ok2) { log.push('118 SKIPPED ' + c2.join(',')); }
  else {
    await wp.apiFetch({ path: '/code-snippets/v1/snippets/118', method: 'POST', data: { id: 118, code: code } });
    log.push('118 ok ' + c2.join(',') + ' ' + s.code.length + '->' + code.length);
  }
  return log.join(' ||| ');
};
"ready"