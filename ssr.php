/**
 * JAYMS - Tool entries: real URLs, server render, sitemap
 *
 * Every tool on the Alpha Tool Runner fetched its data in the browser, which
 * meant a crawler was served an empty div: 385 cards behind one URL, with no
 * text and no links to follow. This gives each entry a URL of its own and
 * prints it into the HTML before any JavaScript runs.
 *
 *   /bible-study-tools-2/<tool>/<entry-id>/
 *
 * The runner stays the only place a tool's behaviour is defined. This file
 * owns no content: it walks the same `blocks` list the JS walks, so a block
 * added to a data file appears here without a change.
 */

defined( 'JAYMS_SSR_VER' )  || define( 'JAYMS_SSR_VER', '7' );   // bump to reflush rewrites
defined( 'JAYMS_SSR_TTL' )  || define( 'JAYMS_SSR_TTL', 21600 ); // 6 hours
defined( 'JAYMS_SSR_BASE' ) || define( 'JAYMS_SSR_BASE',
	'https://raw.githubusercontent.com/sixcore-droid/jayms-tool-data/main/' );

/** page id => data slug, owned by the runner (snippet 137). */
function jayms_ssr_map() {
	if ( function_exists( 'jayms_alpha_tool_map' ) ) {
		return jayms_alpha_tool_map();
	}
	return array();
}

/** page id => path, with no leading or trailing slash. Cached: resolving a
 *  permalink is a query, and init runs on every admin and cron request too. */
function jayms_ssr_paths() {
	$k = 'jayms_ssr_paths_' . JAYMS_SSR_VER;
	$c = get_transient( $k );
	if ( is_array( $c ) ) { return $c; }
	$c = array();
	foreach ( array_keys( jayms_ssr_map() ) as $pid ) {
		$u = get_permalink( $pid );
		if ( $u ) { $c[ $pid ] = trim( wp_parse_url( $u, PHP_URL_PATH ), '/' ); }
	}
	set_transient( $k, $c, DAY_IN_SECONDS );
	return $c;
}

/* -------------------------------------------------------------------------
   The data, cached as a file under uploads. A transient is the wrong home:
   these files run to hundreds of kilobytes and the object cache drops
   anything near a megabyte without saying so.
   ------------------------------------------------------------------------- */

function jayms_ssr_cache_path( $slug ) {
	$up = wp_upload_dir();
	$dir = trailingslashit( $up['basedir'] ) . 'jayms-tool-cache';
	if ( ! is_dir( $dir ) ) { wp_mkdir_p( $dir ); }
	return $dir . '/' . sanitize_file_name( $slug ) . '.json';
}

function jayms_ssr_data( $slug ) {
	static $mem = array();
	if ( isset( $mem[ $slug ] ) ) { return $mem[ $slug ]; }

	$file = jayms_ssr_cache_path( $slug );
	$fresh = file_exists( $file ) && ( time() - filemtime( $file ) ) < JAYMS_SSR_TTL;

	if ( ! $fresh ) {
		// One request refreshes; the rest keep serving the stale copy rather
		// than all stampeding GitHub at once.
		$lock = 'jayms_ssr_lock_' . $slug;
		if ( ! get_transient( $lock ) ) {
			set_transient( $lock, 1, 120 );
			$r = wp_remote_get( JAYMS_SSR_BASE . $slug . '.json', array( 'timeout' => 20 ) );
			if ( ! is_wp_error( $r ) && 200 === wp_remote_retrieve_response_code( $r ) ) {
				$body = wp_remote_retrieve_body( $r );
				if ( $body && json_decode( $body ) !== null ) {
					file_put_contents( $file, $body );
				}
			}
			delete_transient( $lock );
		}
	}
	if ( ! file_exists( $file ) ) { return null; }
	$d = json_decode( file_get_contents( $file ), true );
	$mem[ $slug ] = $d;
	return $d;
}

function jayms_ssr_entry( $data, $id ) {
	if ( empty( $data['entries'] ) ) { return null; }
	foreach ( $data['entries'] as $e ) {
		if ( isset( $e['id'] ) && $e['id'] === $id ) { return $e; }
	}
	return null;
}

/* -------------------------------------------------------------------------
   Rewrites: /<tool path>/<entry>/
   ------------------------------------------------------------------------- */

add_filter( 'query_vars', function ( $v ) { $v[] = 'jentry'; return $v; } );

add_action( 'init', function () {
	foreach ( jayms_ssr_paths() as $pid => $path ) {
		if ( ! $path ) { continue; }
		add_rewrite_rule(
			'^' . preg_quote( $path, '/' ) . '/([a-z0-9][a-z0-9-]*)/?$',
			'index.php?page_id=' . (int) $pid . '&jentry=$matches[1]',
			'top'
		);
	}
	add_rewrite_rule( '^jayms-tool-entries-sitemap\.xml$',
		'index.php?jentry=__sitemap__', 'top' );

	if ( get_option( 'jayms_ssr_rules' ) !== JAYMS_SSR_VER ) {
		flush_rewrite_rules( false );
		update_option( 'jayms_ssr_rules', JAYMS_SSR_VER );
	}
}, 11 );

/** The entry asked for on this request, or '' . */
function jayms_ssr_current() {
	$e = get_query_var( 'jentry' );
	return is_string( $e ) ? $e : '';
}

/** Which tool page we are on, or 0. */
function jayms_ssr_page() {
	$map = jayms_ssr_map();
	$id  = (int) get_queried_object_id();
	return isset( $map[ $id ] ) ? $id : 0;
}

/* -------------------------------------------------------------------------
   Rendering. The same `blocks` list the runner walks.
   ------------------------------------------------------------------------- */

/** The runner's rich(): bold, italics and paragraphs, nothing else. */
function jayms_ssr_rich( $s ) {
	$s = esc_html( (string) $s );
	$s = preg_replace( '/\*\*(.+?)\*\*/s', '<b>$1</b>', $s );
	$s = preg_replace( '/(?<!\*)\*(?!\s)(.+?)(?<!\s)\*(?!\*)/s', '<i>$1</i>', $s );
	$parts = preg_split( '/\n{2,}/', trim( $s ) );
	return '<p>' . implode( '</p><p>', array_map(
		function ( $p ) { return nl2br( trim( $p ) ); }, $parts ) ) . '</p>';
}

function jayms_ssr_entry_url( $page_id, $id ) {
	return trailingslashit( get_permalink( $page_id ) ) . rawurlencode( $id ) . '/';
}

function jayms_ssr_box( $label, $html, $variant = 'open' ) {
	return '<div class="a-box v-' . esc_attr( $variant ) . '">'
		. '<div class="a-blabel">' . esc_html( $label ) . '</div>'
		. '<div class="a-btext">' . $html . '</div></div>';
}

function jayms_ssr_detail( $data, $e, $page_id ) {
	$out  = '<h1 class="a-dtitle">' . esc_html( $e['title'] ) . '</h1>';
	if ( ! empty( $e['summary'] ) ) {
		$out .= '<p class="a-dsum">' . esc_html( $e['summary'] ) . '</p>';
	}

	// Tags, read off the filter rows so a new row needs no change here.
	$tags = '';
	foreach ( (array) ( $data['filters'] ?? array() ) as $f ) {
		$fld = $f['field'] ?? '';
		if ( ! $fld || empty( $e[ $fld ] ) ) { continue; }
		foreach ( (array) ( $f['options'] ?? array() ) as $o ) {
			if ( ( $o['k'] ?? null ) === $e[ $fld ] ) {
				$tags .= '<span class="a-tag">' . esc_html( $o['n'] ) . '</span>';
			}
		}
	}
	if ( $tags ) { $out .= '<div class="a-dtags">' . $tags . '</div>'; }

	foreach ( (array) ( $data['blocks'] ?? array() ) as $b ) {
		$type = $b['type'] ?? 'box';

		if ( 'scripture' === $type ) {
			$ref = $e[ $b['refField'] ?? 'bibleRef' ] ?? '';
			if ( $ref ) {
				$out .= '<div class="a-box v-scripture"><div class="a-blabel">'
					. esc_html( $ref ) . '</div><div class="a-btext"><p>'
					. esc_html( $ref ) . ', in every translation this tool compares.'
					. '</p></div></div>';
			}
			continue;
		}

		if ( 'linkrow' === $type ) {
			$ids = (array) ( $e[ $b['field'] ?? 'links' ] ?? array() );
			if ( ! $ids ) { continue; }
			$row = '';
			foreach ( $ids as $lid ) {
				$t = jayms_ssr_entry( $data, $lid );
				if ( ! $t ) { continue; }
				$row .= '<a class="a-lnk" href="' . esc_url( jayms_ssr_entry_url( $page_id, $lid ) )
					. '">' . esc_html( $t['title'] ) . '</a>';
			}
			if ( $row ) {
				$out .= '<div class="a-box v-apparatus"><div class="a-blabel">'
					. esc_html( $b['title'] ?? 'Reads with' ) . '</div>'
					. '<div class="a-linkrow">' . $row . '</div></div>';
			}
			continue;
		}

		$v = $e[ $b['field'] ?? '' ] ?? '';
		if ( ! $v ) { continue; }

		if ( is_array( $v ) ) {                       // Sources and the like
			$tag = ( ( $b['list'] ?? '' ) === 'ordered' ) ? 'ol' : 'ul';
			$li  = '';
			foreach ( $v as $x ) {
				$li .= '<li>' . esc_html( is_array( $x ) ? wp_json_encode( $x ) : $x ) . '</li>';
			}
			$html = "<$tag>$li</$tag>";
		} else {
			$html = jayms_ssr_rich( $v );
			$note = $e[ $b['noteField'] ?? '' ] ?? '';
			if ( $note ) { $html .= '<div class="a-bnote">' . esc_html( $note ) . '</div>'; }
		}
		$out .= jayms_ssr_box( $b['title'] ?? '', $html, $b['variant'] ?? 'open' );
	}

	$out .= '<p class="a-ssr-back"><a href="' . esc_url( get_permalink( $page_id ) ) . '">'
		. esc_html( $data['tool']['backLabel'] ?? 'All entries' ) . '</a></p>';
	return $out;
}

/** Every entry as a real link, so a crawler can reach all of them. */
function jayms_ssr_index( $data, $page_id ) {
	$group = null;
	foreach ( (array) ( $data['filters'] ?? array() ) as $f ) {
		if ( ( $f['role'] ?? '' ) === 'group' ) { $group = $f; break; }
	}
	$buckets = array();
	foreach ( (array) ( $data['entries'] ?? array() ) as $e ) {
		$k = $group ? ( $e[ $group['field'] ] ?? '' ) : '';
		$buckets[ $k ][] = $e;
	}
	$order = array();
	if ( $group ) {
		foreach ( (array) $group['options'] as $o ) { $order[ $o['k'] ] = $o['n']; }
	}
	$out = '<div class="a-ssr-index">';
	foreach ( ( $order ?: array_fill_keys( array_keys( $buckets ), '' ) ) as $k => $name ) {
		if ( empty( $buckets[ $k ] ) ) { continue; }
		if ( $name ) { $out .= '<h2>' . esc_html( $name ) . '</h2>'; }
		$out .= '<ul>';
		foreach ( $buckets[ $k ] as $e ) {
			$out .= '<li><a href="' . esc_url( jayms_ssr_entry_url( $page_id, $e['id'] ) ) . '">'
				. esc_html( $e['title'] ) . '</a> '
				. esc_html( $e['summary'] ?? '' ) . '</li>';
		}
		$out .= '</ul>';
	}
	return $out . '</div>';
}

/* -------------------------------------------------------------------------
   Injection. The markup goes inside the runner's own mount, which the
   runner overwrites the moment it boots.
   ------------------------------------------------------------------------- */

add_filter( 'the_content', function ( $c ) {
	$pid = jayms_ssr_page();
	if ( ! $pid || ! in_the_loop() || ! is_main_query() ) { return $c; }
	$map  = jayms_ssr_map();
	$data = jayms_ssr_data( $map[ $pid ] );
	if ( ! $data ) { return $c; }

	$id   = jayms_ssr_current();
	$html = $id
		? ( ( $e = jayms_ssr_entry( $data, $id ) ) ? jayms_ssr_detail( $data, $e, $pid ) : '' )
		: jayms_ssr_index( $data, $pid );
	if ( ! $html ) { return $c; }

	return preg_replace(
		'/(<div[^>]*class="[^"]*jayms-tool-alpha[^"]*"[^>]*>)(<\/div>)/',
		'$1<div class="a-ssr">' . str_replace( '$', '\\$', $html ) . '</div>$2',
		$c, 1
	);
}, 9 );

/* -------------------------------------------------------------------------
   Head: title, description, canonical, open graph, structured data.
   ------------------------------------------------------------------------- */

function jayms_ssr_pair() {
	$pid = jayms_ssr_page();
	$id  = jayms_ssr_current();
	if ( ! $pid || ! $id ) { return null; }
	$map  = jayms_ssr_map();
	$data = jayms_ssr_data( $map[ $pid ] );
	if ( ! $data ) { return null; }
	$e = jayms_ssr_entry( $data, $id );
	return $e ? array( $pid, $data, $e ) : null;
}

add_filter( 'pre_get_document_title', function ( $t ) {
	$p = jayms_ssr_pair();
	if ( ! $p ) { return $t; }
	list( , $data, $e ) = $p;
	$tool = $data['tool']['name'] ?? '';
	return $e['title'] . ( $tool ? ' - ' . $tool : '' ) . ' | JAYMS.COM';
}, 20 );

// Jetpack prints the description from this meta key, so changing the value it
// reads is cleaner than printing a second tag beside it.
add_filter( 'get_post_metadata', function ( $val, $oid, $key ) {
	static $busy = false;
	if ( $busy || 'advanced_seo_description' !== $key ) { return $val; }
	$busy = true;
	$p = jayms_ssr_pair();
	$busy = false;
	if ( ! $p || (int) $oid !== (int) $p[0] ) { return $val; }
	return array( $p[2]['summary'] ?? '' );
}, 10, 3 );

add_filter( 'get_canonical_url', function ( $url, $post ) {
	$p = jayms_ssr_pair();
	if ( ! $p || ! $post || (int) $post->ID !== (int) $p[0] ) { return $url; }
	return jayms_ssr_entry_url( $p[0], $p[2]['id'] );
}, 20, 2 );

add_filter( 'jetpack_open_graph_tags', function ( $tags ) {
	$p = jayms_ssr_pair();
	if ( ! $p ) { return $tags; }
	list( $pid, $data, $e ) = $p;
	$tags['og:title']       = $e['title'];
	$tags['og:description'] = $e['summary'] ?? '';
	$tags['og:url']         = jayms_ssr_entry_url( $pid, $e['id'] );
	return $tags;
}, 20 );

add_action( 'wp_head', function () {
	$p = jayms_ssr_pair();
	if ( ! $p ) { return; }
	list( $pid, $data, $e ) = $p;
	$g = array(
		'@context'         => 'https://schema.org',
		'@type'            => 'Article',
		'headline'         => $e['title'],
		'description'      => $e['summary'] ?? '',
		'url'              => jayms_ssr_entry_url( $pid, $e['id'] ),
		'isPartOf'         => array(
			'@type' => 'Collection',
			'name'  => $data['tool']['name'] ?? '',
			'url'   => get_permalink( $pid ),
		),
		'inLanguage'       => 'en',
		'author'           => array( '@type' => 'Person', 'name' => 'James McLaughlin' ),
		'publisher'        => array( '@type' => 'Organization', 'name' => 'JAYMS.COM' ),
		'mainEntityOfPage' => jayms_ssr_entry_url( $pid, $e['id'] ),
	);
	if ( ! empty( $e['srcs'] ) && is_array( $e['srcs'] ) ) {
		$g['citation'] = array_values( array_filter( array_map( 'strval', $e['srcs'] ) ) );
	}
	echo "\n<script type=\"application/ld+json\">"
		. wp_json_encode( $g, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
		. "</script>\n";
}, 5 );

/* -------------------------------------------------------------------------
   Sitemap for the entry URLs, and a line in robots.txt pointing at it.
   ------------------------------------------------------------------------- */

add_action( 'template_redirect', function () {
	if ( '__sitemap__' !== jayms_ssr_current() ) { return; }
	status_header( 200 );
	nocache_headers();
	header( 'Content-Type: application/xml; charset=UTF-8' );
	echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
	echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
	foreach ( jayms_ssr_map() as $pid => $slug ) {
		$data = jayms_ssr_data( $slug );
		if ( empty( $data['entries'] ) ) { continue; }
		foreach ( $data['entries'] as $e ) {
			if ( empty( $e['id'] ) ) { continue; }
			echo "  <url><loc>" . esc_url( jayms_ssr_entry_url( $pid, $e['id'] ) )
				. "</loc><changefreq>monthly</changefreq></url>\n";
		}
	}
	echo '</urlset>';
	exit;
}, 0 );

add_filter( 'robots_txt', function ( $t ) {
	return $t . "\nSitemap: " . home_url( '/jayms-tool-entries-sitemap.xml' ) . "\n";
}, 20 );
