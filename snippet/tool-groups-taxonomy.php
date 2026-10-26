/**
 * JAYMS — Tool groups
 *
 * The groups the Bible Study Tools are filed under used to be typed into the
 * code: four keys and four names in the launcher snippet, again in the menu
 * snippet, again in the hub shortcodes. Renaming a group, adding one, or
 * moving one up the list all meant editing a snippet.
 *
 * Groups are terms now. Pages -> Tool groups in wp-admin: add one, rename
 * one, change where it sits in the running order, pick its launcher icon,
 * delete one. Nothing in any snippet changes for any of that.
 *
 * A tool still joins a group the way it always has: the Category dropdown in
 * the "Tools Menu" box on its own page, which stores the group's slug in
 * jayms_tool_category. That dropdown now lists whatever groups exist.
 *
 * Until the first group is created every list below falls back to the four
 * original groups, so installing this changes nothing by itself.
 *
 * What reads this:
 *   jayms_tool_groups()          slug => [label, icon], in running order
 *   jayms_tools_menu_groups()    slug => label, the nav submenu's list. The
 *                                menu snippet defines its own only if this
 *                                one is not here, so this file takes over.
 *   [jayms_tool_sections]        every group with tools in it, heading and
 *                                card grid, for the Bible Study Tools page
 */

defined( 'JAYMS_TG_TAX' )   || define( 'JAYMS_TG_TAX', 'jayms_tool_group' );
defined( 'JAYMS_TG_CACHE' ) || define( 'JAYMS_TG_CACHE', 'jayms_tool_groups_v2' );

/* ----------------------------------------------------------- the original four */

if ( ! function_exists( 'jayms_tool_groups_default' ) ) {
	function jayms_tool_groups_default() {
		return array(
			'timeline'       => array( 'label' => 'Timelines',         'icon' => 'clock' ),
			'command-center' => array( 'label' => 'Command Centers',   'icon' => 'terminal' ),
			'search-lookup'  => array( 'label' => 'Search and Lookup', 'icon' => 'search' ),
			'printable'      => array( 'label' => 'Printables',        'icon' => 'document' ),
		);
	}
}

/** The launcher draws one of these four. A fifth means a change to the launcher. */
if ( ! function_exists( 'jayms_tool_group_icons' ) ) {
	function jayms_tool_group_icons() {
		return array(
			'clock'    => 'Clock',
			'terminal' => 'Terminal',
			'search'   => 'Magnifier',
			'document' => 'Document',
		);
	}
}

/* ------------------------------------------------------------------ the taxonomy */

add_action( 'init', function () {
	register_taxonomy( JAYMS_TG_TAX, array( 'page' ), array(
		'labels' => array(
			'name'          => 'Tool groups',
			'singular_name' => 'Tool group',
			'menu_name'     => 'Tool groups',
			'add_new_item'  => 'Add a tool group',
			'edit_item'     => 'Edit tool group',
			'search_items'  => 'Search tool groups',
			'not_found'     => 'No tool groups yet. The original four are in use until you add one.',
		),
		'hierarchical'       => false,
		'public'             => false,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_nav_menus'  => false,
		'show_in_rest'       => true,
		'show_admin_column'  => false,
		'show_in_quick_edit' => false,
		// a tool picks its group from the Category dropdown in its own Tools
		// Menu box, so no second picker on the page edit screen
		'meta_box_cb'        => false,
		'rewrite'            => false,
		'capabilities'       => array(
			'manage_terms' => 'edit_pages',
			'edit_terms'   => 'edit_pages',
			'delete_terms' => 'edit_pages',
			'assign_terms' => 'edit_pages',
		),
	) );

	register_term_meta( JAYMS_TG_TAX, 'jayms_group_order', array(
		'type'          => 'integer',
		'single'        => true,
		'default'       => 0,
		'show_in_rest'  => true,
		'auth_callback' => function () { return current_user_can( 'edit_pages' ); },
	) );
	register_term_meta( JAYMS_TG_TAX, 'jayms_group_icon', array(
		'type'          => 'string',
		'single'        => true,
		'default'       => 'document',
		'show_in_rest'  => true,
		'auth_callback' => function () { return current_user_can( 'edit_pages' ); },
	) );
} );

/* --------------------------------------------- Order and Icon on the term screens */

if ( ! function_exists( 'jayms_tg_fields' ) ) {
	function jayms_tg_fields( $order, $icon, $row ) {
		$sel = '<select name="jayms_group_icon" id="jayms_group_icon">';
		foreach ( jayms_tool_group_icons() as $k => $label ) {
			$sel .= '<option value="' . esc_attr( $k ) . '"' . ( $k === $icon ? ' selected' : '' ) . '>' . esc_html( $label ) . '</option>';
		}
		$sel .= '</select>';
		$o = '<input type="number" name="jayms_group_order" id="jayms_group_order" value="' . esc_attr( $order ) . '" step="1">';
		$hint_o = 'Lower numbers come first in the menu, the launcher and on the hub page. Leave gaps (10, 20, 30) so a group can be slotted in between later.';
		$hint_i = 'The symbol the floating launcher shows beside this group.';
		if ( $row ) {
			return '<tr class="form-field"><th><label for="jayms_group_order">Order</label></th><td>' . $o .
			       '<p class="description">' . esc_html( $hint_o ) . '</p></td></tr>' .
			       '<tr class="form-field"><th><label for="jayms_group_icon">Launcher icon</label></th><td>' . $sel .
			       '<p class="description">' . esc_html( $hint_i ) . '</p></td></tr>';
		}
		return '<div class="form-field"><label for="jayms_group_order">Order</label>' . $o .
		       '<p>' . esc_html( $hint_o ) . '</p></div>' .
		       '<div class="form-field"><label for="jayms_group_icon">Launcher icon</label>' . $sel .
		       '<p>' . esc_html( $hint_i ) . '</p></div>';
	}
}

add_action( JAYMS_TG_TAX . '_add_form_fields', function () {
	echo jayms_tg_fields( 0, 'document', false );
} );
add_action( JAYMS_TG_TAX . '_edit_form_fields', function ( $term ) {
	echo jayms_tg_fields(
		(int) get_term_meta( $term->term_id, 'jayms_group_order', true ),
		(string) get_term_meta( $term->term_id, 'jayms_group_icon', true ),
		true
	);
} );

if ( ! function_exists( 'jayms_tg_save_fields' ) ) {
	function jayms_tg_save_fields( $term_id ) {
		if ( ! current_user_can( 'edit_pages' ) ) { return; }
		if ( isset( $_POST['jayms_group_order'] ) ) {
			update_term_meta( $term_id, 'jayms_group_order', (int) $_POST['jayms_group_order'] );
		}
		if ( isset( $_POST['jayms_group_icon'] ) ) {
			$icon = sanitize_key( $_POST['jayms_group_icon'] );
			if ( isset( jayms_tool_group_icons()[ $icon ] ) ) {
				update_term_meta( $term_id, 'jayms_group_icon', $icon );
			}
		}
	}
}
add_action( 'created_' . JAYMS_TG_TAX, 'jayms_tg_save_fields' );
add_action( 'edited_'  . JAYMS_TG_TAX, 'jayms_tg_save_fields' );

add_filter( 'manage_edit-' . JAYMS_TG_TAX . '_columns', function ( $cols ) {
	unset( $cols['posts'] );
	$cols['jayms_group_order'] = 'Order';
	$cols['jayms_group_tools'] = 'Tools in it';
	return $cols;
} );
add_filter( 'manage_' . JAYMS_TG_TAX . '_custom_column', function ( $out, $col, $term_id ) {
	if ( 'jayms_group_order' === $col ) {
		return (string) (int) get_term_meta( $term_id, 'jayms_group_order', true );
	}
	if ( 'jayms_group_tools' === $col ) {
		$t = get_term( $term_id, JAYMS_TG_TAX );
		if ( ! $t || is_wp_error( $t ) ) { return '0'; }
		$ids = get_posts( array(
			'post_type' => 'page', 'post_status' => 'publish', 'posts_per_page' => -1,
			'fields' => 'ids', 'no_found_rows' => true,
			'meta_key' => 'jayms_tool_category', 'meta_value' => $t->slug,
		) );
		return (string) count( $ids );
	}
	return $out;
}, 10, 3 );

/* ------------------------------------------------------------------ what others read */

if ( ! function_exists( 'jayms_tool_groups' ) ) {
	/** slug => [ label, icon ] in running order. The original four if none exist yet. */
	function jayms_tool_groups() {
		$cached = get_transient( JAYMS_TG_CACHE );
		if ( is_array( $cached ) ) { return $cached; }

		$out   = array();
		$terms = get_terms( array( 'taxonomy' => JAYMS_TG_TAX, 'hide_empty' => false ) );
		if ( ! is_wp_error( $terms ) && $terms ) {
			usort( $terms, function ( $a, $b ) {
				$x = (int) get_term_meta( $a->term_id, 'jayms_group_order', true );
				$y = (int) get_term_meta( $b->term_id, 'jayms_group_order', true );
				return $x === $y ? strcasecmp( $a->name, $b->name ) : $x - $y;
			} );
			$icons = jayms_tool_group_icons();
			foreach ( $terms as $t ) {
				$icon = (string) get_term_meta( $t->term_id, 'jayms_group_icon', true );
				$out[ $t->slug ] = array(
					'label' => $t->name,
					'icon'  => isset( $icons[ $icon ] ) ? $icon : 'document',
				);
			}
		}
		if ( ! $out ) { $out = jayms_tool_groups_default(); }

		set_transient( JAYMS_TG_CACHE, $out, DAY_IN_SECONDS );
		return $out;
	}
}

/**
 * The nav submenu's list. The menu snippet only defines its own copy when this
 * one does not exist, and this file loads first (priority 5), so this wins.
 */
if ( ! function_exists( 'jayms_tools_menu_groups' ) ) {
	function jayms_tools_menu_groups() {
		$out = array();
		foreach ( jayms_tool_groups() as $slug => $g ) { $out[ $slug ] = $g['label']; }
		return $out;
	}
}

/* ----------------------------------------------------- the hub page builds itself */

if ( ! function_exists( 'jayms_tool_sections_shortcode' ) ) {
	/**
	 * [jayms_tool_sections] -- one numbered heading and one card grid for every
	 * group that has a tool in it, in running order. Adding a group, renaming
	 * one or reordering them changes this page with no edit to it.
	 */
	function jayms_tool_sections_shortcode() {
		$out = '';
		$n   = 0;
		foreach ( jayms_tool_groups() as $slug => $g ) {
			$grid = do_shortcode( '[jayms_tools category="' . esc_attr( $slug ) . '"]' );
			if ( '' === trim( $grid ) || 0 === strpos( ltrim( $grid ), '<!--' ) ) { continue; }
			$count = do_shortcode( '[jayms_tools_count category="' . esc_attr( $slug ) . '"]' );
			$n++;
			$out .= '<h2 id="' . esc_attr( sanitize_title( $g['label'] ) ) . '" class="wp-block-heading bst-section">' .
			        '<em>' . sprintf( '%02d', $n ) . '</em>' . esc_html( $g['label'] ) .
			        ' <span>' . esc_html( $count ) . '</span></h2>' . $grid;
		}
		return $out;
	}
}
add_shortcode( 'jayms_tool_sections', 'jayms_tool_sections_shortcode' );

/* ------------------------------------- renaming a slug must not orphan its tools */

add_action( 'edit_terms', function ( $term_id, $taxonomy ) {
	if ( JAYMS_TG_TAX !== $taxonomy ) { return; }
	$t = get_term( $term_id, $taxonomy );
	if ( $t && ! is_wp_error( $t ) ) {
		$GLOBALS['jayms_tg_old_slug'][ $term_id ] = $t->slug;
	}
}, 10, 2 );

add_action( 'edited_' . JAYMS_TG_TAX, function ( $term_id ) {
	$old = isset( $GLOBALS['jayms_tg_old_slug'][ $term_id ] ) ? $GLOBALS['jayms_tg_old_slug'][ $term_id ] : '';
	$t   = get_term( $term_id, JAYMS_TG_TAX );
	$new = ( $t && ! is_wp_error( $t ) ) ? $t->slug : '';
	if ( $old && $new && $old !== $new ) {
		$ids = get_posts( array(
			'post_type' => 'page', 'post_status' => 'any', 'posts_per_page' => -1,
			'fields' => 'ids', 'no_found_rows' => true,
			'meta_key' => 'jayms_tool_category', 'meta_value' => $old,
		) );
		foreach ( $ids as $id ) { update_post_meta( $id, 'jayms_tool_category', $new ); }
	}
} );

/* ---------------------------------------------- forget the list when it changes */

foreach ( array( 'created_', 'edited_', 'delete_' ) as $jayms_tg_ev ) {
	add_action( $jayms_tg_ev . JAYMS_TG_TAX, function () { delete_transient( JAYMS_TG_CACHE ); } );
}
unset( $jayms_tg_ev );
