/**
 * One public read route for every index-backed tool.
 *
 * The entries for the Myth Checker, the Fringe Files and the rest all live
 * in the same jayms_index_entry post type, tagged by jayms_index_type, with
 * the tool's own fields in the jidx_payload meta. That meta is not exposed
 * through the core REST API, so a tool's data could only be read by loading
 * the page it renders on. This hands it over directly, the way
 * /jayms/v1/differences already does for one type.
 *
 *   /wp-json/jayms/v1/index-entries?type=myth-checker
 */
add_action( 'rest_api_init', function () {
	register_rest_route( 'jayms/v1', '/index-entries', array(
		'methods'             => 'GET',
		'permission_callback' => '__return_true',
		'args'                => array(
			'type' => array( 'required' => true, 'type' => 'string' ),
		),
		'callback' => function ( WP_REST_Request $req ) {
			$type = sanitize_title( $req->get_param( 'type' ) );
			if ( ! $type ) {
				return new WP_Error( 'jayms_no_type', 'Pass ?type=<index type slug>', array( 'status' => 400 ) );
			}
			$posts = get_posts( array(
				'post_type'        => 'jayms_index_entry',
				'posts_per_page'   => -1,
				'orderby'          => 'menu_order ID',
				'order'            => 'ASC',
				'suppress_filters' => false,
				'tax_query'        => array( array(
					'taxonomy' => 'jayms_index_type',
					'field'    => 'slug',
					'terms'    => $type,
				) ),
			) );
			$out = array();
			foreach ( $posts as $p ) {
				$payload = get_post_meta( $p->ID, 'jidx_payload', true );
				if ( is_string( $payload ) ) {
					$payload = json_decode( $payload, true );
				}
				if ( ! is_array( $payload ) ) {
					$payload = array();
				}
				$payload['id']    = (int) $p->ID;
				$payload['title'] = $p->post_title;
				$out[] = $payload;
			}
			return rest_ensure_response( $out );
		},
	) );
} );
