<?php
/**
 * Enforcement of Switch A (audit) and Switch B (team), plus staff-name privacy.
 * Templates also check the switches, but these hooks make sure nothing leaks through
 * URLs, search, feeds, the XML sitemap, the REST API or oEmbed.
 */

defined( 'ABSPATH' ) || exit;

/** Flush rewrite rules on the request after a switch changes. */
add_action(
	'init',
	function () {
		if ( get_option( 'cwm_flush_rewrite' ) ) {
			delete_option( 'cwm_flush_rewrite' );
			flush_rewrite_rules( false ); // Deferred by WordPress until wp_loaded, after every type is registered.
		}
	},
	1 // Before check_theme_switched (init 99), so a flag set during first-run setup waits for the next request.
);

/** IDs that must not appear publicly right now. */
function cwm_hidden_ids() {
	$ids = array();
	if ( ! cwm_audit_visible() && cwm_audit_service_id() ) {
		$ids[] = cwm_audit_service_id();
	}
	if ( ! cwm_team_on() && cwm_page_id( 'team' ) ) {
		$ids[] = cwm_page_id( 'team' );
	}
	return $ids;
}

/** Tax query that keeps audit-tagged content out while Switch A is Hidden. */
function cwm_audit_tax_exclusion() {
	$slug = cwm_audit_term_slug();
	if ( cwm_audit_visible() || ! $slug ) {
		return array();
	}
	return array(
		'taxonomy' => 'service_area',
		'field'    => 'slug',
		'terms'    => array( $slug ),
		'operator' => 'NOT IN',
	);
}

/** Apply exclusions to every front-end query. */
add_action(
	'pre_get_posts',
	function ( $q ) {
		static $busy = false;
		if ( $busy || ( is_admin() && ! wp_doing_ajax() ) ) {
			return;
		}
		$busy = true;
		cwm_hidden_ids(); // Warm caches without recursing into this hook.
		cwm_audit_term_slug();
		$busy = false;
		$type = $q->get( 'post_type' );
		if ( $q->is_search() || in_array( $type, array( '', 'any', 'post', 'page', 'service', 'resource' ), true ) || is_array( $type ) ) {
			$hidden = cwm_hidden_ids();
			if ( $hidden && ! $q->get( 'p' ) && ! $q->get( 'page_id' ) && ! $q->get( 'name' ) && ! $q->get( 'pagename' ) ) {
				$q->set( 'post__not_in', array_merge( (array) $q->get( 'post__not_in' ), $hidden ) );
			}
			$ex = cwm_audit_tax_exclusion();
			if ( $ex && in_array( $type, array( '', 'any', 'post', 'resource' ), true ) ) {
				$tq   = (array) $q->get( 'tax_query' );
				$tq[] = $ex;
				$q->set( 'tax_query', $tq );
			}
		}
		if ( $q->is_search() && $q->is_main_query() ) {
			$q->set( 'post_type', cwm_team_on() ? array( 'post', 'page', 'service', 'team_member' ) : array( 'post', 'page', 'service' ) );
			if ( cwm_team_on() ) {
				$q->set(
					'meta_query',
					array(
						'relation' => 'OR',
						array(
							'key'     => '_cwm_published',
							'compare' => 'NOT EXISTS',
						),
						array(
							'key'   => '_cwm_published',
							'value' => '1',
						),
					)
				);
			}
		}
	}
);

/** Direct visits to hidden things. */
add_action(
	'template_redirect',
	function () {
		// Switch A: the audit page and audit-tagged insights 404 while hidden.
		if ( ! cwm_audit_visible() ) {
			if ( is_singular( 'service' ) && (int) get_queried_object_id() === cwm_audit_service_id() ) {
				cwm_force_404();
				return;
			}
			$slug = cwm_audit_term_slug();
			if ( is_singular( 'post' ) && $slug && has_term( $slug, 'service_area', get_queried_object_id() ) ) {
				cwm_force_404();
				return;
			}
		}

		// Switch B: every team URL goes to About while the section is off.
		$team_page = cwm_page_id( 'team' );
		if ( ! cwm_team_on() ) {
			$path      = trim( (string) wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '', PHP_URL_PATH ), '/' );
			$team_path = $team_page ? trim( (string) wp_parse_url( get_permalink( $team_page ), PHP_URL_PATH ), '/' ) : 'about/our-team';
			if ( ( $team_page && is_page( $team_page ) ) || is_singular( 'team_member' ) || is_post_type_archive( 'team_member' ) || ( $team_path && ( $path === $team_path || 0 === strpos( $path, $team_path . '/' ) ) ) ) {
				wp_safe_redirect( cwm_page_url( 'about' ), 302 );
				exit;
			}
		} elseif ( is_singular( 'team_member' ) && ! get_post_meta( get_queried_object_id(), '_cwm_published', true ) ) {
			cwm_force_404();
			return;
		}

		// No author archives: they would expose people's names.
		if ( is_author() ) {
			cwm_force_404();
		}
	},
	1
);

function cwm_force_404() {
	global $wp_query;
	$wp_query->set_404();
	status_header( 404 );
	nocache_headers();
}

/** XML sitemap: only what the public may see. */
add_filter(
	'wp_sitemaps_add_provider',
	function ( $provider, $name ) {
		return 'users' === $name ? false : $provider;
	},
	10,
	2
);

add_filter(
	'wp_sitemaps_post_types',
	function ( $types ) {
		if ( ! cwm_team_on() ) {
			unset( $types['team_member'] );
		}
		return $types;
	}
);

add_filter(
	'wp_sitemaps_taxonomies',
	function ( $taxonomies ) {
		unset( $taxonomies['post_tag'] );
		return $taxonomies;
	}
);

add_filter(
	'wp_sitemaps_posts_query_args',
	function ( $args, $post_type ) {
		$args['post__not_in'] = array_merge( isset( $args['post__not_in'] ) ? (array) $args['post__not_in'] : array(), cwm_hidden_ids() );
		if ( 'post' === $post_type && cwm_audit_tax_exclusion() ) {
			$args['tax_query'] = array( cwm_audit_tax_exclusion() ); // phpcs:ignore WordPress.DB.SlowDBQuery
		}
		if ( 'team_member' === $post_type ) {
			$args['meta_key']   = '_cwm_published'; // phpcs:ignore WordPress.DB.SlowDBQuery
			$args['meta_value'] = '1'; // phpcs:ignore WordPress.DB.SlowDBQuery
		}
		return $args;
	},
	10,
	2
);

/** REST API: hide user listings (staff names) from the public. */
add_filter(
	'rest_endpoints',
	function ( $endpoints ) {
		if ( ! is_user_logged_in() ) {
			unset( $endpoints['/wp/v2/users'], $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
		}
		return $endpoints;
	}
);

/** oEmbed: credit the firm, not the WordPress user. */
add_filter(
	'oembed_response_data',
	function ( $data ) {
		$data['author_name'] = cwm_firm();
		$data['author_url']  = home_url( '/' );
		return $data;
	}
);

/** Feeds and anything else that asks for the author: the team byline. */
add_filter(
	'the_author',
	function ( $name ) {
		if ( is_admin() ) {
			return $name;
		}
		$b = cwm_byline();
		return $b['name'];
	}
);
add_filter(
	'author_link',
	function () {
		return home_url( '/' );
	}
);

/** Corporate site: no comments or pingbacks. */
add_filter( 'comments_open', '__return_false', 20 );
add_filter( 'pings_open', '__return_false', 20 );
add_filter( 'comments_array', '__return_empty_array', 20 );
add_action(
	'admin_menu',
	function () {
		remove_menu_page( 'edit-comments.php' );
	}
);
add_filter( 'xmlrpc_enabled', '__return_false' );

/**
 * [assurance]...[/assurance] wraps ESG wording about assurance or verification,
 * so it follows Switch A.
 */
add_shortcode(
	'assurance',
	function ( $atts, $content = '' ) {
		return cwm_audit_visible() ? do_shortcode( $content ) : '';
	}
);

/** Services usable in forms and filters: respects Switch A. */
function cwm_service_choices() {
	$out = array();
	foreach ( cwm_services() as $s ) {
		$out[ $s->post_name ] = $s->post_title;
	}
	return $out;
}
