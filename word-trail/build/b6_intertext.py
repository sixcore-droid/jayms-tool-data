"""Word Trail: where each book echoes the rest of the Bible.

Three sources, kept apart so the page can say which one a line comes from:
  q  shared wording. UBS Parallel Passages Database (c) 2023 United Bible Societies, CC BY-SA 4.0,
     github.com/ubsicap/ubs-open-license. Passages whose words match word for word, OT quotes in the NT
     among them. Strength 3 when at least 40% of either verse's words match, else 2.
  e  graded echo. JAYMS.COM Echoes of Revelation (links.json). clear 3, probable 2, possible 1.
  x  cross reference. OpenBible.info cross references, CC BY, via scrollmapper/bible_databases.
     Readers vote on each one; 50 votes or more is 3, 15 to 49 is 2, 5 to 14 is 1. Fewer than 5 is dropped.

Output: intertext/b0.json ... b65.json, one per book, every pair with one end in that book:
  [this start, this end, other start, other end, source, strength, extra]
  extra is the Echoes entry id (e) or the vote count (x) or the match share in percent (q).
"""
import json, re, glob, sqlite3, collections
from pathlib import Path
OUT = Path('/home/claude/wt/out/word-trail')
BOOKS = json.load(open(OUT / 'books.json'))['books']
NAME = {b['n']: k for k, b in enumerate(BOOKS)}
UBS = ['GEN','EXO','LEV','NUM','DEU','JOS','JDG','RUT','1SA','2SA','1KI','2KI','1CH','2CH','EZR','NEH','EST','JOB','PSA','PRO','ECC','SNG','ISA','JER','LAM','EZK','DAN','HOS','JOL','AMO','OBA','JON','MIC','NAM','HAB','ZEP','HAG','ZEC','MAL',
       'MAT','MRK','LUK','JHN','ACT','ROM','1CO','2CO','GAL','EPH','PHP','COL','1TH','2TH','1TI','2TI','TIT','PHM','HEB','JAS','1PE','2PE','1JN','2JN','3JN','JUD','REV']
CODE = {c: k for k, c in enumerate(UBS)}

# UBS gives Old Testament references in the Hebrew numbering (Joel 3:4 for English Joel 2:31); the grid is English
HE2EN = json.load(open('/home/claude/wt/jayms-tool-data/versification-map.json'))
def he2en(b, ch, v):
    for a, z, ech, off in HE2EN.get(BOOKS[b]['n'], {}).get(str(ch), []):
        if a <= v <= z: return (ech, 1) if off == 'fold' else (ech, v + off)
    return ch, v
def vid(b, ch, v):
    c = BOOKS[b]['c']
    if ch < 1 or ch > len(c) or v < 1 or v > c[ch - 1]: return None
    return BOOKS[b]['s'] + sum(c[:ch - 1]) + v - 1

pairs = collections.defaultdict(dict)   # key (a0,a1,b0,b1,src) -> (strength, extra), a < b
def add(a0, a1, b0, b1, src, st, ex):
    if a0 is None or b0 is None: return
    if a1 is None: a1 = a0
    if b1 is None: b1 = b0
    def bk(i):
        lo, hi = 0, 65
        while lo < hi:
            m = (lo + hi + 1) // 2
            if BOOKS[m]['s'] <= i: lo = m
            else: hi = m - 1
        return lo
    if bk(a0) == bk(b0): return            # book to book only
    if b0 < a0: a0, a1, b0, b1 = b0, b1, a0, a1
    k = (a0, a1, b0, b1, src); old = pairs[src].get(k)
    if not old or st > old[0] or (st == old[0] and isinstance(ex, int) and isinstance(old[1], int) and ex > old[1]):
        pairs[src][k] = (st, ex)

# --- UBS shared wording
xml = open('/home/claude/src-ext/ubs-open-license/parallel passages/ParallelPassages.xml', encoding='utf-8-sig').read()
nq = 0
for p in re.findall(r'<Passage>(.*?)</Passage>', xml, re.S):
    vs = []
    for marks, ref in re.findall(r'<Verse \w+="([0-9]*)">([^<]+)</Verse>', p):
        m = re.match(r'(\S+) (\d+):(\d+)(?:-(?:(\d+):)?(\d+))?$', ref.strip())
        if not m or m.group(1) not in CODE: continue
        b = CODE[m.group(1)]; ch = int(m.group(2)); v = int(m.group(3))
        ch2 = int(m.group(4)) if m.group(4) else ch; v2 = int(m.group(5)) if m.group(5) else v
        if b < 39: ch, v = he2en(b, ch, v); ch2, v2 = he2en(b, ch2, v2)
        s = vid(b, ch, v); e = vid(b, ch2, v2) or s
        hit = sum(1 for d in marks if d in '1245'); share = hit / max(1, len(marks))
        vs.append((s, e, share))
    for i in range(len(vs)):
        for j in range(i + 1, len(vs)):
            sh = max(vs[i][2], vs[j][2]); add(vs[i][0], vs[i][1], vs[j][0], vs[j][1], 'q', 3 if sh >= 0.4 else 2, round(sh * 100)); nq += 1

# --- Echoes of Revelation, graded
G = {'clear': 3, 'probable': 2, 'possible': 1}
for l in json.load(open(OUT / 'links.json'))['links']:
    ot = l[4]; run = [ot[0]]
    for i in ot[1:]:
        if i == run[-1] + 1: run.append(i)
        else: break
    add(run[0], run[-1], l[5][0], l[5][-1], 'e', G.get(l[2], 1), l[0])

# --- OpenBible cross references
for f in sorted(glob.glob('/home/claude/src-ext/bible_databases/formats/sqlite/extras/cross_references_*.db')):
    for fb, fc, fv, tb, tc, ts, te, votes in sqlite3.connect(f).execute('select from_book,from_chapter,from_verse,to_book,to_chapter,to_verse_start,to_verse_end,votes from cross_references'):
        if votes < 5 or fb not in NAME or tb not in NAME: continue
        st = 3 if votes >= 50 else 2 if votes >= 15 else 1
        a = vid(NAME[fb], fc, fv); b0 = vid(NAME[tb], tc, ts); b1 = vid(NAME[tb], tc, te or ts)
        add(a, a, b0, b1 if b1 and b1 >= b0 else b0, 'x', st, votes)

def bk(i):
    lo, hi = 0, 65
    while lo < hi:
        m = (lo + hi + 1) // 2
        if BOOKS[m]['s'] <= i: lo = m
        else: hi = m - 1
    return lo
per = collections.defaultdict(list)
for src, d in pairs.items():
    for (a0, a1, b0, b1, s), (st, ex) in d.items():
        per[bk(a0)].append([a0, a1, b0, b1, s, st, ex])
        per[bk(b0)].append([b0, b1, a0, a1, s, st, ex])
(OUT / 'intertext').mkdir(exist_ok=True)
tot = 0
for b in range(66):
    rows = sorted(per[b], key=lambda r: (r[0], -r[5]))
    json.dump(rows, open(OUT / 'intertext' / f'b{b}.json', 'w'), ensure_ascii=False, separators=(',', ':')); tot += len(rows)
print({k: len(v) for k, v in pairs.items()}, 'rows', tot, 'ubs pairs seen', nq)
