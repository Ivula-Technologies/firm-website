<?php
/**
 * Small helpers used across templates.
 */

defined( 'ABSPATH' ) || exit;

/** Default values for every firm setting. */
function cwm_setting_defaults() {
	return array(
		'firm_name'          => 'Firm Name',
		'tagline'            => 'Count what matters.',
		'description'        => 'Accounting, tax, advisory and ESG support for Kenyan businesses that want to grow, and to do it right.',
		'logo'               => 0,
		'logo_footer'        => 0,
		'phone'              => '+254 700 000 000',
		'email'              => 'info@example.co.ke',
		'whatsapp'           => '254700000000',
		'address'            => "Upper Hill, Nairobi\nP.O. Box 00000-00100, Nairobi, Kenya",
		'hours'              => "Monday to Friday, 8:00am to 5:00pm\nSaturday by appointment",
		'map_query'          => 'Upper Hill, Nairobi, Kenya',
		'linkedin'           => '',
		'x'                  => '',
		'facebook'           => '',
		'licence_line'       => '',
		'firm_profile'       => 0,
		'disclaimer'         => 'This article is general information only and is not professional advice. Tax and reporting rules change and every situation is different, so please talk to us before you act on it. We accept no liability for decisions made on the basis of this content alone.',
		'audit_state'        => 'hidden',
		'audit_partner'      => '[licensed audit firm]',
		'audit_licence'      => '',
		'show_team'          => 0,
		'author_teams'       => "Tax Team\nESG Team\nAdvisory Team\nAccounting Team",
		'email_contact'      => '',
		'email_proposals'    => '',
		'email_newsletter'   => '',
		'autoreply'          => 1,
		'ga_id'              => '',
		'newsletter_text'    => 'Tax alerts, ESG updates and practical guides, about twice a month.',
		'preview_on'         => 1,
		'preview_password'   => '',
		'preview_note'       => '',
		'preview_feedback'   => '',
	);
}

/** Read a firm setting. */
function cwm_opt( $key ) {
	static $cache = null;
	if ( null === $cache || doing_action( 'update_option_cwm_settings' ) ) {
		$cache = wp_parse_args( (array) get_option( 'cwm_settings', array() ), cwm_setting_defaults() );
	}
	return isset( $cache[ $key ] ) ? $cache[ $key ] : '';
}

/** Clear the settings cache after a save. */
add_action(
	'update_option_cwm_settings',
	function () {
		cwm_opt( '__refresh' );
	}
);

function cwm_firm() {
	return cwm_opt( 'firm_name' );
}

/** Switch A: hidden, partner or live. */
function cwm_audit_state() {
	$s = cwm_opt( 'audit_state' );
	return in_array( $s, array( 'hidden', 'partner', 'live' ), true ) ? $s : 'hidden';
}

function cwm_audit_visible() {
	return 'hidden' !== cwm_audit_state();
}

/** Switch B. */
function cwm_team_on() {
	return (bool) cwm_opt( 'show_team' );
}

/** The service post with a given role (audit, tax, esg), or 0. */
function cwm_service_by_role( $role ) {
	$ids = get_posts(
		array(
			'post_type'        => 'service',
			'post_status'      => 'publish',
			'meta_key'         => '_cwm_role',
			'meta_value'       => $role,
			'fields'           => 'ids',
			'numberposts'      => 1,
			'suppress_filters' => true,
		)
	);
	return $ids ? (int) $ids[0] : 0;
}

/** The service post flagged as audit and assurance (0 if none). */
function cwm_audit_service_id() {
	static $id = null;
	if ( null === $id ) {
		$ids = get_posts(
			array(
				'post_type'        => 'service',
				'post_status'      => 'any',
				'meta_key'         => '_cwm_role',
				'meta_value'       => 'audit',
				'fields'           => 'ids',
				'numberposts'      => 1,
				'suppress_filters' => true,
			)
		);
		$id  = $ids ? (int) $ids[0] : 0;
	}
	return $id;
}

/** Service-area term slug for the audit service (to keep tagged content out while hidden). */
function cwm_audit_term_slug() {
	$id = cwm_audit_service_id();
	return $id ? get_post_field( 'post_name', $id ) : '';
}

/**
 * Published services visible to the public, in menu order. Respects Switch A.
 *
 * @param string $group Optional group: money, obligations, impact.
 * @return WP_Post[]
 */
function cwm_services( $group = '' ) {
	$args = array(
		'post_type'        => 'service',
		'post_status'      => 'publish',
		'numberposts'      => -1,
		'orderby'          => array(
			'menu_order' => 'ASC',
			'title'      => 'ASC',
		),
		'suppress_filters' => false,
	);
	if ( ! cwm_audit_visible() && cwm_audit_service_id() ) {
		$args['post__not_in'] = array( cwm_audit_service_id() );
	}
	if ( $group ) {
		$args['meta_key']   = '_cwm_group';
		$args['meta_value'] = $group;
	}
	return get_posts( $args );
}

/** Field value for a post (meta key is _cwm_{key}). */
function cwm_meta( $key, $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	return get_post_meta( $post_id, '_cwm_' . $key, true );
}

/** Page field with a fallback default. */
function cwm_field( $key, $default = '', $post_id = null ) {
	$v = cwm_meta( $key, $post_id );
	return ( '' === $v || null === $v || array() === $v ) ? $default : $v;
}

/** Inline SVG icon from the bundled set. */
function cwm_icon( $name, $class = '' ) {
	static $icons = null;
	if ( null === $icons ) {
		$icons = require CWM_DIR . '/inc/icons-data.php';
	}
	if ( ! isset( $icons[ $name ] ) ) {
		$name = 'circle-check';
	}
	$inner = $icons[ $name ];
	$cls   = trim( 'icon icon-' . $name . ' ' . $class );
	if ( 0 === strpos( $inner, 'brand:' ) ) {
		return '<svg class="' . esc_attr( $cls ) . '" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">' . substr( $inner, 6 ) . '</svg>';
	}
	return '<svg class="' . esc_attr( $cls ) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $inner . '</svg>';
}

/** Icon names offered in the admin icon pickers. */
function cwm_icon_choices() {
	$icons = require CWM_DIR . '/inc/icons-data.php';
	$out   = array();
	foreach ( array_keys( $icons ) as $k ) {
		if ( 0 !== strpos( $icons[ $k ], 'brand:' ) ) {
			$out[ $k ] = $k;
		}
	}
	return $out;
}

/** URL for an attachment ID, or ''. */
function cwm_file_url( $id ) {
	$id = (int) $id;
	return $id ? (string) wp_get_attachment_url( $id ) : '';
}

/** Human file size of an attachment. */
function cwm_file_size( $id ) {
	$path = get_attached_file( (int) $id );
	return ( $path && file_exists( $path ) ) ? size_format( filesize( $path ), 1 ) : '';
}

/** WhatsApp click-to-chat link. */
function cwm_whatsapp_url( $text = '' ) {
	$n = preg_replace( '/\D+/', '', (string) cwm_opt( 'whatsapp' ) );
	if ( ! $n ) {
		return '';
	}
	return 'https://wa.me/' . $n . ( $text ? '?text=' . rawurlencode( $text ) : '' );
}

function cwm_tel_url() {
	return 'tel:' . preg_replace( '/[^\d+]/', '', (string) cwm_opt( 'phone' ) );
}

/** URL of a page by the template it uses (first match). */
function cwm_page_url( $template ) {
	$id = cwm_page_id( $template );
	return $id ? get_permalink( $id ) : home_url( '/' );
}

function cwm_page_id( $template ) {
	static $cache = array();
	if ( isset( $cache[ $template ] ) ) {
		return $cache[ $template ];
	}
	$ids = get_posts(
		array(
			'post_type'   => 'page',
			'post_status' => 'publish',
			'meta_key'    => '_wp_page_template',
			'meta_value'  => 'page-templates/' . $template . '.php',
			'fields'      => 'ids',
			'numberposts' => 1,
		)
	);
	$cache[ $template ] = $ids ? (int) $ids[0] : 0;
	return $cache[ $template ];
}

/** Insights listing URL (the posts page). */
function cwm_insights_url( $args = array() ) {
	$pid = (int) get_option( 'page_for_posts' );
	$url = $pid ? get_permalink( $pid ) : home_url( '/' );
	return $args ? add_query_arg( $args, $url ) : $url;
}

/** Reading time in minutes. */
function cwm_reading_time( $post_id = null ) {
	$post  = get_post( $post_id );
	$text  = $post->post_content;
	foreach ( array( 'issues', 'facts', 'decision' ) as $k ) {
		$text .= ' ' . cwm_meta( 'case_' . $k, $post->ID );
	}
	$words = str_word_count( wp_strip_all_tags( $text ) );
	return max( 1, (int) ceil( $words / 220 ) );
}

/** Primary category of a post (the most specific one). */
function cwm_primary_category( $post_id = null ) {
	$cats = get_the_category( $post_id );
	if ( ! $cats ) {
		return null;
	}
	usort(
		$cats,
		function ( $a, $b ) {
			return $b->parent <=> $a->parent;
		}
	);
	return $cats[0];
}

function cwm_is_case( $post_id = null ) {
	return has_category( 'case-analysis', $post_id );
}

/** Byline: "<Firm> Tax Team", or a named author only when the team switch is on. */
function cwm_byline( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$member  = cwm_visible_member( cwm_meta( 'named_author', $post_id ) );
	if ( $member ) {
		return array(
			'name' => get_the_title( $member ),
			'url'  => get_permalink( $member ),
		);
	}
	$team = cwm_meta( 'author_team', $post_id );
	if ( ! $team ) {
		$team = cwm_is_case( $post_id ) || has_category( 'tax-alerts', $post_id ) ? 'Tax Team' : ( has_category( 'esg-reporting', $post_id ) ? 'ESG Team' : 'Advisory Team' );
	}
	return array(
		'name' => trim( cwm_firm() . ' ' . $team ),
		'url'  => '',
	);
}

/** Team member ID if it may be shown publicly, else 0. */
function cwm_visible_member( $id ) {
	$id = (int) $id;
	if ( ! $id || ! cwm_team_on() ) {
		return 0;
	}
	$p = get_post( $id );
	if ( ! $p || 'team_member' !== $p->post_type || 'publish' !== $p->post_status || ! get_post_meta( $id, '_cwm_published', true ) ) {
		return 0;
	}
	return $id;
}

/**
 * Light formatting for textarea fields: blank line = paragraph, lines starting "- " = bullets.
 */
function cwm_format( $text ) {
	$text = trim( (string) $text );
	if ( '' === $text ) {
		return '';
	}
	$blocks = preg_split( "/\n\s*\n/", str_replace( "\r", '', $text ) );
	$html   = '';
	foreach ( $blocks as $block ) {
		$lines = explode( "\n", trim( $block ) );
		if ( preg_match( '/^[-*•]\s+/', $lines[0] ) ) {
			$html .= '<ul>';
			foreach ( $lines as $l ) {
				$html .= '<li>' . wp_kses_post( preg_replace( '/^[-*•]\s+/', '', $l ) ) . '</li>';
			}
			$html .= '</ul>';
		} else {
			$html .= '<p>' . wp_kses_post( implode( '<br>', $lines ) ) . '</p>';
		}
	}
	return $html;
}

/** Replace [Firm name] tokens in editable copy with the firm name from settings. */
function cwm_t( $text ) {
	return str_replace( array( '[Firm name]', '[firm name]' ), cwm_firm(), (string) $text );
}

/** Echo a primary call-to-action section used at the end of pages. */
function cwm_cta( $title = '', $text = '', $service_id = 0 ) {
	get_template_part(
		'template-parts/cta',
		null,
		array(
			'title'   => $title,
			'text'    => $text,
			'service' => $service_id,
		)
	);
}

/** Labels for case fields. */
function cwm_case_options( $field ) {
	$o = array(
		'court'   => array(
			'tat'  => 'Tax Appeals Tribunal',
			'hc'   => 'High Court',
			'coa'  => 'Court of Appeal',
			'sc'   => 'Supreme Court',
		),
		'taxtype' => array(
			'income-tax'      => 'Income tax',
			'vat'             => 'VAT',
			'paye'            => 'PAYE',
			'withholding-tax' => 'Withholding tax',
			'excise'          => 'Excise',
			'customs'         => 'Customs',
			'tax-procedure'   => 'Tax procedure',
		),
		'outcome' => array(
			'taxpayer' => 'Taxpayer succeeded',
			'kra'      => 'KRA succeeded',
			'partly'   => 'Partly allowed',
		),
	);
	return isset( $o[ $field ] ) ? $o[ $field ] : array();
}

function cwm_case_label( $field, $value ) {
	$o = cwm_case_options( $field );
	return isset( $o[ $value ] ) ? $o[ $value ] : '';
}

/** Group names for the homepage service cards. */
function cwm_groups() {
	return array(
		'money'       => 'Money',
		'obligations' => 'Obligations',
		'impact'      => 'Impact',
	);
}
