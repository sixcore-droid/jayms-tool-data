"""Word Trail, steps 5-7: the two clocks.
clock.json  when each book was written, the copies that survive, when they came to light, and the
            translations and print editions after them.
world.json  when the people lived and the events happened, and the world around them."""
import json, sys, collections
sys.path.insert(0, '/home/claude/wt/build'); from refs import parse, BOOKS
W = '/home/claude/wt'
WIKI = 'https://en.wikipedia.org/wiki/'
# --- dates A: the Bible Knowledge Commentary, as Logos's timeline displays it (James's screenshot, 2026-10-22).
# Zechariah's row is partly cut off at the foot of the screenshot; Malachi is below it and not shown.
BKC = {'Genesis':(-1500,-1401),'Exodus':(-1500,-1401),'Leviticus':(-1500,-1401),'Numbers':(-1406,-1406),'Deuteronomy':(-1500,-1401),
 'Joshua':(-1406,-1406),'Judges':(-1051,-1004),'Ruth':(-1011,-931),'1 Samuel':(-931,-722),'2 Samuel':(-931,-722),'1 Kings':(-560,-538),
 '2 Kings':(-560,-538),'1 Chronicles':(-598,-400),'2 Chronicles':(-598,-400),'Ezra':(-450,-450),'Nehemiah':(-430,-430),'Esther':(-470,-465),
 'Job':(-2100,-1900),'Psalms':(-1500,-501),'Proverbs':(-700,-700),'Ecclesiastes':(-971,-931),'Song of Solomon':(-971,-931),
 'Isaiah':(-739,-686),'Jeremiah':(-627,-582),'Lamentations':(-586,-585),'Ezekiel':(-593,-571),'Daniel':(-605,-536),'Hosea':(-800,-701),
 'Joel':(-516,-516),'Amos':(-762,-760),'Obadiah':(-848,-841),'Jonah':(-793,-753),'Micah':(-750,-680),'Nahum':(-663,-612),
 'Habakkuk':(-606,-604),'Zephaniah':(-622,-612),'Haggai':(-520,-520),'Zechariah':(-520,-518)}
datesA = {"name": "Bible Knowledge Commentary", "note": "As shown in Logos's book timeline. Zechariah read from a partly cut-off row; Malachi not yet entered.",
          "books": {b['n']: BKC.get(b['n']) for b in BOOKS[:39]}}
datesB = {"name": None, "note": "No second dating source chosen yet. The layer stays in place, greyed, until one is.", "books": {}}
# --- the copies we hold
dss = json.load(open(f'{W}/src-dss.json'))
scrolls = sorted(dss['s']); sidx = {s: i for i, s in enumerate(scrolls)}
dssv = {}
NAMEFIX = {'Song of Songs': 'Song of Solomon'}
for key, vv in dss['v'].items():
    book, ch = key.split('|')
    for v, ss in vv.items():
        ids = parse(f"{NAMEFIX.get(book, book)} {ch}:{v}")
        for i in ids: dssv[i] = sorted(sidx[s] for s in ss if s in sidx)
perbook = collections.Counter(); seen = collections.defaultdict(set)
for i, ss in dssv.items():
    bi = next(k for k, b in enumerate(BOOKS) if b['s'] <= i < (BOOKS[k+1]['s'] if k + 1 < len(BOOKS) else 10**9))
    seen[bi].update(ss)
witness = [
 {"k":"ketef","n":"Silver scrolls (Ketef Hinnom)","copy":[-650,-587],"found":1979,"place":"Ketef Hinnom, Jerusalem","held":"Israel Museum","has":"Numbers 6:24-26, a form of the priestly blessing","lang":"he","src":WIKI+"Ketef_Hinnom_scrolls"},
 {"k":"dss","n":"Dead Sea Scrolls","copy":[-300,100],"found":1947,"place":"Qumran caves, Judaean Desert (first finds Nov 1946 to Feb 1947)","held":"Israel Museum (Shrine of the Book); part in the Jordan Museum","has":"36 of the 39 books in the per-verse data used here; 1 Chronicles, Nehemiah and Esther have no verse there","lang":"he","src":WIKI+"Dead_Sea_Scrolls"},
 {"k":"nash","n":"Nash Papyrus","copy":[-150,-100],"found":1902,"place":"Acquired in Egypt by W. L. Nash","held":"Cambridge University Library, MS Or.233","has":"The Ten Commandments and the opening of the Shema","lang":"he","src":WIKI+"Nash_Papyrus"},
 {"k":"vat","n":"Codex Vaticanus","copy":[300,350],"found":1475,"foundNote":"Possibly in the Vatican Library's 1475 catalogue, certainly in 1481","place":"Vatican Library","held":"Vatican Library, Vat. gr. 1209","has":"The Greek Bible; the base of Swete's text used for the Greek Old Testament here","lang":"gr","src":WIKI+"Codex_Vaticanus"},
 {"k":"sin","n":"Codex Sinaiticus","copy":[325,360],"found":1844,"place":"Saint Catherine's Monastery, Sinai (Tischendorf)","held":"British Library, with leaves in Leipzig, Sinai and St Petersburg","has":"The Greek Bible, with the oldest complete New Testament","lang":"gr","src":WIKI+"Codex_Sinaiticus"},
 {"k":"aleppo","n":"Aleppo Codex","copy":[920,920],"found":1958,"foundNote":"Lost after the 1947 Aleppo riots; resurfaced in Israel in 1958","place":"Written in Tiberias; kept in Jerusalem, Cairo, then Aleppo","held":"Israel Museum (Shrine of the Book)","has":"About 60% survives; most of the Torah is lost","lang":"he","src":WIKI+"Aleppo_Codex"},
 {"k":"len","n":"Leningrad Codex","copy":[1008,1009],"found":1838,"foundNote":"Taken to Odessa by Abraham Firkovich in 1838","place":"Written in Cairo","held":"National Library of Russia, Firkovich B 19 A","has":"The whole Hebrew Bible; the base of BHS and of the Hebrew text in this tool","lang":"he","src":WIKI+"Leningrad_Codex"}]
transl = [
 {"n":"Greek Old Testament (Torah)","y":[-299,-200],"note":"Probably the early or middle 3rd century BC","src":WIKI+"Septuagint"},
 {"n":"Greek Old Testament (rest)","y":[-199,-132],"note":"Presumably the 2nd century BC; complete by 132 BC","src":WIKI+"Septuagint"},
 {"n":"Peshitta Old Testament","y":[100,200],"note":"By or before the 2nd century AD","src":WIKI+"Peshitta"},
 {"n":"Old Latin","y":[200,350],"note":"New Testament translations before Jerome, before about AD 350","src":WIKI+"Vetus_Latina"},
 {"n":"Jerome's Vulgate","y":[382,405],"note":"Commissioned 382; worked on to 405","src":WIKI+"Vulgate"},
 {"n":"Wycliffe Bible","y":[1382,1395],"note":"About 1382 to 1395","src":WIKI+"Wycliffe%27s_Bible"},
 {"n":"Gutenberg Bible","y":[1454,1455],"note":"First copies 1454 or 1455, Mainz","src":WIKI+"Gutenberg_Bible"},
 {"n":"Erasmus Greek New Testament","y":[1516,1516],"note":"Printed 1515 to 1 March 1516","src":WIKI+"Novum_Instrumentum_omne"},
 {"n":"Luther New Testament","y":[1522,1522],"note":"The September Testament, 1522; whole Bible 1534","src":WIKI+"Luther_Bible"},
 {"n":"Tyndale New Testament","y":[1526,1526],"note":"First complete edition 1526","src":WIKI+"Tyndale_Bible"},
 {"n":"Trent names the Vulgate authentic","y":[1546,1546],"note":"Fourth Session, 8 April 1546","src":WIKI+"Vulgate"},
 {"n":"King James Version","y":[1611,1611],"note":"Commissioned 1604, published 1611","src":WIKI+"King_James_Version"},
 {"n":"Westcott and Hort","y":[1881,1881],"note":"The New Testament in the Original Greek, 1881","src":WIKI+"The_New_Testament_in_the_Original_Greek"},
 {"n":"Revised Standard Version","y":[1946,1952],"note":"New Testament 1946, whole Bible 1952","src":WIKI+"Revised_Standard_Version"},
 {"n":"Biblia Hebraica Stuttgartensia","y":[1968,1977],"note":"Fascicles 1968 to 1976, from the Leningrad Codex","src":WIKI+"Biblia_Hebraica_Stuttgartensia"},
 {"n":"NET Bible","y":[2006,2006],"note":"Betas 2001 and 2003; first edition 2006","src":WIKI+"New_English_Translation"}]
clock = {"datesA": datesA, "datesB": datesB, "witness": witness, "translations": transl,
         "dss": {"about": dss['_about'], "scrolls": [[s, dss['s'][s][0], dss['s'][s][1]] for s in scrolls],
                 "verses": {str(k): v for k, v in sorted(dssv.items())},
                 "perBook": {BOOKS[b]['n']: len(s) for b, s in seen.items()}}}
json.dump(clock, open(f'{W}/out/word-trail/clock.json', 'w'), ensure_ascii=False, separators=(',', ':'))
print('dss verses', len(dssv), 'books with scrolls', len(seen))

# --- people: Theographic (CC BY-SA 4.0), Old Testament chronology from Floyd Nolen Jones
theo = json.load(open(f'{W}/theo/json/people.json'))
tverse = {r['id']: r['fields'].get('osisRef') for r in json.load(open(f'{W}/theo/json/verses.json'))}
OSIS = {'Gen':'Genesis','Exod':'Exodus','Lev':'Leviticus','Num':'Numbers','Deut':'Deuteronomy','Josh':'Joshua','Judg':'Judges','Ruth':'Ruth','1Sam':'1 Samuel','2Sam':'2 Samuel','1Kgs':'1 Kings','2Kgs':'2 Kings','1Chr':'1 Chronicles','2Chr':'2 Chronicles','Ezra':'Ezra','Neh':'Nehemiah','Esth':'Esther','Job':'Job','Ps':'Psalms','Prov':'Proverbs','Eccl':'Ecclesiastes','Song':'Song of Solomon','Isa':'Isaiah','Jer':'Jeremiah','Lam':'Lamentations','Ezek':'Ezekiel','Dan':'Daniel','Hos':'Hosea','Joel':'Joel','Amos':'Amos','Obad':'Obadiah','Jonah':'Jonah','Mic':'Micah','Nah':'Nahum','Hab':'Habakkuk','Zeph':'Zephaniah','Hag':'Haggai','Zech':'Zechariah','Mal':'Malachi','Matt':'Matthew','Mark':'Mark','Luke':'Luke','John':'John','Acts':'Acts','Rom':'Romans','1Cor':'1 Corinthians','2Cor':'2 Corinthians','Gal':'Galatians','Eph':'Ephesians','Phil':'Philippians','Col':'Colossians','1Thess':'1 Thessalonians','2Thess':'2 Thessalonians','1Tim':'1 Timothy','2Tim':'2 Timothy','Titus':'Titus','Phlm':'Philemon','Heb':'Hebrews','Jas':'James','1Pet':'1 Peter','2Pet':'2 Peter','1John':'1 John','2John':'2 John','3John':'3 John','Jude':'Jude','Rev':'Revelation'}
def osis2id(o):
    try: b, c, v = o.split('.'); r = parse(f"{OSIS[b]} {c}:{v}"); return r[0] if r else None
    except Exception: return None
people = []
for r in theo:
    f = r['fields']
    if not (f.get('birthYear') and f.get('deathYear')): continue
    vs = sorted({x for x in (osis2id(tverse.get(v, '')) for v in f.get('verses', [])) if x is not None})
    people.append({"n": f.get('displayTitle') or f['name'], "a": int(f['birthYear']), "b": int(f['deathYear']), "v": vs, "vc": f.get('verseCount', 0)})
people.sort(key=lambda p: p['a'])
# --- events: James's own Old Testament Timeline (jayms.com, the turning points marked major), its dates
EV = [("Noah and the Great Flood",-2350,-2350,"Genesis 6:9-9:17"),("The Call of Abram",-2091,-2091,"Genesis 12:1-9"),
 ("The Abrahamic Covenant",-2081,-2081,"Genesis 15:1-21"),("Destruction of Sodom and Gomorrah",-2067,-2067,"Genesis 18:1-19:38"),
 ("Birth of Isaac",-2066,-2066,"Genesis 21:1-7"),("The Binding of Isaac",-2051,-2051,"Genesis 22:1-19"),
 ("Jacob steals the blessing; Bethel",-1930,-1930,"Genesis 27:1-28:22"),("Jacob wrestles God",-1909,-1909,"Genesis 32:22-32"),
 ("Joseph sold into Egypt",-1898,-1898,"Genesis 37:1-36"),("The burning bush",-1446,-1446,"Exodus 3:1-4:17"),
 ("The ten plagues",-1446,-1446,"Exodus 7:14-12:30"),("The Passover",-1446,-1446,"Exodus 12:1-28"),
 ("The Exodus; the Red Sea",-1446,-1446,"Exodus 12:31-15:21"),("Sinai; the Ten Commandments",-1446,-1446,"Exodus 19:1-20:21"),
 ("The golden calf",-1445,-1445,"Exodus 32:1-34:35"),("The twelve spies",-1445,-1445,"Numbers 13:1-14:45"),
 ("Moses' last addresses",-1406,-1406,"Deuteronomy 1:1-34:12"),("Joshua commissioned; Rahab",-1406,-1406,"Joshua 1:1-2:24"),
 ("Crossing the Jordan",-1406,-1406,"Joshua 3:1-5:12"),("The fall of Jericho",-1406,-1406,"Joshua 6:1-27"),
 ("Samson",-1069,-1049,"Judges 13:1-16:31"),("Saul anointed",-1050,-1050,"1 Samuel 9:1-15:35"),
 ("David anointed; Goliath",-1025,-1010,"1 Samuel 16:1-31:13"),("David king over Judah, then Israel",-1010,-1003,"2 Samuel 1:1-5:25"),
 ("The Davidic covenant",-1000,-1000,"2 Samuel 7:1-29"),("David and Bathsheba",-990,-990,"2 Samuel 11:1-12:25"),
 ("Solomon's reign and temple",-970,-931,"1 Kings 1:1-11:43"),("The kingdom splits",-931,-931,"1 Kings 12:1-33"),
 ("Fall of the northern kingdom",-722,-722,"2 Kings 17:1-41"),("Sennacherib invades Judah",-701,-701,"2 Kings 18:13-19:37"),
 ("Deportations; Jerusalem falls",-605,-586,"2 Kings 24:1-25:30"),("Daniel in Babylon",-605,-535,"Daniel 1:1-6:28"),
 ("Cyrus decrees the return",-538,-538,"Ezra 1:1-4"),("Zerubbabel's return; the temple rebuilt",-538,-515,"Ezra 1:1-6:22"),
 ("Ezra returns",-458,-458,"Ezra 7:1-10:44"),("Nehemiah rebuilds the walls",-445,-425,"Nehemiah 1:1-13:31"),("Malachi",-433,-433,"Malachi 1:1-4:6")]
events = [{"n": n, "a": a, "b": b, "ref": ref, "v": parse(ref)} for n, a, b, ref in EV]
# --- the world: Cliopatria (Seshat Global History Databank, CC BY 4.0), polity spans
clio = json.load(open(f'{W}/src-clio.json'))
LANES = [("egypt","Egypt",["Old Kingdom of Egypt","Middle Kingdom of Egypt","New Kingdom of Egypt","Twenty-second Dynasty of Egypt","Twenty-sixth Dynasty of Egypt","Thirtieth Dynasty of Egypt","Ptolemaic Kingdom"]),
 ("meso","Mesopotamia",["Akkadian Empire","Babylonia","Assyria","Mitanni","Neo-Assyrian Empire","Neo-Babylonian Empire"]),
 ("anatolia","Anatolia and the Levant",["Hittites","Phoenicia","Kingdom of Israel","Kingdom of Judah","Phrygia","Lydia"]),
 ("persia","Persia",["Elam","Median Kingdom","Achaemenid Empire","Seleucid Empire","Parthian Empire"]),
 ("greece","Greece",["Minoan civilization","Mycenaean Greece","Greek City-States","Macedonian Empire"]),
 ("rome","Rome",["Etruscans","Roman Kingdom","Roman Republic","Roman Empire"]),
 ("china","China",["Shang Dynasty","Zhou Dynasty","Qin Dynasty","Han Dynasty"]),
 ("india","India",["Indus Valley Civilization","Nanda Empire","Maurya Empire","Shunga Empire"]),
 ("africa","Africa",["Kingdom of Kush","Carthage","Kingdom of Numidia","Kingdom of Axum"]),
 ("americas","The Americas",["Olmec Civilization","Zapotec Civilization","Mayan City-States"])]
world = []
for k, label, names in LANES:
    bands = []
    for n in names:
        e = clio.get(n)
        if not e: print('missing polity', n); continue
        bands.append({"n": n, "a": e['a'], "b": min(e['b'], 150)})
    world.append({"k": k, "n": label, "bands": bands})
FAMOUS = [("Code of Hammurabi",-1753,"Code_of_Hammurabi","c. 1753 BC"),("Tutankhamun's reign",-1332,"Tutankhamun","c. 1332 to 1323 BC"),
 ("First Olympic Games (traditional)",-776,"Ancient_Olympic_Games","traditionally 776 BC"),("Rome founded (traditional)",-753,"Founding_of_Rome","753 BC, Varro's reckoning"),
 ("Confucius",-551,"Confucius","c. 551 to c. 479 BC"),("Battle of Marathon",-490,"Battle_of_Marathon","490 BC"),
 ("Socrates tried and executed",-399,"Trial_of_Socrates","399 BC"),("Alexander dies in Babylon",-323,"Death_of_Alexander_the_Great","June 323 BC"),
 ("Julius Caesar assassinated",-44,"Assassination_of_Julius_Caesar","15 March 44 BC")]
famous = [{"n": n, "y": y, "d": d, "src": WIKI + s} for n, y, s, d in FAMOUS]
hinges = [
 {"k": "exodus", "n": "Exodus", "opts": [
   {"k": "early", "n": "Early date", "y": -1446, "note": "1 Kings 6:1 taken at face value (James's Old Testament Timeline)", "src": "https://jayms.com/bible-study-tools-2/the-old-testament-timeline/"},
   {"k": "late", "n": "Late date", "y": -1250, "note": "Under Rameses II, the critical reckoning (Accordance's critical dates)", "src": "https://accordancefiles1.com/helpfiles/OSX13/content/topics/08_time/understanding_conserv_vs_critical.htm"}]},
 {"k": "egypt", "n": "Israel in Egypt", "opts": [
   {"k": "430", "n": "430 years", "y": 430, "note": "Exodus 12:40 in the Hebrew: 430 years in Egypt"},
   {"k": "215", "n": "215 years", "y": 215, "note": "Exodus 12:40 in the Greek counts Egypt and Canaan together (Vaticanus, in Swete, reads 435), leaving about 215 in Egypt; Galatians 3:17 counts 430 from the promise to the law"}]}]
NODATE = [("The six days of creation","Genesis 1:1-2:3"),("The fall","Genesis 3:1-24"),("Cain and Abel","Genesis 4:1-26"),("The sons of God and the Nephilim","Genesis 6:1-8"),("Table of Nations","Genesis 10:1-32"),("The tower of Babel","Genesis 11:1-9")]
world_out = {"people": {"source": "Theographic Bible Metadata (CC BY-SA 4.0); Old Testament dates from Floyd Nolen Jones, The Chronology of the Old Testament", "src": "https://github.com/robertrouse/theographic-bible-metadata", "rows": people},
 "events": {"source": "JAYMS.COM, The Old Testament Timeline (turning points marked major)", "src": "https://jayms.com/bible-study-tools-2/the-old-testament-timeline/", "rows": events},
 "world": {"source": "Cliopatria, from the Seshat Global History Databank (CC BY 4.0)", "src": "https://github.com/Seshat-Global-History-Databank/cliopatria", "lanes": world},
 "famous": famous, "hinges": hinges, "nodate": [{"n": n, "ref": r} for n, r in NODATE]}
json.dump(world_out, open(f'{W}/out/word-trail/world.json', 'w'), ensure_ascii=False, separators=(',', ':'))
print('people', len(people), 'events', len(events), 'lanes', len(world))
