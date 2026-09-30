window.__tagFix = async function () {
  var log = [];

  // the list tag says where a name is attested; both values were rust, which
  // in the standard means "what this is not". Canon only is scripture alone,
  // canon + Second Temple has a witness outside it.
  var s = await wp.apiFetch({ path: '/code-snippets/v1/snippets/110' });
  var rx = /class="genre-tag">\$\{([^}]*)\}/;
  var hits = (s.code.match(new RegExp(rx.source, 'g')) || []).length;
  if (hits !== 1) return 'ABORT genre-tag matches=' + hits;
  var code = s.code.replace(rx, function (_, expr) {
    return 'class="genre-tag ${((' + expr + ') === \'Canon only\' ? \'v-scripture\' : \'v-witness\')}">${' + expr + '}';
  });
  await wp.apiFetch({ path: '/code-snippets/v1/snippets/110', method: 'POST', data: { id: 110, code: code } });
  log.push('110 ok ' + s.code.length + '->' + code.length);

  var anchor = 'h2.section.v-witness + div,h2.section.v-apparatus + div{padding-bottom:4px;margin-bottom:30px}';
  var add = anchor + '\n' +
    '/* the Fact Book list tag: where the name is attested */\n' +
    '.entity-tile .genre-tag.v-scripture{color:var(--edge-text)}\n' +
    '.entity-tile .genre-tag.v-witness{color:var(--edge-original)}';
  var gs = await wp.apiFetch({ path: '/wp/v2/global-styles/90171' });
  var n = gs.styles.css.split(anchor).length - 1;
  if (n !== 1) { log.push('90171 SKIPPED count=' + n); return log.join(' ||| '); }
  var css = gs.styles.css.replace(anchor, function () { return add; });
  var styles = JSON.parse(JSON.stringify(gs.styles));
  styles.css = css;
  await wp.apiFetch({ path: '/wp/v2/global-styles/90171', method: 'POST', data: { styles: styles } });
  log.push('90171 ok ' + gs.styles.css.length + '->' + css.length);
  return log.join(' ||| ');
};
"ready"