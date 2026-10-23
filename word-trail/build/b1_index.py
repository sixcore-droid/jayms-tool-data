"""Word Trail, step 2: every Hebrew OT and Greek NT Strong's number, every verse it stands in.
Source: sixcore-droid/interlinear-data (Macula Hebrew WLC / Macula Greek SBLGNT, CC BY 4.0).
Verse numbers are moved to English numbering with jayms-tool-data/versification-map.json, so the
chart, the reader and the LXX alignment all count the same verses."""
import json, re, collections, pathlib
W = pathlib.Path('/home/claude/wt'); IL = W/'interlinear-data'; OUT = W/'out/word-trail'
BOOKS = [("Genesis","genesis","Ge"),("Exodus","exodus","Ex"),("Leviticus","leviticus","Le"),("Numbers","numbers","Nu"),("Deuteronomy","deuteronomy","Dt"),("Joshua","joshua","Jos"),("Judges","judges","Jdg"),("Ruth","ruth","Ru"),("1 Samuel","1samuel","1Sa"),("2 Samuel","2samuel","2Sa"),("1 Kings","1kings","1Ki"),("2 Kings","2kings","2Ki"),("1 Chronicles","1chronicles","1Ch"),("2 Chronicles","2chronicles","2Ch"),("Ezra","ezra","Ezr"),("Nehemiah","nehemiah","Ne"),("Esther","esther","Es"),("Job","job","Job"),("Psalms","psalms","Ps"),("Proverbs","proverbs","Pr"),("Ecclesiastes","ecclesiastes","Ec"),("Song of Solomon","songofsolomon","So"),("Isaiah","isaiah","Is"),("Jeremiah","jeremiah","Je"),("Lamentations","lamentations","La"),("Ezekiel","ezekiel","Eze"),("Daniel","daniel","Da"),("Hosea","hosea","Ho"),("Joel","joel","Joe"),("Amos","amos","Am"),("Obadiah","obadiah","Ob"),("Jonah","jonah","Jon"),("Micah","micah","Mic"),("Nahum","nahum","Na"),("Habakkuk","habakkuk","Hab"),("Zephaniah","zephaniah","Zep"),("Haggai","haggai","Hag"),("Zechariah","zechariah","Zec"),("Malachi","malachi","Mal"),
("Matthew","matthew","Mt"),("Mark","mark","Mk"),("Luke","luke","Lk"),("John","john","Jn"),("Acts","acts","Ac"),("Romans","romans","Ro"),("1 Corinthians","1corinthians","1Co"),("2 Corinthians","2corinthians","2Co"),("Galatians","galatians","Ga"),("Ephesians","ephesians","Eph"),("Philippians","philippians","Php"),("Colossians","colossians","Col"),("1 Thessalonians","1thessalonians","1Th"),("2 Thessalonians","2thessalonians","2Th"),("1 Timothy","1timothy","1Ti"),("2 Timothy","2timothy","2Ti"),("Titus","titus","Tit"),("Philemon","philemon","Phm"),("Hebrews","hebrews","Heb"),("James","james","Jas"),("1 Peter","1peter","1Pe"),("2 Peter","2peter","2Pe"),("1 John","1john","1Jn"),("2 John","2john","2Jn"),("3 John","3john","3Jn"),("Jude","jude","Jud"),("Revelation","revelation","Re")]
VMAP = json.load(open(W/'jayms-tool-data/versification-map.json'))
def eng(book, c, v):
    for lo, hi, ec, off in VMAP.get(book, {}).get(str(c), []):
        if lo <= v <= hi:
            return (ec, 1) if off == 'fold' else (ec, v + off)
    return (c, v)
files = {p.stem: p for p in IL.glob('*.json')}
LEX0 = json.load(open(IL/'lexicon.json'))
cons = lambda t: re.sub(r'[^\u05D0-\u05EA]', '', t)
PREFIXNUM = {k: cons(LEX0[k]['strong']['lemma']) for k in ('H2050', 'H1886', 'H3807', 'H871', 'H3509')}
# pass 1: English verse grid per book
grid = []          # per book: {chapter: maxverse}
tok = []           # (bookIdx, ec, ev, strong)
gloss = collections.defaultdict(collections.Counter)   # strong -> English glosses as the interlinear gives them
for bi, (name, slug, ab) in enumerate(BOOKS):
    f = files.get(slug) or files.get(slug.replace('songofsolomon','songofsongs'))
    d = json.load(open(f))
    g = collections.defaultdict(int)
    for c, ch in d['chapters'].items():
        for v, vs in ch['verses'].items():
            ec, ev = eng(name, int(c), int(v))
            if ev < 1: ev = 1
            g[ec] = max(g[ec], ev)
            for w in vs['words']:
                s = str(w.get('strong') or '')
                # Macula gives the one-letter prefixes (and, the, to, in, like) numbers that belong to other
                # words (H2050, H1886, H3807, H871, H3509). A prefix is a single consonant: skip it.
                if bi < 39 and len(re.sub(r'[^\u05D0-\u05EA]', '', w.get('lemma', ''))) == 1: continue
                # and the compounds built on them (lakhen, lamah) carry the same borrowed number
                if bi < 39 and s in PREFIXNUM and cons(w.get('lemma', '')) != PREFIXNUM[s]: continue
                m = re.match(r'^([HG]?)0*(\d+)', s)
                if m:
                    sid = (m.group(1) or ('H' if bi < 39 else 'G')) + m.group(2)
                    tok.append((bi, ec, ev, sid))
                    gl = (w.get('gloss') or '').replace('.', ' ').strip()
                    if gl and not gl.startswith('('): gloss[sid][gl] += 1
    grid.append(dict(sorted(g.items())))
# verse ids in canonical order
vid = {}; n = 0; books_out = []
for bi, (name, slug, ab) in enumerate(BOOKS):
    start = n; chs = []
    for c in range(1, max(grid[bi]) + 1):
        mv = grid[bi].get(c, 0); chs.append(mv)
        for v in range(1, mv + 1): vid[(bi, c, v)] = n; n += 1
    books_out.append({"n": name, "a": ab, "t": "ot" if bi < 39 else "nt", "s": start, "c": chs})
occ = collections.defaultdict(list)
for bi, c, v, s in tok: occ[s].append(vid[(bi, c, v)])
lex = json.load(open(IL/'lexicon.json'))
# Word Study is the site's own word list: its display name (adonay) and gloss (Lord) are what Word Trail shows and searches
WS = {e['strong']: e for e in json.load(open(W/'jayms-tool-data/word-study.json'))['entries']}
# the Fact Book: every person, place, group and term, keyed by the numbers it carries. Its summary is the
# "First, in plain English" section of the entry, James's own prose, so Word Trail shows it as written.
FB = collections.defaultdict(list)
for fe in json.load(open(W/'jayms-tool-data/fact-book.json'))['entries']:
    nm = fe['title'].split(' (')[0]; ds = fe['title'][len(nm):].strip(' ()')
    nref = len(fe['refs']) if isinstance(fe.get('refs'), list) else str(fe.get('refs') or '').count("',") + 1
    for k in re.findall(r'[HG]\d+', fe.get('strong') or ''):
        FB[k].append([fe['id'], nm, ds, fe.get('summary') or '', fe.get('kind') or fe.get('category') or '', nref])
for k in FB: FB[k].sort(key=lambda r: -r[5])
shards = collections.defaultdict(dict)
for s, ids in occ.items():
    e = (lex.get(s) or {}).get('strong') or {}
    shards[s[0] + str(int(s[1:]) // 100)][s] = {"l": e.get("lemma",""), "x": e.get("xlit",""), "d": e.get("def",""), "p": e.get("pron",""), "t": WS.get(s, {}).get('title', ''), "g": WS.get(s, {}).get('gloss') or (gloss[s].most_common(1)[0][0] if gloss[s] else ""), "fb": FB.get(s, []), "v": sorted(ids)}
(OUT/'words').mkdir(parents=True, exist_ok=True)
for k, v in shards.items():
    json.dump(v, open(OUT/f'words/{k}.json', 'w'), ensure_ascii=False, separators=(',', ':'))
json.dump({"verses": n, "books": books_out}, open(OUT/'books.json', 'w'), separators=(',', ':'))
print('verses', n, 'tokens', len(tok), 'strongs', len(occ), 'shards', len(shards))
for s in ['H776', 'G1093', 'H430', 'G2316']:
    ids = occ[s]; print(s, len(ids), 'uses in', len(set(ids)), 'verses')
