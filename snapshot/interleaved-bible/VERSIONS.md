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
| v002 | 144 | current: `.a-chip.on` gold-filled, shared by every tool |

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
