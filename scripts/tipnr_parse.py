"""TIPNR: one record per individual person, place or thing.

Source: STEPBible-Data, "TIPNR - Translators Individualised Proper Names with
all References", CC BY 4.0, github.com/STEPBible/STEPBible-Data.
Credit: STEP Bible, https://www.STEPBible.org

Shape of the file: records start with "$", the first line of a record carries
the unified name and the summary, and the lines after it that start with an
en dash are its alternate names, one per form, each with its own Strong's
numbers and exhaustive references. A "– Total" line aggregates them.

What this pulls out, per individual:
  key        Aaron@Exo.4.14-Heb, unique across the file
  name       the English form before the "@"
  ustrong    the unified Strong's number, normalised to H175 / G2
  strongs    every disambiguated Strong's on the record, same normalisation
  type       Male, Female, Group, Place, Language, Time, Supernatural, ...
  summary    the standard-sentence description
  refs       every reference, in English versification already
"""
import io, os, re, collections

HERE = os.path.dirname(os.path.abspath(__file__))
TIPNR = os.path.join(HERE, 'step', 'TIPNR.txt')

BOOK = None   # filled from tvtms_parse so the two files agree on book names

# Aaron@Exo.4.14-Heb=H0175 . The @Brief / @Short lines also carry an "=" and
# have to be kept out of this.
HEAD = re.compile(r'^[^\t@]+@[1-3A-Za-z]{3}\.[\d.]+[^\t]*=[HG]\d')


def _norm_strong(s):
    """H0175A and H0175 and H175 all mean H175 here."""
    m = re.match(r'^([HG])0*(\d+)', str(s).strip())
    return ('%s%d' % (m.group(1), int(m.group(2)))) if m else None


def _refs(cell):
    """TIPNR writes Exo.4.14; this project writes Exodus 4:14."""
    from tvtms_parse import BOOK as B
    out = []
    for raw in re.split(r'[;,]', cell or ''):
        raw = raw.strip()
        if not raw or raw.startswith('LXX'):
            continue
        m = re.match(r'^([1-3A-Za-z]{3})\.(\d+)\.(\d+)', raw)
        if not m:
            continue
        b = B.get(m.group(1))
        if b:
            out.append('%s %s:%s' % (b, m.group(2), m.group(3)))
    return out


def load():
    recs, cur, section = [], None, None
    for line in io.open(TIPNR, encoding='utf-8-sig'):
        line = line.rstrip('\n')
        if line.startswith('$=========='):
            section = line.split('$==========')[1].strip()
            continue
        if line.startswith('$'):
            continue
        c = [x.strip() for x in line.split('\t')]
        if not c or not c[0]:
            continue
        head = c[0]
        if head.startswith('–') or head.startswith('-'):
            if not cur:
                continue
            tag = head.lstrip('–- ').strip()
            if tag.lower().startswith('total'):
                # The Total line abbreviates its references ("Exo.4.27ff"), so
                # the per-form lines above are the exhaustive ones and the only
                # thing taken from here is the full set of Strong's numbers.
                cur['strongs'] |= {s for s in (_norm_strong(x) for x in
                                   re.split(r'[«,/ ]+', c[2] if len(c) > 2 else '')) if s}
            else:
                cur['strongs'] |= {s for s in (_norm_strong(x) for x in
                                   re.split(r'[«,/ ]+', c[2] if len(c) > 2 else '')) if s}
                cur['refs'] += _refs(c[4] if len(c) > 4 else '')
                if len(c) > 3 and c[3]:
                    cur['forms'].add(c[3].strip())
            continue
        if head.startswith('@'):
            if cur:
                k, _, v = head.partition('=')
                cur[k.lstrip('@').strip().lower()] = v.strip()
            continue
        if HEAD.match(head):
            key, _, us = head.partition('=')
            cur = {'key': key.strip(), 'name': key.split('@')[0].strip(),
                   'ustrong': _norm_strong(us), 'strongs': set(), 'refs': [],
                   'forms': set(), 'section': section,
                   'summary': (c[7].lstrip('#').strip() if len(c) > 7 else ''),
                   'type': (c[8].strip() if len(c) > 8 else ''),
                   'desc': (c[1].strip() if len(c) > 1 else '')}
            if cur['ustrong']:
                cur['strongs'].add(cur['ustrong'])
            recs.append(cur)
    for r in recs:
        seen, out = set(), []
        for x in r['refs']:
            if x not in seen:
                seen.add(x); out.append(x)
        r['refs'] = out
        r['strongs'] = sorted(r['strongs'])
    return recs


if __name__ == '__main__':
    R = load()
    print('individuals:', len(R))
    print('sections:', collections.Counter(r['section'] for r in R).most_common())
    print('types:', collections.Counter(r['type'] for r in R).most_common(14))
    print('with a ustrong:', sum(1 for r in R if r['ustrong']))
    print('total refs:', sum(len(r['refs']) for r in R))
    for probe in ('Aaron', 'Azariah', 'Abel', 'Rahab'):
        hits = [r for r in R if r['name'] == probe]
        print(f'  {probe}: {len(hits)} individuals', [(h['key'][:28], h['type'], len(h['refs'])) for h in hits[:4]])
