"""Word Trail: the search list, taken from the Word Study tool's own entries (jayms-tool-data/word-study.json),
so a word is found here the same way it is found there: by its written-out name, its gloss, its lemma, its
transliteration, its Strong's number or its meaning. Row: number, lemma, name, gloss, meaning, uses, band."""
import json, unicodedata, re
def plain(s): return ''.join(c for c in unicodedata.normalize('NFD', (s or '').replace('ç', 's')) if unicodedata.category(c) != 'Mn').lower()
E = json.load(open('/home/claude/wt/jayms-tool-data/word-study.json'))['entries']
have = set()
import glob
for f in glob.glob('/home/claude/wt/out/word-trail/words/*.json'): have.update(json.load(open(f)).keys())
rows = []
for e in E:
    if e['strong'] not in have: continue
    rows.append([e['strong'], e.get('lemma', ''), e.get('title', ''), e.get('gloss', ''), ((e.get('summary') or '')[:60] + ' · ' + (e.get('kjv') or '')[:70]).strip(' ·'), e.get('count', 0), e.get('xlit', ''), 1 if e.get('band', '').startswith('key') else 0])
rows.sort(key=lambda r: (-r[7], -r[5]))
json.dump(rows, open('/home/claude/wt/out/word-trail/search.json', 'w'), ensure_ascii=False, separators=(',', ':'))
print(len(rows), 'of', len(E), 'word study entries')
