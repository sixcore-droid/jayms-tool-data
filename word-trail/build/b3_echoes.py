"""Word Trail, step 4: graded Old Testament to New Testament links, from Echoes of Revelation (James's own
dataset, 362 entries). Each link carries its verse ids so the page can ask: does this link carry the word?"""
import json, sys
sys.path.insert(0, '/home/claude/wt/build'); from refs import parse
d = json.load(open('/home/claude/wt/jayms-tool-data/echoes.json'))
out = []; bad = []
for e in d['entries']:
    nt = parse(e['bibleRef']); ot = []
    for r in e.get('otRef') or []: ot += parse(r)
    if not nt or not ot: bad.append((e['id'], e['bibleRef'], e.get('otRef'))); continue
    out.append([e['id'], e['title'], e.get('category'), e.get('how'), sorted(set(ot)), sorted(set(nt))])
json.dump({"source": "Echoes of Revelation", "links": out}, open('/home/claude/wt/out/word-trail/links.json', 'w'), ensure_ascii=False, separators=(',', ':'))
print(len(out), 'links;', len(bad), 'unparsed'); [print(b) for b in bad[:20]]
