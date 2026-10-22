<?php
/**
 * Content types and taxonomies.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'cwm_register_types', 5 );

function cwm_register_types() {
	$team_on = cwm_team_on();

	register_post_type(
		'service',
		array(
			'labels'       => cwm_labels( 'Service', 'Services' ),
			'public'       => true,
			'has_archive'  => false,
			'rewrite'      => array(
				'slug'       => 'services',
				'with_front' => false,
			),
			'menu_icon'    => 'dashicons-portfolio',
			'menu_position' => 4,
			'supports'     => array( 'title', 'editor', 'page-attributes', 'thumbnail', 'revisions' ),
			'show_in_rest' => true,
		)
	);

	register_post_type(
		'industry',
		array(
			'labels'             => cwm_labels( 'Industry', 'Industries' ),
			'public'             => false,
			'show_ui'            => true,
			'publicly_queryable' => false,
			'menu_icon'          => 'dashicons-store',
			'menu_position'      => 5,
			'supports'           => array( 'title', 'page-attributes' ),
		)
	);

	register_post_type(
		'resource',
		array(
			'labels'             => cwm_labels( 'Download', 'Downloads' ),
			'description'        => 'Publications, checklists and templates people can download.',
			'public'             => false,
			'show_ui'            => true,
			'publicly_queryable' => false,
			'menu_icon'          => 'dashicons-download',
			'menu_position'      => 6,
			'supports'           => array( 'title', 'page-attributes' ),
		)
	);

	register_post_type(
		'glossary_term',
		array(
			'labels'             => cwm_labels( 'Glossary term', 'ESG glossary' ),
			'public'             => false,
			'show_ui'            => true,
			'publicly_queryable' => false,
			'menu_icon'          => 'dashicons-book-alt',
			'menu_position'      => 7,
			'supports'           => array( 'title', 'editor' ),
		)
	);

	register_post_type(
		'testimonial',
		array(
			'labels'             => cwm_labels( 'Testimonial', 'Testimonials' ),
			'public'             => false,
			'show_ui'            => true,
			'publicly_queryable' => false,
			'menu_icon'          => 'dashicons-format-quote',
			'menu_position'      => 8,
			'supports'           => array( 'title', 'editor', 'page-attributes' ),
		)
	);

	// Switch B: the team type is only public while "Show team section" is on.
	register_post_type(
		'team_member',
		array(
			'labels'              => cwm_labels( 'Team member', 'Team' ),
			'public'              => $team_on,
			'publicly_queryable'  => $team_on,
			'exclude_from_search' => ! $team_on,
			'show_ui'             => true,
			'show_in_nav_menus'   => $team_on,
			'has_archive'         => $team_on ? 'about/our-team' : false,
			'rewrite'             => array(
				'slug'       => 'about/our-team',
				'with_front' => false,
			),
			'menu_icon'           => 'dashicons-groups',
			'menu_position'       => 9,
			'supports'            => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
		)
	);

	register_taxonomy(
		'service_area',
		array( 'post', 'team_member', 'resource' ),
		array(
			'labels'            => cwm_labels( 'Service line', 'Service lines' ),
			'public'            => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'hierarchical'      => true,
			'description'       => 'Tag an insight with one or more services and it is listed on those service pages automatically.',
		)
	);

	register_taxonomy(
		'esg_topic',
		array( 'post', 'resource' ),
		array(
			'labels'            => cwm_labels( 'ESG topic', 'ESG topics' ),
			'public'            => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'hierarchical'      => true,
		)
	);

	register_taxonomy(
		'series',
		array( 'post' ),
		array(
			'labels'            => cwm_labels( 'Article series', 'Article series' ),
			'public'            => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'hierarchical'      => true,
			'description'       => 'Group articles into a named series. Set each article\'s part number in its "Insight details" box.',
		)
	);

	register_post_type(
		'enquiry',
		array(
			'labels'          => cwm_labels( 'Enquiry', 'Enquiries' ),
			'public'          => false,
			'show_ui'         => true,
			'menu_icon'       => 'dashicons-email-alt',
			'menu_position'   => 2,
			'supports'        => array( 'title' ),
			'capability_type' => 'post',
			'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap'    => true,
		)
	);
}

function cwm_labels( $one, $many ) {
	return array(
		'name'               => $many,
		'singular_name'      => $one,
		'add_new'            => 'Add ' . strtolower( $one ),
		'add_new_item'       => 'Add ' . strtolower( $one ),
		'edit_item'          => 'Edit ' . strtolower( $one ),
		'new_item'           => 'New ' . strtolower( $one ),
		'view_item'          => 'View ' . strtolower( $one ),
		'search_items'       => 'Search ' . strtolower( $many ),
		'not_found'          => 'Nothing found',
		'all_items'          => 'All ' . strtolower( $many ),
		'menu_name'          => $many,
	);
}

/** Rename "Posts" to "Insights" in the admin. */
add_action(
	'init',
	function () {
		global $wp_post_types;
		if ( isset( $wp_post_types['post'] ) ) {
			$l                 = $wp_post_types['post']->labels;
			$l->name           = 'Insights';
			$l->singular_name  = 'Insight';
			$l->menu_name      = 'Insights';
			$l->all_items      = 'All insights';
			$l->add_new_item   = 'Add insight';
			$l->edit_item      = 'Edit insight';
			$l->name_admin_bar = 'Insight';
		}
	},
	20
);

/** Each service gets a matching service-line term so insights can be tagged to it. */
add_action(
	'save_post_service',
	function ( $post_id, $post ) {
		if ( wp_is_post_revision( $post_id ) || 'auto-draft' === $post->post_status || ! $post->post_name ) {
			return;
		}
		$term_id = (int) get_post_meta( $post_id, '_cwm_term', true );
		$term    = $term_id ? get_term( $term_id, 'service_area' ) : null;
		if ( $term && ! is_wp_error( $term ) ) {
			wp_update_term( $term_id, 'service_area', array( 'name' => $post->post_title, 'slug' => $post->post_name ) );
		} else {
			$existing = get_term_by( 'slug', $post->post_name, 'service_area' );
			$res      = $existing ? array( 'term_id' => $existing->term_id ) : wp_insert_term( $post->post_title, 'service_area', array( 'slug' => $post->post_name ) );
			if ( ! is_wp_error( $res ) ) {
				update_post_meta( $post_id, '_cwm_term', $res['term_id'] );
			}
		}
	},
	10,
	2
);

/** Case analyses always sit under Tax alerts and are tagged to the Tax service. */
add_action(
	'save_post_post',
	function ( $post_id ) {
		if ( wp_is_post_revision( $post_id ) || ! has_category( 'case-analysis', $post_id ) ) {
			return;
		}
		$tax_alerts = get_category_by_slug( 'tax-alerts' );
		if ( $tax_alerts && ! has_category( 'tax-alerts', $post_id ) ) {
			wp_set_post_categories( $post_id, array( $tax_alerts->term_id ), true );
		}
		$tax_service = cwm_service_by_role( 'tax' );
		$slug        = $tax_service ? get_post_field( 'post_name', $tax_service ) : 'tax';
		if ( get_term_by( 'slug', $slug, 'service_area' ) ) {
			wp_set_object_terms( $post_id, $slug, 'service_area', true );
		}
	},
	20
);

/** Only one insight can be the featured pin. */
add_action(
	'updated_post_meta',
	'cwm_single_featured',
	10,
	4
);
add_action(
	'added_post_meta',
	'cwm_single_featured',
	10,
	4
);
function cwm_single_featured( $meta_id, $post_id, $key, $value ) {
	if ( '_cwm_featured' !== $key || '1' !== (string) $value ) {
		return;
	}
	$others = get_posts(
		array(
			'post_type'    => 'post',
			'post_status'  => 'any',
			'meta_key'     => '_cwm_featured',
			'meta_value'   => '1',
			'post__not_in' => array( $post_id ),
			'fields'       => 'ids',
			'numberposts'  => -1,
		)
	);
	foreach ( $others as $id ) {
		update_post_meta( $id, '_cwm_featured', '' );
	}
}
