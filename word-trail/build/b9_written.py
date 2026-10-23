"""Word Timelines: the "When it was written" chart, on the same engine as "When it happened".
Streams: the Old Testament written, the New Testament written, the New Testament gathered, the copies we hold,
when the copies came to light, translations, print and modern editions.

Every Old Testament book carries the gap between its writing and the oldest copy of it here, worked out from the
Dead Sea Scrolls per-verse data (ETCBC/dss, Abegg) and the Leningrad Codex. New Testament dates come only from this
site's own timelines; books those timelines do not date are listed in the note, not guessed.
Run after b4_time.py, b8_nowiki.py and b7_chart.py (it reads clock.json and church_data.py)."""
import json, sys
from pathlib import Path
OUT = Path('/home/claude/wt/out/word-trail')
sys.path.insert(0, str(Path(__file__).parent)); import church_data as CD
K = json.load(open(OUT / 'clock.json')); BK = json.load(open(OUT / 'books.json'))['books']
def yl(y): return ('%d BC' % -y) if y < 0 else ('AD %d' % y)
def sp(a, b): return yl(a) if a == b else (('%d to %d BC' % (-a, -b)) if b < 0 else yl(a) + ' to ' + yl(b))
# Dead Sea Scrolls verses per book
dssv = {int(k) for k in K['dss']['verses']}
def bookof(i):
    lo, hi = 0, 65
    while lo < hi:
        m = (lo + hi + 1) // 2
        if BK[m]['s'] <= i: lo = m
        else: hi = m - 1
    return lo
surv = {}
for i in dssv: b = bookof(i); surv[b] = surv.get(b, 0) + 1
W = {w['k']: w for w in K['witness']}
S = []
def stream(k, n, c, items, src): S.append({'k': k, 'n': n, 'c': c, 'items': sorted(items, key=lambda x: (x['a'], -x['b'])), 'src': src, 'url': ''})
ot = []
for b in range(39):
    n = BK[b]['n']; A = K['datesA']['books'].get(n)
    if not A: continue
    nv = sum(BK[b]['c'])
    if surv.get(b):
        d = W['dss']; gap = d['copy'][0] - A[1]
        more = ('Oldest copy here: the Dead Sea Scrolls, copied %s. %s of its %s verses survive in at least one scroll (%d scrolls). '
                'From the writing to the earliest scrolls: as little as about %s years.') % (sp(*d['copy']), format(surv[b], ','), format(nv, ','), K['dss']['perBook'].get(n, 0), format(max(gap, 0), ','))
    else:
        d = W['len']; gap = d['copy'][0] - A[1]
        more = 'No verse of it survives in the Dead Sea Scrolls data used here. Oldest complete copy here: the Leningrad Codex, copied AD 1008, about %s years after the writing.' % format(gap, ',')
    if n == 'Numbers': more += ' The silver scrolls of Ketef Hinnom, copied 650 to 587 BC, carry a form of its priestly blessing (6:24-26).'
    if n in ('Exodus', 'Deuteronomy'): more += ' The Nash Papyrus, copied 150 to 100 BC, carries the Ten Commandments' + (' and the opening of the Shema.' if n == 'Deuteronomy' else '.')
    ot.append({'n': n, 'a': A[0], 'b': A[1], 't': 'seg' if A[1] > A[0] else 'pt', 'd': 'Written ' + sp(A[0], A[1]), 'more': more})
stream('ot', 'The Old Testament written', 'gold', ot, K['datesA']['name'] + ' dates, as Logos shows them')
WRIT = [r for r in CD.ROWS if r[0] in ('canon', 'apostles') and ('written' in r[4].lower() or r[4].startswith(('Ephesians', 'John:')))]
nt = [{'n': r[4].replace(' written', ''), 'a': r[2], 'b': r[3], 't': 'seg' if r[3] > r[2] else 'pt', 'd': r[1], 'url': r[5]} for r in WRIT]
stream('nt', 'The New Testament written', 'blue', nt, "This site's Apostle Paul, Twelve Apostles and New Testament Canon timelines")
gath = [{'n': r[4], 'a': r[2], 'b': r[3], 't': 'seg' if r[3] > r[2] else 'pt', 'd': r[1], 'url': r[5]} for r in CD.ROWS if r[0] == 'canon' and r not in WRIT]
stream('canon', 'The New Testament gathered', 'aramaic', gath, "This site's New Testament Canon timeline")
cop = [{'n': w['n'], 'a': w['copy'][0], 'b': w['copy'][1], 't': 'seg' if w['copy'][1] > w['copy'][0] else 'pt', 'd': 'Copied ' + sp(*w['copy']),
        'more': w['has'] + '. Held: ' + w['held'] + '.'} for w in K['witness']]
stream('copies', 'The copies we hold', 'olive', cop, 'Standard dates, not yet checked in Logos')
fnd = [{'n': w['n'] + ' found', 'a': w['found'], 'b': w['found'], 't': 'flag', 'd': 'Came to light ' + yl(w['found']) + ('. ' + w['foundNote'] if w.get('foundNote') else ''), 'more': w['place'] + '.'} for w in K['witness']]
stream('found', 'When the copies came to light', 'ink-soft', fnd, 'Standard dates, not yet checked in Logos')
# Second Temple and rabbinic writings, dated only as this site and the study notes date them
ENP = 'https://jayms.com/book-of-enoch/'
SS = 'Strack-Stemberger, Introduction to the Talmud and Midrash (1996), via the study notes'
per = [{'n': 'Persian period', 'a': -539, 'b': -332, 't': 'seg', 'd': '539 to 332 BC', 'more': "Cyrus's decree; the temple rebuilt (finished 516 BC); Ezra and Nehemiah."},
       {'n': 'Hellenistic period', 'a': -332, 'b': -167, 't': 'seg', 'd': '332 to 167 BC', 'more': 'Alexander and after; the Torah put into Greek.'},
       {'n': 'Maccabean and Hasmonean period', 'a': -167, 'b': -63, 't': 'seg', 'd': '167 to 63 BC', 'more': 'Antiochus IV desecrates the temple (167 BC); the revolt; Pharisees, Sadducees and Essenes take shape.'},
       {'n': 'Roman period', 'a': -63, 'b': 70, 't': 'seg', 'd': '63 BC to AD 70', 'more': 'Pompey takes Jerusalem; Herod rebuilds the temple; Rome destroys it in AD 70.'}]
stream('period', 'The Second Temple period', 'ink-soft', per, 'Study notes: Second Temple Judaism')
st = [{'n': '1 Enoch', 'a': -300, 'b': -1, 't': 'seg', 'd': 'Assembled in stages across the last three centuries BC', 'url': ENP,
       'more': 'Five books in one: the Watchers, the Parables, the Astronomical Book, the Dream Visions, the Epistle. Aramaic fragments at Qumran show it growing; Jude 14-15 quotes it. The date of the Parables is disputed.'},
      {'n': 'Jubilees', 'a': -300, 'b': -1, 't': 'seg', 'd': 'The last centuries BC, as this site places it', 'url': ENP,
       'more': 'Genesis and early Exodus retold as revelation to Moses, cut into 49-year jubilees, on a solar calendar. Several copies at Qumran; canonical in the Ethiopian Orthodox Church.'},
      {'n': '2 Enoch', 'a': 1, 'b': 100, 't': 'seg', 'd': '1st century AD or later; scholars disagree', 'url': ENP,
       'more': 'Slavonic Enoch, a journey through the heavens. How much is Jewish and how much was reworked by Christians is argued.'},
      {'n': '4 Ezra and 2 Baruch', 'a': 70, 'b': 70, 't': 'pt', 'd': 'Written after AD 70', 'more': "Both wrestle with the temple's destruction."},
      {'n': '3 Enoch', 'a': 400, 'b': 600, 't': 'seg', 'd': '5th or 6th century AD', 'url': ENP,
       'more': 'Hebrew Enoch, Sefer Hekhalot: Enoch made into the angel Metatron. Jewish mystical literature, centuries after 1 Enoch.'}]
stream('second', 'Second Temple writings', 'aramaic', st, "This site's Which Book of Enoch? post and the study notes")
rab = [{'n': 'Mishnah', 'a': 200, 'b': 200, 'd': 'c. 200'}, {'n': 'Tosefta', 'a': 250, 'b': 250, 'd': 'c. 250'},
       {'n': 'Palestinian Talmud', 'a': 400, 'b': 400, 'd': 'c. 400'}, {'n': 'Genesis Rabbah', 'a': 400, 'b': 450, 'd': '5th century, probably the first half'},
       {'n': 'Lamentations Rabbah', 'a': 400, 'b': 450, 'd': '5th century, probably the first half'}, {'n': 'Leviticus Rabbah', 'a': 400, 'b': 500, 'd': 'Redaction c. 400 to 500'},
       {'n': 'Pesiqta de Rab Kahana', 'a': 400, 'b': 500, 'd': '5th century, about the time of Leviticus Rabbah'}, {'n': 'Ruth Rabbah', 'a': 500, 'b': 500, 'd': 'c. 500, Palestine; no rabbi cited after the 4th century'},
       {'n': 'Babylonian Talmud', 'a': 500, 'b': 600, 'd': 'c. 500 to 600'}, {'n': 'Song of Songs Rabbah', 'a': 550, 'b': 550, 'd': 'Mid-6th century (Herr)'},
       {'n': 'Deuteronomy Rabbah', 'a': 450, 'b': 800, 'd': 'c. 450 to 800; a precise date is very difficult'},
       {'n': 'Pesiqta Rabbati', 'a': 500, 'b': 700, 'd': 'Composite; the current preference is the 6th to 7th century',
        'more': 'Its Yelamdenu material, which includes chapter 44, existed by about 400 at the latest (Bohl).'}]
for r in rab: r['t'] = 'seg' if r['b'] > r['a'] else 'pt'
stream('rabbinic', 'Mishnah, Talmud and Midrash', 'olive', rab, SS)

# the other gospels, acts and letters, as the Lost Books drafts sort them: honest but not Scripture, forged in an
# apostle's name, and forged with a different Christ. Dates as the drafts give them.
LB = "Study notes: What Do the Lost Gospels Actually Say? (draft)"
def lb(n, a, b, d, more): return {'n': n, 'a': a, 'b': b, 't': 'seg' if b > a else 'pt', 'd': d, 'more': more}
honest = [lb('1 Clement', 95, 95, 'c. AD 95', 'Rome writes to Corinth about elders thrown out. No author named; the earliest outside notice of the deaths of Peter and Paul.'),
          lb('The Didache', 70, 100, 'Possibly late 1st century', 'A church manual: baptism, fasting, travelling prophets. Anonymous; only its title claims too much.'),
          lb('Letters of Ignatius', 110, 110, 'c. AD 110', 'Seven genuine letters on the road to Rome; a longer recension and extra letters are later forgeries.')]
stream('honest', 'Early writings, honest but not Scripture', 'aramaic', honest, LB)
forged = [lb('Epistle of Barnabas', 70, 132, 'Between AD 70 and 132', 'Anonymous; the name was attached later. Teaches that Israel never had the covenant.'),
          lb('Acts of Paul and Thecla', 100, 199, '2nd century', 'Its author, a presbyter in Asia, was found out and deposed (Tertullian). Celibacy as the condition of seeing God.'),
          lb('Acts of Peter', 170, 199, 'Late 2nd century', 'Source of the upside-down crucifixion and the Quo Vadis scene; also a talking dog and a revived smoked fish.'),
          lb('Acts of Andrew', 200, 299, '3rd century', 'Source of the crucifixion at Patras; the diagonal cross comes much later.'),
          lb('Gospel of Nicodemus (Acts of Pilate)', 300, 399, '4th century at the earliest', 'Written into the slot of a pagan Acts of Pilate that Maximinus Daia had schoolchildren memorize around 311. Pilate ends as a confessor.'),
          lb('Paul and Seneca letters', 300, 399, '4th century', "Fourteen empty letters in poor Latin. Jerome's notice around 393 carried them through the Middle Ages."),
          lb("Gospel of Jesus's Wife", 2012, 2016, 'Announced 2012; exposed as a forgery 2016', "Copied from a 2002 online PDF, typo and all, onto old blank papyrus; the owner identified in 2016.")]
stream('forged', "Forged in an apostle's name", 'gold', forged, LB)
other = [lb('Gospel of Thomas', 50, 150, 'Mid-1st to mid-2nd century, argued', '114 sayings: no cross, no resurrection; salvation by self-recognition. Saying 114 is the one rarely quoted.'),
         lb('Protevangelium of James', 150, 150, 'c. AD 150', "Mary's birth and perpetual virginity; the author does not know the road to Bethlehem. Condemned by Innocent I in 405."),
         lb('Gospel of Peter', 140, 160, 'Mid-2nd century', 'A walking cross and a Christ who feels no pain: docetism, caught by Serapion of Antioch around 200.'),
         lb('Infancy Gospel of Thomas', 100, 199, '2nd century', 'A boy Jesus who kills a child in temper.'),
         lb('Gospel of Mary', 100, 199, '2nd century', "The soul escaping a material world: a creation that was a mistake."),
         lb('Gospel of Judas', 100, 180, '2nd century; Irenaeus knew it c. 180', 'The God of Israel as the villain and the cross as a jailbreak.'),
         lb('Epistle to the Laodiceans', 170, 200, 'Named as forged in the Muratorian list (c. 170 to 200)', "Stitched from Philippians to serve Marcion's two gods."),
         lb('Acts of John', 150, 299, '2nd to 3rd century', 'A Christ with no footprints, elsewhere during his own crucifixion. Nicaea II (787) ordered it burned.'),
         lb('Acts of Thomas', 200, 299, '3rd century', 'Christ appears in Thomas\'s likeness on a wedding night to talk the couple out of their marriage.'),
         lb('Gospel of Philip', 200, 299, '3rd century', 'Valentinian; the famous Mary Magdalene line sits on a hole in the page.')]
stream('other', 'Forged, with a different Christ', 'rust', other, LB)

tr = [{'n': t['n'], 'a': t['y'][0], 'b': t['y'][1], 't': 'seg' if t['y'][1] > t['y'][0] else 'pt', 'd': t['note'], 'url': t.get('src', '')} for t in K['translations']]
stream('trans', 'Translations', 'lavender', [x for x in tr if x['a'] < 1450], 'Standard dates, not yet checked in Logos')
stream('print', 'Print and modern editions', 'rust', [x for x in tr if x['a'] >= 1450], 'Standard dates, not yet checked in Logos')
ORDER = ['period', 'ot', 'second', 'nt', 'honest', 'forged', 'other', 'canon', 'rabbinic', 'copies', 'found', 'trans', 'print']
S.sort(key=lambda x: ORDER.index(x['k']))
ERAS = [['Full view', -2200, 2026], ['Torah to the kings', -1600, -900], ['Prophets and exile', -800, -400], ['The Second Temple', -560, 80],
        ['The New Testament', 40, 110], ['The other gospels', 40, 420], ['Mishnah, Talmud, Midrash', 150, 820], ['Gathered and copied', 90, 1100], ['Print to today', 1370, 2026]]
undated = ['Matthew', 'Mark', 'Luke', 'John', 'Acts', 'Hebrews', 'James', 'Jude']
NOTE = ("Each Old Testament book carries how close its oldest copy here comes to its writing; pick a book to see it. "
        "The New Testament books are dated only where this site's own timelines date them, so the four Gospels share one span and "
        "Acts, Hebrews, James and Jude are not on the chart yet. Old Testament dates are the Bible Knowledge Commentary's. "
        "Of the Second Temple writings, only those this site or the study notes date are placed; Tobit, Judith, Wisdom, Sirach, Baruch, "
        "1 and 2 Maccabees, the Psalms of Solomon, the Testaments of the Twelve Patriarchs and the halakhic midrashim wait for a Logos date. "
        "The other gospels, acts and letters are sorted and dated as the Lost Books drafts sort them; the Shepherd of Hermas has no date there yet.")
SOURCES = [['Old Testament written: Bible Knowledge Commentary dates, as shown in Logos.', ''],
           ["New Testament written and gathered: this site's Apostle Paul, Twelve Apostles and New Testament Canon timelines.", 'https://jayms.com/bible-study-tools-2/how-we-got-the-new-testament-canon-timeline/'],
           ["Second Temple writings: this site's Which Book of Enoch? post, the Lost Books draft and the Second Temple Judaism study note.", 'https://jayms.com/book-of-enoch/'],
           ['The other gospels, acts and letters: the Lost Books drafts in the study notes (Ehrman and Plese, Elliott, Metzger, Bruce, Kruger, DeConick, Vuong, Bernhard, Askeland, Sabar).', ''],
           ['Mishnah, Talmud and Midrash: Strack and Stemberger, Introduction to the Talmud and Midrash, 2nd English ed. (Fortress, 1996), as the study notes record it.', ''],
           ['Which verses survive in the Dead Sea Scrolls: ETCBC/dss, Martin Abegg\'s data, CC BY-NC 4.0.', 'https://github.com/ETCBC/dss'],
           ['Manuscripts, find dates, translations and print: standard dates, not yet checked in Logos.', '']]
json.dump({'streams': S, 'range': [-2200, 2026], 'eras': ERAS, 'note': NOTE, 'sources': SOURCES, 'find': 'Find a book, a manuscript or a translation'}, open(OUT / 'written.json', 'w'), ensure_ascii=False, separators=(',', ':'))
print([(s['k'], len(s['items'])) for s in S])
