"""Reference strings -> English verse ids on the Word Trail grid (books.json)."""
import json, re
BOOKS = json.load(open('/home/claude/wt/out/word-trail/books.json'))['books']
NAME = {b['n'].lower(): i for i, b in enumerate(BOOKS)}
NAME.update({'song of songs': 21, 'psalm': 18, 'canticles': 21})
RX = re.compile(r'^\s*((?:[1-3]\s)?[A-Za-z][A-Za-z ]*?)\s+(\d+)(?::(\d+))?(?:\s*[-–]\s*(?:(\d+):)?(\d+))?\s*$')
def vid(bi, c, v):
    b = BOOKS[bi]
    if not (1 <= c <= len(b['c'])): return None
    v = min(max(v, 1), b['c'][c-1])
    return b['s'] + sum(b['c'][:c-1]) + v - 1
def parse(ref, last_book=None):
    """'Daniel 2:28-29' -> list of verse ids. Whole chapters and cross-chapter ranges allowed."""
    out = []
    for part in re.split(r'\s*;\s*', ref.strip()):
        m = RX.match(part)
        if not m:
            m2 = re.match(r'^\s*(\d+)(?::(\d+))?(?:\s*[-–]\s*(?:(\d+):)?(\d+))?\s*$', part)
            if not (m2 and last_book is not None): continue
            bi = last_book; c, v, c2, v2 = m2.groups()
        else:
            name, c, v, c2, v2 = m.groups(); bi = NAME.get(name.strip().lower())
            if bi is None: continue
        last_book = bi; c = int(c)
        if v is None:                                   # whole chapter, or chapter range
            ce = int(v2) if v2 else c
            a, z = vid(bi, c, 1), vid(bi, ce, 999)
        else:
            v = int(v); a = vid(bi, c, v)
            if v2: z = vid(bi, int(c2) if c2 else c, int(v2))
            else: z = a
        if a is not None and z is not None: out.extend(range(a, z + 1))
    return out
