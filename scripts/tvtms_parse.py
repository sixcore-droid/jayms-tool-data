"""TVTMS: Hebrew (MT) verse references to the English standard.

Source: STEPBible-Data, "TVTMS - Translators Versification Traditions with
Methodology for Standardisation", CC BY 4.0, github.com/STEPBible/STEPBible-Data.
Credit: STEP Bible, https://www.STEPBible.org

TVTMS is an action table, not a lookup: each row says what a tradition does at
a verse (Keep, Renumber, Concatenation, Merged, Divided). For this job only one
direction is wanted, Hebrew source to English standard, so the rows whose
SourceType names the Hebrew tradition are read and the pairs where the two refs
differ become the map. Rows where they agree are no-ops and are skipped.

The English verse counts used for validation are derived from the StandardRef
column of the same file, so the table and the check cannot drift apart.
"""
import io, re, os, collections

HERE = os.path.dirname(os.path.abspath(__file__))
TVTMS = os.path.join(HERE, 'step', 'TVTMS.txt')

# TVTMS abbreviation -> the book name this project writes.
BOOK = {
 'Gen':'Genesis','Exo':'Exodus','Lev':'Leviticus','Num':'Numbers','Deu':'Deuteronomy',
 'Jos':'Joshua','Jdg':'Judges','Rut':'Ruth','1Sa':'1 Samuel','2Sa':'2 Samuel',
 '1Ki':'1 Kings','2Ki':'2 Kings','1Ch':'1 Chronicles','2Ch':'2 Chronicles',
 'Ezr':'Ezra','Neh':'Nehemiah','Est':'Esther','Job':'Job','Psa':'Psalm',
 'Pro':'Proverbs','Ecc':'Ecclesiastes','Sng':'Song of Solomon','Isa':'Isaiah',
 'Jer':'Jeremiah','Lam':'Lamentations','Ezk':'Ezekiel','Dan':'Daniel','Hos':'Hosea',
 'Jol':'Joel','Amo':'Amos','Oba':'Obadiah','Jon':'Jonah','Mic':'Micah','Nam':'Nahum',
 'Hab':'Habakkuk','Zep':'Zephaniah','Hag':'Haggai','Zec':'Zechariah','Mal':'Malachi',
 'Mat':'Matthew','Mrk':'Mark','Luk':'Luke','Jhn':'John','Act':'Acts','Rom':'Romans',
 '1Co':'1 Corinthians','2Co':'2 Corinthians','Gal':'Galatians','Eph':'Ephesians',
 'Php':'Philippians','Col':'Colossians','1Th':'1 Thessalonians','2Th':'2 Thessalonians',
 '1Ti':'1 Timothy','2Ti':'2 Timothy','Tit':'Titus','Phm':'Philemon','Heb':'Hebrews',
 'Jas':'James','1Pe':'1 Peter','2Pe':'2 Peter','1Jn':'1 John','2Jn':'2 John',
 '3Jn':'3 John','Jud':'Jude','Rev':'Revelation',
}

# Gen.32:33, Psa.77:21, 1Ch.5:29!a, Gen.2:25-3:1
REF = re.compile(r'^([1-3A-Za-z]{3})\.(\d+):(\d+)')


def _rows():
    lines = io.open(TVTMS, encoding='utf-8-sig')
    started = False
    for l in lines:
        c = [x.strip() for x in l.rstrip('\n').split('\t')]
        if not started:
            if len(c) > 3 and 'SourceRef' in c and 'StandardRef' in c:
                started = True
            continue
        if len(c) < 4 or not c[1] or not c[2]:
            continue
        yield c[0], c[1], c[2], c[3]


def _parse(ref):
    m = REF.match(ref)
    if not m:
        return None
    b = BOOK.get(m.group(1))
    return (b, int(m.group(2)), int(m.group(3))) if b else None


def build():
    """(hebrew -> english map, english verse counts)."""
    shift, counts = {}, collections.defaultdict(int)
    for stype, src, std, action in _rows():
        s, t = _parse(src), _parse(std)
        if t:
            counts[(t[0], t[1])] = max(counts[(t[0], t[1])], t[2])
        if 'Hebrew' not in stype or not s or not t:
            continue
        if s == t:
            continue
        # A later row never overwrites an earlier one: the table lists the
        # plain renumbering first and the subverse and merge cases after it.
        shift.setdefault(s, t)
    return shift, dict(counts)


SHIFT, VERSES = build()


def to_english(ref):
    """A Hebrew-numbered reference as the English standard writes it.

    Anything already English, or not a reference at all, comes back unchanged.
    """
    m = re.match(r'^((?:[1-3]\s)?[A-Za-z][A-Za-z ]*?)\s+(\d+):(\d+)(.*)$', str(ref))
    if not m:
        return ref
    key = (m.group(1), int(m.group(2)), int(m.group(3)))
    t = SHIFT.get(key)
    if not t:
        return ref
    return '%s %d:%d%s' % (t[0], t[1], t[2], m.group(4))


TESTS = [
 ('Joel 4:1', 'Joel 3:1'),
 ('Malachi 3:19', 'Malachi 4:1'),
 ('Numbers 17:16', 'Numbers 17:1'),
 ('Numbers 17:23', 'Numbers 17:8'),
 ('1 Chronicles 5:29', '1 Chronicles 6:3'),
 ('Psalm 77:21', 'Psalm 77:20'),
 ('Exodus 8:1', 'Exodus 8:5'),
 ('Genesis 32:33', 'Genesis 32:32'),
 ('John 1:1', 'John 1:1'),          # NT is untouched
 ('Genesis 1:1', 'Genesis 1:1'),    # a verse the traditions agree on
]

if __name__ == '__main__':
    bad = [(a, b, to_english(a)) for a, b in TESTS if to_english(a) != b]
    print('map entries:', len(SHIFT), '| chapters with a verse count:', len(VERSES))
    for a, want, got in bad:
        print('FAIL %-22s want %-22s got %s' % (a, want, got))
    print('tests:', 'all pass' if not bad else '%d FAILED' % len(bad))
