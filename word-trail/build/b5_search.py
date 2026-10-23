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
# a Fact Book name is a row of its own: search Aaron and get Aaron, brother of Moses, pointing at his word
WSI = {e['strong']: e for e in E}
for fe in json.load(open('/home/claude/wt/jayms-tool-data/fact-book.json'))['entries']:
    nums = re.findall(r'[HG]\d+', fe.get('strong') or '')
    nums = [n for n in nums if n in have] or []
    if not nums: continue
    k = next((n for n in nums if n[0] == 'H'), nums[0]); nm = fe['title'].split(' (')[0]; ds = fe['title'][len(nm):].strip(' ()')
    nref = len(fe['refs']) if isinstance(fe.get('refs'), list) else str(fe.get('refs') or '').count("',") + 1
    rows.append([k, WSI.get(k, {}).get('lemma', ''), nm, ds, '', nref, WSI.get(k, {}).get('xlit', ''), 0, fe['id'], fe.get('kind', '')])
rows.sort(key=lambda r: (-r[7], -r[5]))
json.dump(rows, open('/home/claude/wt/out/word-trail/search.json', 'w'), ensure_ascii=False, separators=(',', ':'))
print(len(rows), 'of', len(E), 'word study entries')
