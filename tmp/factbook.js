window.__FBCSS = [[".a-box.v-apparatus,.a-box.v-muted{--bc:var(--edge-apparatus)}", ".a-box.v-apparatus,.a-box.v-muted{--bc:var(--edge-apparatus)}\n\n/* The Fact Book sets a section as a heading followed by a block rather than\n   as a boxed panel, so the edge goes on the pair. Same seven meanings; the\n   sibling needs its own --bc because a custom property inherits from the\n   parent, not from the element before it. */\nh2.section.v-witness,h2.section.v-witness + div{--bc:var(--edge-original)}\nh2.section.v-apparatus,h2.section.v-apparatus + div{--bc:var(--edge-apparatus)}\nh2.section.v-witness,h2.section.v-apparatus{color:var(--bc)}\nh2.section.v-witness,h2.section.v-apparatus,\nh2.section.v-witness + div,h2.section.v-apparatus + div{\n  border-left:3px solid var(--bc);padding-left:15px}\nh2.section.v-witness,h2.section.v-apparatus{padding-bottom:2px;padding-top:0;border-top:0}\nh2.section.v-witness + div,h2.section.v-apparatus + div{padding-bottom:4px;margin-bottom:30px}"]];
window.__doFactBook = async function () {
  var log = [];

  var gs = await wp.apiFetch({ path: '/wp/v2/global-styles/90171' });
  var css = gs.styles.css, n = css.split(window.__FBCSS[0][0]).length - 1;
  if (n !== 1) { log.push('90171 SKIPPED count=' + n); }
  else {
    css = css.replace(window.__FBCSS[0][0], function () { return window.__FBCSS[0][1]; });
    var styles = JSON.parse(JSON.stringify(gs.styles));
    styles.css = css;
    await wp.apiFetch({ path: '/wp/v2/global-styles/90171', method: 'POST', data: { styles: styles } });
    log.push('90171 ok ' + gs.styles.css.length + '->' + css.length);
  }

  var s = await wp.apiFetch({ path: '/code-snippets/v1/snippets/110' });
  var code = s.code;
  var jobs = [
    [/<h2[^>]*>Canonical<\/h2>/g, '<h2 class="section v-apparatus">Canonical</h2>'],
    [/<h2[^>]*>Second Temple &amp; Extra-biblical<\/h2>/g,
     '<h2 class="section v-witness">Second Temple &amp; Extra-biblical</h2>']
  ];
  var counts = [], ok = true;
  jobs.forEach(function (j) {
    var m = code.match(j[0]) || [];
    counts.push(m.length);
    if (m.length !== 1) { ok = false; return; }
    code = code.replace(j[0], function () { return j[1]; });
  });
  if (!ok) { log.push('110 SKIPPED ' + counts.join(',')); }
  else {
    await wp.apiFetch({ path: '/code-snippets/v1/snippets/110', method: 'POST',
      data: { id: 110, code: code } });
    log.push('110 ok ' + counts.join(',') + ' ' + s.code.length + '->' + code.length);
  }
  return log.join(' ||| ');
};
"ready"