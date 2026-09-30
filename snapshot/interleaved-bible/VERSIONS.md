# Interleaved Bible — version log

Every deploy is a full copy, never a patch over the last one. To go back:

    ./snap.sh restore v009

which pushes that exact file into snippet 142 and records the rollback as a new version.

| ver | date | commit | change |
|-----|------|--------|--------|
| v001 | 2026-09-29 | `a34b401a` | Interleaved Bible rebuild: no tool components, lazy loading, hash routing |
| v002 | 2026-09-30 | `5b64cd51` | Simplify word card: plain apparatus, lead word is a shared component |
| v003 | 2026-09-30 | `e28c12c2` | Singular chapter count; chips sit to their content |
| v004 | 2026-09-30 | `5e3de5d8` | Interleaved Bible: build to the agreed design |
| v005 | 2026-09-30 | `de4b78b5` | IB rebuild: lexicon field names (strong.def/kjv/derivation) |
| v006 | 2026-09-30 | `2299fe36` | IB rebuild: Greek Strong's keys, affix guard, real lexicon fields |
| v007 | 2026-09-30 | `1ed94dcb` | IB rebuild: group morphemes into words on the original line |
| v008 | 2026-09-30 | `839f73f8` | IB rebuild: drop the verse number when a passage is a single verse |
| v009 | 2026-09-30 | `1d2c6e0e` | IB: rebuild all three screens to the approved mock-up; use the site's own components |
| v010 | 2026-09-30 | `4b89e61a` | IB: reading text at the mock-up's size |
| v011 | 2026-09-30 | `8255ad90` | IB picker: Acts is history, a Show all genre key, and a search that tolerates spelling |
| v012 | 2026-09-30 | `1418c852` | IB search: fold spelling to sound so Zechariah survives zekaria |
| v013 | 2026-09-30 | `e5f62352` | IB search: do not trust a fold that eats the query |
| v014 | 2026-09-30 | `ead4caae` | IB search: a typeahead list under the box, keyboard and mouse |
| v015 | 2026-09-30 | `b80d68cc` | IB search: a short query means the start of a name, and ties fall in canonical order |
| v016 | 2026-09-30 | `89c79cb3` | IB search: the keyboard row you are on is legible |
| v017 | 2026-09-30 | `9336362c` | IB: the typeahead belongs on the reference box; the find box filters as it did |

## Global styles 90171 (the shared stylesheet every tool draws on)

WordPress keeps revisions for it, but it folds same-hour edits by the same
author into one revision, so a revision is not a reliable per-change
backup. Each change therefore gets its own inactive Code Snippets entry
named `BACKUP css 90171 vNNN`, holding the CSS base64-encoded in a PHP
string. The snippet is never activated and could do nothing if it were.

| ver | snippet | state |
|-----|---------|-------|
| v001 | 143 | before the shared `.a-chip.on` pill became gold-filled |
| v002 | 144 | `.a-chip.on` gold-filled, shared by every tool |
| v003 | 147 | before `--ink-bright` was added |
| v004 | 148 | before the `--edge-*` tokens were added |

Take a new one before editing 90171, in the logged-in browser:

```js
function b64(s){const b=new TextEncoder().encode(s);let o='';for(const x of b)o+=String.fromCharCode(x);return btoa(o)}
const g = await wp.apiFetch({path:'/wp/v2/global-styles/90171'});
await wp.apiFetch({path:'/code-snippets/v1/snippets', method:'POST', data:{
  name:'BACKUP css 90171 vNNN', active:false, scope:'single-use',
  code:"$JAYMS_CSS_BACKUP_VNNN = '" + b64(g.styles.css) + "';\n"}});
```

Put one back:

```js
function unb64(s){const n=atob(s),b=new Uint8Array(n.length);for(let i=0;i<n.length;i++)b[i]=n.charCodeAt(i);return new TextDecoder().decode(b)}
const s = await wp.apiFetch({path:'/code-snippets/v1/snippets/143'});
const g = await wp.apiFetch({path:'/wp/v2/global-styles/90171'});
await wp.apiFetch({path:'/wp/v2/global-styles/90171', method:'POST',
  data:{styles: Object.assign({}, g.styles, {css: unb64(s.code.match(/'([A-Za-z0-9+/=]+)'/)[1])})}});
```
| v018 | 2026-09-29 | `94d63e07` | IB book page: the rail scrolls instead of navigating, tracks the section you are in, and an in-page anchor no longer resets to the picker |
| v019 | 2026-09-29 | `3b9f5606` | IB book page: the rail lands where it says, because smooth scrolling does nothing on this theme |
| v020 | 2026-09-29 | `c08502d5` | IB book page: the rail tracks the movement you are reading while you scroll |
| v021 | 2026-09-29 | `6772e98b` | IB book page: the rail tracks on a timer, so it works in a background tab too |
| v022 | 2026-09-29 | `08d5d1f2` | IB: coming back from a passage puts you where you were in the outline |
| v023 | 2026-09-29 | `2294962d` | IB: the Genesis crumb keeps your place too, not just the Back button |
| v024 | 2026-09-29 | `0fd54ce1` | IB: the book grid fills the width it is given, and the reading column sits in its space |
| v025 | 2026-09-29 | `23e91356` | IB: the book grid column count follows the screen, counted not auto-filled |
| v026 | 2026-09-29 | `cf6d6068` | IB: stacked columns fill the width instead of shrinking to their content |
| v027 | 2026-09-29 | `70949062` | IB read screen: past 1400px the original stands beside the English and the panel grows with the screen |
| v028 | 2026-09-29 | `d3c279a2` | IB fold: drop the translation signature from the text, and say ESV not ESV fetched live |
| v029 | 2026-09-29 | `d97573e0` | IB: the link says Interleaving, and the flag uses the data's own word, Major event |
| v030 | 2026-09-29 | `676cac14` | IB: the link says Interleaving, and the flag uses the data's own word, Major event |
| v031 | 2026-09-29 | `21959a24` | IB book page: show all four event kinds, each with its own colour and filter |
| v032 | 2026-09-29 | `086eba9b` | IB: verse numbers in round brackets so a screen reader skips them |
| v033 | 2026-09-29 | `264f3e3f` | IB: verse numbers in square brackets, which is the pair Speechify skips |
| v034 | 2026-09-29 | `7ac2b857` | IB: changing translation keeps the passages you had open |
| v035 | 2026-09-29 | `eea1f281` | IB: event kinds keep the outline's own names, so Teaching / Parable is visible as one |
| v036 | 2026-09-29 | `abe5156a` | IB: label and filter for material only one Gospel carries |
| v037 | 2026-09-29 | `a23908aa` | IB book screen: the blurb, the era note, the detail and the passage take the full column |
| v038 | 2026-09-29 | `f3d78531` | IB read screen: a real interlinear, gloss over word, flowing the full width |
| v039 | 2026-09-29 | `dea5e359` | IB read screen: a real interlinear, gloss over word, flowing the full width |
| v040 | 2026-09-29 | `0e1d7f0b` | IB interlinear: the verse number opens its verse, and a click opens the word not its prefix |
| v041 | 2026-09-29 | `7734e9da` | IB word panel: the full classic lexicon entry, and type you can actually read |
| v042 | 2026-09-29 | `3a50fe22` | IB interlinear: the English gloss at a size you can read |
| v043 | 2026-09-29 | `836cb2f8` | IB interlinear: gloss at 15px, between the two sizes tried |
| v044 | 2026-09-29 | `3aaa8848` | IB word panel: a count of uses above the transliteration, and the transliteration bigger |
| v045 | 2026-09-29 | `21ca3508` | IB word panel: a count of uses above the transliteration, and the transliteration bigger |
| v046 | 2026-09-29 | `e6fc219c` | IB word panel: the count and the word on one line |
| v047 | 2026-09-29 | `bdc856f7` | IB: word counts from a 198KB table that covers every Strong's number, not a 12MB index that missed 700 |
| v048 | 2026-09-30 | `3a0f8d7e` | IB read screen: where the versions differ, on the verse and on the word |
| v049 | 2026-09-30 | `929918ef` | IB: the differences panel set the way that tool sets it, one column of prose |
| v050 | 2026-09-30 | `2604dad4` | IB: read every reference shape the differences data uses, including ranges that cross a chapter |
| v051 | 2026-09-30 | `51fc9051` | IB interlinear: an argued word is marked in rust and opens its entry when clicked |
| v052 | 2026-09-30 | `a4487eae` | IB Translation mode: KJV added, and the translations you pick read side by side |
| v053 | 2026-09-30 | `469a86af` | IB Translation mode: KJV added, and the translations you pick read side by side |
| v054 | 2026-09-30 | `83210025` | IB Translation mode: KJV added, and the translations you pick read side by side |
| v055 | 2026-09-30 | `4bfac606` | IB parallel reading: the verse text bright, the column heading quiet |
| v056 | 2026-09-30 | `d7ce195c` | IB interlinear: the English leads and the original sits under it |
| v057 | 2026-09-30 | `84086fc0` | IB reading: plain white text, and a hairline under each verse |
| v058 | 2026-09-30 | `7e356555` | IB reading: meet the theme's !important so the verse text is actually white |
| v059 | 2026-09-30 | `94603dd7` | IB parallel reading: the column heading steps back for real |
| v060 | 2026-09-30 | `cbfc7422` | IB read screen: Parallel keeps its name and its choices, and drops the word panel |
| v061 | 2026-09-30 | `bfb32a2e` | IB: the reading sits on a panel, like every passage on the book screen |
| v062 | 2026-09-30 | `2cb2d475` | IB parallel: the panel colour reaches the text, not just the gaps |
| v063 | 2026-09-30 | `eda725c0` | IB differences: the entry's own emphasis renders, and the argument sits on a panel with a measure |
| v064 | 2026-09-30 | `7103abae` | IB: every panel edge says the same thing, from the site's own edge tokens |
