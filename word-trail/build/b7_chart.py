"""Word Timelines: the "When it happened" chart, laid out as a synchronological chart in the manner of
Adams' Chart of History: time runs left to right, each people is a stream, and Israel splits into two.

Sources
  Theographic Bible Metadata, CC BY-SA 4.0 (Events.csv): lifetimes, reigns, judges, prophets, the New Testament,
    dated on Floyd Nolen Jones, The Chronology of the Old Testament.
  JAYMS.COM, The Old Testament Timeline: turning points, on James's own dates (world.json events).
  Cliopatria (Seshat Global History Databank), CC BY 4.0: the nations around Israel (world.json lanes).
  Famous moments (world.json famous): standard dates, not yet checked in Logos. No Wikipedia links: b8_nowiki.py strips them.
Output chart.json: {streams:[{k,n,c,items:[{n,a,b,t,ref,src}]}], range:[a,b], eras:[...], notes}
"""
import csv, json, re
from pathlib import Path
OUT = Path('/home/claude/wt/out/word-trail')
W = json.load(open(OUT / 'world.json'))
EV = list(csv.DictReader(open('/home/claude/wt/theo/CSV/Events.csv', encoding='utf-8-sig')))

def year(s):
    s = s.strip()
    m = re.match(r'^(-?)0*(\d+)', s)
    return (-1 if m.group(1) else 1) * int(m.group(2)) if m else None
def dur(s):
    y = 0.0
    for n, u in re.findall(r'(\d+)([YMWD])', s or ''):
        y += int(n) * {'Y': 1, 'M': 1 / 12, 'W': 7 / 365, 'D': 1 / 365}[u]
    return y
def ref(v):
    # Theographic verse ids look like 2Kgs.13.13; keep the first, as a readable reference
    v = (v or '').split(',')[0].strip()
    m = re.match(r'(\w+)\.(\d+)\.(\d+)', v)
    if not m: return ''
    OSIS = {'Gen':'Genesis','Exod':'Exodus','Lev':'Leviticus','Num':'Numbers','Deut':'Deuteronomy','Josh':'Joshua','Judg':'Judges','Ruth':'Ruth','1Sam':'1 Samuel','2Sam':'2 Samuel','1Kgs':'1 Kings','2Kgs':'2 Kings','1Chr':'1 Chronicles','2Chr':'2 Chronicles','Ezra':'Ezra','Neh':'Nehemiah','Esth':'Esther','Job':'Job','Ps':'Psalms','Prov':'Proverbs','Eccl':'Ecclesiastes','Song':'Song of Solomon','Isa':'Isaiah','Jer':'Jeremiah','Lam':'Lamentations','Ezek':'Ezekiel','Dan':'Daniel','Hos':'Hosea','Joel':'Joel','Amos':'Amos','Obad':'Obadiah','Jonah':'Jonah','Mic':'Micah','Nah':'Nahum','Hab':'Habakkuk','Zeph':'Zephaniah','Hag':'Haggai','Zech':'Zechariah','Mal':'Malachi','Matt':'Matthew','Mark':'Mark','Luke':'Luke','John':'John','Acts':'Acts','Rom':'Romans','1Cor':'1 Corinthians','2Cor':'2 Corinthians','Gal':'Galatians','Eph':'Ephesians','Phil':'Philippians','Col':'Colossians','1Thess':'1 Thessalonians','2Thess':'2 Thessalonians','1Tim':'1 Timothy','2Tim':'2 Timothy','Titus':'Titus','Phlm':'Philemon','Heb':'Hebrews','Jas':'James','1Pet':'1 Peter','2Pet':'2 Peter','1John':'1 John','2John':'2 John','3John':'3 John','Jude':'Jude','Rev':'Revelation'}
    return (OSIS.get(m.group(1), m.group(1)) + ' ' + m.group(2) + ':' + m.group(3))

THEO = 'Theographic Bible Metadata (Floyd Nolen Jones chronology)'
TSRC = 'https://github.com/robertrouse/theographic-bible-metadata'
rows = []
for e in EV:
    t = e['title']; a = year(e['startDate'])
    if a is None: continue
    d = dur(e['duration']); b = a + max(d, 0)
    rows.append(dict(n=t, a=a, b=round(b, 2), d=d, part=e['partOf'], ref=ref(e['verses'])))

def it(r, t):
    o = {'n': r['n'], 'a': r['a'], 'b': r['b'], 't': t}
    if r['ref']: o['ref'] = r['ref']
    return o
S = []
def stream(k, n, c, items, note, src):
    S.append({'k': k, 'n': n, 'c': c, 'items': sorted(items, key=lambda x: (x['a'], -x['b'])), 'src': note, 'url': src})

life = [it(dict(r, n=r['n'].replace('Lifetime of ', '')), 'life') for r in rows if r['n'].startswith('Lifetime of ')]
stream('line', 'The line from Adam', 'gold', life, THEO, TSRC)

def isr(r):
    n = r['n']
    if n.startswith(('Lifetime of', 'Reign of', 'Prophecies of')): return False
    if r['part'] in ('Southern Kingdom (Judah)', 'Northern Kingdom (Israel)'): return False
    return r['a'] < 0 and r['d'] >= 1 and n not in ('Southern Kingdom (Judah)', 'Northern Kingdom (Israel)', 'United Kingdom', 'The Great Flood')
period = []
for r in rows:
    if isr(r):
        kind = 'bad' if re.match(r'(Subjugation|Oppression|Usurpation|Philistine)', r['n']) else 'seg'
        period.append(it(r, kind))
for r in rows:
    if r['n'] in ('Reign of Saul', 'Reign of David', 'Reign of Solomon'): period.append(it(r, 'reign'))
stream('israel', 'Israel, one people', 'gold', period, THEO, TSRC)

reignN = {r['n'] for r in rows if r['part'] == 'Northern Kingdom (Israel)'}
north = [it(dict(r, n=r['n'].replace('Reign of ', '')), 'reign') for r in rows if r['n'].startswith('Reign of') and r['part'] == 'Northern Kingdom (Israel)']
south = [it(dict(r, n=r['n'].replace('Reign of ', '')), 'reign') for r in rows if r['n'].startswith('Reign of') and r['part'] == 'Southern Kingdom (Judah)']
# Theographic files a few reigns without partOf; place them by the kingdom lists in 1 and 2 Kings
NORTHK = {'Jeroboam I','Nadab','Baasha','Elah','Zimri','Tibni','Omri','Ahab','Ahaziah','Joram','Jehu','Jehoahaz','Jehoash','Jeroboam II','Zachariah','Shallum','Menahem','Pekahiah','Pekah','Hoshea'}
have = {x['n'] for x in north + south}
for r in rows:
    if r['n'].startswith('Reign of') and r['n'] not in ('Reign of Saul', 'Reign of David', 'Reign of Solomon'):
        nm = r['n'].replace('Reign of ', '')
        if nm in have: continue
        (north if nm in NORTHK else south).append(it(dict(r, n=nm), 'reign')); have.add(nm)
for r in rows:
    # the kings' deaths sit at the end of each reign already, so only the other moments are marked
    if r['part'] == 'Northern Kingdom (Israel)' and not r['n'].startswith(('Reign of', 'Death of')) and r['d'] < 1: north.append(it(r, 'pt'))
    if r['part'] == 'Southern Kingdom (Judah)' and not r['n'].startswith(('Reign of', 'Death of')) and r['d'] < 1: south.append(it(r, 'pt'))
stream('north', 'Israel, the northern kingdom', 'olive', north, THEO, TSRC)
stream('south', 'Judah, the southern kingdom', 'gold', south, THEO, TSRC)

proph = [it(dict(r, n=r['n'].replace('Prophecies of ', '')), 'life') for r in rows if r['n'].startswith('Prophecies of')]
stream('prophets', 'The prophets', 'lavender', proph, THEO, TSRC)

# the New Testament has some two hundred day-dated events; the chart keeps the spans and the turning points
NTKEY = {'Birth of Jesus', 'Birth of John the Baptist', 'Baptism of Jesus', 'Crucifixion and Burial', 'Resurrection and Ascension',
         'Peter preaches at Pentecost', 'Jerusalem Council', "Paul's Journey to Rome", 'Paul arrives at Rome', "Paul's First Roman imprisonment"}
nt = [it(r, 'seg' if r['d'] >= 1 else 'pt') for r in rows if -5 <= r['a'] <= 33 and (r['d'] >= 1 or r['n'] in NTKEY)]
for p in W['people']['rows']:
    if p['n'] == 'Jesus': nt.append({'n': 'Jesus', 'a': p['a'], 'b': p['b'], 't': 'life'})
stream('nt', 'Jesus', 'blue', nt, THEO, TSRC)

# after the Gospels: James's own timelines, then the Bible copied and translated
import sys; sys.path.insert(0, str(Path(__file__).parent)); import church_data as CDAT
CK = json.load(open(OUT / 'clock.json'))
def crow(k): return [{'n': r[4], 'a': r[2], 'b': r[3], 't': 'seg' if r[3] > r[2] else 'pt', 'd': r[1], 'url': r[5] if r[5].startswith('http') else '', 'note': '' if r[5].startswith('http') else r[5]} for r in CDAT.ROWS if r[0] == k]
stream('apostles', 'The apostles and Paul', 'blue', crow('apostles'), "This site's Twelve Apostles and Apostle Paul timelines", CDAT.AP)
stream('canon', 'The New Testament written and gathered', 'gold', crow('canon'), "This site's Apostle Paul and New Testament Canon timelines", CDAT.CA)
bible = [{'n': t['n'], 'a': t['y'][0], 'b': t['y'][1], 't': 'seg' if t['y'][1] > t['y'][0] else 'pt', 'd': t['note'], 'url': t['src']} for t in CK['translations']]
bible += [{'n': w['n'] + ' copied', 'a': w['copy'][0], 'b': w['copy'][1], 't': 'seg' if w['copy'][1] > w['copy'][0] else 'pt', 'd': 'Copied ' + ('%d' % w['copy'][0]) + ('' if w['copy'][1] == w['copy'][0] else ' to %d' % w['copy'][1]) + '; found ' + str(w['found']) + '. ' + w['has'], 'url': w['src']} for w in CK['witness']]
stream('bible', 'The Bible copied and translated', 'olive', bible, 'Standard dates, not yet checked in Logos', '')
stream('fathers', 'The church fathers', 'lavender', crow('fathers'), "This site's Ante-Nicene Age and Gnostic Crisis timelines", CDAT.AN)
stream('church', 'The Crusades and their memory', 'rust', crow('church'), "This site's Crusades timeline", CDAT.CR)
stream('denom', 'Denominations begin and split', 'gold', crow('denom'), 'The Denominational Map, in the study notes', '')

# points that belong to no stream above: births, deaths, battles before the kingdom split
pts = [it(r, 'pt') for r in rows if r['d'] < 1 and r['a'] < -5 and not r['part'].startswith(('Southern', 'Northern')) and r['n'] != 'The Great Flood']
pts.append({'n': 'The Great Flood', 'a': -2347, 'b': -2346, 't': 'pt', 'ref': 'Genesis 7:11'})
stream('events', 'Events in Scripture', 'ink-soft', pts, THEO, TSRC)

tp = [{'n': e['n'], 'a': e['a'], 'b': e['b'], 't': 'flag', 'ref': e.get('ref', '')} for e in W['events']['rows']]
stream('turning', 'Turning points, on this site\'s dates', 'rust', tp, W['events']['source'], W['events']['src'])

import stoic_data as SD
stoics = [{'n': r[0], 'a': r[1], 'b': r[2], 't': 'seg' if r[2] > r[1] else 'pt', 'd': r[3], 'more': r[4], 'note': r[5], 'url': SD.SR, 'lab': 'Open the Stoic Reader'} for r in SD.LIVES]
stream('stoics', 'The Stoics', 'aramaic', stoics, 'Stanford and Internet Encyclopedias of Philosophy', SD.SEP)
COL = {'egypt': 'rust', 'meso': 'blue', 'anatolia': 'olive', 'persia': 'lavender', 'greece': 'aramaic', 'rome': 'rust',
       'china': 'gold', 'india': 'olive', 'africa': 'aramaic', 'americas': 'lavender'}
for lane in W['world']['lanes']:
    items = [{'n': b['n'], 'a': b['a'], 'b': b['b'], 't': 'nation'} for b in lane['bands']]
    stream(lane['k'], lane['n'], COL.get(lane['k'], 'ink-soft'), items, W['world']['source'], W['world']['src'])
fam = [{'n': f['n'], 'a': f['y'], 'b': f['y'], 't': 'flag', 'd': f['d'], 'url': f['src']} for f in W['famous']]
stream('famous', 'Famous moments elsewhere', 'ink-soft', fam, 'Standard dates, not yet checked in Logos', '')

ERAS = [['Full view', -4004, 2026], ['Creation to the Flood', -4004, -2300], ['The patriarchs', -2400, -1650], ['Egypt and the Exodus', -1760, -1400],
        ['The judges', -1460, -1050], ['The kingdom', -1110, -930], ['Two kingdoms', -990, -570], ['Exile and return', -620, -400],
        ['Persia and Greece', -560, -140], ['Rome and Christ', -200, 100], ['The apostles', 0, 105], ['The church fathers', 90, 340],
        ['Councils and canon', 300, 480], ['The Middle Ages', 450, 1500], ['The Reformation', 1450, 1750], ['The modern church', 1700, 2026]]
SOURCES = [
  ['Israel, the kings, the judges, the prophets, Jesus: Theographic Bible Metadata, CC BY-SA 4.0.', 'https://github.com/robertrouse/theographic-bible-metadata'],
  ["Turning points: this site's Old Testament Timeline.", 'https://jayms.com/bible-study-tools-2/the-old-testament-timeline/'],
  ["The apostles and Paul: this site's Twelve Apostles and Apostle Paul timelines.", 'https://jayms.com/bible-study-tools-2/the-apostle-paul-timeline-and-letters/'],
  ["The New Testament gathered: this site's New Testament Canon timeline.", 'https://jayms.com/bible-study-tools-2/how-we-got-the-new-testament-canon-timeline/'],
  ["The church fathers: this site's Ante-Nicene Age and Gnostic Crisis timelines.", 'https://jayms.com/bible-study-tools-2/the-ante-nicene-age-timeline/'],
  ["The Crusades and their memory: this site's Crusades timeline.", 'https://jayms.com/bible-study-tools-2/the-crusades-full-timeline/'],
  ['Denominations begin and split: The Denominational Map in the study notes, Logos-verified (Olson, Needham, McGuckin, Ladouceur, Fairbairn, Bray, Beeke, Wengert, Synan), with current figures web-verified.', ''],
  ['The nations: Cliopatria, Seshat Global History Databank, CC BY 4.0.', 'https://github.com/Seshat-Global-History-Databank/cliopatria'],
  ['The Stoics: Stanford Encyclopedia of Philosophy (Stoicism, Seneca, Epictetus, Marcus Aurelius) and the Internet Encyclopedia of Philosophy (Chrysippus, Musonius Rufus, Stoicism).', 'https://plato.stanford.edu/entries/stoicism/'],
  ['Famous moments, translations and manuscripts: standard dates, not yet checked in Logos.', ''],
  ["The layout follows Adams' Synchronological Chart of Universal History (1871).", 'https://www.adamschart.org/']]
json.dump({'streams': S, 'range': [-4004, 2026], 'eras': ERAS, 'sources': SOURCES,
           'note': "Israel's streams follow Floyd Nolen Jones's chronology, as Theographic carries it. The turning points follow this site's Old Testament Timeline, which dates the Exodus to 1446 BC, so before the kings the two can sit decades apart. The nations come from Cliopatria, on historians' dates. After the Gospels the apostles, the fathers, the canon and the divided church come from this site's own timelines, dated as each page dates them."},
          open(OUT / 'chart.json', 'w'), ensure_ascii=False, separators=(',', ':'))
print([(s['k'], len(s['items'])) for s in S])
