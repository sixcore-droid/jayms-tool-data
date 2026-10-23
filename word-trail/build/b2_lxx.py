"""Word Trail, step 3: how the Greek Old Testament renders each Hebrew word.
Greek: QuantForgeSoftware/lxx, Swete's text lemmatized by Morpheus (CC BY-SA 4.0). Not CATSS-derived.
Hebrew: Macula Hebrew (WLC), via the occurrence index from step 2.
Method: the Hebrew and Greek of each verse are lined up (STEPBible TVTMS moves Swete's numbering to
English), then a word-alignment model (IBM Model 1, 6 rounds) learns which Greek word answers which
Hebrew word. Each Hebrew word in a verse is then credited to the Greek word it most likely became.
Counts are statistical, the way Hidden Manna's are: they say how often, not that every instance is right."""
import json, re, sys, collections, unicodedata, pathlib
sys.path.insert(0, '/home/claude/wt/build'); import lxx_map
W = pathlib.Path('/home/claude/wt'); OUT = W/'out/word-trail'
books = json.load(open(OUT/'books.json'))['books']
# Swete file -> (our book index, TVTMS abbr, chapter shift)
SW = {'Gen':(0,'Gen'),'Exod':(1,'Exo'),'Lev':(2,'Lev'),'Num':(3,'Num'),'Deut':(4,'Deu'),'Josh':(5,'Jos'),'Judg':(6,'Jdg'),'Ruth':(7,'Rut'),
 '1Kgdms':(8,'1Sa'),'2Kgdms':(9,'2Sa'),'3Kgdms':(10,'1Ki'),'4Kgdms':(11,'2Ki'),'1Chr':(12,'1Ch'),'2Chr':(13,'2Ch'),'2Esd':(None,None),
 'Esth':(16,'Est'),'Job':(17,'Job'),'Ps':(18,'Psa'),'Prov':(19,'Pro'),'Song':(21,'Sng'),'Isa':(22,'Isa'),'Jer':(23,'Jer'),'Lam':(24,'Lam'),
 'Ezek':(25,'Ezk'),'DanTh':(26,'Dan'),'Hos':(27,'Hos'),'Joel':(28,'Jol'),'Amos':(29,'Amo'),'Obad':(30,'Oba'),'Jonah':(31,'Jon'),
 'Mic':(32,'Mic'),'Nah':(33,'Nam'),'Hab':(34,'Hab'),'Zeph':(35,'Zep'),'Hag':(36,'Hag'),'Zech':(37,'Zec'),'Mal':(38,'Mal')}
SKIP_POS = {'RA'}
FUNC = {'RP', 'C-', 'RD', 'RR', 'X-', 'P-', 'D-'}   # pronouns and conjunctions: a Hebrew suffix or prefix with no number of its own lands here            # the article answers Hebrew prefixes that carry no Strong's number
def lev(a, b):
    if abs(len(a) - len(b)) > 1: return 9
    p = list(range(len(b) + 1))
    for i, x in enumerate(a, 1):
        q = [i]
        for j, y in enumerate(b, 1): q.append(min(p[j] + 1, q[j-1] + 1, p[j-1] + (x != y)))
        p = q
    return p[-1]
def strip(s): return ''.join(c for c in unicodedata.normalize('NFD', s) if unicodedata.category(c) != 'Mn').lower()
# English verse id lookup
def vid(bi, c, v):
    b = books[bi]
    if c < 1 or c > len(b['c']) or v < 1 or v > b['c'][c-1]: return None
    return b['s'] + sum(b['c'][:c-1]) + v - 1
greek = collections.defaultdict(list)       # verse id -> [lemma]
forms = collections.defaultdict(collections.Counter)
gpos = collections.defaultdict(collections.Counter)
for f in sorted((W/'lxx/data/morph-morpheus').glob('*.txt')):
    ab = f.stem.split('-', 1)[1]
    if ab not in SW: continue
    for line in open(f, encoding='utf-8'):
        p = line.split()
        if len(p) < 9: continue
        bcv, pos, lem, src = p[0], p[1], p[6], p[8]
        c, v = int(bcv[3:6]), int(bcv[6:9])
        if pos in SKIP_POS: continue
        if src == 'morpheus-proper' and lem.isupper(): continue      # OCR verse-initial capitals
        bi, tv = SW[ab]
        if ab == '2Esd':
            bi, tv = (14, 'Ezr') if c <= 10 else (15, 'Neh')
            if c > 10: c -= 10
        b2, ec, ev = lxx_map.english(tv, c, v)
        i = vid(bi, ec, ev)
        if i is None: continue
        lem = re.sub(r'\d+$', '', lem)
        greek[i].append(lem); forms[lem][p[4]] += 1; gpos[lem][pos] += 1
# Hebrew per verse, from the occurrence shards
heb = collections.defaultdict(list)
for f in (OUT/'words').glob('H*.json'):
    for s, e in json.load(open(f)).items():
        for i in e['v']: heb[i].append(s)
pairs = [(heb[i], greek[i]) for i in heb if greek.get(i)]
print('verse pairs', len(pairs), 'of', len(heb), 'Hebrew verses')
# IBM Model 1, both ways. f[x][y] = P(y | x), trained with x on the "source" side.
def model1(P, rounds=6):
    t = collections.defaultdict(lambda: collections.defaultdict(lambda: 1.0))
    for it in range(rounds):
        cnt = collections.defaultdict(lambda: collections.defaultdict(float)); tot = collections.defaultdict(float)
        for S, T in P:
            Sn = S + ['NULL']
            for y in T:
                z = sum(t[x][y] for x in Sn)
                for x in Sn:
                    c = t[x][y] / z; cnt[x][y] += c; tot[x] += c
        t = collections.defaultdict(lambda: collections.defaultdict(lambda: 1e-9))
        for x, row in cnt.items():
            for y, c in row.items(): t[x][y] = c / tot[x]
    return t
t = model1(pairs); print('Hebrew -> Greek trained')
r = model1([(G, H) for H, G in pairs]); print('Greek -> Hebrew trained')
# credit each Greek word to the Hebrew word that most likely produced it (the model's own direction)
render = collections.defaultdict(collections.Counter)
for H, G in pairs:
    Hn = H + ['NULL']
    for g in G:
        h = max(Hn, key=lambda x: t[x][g] * (r[g][x] if x != 'NULL' else r[g]['NULL']))
        if h != 'NULL': render[h][g] += 1
# lemma -> Greek Strong's where one exists (NT vocabulary)
lex = json.load(open(W/'interlinear-data/lexicon.json'))
g2s = {}
for k, e in lex.items():
    e = e.get('strong') or {}
    if k.startswith('G') and e.get('lemma'): g2s.setdefault(strip(e['lemma']).strip(), k)
out = {}; fnw = []
for h, ctr in render.items():
    rows = []
    func = lambda g: gpos[g].most_common(1)[0][0] in FUNC
    top = ctr.most_common(1)[0][0]
    total = sum(ctr.values())
    # a rendering is kept when the Greek word is a real answer to this Hebrew word in its own right
    # (the reverse model gives this Hebrew word at least a tenth of it), or it carries 5% of the total
    items = [(g, n) for g, n in ctr.most_common(30) if (func(top) or not func(g))
             and (g == top or n >= max(3, ctr[top] * 0.004)) and (g == top and n >= 3 or r[g][h] >= 0.02 and (r[g][h] >= 0.10 or n / total >= 0.05))]
    for g, n in items:
        # an OCR slip in Swete parses as a near-spelling (κύριος read as Τύριος): fold it into the word it was
        twin = next((row for row in rows if lev(strip(row[0]), strip(g)) <= 1 and n < row[2] * 0.1), None)
        if twin: twin[2] += n; continue
        rows.append([g, g2s.get(strip(g)), n])
    rows = rows[:8]
    out[h] = rows
    if func(top): fnw.append(h)
inv = collections.defaultdict(list)
hlex = {}
for f in (OUT/'words').glob('H*.json'):
    for k, e in json.load(open(f)).items(): hlex[k] = (e['l'], e['x'])
for h, rows in out.items():
    for g, gs, n in rows:
        if gs: inv[gs].append([h, n, hlex.get(h, ('', ''))[0], hlex.get(h, ('', ''))[1]])
for gs in inv: inv[gs].sort(key=lambda x: -x[1]); inv[gs] = inv[gs][:8]
json.dump({"h": out, "g": inv, "missing": ["Ecclesiastes"], "fn": sorted(fnw)}, open(OUT/'greek-ot.json', 'w'), ensure_ascii=False, separators=(',', ':'))
for h in ['H776', 'H430', 'H3068', 'H1285', 'H2617', 'H7307']:
    print(h, out.get(h, [])[:5])
if '--debug' in sys.argv:
    inv = collections.Counter()
    for h, ctr in render.items():
        if ctr.get('χώρα'): inv[h] = ctr['χώρα']
    print('χώρα from', inv.most_common(6))
    print('verses with erets and χώρα', sum(1 for H, G in pairs if 'H776' in H and 'χώρα' in G))
    print('t(χώρα|H776)', t['H776']['χώρα'], 't(γῆ|H776)', t['H776']['γῆ'])
