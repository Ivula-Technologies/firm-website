<?php
/**
 * Theme setup, assets and navigation.
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	function () {
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'align-wide' );
		add_theme_support( 'editor-styles' );
		add_editor_style( 'assets/css/editor.css' );
		add_theme_support( 'disable-custom-colors' );
		add_theme_support(
			'editor-color-palette',
			array(
				array( 'name' => 'Deep navy', 'slug' => 'navy', 'color' => '#0B2545' ),
				array( 'name' => 'Royal blue', 'slug' => 'royal', 'color' => '#1F5FBF' ),
				array( 'name' => 'Sky tint', 'slug' => 'sky', 'color' => '#E8F1FB' ),
				array( 'name' => 'Teal (tags and charts)', 'slug' => 'teal', 'color' => '#1E8E8A' ),
				array( 'name' => 'Charcoal', 'slug' => 'charcoal', 'color' => '#1F2933' ),
				array( 'name' => 'White', 'slug' => 'white', 'color' => '#FFFFFF' ),
			)
		);
		add_image_size( 'card', 720, 405, true );
		add_image_size( 'hero', 1600, 1000, false );
		add_image_size( 'portrait', 600, 750, true );
	}
);

add_action(
	'wp_enqueue_scripts',
	function () {
		$css = CWM_DIR . '/style.css';
		$js  = CWM_DIR . '/assets/js/site.js';
		wp_enqueue_style( 'cwm', get_stylesheet_uri(), array(), CWM_VERSION . '.' . filemtime( $css ) );
		wp_enqueue_script( 'cwm', CWM_URI . '/assets/js/site.js', array(), CWM_VERSION . '.' . filemtime( $js ), array( 'strategy' => 'defer', 'in_footer' => true ) );
		wp_dequeue_style( 'classic-theme-styles' );
		if ( ! is_singular( array( 'post', 'page' ) ) ) {
			wp_dequeue_style( 'wp-block-library' );
			wp_dequeue_style( 'global-styles' );
		}
	},
	20
);

/** Preload the two fonts so text renders in the brand faces straight away. */
add_action(
	'wp_head',
	function () {
		foreach ( array( 'manrope-latin-wght-normal.woff2', 'inter-latin-wght-normal.woff2' ) as $f ) {
			echo '<link rel="preload" href="' . esc_url( CWM_URI . '/assets/fonts/' . $f ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
		}
		echo '<meta name="theme-color" content="#0B2545">' . "\n";
		if ( ! has_site_icon() ) {
			echo '<link rel="icon" href="' . esc_url( CWM_URI . '/assets/img/favicon.svg' ) . '" type="image/svg+xml">' . "\n";
		}
	},
	1
);

/** Lean front end. */
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );

add_action(
	'send_headers',
	function () {
		if ( is_admin() ) {
			return;
		}
		header( 'X-Content-Type-Options: nosniff' );
		header( 'Referrer-Policy: strict-origin-when-cross-origin' );
		header( 'X-Frame-Options: SAMEORIGIN' );
	}
);

add_filter(
	'excerpt_length',
	function () {
		return 26;
	}
);
add_filter(
	'excerpt_more',
	function () {
		return '…';
	}
);

/**
 * Main menu, built from content so the switches update it automatically.
 *
 * @return array[] label, url, children, current
 */
function cwm_menu() {
	$req      = trailingslashit( strtok( cwm_current_url(), '?' ) );
	$services = array(
		array(
			'label' => 'All services',
			'url'   => cwm_page_url( 'services' ),
		),
	);
	foreach ( cwm_services() as $s ) {
		$services[] = array(
			'label' => $s->post_title,
			'url'   => get_permalink( $s ),
			'icon'  => cwm_meta( 'icon', $s->ID ),
		);
	}
	$insights = array(
		array(
			'label' => 'All insights',
			'url'   => cwm_insights_url(),
		),
	);
	foreach ( array( 'tax-alerts', 'case-analysis' ) as $slug ) {
		$cat = get_category_by_slug( $slug );
		if ( $cat ) {
			$insights[] = array(
				'label' => 'case-analysis' === $slug ? 'Tax case analysis' : $cat->name,
				'url'   => get_category_link( $cat ),
			);
		}
	}
	if ( cwm_page_id( 'esg-hub' ) ) {
		$insights[] = array(
			'label' => get_the_title( cwm_page_id( 'esg-hub' ) ),
			'url'   => cwm_page_url( 'esg-hub' ),
		);
	}
	foreach ( array( 'guides', 'news-events' ) as $slug ) {
		$cat = get_category_by_slug( $slug );
		if ( $cat ) {
			$insights[] = array(
				'label' => $cat->name,
				'url'   => get_category_link( $cat ),
			);
		}
	}
	$about = array(
		array(
			'label' => 'Who we are',
			'url'   => cwm_page_url( 'about' ),
		),
		array(
			'label' => 'Our approach',
			'url'   => cwm_page_url( 'approach' ),
		),
	);
	if ( cwm_team_on() && cwm_page_id( 'team' ) ) {
		$about[] = array(
			'label' => 'Our team',
			'url'   => cwm_page_url( 'team' ),
		);
	}
	$items = array(
		array(
			'label' => 'Home',
			'url'   => home_url( '/' ),
		),
		array(
			'label'    => 'Services',
			'url'      => cwm_page_url( 'services' ),
			'children' => $services,
			'match'    => is_singular( 'service' ),
		),
		array(
			'label' => 'Industries',
			'url'   => cwm_page_url( 'industries' ),
		),
		array(
			'label'    => 'Insights',
			'url'      => cwm_insights_url(),
			'children' => $insights,
			'match'    => is_home() || is_category() || is_singular( 'post' ) || is_page( cwm_page_id( 'esg-hub' ) ),
		),
		array(
			'label'    => 'About',
			'url'      => cwm_page_url( 'about' ),
			'children' => $about,
			'match'    => is_page( array( cwm_page_id( 'approach' ), cwm_page_id( 'team' ) ) ) || is_singular( 'team_member' ),
		),
		array(
			'label' => 'Contact',
			'url'   => cwm_page_url( 'contact' ),
		),
	);
	foreach ( $items as &$item ) {
		$item['current'] = ! empty( $item['match'] ) || trailingslashit( $item['url'] ) === $req;
		if ( 'Home' === $item['label'] ) {
			$item['current'] = is_front_page();
		}
	}
	return $items;
}

/** Body classes for switch states (handy for styling and support). */
add_filter(
	'body_class',
	function ( $classes ) {
		$classes[] = 'audit-' . cwm_audit_state();
		$classes[] = cwm_team_on() ? 'team-on' : 'team-off';
		return $classes;
	}
);

/** Allow PDF uploads with a sensible size for profiles. */
add_filter(
	'upload_mimes',
	function ( $m ) {
		$m['pdf'] = 'application/pdf';
		return $m;
	}
);
