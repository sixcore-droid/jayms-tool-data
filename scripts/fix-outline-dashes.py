#!/usr/bin/env python3
"""Take the em dashes out of the book outlines' own prose (outline/*.json), James's style rule.
Titles: '"Bone of My Bones" — the Woman Formed' becomes '"Bone of My Bones": the Woman Formed'.
Prose (desc, era summaries, event details): a pair of dashes around an aside becomes parentheses when the aside has commas in
it ("one family (Abraham, Isaac, Jacob, and Joseph) carrying") and commas when it doesn't; a single dash becomes a comma before a
clause that carries on the sentence ("humankind, declaring", "Enoch, who") and a colon before a list or an explanation.
Quoted Bible text (events[].verses[].text) is the translation's own wording and is left as it is.
Run: python3 scripts/fix-outline-dashes.py
"""
import json, glob, re
COMMA_NEXT = re.compile(r"(\w+ing|which|who|whom|whose|where|when|while|not|and|but|yet|so|or|as|until|though|although|because|only|even|still|then|each|both|now|before|after|without|with|leaving|turning|all|just|almost|often|sometimes|usually|also|including|especially|perhaps|probably|this|that|these|those|one|there|here|again|instead|never|always)\b")
def single(m):
    nxt = m.group(2)
    return m.group(1) + (", " if COMMA_NEXT.match(nxt) else ": ") + nxt
def prose(t):
    out = []
    for s in re.split(r"(?<=[.!?])\s+", t):
        # pairs first: " — aside — "
        def pair(m):
            inner = m.group(1)
            return (" (" + inner + ")" if "," in inner else ", " + inner + ",")
        s = re.sub(r"\s+—\s+([^—]+?)\s+—\s+", lambda m: pair(m) + " ", s)
        s = re.sub(r"(\S)\s*—\s*(\S+)", single, s)
        out.append(s)
    return " ".join(out)
def title(t): return re.sub(r"\s*—\s*", ": ", t)
# a dash straight after a question that sits mid-sentence: reworded by hand
BY_HAND = {"? — resolving": "? It resolves", "? — by keeping": "? By keeping", "? — answered by": "? It is answered by", "? — and receives": "? He receives",
           "? — draws": "? It draws", "? — and traps them": "? He traps them", "? — then warns": "? He then warns"}
n = 0
for f in sorted(glob.glob("outline/*.json")):
    raw = open(f).read(); pretty = raw.startswith("{\n")
    for a, b in BY_HAND.items(): raw = raw.replace(a, b)
    d = json.loads(raw); before = open(f).read().count("—")
    if "—" in d.get("desc", ""): d["desc"] = prose(d["desc"])
    for e in d.get("eras", []):
        if "—" in (e.get("dates") or ""): e["dates"] = prose(e["dates"])
        for ev in e.get("events", []):
            if "—" in (ev.get("title") or ""): ev["title"] = title(ev["title"])
            if "—" in (ev.get("detail") or ""): ev["detail"] = prose(ev["detail"])
    after = json.dumps(d, ensure_ascii=False).count("—")
    n += before - after
    if before != after: open(f, "w").write(json.dumps(d, ensure_ascii=False, indent=1) if pretty else json.dumps(d, ensure_ascii=False, separators=(",", ":")))
print("em dashes removed:", n)
