/**
 * JAYMS Interleaved Bible: per-book serving and server-side rendering
 *
 * Two jobs, both on page 96261 only:
 *
 * 1. Only ship the book being read. Snippet 110 used to inline all 66 books,
 *    1.65MB, to display one. This preloads the single requested book (median
 *    14KB) and hands the picker a 2.5KB index of names. Everything else is
 *    fetched on demand.
 *
 * 2. Put the outline in the HTML. The whole tool renders in JavaScript, so a
 *    crawler saw two words: "Interleaved Bible". This echoes the real outline
 *    into the page before the script runs. JS replaces it on boot, so the
 *    markup only has to carry the words, not the behaviour.
 *
 * ?book=<slug> selects a book, which gives every book a crawlable URL without
 * creating 66 pages. James ruled out 66 pages, and a #hash is not a URL a
 * crawler can index.
 *
 * Data is read from sixcore-droid/jayms-tool-data and cached in a transient,
 * so the repo stays the single source of truth. A failed fetch degrades to
 * "no server-rendered markup" and the client still works.
 */

const JAYMS_OUTLINE_PAGE = 96261;
const JAYMS_OUTLINE_BASE = 'https://raw.githubusercontent.com/sixcore-droid/jayms-tool-data/main/outline/';
const JAYMS_OUTLINE_TTL  = 43200; // 12 hours

function jayms_outline_get( $file ) {
	$key = 'jayms_outline_' . md5( $file );
	$hit = get_transient( $key );
	if ( false !== $hit ) {
		return $hit;
	}
	$res = wp_remote_get( JAYMS_OUTLINE_BASE . $file . '.json', array( 'timeout' => 8 ) );
	if ( is_wp_error( $res ) || 200 !== wp_remote_retrieve_response_code( $res ) ) {
		return null;
	}
	$data = json_decode( wp_remote_retrieve_body( $res ), true );
	if ( ! is_array( $data ) ) {
		return null;
	}
	set_transient( $key, $data, JAYMS_OUTLINE_TTL );
	return $data;
}

/** The book the URL asked for, as [name, slug, data], or null for the picker. */
function jayms_outline_current() {
	$want = isset( $_GET['book'] ) ? sanitize_title( wp_unslash( $_GET['book'] ) ) : '';
	if ( '' === $want ) {
		return null;
	}
	$index = jayms_outline_get( 'index' );
	if ( ! $index || empty( $index['books'] ) ) {
		return null;
	}
	foreach ( $index['books'] as $b ) {
		if ( $b['slug'] === $want ) {
			$data = jayms_outline_get( $b['slug'] );
			return $data ? array( $b['name'], $b['slug'], $data ) : null;
		}
	}
	return null;
}

/**
 * The outline as plain crawlable markup. Deliberately not a copy of the JS
 * rendering: it carries the words a search engine needs and nothing else,
 * and the script overwrites it.
 */
function jayms_outline_markup( $name, $book ) {
	$h  = '<div class="jayms-outline-seo">';
	$h .= '<h2>' . esc_html( isset( $book['title'] ) ? $book['title'] : $name ) . '</h2>';
	if ( ! empty( $book['sub'] ) ) {
		$h .= '<p>' . esc_html( $book['sub'] ) . '</p>';
	}
	if ( ! empty( $book['desc'] ) ) {
		$h .= '<p>' . esc_html( wp_strip_all_tags( $book['desc'] ) ) . '</p>';
	}
	if ( ! empty( $book['eras'] ) && is_array( $book['eras'] ) ) {
		foreach ( $book['eras'] as $era ) {
			$label = '';
			foreach ( array( 'name', 'label', 'title' ) as $k ) {
				if ( ! empty( $era[ $k ] ) ) { $label = $era[ $k ]; break; }
			}
			if ( $label ) {
				$h .= '<h3>' . esc_html( $label ) . '</h3>';
			}
			if ( ! empty( $era['dates'] ) ) {
				$h .= '<p>' . esc_html( $era['dates'] ) . '</p>';
			}
			foreach ( array( 'rows', 'items', 'entries' ) as $k ) {
				if ( empty( $era[ $k ] ) || ! is_array( $era[ $k ] ) ) {
					continue;
				}
				$h .= '<ul>';
				foreach ( $era[ $k ] as $row ) {
					if ( ! is_array( $row ) ) {
						$h .= '<li>' . esc_html( (string) $row ) . '</li>';
						continue;
					}
					$bits = array();
					foreach ( array( 'ref', 'title', 'label', 'text', 'note', 'desc' ) as $f ) {
						if ( ! empty( $row[ $f ] ) && is_string( $row[ $f ] ) ) {
							$bits[] = wp_strip_all_tags( $row[ $f ] );
						}
					}
					if ( $bits ) {
						$h .= '<li>' . esc_html( implode( ' — ', $bits ) ) . '</li>';
					}
				}
				$h .= '</ul>';
				break;
			}
		}
	}
	$h .= '</div>';
	return $h;
}

/** The picker as crawlable markup: every book, every link. */
function jayms_outline_picker_markup( $index, $base ) {
	$h = '<div class="jayms-outline-seo"><h2>Every book of the Bible, outlined</h2><ul>';
	foreach ( $index['books'] as $b ) {
		$h .= '<li><a href="' . esc_url( add_query_arg( 'book', $b['slug'], $base ) ) . '">'
			. esc_html( $b['name'] ) . '</a></li>';
	}
	$h .= '</ul></div>';
	return $h;
}

/* The markup goes INSIDE the app container, because the script sets
   app.innerHTML on its first render and therefore replaces it. Anything
   appended after the container would sit on the page as duplicate text a
   reader can see, which is a different and worse problem. If the container
   is not found, nothing is added: no markup beats stray markup. */
add_filter( 'the_content', function ( $content ) {
	if ( ! is_page( JAYMS_OUTLINE_PAGE ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	$needle = '<div class="wrap" id="app"></div>';
	if ( false === strpos( $content, $needle ) ) {
		return $content;
	}

	$current = jayms_outline_current();
	if ( $current ) {
		$inner = jayms_outline_markup( $current[0], $current[2] );
	} else {
		$index = jayms_outline_get( 'index' );
		if ( ! $index || empty( $index['books'] ) ) {
			return $content;
		}
		$inner = jayms_outline_picker_markup( $index, get_permalink( JAYMS_OUTLINE_PAGE ) );
	}

	return str_replace( $needle, '<div class="wrap" id="app">' . $inner . '</div>', $content );
}, 21 );

/* Preload: the index always, plus the one requested book. Printed before
   snippet 110's own script so its TIMELINES can read them. */
add_action( 'wp_footer', function () {
	if ( ! is_page( JAYMS_OUTLINE_PAGE ) ) {
		return;
	}
	$index   = jayms_outline_get( 'index' );
	$current = jayms_outline_current();
	$names   = array();
	if ( $index && ! empty( $index['books'] ) ) {
		foreach ( $index['books'] as $b ) {
			$names[ $b['name'] ] = $b['slug'];
		}
	}
	$preload = $current ? array( $current[0] => $current[2] ) : array();
	?>
<script id="jayms-outline-preload">
window.JAYMS_OUTLINE_BOOKS   = <?php echo wp_json_encode( (object) $names ); ?>;
/* Ordered, because a short #id= is an index into this list. Changing the
   order changes every shared link, so it comes from index.json and
   nowhere else. */
window.JAYMS_OUTLINE_ORDER   = <?php echo wp_json_encode( array_keys( $names ) ); ?>;
window.JAYMS_OUTLINE_PRELOAD = <?php echo wp_json_encode( (object) $preload ); ?>;
window.JAYMS_OUTLINE_BASE    = <?php echo wp_json_encode( JAYMS_OUTLINE_BASE ); ?>;
</script>
	<?php
}, 4 );

/* Titles and canonical per book, so each ?book= URL is its own result. */
/* The page has a Jetpack custom title, which outranks document_title_parts
   and was serving the Luke title on every book's URL. pre_get_document_title
   short-circuits the whole chain, so it is the only hook that wins here. */
add_filter( 'pre_get_document_title', function ( $title ) {
	if ( ! is_page( JAYMS_OUTLINE_PAGE ) ) {
		return $title;
	}
	$current = jayms_outline_current();
	if ( ! $current ) {
		return $title;
	}
	$book = $current[2];
	$sub  = ! empty( $book['title'] ) ? $book['title'] : $current[0];
	return $sub . ' — Interactive Outline, Chapter by Chapter | JAYMS.COM';
}, 99 );

add_filter( 'get_canonical_url', function ( $url, $post ) {
	if ( ! $post || JAYMS_OUTLINE_PAGE !== (int) $post->ID ) {
		return $url;
	}
	$current = jayms_outline_current();
	return $current ? add_query_arg( 'book', $current[1], $url ) : $url;
}, 10, 2 );

add_action( 'wp_head', function () {
	if ( ! is_page( JAYMS_OUTLINE_PAGE ) ) {
		return;
	}
	$current = jayms_outline_current();
	if ( ! $current || empty( $current[2]['sub'] ) ) {
		return;
	}
	echo '<meta name="description" content="'
		. esc_attr( wp_strip_all_tags( $current[2]['sub'] ) ) . '" />' . "\n";
}, 1 );
