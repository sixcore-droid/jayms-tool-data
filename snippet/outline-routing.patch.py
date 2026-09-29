# Routing patch for snippet 110: hash navigation instead of pushState,
# and short #id= links instead of a whole encoded view object.
PATCHES = [

# 1. short ids in, long encoded views still understood so old links keep working
("""function viewFromHash(){
  const h = location.hash;
  if (!h || h.length < 4 || h.slice(0, 3) !== "#v=") return null;
  try {
    const parsed = JSON.parse(decodeURIComponent(h.slice(3)));
    if (parsed && typeof parsed === "object" && parsed.screen) return parsed;
  } catch (e) {}
  return null;
}
function hashForView(view){
  return "#v=" + encodeURIComponent(JSON.stringify(view));
}""",
"""// Short ids. A view used to be a whole JSON object encoded into the URL,
// which produced 200-character links nobody could share. An id is
// bookIndex*1000 + eventIndex, from the published book order, so Leviticus
// 1:1-17 is #id=46001 rather than a paragraph. 131 events is the most any
// book has, so 1000 leaves room. The old #v= form is still read, because
// links already exist in the wild.
const OUTLINE_ORDER = window.JAYMS_OUTLINE_ORDER || [];
function outlineEvents(book){
  const t = TIMELINES[book];
  if (!t || !t.eras) return [];
  const out = [];
  t.eras.forEach(era => (era.events || []).forEach(ev => out.push(ev)));
  return out;
}
function viewFromHash(){
  const h = location.hash || "";
  const m = /^#id=(\\d+)$/.exec(h);
  if (m) {
    const n = parseInt(m[1], 10);
    if (!n) return {screen: "timeline"};
    const bi = Math.floor(n / 1000), ev = n % 1000;
    const book = OUTLINE_ORDER[bi - 1];
    if (!book) return null;
    if (!ev) return {screen: "timeline", book: book};
    return {screen: "passage", book: book, evIndex: ev - 1};
  }
  if (h.length < 4 || h.slice(0, 3) !== "#v=") return null;
  try {
    const parsed = JSON.parse(decodeURIComponent(h.slice(3)));
    if (parsed && typeof parsed === "object" && parsed.screen) return parsed;
  } catch (e) {}
  return null;
}
function hashForView(view){
  if (view && view.screen === "timeline" && !view.book) return "#id=0";
  if (view && (view.screen === "timeline" || view.screen === "passage")) {
    const bi = OUTLINE_ORDER.indexOf(view.book) + 1;
    if (bi) {
      if (view.screen === "timeline") return "#id=" + (bi * 1000);
      const evs = outlineEvents(view.book);
      let idx = typeof view.evIndex === "number" ? view.evIndex
              : evs.findIndex(e => e.date === view.ref);
      if (idx >= 0) return "#id=" + (bi * 1000 + idx + 1);
    }
  }
  // screens that carry more state than an id can hold keep the long form
  return "#v=" + encodeURIComponent(JSON.stringify(view));
}
// A passage view from a short id only knows which event it is; the ref and
// title arrive with the book's data.
function fillPassageView(view){
  if (!view || view.screen !== "passage" || typeof view.evIndex !== "number") return view;
  const ev = outlineEvents(view.book)[view.evIndex];
  if (ev) { view.ref = ev.date; view.title = ev.title; }
  return view;
}"""),

# 2. navigate by hash, never pushState: going back to a pushState entry
#    forces a full document reload on this site, going back to a hash entry
#    does not. Measured directly, with no tool code involved.
("""function go(view){
  VIEW = view;
  history.pushState(view, "", hashForView(view));
  renderTransitioned(() => {
    const app = document.getElementById("app");
    if (app) app.scrollIntoView({behavior: "smooth", block: "start"});
  });
}""",
"""let hashSelfSet = false;
function go(view){
  VIEW = view;
  const h = hashForView(view);
  if (location.hash !== h) { hashSelfSet = true; location.hash = h; }
  renderTransitioned(() => {
    const app = document.getElementById("app");
    if (app) app.scrollIntoView({behavior: "smooth", block: "start"});
  });
}"""),

# 3. hashchange replaces popstate, and a passage needs its book loaded first
("""window.addEventListener("popstate", e => {
  VIEW = (e.state && e.state.screen) ? e.state : (viewFromHash() || {screen: PAGE_MODE === "library" ? "home" : PAGE_MODE});""",
"""window.addEventListener("hashchange", () => {
  if (hashSelfSet) { hashSelfSet = false; return; }
  const next = viewFromHash() || {screen: PAGE_MODE === "library" ? "home" : PAGE_MODE};
  if (next.screen === "passage" && typeof next.evIndex === "number" && !TIMELINES[next.book]) {
    ensureOutlineLoaded(next.book).then(function(){ VIEW = fillPassageView(next); render(); });
    return;
  }
  VIEW = fillPassageView(next);"""),
]
