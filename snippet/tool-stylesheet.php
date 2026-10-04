/**
 * JAYMS — Tool visual system (shared).
 *
 * One stylesheet for every JSON-driven tool. It was inline in snippet 137,
 * which meant the Divine Council Index and Gods of the Bible had the house
 * look and the Interleaved Bible did not -- so the outline grew its own
 * imitation of it, drifted, and had to be hand-corrected every time James
 * spotted a gap. Same rules, same values, all three pages: a size or a
 * colour is changed once, here, and nowhere else.
 *
 * Priority 8, ahead of the tools themselves. It does not need to load last:
 * every rule is scoped to .jayms-tool-alpha, so it outranks the bare class
 * selectors the outline inherited from the old timeline page.
 */

add_action( 'wp_footer', function () {

	// Divine Council Index, Gods of the Bible, Interleaved Bible.
	$PAGES = array( 98131, 98132, 96261 );

	if ( ! is_page() || ! in_array( get_queried_object_id(), $PAGES, true ) ) {
		return;
	}
	?>
<style id="jayms-alpha-css">
/* The shared components -- verse box, card, label, body, pills, chips --
   are NOT here. They live in global styles 90171, so every tool and every
   post pulls from one definition. What follows is Interleaved Bible only:
   word cards, era layout, the interlinear grid. If any of it turns up in a
   second tool, move it up to 90171 and delete the copy. */
/* ====================================================================
   Interleaved Bible components, migrated out of snippet 140 on
   2026-09-29. That page was carrying 36KB of its own stylesheet, copied
   from the old Old Testament Timeline page, with its own alias palette on
   top of the real one -- which is why it never matched Divine Council and
   why every correction needed another correction.

   Nothing here sets a colour or a size of its own: the aliases were
   rewritten to the tokens in global styles 90171, and anything the rules
   above already own (labels, card copy, reference chips, box text) had its
   typography stripped so there is one declaration per style, not two.
   ==================================================================== */
.jayms-tool-alpha *{box-sizing:border-box}
.jayms-tool-alpha .header{text-align:center;padding-top:50px;padding-right:40px;padding-bottom:34px;padding-left:40px;border-bottom-width:;border-bottom-style:;border-bottom-color:}
.jayms-tool-alpha .header::before{content:"✦  ✦  ✦";display:block;font-size:12px;color:var(--gold);letter-spacing:12px;margin-bottom:16px}
.jayms-tool-alpha .header::after{content:"✦  ✦  ✦";display:block;font-size:12px;color:var(--gold);letter-spacing:12px;margin-top:16px}
.jayms-tool-alpha .header-sub{font-size:10px;letter-spacing:5px;color:var(--ink-soft);text-transform:uppercase;margin-bottom:12px}
.jayms-tool-alpha .header-title{font-size:28px;font-weight:800;color:var(--ink);line-height:1.25;margin-top:0px;margin-right:0px;margin-bottom:0px;margin-left:0px;padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:0px;font-family:inherit;text-transform:none;letter-spacing:normal}
.jayms-tool-alpha .header-title span{color:var(--gold)}
.jayms-tool-alpha .header-desc{font-size:14px;color:var(--ink-soft);font-style:italic;margin-top:12px;max-width:520px;margin-left:auto;margin-right:auto;line-height:1.65}
.jayms-tool-alpha .era-header{display:flex;align-items:center;row-gap:14px;column-gap:14px;margin-bottom:20px;padding-bottom:14px;border-bottom-width:;border-bottom-style:;border-bottom-color:}
.jayms-tool-alpha .era-number{min-width:50px;text-align:center}
.jayms-tool-alpha .era-label{margin-bottom:3px}
.jayms-tool-alpha .era-name{margin-top:0px;margin-right:0px;margin-bottom:0px;margin-left:0px;padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:0px}
.jayms-tool-alpha .era-dates{margin-top:2px}
.jayms-tool-alpha .era-divider{flex-grow:1;flex-shrink:1;flex-basis:0%;height:1px;background-image:;background-position-x:;background-position-y:;background-size:;background-repeat:;background-attachment:;background-origin:;background-clip:;background-color:}
.jayms-tool-alpha .events-list{list-style-position:initial;list-style-image:initial;list-style-type:none;position:relative;padding-left:22px;margin-top:0px;margin-right:0px;margin-bottom:0px;margin-left:0px}
.jayms-tool-alpha .events-list::before{content:"";position:absolute;left:5px;top:8px;bottom:8px;width:1px;background-image:;background-position-x:;background-position-y:;background-size:;background-repeat:;background-attachment:;background-origin:;background-clip:;background-color:}
.jayms-tool-alpha .event-item{position:relative;border-top-color:;border-top-style:;border-top-width:;border-right-color:;border-right-style:;border-right-width:;border-bottom-color:;border-bottom-style:;border-bottom-width:;border-left-color:;border-left-style:;border-left-width:;border-image-source:;border-image-slice:;border-image-width:;border-image-outset:;border-image-repeat:;margin-bottom:10px;background-image:;background-position-x:;background-position-y:;background-size:;background-repeat:;background-attachment:;background-origin:;background-clip:;transition-behavior:normal;transition-duration:0.2s;transition-timing-function:ease;transition-delay:0s;transition-property:border-color}
.jayms-tool-alpha .event-item:hover{border-top-color:;border-right-color:;border-bottom-color:;border-left-color:}
.jayms-tool-alpha .event-item::before{content:"";position:absolute;left:-20px;top:16px;width:8px;height:8px;border-top-left-radius:50%;border-top-right-radius:50%;border-bottom-right-radius:50%;border-bottom-left-radius:50%;background-image:;background-position-x:;background-position-y:;background-size:;background-repeat:;background-attachment:;background-origin:;background-clip:;border-top-color:;border-top-style:;border-top-width:;border-right-color:;border-right-style:;border-right-width:;border-bottom-color:;border-bottom-style:;border-bottom-width:;border-left-color:;border-left-style:;border-left-width:;border-image-source:;border-image-slice:;border-image-width:;border-image-outset:;border-image-repeat:;z-index:1}
.jayms-tool-alpha .event-item.major::before{width:11px;height:11px;left:-21px;top:15px;background-image:;background-position-x:;background-position-y:;background-size:;background-repeat:;background-attachment:;background-origin:;background-clip:;border-top-color:;border-right-color:;border-bottom-color:;border-left-color:}
.jayms-tool-alpha .event-header{padding-top:12px;padding-right:14px;padding-bottom:12px;padding-left:14px;display:flex;align-items:baseline;row-gap:10px;column-gap:10px}
.jayms-tool-alpha .event-date{font-size:10px;letter-spacing:0.5px;color:var(--muted);white-space-collapse:collapse;text-wrap-mode:nowrap;min-width:80px;text-transform:uppercase}
.jayms-tool-alpha h3.event-title{font-size:18px;font-weight:700;color:var(--ink);flex-grow:1;flex-shrink:1;flex-basis:0%;line-height:1.3;margin-top:0px;margin-right:0px;margin-bottom:0px;margin-left:0px;padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:0px;font-family:inherit;text-transform:none;letter-spacing:normal}
.jayms-tool-alpha .event-books{white-space-collapse:collapse;text-wrap-mode:nowrap;opacity:0.9;flex-shrink:0}
.jayms-tool-alpha .event-arrow{font-size:10px;color:var(--gold);flex-shrink:0}
.jayms-tool-alpha .event-detail{margin-top:0px;margin-right:14px;margin-bottom:14px;margin-left:14px}
.jayms-tool-alpha .footer{text-align:center;padding-top:32px;padding-right:40px;padding-bottom:32px;padding-left:40px;border-top-width:;border-top-style:;border-top-color:;margin-top:50px}
.jayms-tool-alpha .footer-ornament{color:var(--gold);font-size:16px;margin-bottom:10px;letter-spacing:8px;opacity:0.6}
.jayms-tool-alpha .footer p{font-size:10px;letter-spacing:2px;color:var(--muted);text-transform:uppercase}
@media (max-width: 860px){
.jayms-tool-alpha .header{padding-top:32px;padding-right:18px;padding-bottom:24px;padding-left:18px}
.jayms-tool-alpha .header-title{font-size:22px}
.jayms-tool-alpha .events-list{padding-left:14px}
}
.jayms-tool-alpha .dek.dek-wide{max-width:none}
.jayms-tool-alpha .crumb{display:flex;align-items:center;row-gap:8px;column-gap:8px;margin-bottom:26px;font-family:"Cormorant Garamond", Georgia, serif;font-size:15px;letter-spacing:0.03em}
.jayms-tool-alpha .crumb button,
.jayms-tool-alpha .crumb-home{background-image:none;background-position-x:initial;background-position-y:initial;background-size:initial;background-repeat:initial;background-attachment:initial;background-origin:initial;background-clip:initial;background-color:initial;border-top-color:;border-top-style:;border-top-width:;border-right-color:;border-right-style:;border-right-width:;border-bottom-color:;border-bottom-style:;border-bottom-width:;border-left-color:;border-left-style:;border-left-width:;border-image-source:;border-image-slice:;border-image-width:;border-image-outset:;border-image-repeat:;color:var(--ink-soft);padding-top:5px;padding-right:10px;padding-bottom:5px;padding-left:10px;border-top-left-radius:5px;border-top-right-radius:5px;border-bottom-right-radius:5px;border-bottom-left-radius:5px;cursor:pointer;font-style:inherit;font-variant-ligatures:inherit;font-variant-caps:inherit;font-variant-numeric:inherit;font-variant-east-asian:inherit;font-variant-alternates:inherit;font-variant-position:inherit;font-variant-emoji:inherit;font-weight:inherit;font-stretch:inherit;font-size:inherit;line-height:inherit;font-family:inherit;font-optical-sizing:inherit;font-size-adjust:inherit;font-kerning:inherit;font-feature-settings:inherit;font-variation-settings:inherit;font-language-override:inherit;text-decoration-line:none;text-decoration-thickness:initial;text-decoration-style:initial;text-decoration-color:initial;display:inline-block}
.jayms-tool-alpha .crumb button:hover,
.jayms-tool-alpha .crumb-home:hover{color:var(--ink);border-top-color:;border-right-color:;border-bottom-color:;border-left-color:}
.jayms-tool-alpha .crumb .path{color:var(--ink-soft)}
.jayms-tool-alpha .crumb .path b{color:var(--gold);font-weight:600}
.jayms-tool-alpha .crumb .path .crumb-link{background-image:none;background-position-x:initial;background-position-y:initial;background-size:initial;background-repeat:initial;background-attachment:initial;background-origin:initial;background-clip:initial;background-color:initial;border-top-width:medium;border-right-width:medium;border-bottom-width:medium;border-left-width:medium;border-top-style:none;border-right-style:none;border-bottom-style:none;border-left-style:none;border-top-color:currentcolor;border-right-color:currentcolor;border-bottom-color:currentcolor;border-left-color:currentcolor;border-image-source:none;border-image-slice:100%;border-image-width:1;border-image-outset:0;border-image-repeat:stretch;padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:0px;font-style:inherit;font-variant-ligatures:inherit;font-variant-caps:inherit;font-variant-numeric:inherit;font-variant-east-asian:inherit;font-variant-alternates:inherit;font-variant-position:inherit;font-variant-emoji:inherit;font-stretch:inherit;font-size:inherit;line-height:inherit;font-family:inherit;font-optical-sizing:inherit;font-size-adjust:inherit;font-kerning:inherit;font-feature-settings:inherit;font-variation-settings:inherit;font-language-override:inherit;color:var(--gold);font-weight:600;cursor:pointer;text-decoration-line:none;text-decoration-thickness:initial;text-decoration-style:initial;text-decoration-color:initial}
.jayms-tool-alpha .crumb .path .crumb-link:hover{text-decoration-line:underline;text-decoration-thickness:initial;text-decoration-style:initial;text-decoration-color:initial}
.jayms-tool-alpha .label-link{background-image:none;background-position-x:initial;background-position-y:initial;background-size:initial;background-repeat:initial;background-attachment:initial;background-origin:initial;background-clip:initial;background-color:initial;border-top-width:medium;border-right-width:medium;border-bottom-width:medium;border-left-width:medium;border-top-style:none;border-right-style:none;border-bottom-style:none;border-left-style:none;border-top-color:currentcolor;border-right-color:currentcolor;border-bottom-color:currentcolor;border-left-color:currentcolor;border-image-source:none;border-image-slice:100%;border-image-width:1;border-image-outset:0;border-image-repeat:stretch;padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:0px;cursor:pointer;color:var(--gold);text-decoration-line:none;text-decoration-thickness:initial;text-decoration-style:initial;text-decoration-color:initial}
.jayms-tool-alpha .label-link:hover{text-decoration-line:underline;text-decoration-thickness:initial;text-decoration-style:initial;text-decoration-color:initial}
.jayms-tool-alpha .bst-console{position:relative;margin-top:0px;margin-right:0px;margin-bottom:2.5rem;margin-left:0px;border-top-width:1px;border-right-width:1px;border-bottom-width:1px;border-left-width:1px;border-top-style:solid;border-right-style:solid;border-bottom-style:solid;border-left-style:solid;border-top-color:rgb(58, 52, 44);border-right-color:rgb(58, 52, 44);border-bottom-color:rgb(58, 52, 44);border-left-color:rgb(58, 52, 44);border-image-source:none;border-image-slice:100%;border-image-width:1;border-image-outset:0;border-image-repeat:stretch;background-color:rgb(20, 17, 14);background-image:linear-gradient(rgba(58, 52, 44, 0.4) 1px, transparent 1px), linear-gradient(90deg, rgba(58, 52, 44, 0.4) 1px, transparent 1px);background-size:26px 26px}
.jayms-tool-alpha .bst-titlebar{display:flex;align-items:center;row-gap:0.9rem;column-gap:0.9rem;padding-top:0.6rem;padding-right:0.9rem;padding-bottom:0.6rem;padding-left:0.9rem;border-bottom-width:1px;border-bottom-style:solid;border-bottom-color:rgb(58, 52, 44);background-image:initial;background-position-x:initial;background-position-y:initial;background-size:initial;background-repeat:initial;background-attachment:initial;background-origin:initial;background-clip:initial;background-color:rgb(29, 25, 22);font-family:"JetBrains Mono", ui-monospace, Menlo, Consolas, monospace;font-size:0.68rem;letter-spacing:0.12em;text-transform:uppercase}
.jayms-tool-alpha .bst-lights{display:inline-flex;row-gap:0.35rem;column-gap:0.35rem}
.jayms-tool-alpha .bst-lights i{width:9px;height:9px;border-top-left-radius:50%;border-top-right-radius:50%;border-bottom-right-radius:50%;border-bottom-left-radius:50%;display:block}
.jayms-tool-alpha .bst-lights .l1{background-image:initial;background-position-x:initial;background-position-y:initial;background-size:initial;background-repeat:initial;background-attachment:initial;background-origin:initial;background-clip:initial;background-color:rgb(212, 102, 79)}
.jayms-tool-alpha .bst-lights .l2{background-image:initial;background-position-x:initial;background-position-y:initial;background-size:initial;background-repeat:initial;background-attachment:initial;background-origin:initial;background-clip:initial;background-color:rgb(212, 168, 92)}
.jayms-tool-alpha .bst-lights .l3{background-image:initial;background-position-x:initial;background-position-y:initial;background-size:initial;background-repeat:initial;background-attachment:initial;background-origin:initial;background-clip:initial;background-color:rgb(168, 165, 101)}
.jayms-tool-alpha .bst-titlebar-name{color:rgb(237, 228, 211);letter-spacing:0.18em}
.jayms-tool-alpha .bst-titlebar-meta{margin-left:auto;color:rgb(132, 122, 106)}
.jayms-tool-alpha .bst-readout{display:flex;flex-wrap:wrap;row-gap:0.4rem;column-gap:2.5rem;padding-top:1.1rem;padding-right:1.2rem;padding-bottom:0.9rem;padding-left:1.2rem;font-family:"JetBrains Mono", ui-monospace, Menlo, Consolas, monospace;font-size:0.66rem;letter-spacing:0.08em;text-transform:uppercase;color:rgb(196, 185, 163)}
.jayms-tool-alpha .bst-readout b{color:rgb(132, 122, 106);font-weight:400;margin-right:0.5rem}
.jayms-tool-alpha .bst-mark{position:absolute;width:12px;height:12px}
.jayms-tool-alpha .bst-mark-tl{top:-1px;left:-1px;border-top-width:2px;border-top-style:solid;border-top-color:rgb(212, 168, 92);border-left-width:2px;border-left-style:solid;border-left-color:rgb(212, 168, 92)}
.jayms-tool-alpha .bst-mark-tr{top:-1px;right:-1px;border-top-width:2px;border-top-style:solid;border-top-color:rgb(212, 168, 92);border-right-width:2px;border-right-style:solid;border-right-color:rgb(212, 168, 92)}
.jayms-tool-alpha .bst-mark-bl{bottom:-1px;left:-1px;border-bottom-width:2px;border-bottom-style:solid;border-bottom-color:rgb(212, 168, 92);border-left-width:2px;border-left-style:solid;border-left-color:rgb(212, 168, 92)}
.jayms-tool-alpha .bst-mark-br{bottom:-1px;right:-1px;border-bottom-width:2px;border-bottom-style:solid;border-bottom-color:rgb(212, 168, 92);border-right-width:2px;border-right-style:solid;border-right-color:rgb(212, 168, 92)}
.jayms-tool-alpha .bst-console .bst-intro{max-width:58rem;margin-top:0px;margin-right:1.2rem;margin-bottom:1.4rem;margin-left:1.2rem;padding-top:0.9rem;border-top-width:1px;border-top-style:solid;border-top-color:rgb(58, 52, 44);color:rgb(196, 185, 163);font-size:1.02rem;line-height:1.6}
@media (max-width: 900px){
.jayms-tool-alpha .bst-readout{flex-direction:column;row-gap:0.35rem;column-gap:0.35rem}
.jayms-tool-alpha .bst-titlebar-meta{display:none}
.jayms-tool-alpha .bst-console .bst-intro{font-size:0.95rem;margin-top:0px;margin-right:1rem;margin-bottom:1.2rem;margin-left:1rem}
}
.parent-pageid-91449 .jayms-page-shell{display:none !important}
.parent-pageid-91449 main.wp-block-group{padding-top:24px !important}
.jayms-tool-alpha .other-tools{font-family:"Cormorant Garamond", Georgia, serif;font-size:11px;color:var(--muted);margin-top:-16px;margin-right:0px;margin-bottom:26px;margin-left:0px;display:flex;align-items:center;row-gap:8px;column-gap:8px;flex-wrap:wrap}
.jayms-tool-alpha .other-tools a{color:var(--gold);text-decoration-line:none;text-decoration-thickness:initial;text-decoration-style:initial;text-decoration-color:initial;border-bottom-width:;border-bottom-style:;border-bottom-color:}
.jayms-tool-alpha .other-tools a:hover{color:var(--ink);border-top-color:;border-right-color:;border-bottom-color:;border-left-color:}
.jayms-tool-alpha h2.section{font-family:"Cormorant Garamond", Georgia, serif;font-size:11px;text-transform:uppercase;letter-spacing:0.14em;color:var(--ink-soft);margin-top:38px;margin-right:0px;margin-bottom:14px;margin-left:0px;padding-top:24px;border-top-width:;border-top-style:;border-top-color:}
.jayms-tool-alpha h2.section:first-of-type{border-top-width:medium;border-top-style:none;border-top-color:currentcolor;padding-top:0px;margin-top:0px}
.jayms-tool-alpha h3.subsection{font-family:Cormorant, serif;font-weight:600;font-size:16px;color:var(--gold);margin-top:22px;margin-right:0px;margin-bottom:12px;margin-left:0px;display:flex;align-items:baseline;row-gap:10px;column-gap:10px}
.jayms-tool-alpha h3.subsection .count{font-family:"Cormorant Garamond", Georgia, serif;font-size:10px;text-transform:uppercase;letter-spacing:0.08em;color:var(--ink-soft);font-weight:600}
.jayms-tool-alpha .grid + h3.subsection{margin-top:28px}
.jayms-tool-alpha .grid{display:grid;grid-template-columns:repeat(auto-fill, minmax(150px, 1fr));row-gap:10px;column-gap:10px}
.jayms-tool-alpha .grid.entities{grid-template-columns:repeat(auto-fill, minmax(280px, 1fr));row-gap:14px;column-gap:14px;margin-top:20px;align-items:stretch}
.jayms-tool-alpha .tile.entity-tile{padding-top:22px;padding-right:22px;padding-bottom:20px;padding-left:22px;display:flex;flex-direction:column;height:100%}
.jayms-tool-alpha .entity-name{display:block;font-size:23px;font-weight:600;color:var(--ink);margin-bottom:5px}
.jayms-tool-alpha .tile.entity-tile .genre-tag{font-size:10.5px;margin-top:0px}
.jayms-tool-alpha .entity-note{display:block;font-size:15px;font-style:italic;color:var(--ink-soft);line-height:1.5;margin-top:8px;margin-right:0px;margin-bottom:10px;margin-left:0px;font-family:"Cormorant Garamond", Georgia, serif;text-transform:none;letter-spacing:normal}
.jayms-tool-alpha .tile.entity-tile .sub{font-size:10.5px;margin-top:auto;padding-top:10px}
.jayms-tool-alpha .grid.chapters,
.jayms-tool-alpha .grid.verses{grid-template-columns:repeat(auto-fill, minmax(64px, 1fr))}
.jayms-tool-alpha .tile{border-top-color:;border-top-style:;border-top-width:;border-right-color:;border-right-style:;border-right-width:;border-bottom-color:;border-bottom-style:;border-bottom-width:;border-left-color:;border-left-style:;border-left-width:;border-image-source:;border-image-slice:;border-image-width:;border-image-outset:;border-image-repeat:;border-top-left-radius:8px;border-top-right-radius:8px;border-bottom-right-radius:8px;border-bottom-left-radius:8px;padding-top:14px;padding-right:14px;padding-bottom:12px;padding-left:14px;background-image:;background-position-x:;background-position-y:;background-size:;background-repeat:;background-attachment:;background-origin:;background-clip:;background-color:;cursor:pointer;text-align:left;font-family:inherit;color:var(--ink);font-size:16px;display:block;text-decoration-line:none;text-decoration-thickness:initial;text-decoration-style:initial;text-decoration-color:initial;width:100%}
.jayms-tool-alpha .tile:hover{border-top-color:;border-right-color:;border-bottom-color:;border-left-color:}
.jayms-tool-alpha .tile .genre-tag{display:block;font-family:"Cormorant Garamond", Georgia, serif;font-size:9px;text-transform:uppercase;letter-spacing:0.09em;color:var(--rust);margin-top:6px}
.jayms-tool-alpha .tile .sub{display:block;font-family:"Cormorant Garamond", Georgia, serif;font-size:9.5px;text-transform:uppercase;letter-spacing:0.08em;color:var(--ink-soft);margin-top:3px}
.jayms-tool-alpha .tile.witness{border-left-width:;border-left-style:;border-left-color:}
.jayms-tool-alpha .tile.tool{border-left-width:;border-left-style:;border-left-color:}
.jayms-tool-alpha .tile.tool .genre-tag{color:var(--gold)}
.jayms-tool-alpha .tile.chap,
.jayms-tool-alpha .tile.vs{display:flex;align-items:center;justify-content:center;padding-top:12px;padding-right:6px;padding-bottom:12px;padding-left:6px;font-size:16px;text-align:center;border-top-left-radius:6px;border-top-right-radius:6px;border-bottom-right-radius:6px;border-bottom-left-radius:6px}
.jayms-tool-alpha .tile.disabled{opacity:0.32;cursor:default;pointer-events:none}
.jayms-tool-alpha .tile.disabled:hover{border-top-color:;border-right-color:;border-bottom-color:;border-left-color:}
.jayms-tool-alpha .note{margin-top:36px;padding-top:14px;padding-right:16px;padding-bottom:14px;padding-left:16px;background-image:;background-position-x:;background-position-y:;background-size:;background-repeat:;background-attachment:;background-origin:;background-clip:;background-color:;border-left-width:;border-left-style:;border-left-color:;font-size:13.5px;color:var(--ink-soft);line-height:1.5}
.jayms-tool-alpha .note b{color:var(--ink);font-weight:600}
.jayms-tool-alpha.overlay{position:fixed;top:0px;right:0px;bottom:0px;left:0px;background-image:initial;background-position-x:initial;background-position-y:initial;background-size:initial;background-repeat:initial;background-attachment:initial;background-origin:initial;background-clip:initial;background-color:rgba(10, 8, 6, 0.6);backdrop-filter:blur(2px);display:flex;align-items:flex-end;justify-content:center;z-index:40;opacity:0;pointer-events:none;transition-behavior:normal;transition-duration:0.18s;transition-timing-function:ease;transition-delay:0s;transition-property:opacity}
.jayms-tool-alpha.overlay.open{opacity:1;pointer-events:auto}
@media (min-width: 760px){
.jayms-tool-alpha.overlay{align-items:center}
}
.jayms-tool-alpha .panel{width:100%;max-width:1440px;min-height:64vh;max-height:94vh;overflow-y:auto;background-image:;background-position-x:;background-position-y:;background-size:;background-repeat:;background-attachment:;background-origin:;background-clip:;background-color:;border-top-color:;border-top-style:;border-top-width:;border-right-color:;border-right-style:;border-right-width:;border-left-color:;border-left-style:;border-left-width:;border-image-source:;border-image-slice:;border-image-width:;border-image-outset:;border-image-repeat:;border-bottom-width:medium;border-bottom-style:none;border-bottom-color:currentcolor;border-top-left-radius:14px;border-top-right-radius:14px;border-bottom-right-radius:0px;border-bottom-left-radius:0px;padding-top:26px;padding-right:24px;padding-bottom:22px;padding-left:24px;transform:translateY(18px);transition-behavior:normal;transition-duration:0.2s;transition-timing-function:ease;transition-delay:0s;transition-property:transform}
.jayms-tool-alpha.overlay.open .panel{transform:translateY(0px)}
@media (min-width: 760px){
.jayms-tool-alpha .panel{border-bottom-width:;border-bottom-style:;border-bottom-color:;border-top-left-radius:14px;border-top-right-radius:14px;border-bottom-right-radius:14px;border-bottom-left-radius:14px;max-height:94vh}
}
.jayms-tool-alpha .panel-top{display:flex;align-items:flex-start;justify-content:space-between;row-gap:16px;column-gap:16px;border-bottom-width:;border-bottom-style:;border-bottom-color:;padding-bottom:16px;margin-bottom:18px}
.jayms-tool-alpha .panel-ref{font-size:19px;color:var(--gold);margin-bottom:6px}
.jayms-tool-alpha .panel-verse-text{font-size:22px;line-height:1.5;color:var(--ink);text-wrap-mode:initial;text-wrap-style:balance}
.jayms-tool-alpha .panel-verse-text.rtl{direction:rtl;text-align:right}
.jayms-tool-alpha .panel-en-row{display:flex;row-gap:8px;column-gap:8px;align-items:baseline;margin-top:8px}
.jayms-tool-alpha .panel-en-tag{flex-grow:0;flex-shrink:0;flex-basis:auto;border-top-color:;border-top-style:;border-top-width:;border-right-color:;border-right-style:;border-right-width:;border-bottom-color:;border-bottom-style:;border-bottom-width:;border-left-color:;border-left-style:;border-left-width:;border-image-source:;border-image-slice:;border-image-width:;border-image-outset:;border-image-repeat:;border-top-left-radius:3px;border-top-right-radius:3px;border-bottom-right-radius:3px;border-bottom-left-radius:3px;padding-top:1px;padding-right:5px;padding-bottom:1px;padding-left:5px}
.jayms-tool-alpha .panel-en{text-wrap-mode:initial;text-wrap-style:balance;min-height:1.6em}
.jayms-tool-alpha .panel-en.loading{opacity:0.5}
.jayms-tool-alpha .panel-close{flex-grow:0;flex-shrink:0;flex-basis:auto;width:30px;height:30px;border-top-left-radius:50%;border-top-right-radius:50%;border-bottom-right-radius:50%;border-bottom-left-radius:50%;border-top-color:;border-top-style:;border-top-width:;border-right-color:;border-right-style:;border-right-width:;border-bottom-color:;border-bottom-style:;border-bottom-width:;border-left-color:;border-left-style:;border-left-width:;border-image-source:;border-image-slice:;border-image-width:;border-image-outset:;border-image-repeat:;background-image:initial;background-position-x:initial;background-position-y:initial;background-size:initial;background-repeat:initial;background-attachment:initial;background-origin:initial;background-clip:initial;background-color:transparent;color:var(--muted);font-size:16px;cursor:pointer;display:flex;align-items:center;justify-content:center;line-height:1}
.jayms-tool-alpha .panel-close:hover{color:var(--ink);border-top-color:;border-right-color:;border-bottom-color:;border-left-color:}
.jayms-tool-alpha .words{display:flex;row-gap:10px;column-gap:10px;overflow-x:auto;padding-bottom:10px;margin-bottom:6px;scroll-snap-type:x}
.jayms-tool-alpha .words::-webkit-scrollbar{height:6px}
.jayms-tool-alpha .words::-webkit-scrollbar-thumb{background-image:;background-position-x:;background-position-y:;background-size:;background-repeat:;background-attachment:;background-origin:;background-clip:;background-color:;border-top-left-radius:3px;border-top-right-radius:3px;border-bottom-right-radius:3px;border-bottom-left-radius:3px}
.jayms-tool-alpha .wcard{flex-grow:0;flex-shrink:0;flex-basis:auto;width:200px;scroll-snap-align:start;border-top-color:;border-top-style:;border-top-width:;border-right-color:;border-right-style:;border-right-width:;border-bottom-color:;border-bottom-style:;border-bottom-width:;border-left-color:;border-left-style:;border-left-width:;border-image-source:;border-image-slice:;border-image-width:;border-image-outset:;border-image-repeat:;border-top-left-radius:8px;border-top-right-radius:8px;border-bottom-right-radius:8px;border-bottom-left-radius:8px;padding-top:14px;padding-right:12px;padding-bottom:12px;padding-left:12px;background-image:;background-position-x:;background-position-y:;background-size:;background-repeat:;background-attachment:;background-origin:;background-clip:;background-color:;display:flex;flex-direction:column;direction:ltr}
.jayms-tool-alpha .wcard .grk{font-size:24px;color:var(--ink);line-height:1.2;margin-bottom:2px;text-wrap-mode:initial;text-wrap-style:balance}
.jayms-tool-alpha .wcard .grk.rtl{direction:rtl;text-align:right}
.jayms-tool-alpha .wcard .translit{font-size:13px;color:var(--ink-soft);font-style:italic;margin-bottom:6px}
.jayms-tool-alpha .wcard .gloss{font-family:"Cormorant Garamond", Georgia, serif;font-size:15px;color:var(--gold);margin-bottom:10px}
.jayms-tool-alpha .wcard .gram{font-family:"Cormorant Garamond", Georgia, serif;font-size:12px;color:var(--ink-soft);text-transform:uppercase;letter-spacing:0.05em;line-height:1.7}
.jayms-tool-alpha .wcard .gram div{display:flex;justify-content:space-between;row-gap:6px;column-gap:6px}
.jayms-tool-alpha .wcard .gram span:last-child{color:var(--ink);font-weight:600}
.jayms-tool-alpha .wcard .strong{margin-top:auto;padding-top:8px;border-top-width:;border-top-style:;border-top-color:;font-family:"Cormorant Garamond", Georgia, serif;font-size:11px;color:var(--ink-soft);letter-spacing:0.04em}
.jayms-tool-alpha .fullentry-btn:hover{color:var(--ink);border-top-color:;border-right-color:;border-bottom-color:;border-left-color:}
.jayms-tool-alpha .wcard-links{display:flex;row-gap:14px;column-gap:14px;margin-top:8px;padding-top:8px;border-top-width:;border-top-style:;border-top-color:}
.jayms-tool-alpha .wcard .fullentry-btn{display:block;width:auto;flex-grow:1;flex-shrink:1;flex-basis:0%;margin-top:0px;padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:0px;background-image:none;background-position-x:initial;background-position-y:initial;background-size:initial;background-repeat:initial;background-attachment:initial;background-origin:initial;background-clip:initial;background-color:initial;border-top-width:medium;border-right-width:medium;border-bottom-width:medium;border-left-width:medium;border-top-style:none;border-right-style:none;border-bottom-style:none;border-left-style:none;border-top-color:currentcolor;border-right-color:currentcolor;border-bottom-color:currentcolor;border-left-color:currentcolor;border-image-source:none;border-image-slice:100%;border-image-width:1;border-image-outset:0;border-image-repeat:stretch;font-family:"Cormorant Garamond", Georgia, serif;font-size:12px;letter-spacing:0.06em;text-transform:uppercase;color:var(--gold);cursor:pointer;text-align:left}
.jayms-tool-alpha .wcard .fullentry-btn:hover{color:var(--ink)}
.jayms-tool-alpha .attrib{margin-top:16px;padding-top:14px;border-top-width:;border-top-style:;border-top-color:;font-family:"Cormorant Garamond", Georgia, serif;font-size:9.5px;color:var(--ink-soft);letter-spacing:0.02em;line-height:1.6}
.jayms-tool-alpha .attrib a{color:var(--ink-soft)}
.jayms-tool-alpha .swipe-hint{font-family:"Cormorant Garamond", Georgia, serif;font-size:8.5px;color:var(--muted);text-transform:uppercase;letter-spacing:0.1em;margin-bottom:10px}
.jayms-tool-alpha.lex-overlay{position:fixed;top:0px;right:0px;bottom:0px;left:0px;background-image:initial;background-position-x:initial;background-position-y:initial;background-size:initial;background-repeat:initial;background-attachment:initial;background-origin:initial;background-clip:initial;background-color:rgba(10, 8, 6, 0.72);backdrop-filter:blur(2px);display:flex;align-items:center;justify-content:center;z-index:50;padding-top:20px;padding-right:20px;padding-bottom:20px;padding-left:20px;opacity:0;pointer-events:none;transition-behavior:normal;transition-duration:0.16s;transition-timing-function:ease;transition-delay:0s;transition-property:opacity}
.jayms-tool-alpha.lex-overlay.open{opacity:1;pointer-events:auto}
.jayms-tool-alpha .lex-panel{width:100%;max-width:480px;max-height:84vh;overflow-y:auto;background-image:;background-position-x:;background-position-y:;background-size:;background-repeat:;background-attachment:;background-origin:;background-clip:;background-color:;border-top-color:;border-top-style:;border-top-width:;border-right-color:;border-right-style:;border-right-width:;border-bottom-color:;border-bottom-style:;border-bottom-width:;border-left-color:;border-left-style:;border-left-width:;border-image-source:;border-image-slice:;border-image-width:;border-image-outset:;border-image-repeat:;border-top-left-radius:12px;border-top-right-radius:12px;border-bottom-right-radius:12px;border-bottom-left-radius:12px;padding-top:24px;padding-right:22px;padding-bottom:20px;padding-left:22px}
.jayms-tool-alpha .lex-top{display:flex;align-items:flex-start;justify-content:space-between;row-gap:14px;column-gap:14px;border-bottom-width:;border-bottom-style:;border-bottom-color:;padding-bottom:14px;margin-bottom:16px}
.jayms-tool-alpha .lex-word{font-size:26px;color:var(--ink)}
.jayms-tool-alpha .lex-word.rtl{direction:rtl}
.jayms-tool-alpha .lex-translit{font-size:16px;color:var(--ink-soft);font-style:italic;margin-top:2px}
.jayms-tool-alpha .lex-close{flex-grow:0;flex-shrink:0;flex-basis:auto;width:28px;height:28px;border-top-left-radius:50%;border-top-right-radius:50%;border-bottom-right-radius:50%;border-bottom-left-radius:50%;border-top-color:;border-top-style:;border-top-width:;border-right-color:;border-right-style:;border-right-width:;border-bottom-color:;border-bottom-style:;border-bottom-width:;border-left-color:;border-left-style:;border-left-width:;border-image-source:;border-image-slice:;border-image-width:;border-image-outset:;border-image-repeat:;background-image:initial;background-position-x:initial;background-position-y:initial;background-size:initial;background-repeat:initial;background-attachment:initial;background-origin:initial;background-clip:initial;background-color:transparent;color:var(--muted);font-size:15px;cursor:pointer}
.jayms-tool-alpha .lex-close:hover{color:var(--ink);border-top-color:;border-right-color:;border-bottom-color:;border-left-color:}
.jayms-tool-alpha .lex-block{margin-bottom:18px}
.jayms-tool-alpha .lex-block .lbl{font-family:"Cormorant Garamond", Georgia, serif;font-size:9.5px;text-transform:uppercase;letter-spacing:0.1em;color:var(--gold);margin-bottom:8px}
.jayms-tool-alpha .lex-block .body{font-size:19px;line-height:1.6;color:var(--ink)}
.jayms-tool-alpha .lex-block .pron{color:var(--ink-soft);font-style:italic;font-size:16px;margin-bottom:4px}
.jayms-tool-alpha .lex-block .lx-head{margin-bottom:8px}
.jayms-tool-alpha .lex-block .lx-sense{margin-bottom:6px}
.jayms-tool-alpha .lex-block .lx-num{color:var(--gold);font-weight:600}
.jayms-tool-alpha .lex-source{font-family:"Cormorant Garamond", Georgia, serif;font-size:9.5px;color:var(--muted);margin-top:2px}
.jayms-tool-alpha .lex-source a{color:var(--muted)}
.jayms-tool-alpha .witness-body .compare{display:grid;grid-template-columns:1fr 1fr;row-gap:16px;column-gap:16px;margin-top:16px;margin-right:0px;margin-bottom:16px;margin-left:0px}
@media (max-width: 640px){
.jayms-tool-alpha .witness-body .compare{grid-template-columns:1fr}
}
.jayms-tool-alpha .witness-body .colblock{background-image:;background-position-x:;background-position-y:;background-size:;background-repeat:;background-attachment:;background-origin:;background-clip:;background-color:;border-top-color:;border-top-style:;border-top-width:;border-right-color:;border-right-style:;border-right-width:;border-bottom-color:;border-bottom-style:;border-bottom-width:;border-left-color:;border-left-style:;border-left-width:;border-image-source:;border-image-slice:;border-image-width:;border-image-outset:;border-image-repeat:;padding-top:14px;padding-right:16px;padding-bottom:14px;padding-left:16px}
.jayms-tool-alpha .witness-body .colblock .label{font-size:10.5px;text-transform:uppercase;letter-spacing:1px;color:var(--ink-soft);margin-bottom:8px;font-family:"Cormorant Garamond", Georgia, serif}
.jayms-tool-alpha .witness-body .colblock .txt{font-size:16px;line-height:1.55;color:var(--ink)}
.jayms-tool-alpha .witness-body .colblock.aramaic .txt{color:var(--aramaic)}
.jayms-tool-alpha .witness-body .colblock .script{direction:rtl;font-size:17px;line-height:1.9;color:var(--aramaic);margin-top:10px;padding-top:10px;border-top-width:;border-top-style:;border-top-color:}
.jayms-tool-alpha .witness-body .note{margin-top:0px}
.jayms-tool-alpha p.reading{font-size:19px;line-height:1.75;color:var(--ink);max-width:640px;margin-top:0px;margin-right:0px;margin-bottom:28px;margin-left:0px}
.jayms-tool-alpha .passage-verse{border-top-width:;border-top-style:;border-top-color:;padding-top:22px;padding-right:0px;padding-bottom:22px;padding-left:0px}
.jayms-tool-alpha .passage-verse:first-of-type{border-top-width:medium;border-top-style:none;border-top-color:currentcolor;padding-top:6px}
.jayms-tool-alpha .passage-ref{margin-bottom:6px}
.jayms-tool-alpha .witness-trigger{display:inline-flex;align-items:center;row-gap:8px;column-gap:8px;margin-top:0px;margin-right:0px;margin-bottom:30px;margin-left:0px;padding-top:10px;padding-right:16px;padding-bottom:10px;padding-left:13px;background-image:;background-position-x:;background-position-y:;background-size:;background-repeat:;background-attachment:;background-origin:;background-clip:;background-color:;border-top-color:;border-top-style:;border-top-width:;border-right-color:;border-right-style:;border-right-width:;border-bottom-color:;border-bottom-style:;border-bottom-width:;border-image-source:;border-image-slice:;border-image-width:;border-image-outset:;border-image-repeat:;border-left-width:;border-left-style:;border-left-color:;border-top-left-radius:4px;border-top-right-radius:4px;border-bottom-right-radius:4px;border-bottom-left-radius:4px;color:var(--aramaic);font-family:"Cormorant Garamond", Georgia, serif;font-size:15.5px;cursor:pointer}
.jayms-tool-alpha .witness-trigger:hover{border-top-color:;border-right-color:;border-bottom-color:;border-left-color:}
.jayms-tool-alpha .witness-trigger .glyph{font-size:16px;line-height:1}
.jayms-tool-alpha .wsearch{width:100%;margin-bottom:22px;padding-top:13px;padding-right:16px;padding-bottom:13px;padding-left:16px;background-image:;background-position-x:;background-position-y:;background-size:;background-repeat:;background-attachment:;background-origin:;background-clip:;background-color:;border-top-color:;border-top-style:;border-top-width:;border-right-color:;border-right-style:;border-right-width:;border-bottom-color:;border-bottom-style:;border-bottom-width:;border-left-color:;border-left-style:;border-left-width:;border-image-source:;border-image-slice:;border-image-width:;border-image-outset:;border-image-repeat:;border-top-left-radius:6px;border-top-right-radius:6px;border-bottom-right-radius:6px;border-bottom-left-radius:6px;color:var(--ink);font-family:"Cormorant Garamond", Georgia, serif;font-size:17px}
.jayms-tool-alpha .wsearch:focus{outline-color:initial;outline-style:none;outline-width:initial;border-top-color:;border-right-color:;border-bottom-color:;border-left-color:}
.jayms-tool-alpha .wsearch-wrap{position:relative}
.jayms-tool-alpha .wsearch-wrap .wsearch{padding-right:40px}
.jayms-tool-alpha .wsearch-clear{position:absolute;right:6px;top:0px;bottom:22px;margin-top:auto;margin-right:0px;margin-bottom:auto;margin-left:0px;background-image:none;background-position-x:initial;background-position-y:initial;background-size:initial;background-repeat:initial;background-attachment:initial;background-origin:initial;background-clip:initial;background-color:initial;border-top-width:medium;border-right-width:medium;border-bottom-width:medium;border-left-width:medium;border-top-style:none;border-right-style:none;border-bottom-style:none;border-left-style:none;border-top-color:currentcolor;border-right-color:currentcolor;border-bottom-color:currentcolor;border-left-color:currentcolor;border-image-source:none;border-image-slice:100%;border-image-width:1;border-image-outset:0;border-image-repeat:stretch;color:var(--ink-soft);cursor:pointer;font-size:22px;line-height:1;padding-top:6px;padding-right:10px;padding-bottom:6px;padding-left:10px;font-family:inherit}
.jayms-tool-alpha .wsearch-clear:hover{color:var(--ink)}
.jayms-tool-alpha .wslist{display:flex;flex-direction:column;row-gap:1px;column-gap:1px}
.jayms-tool-alpha .wsrow{display:flex;align-items:baseline;row-gap:16px;column-gap:16px;padding-top:14px;padding-right:16px;padding-bottom:14px;padding-left:16px;background-image:;background-position-x:;background-position-y:;background-size:;background-repeat:;background-attachment:;background-origin:;background-clip:;background-color:;border-top-color:;border-top-style:;border-top-width:;border-right-color:;border-right-style:;border-right-width:;border-bottom-color:;border-bottom-style:;border-bottom-width:;border-left-color:;border-left-style:;border-left-width:;border-image-source:;border-image-slice:;border-image-width:;border-image-outset:;border-image-repeat:;border-top-left-radius:5px;border-top-right-radius:5px;border-bottom-right-radius:5px;border-bottom-left-radius:5px;color:var(--ink);font-family:inherit;font-size:18px;cursor:pointer;text-align:left;margin-bottom:5px}
.jayms-tool-alpha .wsrow:hover{border-top-color:;border-right-color:;border-bottom-color:;border-left-color:}
.jayms-tool-alpha .wsrow[hidden]{display:none}
.jayms-tool-alpha .wsgloss{flex-grow:0;flex-shrink:1;flex-basis:300px;color:var(--ink);font-size:19px}
.jayms-tool-alpha .wsgrk{flex-grow:0;flex-shrink:0;flex-basis:auto;min-width:110px;color:var(--gold);font-size:18px}
.jayms-tool-alpha .wsstrong{flex-grow:0;flex-shrink:0;flex-basis:auto;min-width:64px;font-size:12px;color:var(--muted);letter-spacing:0.03em;font-family:"Cormorant Garamond", Georgia, serif;margin-left:auto}
.jayms-tool-alpha .wscount{flex-grow:0;flex-shrink:0;flex-basis:auto;font-family:"Cormorant Garamond", Georgia, serif;font-size:12.5px;color:var(--muted)}
.jayms-tool-alpha .ws-range{font-family:"Cormorant Garamond", Georgia, serif;font-size:11px;text-transform:uppercase;letter-spacing:0.06em;color:var(--muted);margin-top:0px;margin-right:0px;margin-bottom:10px;margin-left:0px}
.jayms-tool-alpha .ws-pager{display:flex;align-items:center;justify-content:center;row-gap:16px;column-gap:16px;margin-top:22px;margin-right:0px;margin-bottom:8px;margin-left:0px;font-family:"Cormorant Garamond", Georgia, serif;font-size:12px;color:var(--muted)}
.jayms-tool-alpha .ws-pager button{background-image:none;background-position-x:initial;background-position-y:initial;background-size:initial;background-repeat:initial;background-attachment:initial;background-origin:initial;background-clip:initial;background-color:initial;border-top-color:;border-top-style:;border-top-width:;border-right-color:;border-right-style:;border-right-width:;border-bottom-color:;border-bottom-style:;border-bottom-width:;border-left-color:;border-left-style:;border-left-width:;border-image-source:;border-image-slice:;border-image-width:;border-image-outset:;border-image-repeat:;color:var(--ink);border-top-left-radius:5px;border-top-right-radius:5px;border-bottom-right-radius:5px;border-bottom-left-radius:5px;padding-top:7px;padding-right:14px;padding-bottom:7px;padding-left:14px;cursor:pointer;font-style:inherit;font-variant-ligatures:inherit;font-variant-caps:inherit;font-variant-numeric:inherit;font-variant-east-asian:inherit;font-variant-alternates:inherit;font-variant-position:inherit;font-variant-emoji:inherit;font-weight:inherit;font-stretch:inherit;font-size:inherit;line-height:inherit;font-family:inherit;font-optical-sizing:inherit;font-size-adjust:inherit;font-kerning:inherit;font-feature-settings:inherit;font-variation-settings:inherit;font-language-override:inherit}
.jayms-tool-alpha .ws-pager button:hover:not(:disabled){border-top-color:;border-right-color:;border-bottom-color:;border-left-color:;color:var(--gold)}
.jayms-tool-alpha .ws-pager button:disabled{opacity:0.32;cursor:default}
.jayms-tool-alpha .wsdetail-head{display:flex;align-items:center;row-gap:18px;column-gap:18px;margin-bottom:6px}
.jayms-tool-alpha .wsdetail-ring{flex-grow:0;flex-shrink:0;flex-basis:auto;width:64px;height:64px;border-top-left-radius:50%;border-top-right-radius:50%;border-bottom-right-radius:50%;border-bottom-left-radius:50%;border-top-color:;border-top-style:;border-top-width:;border-right-color:;border-right-style:;border-right-width:;border-bottom-color:;border-bottom-style:;border-bottom-width:;border-left-color:;border-left-style:;border-left-width:;border-image-source:;border-image-slice:;border-image-width:;border-image-outset:;border-image-repeat:;display:flex;align-items:center;justify-content:center;font-family:"Cormorant Garamond", Georgia, serif;font-weight:700;font-size:19px;color:var(--gold)}
.jayms-tool-alpha .wsdetail-gloss{color:var(--ink-soft);font-size:19px;font-style:italic}
.jayms-tool-alpha .occlist{display:flex;flex-direction:column;row-gap:6px;column-gap:6px;margin-top:20px}
.jayms-tool-alpha .place-media{display:flex;row-gap:16px;column-gap:16px;flex-wrap:wrap;margin-top:16px;margin-right:0px;margin-bottom:16px;margin-left:0px}
.jayms-tool-alpha .place-photo{margin-top:0px;margin-right:0px;margin-bottom:0px;margin-left:0px;flex-grow:1;flex-shrink:1;flex-basis:320px;max-width:520px}
.jayms-tool-alpha .place-photo img{width:100%;height:280px;object-fit:cover;border-top-left-radius:8px;border-top-right-radius:8px;border-bottom-right-radius:8px;border-bottom-left-radius:8px;display:block;border-top-color:;border-top-style:;border-top-width:;border-right-color:;border-right-style:;border-right-width:;border-bottom-color:;border-bottom-style:;border-bottom-width:;border-left-color:;border-left-style:;border-left-width:;border-image-source:;border-image-slice:;border-image-width:;border-image-outset:;border-image-repeat:}
.jayms-tool-alpha .place-photo figcaption{font-size:12px;color:var(--ink-soft);margin-top:6px}
.jayms-tool-alpha .place-photo figcaption a{color:var(--gold)}
.jayms-tool-alpha .place-map{flex-grow:1;flex-shrink:1;flex-basis:320px;max-width:520px;height:280px;border-top-left-radius:8px;border-top-right-radius:8px;border-bottom-right-radius:8px;border-bottom-left-radius:8px;border-top-color:;border-top-style:;border-top-width:;border-right-color:;border-right-style:;border-right-width:;border-bottom-color:;border-bottom-style:;border-bottom-width:;border-left-color:;border-left-style:;border-left-width:;border-image-source:;border-image-slice:;border-image-width:;border-image-outset:;border-image-repeat:;position:relative;z-index:0}
.jayms-tool-alpha .grid.refgrid{grid-template-columns:repeat(auto-fill, minmax(118px, 1fr));row-gap:8px;column-gap:8px;margin-top:16px}
.jayms-tool-alpha .refchip{display:flex;flex-direction:column;align-items:center;row-gap:3px;column-gap:3px;text-align:center;padding-top:9px;padding-right:6px;padding-bottom:9px;padding-left:6px;background-image:;background-position-x:;background-position-y:;background-size:;background-repeat:;background-attachment:;background-origin:;background-clip:;background-color:;border-top-color:;border-top-style:;border-top-width:;border-right-color:;border-right-style:;border-right-width:;border-bottom-color:;border-bottom-style:;border-bottom-width:;border-left-color:;border-left-style:;border-left-width:;border-image-source:;border-image-slice:;border-image-width:;border-image-outset:;border-image-repeat:;border-top-left-radius:6px;border-top-right-radius:6px;border-bottom-right-radius:6px;border-bottom-left-radius:6px;cursor:pointer;font-family:inherit}
.jayms-tool-alpha .refchip:hover{border-top-color:;border-right-color:;border-bottom-color:;border-left-color:}
.jayms-tool-alpha .refchip.static{cursor:default;opacity:0.6}
.jayms-tool-alpha .refchip.range{border-top-style:dashed;border-right-style:dashed;border-bottom-style:dashed;border-left-style:dashed}
.jayms-tool-alpha .refchip.range .refchip-book{color:var(--rust)}
.jayms-tool-alpha .refchip.range .refchip-cv::after{content:"↗";display:inline-block;margin-left:5px;font-size:0.75em;color:var(--rust)}
.jayms-tool-alpha .refchip-book{font-size:11px;text-transform:uppercase;letter-spacing:0.05em;color:var(--ink-soft)}
.jayms-tool-alpha .refchip-cv{font-family:"JetBrains Mono", ui-monospace, Menlo, Consolas, monospace;font-size:14px;color:var(--gold)}
.jayms-tool-alpha .occrow{display:flex;row-gap:14px;column-gap:14px;align-items:flex-start;padding-top:12px;padding-right:14px;padding-bottom:12px;padding-left:14px;background-image:;background-position-x:;background-position-y:;background-size:;background-repeat:;background-attachment:;background-origin:;background-clip:;background-color:;border-top-color:;border-top-style:;border-top-width:;border-right-color:;border-right-style:;border-right-width:;border-bottom-color:;border-bottom-style:;border-bottom-width:;border-left-color:;border-left-style:;border-left-width:;border-image-source:;border-image-slice:;border-image-width:;border-image-outset:;border-image-repeat:;border-top-left-radius:6px;border-top-right-radius:6px;border-bottom-right-radius:6px;border-bottom-left-radius:6px;color:var(--ink);font-family:inherit;font-size:18px;cursor:pointer;text-align:left}
.jayms-tool-alpha .occrow:hover{border-top-color:;border-right-color:;border-bottom-color:;border-left-color:}
.jayms-tool-alpha .occrow:disabled{cursor:default;opacity:0.5}
.jayms-tool-alpha .occrow.static:hover{border-top-color:;border-right-color:;border-bottom-color:;border-left-color:}
.jayms-tool-alpha .occref{flex-grow:0;flex-shrink:0;flex-basis:auto;width:96px;padding-top:3px;color:var(--gold);font-family:"JetBrains Mono", ui-monospace, Menlo, Consolas, monospace;font-size:14px}
.jayms-tool-alpha .occbody{display:flex;flex-direction:column;row-gap:6px;column-gap:6px;flex-grow:1;flex-shrink:1;flex-basis:0%}
.jayms-tool-alpha .occen{color:var(--ink-soft);font-style:italic;font-size:19px;line-height:1.6}
.jayms-tool-alpha .occen.loading{opacity:0.5}
.jayms-tool-alpha .occtext .hl,
.jayms-tool-alpha :root{--tl-blue:#7ea8c4;--genre-purple:#9b7fb0;--genre-mauve:#c589a0;--genre-orange:#c98a4b;--genre-slate:#7d8fa3;--genre-wine:#8a3f4f}
.jayms-tool-alpha .timeline{max-width:640px}
.jayms-tool-alpha .tl-era{margin-bottom:36px}
.jayms-tool-alpha .tl-era-head{display:flex;align-items:baseline;row-gap:14px;column-gap:14px;margin-bottom:16px;border-bottom-width:;border-bottom-style:;border-bottom-color:;padding-bottom:10px}
.jayms-tool-alpha .tl-era-num{font-family:Cormorant, serif;font-weight:700;font-size:22px;color:var(--gold);flex-grow:0;flex-shrink:0;flex-basis:auto;width:28px}
.jayms-tool-alpha .tl-era-name{font-family:Cormorant, serif;font-weight:600;font-size:19px;margin-top:0px;margin-right:0px;margin-bottom:0px;margin-left:0px;color:var(--ink)}
.jayms-tool-alpha .tl-era-dates{color:var(--muted);font-size:13.5px;font-style:italic}
.jayms-tool-alpha .tl-event{border-left-width:;border-left-style:;border-left-color:;padding-top:4px;padding-right:0px;padding-bottom:16px;padding-left:16px;margin-bottom:4px}
.jayms-tool-alpha .tl-event.tl-gold{border-left-color:var(--gold)}
.jayms-tool-alpha .tl-event.tl-red{border-left-color:var(--rust)}
.jayms-tool-alpha .tl-event.tl-blue{border-left-color:var(--tl-blue)}
.jayms-tool-alpha .tl-event.tl-green{border-left-color:var(--aramaic)}
.jayms-tool-alpha .tl-legend{display:flex;flex-wrap:wrap;row-gap:8px;column-gap:10px;margin-top:0px;margin-right:0px;margin-bottom:30px;margin-left:0px;align-items:center;padding-top:12px;padding-right:16px;padding-bottom:12px;padding-left:16px;background-image:;background-position-x:;background-position-y:;background-size:;background-repeat:;background-attachment:;background-origin:;background-clip:;background-color:;border-top-color:;border-top-style:;border-top-width:;border-right-color:;border-right-style:;border-right-width:;border-bottom-color:;border-bottom-style:;border-bottom-width:;border-left-color:;border-left-style:;border-left-width:;border-image-source:;border-image-slice:;border-image-width:;border-image-outset:;border-image-repeat:;border-top-left-radius:6px;border-top-right-radius:6px;border-bottom-right-radius:6px;border-bottom-left-radius:6px}
.jayms-tool-alpha .tl-legend-item{display:inline-flex;align-items:center;row-gap:7px;column-gap:7px;cursor:pointer;background-image:initial;background-position-x:initial;background-position-y:initial;background-size:initial;background-repeat:initial;background-attachment:initial;background-origin:initial;background-clip:initial;background-color:transparent;border-top-width:1px;border-right-width:1px;border-bottom-width:1px;border-left-width:1px;border-top-style:solid;border-right-style:solid;border-bottom-style:solid;border-left-style:solid;border-top-color:transparent;border-right-color:transparent;border-bottom-color:transparent;border-left-color:transparent;border-image-source:none;border-image-slice:100%;border-image-width:1;border-image-outset:0;border-image-repeat:stretch;border-top-left-radius:4px;border-top-right-radius:4px;border-bottom-right-radius:4px;border-bottom-left-radius:4px;padding-top:4px;padding-right:8px;padding-bottom:4px;padding-left:8px;margin-top:0px;margin-right:0px;margin-bottom:0px;margin-left:0px;font-family:"Cormorant Garamond", Georgia, serif;font-size:11px;text-transform:uppercase;letter-spacing:0.05em;color:var(--muted)}
.jayms-tool-alpha .tl-legend-item:hover{border-top-color:;border-right-color:;border-bottom-color:;border-left-color:;color:var(--ink)}
.jayms-tool-alpha .tl-legend-item.active{background-image:;background-position-x:;background-position-y:;background-size:;background-repeat:;background-attachment:;background-origin:;background-clip:;background-color:;border-top-color:currentcolor;border-right-color:currentcolor;border-bottom-color:currentcolor;border-left-color:currentcolor;color:var(--ink)}
.jayms-tool-alpha .tl-legend-item.tl-legend-all{border-left-width:;border-left-style:;border-left-color:;border-top-left-radius:0px;border-top-right-radius:0px;border-bottom-right-radius:0px;border-bottom-left-radius:0px;padding-left:14px;margin-left:4px}
.jayms-tool-alpha .tl-legend-item.tl-legend-all.active{border-top-color:;border-right-color:;border-bottom-color:;border-left-color:;color:var(--gold);border-top-left-radius:4px;border-top-right-radius:4px;border-bottom-right-radius:4px;border-bottom-left-radius:4px;margin-left:0px;padding-left:8px}
.jayms-tool-alpha .tl-legend-dot{width:9px;height:9px;border-top-left-radius:50%;border-top-right-radius:50%;border-bottom-right-radius:50%;border-bottom-left-radius:50%;flex-grow:0;flex-shrink:0;flex-basis:auto}
.jayms-tool-alpha .tl-dot-gold{background-image:;background-position-x:;background-position-y:;background-size:;background-repeat:;background-attachment:;background-origin:;background-clip:;background-color:}
.jayms-tool-alpha .tl-dot-red{background-image:;background-position-x:;background-position-y:;background-size:;background-repeat:;background-attachment:;background-origin:;background-clip:;background-color:}
.jayms-tool-alpha .tl-dot-blue{background-image:;background-position-x:;background-position-y:;background-size:;background-repeat:;background-attachment:;background-origin:;background-clip:;background-color:}
.jayms-tool-alpha .tl-dot-green{background-image:;background-position-x:;background-position-y:;background-size:;background-repeat:;background-attachment:;background-origin:;background-clip:;background-color:}
.jayms-tool-alpha .tl-dot-purple{background-image:;background-position-x:;background-position-y:;background-size:;background-repeat:;background-attachment:;background-origin:;background-clip:;background-color:}
.jayms-tool-alpha .tl-dot-mauve{background-image:;background-position-x:;background-position-y:;background-size:;background-repeat:;background-attachment:;background-origin:;background-clip:;background-color:}
.jayms-tool-alpha .tl-dot-orange{background-image:;background-position-x:;background-position-y:;background-size:;background-repeat:;background-attachment:;background-origin:;background-clip:;background-color:}
.jayms-tool-alpha .tl-dot-slate{background-image:;background-position-x:;background-position-y:;background-size:;background-repeat:;background-attachment:;background-origin:;background-clip:;background-color:}
.jayms-tool-alpha .tl-dot-wine{background-image:;background-position-x:;background-position-y:;background-size:;background-repeat:;background-attachment:;background-origin:;background-clip:;background-color:}
.jayms-tool-alpha .testament-toggle{display:flex;row-gap:8px;column-gap:8px;margin-bottom:16px}
.jayms-tool-alpha .testament-toggle button{background-image:;background-position-x:;background-position-y:;background-size:;background-repeat:;background-attachment:;background-origin:;background-clip:;background-color:;border-top-color:;border-top-style:;border-top-width:;border-right-color:;border-right-style:;border-right-width:;border-bottom-color:;border-bottom-style:;border-bottom-width:;border-left-color:;border-left-style:;border-left-width:;border-image-source:;border-image-slice:;border-image-width:;border-image-outset:;border-image-repeat:;border-top-left-radius:5px;border-top-right-radius:5px;border-bottom-right-radius:5px;border-bottom-left-radius:5px;padding-top:6px;padding-right:14px;padding-bottom:6px;padding-left:14px;font-family:"Cormorant Garamond", Georgia, serif;font-size:11px;text-transform:uppercase;letter-spacing:0.06em;color:var(--muted);cursor:pointer}
.jayms-tool-alpha .testament-toggle button:hover{color:var(--ink);border-top-color:;border-right-color:;border-bottom-color:;border-left-color:}
.jayms-tool-alpha .testament-toggle button.active{background-image:;background-position-x:;background-position-y:;background-size:;background-repeat:;background-attachment:;background-origin:;background-clip:;background-color:;border-top-color:;border-right-color:;border-bottom-color:;border-left-color:;color:var(--gold)}
.jayms-tool-alpha .testament-toggle button:disabled{opacity:0.4;cursor:default;pointer-events:none}
.jayms-tool-alpha .tl-event-title{font-size:17px;color:var(--ink);margin-top:2px;margin-right:0px;margin-bottom:4px;margin-left:0px;font-weight:600}
.jayms-tool-alpha .tl-event-detail{font-size:14.5px;color:var(--ink-soft);line-height:1.55}
.jayms-tool-alpha .tl-ref.live{background-image:none;background-position-x:initial;background-position-y:initial;background-size:initial;background-repeat:initial;background-attachment:initial;background-origin:initial;background-clip:initial;border-top-width:medium;border-right-width:medium;border-left-width:medium;border-top-style:none;border-right-style:none;border-left-style:none;border-top-color:currentcolor;border-right-color:currentcolor;border-left-color:currentcolor;border-image-source:none;border-image-slice:100%;border-image-width:1;border-image-outset:0;border-image-repeat:stretch;cursor:pointer;padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:0px;border-bottom-width:1px;border-bottom-style:dotted;border-bottom-color:rgba(212, 168, 92, 0.55)}

/* the verse number heads a whole block of interlinear, not a line of
   apparatus, and there is a column to put it in */
.jayms-tool-outline .passage-ref{font-size:16px}
/* ====================================================================
   Interleaved Bible only. Everything above is shared by every tool; these
   hang off .jayms-tool-outline because the mount id is the same on all of
   them. One file, one place to look -- the class says which tool, not
   which stylesheet.
   ==================================================================== */

.jayms-tool-outline{
  font-family:"Archivo",system-ui,-apple-system,Arial,sans-serif;
  /* Divine Council starts flush under the header; the outline was 44px lower */
  padding-top:0;
}

/* headings and the standfirst */
.jayms-tool-outline h1{font-size:clamp(25px,4.6vw,40px);line-height:1.14;margin:10px 0 10px;font-weight:600;color:var(--ink)}
.jayms-tool-outline .dek{font-size:19px;line-height:1.6;color:var(--ink-soft);margin:0 0 22px}
.jayms-tool-outline .hero-eyebrow{font-size:12px;letter-spacing:.18em;text-transform:uppercase;color:var(--gold)}
/* the base rule caps this at 70ch; the book blurb is meant to run the
   full width of the outline, same as the era rows under it */
.jayms-tool-outline .hero-sub{font-size:18px;line-height:1.55;color:var(--ink-soft);max-width:none}
.jayms-tool-outline .subsection{font-size:12px;letter-spacing:.14em;text-transform:uppercase;color:var(--muted);margin:22px 0 10px;font-weight:600}
.jayms-tool-outline .subsection .count{color:var(--muted);font-weight:400}

/* crumbs: gold, spaced, their own line */
.jayms-tool-outline .crumb{display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin:0 0 14px;
  font-family:"Archivo",system-ui,-apple-system,"Segoe UI",Arial,sans-serif}
.jayms-tool-outline .crumb button,.jayms-tool-outline .crumb-link{background:transparent;border:0;color:var(--gold);cursor:pointer;
  font-family:inherit;font-size:13px;font-weight:430;letter-spacing:.12em;text-transform:uppercase;
  padding:6px 0;line-height:1.35}
.jayms-tool-outline .crumb button:hover,.jayms-tool-outline .crumb-link:hover{color:var(--ink);text-decoration:underline}
/* the separator is muted and unstyled; only the segments carry the tracking */
.jayms-tool-outline .crumb .path{display:inline-flex;align-items:center;gap:8px;color:var(--muted);
  font-size:13px;font-weight:430;letter-spacing:normal;text-transform:none}
.jayms-tool-outline .crumb .path b{color:var(--ink-soft);font-weight:430}
.jayms-tool-outline .crumb .path button{padding:0}
/* a segment with no button is the page you are already on: Divine Council leaves it off */
.jayms-tool-outline .crumb .path:not(:has(button)){display:none}

/* the verse number heads its own block and there is a full column to put it
   in, so it is read at reading size rather than as fine print */

/* Readability pass, 2026-09-29. Every size and colour here is the Divine
   Council value for the same job: a label is 12px gold at .14em in the sans,
   body copy is 17px in full-strength ink, and nothing that runs to a whole
   sentence is set in italic. The outline had been setting its prose in muted
   italic and its apparatus at 10 and 11px, which is what made it hard work. */

/* era headers: the label reads as a label, the blurb reads as prose */

/* the event blurb is a sentence, not an aside */

/* the passage box label, in the sans like every other label on the page */

/* the English verse: upright, full strength, same size as Divine Council */

/* word cards: the Strong's number and the grammar rows are the reason the
   card exists, so they are readable rather than fine print */
.jayms-tool-outline .words .wcard .strong{font-size:13px;color:var(--ink-soft)}
.jayms-tool-outline .words .wcard .gram{font-size:13px;color:var(--ink-soft)}
.jayms-tool-outline .words .wcard .translit{font-size:14px}
.jayms-tool-outline .words .wcard .gloss{font-size:16px}
.jayms-tool-outline .words .wcard .fullentry-btn{font-size:12px}

/* the full passage, folded into the event it belongs to. Same four pills,
   the same gold-edged box and the same default translation as the scripture
   block in the Divine Council Index, so a verse reads the same way wherever
   it turns up on the site. */
.jayms-tool-outline .tl-pass{margin:0 14px 14px}
.jayms-tool-outline .tl-pass-toggle{background:transparent;border:1px solid var(--line);color:var(--gold);
  border-radius:999px;padding:6px 14px;cursor:pointer;font-family:inherit;font-size:13px;line-height:1.35}
.jayms-tool-outline .tl-pass-toggle:hover{border-color:var(--gold)}
.jayms-tool-outline .tl-pass-toggle::after{content:" \25be"}
.jayms-tool-outline .tl-pass.open > .tl-pass-toggle::after{content:" \25b4"}
.jayms-tool-outline .tl-pass-body{margin-top:11px}
.jayms-tool-outline .tl-vtext p{margin:0}
.jayms-tool-outline .tl-vtext sup{color:var(--muted);font-size:11px;padding-right:2px}
.jayms-tool-outline .tl-loading{color:var(--muted);font-style:italic}

/* the book grid reads as Divine Council's cards */
.jayms-tool-outline .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(290px,1fr));gap:13px}
.jayms-tool-outline .tile{display:flex;flex-direction:column;gap:9px;text-align:left;
  background:var(--paper-deep);border:1px solid var(--line);border-left:3px solid var(--gold);
  border-radius:9px;padding:16px;cursor:pointer;font-family:inherit;color:var(--ink);font-size:16px;line-height:1.45}
.jayms-tool-outline .tile:hover{border-color:var(--gold)}
.jayms-tool-outline .tile.disabled{opacity:.4;cursor:default;border-left-color:var(--line)}
.jayms-tool-outline .tile .genre-tag{font-size:10.5px;letter-spacing:.11em;text-transform:uppercase;
  border:1px solid var(--line);border-radius:3px;padding:3px 7px;align-self:flex-start}
.jayms-tool-outline .tile .sub{font-size:13px;color:var(--muted);line-height:1.5}

/* an era is a heading, not a box; the events inside it are the boxes */
.jayms-tool-outline .era-section{margin:0 0 26px}
.jayms-tool-outline .era-header{display:flex;align-items:baseline;gap:12px;margin:0 0 12px}
.jayms-tool-outline .era-divider{flex:1;height:1px;background:var(--line)}
.jayms-tool-outline .events-list{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:13px}

/* every event is a Divine Council box, colour-coded the same way */
.jayms-tool-outline .event-item.major{--bc:var(--rust)}
.jayms-tool-outline .event-item.poetry{--bc:var(--blue)}
.jayms-tool-outline .event-item.prophet{--bc:var(--aramaic)}
.jayms-tool-outline .event-header{display:flex;flex-wrap:wrap;align-items:baseline;gap:10px;margin:0 0 8px}

/* filter rows become chips */
.jayms-tool-outline .tl-legend,.jayms-tool-outline .testament-toggle{display:flex;flex-wrap:wrap;gap:7px;
  background:var(--paper-deep);border:1px solid var(--line);border-radius:9px;padding:11px;margin:0 0 11px}
.jayms-tool-outline .tl-legend-item,.jayms-tool-outline .testament-toggle button{display:inline-flex;align-items:center;gap:7px;
  padding:7px 13px;border-radius:999px;background:transparent;border:1px solid transparent;
  color:var(--ink-soft);font-size:14px;cursor:pointer;font-family:inherit;line-height:1.3}
.jayms-tool-outline .tl-legend-item:hover,.jayms-tool-outline .testament-toggle button:hover{color:var(--ink)}
.jayms-tool-outline .tl-legend-item.active,.jayms-tool-outline .testament-toggle button.active{border-color:var(--gold);color:var(--ink)}
.jayms-tool-outline .tl-legend-dot{width:9px;height:9px;border-radius:50%;flex:0 0 auto}

/* buttons read as the pills on the other tool */
.jayms-tool-outline .fullentry-btn{display:inline-block;background:transparent;border:1px solid var(--line);
  color:var(--gold);border-radius:999px;padding:6px 14px;cursor:pointer;font-family:inherit;
  font-size:13px;line-height:1.35;text-decoration:none}
.jayms-tool-outline .fullentry-btn:hover{border-color:var(--gold)}
/* Inside a word card the pill has to fit a narrow column. "Word Study" was
   wrapping to two lines, which made that card taller than its neighbours and
   threw the whole row out of line. Smaller type, no wrapping, fixed height. */
.jayms-tool-outline .wcard-links{display:flex;flex-wrap:wrap;gap:6px;align-items:flex-start}
.jayms-tool-outline .wcard-links .fullentry-btn{flex:1 1 0;min-width:0;justify-content:center;
  font-size:10px;letter-spacing:.04em;text-transform:uppercase;
  padding:5px 6px;white-space:nowrap;line-height:1.2;min-height:26px;display:inline-flex;
  align-items:center}

/* a verse is a box, its reference is the box label */
.jayms-tool-outline .passage-verse{background:var(--paper-deep);border:1px solid var(--line);
  border-left:3px solid var(--gold);border-radius:9px;padding:15px 18px;margin:0 0 13px}
.jayms-tool-outline .panel-en.loading{color:var(--muted);font-style:italic}
.jayms-tool-outline .wcard{background:var(--paper-deeper);border:1px solid var(--line);border-radius:7px}
.jayms-tool-outline .attrib{font-size:13px;line-height:1.7;color:var(--muted);
  border-top:1px solid var(--line);padding-top:13px;margin-top:18px}

/* the server-rendered fallback is for crawlers, never for readers */
.jayms-tool-outline .jayms-outline-seo{display:none}

@media(max-width:640px){
  .jayms-tool-outline .grid{grid-template-columns:1fr}
  .jayms-tool-outline .event-item,.jayms-tool-outline .passage-verse{padding:13px 15px}
  .jayms-tool-outline .event-detail,.jayms-tool-outline .panel-en{font-size:16px}
}

</style>
	<?php
}, 8 );
