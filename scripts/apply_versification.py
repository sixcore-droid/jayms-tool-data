"""Prompt 1: every OT reference from Hebrew (MT) numbering to the English standard."""
import json, re, sys, collections
sys.path.insert(0, '.')
import tvtms_parse as T

ORDER = {b: i for i, b in enumerate([
 'Genesis','Exodus','Leviticus','Numbers','Deuteronomy','Joshua','Judges','Ruth',
 '1 Samuel','2 Samuel','1 Kings','2 Kings','1 Chronicles','2 Chronicles','Ezra',
 'Nehemiah','Esther','Job','Psalm','Proverbs','Ecclesiastes','Song of Solomon',
 'Isaiah','Jeremiah','Lamentations','Ezekiel','Daniel','Hosea','Joel','Amos',
 'Obadiah','Jonah','Micah','Nahum','Habakkuk','Zephaniah','Haggai','Zechariah',
 'Malachi','Matthew','Mark','Luke','John','Acts','Romans','1 Corinthians',
 '2 Corinthians','Galatians','Ephesians','Philippians','Colossians',
 '1 Thessalonians','2 Thessalonians','1 Timothy','2 Timothy','Titus','Philemon',
 'Hebrews','James','1 Peter','2 Peter','1 John','2 John','3 John','Jude','Revelation'])}
REF = re.compile(r'^((?:[1-3]\s)?[A-Za-z][A-Za-z ]*?)\s+(\d+):(\d+)')

def key(ref):
    m = REF.match(str(ref))
    if not m:
        return (99, 0, 0, str(ref))
    return (ORDER.get(m.group(1), 98), int(m.group(2)), int(m.group(3)), '')

def main():
    src = json.load(open('entities.baseline.json'))
    counts = json.load(open('english-verse-counts.json'))
    changed = collections.Counter()
    for r in src:
        out, seen = [], set()
        for it in r.get('canonical') or []:
            ref = it['ref'] if isinstance(it, dict) else it
            eng = T.to_english(ref)
            item = dict(it) if isinstance(it, dict) else {'ref': ref}
            item['ref'] = eng
            if eng != ref:
                item['refHeb'] = ref
                m = REF.match(str(ref))
                changed[m.group(1) if m else '?'] += 1
            if eng in seen:          # a shift can land two refs on one verse
                continue
            seen.add(eng)
            out.append(item)
        out.sort(key=lambda x: key(x['ref']))
        r['canonical'] = out
    json.dump(src, open('entities.next.json', 'w'), ensure_ascii=False)

    # validation
    bad_pos, over = [], []
    for r in src:
        for it in r['canonical']:
            ref = it['ref']
            m = REF.match(str(ref))
            if not m:
                continue
            b, c, v = m.group(1), int(m.group(2)), int(m.group(3))
            if (b == 'Joel' and c == 4) or (b == 'Malachi' and c == 3 and v >= 19) \
               or (b == 'Numbers' and c == 17 and v >= 14) \
               or (b == '1 Chronicles' and c == 5 and v >= 27):
                bad_pos.append(ref)
            mx = (counts.get(b) or {}).get(str(c))
            if mx and v > mx:
                over.append((r['name'], ref, mx))
    print('refs changed, by book:', dict(changed))
    print('total changed:', sum(changed.values()))
    print('refs left in Hebrew-only positions:', len(bad_pos), bad_pos[:5])
    print('refs past the English verse count:', len(over))
    for x in over[:12]:
        print('   ', x)

main()
