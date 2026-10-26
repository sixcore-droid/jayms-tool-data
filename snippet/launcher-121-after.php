if ( ! function_exists( 'jayms_tool_icon_alt' ) ) {
	function jayms_tool_icon_alt( $page_id ) {
		$icon = (int) get_post_meta( $page_id, 'jayms_menu_icon_id', true );
		$alt  = $icon ? trim( (string) get_post_meta( $icon, '_wp_attachment_image_alt', true ) ) : '';
		if ( '' !== $alt ) { return $alt; }
		$label = trim( (string) get_post_meta( $page_id, 'jayms_menu_label', true ) );
		if ( '' === $label ) { $label = get_the_title( $page_id ); }
		return $label ? $label . ' tool icon' : '';
	}
}

/**
 * JAYMS -- Floating tools launcher + tool registry (single source of truth)
 *
 * "Think of it as a start menu on Windows." Persistent, site-wide floating
 * button (top-right) that opens a panel listing every tool, grouped, each
 * row showing that page's own icon. Favorites are per-browser (localStorage)
 * and pinned to the top when any exist.
 *
 * Every tool page carries its own membership via ONE meta box ("Tools
 * Menu", Page edit screen sidebar): a Category (which group it belongs to,
 * on this menu AND on the Bible Study Tools page), a Label override, and
 * an Icon override (falls back to the page's own Featured Image if unset).
 * jayms_launcher_groups() below reads that live, sorted by each page's own
 * menu_order -- there is no hand-kept list anywhere anymore. Adding a tool
 * means: set its Category (and menu_order if you care about its position
 * within that category) on its own page. Nothing else to update, and
 * nothing to keep in sync by hand -- this function is the one place both
 * the launcher and the Bible Study Tools page (snippet: JAYMS -- Bible
 * Study Tools page renderer) read from.
 */

function jayms_tool_categories() {
	// The groups are terms now (Pages > Tool groups), supplied by the snippet
	// "JAYMS — Tool groups". The four below only run if that snippet is off.
	if ( function_exists( 'jayms_tool_groups' ) ) {
		return jayms_tool_groups();
	}
	return array(
		'timeline'       => array( 'label' => 'Timelines', 'icon' => 'clock' ),
		'command-center' => array( 'label' => 'Command Centers', 'icon' => 'terminal' ),
		'search-lookup'  => array( 'label' => 'Search and Lookup', 'icon' => 'search' ),
		'printable'      => array( 'label' => 'Printables', 'icon' => 'document' ),
	);
}

function jayms_tool_icon_url( $id ) {
	$icon_id = (int) get_post_meta( $id, 'jayms_menu_icon_id', true );
	if ( $icon_id ) {
		$url = wp_get_attachment_image_url( $icon_id, 'thumbnail' );
		if ( $url ) {
			return $url;
		}
	}
	return get_the_post_thumbnail_url( $id, 'thumbnail' );
}

function jayms_launcher_groups() {
	$cats  = jayms_tool_categories();
	$order = array_keys( $cats );

	$posts = get_posts( array(
		'post_type'      => 'page',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'meta_key'       => 'jayms_tool_category',
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	) );

	$grouped = array();
	foreach ( $posts as $p ) {
		$cat = get_post_meta( $p->ID, 'jayms_tool_category', true );
		if ( ! isset( $cats[ $cat ] ) ) {
			continue;
		}
		$grouped[ $cat ][] = $p;
	}

	$groups = array();
	foreach ( $order as $slug ) {
		if ( empty( $grouped[ $slug ] ) ) {
			continue;
		}
		$items = array();
		foreach ( $grouped[ $slug ] as $p ) {
			$items[] = array( $p->post_title, wp_make_link_relative( get_permalink( $p->ID ) ), $p->ID );
		}
		$groups[] = array( 'label' => $cats[ $slug ]['label'], 'icon' => $cats[ $slug ]['icon'], 'items' => $items );
	}
	return $groups;
}

/**
 * Tools Menu -- one meta box on the Page edit screen: Category (drives
 * both this launcher and the Bible Study Tools page), Label override, and
 * Icon override. Shown on every child of the Bible Study Tools page
 * (91449), plus any page that already has a category set (so it never
 * disappears from a page that's using it).
 */
add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen || 'page' !== $screen->post_type ) {
		return;
	}
	wp_enqueue_media();
} );

add_action( 'add_meta_boxes_page', function ( $post ) {
	$eligible = ( 91449 === (int) $post->post_parent ) || get_post_meta( $post->ID, 'jayms_tool_category', true );
	if ( ! $eligible ) {
		return;
	}
	add_meta_box(
		'jayms_launcher_label',
		'Tools Menu',
		function ( $post ) {
			wp_nonce_field( 'jayms_launcher_label_save', 'jayms_launcher_label_nonce' );
			$val     = get_post_meta( $post->ID, 'jayms_menu_label', true );
			$iconId  = (int) get_post_meta( $post->ID, 'jayms_menu_icon_id', true );
			$iconUrl = $iconId ? wp_get_attachment_image_url( $iconId, 'thumbnail' ) : '';
			$catVal  = get_post_meta( $post->ID, 'jayms_tool_category', true );

			echo '<p style="margin-top:0"><b>Category</b> -- which group this tool appears in, here and on the Bible Study Tools page. Leave on &ldquo;Not a tool&rdquo; to keep it out of both.</p>';
			echo '<select name="jayms_tool_category" style="width:100%;margin-bottom:14px">';
			echo '<option value="">&mdash; Not a tool &mdash;</option>';
			foreach ( jayms_tool_categories() as $slug => $info ) {
				echo '<option value="' . esc_attr( $slug ) . '"' . selected( $catVal, $slug, false ) . '>' . esc_html( $info['label'] ) . '</option>';
			}
			echo '</select>';

			$featured = (bool) get_post_meta( $post->ID, 'jayms_tool_featured', true );
			echo '<label style="display:block;margin-bottom:14px"><input type="checkbox" name="jayms_tool_featured" value="1"' . checked( $featured, true, false ) . '> <b>Featured</b> -- show this tool in the homepage\'s Featured Tools strip.</label>';

			echo '<p style="margin:0 0 6px"><b>Name</b> -- shown for this page in the floating tools menu and on the Bible Study Tools page. Leave blank to use the page title.</p>';
			echo '<input type="text" name="jayms_menu_label" value="' . esc_attr( $val ) . '" style="width:100%;box-sizing:border-box;margin-bottom:14px" placeholder="Default name">';

			echo '<p style="margin:0 0 6px"><b>Icon</b> -- shown everywhere this tool appears. Change it here and it updates in every menu and the Bible Study Tools page at once. Falls back to the Featured Image if none is set.</p>';
			echo '<div id="jayms-icon-preview" style="width:60px;height:60px;border-radius:8px;background:var(--paper-deep);border:1px solid var(--line);display:flex;align-items:center;justify-content:center;overflow:hidden;margin-bottom:8px;">';
			echo $iconUrl ? '<img src="' . esc_url( $iconUrl ) . '" style="width:100%;height:100%;object-fit:contain">' : '<span style="color:var(--muted);font-size:10px">No icon</span>';
			echo '</div>';
			echo '<input type="hidden" id="jayms_menu_icon_id" name="jayms_menu_icon_id" value="' . esc_attr( $iconId ) . '">';
			echo '<button type="button" class="button" id="jayms-icon-pick">Choose Icon</button> ';
			echo '<button type="button" class="button" id="jayms-icon-clear"' . ( $iconId ? '' : ' style="display:none"' ) . '>Remove</button>';
			?>
<script>
(function($){
  var frame;
  $('#jayms-icon-pick').on('click', function(e){
    e.preventDefault();
    if (frame) { frame.open(); return; }
    frame = wp.media({ title: 'Choose Menu Icon', button: { text: 'Use this icon' }, multiple: false });
    frame.on('select', function(){
      var att = frame.state().get('selection').first().toJSON();
      var url = (att.sizes && att.sizes.thumbnail) ? att.sizes.thumbnail.url : att.url;
      $('#jayms_menu_icon_id').val(att.id);
      $('#jayms-icon-preview').html('<img src="' + url + '" style="width:100%;height:100%;object-fit:contain">');
      $('#jayms-icon-clear').show();
    });
    frame.open();
  });
  $('#jayms-icon-clear').on('click', function(e){
    e.preventDefault();
    $('#jayms_menu_icon_id').val('');
    $('#jayms-icon-preview').html('<span style="color:var(--muted);font-size:10px">No icon</span>');
    $(this).hide();
  });
})(jQuery);
</script>
			<?php
		},
		'page',
		'side',
		'default'
	);
} );

add_action( 'save_post_page', function ( $post_id ) {
	if ( ! isset( $_POST['jayms_launcher_label_nonce'] ) || ! wp_verify_nonce( $_POST['jayms_launcher_label_nonce'], 'jayms_launcher_label_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_page', $post_id ) ) {
		return;
	}
	if ( isset( $_POST['jayms_menu_label'] ) ) {
		update_post_meta( $post_id, 'jayms_menu_label', sanitize_text_field( $_POST['jayms_menu_label'] ) );
	}
	if ( isset( $_POST['jayms_menu_icon_id'] ) ) {
		update_post_meta( $post_id, 'jayms_menu_icon_id', absint( $_POST['jayms_menu_icon_id'] ) );
	}
	update_post_meta( $post_id, 'jayms_tool_featured', ! empty( $_POST['jayms_tool_featured'] ) ? 1 : 0 );
	if ( isset( $_POST['jayms_tool_category'] ) ) {
		$cats = jayms_tool_categories();
		$val  = sanitize_key( $_POST['jayms_tool_category'] );
		if ( isset( $cats[ $val ] ) ) {
			update_post_meta( $post_id, 'jayms_tool_category', $val );
		} else {
			delete_post_meta( $post_id, 'jayms_tool_category' );
		}
	}
} );

add_action( 'init', function () {
	register_post_meta( 'page', 'jayms_tool_category', array(
		'type'          => 'string',
		'single'        => true,
		'show_in_rest'  => true,
		'auth_callback' => function () { return current_user_can( 'edit_posts' ); },
	) );
	register_post_meta( 'page', 'jayms_menu_icon_id', array(
		'type'          => 'integer',
		'single'        => true,
		'show_in_rest'  => true,
		'auth_callback' => function () { return current_user_can( 'edit_posts' ); },
	) );
	register_post_meta( 'page', 'jayms_menu_label', array(
		'type'          => 'string',
		'single'        => true,
		'show_in_rest'  => true,
		'auth_callback' => function () { return current_user_can( 'edit_posts' ); },
	) );
	register_post_meta( 'page', 'jayms_tool_featured', array(
		'type'          => 'boolean',
		'single'        => true,
		'show_in_rest'  => true,
		'auth_callback' => function () { return current_user_can( 'edit_posts' ); },
	) );
}, 20 );

add_action( 'wp_footer', function () {
	$groups = jayms_launcher_groups();

	$icons = array(
		'clock'    => '<svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 .001 20.001A10 10 0 0 0 12 2zm0 18a8 8 0 1 1 .001-16.001A8 8 0 0 1 12 20zm.5-13H11v6l5.2 3.1.8-1.3-4.5-2.7V7z"/></svg>',
		'terminal' => '<svg viewBox="0 0 24 24"><path d="M3 4h18a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1zm1 2v12h16V6H4zm2.7 2.3 3.6 3.2-3.6 3.2-1.3-1.5 2-1.7-2-1.7 1.3-1.5zM12 14h6v2h-6v-2z"/></svg>',
		'search'   => '<svg viewBox="0 0 24 24"><path d="M10 2a8 8 0 1 0 4.9 14.3l5.4 5.4 1.4-1.4-5.4-5.4A8 8 0 0 0 10 2zm0 2a6 6 0 1 1 0 12 6 6 0 0 1 0-12z"/></svg>',
		'document' => '<svg viewBox="0 0 24 24"><path d="M6 2h9l5 5v13a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2zm8 1.5V8h4.5L14 3.5zM8 13h8v1.5H8V13zm0 3h8v1.5H8V16zm0-6h4v1.5H8V10z"/></svg>',
	);

	$star = '<svg viewBox="0 0 24 24"><path d="M12 2l2.9 6.6 7.1.6-5.4 4.7 1.7 7-6.3-3.9L5.7 21l1.7-7-5.4-4.7 7.1-.6L12 2z"/></svg>';

	?>
<style id="jayms-launcher-css">
.jayms-launcher-wrap{position:fixed;top:60px;right:18px;z-index:60;font-family:"Archivo",Arial,sans-serif}
.jayms-launcher-btn{width:42px;height:42px;display:inline-flex;align-items:center;justify-content:center;background:var(--paper-deep);color:var(--ink-soft);border:1px solid var(--line);border-radius:50%;cursor:pointer;box-shadow:0 4px 14px rgba(0,0,0,.45);box-sizing:border-box}
.jayms-launcher-btn:hover,.jayms-launcher-wrap.open .jayms-launcher-btn{background:var(--paper-deeper);color:var(--gold);border-color:var(--gold)}
.jayms-launcher-btn svg{width:18px;height:18px;fill:currentColor}
.jayms-launcher-panel{display:none;position:absolute;top:50px;right:0;width:320px;max-width:calc(100vw - 36px);max-height:70vh;overflow-y:auto;background:var(--paper-deep);border:1px solid var(--line);border-radius:8px;box-shadow:0 10px 30px rgba(0,0,0,.5);padding:8px 0}
.jayms-launcher-wrap.open .jayms-launcher-panel{display:block}
.jayms-launcher-group-label{display:flex;align-items:center;gap:8px;padding:10px 14px 6px;font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:var(--gold)}
.jayms-launcher-group-label svg{width:13px;height:13px;fill:currentColor;flex:0 0 auto}
.jayms-launcher-row{display:flex;align-items:center}
.jayms-launcher-item{display:flex;align-items:center;gap:10px;flex:1;min-width:0;padding:6px 4px 6px 14px;font-size:14px;line-height:1.4;color:var(--ink-soft);text-decoration:none}
.jayms-launcher-item img{width:24px;height:24px;border-radius:5px;object-fit:contain;flex:0 0 auto;background:var(--paper)}
.jayms-launcher-item span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.jayms-launcher-row:hover .jayms-launcher-item{color:var(--ink);background:var(--paper)}
.jayms-launcher-item.current{color:var(--gold);font-weight:700}
.jayms-launcher-fav{background:none;border:none;color:#4a463d;cursor:pointer;padding:6px 12px 6px 6px;flex:0 0 auto;line-height:0}
.jayms-launcher-fav svg{width:15px;height:15px;fill:currentColor}
.jayms-launcher-fav:hover{color:var(--muted)}
.jayms-launcher-fav.active{color:var(--gold)}
.jayms-launcher-divider{height:1px;background:var(--line);margin:8px 0}
.jayms-launcher-favorites{display:none}
.jayms-launcher-favorites.has-items{display:block}
@media(max-width:700px){.jayms-launcher-wrap{top:52px;right:14px}.jayms-launcher-btn{width:38px;height:38px}}
</style>
<div class="jayms-launcher-wrap" id="jayms-launcher">
  <button class="jayms-launcher-btn" id="jayms-launcher-toggle" aria-label="All tools" aria-expanded="false">
    <svg viewBox="0 0 24 24"><path d="M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 0h6v6h-6v-6z"/></svg>
  </button>
  <div class="jayms-launcher-panel" id="jayms-launcher-panel">
    <div class="jayms-launcher-favorites" id="jayms-launcher-favorites">
      <div class="jayms-launcher-group-label"><?php echo $star; ?><span>Favorites</span></div>
      <div class="jayms-launcher-fav-body"></div>
      <div class="jayms-launcher-divider"></div>
    </div>
<?php foreach ( $groups as $gi => $group ) : ?>
	<?php if ( $gi > 0 ) : ?><div class="jayms-launcher-divider"></div><?php endif; ?>
    <div class="jayms-launcher-group-label"><?php echo $icons[ $group['icon'] ]; ?><span><?php echo esc_html( $group['label'] ); ?></span></div>
<?php
foreach ( $group['items'] as $item ) :
	$id      = $item[2];
	$label   = get_post_meta( $id, 'jayms_menu_label', true );
	$label   = $label ? $label : $item[0];
	$url     = home_url( $item[1] );
	$thumb   = jayms_tool_icon_url( $id );
	?>
    <div class="jayms-launcher-row" data-id="<?php echo (int) $id; ?>">
      <a class="jayms-launcher-item" href="<?php echo esc_url( $url ); ?>" data-path="<?php echo esc_attr( $item[1] ); ?>">
<?php if ( $thumb ) : ?>
        <img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( jayms_tool_icon_alt( $id ) ); ?>" loading="lazy">
<?php endif; ?>
        <span><?php echo esc_html( $label ); ?></span>
      </a>
      <button class="jayms-launcher-fav" data-id="<?php echo (int) $id; ?>" aria-label="Toggle favorite"><?php echo $star; ?></button>
    </div>
<?php endforeach; ?>
<?php endforeach; ?>
  </div>
</div>
<script>
(function(){
  var wrap = document.getElementById('jayms-launcher');
  var btn = document.getElementById('jayms-launcher-toggle');
  var panel = document.getElementById('jayms-launcher-panel');
  var favSection = document.getElementById('jayms-launcher-favorites');
  var favBody = favSection.querySelector('.jayms-launcher-fav-body');
  var FAV_KEY = 'jaymsToolFavorites';

  btn.addEventListener('click', function(e){
    e.stopPropagation();
    var open = wrap.classList.toggle('open');
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
  });
  document.addEventListener('click', function(e){
    if (!wrap.contains(e.target)) { wrap.classList.remove('open'); btn.setAttribute('aria-expanded','false'); }
  });
  document.addEventListener('keydown', function(e){
    if (e.key === 'Escape') { wrap.classList.remove('open'); btn.setAttribute('aria-expanded','false'); }
  });

  var here = location.pathname.replace(/\/?$/, '/');
  panel.querySelectorAll('.jayms-launcher-item').forEach(function(a){
    var p = a.getAttribute('data-path').replace(/\/?$/, '/');
    if (decodeURIComponent(p) === decodeURIComponent(here)) a.classList.add('current');
  });

  function getFavs(){ try { return JSON.parse(localStorage.getItem(FAV_KEY) || '[]'); } catch(e){ return []; } }
  function setFavs(arr){ try { localStorage.setItem(FAV_KEY, JSON.stringify(arr)); } catch(e){} }

  function renderFavorites(){
    var favs = getFavs();
    favBody.innerHTML = '';
    panel.querySelectorAll('.jayms-launcher-fav').forEach(function(b){
      b.classList.toggle('active', favs.indexOf(String(b.getAttribute('data-id'))) !== -1);
    });
    if (!favs.length) { favSection.classList.remove('has-items'); return; }
    favSection.classList.add('has-items');
    favs.forEach(function(id){
      var original = panel.querySelector('.jayms-launcher-row[data-id="' + id + '"]');
      if (original && !favSection.contains(original)) favBody.appendChild(original.cloneNode(true));
    });
  }

  panel.addEventListener('click', function(e){
    var b = e.target.closest('.jayms-launcher-fav');
    if (!b) return;
    e.preventDefault();
    var id = String(b.getAttribute('data-id'));
    var favs = getFavs();
    var idx = favs.indexOf(id);
    if (idx === -1) favs.push(id); else favs.splice(idx, 1);
    setFavs(favs);
    renderFavorites();
  });

  renderFavorites();
})();
</script>
	<?php
}, 25 );
