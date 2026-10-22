<?php
/**
 * Insights listing filters and content queries.
 */

defined( 'ABSPATH' ) || exit;

/** Read a filter value from the URL. */
function cwm_get( $key ) {
	return isset( $_GET[ $key ] ) ? sanitize_text_field( wp_unslash( $_GET[ $key ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
}

add_action(
	'pre_get_posts',
	function ( $q ) {
		if ( is_admin() || ! $q->is_main_query() || ! ( $q->is_home() || $q->is_category() ) ) {
			return;
		}
		$q->set( 'posts_per_page', 9 );
		$q->set( 'ignore_sticky_posts', true );

		$year = absint( cwm_get( 'yr' ) );
		if ( $year > 1990 ) {
			$q->set( 'year', $year );
		}

		$tq      = (array) $q->get( 'tax_query' );
		$service = sanitize_title( cwm_get( 'service' ) );
		if ( $service && array_key_exists( $service, cwm_service_choices() ) ) {
			$tq[] = array(
				'taxonomy' => 'service_area',
				'field'    => 'slug',
				'terms'    => array( $service ),
			);
		}
		$topic = sanitize_title( cwm_get( 'topic' ) );
		if ( $topic ) {
			$tq[] = array(
				'taxonomy' => 'esg_topic',
				'field'    => 'slug',
				'terms'    => array( $topic ),
			);
		}
		if ( $tq ) {
			$q->set( 'tax_query', $tq );
		}

		$search  = cwm_get( 'q' );
		$is_case = $q->is_category( 'case-analysis' );
		if ( $is_case ) {
			$mq = array();
			foreach ( array( 'court', 'taxtype', 'outcome' ) as $k ) {
				$v = sanitize_key( cwm_get( $k ) );
				if ( $v && array_key_exists( $v, cwm_case_options( $k ) ) ) {
					$mq[] = array(
						'key'   => '_cwm_case_' . $k,
						'value' => $v,
					);
				}
			}
			if ( $year > 1990 ) {
				// For cases, the year is the year of decision.
				$q->set( 'year', '' );
				$mq[] = array(
					'key'     => '_cwm_case_date',
					'value'   => array( $year . '-01-01', $year . '-12-31' ),
					'compare' => 'BETWEEN',
					'type'    => 'DATE',
				);
			}
			if ( $mq ) {
				$q->set( 'meta_query', $mq );
			}
			if ( '' !== $search ) {
				$q->set( 'post__in', cwm_case_search_ids( $search ) );
			}
		} elseif ( '' !== $search ) {
			$q->set( 's', $search );
		}
	},
	20
);

/** Case analyses whose title, case name or citation matches. */
function cwm_case_search_ids( $term ) {
	global $wpdb;
	$like = '%' . $wpdb->esc_like( $term ) . '%';
	$ids  = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->prepare(
			"SELECT DISTINCT p.ID FROM {$wpdb->posts} p
			LEFT JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key IN ('_cwm_case_name','_cwm_case_citation')
			WHERE p.post_type = 'post' AND p.post_status = 'publish' AND ( p.post_title LIKE %s OR m.meta_value LIKE %s )",
			$like,
			$like
		)
	);
	return $ids ? array_map( 'intval', $ids ) : array( 0 );
}

/** Years that have published insights (or case decisions), newest first. */
function cwm_years( $cases = false ) {
	global $wpdb;
	if ( $cases ) {
		$rows = $wpdb->get_col( "SELECT DISTINCT LEFT(meta_value,4) y FROM {$wpdb->postmeta} WHERE meta_key = '_cwm_case_date' AND meta_value <> '' ORDER BY y DESC" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	} else {
		$rows = $wpdb->get_col( "SELECT DISTINCT YEAR(post_date) y FROM {$wpdb->posts} WHERE post_type = 'post' AND post_status = 'publish' ORDER BY y DESC" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	}
	return array_filter( array_map( 'intval', $rows ) );
}

/** Insights tagged to a service, newest first. */
function cwm_service_insights( $service, $count = 6, $category = '', $not_category = '' ) {
	$args = array(
		'post_type'           => 'post',
		'posts_per_page'      => $count,
		'ignore_sticky_posts' => true,
		'tax_query'           => array( // phpcs:ignore WordPress.DB.SlowDBQuery
			array(
				'taxonomy' => 'service_area',
				'field'    => 'slug',
				'terms'    => array( $service->post_name ),
			),
		),
	);
	if ( $category ) {
		$args['category_name'] = $category;
	}
	$skip = $not_category ? get_category_by_slug( $not_category ) : null;
	if ( $skip ) {
		$args['category__not_in'] = array( $skip->term_id );
	}
	return new WP_Query( $args );
}

/** Latest case analyses. */
function cwm_recent_cases( $count = 3, $exclude = 0 ) {
	return new WP_Query(
		array(
			'post_type'           => 'post',
			'category_name'       => 'case-analysis',
			'posts_per_page'      => $count,
			'post__not_in'        => $exclude ? array( $exclude ) : array(),
			'ignore_sticky_posts' => true,
		)
	);
}

/** Related insights: same series or service line first, then same category. */
function cwm_related( $post_id, $count = 3 ) {
	$tax = array( 'relation' => 'OR' );
	$cat = cwm_primary_category( $post_id );
	if ( cwm_is_case( $post_id ) ) {
		return cwm_recent_cases( $count, $post_id );
	}
	$areas = wp_get_post_terms( $post_id, 'service_area', array( 'fields' => 'ids' ) );
	if ( $areas && ! is_wp_error( $areas ) ) {
		$tax[] = array(
			'taxonomy' => 'service_area',
			'terms'    => $areas,
		);
	}
	if ( $cat ) {
		$tax[] = array(
			'taxonomy' => 'category',
			'terms'    => array( $cat->term_id ),
		);
	}
	return new WP_Query(
		array(
			'post_type'           => 'post',
			'posts_per_page'      => $count,
			'post__not_in'        => array( $post_id ),
			'ignore_sticky_posts' => true,
			'tax_query'           => count( $tax ) > 1 ? $tax : array(), // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);
}

/**
 * Series information for an article: term, ordered post IDs and position.
 *
 * @return array|null
 */
function cwm_series_info( $post_id ) {
	$terms = wp_get_post_terms( $post_id, 'series' );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return null;
	}
	$term = $terms[0];
	$ids  = cwm_series_posts( $term->term_id );
	$pos  = array_search( (int) $post_id, $ids, true );
	if ( false === $pos ) {
		return null;
	}
	return array(
		'term'  => $term,
		'ids'   => $ids,
		'index' => $pos,
		'total' => count( $ids ),
		'prev'  => $pos > 0 ? $ids[ $pos - 1 ] : 0,
		'next'  => $pos < count( $ids ) - 1 ? $ids[ $pos + 1 ] : 0,
	);
}

/** Published posts in a series, ordered by part number then date. */
function cwm_series_posts( $term_id ) {
	$posts = get_posts(
		array(
			'post_type'   => 'post',
			'numberposts' => -1,
			'tax_query'   => array( // phpcs:ignore WordPress.DB.SlowDBQuery
				array(
					'taxonomy' => 'series',
					'terms'    => array( $term_id ),
				),
			),
			'orderby'     => 'date',
			'order'       => 'ASC',
			'suppress_filters' => false,
		)
	);
	usort(
		$posts,
		function ( $a, $b ) {
			$pa = (float) get_post_meta( $a->ID, '_cwm_series_part', true );
			$pb = (float) get_post_meta( $b->ID, '_cwm_series_part', true );
			if ( $pa && $pb && $pa !== $pb ) {
				return $pa <=> $pb;
			}
			return strcmp( $a->post_date, $b->post_date );
		}
	);
	return array_map(
		function ( $p ) {
			return (int) $p->ID;
		},
		$posts
	);
}

/** The pinned featured insight (or 0). */
function cwm_featured_post_id() {
	$ids = get_posts(
		array(
			'post_type'        => 'post',
			'meta_key'         => '_cwm_featured',
			'meta_value'       => '1',
			'numberposts'      => 1,
			'fields'           => 'ids',
			'suppress_filters' => false,
		)
	);
	return $ids ? (int) $ids[0] : 0;
}
