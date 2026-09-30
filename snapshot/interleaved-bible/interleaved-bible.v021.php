<?php
/**
 * JAYMS — Interleaved Bible.
 *
 * Built 2026-09-29 to the design agreed on the canvas, and built to two rules.
 *
 *   1. It ships no components. The card, the label, the pill, the chip and
 *      the body text are jayms.com components and live in global styles
 *      90171. What is below is layout only -- grid, flow, gaps -- and the
 *      few things only this tool has: the word rail, the fold, the panel.
 *      No colour is chosen here; every one is a token from that stylesheet.
 *   2. It fetches nothing until a reader asks. One book when a passage
 *      opens, the lexicon when a word is opened, the word index with it.
 *
 * Traps, each paid for once already:
 *   - #app is the mount id on every tool. Never style by it.
 *   - The theme sets font-family on body, p, li with !important.
 *   - DATA / LEX / WORD_INDEX start as {}, which is truthy. Readiness is a
 *     question of contents.
 *   - Back to a pushState entry reloads the document here. Hash only, and
 *     the listener is attached during parse.
 */

add_filter( 'the_content', function ( $content ) {
	if ( ! is_page( 96261 ) ) {
		return $content;
	}
	return $content . '<div class="wrap jayms-tool-alpha jayms-tool-outline" id="app"></div>';
}, 20 );

add_action( 'wp_footer', function () {
	if ( ! is_page( 96261 ) ) {
		return;
	}
	?>
<style id="jayms-outline-layout">
/* Layout, and the few things only this tool has. Every component here --
   the pill, the card, the search box, the crumb, the label -- is the
   site's, from global styles 90171. Nothing below picks a colour that is
   not a token, and nothing below restyles a shared component. */
.jayms-tool-outline .ib-sr{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0)}
.jayms-tool-outline .ib-note{color:var(--muted);font-size:13px;margin:0}

.jayms-tool-outline .ib-hero{margin:0 0 22px}
.jayms-tool-outline .ib-find{display:flex;flex-wrap:wrap;align-items:center;gap:14px;margin:0 0 4px}
.jayms-tool-outline .ib-find .a-search{flex:1 1 320px;min-width:260px}
.jayms-tool-outline .ib-findbox{position:relative;display:block;width:100%}
.jayms-tool-outline .ib-findbox .a-search{width:100%;box-sizing:border-box}
.jayms-tool-outline .ib-sugg{position:absolute;z-index:30;right:0;left:auto;top:calc(100% + 6px);
  min-width:100%;width:max-content;max-width:min(340px,78vw);
  margin:0;padding:6px;list-style:none;background:var(--paper-deep);border:1px solid var(--line);
  border-radius:10px;box-shadow:0 10px 30px rgba(0,0,0,.45);max-height:340px;overflow:auto}
.jayms-tool-outline .ib-sugg-item{display:flex;align-items:center;gap:10px;padding:9px 10px;
  border-radius:7px;cursor:pointer}
.jayms-tool-outline .ib-sugg-item.on{background:var(--paper-deeper)}
.jayms-tool-outline .ib-sugg-item.on .ib-sugg-name{color:var(--gold)}
.jayms-tool-outline .ib-sugg-item.on .ib-sugg-meta{color:var(--ink-soft)}
.jayms-tool-outline .ib-sugg-name{flex:1 1 auto;color:var(--ink);font-size:16px}
.jayms-tool-outline .ib-sugg-meta{flex:0 0 auto;color:var(--muted);font-size:12px}

/* the rule row: a label, its controls, and a line above and below */
.jayms-tool-outline .ib-rule{display:flex;flex-wrap:wrap;align-items:center;gap:14px;
  border-top:1px solid var(--line);border-bottom:1px solid var(--line);padding:12px 0;margin:14px 0 24px}
.jayms-tool-outline .ib-key{display:inline-flex;align-items:center;gap:7px;background:none;border:0;
  padding:4px 2px;cursor:pointer;font-family:inherit;font-size:13px;color:var(--ink-soft)}
.jayms-tool-outline .ib-key.on{color:var(--ink)}
.jayms-tool-outline .ib-key-all{border:1px solid var(--line);border-radius:999px;padding:4px 12px}
.jayms-tool-outline .ib-key-all.on{border-color:var(--gold);color:var(--gold)}
.jayms-tool-outline .ib-rescount{margin:-10px 0 16px;min-height:1em}
.jayms-tool-outline .ib-swatch{width:3px;height:15px;border-radius:2px;display:inline-block;flex:0 0 auto}

.jayms-tool-outline .ib-main{display:flex;gap:40px;align-items:flex-start}
.jayms-tool-outline .ib-results{flex:1 1 auto;min-width:0}
.jayms-tool-outline .ib-jump{position:sticky;top:20px;flex:0 0 264px;display:flex;flex-direction:column;
  gap:12px;border-left:1px solid var(--line);padding-left:28px}
.jayms-tool-outline .ib-jump-note{margin:0;color:var(--ink-soft)}
.jayms-tool-outline .ib-jump .a-lnk{align-self:flex-start}
.jayms-tool-outline .ib-jump-foot{margin-top:6px}

.jayms-tool-outline .ib-gsec{display:flex;align-items:baseline;gap:10px;margin:26px 0 12px}
.jayms-tool-outline .ib-gsec:first-child{margin-top:0}
.jayms-tool-outline .ib-slash{color:var(--line)}
.jayms-tool-outline .ib-tile{gap:3px;align-items:flex-start;text-align:left}
.jayms-tool-outline .ib-tile-name{font-size:22px;line-height:1.25;color:var(--ink)}
.jayms-tool-outline .ib-tile-meta{font-size:12px;color:var(--muted)}

/* ------------------------------------------------------------ the book */
.jayms-tool-outline .ib-bookhead{display:flex;flex-wrap:wrap;align-items:flex-end;
  justify-content:space-between;gap:24px}
.jayms-tool-outline .ib-bookhead-t{flex:1 1 380px;min-width:0}
.jayms-tool-outline .ib-bookhead .a-sub{margin-bottom:0}

.jayms-tool-outline .ib-book{display:flex;gap:44px;align-items:flex-start;margin-top:4px}
.jayms-tool-outline .ib-rail{position:sticky;top:20px;flex:0 0 210px;display:flex;flex-direction:column;
  gap:8px;border-right:1px solid var(--line);padding-right:24px;max-height:calc(100vh - 40px)}
.jayms-tool-outline .ib-raillist{display:flex;flex-direction:column;gap:2px;overflow:auto;min-height:0;
  scrollbar-width:thin}
.jayms-tool-outline .ib-rail a{display:flex;justify-content:space-between;gap:10px;padding:7px 10px;
  border-radius:7px;text-decoration:none;font-size:13px;color:var(--ink-soft)}
.jayms-tool-outline .ib-rail a span + span{color:var(--muted);flex:0 0 auto}
.jayms-tool-outline .ib-rail a.on{background:var(--paper-deep);color:var(--ink)}
.jayms-tool-outline .ib-rail a:hover{background:var(--paper-deep);color:var(--ink)}
.jayms-tool-outline .ib-eras{flex:1 1 auto;min-width:0}
.jayms-tool-outline .ib-era{margin:0 0 30px;scroll-margin-top:24px}
.jayms-tool-outline .ib-era-head{display:flex;flex-wrap:wrap;align-items:baseline;gap:14px}
.jayms-tool-outline .ib-era-note{margin:8px 0 0;max-width:72ch;color:var(--ink-soft)}

/* one container, hairline-separated, not a stack of cards */
.jayms-tool-outline .ib-rows{display:flex;flex-direction:column;gap:1px;background:var(--line);
  border:1px solid var(--line);border-radius:10px;overflow:hidden;margin-top:14px}
.jayms-tool-outline .ib-fold{background:var(--paper-deep)}
.jayms-tool-outline .ib-fold.turning{border-left:3px solid var(--rust)}
.jayms-tool-outline .ib-fold > summary{display:flex;align-items:baseline;gap:18px;padding:15px 18px;
  list-style:none;cursor:pointer;min-height:44px;box-sizing:border-box}
.jayms-tool-outline .ib-fold.turning > summary{padding-left:15px}
.jayms-tool-outline .ib-fold > summary::-webkit-details-marker{display:none}
.jayms-tool-outline .ib-fold > summary:hover{background:var(--paper-deeper)}
.jayms-tool-outline .ib-ref{flex:0 0 122px;font-size:12px;letter-spacing:.1em;text-transform:uppercase;
  color:var(--gold)}
.jayms-tool-outline .ib-fold.turning .ib-ref{color:var(--rust)}
.jayms-tool-outline .ib-title{flex:1 1 auto;min-width:0;font-size:20px;color:var(--ink)}
.jayms-tool-outline .ib-flag{margin-left:8px;font-size:11px;letter-spacing:.1em;text-transform:uppercase;
  color:var(--rust)}
.jayms-tool-outline .ib-caret{flex:0 0 auto;color:var(--gold);font-size:13px}
.jayms-tool-outline .ib-fold[open] > summary .ib-caret{display:inline-block;transform:rotate(90deg)}
.jayms-tool-outline .ib-fold-body{padding:2px 22px 20px 158px;display:flex;flex-direction:column;gap:14px}
.jayms-tool-outline .ib-fold.turning .ib-fold-body{padding-left:155px}
.jayms-tool-outline .ib-detail{margin:0;max-width:66ch;color:var(--ink-soft)}
.jayms-tool-outline .ib-passage{margin:0;max-width:62ch;line-height:1.72}
.jayms-tool-outline .ib-vn{font-size:.58em;vertical-align:super;color:var(--muted);padding:0 3px 0 4px}

/* ------------------------------------------------------------ the read */
.jayms-tool-outline .ib-read{display:flex;gap:0;align-items:flex-start}
.jayms-tool-outline .ib-text{flex:1 1 auto;min-width:0;padding-right:44px}
.jayms-tool-outline .ib-readhead{display:flex;flex-wrap:wrap;align-items:flex-end;
  justify-content:space-between;gap:20px;margin:0 0 24px}
.jayms-tool-outline .ib-readhead-t{flex:1 1 320px;min-width:0}
.jayms-tool-outline .ib-readhead .a-eyebrow{margin:6px 0 0}
.jayms-tool-outline .ib-verse{margin:0 0 22px}
.jayms-tool-outline .ib-verse > .a-clabel{margin:0 0 6px}
.jayms-tool-outline .ib-verse .ib-passage{font-size:1.15em;line-height:1.75}
.jayms-tool-outline .ib-origbox{max-width:62ch;background:var(--paper-deep);border:1px solid var(--line);
  border-left:3px solid var(--aramaic);border-radius:9px;padding:14px 20px;margin:12px 0 0;
  display:flex;flex-direction:column;gap:8px}
.jayms-tool-outline .ib-origlabel{color:var(--aramaic);margin:0}
.jayms-tool-outline .ib-orig{margin:0;line-height:1.95;font-size:1.22em}
.jayms-tool-outline .ib-rtl{direction:rtl;text-align:right}
.jayms-tool-outline .ib-wd{display:inline-block;white-space:nowrap}
.jayms-tool-outline .ib-w{background:none;border:0;padding:0 1px;margin:0;font:inherit;color:inherit;
  cursor:pointer;border-radius:3px}
.jayms-tool-outline .ib-w:hover{background:var(--paper-deeper)}
.jayms-tool-outline .ib-w.on{background:var(--gold);color:var(--paper-deep)}

.jayms-tool-outline .ib-panel{position:sticky;top:20px;flex:0 0 352px;background:var(--paper-deep);
  border-left:1px solid var(--line);padding:28px 30px;display:flex;flex-direction:column;gap:16px}
.jayms-tool-outline .ib-panel-word{margin:0;font-size:2.1em;line-height:1.2}
.jayms-tool-outline .ib-panel-translit{margin:0;font-style:italic;color:var(--ink-soft)}
.jayms-tool-outline .ib-panel-gloss{margin:0;color:var(--gold);font-size:1.4em;line-height:1.35}
.jayms-tool-outline .ib-parse{margin:0;display:grid;grid-template-columns:auto 1fr;gap:7px 18px;font-size:13px}
.jayms-tool-outline .ib-parse dt{margin:0;color:var(--muted)}
.jayms-tool-outline .ib-parse dd{margin:0;color:var(--ink)}
.jayms-tool-outline .ib-def{border-top:1px solid var(--line);padding-top:16px;display:flex;
  flex-direction:column;gap:8px}
.jayms-tool-outline .ib-def p{margin:0}
.jayms-tool-outline .ib-defbody{line-height:1.55}
.jayms-tool-outline .ib-occ{color:var(--ink-soft)}

@media (max-width: 900px) {
  .jayms-tool-outline .ib-main,
  .jayms-tool-outline .ib-book,
  .jayms-tool-outline .ib-read{flex-direction:column;gap:22px}
  .jayms-tool-outline .ib-jump{position:static;flex:1 1 auto;border-left:0;
    border-top:1px solid var(--line);padding:18px 0 0}
  .jayms-tool-outline .ib-rail{position:static;flex:1 1 auto;border-right:0;
    border-bottom:1px solid var(--line);padding:0 0 12px;max-height:none}
  .jayms-tool-outline .ib-raillist{max-height:230px}
  .jayms-tool-outline .ib-panel{position:static;flex:1 1 auto;border-left:0;
    border-top:1px solid var(--line);padding:22px 0 0}
  .jayms-tool-outline .ib-text{padding-right:0}
  .jayms-tool-outline .ib-fold-body,
  .jayms-tool-outline .ib-fold.turning .ib-fold-body{padding-left:18px}
  .jayms-tool-outline .ib-fold > summary{flex-wrap:wrap;gap:4px 14px}
  .jayms-tool-outline .ib-ref{flex:0 0 100%}
}
</style>

<script id="jayms-outline-counts">
/* How many events each outline holds, counted at build time so the
   picker can show it without fetching 66 files. Keyed by outline slug. */
window.JAYMS_OUTLINE_COUNTS = {"1-chronicles":[53,21],"1-corinthians":[50,16],"1-john":[17,5],"1-kings":[71,21],"1-peter":[14,5],"1-samuel":[32,22],"1-thessalonians":[14,5],"1-timothy":[20,5],"2-chronicles":[56,32],"2-corinthians":[28,13],"2-john":[4,1],"2-kings":[69,25],"2-peter":[10,3],"2-samuel":[25,20],"2-thessalonians":[10,3],"2-timothy":[16,4],"3-john":[4,1],"acts":[56,11],"amos":[14,9],"colossians":[15,4],"daniel":[16,12],"deuteronomy":[50,34],"ecclesiastes":[19,12],"ephesians":[16,6],"esther":[14,10],"exodus":[64,30],"ezekiel":[89,35],"ezra":[13,10],"galatians":[19,6],"genesis":[100,50],"habakkuk":[6,3],"haggai":[5,2],"hebrews":[29,13],"hosea":[20,14],"isaiah":[131,51],"james":[14,5],"jeremiah":[123,47],"job":[43,25],"joel":[7,4],"john":[53,10],"jonah":[8,4],"joshua":[24,15],"jude":[5,1],"judges":[22,15],"lamentations":[7,5],"leviticus":[36,22],"luke":[43,9],"malachi":[8,3],"mark":[65,10],"matthew":[70,16],"micah":[15,7],"nahum":[6,3],"nehemiah":[16,13],"numbers":[51,31],"obadiah":[3,1],"philemon":[6,1],"philippians":[16,4],"proverbs":[54,22],"psalms":[104,42],"revelation":[40,22],"romans":[49,16],"ruth":[7,4],"song-of-solomon":[11,8],"titus":[10,3],"zechariah":[24,14],"zephaniah":[6,3]};
</script>

<script id="jayms-outline-versification">
/* Hebrew and Greek numbering is not English numbering; they part company in
   120 chapters. Derived by aligning the interlinear's own glosses against an
   English text, not copied from a list. [from, to, englishChapter, delta],
   or "fold" where English keeps a Hebrew superscription inside verse 1. */
window.JAYMS_VERSIFICATION = {"1 Chronicles": {"5": [[1, 26, 5, 0], [27, 41, 6, -26]], "6": [[1, 66, 6, 15]], "12": [[1, 1, 11, 46], [2, 41, 12, -1]]}, "1 Kings": {"5": [[1, 14, 4, 20], [15, 32, 5, -14]], "22": [[1, 43, 22, 0], [44, 54, 22, -1]]}, "1 Samuel": {"21": [[1, 1, 20, 41], [2, 16, 21, -1]], "24": [[1, 1, 23, 28], [2, 23, 24, -1]]}, "2 Chronicles": {"1": [[1, 17, 1, 0], [18, 18, 2, -17]], "2": [[1, 17, 2, 1]], "13": [[1, 22, 13, 0], [23, 23, 14, -22]], "14": [[1, 14, 14, 1]]}, "2 Kings": {"12": [[1, 1, 11, 20], [2, 22, 12, -1]]}, "2 Samuel": {"19": [[1, 1, 18, 32], [2, 44, 19, -1]]}, "3 John": {"1": [[1, 15, 1, 0]]}, "Daniel": {"3": [[1, 30, 3, 0], [31, 33, 4, -30]], "4": [[1, 34, 4, 3]], "6": [[1, 1, 5, 30], [2, 29, 6, -1]]}, "Deuteronomy": {"13": [[1, 1, 12, 31], [2, 19, 13, -1]], "23": [[1, 1, 22, 29], [2, 26, 23, -1]], "28": [[1, 68, 28, 0], [69, 69, 29, -68]], "29": [[1, 28, 29, 1]]}, "Ecclesiastes": {"4": [[1, 16, 4, 0], [17, 17, 5, -16]], "5": [[1, 19, 5, 1]]}, "Exodus": {"7": [[1, 25, 7, 0], [26, 29, 8, -25]], "8": [[1, 28, 8, 4]], "21": [[1, 36, 21, 0], [37, 37, 22, -36]], "22": [[1, 30, 22, 1]]}, "Ezekiel": {"21": [[1, 5, 20, 44], [6, 37, 21, -5]]}, "Genesis": {"32": [[1, 1, 31, 54], [2, 33, 32, -1]]}, "Hosea": {"2": [[1, 2, 1, 9], [3, 25, 2, -2]], "12": [[1, 1, 11, 11], [2, 15, 12, -1]], "14": [[1, 1, 13, 15], [2, 10, 14, -1]]}, "Isaiah": {"8": [[1, 22, 8, 0], [23, 23, 9, -22]], "9": [[1, 20, 9, 1]], "64": [[1, 11, 64, 1]]}, "Jeremiah": {"8": [[1, 22, 8, 0], [23, 23, 9, -22]], "9": [[1, 25, 9, 1]]}, "Job": {"40": [[1, 24, 40, 0], [25, 32, 41, -24]], "41": [[1, 26, 41, 8]]}, "Joel": {"3": [[1, 5, 2, 27]], "4": [[1, 21, 3, 0]]}, "Jonah": {"2": [[1, 1, 1, 16], [2, 11, 2, -1]]}, "Leviticus": {"5": [[1, 19, 5, 0], [20, 26, 6, -19]], "6": [[1, 23, 6, 7]]}, "Malachi": {"3": [[1, 18, 3, 0], [19, 24, 4, -18]]}, "Micah": {"4": [[1, 13, 4, 0], [14, 14, 5, -13]], "5": [[1, 14, 5, 1]]}, "Nahum": {"2": [[1, 1, 1, 14], [2, 14, 2, -1]]}, "Nehemiah": {"3": [[1, 32, 3, 0], [33, 38, 4, -32]], "4": [[1, 17, 4, 6]], "7": [[1, 72, 7, 0]], "10": [[1, 1, 9, 37], [2, 40, 10, -1]]}, "Numbers": {"17": [[1, 15, 16, 35], [16, 28, 17, -15]], "25": [[1, 18, 25, 0], [19, 19, 26, -18]], "30": [[1, 1, 29, 39], [2, 17, 30, -1]]}, "Psalms": {"3": [[1, 1, 3, "fold"], [2, 9, 3, -1]], "4": [[1, 1, 4, "fold"], [2, 9, 4, -1]], "5": [[1, 1, 5, "fold"], [2, 13, 5, -1]], "6": [[1, 1, 6, "fold"], [2, 11, 6, -1]], "7": [[1, 1, 7, "fold"], [2, 18, 7, -1]], "8": [[1, 1, 8, "fold"], [2, 10, 8, -1]], "9": [[1, 1, 9, "fold"], [2, 21, 9, -1]], "12": [[1, 1, 12, "fold"], [2, 9, 12, -1]], "18": [[1, 1, 18, "fold"], [2, 51, 18, -1]], "19": [[1, 1, 19, "fold"], [2, 15, 19, -1]], "20": [[1, 1, 20, "fold"], [2, 10, 20, -1]], "21": [[1, 1, 21, "fold"], [2, 14, 21, -1]], "22": [[1, 1, 22, "fold"], [2, 32, 22, -1]], "30": [[1, 1, 30, "fold"], [2, 13, 30, -1]], "31": [[1, 1, 31, "fold"], [2, 25, 31, -1]], "34": [[1, 1, 34, "fold"], [2, 23, 34, -1]], "36": [[1, 1, 36, "fold"], [2, 13, 36, -1]], "38": [[1, 1, 38, "fold"], [2, 23, 38, -1]], "39": [[1, 1, 39, "fold"], [2, 14, 39, -1]], "40": [[1, 1, 40, "fold"], [2, 18, 40, -1]], "41": [[1, 1, 41, "fold"], [2, 14, 41, -1]], "42": [[1, 1, 42, "fold"], [2, 12, 42, -1]], "44": [[1, 1, 44, "fold"], [2, 27, 44, -1]], "45": [[1, 1, 45, "fold"], [2, 18, 45, -1]], "46": [[1, 1, 46, "fold"], [2, 12, 46, -1]], "47": [[1, 1, 47, "fold"], [2, 10, 47, -1]], "48": [[1, 1, 48, "fold"], [2, 15, 48, -1]], "49": [[1, 1, 49, "fold"], [2, 21, 49, -1]], "51": [[1, 2, 51, "fold"], [3, 21, 51, -2]], "52": [[1, 2, 52, "fold"], [3, 11, 52, -2]], "53": [[1, 1, 53, "fold"], [2, 7, 53, -1]], "54": [[1, 2, 54, "fold"], [3, 9, 54, -2]], "55": [[1, 1, 55, "fold"], [2, 24, 55, -1]], "56": [[1, 1, 56, "fold"], [2, 14, 56, -1]], "57": [[1, 1, 57, "fold"], [2, 12, 57, -1]], "58": [[1, 1, 58, "fold"], [2, 12, 58, -1]], "59": [[1, 1, 59, "fold"], [2, 18, 59, -1]], "60": [[1, 2, 60, "fold"], [3, 14, 60, -2]], "61": [[1, 1, 61, "fold"], [2, 9, 61, -1]], "62": [[1, 1, 62, "fold"], [2, 13, 62, -1]], "63": [[1, 1, 63, "fold"], [2, 12, 63, -1]], "64": [[1, 1, 64, "fold"], [2, 11, 64, -1]], "65": [[1, 1, 65, "fold"], [2, 14, 65, -1]], "67": [[1, 1, 67, "fold"], [2, 8, 67, -1]], "68": [[1, 1, 68, "fold"], [2, 36, 68, -1]], "69": [[1, 1, 69, "fold"], [2, 37, 69, -1]], "70": [[1, 1, 70, "fold"], [2, 6, 70, -1]], "75": [[1, 1, 75, "fold"], [2, 11, 75, -1]], "76": [[1, 1, 76, "fold"], [2, 13, 76, -1]], "77": [[1, 1, 77, "fold"], [2, 21, 77, -1]], "80": [[1, 1, 80, "fold"], [2, 20, 80, -1]], "81": [[1, 1, 81, "fold"], [2, 17, 81, -1]], "83": [[1, 1, 83, "fold"], [2, 19, 83, -1]], "84": [[1, 1, 84, "fold"], [2, 13, 84, -1]], "85": [[1, 1, 85, "fold"], [2, 14, 85, -1]], "88": [[1, 1, 88, "fold"], [2, 19, 88, -1]], "89": [[1, 1, 89, "fold"], [2, 53, 89, -1]], "92": [[1, 1, 92, "fold"], [2, 16, 92, -1]], "102": [[1, 1, 102, "fold"], [2, 29, 102, -1]], "108": [[1, 1, 108, "fold"], [2, 14, 108, -1]], "140": [[1, 1, 140, "fold"], [2, 14, 140, -1]], "142": [[1, 1, 142, "fold"], [2, 8, 142, -1]]}, "Song of Solomon": {"7": [[1, 1, 6, 12], [2, 14, 7, -1]]}, "Zechariah": {"2": [[1, 4, 1, 17], [5, 17, 2, -4]]}, "Revelation": {"12": [[1, 17, 12, 0], [18, 18, 12, -1]]}};
</script>

<script id="jayms-outline-app">
(function () {
  "use strict";

  var MOUNT = document.getElementById("app");
  if (!MOUNT) return;

  var OUTLINE_BASE = "https://raw.githubusercontent.com/sixcore-droid/jayms-tool-data/main/outline/";
  var GH = "https://raw.githubusercontent.com/sixcore-droid/interlinear-data/main";

  var BOOK_SLUGS = window.JAYMS_OUTLINE_BOOKS || {};
  var PRELOAD    = window.JAYMS_OUTLINE_PRELOAD || {};
  var VERSIFICATION = window.JAYMS_VERSIFICATION || {};
  var COUNTS = window.JAYMS_OUTLINE_COUNTS || {};

  var OUTLINES = {};
  var DATA = {};
  var LEX = {};
  var WORD_INDEX = { hebrew: {}, greek: {} };

  Object.keys(PRELOAD).forEach(function (b) { OUTLINES[b] = PRELOAD[b]; });

  // name, testament, chapters, genre. Acts is history, and there is one
  // History -- a testament is not a genre, so it does not split the list.
  var BOOKS = [
["Genesis","OT",50,"Law"],["Exodus","OT",40,"Law"],["Leviticus","OT",27,"Law"],
["Numbers","OT",36,"Law"],["Deuteronomy","OT",34,"Law"],["Joshua","OT",24,"History"],
["Judges","OT",21,"History"],["Ruth","OT",4,"History"],["1 Samuel","OT",31,"History"],
["2 Samuel","OT",24,"History"],["1 Kings","OT",22,"History"],["2 Kings","OT",25,"History"],
["1 Chronicles","OT",29,"History"],["2 Chronicles","OT",36,"History"],["Ezra","OT",10,"History"],
["Nehemiah","OT",13,"History"],["Esther","OT",10,"History"],["Job","OT",42,"Wisdom"],
["Psalms","OT",150,"Wisdom"],["Proverbs","OT",31,"Wisdom"],["Ecclesiastes","OT",12,"Wisdom"],
["Song of Solomon","OT",8,"Wisdom"],["Isaiah","OT",66,"Major Prophets"],
["Jeremiah","OT",52,"Major Prophets"],["Lamentations","OT",5,"Major Prophets"],
["Ezekiel","OT",48,"Major Prophets"],["Daniel","OT",12,"Major Prophets"],
["Hosea","OT",14,"Minor Prophets"],["Joel","OT",3,"Minor Prophets"],["Amos","OT",9,"Minor Prophets"],
["Obadiah","OT",1,"Minor Prophets"],["Jonah","OT",4,"Minor Prophets"],["Micah","OT",7,"Minor Prophets"],
["Nahum","OT",3,"Minor Prophets"],["Habakkuk","OT",3,"Minor Prophets"],["Zephaniah","OT",3,"Minor Prophets"],
["Haggai","OT",2,"Minor Prophets"],["Zechariah","OT",14,"Minor Prophets"],["Malachi","OT",4,"Minor Prophets"],
["Matthew","NT",28,"Gospels"],["Mark","NT",16,"Gospels"],["Luke","NT",24,"Gospels"],
["John","NT",21,"Gospels"],["Acts","NT",28,"History"],
["Romans","NT",16,"Letters"],["1 Corinthians","NT",16,"Letters"],
["2 Corinthians","NT",13,"Letters"],["Galatians","NT",6,"Letters"],["Ephesians","NT",6,"Letters"],
["Philippians","NT",4,"Letters"],["Colossians","NT",4,"Letters"],["1 Thessalonians","NT",5,"Letters"],
["2 Thessalonians","NT",3,"Letters"],["1 Timothy","NT",6,"Letters"],["2 Timothy","NT",4,"Letters"],
["Titus","NT",3,"Letters"],["Philemon","NT",1,"Letters"],["Hebrews","NT",13,"Letters"],
["James","NT",5,"Letters"],["1 Peter","NT",5,"Letters"],["2 Peter","NT",3,"Letters"],
["1 John","NT",5,"Letters"],["2 John","NT",1,"Letters"],["3 John","NT",1,"Letters"],
["Jude","NT",1,"Letters"],["Revelation","NT",22,"Apocalyptic"]
  ].map(function (b) { return { name: b[0], testament: b[1], chapters: b[2], genre: b[3] }; });

  // Colour says genre, never selection, and every one is a site token.
  var GENRE_TOKEN = {
    "Law": "--gold", "History": "--olive", "Wisdom": "--aramaic",
    "Major Prophets": "--blue", "Minor Prophets": "--lavender",
    "Gospels": "--rust", "Letters": "--gold", "Apocalyptic": "--lavender"
  };
  var GENRE_ORDER = ["Law","History","Wisdom","Major Prophets","Minor Prophets",
                     "Gospels","Letters","Apocalyptic"];

  function esc(s) {
    return String(s == null ? "" : s)
      .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
  }
  function attr(s) { return JSON.stringify(String(s)).replace(/"/g, "&quot;"); }

  // ------------------------------------------------------- versification

  function toEnglishVerse(book, chapter, verse) {
    var segs = VERSIFICATION[book] && VERSIFICATION[book][chapter];
    if (!segs) return { chapter: chapter, verse: verse };
    for (var i = 0; i < segs.length; i++) {
      var s = segs[i];
      if (verse >= s[0] && verse <= s[1]) {
        return s[3] === "fold" ? { chapter: s[2], verse: 1 } : { chapter: s[2], verse: verse + s[3] };
      }
    }
    return { chapter: chapter, verse: verse };
  }

  function toEnglishRef(ref) {
    var m = String(ref).match(/^(.+?)\s+(\d+):(\d+)(?:\s*[-–]\s*(?:(\d+):)?(\d+))?$/);
    if (!m) return ref;
    var book = m[1], ch = parseInt(m[2], 10);
    var a = toEnglishVerse(book, ch, parseInt(m[3], 10));
    if (!m[5]) return book + " " + a.chapter + ":" + a.verse;
    var endCh = m[4] ? parseInt(m[4], 10) : ch;
    var b = toEnglishVerse(book, endCh, parseInt(m[5], 10));
    if (b.chapter === a.chapter) return book + " " + a.chapter + ":" + a.verse + "–" + b.verse;
    return book + " " + a.chapter + ":" + a.verse + "–" + b.chapter + ":" + b.verse;
  }

  // strips the book name off a reference, for the last crumb and the title
  function refTail(ref) {
    var m = String(ref).match(/^(.+?)\s+(\d+:.*)$/);
    return m ? m[2] : ref;
  }

  // ------------------------------------------------------------- loading
  // Nothing at boot. Readiness is contents, never truthiness: the
  // placeholders all start life as {}.

  var inFlight = {};
  function once(key, run) {
    if (inFlight[key]) return inFlight[key];
    inFlight[key] = run().catch(function () { inFlight[key] = null; });
    return inFlight[key];
  }
  function outlineSlug(b) { return BOOK_SLUGS[b] || String(b).toLowerCase().replace(/\s+/g, "-"); }
  function dataSlug(b) { return String(b).toLowerCase().replace(/\s+/g, ""); }

  function ensureOutline(book) {
    if (!book || OUTLINES[book]) return Promise.resolve();
    return once("o:" + book, function () {
      return fetch(OUTLINE_BASE + outlineSlug(book) + ".json")
        .then(function (r) { return r.json(); })
        .then(function (d) { OUTLINES[book] = d; render(); });
    });
  }
  function ensureBook(book) {
    if (!book || DATA[book]) return Promise.resolve();
    return once("b:" + book, function () {
      return fetch(GH + "/" + dataSlug(book) + ".json")
        .then(function (r) { return r.json(); })
        .then(function (d) { DATA[book] = d; render(); });
    });
  }
  function ensureLexicon() {
    if (Object.keys(LEX).length) return Promise.resolve();
    return once("lex", function () {
      return fetch(GH + "/lexicon.json").then(function (r) { return r.json(); })
        .then(function (d) { LEX = d; render(); });
    });
  }
  function idxKind(key) { return (key && key[0] === "H") ? "hebrew" : "greek"; }
  function idxReady(key) { return Object.keys(WORD_INDEX[idxKind(key)]).length > 0; }
  function ensureWordIndex(key) {
    if (idxReady(key)) return Promise.resolve();
    var kind = idxKind(key);
    return once("i:" + kind, function () {
      return fetch(GH + (kind === "hebrew" ? "/word-index-hebrew.json" : "/word-index.json"))
        .then(function (r) { return r.json(); })
        .then(function (d) { WORD_INDEX[kind] = d; render(); });
    });
  }

  // --------------------------------------------------------------- state

  var VIEW = { screen: "picker" };
  var selfSetHash = false;

  var uiTestament = null;
  var uiGenre = null;
  var uiFind = "";
  var uiTurningOnly = false;
  var uiVersion = "net";
  var uiHebrew = true;
  var uiWord = null;
  var uiEra = null;

  function hashFor(v) {
    if (v.screen === "book")    return "#book=" + encodeURIComponent(v.book);
    if (v.screen === "read")    return "#read=" + encodeURIComponent(v.book) + "|" + encodeURIComponent(v.ref);
    return "#books";
  }
  function viewFromHash() {
    var h = decodeURIComponent(location.hash.replace(/^#/, "")), m;
    if ((m = h.match(/^book=(.+)$/)))            return { screen: "book", book: m[1] };
    if ((m = h.match(/^read=([^|]+)\|(.+)$/)))   return { screen: "read", book: m[1], ref: m[2] };
    return { screen: "picker" };
  }
  function go(v) {
    VIEW = v;
    uiWord = null;
    uiEra = null;
    var h = hashFor(v);
    if (location.hash !== h) { selfSetHash = true; location.hash = h; }
    render();
    MOUNT.scrollIntoView({ block: "start" });
  }
  window.ibGo = go;

  // Attached during parse: a listener that waits on a fetch lets Back fall
  // through to a full document load.
  window.addEventListener("hashchange", function () {
    if (selfSetHash) { selfSetHash = false; return; }
    // an in-page anchor is not a screen: #era7 must not be read as "no
    // screen named that, so show the picker"
    var h = decodeURIComponent(location.hash.replace(/^#/, ""));
    if (h && !/^(books$|book=|read=)/.test(h)) return;
    VIEW = viewFromHash();
    uiWord = null;
    render();
  });

  window.ibTestament = function (t) { uiTestament = t; render(); };
  window.ibGenre     = function (g) { uiGenre = g; render(); };
  window.ibTurning   = function (b) { uiTurningOnly = b; render(); };
  window.ibHebrew    = function () { uiHebrew = !uiHebrew; render(); };
  window.ibVersion   = function (v) { uiVersion = v; render(); };

  // ------------------------------------------------------------- verses

  var verseCache = {};

  function fetchVerse(ref, version) {
    var key = version + "|" + ref;
    if (verseCache[key]) return Promise.resolve(verseCache[key]);

    // NET comes straight from labs.bible.org: it answers one row per verse,
    // which is what carries the superscript numbers.
    var direct = version === "net"
      ? fetch("https://labs.bible.org/api/?passage=" + encodeURIComponent(ref) + "&type=json&formatting=plain")
          .then(function (r) { return r.json(); })
          .then(function (j) {
            var list = j || [];
            // one verse carries its own label above it; numbers are only
            // useful once the passage is a run of them
            return list.map(function (v) {
              return (list.length > 1 ? '<sup class="ib-vn">' + esc(v.verse) + "</sup>" : "") +
                esc(String(v.text).trim());
            }).join(" ");
          }).catch(function () { return ""; })
      : Promise.resolve("");

    return direct.then(function (out) {
      if (out) return out;
      if (typeof window.jaymsFetchVerse !== "function") return "";
      return window.jaymsFetchVerse(ref, version)
        .then(function (r) { return r && r.text ? esc(r.text) : ""; })
        .catch(function () { return ""; });
    }).then(function (out) {
      if (out) { verseCache[key] = out; return out; }
      // the shared fetcher cannot parse a range, so a range is walked a
      // verse at a time and stitched back together
      var m = ref.match(/^(.+?)\s+(\d+):(\d+)\s*[-–]\s*(\d+)$/);
      if (!m || typeof window.jaymsFetchVerse !== "function") return "";
      var from = parseInt(m[3], 10), to = parseInt(m[4], 10);
      if (to <= from || to - from > 60) return "";
      var jobs = [];
      for (var n = from; n <= to; n++) {
        (function (n) {
          jobs.push(window.jaymsFetchVerse(m[1] + " " + m[2] + ":" + n, version)
            .then(function (r) { return r && r.text ? '<sup class="ib-vn">' + n + "</sup>" + esc(r.text) : ""; })
            .catch(function () { return ""; }));
        })(n);
      }
      return Promise.all(jobs).then(function (parts) {
        var joined = parts.filter(Boolean).join(" ");
        if (joined) verseCache[key] = joined;
        return joined;
      });
    });
  }

  function fillVerse(id, ref) {
    var node = document.getElementById(id);
    if (!node || node.dataset.v === uiVersion) return;
    node.dataset.v = uiVersion;
    node.textContent = "Loading…";
    fetchVerse(ref, uiVersion).then(function (html) {
      if (!node.isConnected) return;
      node.innerHTML = html || "(" + uiVersion.toUpperCase() + " does not resolve this reference)";
    });
  }

  // ------------------------------------------------------- shared pieces

  var VERSION_LABEL = { net: "NET", web: "WEB", nlt: "NLT", esv: "ESV" };

  // .a-chip is the site's pill. Nothing here invents one.
  function chip(label, on, call) {
    return '<button type="button" class="a-chip' + (on ? " on" : "") +
      '" aria-pressed="' + (on ? "true" : "false") + '" onclick="' + call + '">' + esc(label) + "</button>";
  }

  function crumbs(trail) {
    var out = ['<button type="button" class="a-crumb" onclick="ibGo({screen:\'picker\'})">← All books</button>'];
    trail.forEach(function (t) {
      out.push('<span class="a-crumb-sep">›</span>');
      out.push(t.go ? '<button type="button" class="a-crumb" onclick="' + t.go + '">' + esc(t.label) + "</button>"
                    : '<span class="a-crumb is-here">' + esc(t.label) + "</span>");
    });
    return '<nav class="a-crumbs" aria-label="Breadcrumb">' + out.join("") + "</nav>";
  }

  function versionBar(note) {
    return '<div class="ib-rule"><span class="a-clabel" id="ib-tr">Translation</span>' +
      '<span role="group" aria-labelledby="ib-tr" class="a-row">' +
      ["net","web","nlt","esv"].map(function (v) {
        return chip(VERSION_LABEL[v], uiVersion === v, "ibVersion('" + v + "')");
      }).join("") + "</span>" +
      (note ? '<span class="ib-note">' + esc(note) + "</span>" : "") + "</div>";
  }

  function attribution() {
    return '<p class="a-credit">NET text fetched live from ' +
      '<a href="https://labs.bible.org/" rel="noopener" target="_blank">bible.org’s web service</a>. ' +
      "Hebrew and Greek from the open Macula and SBLGNT datasets.</p>";
  }

  // -------------------------------------------------------------- picker

  // The picker repaints in place rather than through render(), or the box
  // you are typing in loses focus on every keystroke.
  window.ibFind = function (v) {
    uiFind = v;
    var host = document.getElementById("ib-results");
    if (host) host.innerHTML = pickerResults();
    var head = document.getElementById("ib-rescount");
    if (head) head.textContent = resultNote();
  };

  // ------------------------------------------------- the reference box
  // It answers while you type, because nobody should have to spell the
  // book before they are allowed to name the chapter.
  var SUGG_MAX = 8;
  var uiSugg = -1;
  var suggOpen = false;
  var refText = "";

  // the book part is whatever comes before the first digit
  function refBookPart() {
    var m = refText.replace(/[\u2013\u2014]/g, "-").match(/^(.*?)(\d.*)?$/);
    return { name: (m && m[1] || "").trim(), rest: (m && m[2] || "").trim() };
  }

  function suggList() {
    var part = refBookPart();
    var q = norm(part.name);
    if (!q) return [];
    var hits = [];
    BOOKS.forEach(function (b, i) {
      var v = score(b, q);
      if (v > 0) hits.push({ b: b, v: v, i: i });
    });
    hits.sort(function (x, y) { return (y.v - x.v) || (x.i - y.i); });
    // once the name is typed out in full there is nothing left to suggest
    if (hits.length === 1 && norm(hits[0].b.name) === q && part.rest) return [];
    return hits.slice(0, SUGG_MAX).map(function (h) { return h.b; });
  }

  function suggHTML() {
    var list = suggOpen ? suggList() : [];
    if (!list.length) return "";
    return '<ul class="ib-sugg" id="ib-sugg" role="listbox" aria-label="Matching books">' +
      list.map(function (b, i) {
        return '<li id="ib-sugg-' + i + '" role="option" aria-selected="' + (i === uiSugg) + '"' +
          ' class="ib-sugg-item' + (i === uiSugg ? " on" : "") + '"' +
          ' onmousedown="event.preventDefault();ibPick(' + i + ')" onmouseenter="ibSuggHover(' + i + ')">' +
          '<span class="ib-swatch" aria-hidden="true" style="background:var(' + GENRE_TOKEN[b.genre] + ')"></span>' +
          '<span class="ib-sugg-name">' + esc(b.name) + "</span>" +
          '<span class="ib-sugg-meta">' + esc(b.genre) + " · " + b.chapters +
          " chapter" + (b.chapters === 1 ? "" : "s") + "</span></li>";
      }).join("") + "</ul>";
  }

  function paintSugg() {
    var wrap = document.getElementById("ib-suggwrap");
    if (wrap) wrap.innerHTML = suggHTML();
    var box = document.getElementById("ib-ref");
    if (!box) return;
    var open = !!(wrap && wrap.firstChild);
    box.setAttribute("aria-expanded", open ? "true" : "false");
    if (open && uiSugg > -1) box.setAttribute("aria-activedescendant", "ib-sugg-" + uiSugg);
    else box.removeAttribute("aria-activedescendant");
  }

  window.ibSuggHover = function (i) { uiSugg = i; paintSugg(); };

  window.ibRef = function (v) {
    refText = v;
    uiSugg = -1;
    suggOpen = true;
    var msg = document.getElementById("ib-jump-msg");
    if (msg) msg.hidden = true;
    paintSugg();
  };

  // choosing a book fills the name in and leaves you on the chapter
  window.ibPick = function (i) {
    var b = suggList()[i];
    if (!b) return;
    var box = document.getElementById("ib-ref");
    var rest = refBookPart().rest;
    refText = b.name + " " + rest;
    if (box) { box.value = refText; box.focus(); }
    suggOpen = false;
    uiSugg = -1;
    paintSugg();
    if (rest) ibJump();
  };

  window.ibRefKey = function (e) {
    var list = suggOpen ? suggList() : [];
    if (e.key === "ArrowDown") {
      e.preventDefault();
      if (!suggOpen) { suggOpen = true; paintSugg(); list = suggList(); }
      uiSugg = Math.min(uiSugg + 1, list.length - 1);
      paintSugg();
    } else if (e.key === "ArrowUp") {
      e.preventDefault();
      uiSugg = Math.max(uiSugg - 1, -1);
      paintSugg();
    } else if (e.key === "Enter") {
      e.preventDefault();
      // a highlighted book, or a name with no chapter yet, fills the box;
      // anything else is a finished reference and goes
      if (list.length && (uiSugg > -1 || !refBookPart().rest)) ibPick(uiSugg < 0 ? 0 : uiSugg);
      else ibJump();
    } else if (e.key === "Escape") {
      suggOpen = false;
      paintSugg();
    }
  };

  window.ibRefOpen = function () { if (refText.trim()) { suggOpen = true; paintSugg(); } };
  window.ibRefShut = function () { suggOpen = false; paintSugg(); };

  // ---------------------------------------------------------- the search
  // Typed by someone who should not have to spell Ecclesiastes. Four ways
  // in, in order of confidence: it starts the name, it is somewhere in the
  // name, its letters run through the name in order, or it is simply close
  // to the name. Anything that clears the bar is shown, best first.

  var ALIAS = {
    "Song of Solomon": ["songofsongs", "canticles", "song"],
    "Revelation": ["revelations", "apocalypse"],
    "Psalms": ["psalm", "psalter"],
    "Acts": ["actsoftheapostles", "book of acts"],
    "Ecclesiastes": ["qoheleth", "preacher"],
    "Lamentations": ["lament"],
    "1 Chronicles": ["1chron"], "2 Chronicles": ["2chron"],
    "Matthew": ["mt"], "Mark": ["mk"], "Luke": ["lk"], "John": ["jn"],
    "Philemon": ["philem"], "Philippians": ["philip"],
    "Deuteronomy": ["deut"], "Ephesians": ["eph"], "Colossians": ["col"],
    "1 Samuel": ["1sm"], "2 Samuel": ["2sm"]
  };

  // "First", "1st" and "I" are all the numeral the book is filed under
  function norm(s) {
    return String(s == null ? "" : s).toLowerCase()
      .replace(/\bfirst\b|\bi\b/g, "1").replace(/\bsecond\b|\bii\b/g, "2")
      .replace(/\bthird\b|\biii\b/g, "3")
      .replace(/(\d)(st|nd|rd|th)\b/g, "$1")
      .replace(/[^a-z0-9]/g, "");
  }

  function subseq(q, n) {
    var i = 0;
    for (var j = 0; j < n.length && i < q.length; j++) if (n[j] === q[i]) i++;
    return i === q.length;
  }

  function dist(a, b) {
    var prev = [], cur = [], i, j;
    for (j = 0; j <= b.length; j++) prev[j] = j;
    for (i = 1; i <= a.length; i++) {
      cur[0] = i;
      for (j = 1; j <= b.length; j++) {
        cur[j] = Math.min(prev[j] + 1, cur[j - 1] + 1,
                          prev[j - 1] + (a[i - 1] === b[j - 1] ? 0 : 1));
      }
      prev = cur.slice();
    }
    return prev[b.length];
  }

  // Spelling folded to sound: Zechariah and "zekaria" land on the same
  // string, and so do Habakkuk/habakuk and Philippians/philipians.
  function fold(s) {
    return s.replace(/ph/g, "f").replace(/ch/g, "k").replace(/ck/g, "k")
      .replace(/h/g, "").replace(/[cq]/g, "k").replace(/x/g, "ks")
      .replace(/z/g, "s").replace(/y/g, "i").replace(/(.)\1+/g, "$1");
  }

  function scoreName(q, n) {
    if (!n) return 0;
    if (n.indexOf(q) === 0) return 1000;
    // one or two letters is the start of a name, nothing looser: "e" means
    // the books beginning with E, not every book with an e in it
    if (q.length < 3) return 0;
    if (n.indexOf(q) > -1) return 800;
    if (subseq(q, n)) return 640;
    // a typo in the whole word, or in the part of it that was typed
    var whole = dist(q, n);
    var head  = dist(q, n.slice(0, q.length));
    var d = Math.min(whole, head);
    var allow = q.length <= 4 ? 1 : (q.length <= 7 ? 2 : 3);
    if (d <= allow) return 500 - d * 60;
    return 0;
  }

  // The fold is only trustworthy while it leaves enough of the query
  // standing; "qqqzzz" folds down to two letters and would match anything.
  function usableFold(q) {
    var f = fold(q);
    return (f.length >= 3 && f.length >= Math.ceil(q.length * 0.6)) ? f : null;
  }

  function score(book, q) {
    var fq = usableFold(q);
    function one(name) {
      var n = norm(name);
      var v = scoreName(q, n);
      if (fq) v = Math.max(v, scoreName(fq, fold(n)) - 20);
      return v;
    }
    var best = one(book.name);
    (ALIAS[book.name] || []).forEach(function (a) {
      var v = one(a) - 1;           // an alias never outranks the real name
      if (v > best) best = v;
    });
    return best;
  }

  function found() {
    var pool = BOOKS.filter(function (b) {
      return (!uiTestament || b.testament === uiTestament) && (!uiGenre || b.genre === uiGenre);
    });
    var q = norm(uiFind);
    if (!q) return { q: "", list: pool };
    var hits = [];
    pool.forEach(function (b, i) {
      var v = score(b, q);
      if (v > 0) hits.push({ b: b, v: v, i: i });
    });
    // equal matches come back in the order they sit in the Bible, not in
    // the order of their name lengths
    hits.sort(function (x, y) { return (y.v - x.v) || (x.i - y.i); });
    return { q: q, list: hits.map(function (h) { return h.b; }) };
  }

  function resultNote() {
    var r = found();
    if (!r.q) return "";
    if (!r.list.length) return "Nothing close to that.";
    return r.list.length + " book" + (r.list.length === 1 ? "" : "s") +
      " close to “" + uiFind.trim() + "”";
  }

  function tile(b) {
    var c = COUNTS[outlineSlug(b.name)];
    var meta = b.chapters + " chapter" + (b.chapters === 1 ? "" : "s") +
      (c ? " · " + c[0] + " event" + (c[0] === 1 ? "" : "s") : "");
    return '<button type="button" class="a-card ib-tile" style="border-left-color:var(' + GENRE_TOKEN[b.genre] + ')"' +
      ' onclick="ibGo({screen:\'book\',book:' + attr(b.name) + "})\">" +
      '<span class="ib-tile-name">' + esc(b.name) + "</span>" +
      '<span class="ib-tile-meta">' + meta + "</span></button>";
  }

  function pickerResults() {
    var r = found();
    if (!r.list.length) {
      return '<p class="ib-note">Nothing close to that. Try fewer letters — ' +
        '“ecc”, “hab”, “2 kin”.</p>';
    }
    // a search is ranked across the whole Bible, so it is one list, not
    // eight genre bins with the best answer buried in the sixth
    if (r.q) {
      return '<div class="a-cards cols-3 ib-grid">' + r.list.map(tile).join("") + "</div>";
    }
    return GENRE_ORDER.map(function (g) {
      var run = r.list.filter(function (b) { return b.genre === g; });
      if (!run.length) return "";
      return '<h2 class="a-clabel ib-gsec">' + esc(g) +
        ' <span class="ib-slash" aria-hidden="true">/</span> <span class="a-count">' +
        run.length + " book" + (run.length === 1 ? "" : "s") + "</span></h2>" +
        '<div class="a-cards cols-3 ib-grid">' + run.map(tile).join("") + "</div>";
    }).join("");
  }

  function legend() {
    var keys = '<button type="button" class="ib-key ib-key-all' + (uiGenre ? "" : " on") +
      '" aria-pressed="' + (uiGenre ? "false" : "true") + '" onclick="ibGenre(null)">Show all</button>' +
      GENRE_ORDER.map(function (g) {
        var on = uiGenre === g;
        return '<button type="button" class="ib-key' + (on ? " on" : "") + '" aria-pressed="' + (on ? "true" : "false") +
          '" onclick="ibGenre(' + (on ? "null" : attr(g)) + ')">' +
          '<span class="ib-swatch" aria-hidden="true" style="background:var(' + GENRE_TOKEN[g] + ')"></span>' +
          esc(g) + "</button>";
      }).join("");
    return '<div class="ib-rule"><span class="a-clabel">Genre</span>' + keys + "</div>";
  }

  function renderPicker() {
    var head = '<div class="a-hero ib-hero">' +
      '<p class="a-eyebrow">Bible study tools</p>' +
      '<h1 class="a-title">Interleaved Bible</h1>' +
      '<p class="a-sub">Every book laid out by its own structure, so you can see the shape of the ' +
      "argument before you read a line of it. Open any passage to read it with the Hebrew or " +
      "Greek underneath.</p></div>";

    var find = '<div class="ib-find">' +
      '<label class="ib-sr" for="ib-q">Find a book</label>' +
      '<input id="ib-q" class="a-search" type="search" autocomplete="off" spellcheck="false"' +
      ' placeholder="Find a book. Spelling does not have to be right."' +
      ' value="' + esc(uiFind) + '" oninput="ibFind(this.value)">' +
      '<span class="a-row">' +
        chip("All 66", !uiTestament, "ibTestament(null)") +
        chip("Old Testament", uiTestament === "OT", "ibTestament('OT')") +
        chip("New Testament", uiTestament === "NT", "ibTestament('NT')") +
      "</span></div>";

    var jump = '<aside class="ib-jump" aria-label="Jump straight in">' +
      '<h2 class="a-clabel">Jump straight in</h2>' +
      '<p class="ib-jump-note">Type a reference and go directly to the passage.</p>' +
      '<label class="ib-sr" for="ib-ref">Go to a reference</label>' +
      '<span class="ib-findbox">' +
      '<input id="ib-ref" class="a-search" type="text" autocomplete="off" spellcheck="false"' +
      ' role="combobox" aria-expanded="false" aria-controls="ib-sugg" aria-autocomplete="list"' +
      ' placeholder="John 1:1-18" value="' + esc(refText) + '"' +
      ' oninput="ibRef(this.value)" onkeydown="ibRefKey(event)"' +
      ' onfocus="ibRefOpen()" onblur="ibRefShut()">' +
      '<span id="ib-suggwrap">' + suggHTML() + "</span></span>" +
      '<button type="button" class="a-lnk" onclick="ibJump()">Go to passage ›</button>' +
      '<p class="ib-note ib-jump-foot">66 of 66 books · no login · free</p>' +
      '<p class="ib-note" id="ib-jump-msg" hidden></p></aside>';

    return head + find + legend() +
      '<p class="a-count ib-rescount" id="ib-rescount">' + esc(resultNote()) + "</p>" +
      '<div class="ib-main"><div class="ib-results" id="ib-results">' + pickerResults() + "</div>" +
      jump + "</div>";
  }

  // The reference box takes the same spelling licence as the search.
  window.ibJump = function () {
    var box = document.getElementById("ib-ref");
    var msg = document.getElementById("ib-jump-msg");
    var raw = ((box && box.value) || refText || "").trim();
    function fail(t) { if (msg) { msg.hidden = false; msg.textContent = t; } }
    if (!raw) return fail("Type something like John 1:1-18.");

    // split the reference off the back, whatever the book is called
    var m = raw.replace(/[–—]/g, "-").match(/^(.*?)\s*(\d+)(?::(\d+)(?:\s*-\s*(\d+))?)?\s*$/);
    var namePart = m ? m[1] : raw;
    var flat = norm(namePart);
    if (!flat) return fail("Start with a book name, like John 1:1-18.");

    var hit = null;
    BOOKS.forEach(function (b) {
      var v = score(b, flat);
      if (v > 0 && (!hit || v > hit.v)) hit = { b: b, v: v };
    });
    if (!hit) return fail("That book name did not match one of the 66.");
    if (!m || !m[2]) return fail("Add a chapter, like " + hit.b.name + " 1 or " + hit.b.name + " 1:1-18.");

    var ref = m[3]
      ? hit.b.name + " " + m[2] + ":" + m[3] + (m[4] ? "-" + m[4] : "")
      : hit.b.name + " " + m[2] + ":1";
    go({ screen: "read", book: hit.b.name, ref: ref });
  };

  // ---------------------------------------------------------------- book

  function renderBook() {
    var book = VIEW.book, doc = OUTLINES[book];
    if (!doc) { ensureOutline(book); return crumbs([{ label: book }]) + '<p class="ib-note">Loading the outline…</p>'; }

    var eras = (doc.eras || []).map(function (era, i) {
      var events = (era.events || []).filter(function (e) { return !uiTurningOnly || e.colour === "red"; });
      return { era: era, events: events, id: "era" + i };
    }).filter(function (x) { return x.events.length; });

    var rail = '<nav class="ib-rail" aria-label="Movements"><p class="a-clabel">Movements</p>' +
      '<div class="ib-raillist">' +
      eras.map(function (x, i) {
        var range = String(x.era.label || "").split("·").pop().trim().replace(/^[A-Za-z .]+/, "");
        return '<a href="#' + x.id + '" onclick="return ibEra(event,\'' + x.id + '\')"' +
          (i === 0 ? ' class="on" aria-current="true"' : "") + '><span>' +
          esc(x.era.name) + "</span><span>" + esc(range || String(i + 1)) + "</span></a>";
      }).join("") + "</div></nav>";

    var body = eras.map(function (x) {
      return '<section id="' + x.id + '" class="ib-era">' +
        '<div class="ib-era-head"><h2 class="a-h2">' + esc(x.era.name) + "</h2>" +
        '<span class="a-clabel">' + esc(x.era.label || "") + "</span></div>" +
        (x.era.dates ? '<p class="ib-era-note">' + esc(x.era.dates) + "</p>" : "") +
        '<div class="ib-rows">' + x.events.map(eventRow).join("") + "</div></section>";
    }).join("");

    return crumbs([{ label: book }]) +
      '<div class="ib-bookhead"><div class="ib-bookhead-t">' +
        '<h1 class="a-title">' + esc(book) + "</h1>" +
        (doc.desc ? '<p class="a-sub">' + esc(doc.desc) + "</p>" : "") +
      "</div>" +
      '<span class="a-row">' +
        chip("All events", !uiTurningOnly, "ibTurning(false)") +
        chip("Turning points", uiTurningOnly, "ibTurning(true)") +
      "</span></div>" +
      versionBar("Applies to every passage you open on this page.") +
      '<div class="ib-book">' + rail + '<div class="ib-eras">' + body + "</div></div>" +
      attribution();
  }

  function eventRow(ev) {
    var ref = ev.date || "", en = toEnglishRef(ref);
    var id = "p" + en.replace(/[^A-Za-z0-9]/g, "");
    var turning = ev.colour === "red";
    return '<details class="ib-fold' + (turning ? " turning" : "") + '" data-ref="' + esc(en) + '" data-node="' + id + '">' +
      "<summary>" +
        '<span class="ib-ref">' + esc(en) + "</span>" +
        '<span class="ib-title">' + esc(ev.title) + (turning ? ' <span class="ib-flag">Turning point</span>' : "") + "</span>" +
        '<span class="ib-caret" aria-hidden="true">›</span>' +
      "</summary>" +
      '<div class="ib-fold-body">' +
        (ev.detail ? '<p class="ib-detail">' + esc(ev.detail) + "</p>" : "") +
        '<p class="ib-passage" id="' + id + '">Open to read it.</p>' +
        '<div class="a-row">' +
          '<button type="button" class="a-lnk" onclick="ibGo({screen:\'read\',book:' + attr(VIEW.book) + ",ref:" + attr(ref) + '})">Read it word by word ›</button>' +
          '<span class="ib-note">' + esc(VERSION_LABEL[uiVersion]) + " · fetched live</span>" +
        "</div>" +
      "</div></details>";
  }

  // a fold only fetches when a reader actually opens it
  document.addEventListener("toggle", function (e) {
    var d = e.target;
    if (!d.classList || !d.classList.contains("ib-fold") || !d.open) return;
    fillVerse(d.dataset.node, d.dataset.ref);
  }, true);

  // ---------------------------------------------------------------- read

  function parseRef(ref) {
    var m = String(ref).match(/^(.+?)\s+(\d+):(\d+)(?:\s*[-–]\s*(?:(\d+):)?(\d+))?$/);
    if (!m) return null;
    return { c1: parseInt(m[2], 10), v1: parseInt(m[3], 10),
             c2: m[4] ? parseInt(m[4], 10) : parseInt(m[2], 10),
             v2: m[5] ? parseInt(m[5], 10) : parseInt(m[3], 10) };
  }

  function versesIn(book, p) {
    var out = [], data = DATA[book];
    if (!data || !data.chapters) return out;
    for (var c = p.c1; c <= p.c2; c++) {
      var ch = data.chapters[c];
      if (!ch) continue;
      var keys = Object.keys(ch.verses || {}).map(Number).sort(function (a, b) { return a - b; });
      var from = (c === p.c1) ? p.v1 : 1;
      var to = (c === p.c2) ? p.v2 : (keys.length ? keys[keys.length - 1] : 0);
      for (var v = from; v <= to; v++) if (ch.verses[v]) out.push({ c: c, v: v, words: ch.verses[v].words || [] });
    }
    return out;
  }

  function eventTitleFor(book, ref) {
    var doc = OUTLINES[book];
    if (!doc) return null;
    var hit = null;
    (doc.eras || []).forEach(function (era) {
      (era.events || []).forEach(function (e) { if (e.date === ref) hit = e; });
    });
    return hit;
  }

  window.ibWord = function (c, v, i) { uiWord = { c: c, v: v, i: i }; render(); };

  function renderRead() {
    var book = VIEW.book, ref = VIEW.ref, en = toEnglishRef(ref);
    var ev = eventTitleFor(book, ref);
    var head = crumbs([
      { label: book, go: "ibGo({screen:'book',book:" + attr(book) + "})" },
      { label: refTail(en) }
    ]);

    var p = parseRef(ref);
    if (!p) return head + '<p class="ib-note">That reference could not be read.</p>';
    if (!DATA[book]) { ensureBook(book); ensureOutline(book);
      return head + '<p class="ib-note">Loading ' + esc(book) + "…</p>"; }

    var rows = versesIn(book, p);
    if (!rows.length) return head + '<p class="ib-note">No original-language rows for that reference.</p>';

    var top = '<div class="ib-readhead"><div class="ib-readhead-t">' +
      '<h1 class="a-title">' + esc(ev && ev.title ? ev.title : en) + "</h1>" +
      '<p class="a-eyebrow">' + esc(en) + " · " + esc(VERSION_LABEL[uiVersion]) + "</p></div>" +
      '<span class="a-row">' +
        ["net","web","nlt","esv"].map(function (v) {
          return chip(VERSION_LABEL[v], uiVersion === v, "ibVersion('" + v + "')");
        }).join("") +
        chip(uiHebrew ? "Original on" : "Original off", uiHebrew, "ibHebrew()") +
      "</span></div>";

    return head +
      '<div class="ib-read">' +
        '<main class="ib-text">' + top +
          rows.map(function (r) { return verseBlock(book, r); }).join("") + attribution() + "</main>" +
        wordPanel(book) +
      "</div>";
  }

  function verseBlock(book, row) {
    var en = toEnglishVerse(book, row.c, row.v);
    var id = "v" + row.c + "-" + row.v;
    var rtl = row.words.length && /[֐-׿]/.test(row.words[0].text || "");

    // The data is morphemes, not words: a token whose "after" is empty is
    // glued to the next one. Grouping them keeps the line reading as Hebrew
    // instead of as a row of fragments.
    var groups = [], run = [];
    row.words.forEach(function (w, i) {
      var on = uiWord && uiWord.c === row.c && uiWord.v === row.v && uiWord.i === i;
      run.push('<button type="button" class="ib-w' + (on ? " on" : "") + '" onclick="ibWord(' +
        row.c + "," + row.v + "," + i + ')">' + esc(w.text) + "</button>");
      if (w.after !== "") { groups.push(run); run = []; }
    });
    if (run.length) groups.push(run);
    var words = groups.map(function (g) { return '<span class="ib-wd">' + g.join("") + "</span>"; }).join(" ");

    return '<section class="ib-verse">' +
      '<p class="a-clabel">' + esc(book + " " + en.chapter + ":" + en.verse) + "</p>" +
      '<p class="ib-passage" id="' + id + '">Loading…</p>' +
      (uiHebrew ? '<div class="ib-origbox">' +
        '<p class="a-clabel ib-origlabel">' + (rtl ? "Hebrew" : "Greek") +
        " · verse " + en.verse + "</p>" +
        '<p class="ib-orig' + (rtl ? " ib-rtl" : "") + '" lang="' + (rtl ? "he" : "grc") + '"' +
        (rtl ? ' dir="rtl"' : "") + ">" + words + "</p></div>" : "") +
      "</section>";
  }

  function fillReadVerses() {
    if (VIEW.screen !== "read") return;
    var p = parseRef(VIEW.ref);
    if (!p || !DATA[VIEW.book]) return;
    versesIn(VIEW.book, p).forEach(function (row) {
      var en = toEnglishVerse(VIEW.book, row.c, row.v);
      // asked for by its own English number, or the two halves of the row
      // drift apart wherever the numbering differs
      fillVerse("v" + row.c + "-" + row.v, VIEW.book + " " + en.chapter + ":" + en.verse);
    });
  }

  // The interlinear numbers Greek bare and Hebrew with an H, and it hands
  // prefixes and pronominal suffixes a number that is not theirs. So an
  // entry is trusted only when its lemma is the word's lemma; failing that
  // the word's own lemma is looked up, and a bare particle is left with no
  // entry rather than someone else's.
  var AFFIX = { preposition: 1, particle: 1, conjunction: 1, suffix: 1 };
  var byLemma = null;

  function strongKey(raw) {
    var k = String(raw == null ? "" : raw);
    return /^\d/.test(k) ? "G" + k : k;
  }
  function bareHeb(s) { return String(s == null ? "" : s).replace(/[֑-ׇ]/g, ""); }

  function lemmaIndex() {
    if (byLemma) return byLemma;
    byLemma = {};
    Object.keys(LEX).forEach(function (k) {
      var lm = LEX[k] && LEX[k].strong && LEX[k].strong.lemma;
      if (!lm) return;
      var b = bareHeb(lm);
      if (!byLemma[b]) byLemma[b] = k;
    });
    return byLemma;
  }

  function lexFor(w) {
    var key = strongKey(w.strong), e = LEX[key], s = e && e.strong;
    if (s && bareHeb(s.lemma) === bareHeb(w.lemma)) return { key: key, s: s };
    var alt = lemmaIndex()[bareHeb(w.lemma)];
    if (alt) return { key: alt, s: LEX[alt].strong };
    if (AFFIX[w.pos] && bareHeb(w.text).length <= 2) return null;
    return s ? { key: key, s: s } : null;
  }

  function wordPanel(book) {
    if (!uiWord) {
      return '<aside class="ib-panel" aria-label="Word detail"><p class="ib-note">' +
        "Tap any word in the original and it opens here — parsing, definition and " +
        "how often it is used.</p></aside>";
    }
    var data = DATA[book];
    var ch = data && data.chapters && data.chapters[uiWord.c];
    var w = ch && ch.verses[uiWord.v] && ch.verses[uiWord.v].words[uiWord.i];
    if (!w) return '<aside class="ib-panel" aria-label="Word detail"><p class="ib-note">Not found.</p></aside>';

    var rtl = /[֐-׿]/.test(w.text || "");
    var loading = !Object.keys(LEX).length;
    if (loading) ensureLexicon();

    var hit = loading ? null : lexFor(w);
    var key = hit ? hit.key : "";

    var rows = [];
    if (w.pos) rows.push("<dt>Part of speech</dt><dd>" + esc(w.pos) + "</dd>");
    [["stem","Stem"],["tense","Tense"],["voice","Voice"],["mood","Mood"],["person","Person"],
     ["number","Number"],["gender","Gender"],["case","Case"],["state","State"]].forEach(function (pair) {
      if (w[pair[0]]) rows.push("<dt>" + esc(pair[1]) + "</dt><dd>" + esc(w[pair[0]]) + "</dd>");
    });
    if (key) rows.push("<dt>Strong’s</dt><dd>" + esc(key) + "</dd>");

    var def = "", kjv = "", deriv = "";
    if (loading) def = "Loading the lexicon…";
    else if (hit) {
      def   = hit.s.def || "";
      kjv   = hit.s.kjv || "";
      deriv = hit.s.derivation || "";
    } else {
      def = AFFIX[w.pos]
        ? "A prefix Strong’s does not number. It is read with the word it is attached to."
        : "No lexicon entry for this word.";
    }

    var occ = "";
    if (key) {
      if (!idxReady(key)) { ensureWordIndex(key); occ = "Counting occurrences…"; }
      else {
        var entry = WORD_INDEX[idxKind(key)][key];
        if (entry && entry.occ) {
          var books = [];
          entry.occ.forEach(function (o) { if (books.indexOf(o.b) < 0) books.push(o.b); });
          occ = entry.occ.length + " time" + (entry.occ.length === 1 ? "" : "s") +
                " across " + books.length + " book" + (books.length === 1 ? "" : "s") + ".";
        }
      }
    }

    return '<aside class="ib-panel" aria-label="Word detail">' +
      '<p class="ib-panel-word' + (rtl ? " ib-rtl" : "") + '" lang="' + (rtl ? "he" : "grc") + '">' + esc(w.text) + "</p>" +
      (w.translit ? '<p class="ib-panel-translit">' + esc(w.translit) + "</p>" : "") +
      '<p class="ib-panel-gloss">' + esc(String(w.gloss || w.english || "").replace(/\./g, " ")) + "</p>" +
      (rows.length ? '<dl class="ib-parse">' + rows.join("") + "</dl>" : "") +
      (def ? '<div class="ib-def"><p class="a-clabel">Definition</p><p class="ib-defbody">' + esc(def) + "</p>" +
             (kjv ? '<p class="ib-note">Rendered in the King James as ' + esc(kjv) + "</p>" : "") +
             (deriv ? '<p class="ib-note">' + esc(deriv) + "</p>" : "") +
             (occ ? '<p class="ib-note ib-occ">' + esc(occ) + "</p>" : "") + "</div>" : "") +
      "</aside>";
  }

  // ------------------------------------------------------------ the rail
  // A movement is a place on this page, not another screen: clicking one
  // scrolls, and the rail marks where you are as you scroll past.

  var eraWatch = null;

  function markEra(id) {
    var links = MOUNT.querySelectorAll(".ib-rail a");
    for (var i = 0; i < links.length; i++) {
      var on = links[i].getAttribute("href") === "#" + id;
      links[i].classList.toggle("on", on);
      if (on) {
        links[i].setAttribute("aria-current", "true");
        var list = links[i].parentNode, top = links[i].offsetTop;
        if (list && (top < list.scrollTop || top > list.scrollTop + list.clientHeight - 40)) {
          list.scrollTop = top - list.clientHeight / 2;
        }
      } else {
        links[i].removeAttribute("aria-current");
      }
    }
  }

  window.ibEra = function (e, id) {
    if (e && e.preventDefault) e.preventDefault();
    // instant, not smooth: the rail runs to fifty movements and sliding
    // twelve thousand pixels is not a journey anyone wants to watch
    var node = document.getElementById(id);
    if (node) node.scrollIntoView({ block: "start" });
    markEra(id);
    return false;
  };

  // The movement you are reading is the last one whose heading has gone
  // past the top of the screen. Measured on a timer rather than queued on
  // a frame, because a frame never comes in a background tab and the rail
  // would sit frozen on the first movement.
  function wireRail() {
    if (eraWatch) { window.removeEventListener("scroll", eraWatch); eraWatch = null; }
    if (VIEW.screen !== "book") return;
    var secs = [].slice.call(MOUNT.querySelectorAll(".ib-era"));
    if (!secs.length) return;
    var last = 0;
    eraWatch = function () {
      var now = Date.now();
      if (now - last < 80) return;
      last = now;
      var best = secs[0], bestTop = -Infinity;
      for (var i = 0; i < secs.length; i++) {
        var t = secs[i].getBoundingClientRect().top;
        if (t <= 140 && t > bestTop) { bestTop = t; best = secs[i]; }
      }
      markEra(best.id);
    };
    window.addEventListener("scroll", eraWatch, { passive: true });
    eraWatch();
  }

  // ----------------------------------------------------------------- paint

  function render() {
    MOUNT.innerHTML =
      VIEW.screen === "book" ? renderBook() :
      VIEW.screen === "read" ? renderRead() :
                               renderPicker();
    fillReadVerses();
    wireRail();
  }

  // ------------------------------------------------------------------ boot

  VIEW = viewFromHash();
  render();
  if (VIEW.screen === "book" || VIEW.screen === "read") ensureOutline(VIEW.book);
})();
</script>
	<?php
} );
