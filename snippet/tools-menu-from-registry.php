/**
 * The Bible Study Tools menu, grouped by the page's own jayms_tool_category.
 *
 * It used to be grouped in the browser by a script in the header template,
 * which walked the flat list of links and started a new group whenever it
 * met a marker slug. That made a tool's group depend on where it happened
 * to sit in the menu: the Divine Council Index, Word Study and the
 * Interleaved Bible followed the Psalter and so were filed under Command
 * Centers, and the Gods of the Bible followed the printables marker and was
 * filed under Printables.
 *
 * Now the groups are built here, from the same two values the floating
 * launcher reads: jayms_tool_category decides the group, jayms_menu_label
 * the name. Set them on the page and both menus follow.
 *
 * The list is marked data-grouped, which is the header script's own guard,
 * so it leaves the finished menu alone.
 */

if ( ! function_exists( 'jayms_tools_menu_groups' ) ) {
	function jayms_tools_menu_groups() {
		return array(
			'timeline'       => 'Timelines',
			'command-center' => 'Command Centers',
			'search-lookup'  => 'Search and Lookup',
			'printable'      => 'Printables',
		);
	}
}

if ( ! function_exists( 'jayms_tools_menu_list' ) ) {
	function jayms_tools_menu_list() {
		$pages = get_posts( array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
			'meta_query'     => array( array( 'key' => 'jayms_tool_category', 'value' => '', 'compare' => '!=' ) ),
		) );
		if ( empty( $pages ) ) { return ''; }

		$by_group = array();
		foreach ( $pages as $p ) {
			$cat = trim( (string) get_post_meta( $p->ID, 'jayms_tool_category', true ) );
			if ( '' !== $cat ) { $by_group[ $cat ][] = $p; }
		}

		$hub  = esc_url( home_url( '/bible-study-tools-2/' ) );
		$out  = '<ul class="wp-block-navigation__submenu-container jayms-tools-submenu wp-block-navigation-submenu" data-grouped="1">';
		$any  = false;
		foreach ( jayms_tools_menu_groups() as $key => $name ) {
			if ( empty( $by_group[ $key ] ) ) { continue; }
			$any   = true;
			$out  .= '<li class="wp-block-navigation-item has-child wp-block-navigation-submenu jayms-tool-group">';
			$out  .= '<a class="wp-block-navigation-item__content jayms-group-label" href="' . $hub . '">';
			$out  .= '<span class="wp-block-navigation-item__label">' . esc_html( $name ) . '</span></a>';
			$out  .= '<ul class="wp-block-navigation__submenu-container wp-block-navigation-submenu">';
			foreach ( $by_group[ $key ] as $p ) {
				$label = trim( (string) get_post_meta( $p->ID, 'jayms_menu_label', true ) );
				if ( '' === $label ) { $label = get_the_title( $p ); }
				$out .= '<li class="wp-block-navigation-item wp-block-navigation-link">';
				$out .= '<a class="wp-block-navigation-item__content" href="' . esc_url( get_permalink( $p ) ) . '">';
				$out .= '<span class="wp-block-navigation-item__label">' . esc_html( $label ) . '</span></a></li>';
			}
			$out .= '</ul></li>';
		}
		$out .= '</ul>';
		return $any ? $out : '';
	}
}

add_filter( 'render_block', function ( $content, $block ) {
	if ( empty( $block['blockName'] ) || 'core/navigation-submenu' !== $block['blockName'] ) { return $content; }
	$class = isset( $block['attrs']['className'] ) ? (string) $block['attrs']['className'] : '';
	if ( false === strpos( $class, 'jayms-tools-submenu' ) ) { return $content; }

	$at = strpos( $content, '<ul class="wp-block-navigation__submenu-container' );
	if ( false === $at ) { return $content; }
	$menu = jayms_tools_menu_list();
	if ( '' === $menu ) { return $content; }
	return substr( $content, 0, $at ) . $menu . '</li>';
}, 10, 2 );
