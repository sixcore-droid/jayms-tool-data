"""Word Timelines: the "When it was written" chart, on the same engine as "When it happened".
Streams: the Old Testament written, the New Testament written, the New Testament gathered, the copies we hold,
when the copies came to light, translations, print and modern editions.

Every Old Testament book carries the gap between its writing and the oldest copy of it here, worked out from the
Dead Sea Scrolls per-verse data (ETCBC/dss, Abegg) and the Leningrad Codex. New Testament dates come only from this
site's own timelines; books those timelines do not date are listed in the note, not guessed.
Run after b4_time.py, b8_nowiki.py and b7_chart.py (it reads clock.json and church_data.py)."""
import json, sys
from pathlib import Path
OUT = Path('/home/claude/wt/out/word-trail')
sys.path.insert(0, str(Path(__file__).parent)); import church_data as CD
K = json.load(open(OUT / 'clock.json')); BK = json.load(open(OUT / 'books.json'))['books']
def yl(y): return ('%d BC' % -y) if y < 0 else ('AD %d' % y)
def sp(a, b): return yl(a) if a == b else (('%d to %d BC' % (-a, -b)) if b < 0 else yl(a) + ' to ' + yl(b))
# Dead Sea Scrolls verses per book
dssv = {int(k) for k in K['dss']['verses']}
def bookof(i):
    lo, hi = 0, 65
    while lo < hi:
        m = (lo + hi + 1) // 2
        if BK[m]['s'] <= i: lo = m
        else: hi = m - 1
    return lo
surv = {}
for i in dssv: b = bookof(i); surv[b] = surv.get(b, 0) + 1
W = {w['k']: w for w in K['witness']}
S = []
def stream(k, n, c, items, src): S.append({'k': k, 'n': n, 'c': c, 'items': sorted(items, key=lambda x: (x['a'], -x['b'])), 'src': src, 'url': ''})
ot = []
for b in range(39):
    n = BK[b]['n']; A = K['datesA']['books'].get(n)
    if not A: continue
    nv = sum(BK[b]['c'])
    if surv.get(b):
        d = W['dss']; gap = d['copy'][0] - A[1]
        more = ('Oldest copy here: the Dead Sea Scrolls, copied %s. %s of its %s verses survive in at least one scroll (%d scrolls). '
                'From the writing to the earliest scrolls: as little as about %s years.') % (sp(*d['copy']), format(surv[b], ','), format(nv, ','), K['dss']['perBook'].get(n, 0), format(max(gap, 0), ','))
    else:
        d = W['len']; gap = d['copy'][0] - A[1]
        more = 'No verse of it survives in the Dead Sea Scrolls data used here. Oldest complete copy here: the Leningrad Codex, copied AD 1008, about %s years after the writing.' % format(gap, ',')
    if n == 'Numbers': more += ' The silver scrolls of Ketef Hinnom, copied 650 to 587 BC, carry a form of its priestly blessing (6:24-26).'
    if n in ('Exodus', 'Deuteronomy'): more += ' The Nash Papyrus, copied 150 to 100 BC, carries the Ten Commandments' + (' and the opening of the Shema.' if n == 'Deuteronomy' else '.')
    ot.append({'n': n, 'a': A[0], 'b': A[1], 't': 'seg' if A[1] > A[0] else 'pt', 'd': 'Written ' + sp(A[0], A[1]), 'more': more})
stream('ot', 'The Old Testament written', 'gold', ot, K['datesA']['name'] + ' dates, as Logos shows them')
WRIT = [r for r in CD.ROWS if r[0] in ('canon', 'apostles') and ('written' in r[4].lower() or r[4].startswith(('Ephesians', 'John:')))]
nt = [{'n': r[4].replace(' written', ''), 'a': r[2], 'b': r[3], 't': 'seg' if r[3] > r[2] else 'pt', 'd': r[1], 'url': r[5]} for r in WRIT]
stream('nt', 'The New Testament written', 'blue', nt, "This site's Apostle Paul, Twelve Apostles and New Testament Canon timelines")
gath = [{'n': r[4], 'a': r[2], 'b': r[3], 't': 'seg' if r[3] > r[2] else 'pt', 'd': r[1], 'url': r[5]} for r in CD.ROWS if r[0] == 'canon' and r not in WRIT]
stream('canon', 'The New Testament gathered', 'aramaic', gath, "This site's New Testament Canon timeline")
cop = [{'n': w['n'], 'a': w['copy'][0], 'b': w['copy'][1], 't': 'seg' if w['copy'][1] > w['copy'][0] else 'pt', 'd': 'Copied ' + sp(*w['copy']),
        'more': w['has'] + '. Held: ' + w['held'] + '.'} for w in K['witness']]
stream('copies', 'The copies we hold', 'olive', cop, 'Standard dates, not yet checked in Logos')
fnd = [{'n': w['n'] + ' found', 'a': w['found'], 'b': w['found'], 't': 'flag', 'd': 'Came to light ' + yl(w['found']) + ('. ' + w['foundNote'] if w.get('foundNote') else ''), 'more': w['place'] + '.'} for w in K['witness']]
stream('found', 'When the copies came to light', 'ink-soft', fnd, 'Standard dates, not yet checked in Logos')
tr = [{'n': t['n'], 'a': t['y'][0], 'b': t['y'][1], 't': 'seg' if t['y'][1] > t['y'][0] else 'pt', 'd': t['note'], 'url': t.get('src', '')} for t in K['translations']]
stream('trans', 'Translations', 'lavender', [x for x in tr if x['a'] < 1450], 'Standard dates, not yet checked in Logos')
stream('print', 'Print and modern editions', 'rust', [x for x in tr if x['a'] >= 1450], 'Standard dates, not yet checked in Logos')
ERAS = [['Full view', -2200, 2026], ['Torah to the kings', -1600, -900], ['Prophets and exile', -800, -400], ['Between the testaments', -460, 10],
        ['The New Testament', 40, 110], ['Gathered and copied', 90, 1100], ['Print to today', 1370, 2026]]
undated = ['Matthew', 'Mark', 'Luke', 'John', 'Acts', 'Hebrews', 'James', 'Jude']
NOTE = ("Each Old Testament book carries how close its oldest copy here comes to its writing; pick a book to see it. "
        "The New Testament books are dated only where this site's own timelines date them, so the four Gospels share one span and "
        "Acts, Hebrews, James and Jude are not on the chart yet. Old Testament dates are the Bible Knowledge Commentary's.")
SOURCES = [['Old Testament written: Bible Knowledge Commentary dates, as shown in Logos.', ''],
           ["New Testament written and gathered: this site's Apostle Paul, Twelve Apostles and New Testament Canon timelines.", 'https://jayms.com/bible-study-tools-2/how-we-got-the-new-testament-canon-timeline/'],
           ['Which verses survive in the Dead Sea Scrolls: ETCBC/dss, Martin Abegg\'s data, CC BY-NC 4.0.', 'https://github.com/ETCBC/dss'],
           ['Manuscripts, find dates, translations and print: standard dates, not yet checked in Logos.', '']]
json.dump({'streams': S, 'range': [-2200, 2026], 'eras': ERAS, 'note': NOTE, 'sources': SOURCES, 'find': 'Find a book, a manuscript or a translation'}, open(OUT / 'written.json', 'w'), ensure_ascii=False, separators=(',', ':'))
print([(s['k'], len(s['items'])) for s in S])
