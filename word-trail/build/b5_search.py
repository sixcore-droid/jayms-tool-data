"""Word Trail: the search list. One row per Strong's number: number, lemma, transliteration, plain
transliteration (no marks, for typing), a short definition, and how many uses."""
import json, glob, unicodedata, re
rows = []
def plain(s): return ''.join(c for c in unicodedata.normalize('NFD', s or '') if unicodedata.category(c) != 'Mn').lower()
for f in glob.glob('/home/claude/wt/out/word-trail/words/*.json'):
    for k, e in json.load(open(f)).items():
        d = re.split(r'[;,(]', e.get('d') or '')[0].strip()[:48]
        rows.append([k, e['l'], e['x'], re.sub(r"[^a-z ]", '', plain(e['x'])), d, len(e['v'])])
rows.sort(key=lambda r: -r[5])
json.dump(rows, open('/home/claude/wt/out/word-trail/search.json', 'w'), ensure_ascii=False, separators=(',', ':'))
print(len(rows))
