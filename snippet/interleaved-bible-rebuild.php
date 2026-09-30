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
/* The mock was drawn on a 1280 canvas. On a real screen the row takes
   as many books as it has room for. Counted rather than auto-filled,
   because the results sit next to a 264px rail and auto-fill measures
   the container it is in, which lands one column short at 1280. */
.jayms-tool-outline .a-cards.ib-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
@media (min-width:1200px){.jayms-tool-outline .a-cards.ib-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media (min-width:1560px){.jayms-tool-outline .a-cards.ib-grid{grid-template-columns:repeat(4,minmax(0,1fr))}}
@media (min-width:1960px){.jayms-tool-outline .a-cards.ib-grid{grid-template-columns:repeat(5,minmax(0,1fr))}}
@media (max-width:520px){.jayms-tool-outline .a-cards.ib-grid{grid-template-columns:1fr}}
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
.jayms-tool-outline .ib-era-note{margin:8px 0 0;color:var(--ink-soft)}
/* On the book screen the text takes the column it is given. The measure
   caps belong on the read screen, where you read a passage straight
   through; here they left two thirds of the row empty. */
.jayms-tool-outline .ib-bookhead .a-sub,
.jayms-tool-outline .ib-eras .ib-detail,
.jayms-tool-outline .ib-eras .ib-passage{max-width:none}

/* one container, hairline-separated, not a stack of cards */
.jayms-tool-outline .ib-rows{display:flex;flex-direction:column;gap:1px;background:var(--line);
  border:1px solid var(--line);border-radius:10px;overflow:hidden;margin-top:14px}
.jayms-tool-outline .ib-fold{background:var(--paper-deep)}
.jayms-tool-outline .ib-fold.kind{border-left:3px solid var(--k)}
.jayms-tool-outline .ib-fold.k-major{--k:var(--rust)}
.jayms-tool-outline .ib-fold.k-teach{--k:var(--blue)}
.jayms-tool-outline .ib-fold.k-sign{--k:var(--aramaic)}
.jayms-tool-outline .ib-fold > summary{display:flex;align-items:baseline;gap:18px;padding:15px 18px;
  list-style:none;cursor:pointer;min-height:44px;box-sizing:border-box}
.jayms-tool-outline .ib-fold.kind > summary{padding-left:15px}
.jayms-tool-outline .ib-fold > summary::-webkit-details-marker{display:none}
.jayms-tool-outline .ib-fold > summary:hover{background:var(--paper-deeper)}
.jayms-tool-outline .ib-ref{flex:0 0 122px;font-size:12px;letter-spacing:.1em;text-transform:uppercase;
  color:var(--gold)}
.jayms-tool-outline .ib-fold.kind .ib-ref{color:var(--k)}
.jayms-tool-outline .ib-title{flex:1 1 auto;min-width:0;font-size:20px;color:var(--ink)}
.jayms-tool-outline .ib-flag{margin-left:8px;font-size:11px;letter-spacing:.1em;text-transform:uppercase;
  color:var(--k,var(--muted));white-space:nowrap}
/* what only this Gospel carries: a quieter mark than the event kind, and
   never the kind's colour, or the two would read as one label */
.jayms-tool-outline .ib-only{color:var(--muted);border:1px solid var(--line);
  border-radius:999px;padding:2px 8px;letter-spacing:.08em}
.jayms-tool-outline .ib-caret{flex:0 0 auto;color:var(--gold);font-size:13px}
.jayms-tool-outline .ib-fold[open] > summary .ib-caret{display:inline-block;transform:rotate(90deg)}
.jayms-tool-outline .ib-fold-body{padding:2px 22px 20px 158px;display:flex;flex-direction:column;gap:14px}
.jayms-tool-outline .ib-fold.kind .ib-fold-body{padding-left:155px}
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
/* The interlinear: one column per word, its gloss above the original, the
   whole passage wrapping like a paragraph. Reading order runs left to
   right even though each Hebrew word is set right to left inside itself,
   which is how a printed interlinear does it. */
.jayms-tool-outline .ib-il{display:flex;flex-wrap:wrap;align-items:flex-end;
  gap:16px 8px;margin:0 0 26px}
/* the number opens a verse, so it sits high and keeps its distance from
   the word before it, or it reads as that word's footnote */
.jayms-tool-outline .ib-vnum{align-self:flex-start;margin-left:14px;padding:0 1px;
  font-size:13px;font-weight:600;color:var(--gold);line-height:1.25}
.jayms-tool-outline .ib-il > .ib-vnum:first-child{margin-left:0}
.jayms-tool-outline .ib-u{display:inline-flex;flex-direction:column;align-items:center;
  gap:3px;padding:4px 5px;margin:0;border:0;border-radius:6px;background:none;
  font:inherit;color:inherit;cursor:pointer;text-align:center}
.jayms-tool-outline .ib-u-en{font-size:15px;line-height:1.25;color:var(--ink-soft);white-space:nowrap}
.jayms-tool-outline .ib-u-he{font-size:24px;line-height:1.3;color:var(--ink);white-space:nowrap}
.jayms-tool-outline .ib-u:hover{background:var(--paper-deeper)}
.jayms-tool-outline .ib-u.on{background:var(--gold)}
.jayms-tool-outline .ib-u.on .ib-u-en,
.jayms-tool-outline .ib-u.on .ib-u-he{color:var(--paper-deep)}
/* the versions differ on this word: say so before it is clicked */
.jayms-tool-outline .ib-u-diff .ib-u-en{color:var(--rust)}
.jayms-tool-outline .ib-u-diff .ib-u-he{border-bottom:1px dotted var(--rust)}
.jayms-tool-outline .ib-u-diff.on .ib-u-en{color:var(--paper-deep)}
.jayms-tool-outline .ib-u-diff.on .ib-u-he{border-bottom-color:var(--paper-deep)}
.jayms-tool-outline .ib-read.is-il .ib-text{padding-right:36px}
/* a verse the versions argue over says so, and opens the argument below */
.jayms-tool-outline .ib-vnum-diff{background:none;border:0;padding:0 1px;font:inherit;
  color:var(--rust);cursor:pointer;border-bottom:1px dotted var(--rust)}
.jayms-tool-outline .ib-vnum-diff:hover,
.jayms-tool-outline .ib-vnum-diff.on{color:var(--ink);border-bottom-color:var(--ink)}

.jayms-tool-outline .ib-diff{margin:10px 0 26px;padding:26px 0 0;border-top:1px solid var(--line);
  display:flex;flex-direction:column;gap:18px}
.jayms-tool-outline .ib-diff-head{display:flex;flex-wrap:wrap;align-items:flex-start;
  justify-content:space-between;gap:16px}
.jayms-tool-outline .ib-diff-t{flex:1 1 340px;min-width:0}
.jayms-tool-outline .ib-diff-h{margin:0}
.jayms-tool-outline .ib-diff-gist{margin:8px 0 0;font-style:italic;font-size:1.15em;
  line-height:1.5;color:var(--ink-soft)}
.jayms-tool-outline .ib-diff-tools{display:flex;align-items:center;gap:12px;flex:0 0 auto}
.jayms-tool-outline .ib-dw{font-size:11px;letter-spacing:.1em;text-transform:uppercase;
  border:1px solid var(--line);border-radius:999px;padding:6px 13px;color:var(--muted);white-space:nowrap}
.jayms-tool-outline .ib-dw-claim{color:var(--rust);border-color:var(--rust)}
.jayms-tool-outline .ib-dw-emphasis{color:var(--gold);border-color:var(--gold)}

.jayms-tool-outline .ib-dq{background:var(--paper-deep);border:1px solid var(--line);
  border-left:3px solid var(--gold);border-radius:9px;padding:16px 20px}
.jayms-tool-outline .ib-dq p{margin:0}
.jayms-tool-outline .ib-dq-text{margin-top:8px;font-size:1.15em;line-height:1.6;color:var(--ink)}
.jayms-tool-outline .ib-dq-note{margin-top:8px;font-size:13px;line-height:1.5;color:var(--muted)}

.jayms-tool-outline .ib-dprose{display:flex;flex-direction:column;gap:16px}
.jayms-tool-outline .ib-dp{margin:0;font-size:1.12em;line-height:1.65;color:var(--ink-soft)}
.jayms-tool-outline .ib-dp b{color:var(--ink);font-weight:600}
.jayms-tool-outline .ib-dlemma{margin:0;padding:12px 18px;border-left:3px solid var(--aramaic);
  background:var(--paper-deep);border-radius:0 9px 9px 0;color:var(--ink-soft)}
.jayms-tool-outline .ib-dlemma b{color:var(--ink)}
.jayms-tool-outline .ib-dsrc ul{margin:8px 0 0;padding-left:20px;display:flex;
  flex-direction:column;gap:6px}
.jayms-tool-outline .ib-dsrc li{font-size:15px;line-height:1.5;color:var(--ink-soft)}

.jayms-tool-outline .ib-wdiff{border-top:1px solid var(--line);padding-top:16px;
  display:flex;flex-direction:column;gap:8px}
.jayms-tool-outline .ib-wdiff-row{display:flex;flex-direction:column;gap:3px;text-align:left;
  background:none;border:1px solid var(--line);border-radius:8px;padding:10px 12px;
  font:inherit;color:inherit;cursor:pointer}
.jayms-tool-outline .ib-wdiff-row:hover{border-color:var(--rust)}
.jayms-tool-outline .ib-wdiff-ref{font-size:12px;letter-spacing:.1em;text-transform:uppercase;color:var(--rust)}
.jayms-tool-outline .ib-wdiff-gist{font-size:14px;line-height:1.45;color:var(--ink-soft)}

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

.jayms-tool-outline .ib-panel{position:sticky;top:20px;flex:0 0 clamp(330px,24vw,460px);background:var(--paper-deep);
  border-left:1px solid var(--line);padding:28px 30px;display:flex;flex-direction:column;gap:16px;
  max-height:calc(100vh - 40px);overflow:auto}
/* the count and the word share a line: the count on the left, the word
   where a Hebrew reader expects it */
.jayms-tool-outline .ib-panel-top{display:flex;align-items:center;justify-content:space-between;
  gap:16px;min-height:52px}
.jayms-tool-outline .ib-panel-word{margin:0;font-size:2.1em;line-height:1.2}
.jayms-tool-outline .ib-panel-translit{margin:0;font-style:italic;color:var(--ink-soft);font-size:1.45em}
.jayms-tool-outline .ib-count{display:inline-flex;align-items:center;justify-content:center;
  width:52px;height:52px;border-radius:50%;border:1px solid var(--gold);
  color:var(--gold);font-size:16px;line-height:1;flex:0 0 auto}
.jayms-tool-outline .ib-panel-gloss{margin:0;color:var(--gold);font-size:1.45em;line-height:1.35}
.jayms-tool-outline .ib-parse{margin:0;display:grid;grid-template-columns:auto 1fr;gap:9px 20px;font-size:15px}
.jayms-tool-outline .ib-parse dt{margin:0;color:var(--muted)}
.jayms-tool-outline .ib-parse dd{margin:0;color:var(--ink)}
.jayms-tool-outline .ib-def{border-top:1px solid var(--line);padding-top:16px;display:flex;
  flex-direction:column;gap:9px}
.jayms-tool-outline .ib-def p{margin:0}
.jayms-tool-outline .ib-defbody{font-size:1.12em;line-height:1.55}
.jayms-tool-outline .ib-def .ib-note{font-size:14px;line-height:1.5;color:var(--ink-soft)}
.jayms-tool-outline .ib-occ{color:var(--ink-soft)}

/* the classic lexicon entry, as its own editors set it: a headword, then
   numbered senses stepped in by their depth */
.jayms-tool-outline .ib-lex{border-top:1px solid var(--line);padding-top:16px;
  display:flex;flex-direction:column;gap:8px}
.jayms-tool-outline .ib-lex-body{font-size:15px;line-height:1.6;color:var(--ink)}
.jayms-tool-outline .ib-lex-body .lx-head{font-size:17px;line-height:1.4;color:var(--gold);
  margin:0 0 10px}
.jayms-tool-outline .ib-lex-body .lx-sense{margin-top:6px}
.jayms-tool-outline .ib-lex-body .lx-num{color:var(--gold);font-weight:600;margin-right:5px}
.jayms-tool-outline .ib-lex .ib-note{font-size:13px;color:var(--muted)}

/* A wide screen is the whole point of an interlinear: past 1400 the
   original stops sitting under the English and stands beside it, so the
   eye compares across instead of down, and the row is full of text
   instead of half full with a hole in it. */
@media (min-width: 1400px) {
  .jayms-tool-outline .ib-read:not(.no-orig) .ib-verse{display:grid;
    grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:8px 24px;align-items:start}
  .jayms-tool-outline .ib-read:not(.no-orig) .ib-verse > .a-clabel{grid-column:1 / -1}
  .jayms-tool-outline .ib-read:not(.no-orig) .ib-verse .ib-passage{max-width:none}
  .jayms-tool-outline .ib-read:not(.no-orig) .ib-origbox{margin:0;max-width:none}
  .jayms-tool-outline .ib-read.no-orig .ib-passage{max-width:72ch}
}

@media (max-width: 900px) {
  /* align-items:flex-start is right while these are rows and wrong the
     moment they stack: it shrinks every child to its own content width */
  .jayms-tool-outline .ib-main,
  .jayms-tool-outline .ib-book,
  .jayms-tool-outline .ib-read{flex-direction:column;gap:22px;align-items:stretch}
  .jayms-tool-outline .ib-text{max-width:none;padding-right:0}
  .jayms-tool-outline .ib-jump{position:static;flex:1 1 auto;border-left:0;
    border-top:1px solid var(--line);padding:18px 0 0}
  .jayms-tool-outline .ib-rail{position:static;flex:1 1 auto;border-right:0;
    border-bottom:1px solid var(--line);padding:0 0 12px;max-height:none}
  .jayms-tool-outline .ib-raillist{max-height:230px}
  .jayms-tool-outline .ib-panel{position:static;flex:1 1 auto;border-left:0;
    border-top:1px solid var(--line);padding:22px 0 0}
  .jayms-tool-outline .ib-text{padding-right:0}
  .jayms-tool-outline .ib-fold-body,
  .jayms-tool-outline .ib-fold.kind .ib-fold-body{padding-left:18px}
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
  var COUNTS_WORD = {};   // strong -> [times used, books it appears in]

  // Where the translations part company. The entries live on jayms.com, in
  // the same set the Why Does My Bible Say That? page reads, so there is
  // one copy of them and the tools cannot drift apart.
  var DIFF_BASE = "/wp-json/jayms/v1/differences";
  var DIFFS = [];         // the lean rows: id, ref, lemma, weight, family, gist
  var DIFF_FULL = {};     // id -> the whole entry, fetched when one is opened
  var DIFF_BY_VERSE = null;
  var DIFF_BY_STRONG = null;

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
  // How often a word is used is a number, not an atlas: this is 198KB and
  // covers every Strong's number in the corpus, where the two word indexes
  // were 12MB between them and still missed seven hundred of them.
  // "John 1:18", "Romans 3:22; Galatians 2:16", "Ezekiel 37:5, 9, 14",
  // "Genesis 1:26b", "Psalm 82" -- all of them, into verse keys.
  var BOOK_ALIAS = { "psalm": "Psalms", "song of songs": "Song of Solomon",
                     "canticles": "Song of Solomon", "qoheleth": "Ecclesiastes" };
  var BOOK_BY_NAME = null;
  function canonBook(name) {
    if (!BOOK_BY_NAME) {
      BOOK_BY_NAME = {};
      BOOKS.forEach(function (b) { BOOK_BY_NAME[b.name.toLowerCase()] = b.name; });
    }
    var k = String(name || "").toLowerCase().replace(/\s+/g, " ").trim();
    return BOOK_BY_NAME[k] || BOOK_ALIAS[k] || null;
  }

  function refKeys(ref) {
    var out = [];
    String(ref == null ? "" : ref).split(/\s*;\s*/).forEach(function (part) {
      part = part.replace(/[\u2013\u2014]/g, "-").trim();

      // Book C:V-C:V, a range that crosses a chapter
      var m = part.match(/^((?:[1-3]\s*)?[A-Za-z][A-Za-z' ]*?)\s+(\d+):(\d+)[a-d]?\s*-\s*(\d+):(\d+)[a-d]?$/);
      if (m) {
        var bk = canonBook(m[1]);
        if (!bk) return;
        var c1 = +m[2], v1 = +m[3], c2 = +m[4], v2 = +m[5];
        for (var c = c1; c <= c2 && c - c1 < 10; c++) {
          var from = (c === c1) ? v1 : 1;
          var to   = (c === c2) ? v2 : 200;
          for (var v = from; v <= to; v++) out.push(bk + "|" + c + ":" + v);
        }
        return;
      }

      // Book C-C, whole chapters
      m = part.match(/^((?:[1-3]\s*)?[A-Za-z][A-Za-z' ]*?)\s+(\d+)\s*-\s*(\d+)$/);
      if (m) {
        var b2 = canonBook(m[1]);
        if (!b2) return;
        for (var ch = +m[2]; ch <= +m[3] && ch - +m[2] < 20; ch++) out.push(b2 + "|" + ch);
        return;
      }

      // Book C, or Book C:V with a list and ranges inside it
      m = part.match(/^((?:[1-3]\s*)?[A-Za-z][A-Za-z' ]*?)\s+(\d+)(?::([\d,\s\-a-d]+))?$/);
      if (!m) return;
      var book = canonBook(m[1]);
      if (!book) return;
      var chap = +m[2];
      if (!m[3]) { out.push(book + "|" + chap); return; }
      m[3].split(/\s*,\s*/).forEach(function (bit) {
        var r = bit.match(/^\s*(\d+)[a-d]?(?:\s*-\s*(\d+)[a-d]?)?\s*$/);
        if (!r) return;
        var a = +r[1], z = r[2] ? +r[2] : a;
        for (var n = a; n <= z && n - a < 200; n++) out.push(book + "|" + chap + ":" + n);
      });
    });
    return out;
  }

  function diffIndex() {
    if (DIFF_BY_VERSE) return;
    DIFF_BY_VERSE = {}; DIFF_BY_STRONG = {};
    DIFFS.forEach(function (d) {
      refKeys(d.ref).forEach(function (k) {
        (DIFF_BY_VERSE[k] = DIFF_BY_VERSE[k] || []).push(d.id);
      });
      (String(d.lemma || "").match(/\b[HG]\d{1,5}\b/g) || []).forEach(function (sn) {
        (DIFF_BY_STRONG[sn] = DIFF_BY_STRONG[sn] || []).push(d.id);
      });
    });
  }

  function diffsFor(book, chapter, verse) {
    if (!DIFFS.length) return [];
    diffIndex();
    var a = DIFF_BY_VERSE[book + "|" + chapter + ":" + verse] || [];
    var b = DIFF_BY_VERSE[book + "|" + chapter] || [];
    return a.concat(b.filter(function (x) { return a.indexOf(x) < 0; }));
  }
  function diffsForStrong(key) {
    if (!DIFFS.length || !key) return [];
    diffIndex();
    return DIFF_BY_STRONG[key] || [];
  }
  function diffById(id) {
    return DIFF_FULL[id] || null;
  }

  function ensureDiffs() {
    if (DIFFS.length) return Promise.resolve();
    return once("diffs", function () {
      return fetch(DIFF_BASE + "?shape=map").then(function (r) { return r.json(); })
        .then(function (d) { DIFFS = Array.isArray(d) ? d : []; DIFF_BY_VERSE = null; render(); });
    });
  }
  function ensureDiffFull() {
    if (Object.keys(DIFF_FULL).length) return Promise.resolve();
    return once("diffsFull", function () {
      return fetch(DIFF_BASE).then(function (r) { return r.json(); })
        .then(function (d) {
          (Array.isArray(d) ? d : []).forEach(function (row) { DIFF_FULL[row.id] = row; });
          render();
        });
    });
  }

  function countsReady() { return Object.keys(COUNTS_WORD).length > 0; }
  function ensureCounts() {
    if (countsReady()) return Promise.resolve();
    return once("counts", function () {
      return fetch(GH + "/word-counts.json").then(function (r) { return r.json(); })
        .then(function (d) { COUNTS_WORD = d; render(); });
    });
  }

  // --------------------------------------------------------------- state

  var VIEW = { screen: "picker" };
  var selfSetHash = false;

  var uiTestament = null;
  var uiGenre = null;
  var uiFind = "";
  var uiKind = null;      // which kind of event the page is filtered to
  var openFolds = {};     // passages a reader has opened, kept across repaints
  var uiOnly = false;     // showing only what this book alone carries
  var uiDiff = null;      // which translation-difference entry is open

  // The outline tags every event with one of four kinds. A standard event
  // is the default and carries no flag; the other three each get a token.
  // The names are the outline's own, word for word -- shortening "Teaching
  // / Parable" to "Teaching" hid every parable in the Gospels.
  var KINDS = {
    "Major Event":        "k-major",
    "Teaching / Parable": "k-teach",
    "Miracle / Sign":     "k-sign"
  };
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
  // Where you were in each book's outline. Coming back from a passage to
  // a fifty-movement page and landing at the top -- or, as the browser
  // preferred, at the bottom -- loses your place every time.
  var bookScroll = {};
  var restoreFor = null;

  function rememberScroll() {
    if (VIEW.screen === "book" && VIEW.book) {
      bookScroll[VIEW.book] = document.documentElement.scrollTop || document.body.scrollTop || 0;
    }
  }

  function go(v) {
    rememberScroll();
    // a book chosen off the picker starts at its beginning, not wherever
    // it was left the last time
    if (v.screen === "book" && VIEW.screen === "picker") delete bookScroll[v.book];
    VIEW = v;
    uiWord = null;
    uiEra = null;
    restoreFor = (v.screen === "book" && bookScroll[v.book] != null) ? v.book : null;
    var h = hashFor(v);
    if (location.hash !== h) { selfSetHash = true; location.hash = h; }
    // render() consumes restoreFor, so the decision has to be taken first
    var keepingPlace = !!restoreFor;
    render();
    if (!keepingPlace) MOUNT.scrollIntoView({ block: "start" });
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
    rememberScroll();
    VIEW = viewFromHash();
    uiWord = null;
    restoreFor = (VIEW.screen === "book" && bookScroll[VIEW.book] != null) ? VIEW.book : null;
    render();
  });

  window.ibTestament = function (t) { uiTestament = t; render(); };
  window.ibGenre     = function (g) { uiGenre = g; render(); };
  window.ibKind      = function (k) { uiKind = k; render(); };
  window.ibOnly      = function (b) { uiOnly = b; render(); };
  window.ibDiff      = function (id) {
    uiDiff = (uiDiff === id) ? null : id;
    if (uiDiff != null) ensureDiffFull();
    render();
  };
  window.ibHebrew    = function (on) { uiHebrew = !!on; render(); };
  window.ibVersion   = function (v) { uiVersion = v; render(); };

  // ------------------------------------------------------------- verses

  var verseCache = {};

  // Verse numbers go in square brackets on purpose: Speechify is set to
  // skip bracketed text, so the passage is heard as prose instead of "one
  // Now after the Sabbath two Suddenly". Parentheses are read aloud there,
  // so they would not do.
  function verseNo(n) {
    return '<sup class="ib-vn">[' + esc(n) + "]</sup>";
  }

  // the shared fetcher signs every verse "(ESV)", and the row already says
  // which translation it is
  function stripSig(t) {
    return String(t == null ? "" : t).replace(/\s*\([A-Z]{2,6}\)\s*$/, "").trim();
  }

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
              return (list.length > 1 ? verseNo(v.verse) : "") + esc(String(v.text).trim());
            }).join(" ");
          }).catch(function () { return ""; })
      : Promise.resolve("");

    return direct.then(function (out) {
      if (out) return out;
      if (typeof window.jaymsFetchVerse !== "function") return "";
      return window.jaymsFetchVerse(ref, version)
        .then(function (r) { return r && r.text ? esc(stripSig(r.text)) : ""; })
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
            .then(function (r) { return r && r.text ? verseNo(n) + esc(stripSig(r.text)) : ""; })
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
      var events = (era.events || []).filter(function (e) {
        return (!uiKind || e.kind === uiKind) && (!uiOnly || e.only);
      });
      return { era: era, events: events, id: "era" + i };
    }).filter(function (x) { return x.events.length; });

    // only offer a filter for the kinds this book actually holds
    var present = [], tally = {}, onlyCount = 0;
    (doc.eras || []).forEach(function (era) {
      (era.events || []).forEach(function (e) {
        if (e.only) onlyCount++;
        if (!KINDS[e.kind]) return;
        if (present.indexOf(e.kind) < 0) present.push(e.kind);
        tally[e.kind] = (tally[e.kind] || 0) + 1;
      });
    });

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
      '<span class="a-row">' + chip("All events", !uiKind, "ibKind(null)") +
        present.map(function (k) {
          return chip(k + " " + tally[k], uiKind === k, "ibKind(" + attr(k) + ")");
        }).join("") +
        (onlyCount ? chip("Only in " + book + " " + onlyCount, uiOnly,
                          "ibOnly(" + (uiOnly ? "false" : "true") + ")") : "") +
      "</span></div>" +
      versionBar("Applies to every passage you open on this page.") +
      '<div class="ib-book">' + rail + '<div class="ib-eras">' + body + "</div></div>" +
      attribution();
  }

  function eventRow(ev) {
    var ref = ev.date || "", en = toEnglishRef(ref);
    var id = "p" + en.replace(/[^A-Za-z0-9]/g, "");
    var cls = KINDS[ev.kind];
    var key = VIEW.book + "|" + en;
    return '<details class="ib-fold' + (cls ? " kind " + cls : "") + '"' +
      (openFolds[key] ? " open" : "") + ' data-ref="' + esc(en) + '" data-key="' + esc(key) + '" data-node="' + id + '">' +
      "<summary>" +
        '<span class="ib-ref">' + esc(en) + "</span>" +
        '<span class="ib-title">' + esc(ev.title) +
          (cls ? ' <span class="ib-flag">' + esc(ev.kind) + "</span>" : "") +
          (ev.only ? ' <span class="ib-flag ib-only">Only in ' + esc(ev.only) + "</span>" : "") + "</span>" +
        '<span class="ib-caret" aria-hidden="true">›</span>' +
      "</summary>" +
      '<div class="ib-fold-body">' +
        (ev.detail ? '<p class="ib-detail">' + esc(ev.detail) + "</p>" : "") +
        '<p class="ib-passage" id="' + id + '">Open to read it.</p>' +
        '<div class="a-row">' +
          '<button type="button" class="a-lnk" onclick="ibGo({screen:\'read\',book:' + attr(VIEW.book) + ",ref:" + attr(ref) + '})">Interleaving ›</button>' +
          '<span class="ib-note">' + esc(VERSION_LABEL[uiVersion]) + "</span>" +
        "</div>" +
      "</div></details>";
  }

  // A fold only fetches when a reader actually opens it, and what they
  // opened is remembered: changing translation repaints the page, and the
  // passages you were reading should still be in front of you afterwards.
  document.addEventListener("toggle", function (e) {
    var d = e.target;
    if (!d.classList || !d.classList.contains("ib-fold")) return;
    if (d.open) {
      openFolds[d.dataset.key] = 1;
      fillVerse(d.dataset.node, d.dataset.ref);
    } else {
      delete openFolds[d.dataset.key];
    }
  }, true);

  // a fold written out already open fires no toggle, so it is filled here
  function fillOpenFolds() {
    var open = MOUNT.querySelectorAll(".ib-fold[open]");
    for (var i = 0; i < open.length; i++) {
      fillVerse(open[i].dataset.node, open[i].dataset.ref);
    }
  }

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

  window.ibWord = function (c, v, i) {
    uiWord = { c: c, v: v, i: i };
    // if the versions differ on this word, the argument opens with it
    var data = DATA[VIEW.book];
    var ch = data && data.chapters && data.chapters[c];
    var w = ch && ch.verses[v] && ch.verses[v].words[i];
    var ids = w ? diffsForStrong(strongKey(w.strong)) : [];
    if (ids.length) { uiDiff = ids[0]; ensureDiffFull(); }
    render();
  };

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
      '<p class="a-eyebrow">' + esc(en) + (uiHebrew ? "" : " · " + esc(VERSION_LABEL[uiVersion])) + "</p></div>" +
      '<span class="a-row">' +
        chip("Interlinear", uiHebrew, "ibHebrew(true)") +
        chip("Translation", !uiHebrew, "ibHebrew(false)") +
        (uiHebrew ? "" : ["net","web","nlt","esv"].map(function (v) {
          return chip(VERSION_LABEL[v], uiVersion === v, "ibVersion('" + v + "')");
        }).join("")) +
      "</span></div>";

    ensureDiffs();
    var body = uiHebrew ? interlinear(book, rows)
                        : rows.map(function (r) { return verseBlock(book, r); }).join("");
    body += diffPanel();

    return head +
      '<div class="ib-read' + (uiHebrew ? " is-il" : " no-orig") + '">' +
        '<main class="ib-text">' + top + body + attribution() + "</main>" +
        wordPanel(book) +
      "</div>";
  }

  // The interlinear proper: every word is a column, its gloss over the
  // original, and the whole passage runs as one line of reading that wraps
  // like prose. Morphemes glued by "after" stay in one column so a word is
  // not split into fragments the eye has to reassemble.
  function interlinear(book, rows) {
    var out = [];
    rows.forEach(function (row) {
      var en = toEnglishVerse(book, row.c, row.v);
      var hits = diffsFor(book, en.chapter, en.verse);
      out.push(hits.length
        ? '<button type="button" class="ib-vnum ib-vnum-diff' + (uiDiff === hits[0] ? " on" : "") +
          '" id="v' + row.c + "-" + row.v + '" onclick="ibDiff(' + hits[0] + ')"' +
          ' title="Why the translations differ here">[' + en.verse + "]</button>"
        : '<span class="ib-vnum" id="v' + row.c + "-" + row.v + '">[' + en.verse + "]</span>");

      var run = [], first = 0;
      row.words.forEach(function (w, i) {
        if (!run.length) first = i;
        run.push(w);
        if (w.after !== "") { out.push(unit(row, run, first)); run = []; }
      });
      if (run.length) out.push(unit(row, run, first));
    });
    return '<div class="ib-il">' + out.join("") + "</div>";
  }

  function unit(row, run, i) {
    // a click should open the word, not the letter stuck to the front of
    // it: the head of the group is its longest morpheme
    var head = i, longest = -1;
    run.forEach(function (w, n) {
      var len = String(w.text || "").replace(/[\u0591-\u05C7]/g, "").length;
      if (len > longest) { longest = len; head = i + n; }
    });
    var on = uiWord && uiWord.c === row.c && uiWord.v === row.v &&
             uiWord.i >= i && uiWord.i < i + run.length;
    // a word the versions argue over is marked before you click it
    var argued = diffsForStrong(strongKey(run[head - i].strong)).length > 0;
    var word = run.map(function (w) { return esc(w.text); }).join("");
    var gloss = run.map(function (w) { return String(w.gloss || w.english || ""); })
                   .join(" ").replace(/\./g, " ").replace(/\s+/g, " ").trim();
    var rtl = /[\u0590-\u05FF]/.test(run[0].text || "");
    return '<button type="button" class="ib-u' + (on ? " on" : "") + (argued ? " ib-u-diff" : "") +
      '" onclick="ibWord(' + row.c + "," + row.v + "," + head + ')"' +
      (argued ? ' title="The versions differ on this word"' : "") + ">" +
      '<span class="ib-u-en">' + esc(gloss || "\u00b7") + "</span>" +
      '<span class="ib-u-he" lang="' + (rtl ? "he" : "grc") + '"' + (rtl ? ' dir="rtl"' : "") + ">" +
      word + "</span></button>";
  }

  // the plain reading: the translation on its own, no original underneath
  function verseBlock(book, row) {
    var en = toEnglishVerse(book, row.c, row.v);
    return '<section class="ib-verse">' +
      '<p class="a-clabel">' + esc(book + " " + en.chapter + ":" + en.verse) + "</p>" +
      '<p class="ib-passage" id="v' + row.c + "-" + row.v + '">Loading\u2026</p></section>';
  }

  function fillReadVerses() {
    if (VIEW.screen !== "read" || uiHebrew) return;   // the interlinear fetches nothing
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
    if (s && bareHeb(s.lemma) === bareHeb(w.lemma)) return { key: key, s: s, c: e.classic };
    var alt = lemmaIndex()[bareHeb(w.lemma)];
    if (alt) return { key: alt, s: LEX[alt].strong, c: LEX[alt].classic };
    if (AFFIX[w.pos] && bareHeb(w.text).length <= 2) return null;
    return s ? { key: key, s: s, c: e.classic } : null;
  }

  // The classic entry is Brown-Driver-Briggs or Abbott-Smith, and it ships
  // as markup: headword, numbered senses, each sense indented by a margin.
  // Only those tags and that one style are let through.
  function safeLex(html) {
    var box = document.createElement("div");
    box.innerHTML = String(html == null ? "" : html);
    var all = box.querySelectorAll("*");
    for (var i = all.length - 1; i >= 0; i--) {
      var el = all[i], tag = el.tagName.toLowerCase();
      if (tag !== "div" && tag !== "b") {
        while (el.firstChild) el.parentNode.insertBefore(el.firstChild, el);
        el.parentNode.removeChild(el);
        continue;
      }
      for (var j = el.attributes.length - 1; j >= 0; j--) {
        var a = el.attributes[j].name;
        if (a !== "class" && a !== "style") el.removeAttribute(a);
      }
      var st = el.getAttribute("style");
      if (st && !/^\s*margin-left:\s*\d+px;?\s*$/.test(st)) el.removeAttribute("style");
    }
    return box.innerHTML;
  }

  // a word the versions argue over says so, under its lexicon entry
  function diffsForWord(key) {
    var ids = diffsForStrong(key);
    if (!ids.length) return "";
    return '<div class="ib-wdiff"><p class="a-clabel">Versions differ here</p>' +
      ids.map(function (id) {
        var d = null;
        for (var i = 0; i < DIFFS.length; i++) if (DIFFS[i].id === id) { d = DIFFS[i]; break; }
        if (!d) return "";
        return '<button type="button" class="ib-wdiff-row" onclick="ibDiff(' + id + ')">' +
          '<span class="ib-wdiff-ref">' + esc(d.ref) + "</span>" +
          '<span class="ib-wdiff-gist">' + esc(d.gist || "") + "</span></button>";
      }).join("") + "</div>";
  }

  function lexBlock(hit) {
    var c = hit && hit.c;
    if (!c || !c.entry) return "";
    var source = [c.full || c.name, c.page && c.page !== "None" ? "page " + c.page : ""]
                   .filter(Boolean).join(", ");
    return '<div class="ib-lex"><p class="a-clabel">' + esc(c.name || "Lexicon") + "</p>" +
      '<div class="ib-lex-body">' + safeLex(c.entry) + "</div>" +
      (source ? '<p class="ib-note">' + esc(source) + "</p>" : "") + "</div>";
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

    var occ = "", occN = null;
    if (key) {
      if (!countsReady()) { ensureCounts(); }
      else {
        var tally = COUNTS_WORD[key];
        if (tally) {
          occN = tally[0];
          occ = occN + " time" + (occN === 1 ? "" : "s") +
                " across " + tally[1] + " book" + (tally[1] === 1 ? "" : "s") + ".";
        }
      }
    }

    // how many times the word is used, where the eye lands first
    var badge = occN == null ? "" :
      '<span class="ib-count" title="' + occN + " occurrence" + (occN === 1 ? "" : "s") +
      ' in the Bible" aria-label="' + occN + " occurrence" + (occN === 1 ? "" : "s") +
      ' in the Bible">' + occN + "</span>";

    return '<aside class="ib-panel" aria-label="Word detail">' +
      '<div class="ib-panel-top">' + badge +
        '<p class="ib-panel-word' + (rtl ? " ib-rtl" : "") + '" lang="' + (rtl ? "he" : "grc") + '">' +
        esc(w.text) + "</p></div>" +
      (w.translit ? '<p class="ib-panel-translit">' + esc(w.translit) + "</p>" : "") +
      '<p class="ib-panel-gloss">' + esc(String(w.gloss || w.english || "").replace(/\./g, " ")) + "</p>" +
      (rows.length ? '<dl class="ib-parse">' + rows.join("") + "</dl>" : "") +
      (def ? '<div class="ib-def"><p class="a-clabel">Definition</p><p class="ib-defbody">' + esc(def) + "</p>" +
             (kjv ? '<p class="ib-note">Rendered in the King James as ' + esc(kjv) + "</p>" : "") +
             (deriv ? '<p class="ib-note">' + esc(deriv) + "</p>" : "") +
             (occ ? '<p class="ib-note ib-occ">' + esc(occ) + "</p>" : "") + "</div>" : "") +
      lexBlock(hit) +
      diffsForWord(key) +
      "</aside>";
  }

  // --------------------------------------------- where versions disagree

  // Set the way the differences page sets it: the verse first, then the two
  // readings boxed and quoted, then the argument as prose with its lead-ins
  // in line. One column, because these are paragraphs, not columns.
  function diffQuote(label, text, note) {
    if (!text) return "";
    return '<div class="ib-dq"><p class="a-clabel">' + esc(label) + "</p>" +
      '<p class="ib-dq-text">' + esc(text) + "</p>" +
      (note ? '<p class="ib-dq-note">' + esc(note) + "</p>" : "") + "</div>";
  }

  function diffPara(lead, text) {
    if (!text) return "";
    return '<p class="ib-dp">' + (lead ? '<b>' + esc(lead) + ":</b> " : "") + esc(text) + "</p>";
  }

  function diffPanel() {
    if (uiDiff == null) return "";
    var d = diffById(uiDiff);
    if (!d) { ensureDiffFull(); return '<div class="ib-diff"><p class="ib-note">Loading\u2026</p></div>'; }

    var badge = d.weight ? '<span class="ib-dw ib-dw-' + esc(String(d.weight).toLowerCase()) + '">' +
      esc(d.weight === "claim" ? "Changes the claim" :
          d.weight === "emphasis" ? "Changes the emphasis" : "Cosmetic") + "</span>" : "";

    var srcs = (d.srcs && d.srcs.length)
      ? '<div class="ib-dsrc"><p class="a-clabel">Sources</p><ul>' +
        d.srcs.map(function (x) { return "<li>" + esc(x) + "</li>"; }).join("") + "</ul></div>"
      : "";

    return '<section class="ib-diff" aria-label="Why the translations differ">' +
      '<div class="ib-diff-head">' +
        '<div class="ib-diff-t"><h2 class="a-title ib-diff-h">' + esc(d.ref || "") + "</h2>" +
          (d.gist ? '<p class="ib-diff-gist">' + esc(d.gist) + "</p>" : "") + "</div>" +
        '<span class="ib-diff-tools">' + badge +
          '<button type="button" class="a-lnk" onclick="ibDiff(' + uiDiff + ')">Close</button>' +
        "</span>" +
      "</div>" +
      diffQuote((d.ref || "") + " \u00b7 KJV", d.kjv, d.kjvWhy) +
      diffQuote("How modern translations read it", d.others) +
      '<div class="ib-dprose">' +
        diffPara("", d.plain) +
        diffPara("What\u2019s actually there", d.seeing) +
        diffPara("Why English can\u2019t just say it", d.why) +
        diffPara("The pattern to watch for", d.rule) +
        diffPara("What each choice commits you to", d.choosing) +
      "</div>" +
      (d.lemma ? '<p class="ib-dlemma"><b>Lemma:</b> ' + esc(d.lemma) + "</p>" : "") +
      srcs + "</section>";
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
    fillOpenFolds();
    wireRail();
    // the outline may only have arrived on this paint, so the place we
    // were keeping is only restorable once the sections are really there
    if (restoreFor && VIEW.screen === "book" && VIEW.book === restoreFor &&
        MOUNT.querySelector(".ib-era")) {
      window.scrollTo(0, bookScroll[restoreFor]);
      restoreFor = null;
      wireRail();
    }
  }

  // ------------------------------------------------------------------ boot

  // the browser's own guess at where to put you is worse than ours
  if ("scrollRestoration" in history) history.scrollRestoration = "manual";
  VIEW = viewFromHash();
  render();
  if (VIEW.screen === "book" || VIEW.screen === "read") ensureOutline(VIEW.book);
})();
</script>
	<?php
} );
