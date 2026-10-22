<?php
/**
 * Firm settings: the firm's details, the two switches, forms and email.
 */

defined( 'ABSPATH' ) || exit;

function cwm_settings_tabs() {
	return array(
		'firm'     => array(
			'title'  => 'Firm details',
			'fields' => array(
				array( 'key' => 'firm_name', 'label' => 'Firm name', 'help' => 'Used in the header, footer, page titles, bylines ("[Firm name] Tax Team") and structured data. Keep the words "audit" or "auditors" out of it; Switch A controls those.' ),
				array( 'key' => 'tagline', 'label' => 'Tagline' ),
				array( 'key' => 'description', 'label' => 'One-line description', 'type' => 'textarea', 'rows' => 2, 'help' => 'Shown in the footer and used as the homepage meta description.' ),
				array( 'key' => 'logo', 'label' => 'Logo (optional)', 'type' => 'media', 'library' => 'image', 'help' => 'Leave empty to use the built-in mark with the firm name. Upload an SVG or a PNG at least 400px wide.' ),
				array( 'key' => 'logo_footer', 'label' => 'Logo for dark backgrounds (optional)', 'type' => 'media', 'library' => 'image', 'help' => 'A white or light version for the navy footer.' ),
				array( 'key' => 'phone', 'label' => 'Phone' ),
				array( 'key' => 'email', 'label' => 'Public email address', 'type' => 'email' ),
				array( 'key' => 'whatsapp', 'label' => 'WhatsApp number', 'help' => 'International format without + or spaces, e.g. 254712345678. Leave empty to hide WhatsApp buttons.' ),
				array( 'key' => 'address', 'label' => 'Address', 'type' => 'textarea', 'rows' => 3 ),
				array( 'key' => 'hours', 'label' => 'Office hours', 'type' => 'textarea', 'rows' => 2 ),
				array( 'key' => 'map_query', 'label' => 'Map location', 'help' => 'An address or place name as you would type it into Google Maps. Leave empty to hide the map.' ),
				array( 'key' => 'linkedin', 'label' => 'LinkedIn page URL', 'type' => 'url' ),
				array( 'key' => 'x', 'label' => 'X (Twitter) URL', 'type' => 'url' ),
				array( 'key' => 'facebook', 'label' => 'Facebook URL', 'type' => 'url' ),
				array( 'key' => 'firm_profile', 'label' => 'Firm profile (PDF)', 'type' => 'media', 'library' => 'application/pdf', 'help' => 'Powers the "Download firm profile" buttons on About, in the footer and on the homepage. The buttons hide when this is empty.' ),
				array( 'key' => 'licence_line', 'label' => 'Licence line (footer)', 'help' => 'Shown at the bottom of every page only when filled in, e.g. a regulator and licence number.' ),
				array( 'key' => 'disclaimer', 'label' => 'Article disclaimer', 'type' => 'textarea', 'rows' => 3, 'help' => 'Shown at the end of every insight and case analysis.' ),
			),
		),
		'switches' => array(
			'title'  => 'Switches',
			'fields' => array(
				array(
					'key'     => 'audit_state',
					'label'   => 'Switch A: Audit and assurance',
					'type'    => 'select',
					'options' => array(
						'hidden'  => 'Hidden: no audit page, links, form option or wording anywhere',
						'partner' => 'Partner: page live with the partner firm notice',
						'live'    => 'Live: page live as our own service, with licence details',
					),
					'help'    => 'Updates the menu, homepage cards, footer, forms, search, XML sitemap and structured data automatically. ESG wording about assurance or verification follows this switch too.',
				),
				array( 'key' => 'audit_partner', 'label' => 'Licensed audit firm (Partner state)', 'help' => 'Fills the notice: "Audit and assurance engagements are carried out by <em>this firm</em>, licensed by ICPAK."' ),
				array( 'key' => 'audit_licence', 'label' => 'Our audit licence details (Live state)', 'help' => 'For example: "Licensed by ICPAK, practising certificate no. ..."' ),
				array( 'key' => 'show_team', 'label' => 'Switch B: Show team section', 'type' => 'checkbox', 'help' => 'Off: no names, photos, titles or biographies anywhere; team links, pages, key contact blocks and named authors are hidden and team URLs redirect to About. On: each person appears only if their own "Published on site" box is ticked.' ),
				array( 'key' => 'author_teams', 'label' => 'Byline teams', 'type' => 'textarea', 'rows' => 4, 'help' => 'One per line. Articles show "[Firm name] Tax Team" and so on, never a person\'s name while the team switch is off.' ),
			),
		),
		'preview'  => array(
			'title'  => 'Preview and launch',
			'fields' => array(
				array( 'key' => 'preview_on', 'label' => 'Preview mode: the site is not public yet', 'type' => 'checkbox', 'help' => 'Keeps search engines out (noindex, robots.txt, no sitemap), shows a slim "Preview" banner on every page and marks form emails "[Preview]". Untick this on launch day.' ),
				array( 'key' => 'preview_password', 'label' => 'Preview password (optional)', 'help' => 'When set, visitors must type this once before they can see the site (remembered for 30 days on their device). Logged-in users skip it. Share it with the client together with the link. Leave empty to let anyone with the link look.' ),
				array( 'key' => 'preview_note', 'label' => 'Banner text (optional)', 'placeholder' => 'This is a preview of the new website. It is not public yet and search engines cannot see it.' ),
				array( 'key' => 'preview_feedback', 'label' => 'Feedback goes to (optional)', 'help' => 'An email address, a WhatsApp number (e.g. +254712345678) or a link. Adds "Send feedback on this page" to the banner, with the page address filled in.' ),
			),
		),
		'forms'    => array(
			'title'  => 'Forms and email',
			'fields' => array(
				array( 'key' => 'email_contact', 'label' => 'Send contact and service enquiries to', 'help' => 'A shared address such as info@ or enquiries@. Separate several with commas. Leave empty to use the public email address.' ),
				array( 'key' => 'email_proposals', 'label' => 'Send proposal requests to', 'help' => 'Leave empty to use the address above.' ),
				array( 'key' => 'email_newsletter', 'label' => 'Notify about newsletter sign-ups', 'help' => 'Optional. Every sign-up is also saved under Enquiries, where you can export a CSV.' ),
				array( 'key' => 'autoreply', 'label' => 'Send a short confirmation email to people who submit a form', 'type' => 'checkbox' ),
				array( 'key' => 'newsletter_text', 'label' => 'Newsletter line (footer)' ),
				array( 'key' => 'ga_id', 'label' => 'Google Analytics 4 measurement ID (optional)', 'placeholder' => 'G-XXXXXXXXXX', 'help' => 'When set, a cookie banner appears and analytics loads only after the visitor accepts, as the Data Protection Act requires.' ),
			),
		),
	);
}

add_action(
	'admin_menu',
	function () {
		add_menu_page( 'Firm settings', 'Firm settings', 'manage_options', 'cwm-settings', 'cwm_settings_page', 'dashicons-building', 3 );
	}
);

add_action(
	'admin_init',
	function () {
		register_setting(
			'cwm_settings',
			'cwm_settings',
			array(
				'type'              => 'array',
				'sanitize_callback' => 'cwm_sanitize_settings',
			)
		);
	}
);

function cwm_sanitize_settings( $input ) {
	$current = (array) get_option( 'cwm_settings', array() );
	$tab     = isset( $_POST['cwm_tab'] ) ? sanitize_key( wp_unslash( $_POST['cwm_tab'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$tabs    = cwm_settings_tabs();
	if ( ! isset( $tabs[ $tab ] ) ) {
		return is_array( $input ) ? array_merge( $current, $input ) : $current;
	}
	foreach ( $tabs[ $tab ]['fields'] as $f ) {
		$raw                   = isset( $input[ $f['key'] ] ) ? $input[ $f['key'] ] : '';
		$current[ $f['key'] ] = cwm_sanitize_field( $f, is_string( $raw ) ? wp_slash( $raw ) : $raw );
	}
	return $current;
}

/** Keep the WordPress site title in step with the firm name and tagline. */
add_action(
	'update_option_cwm_settings',
	function ( $old, $new ) {
		$new = wp_parse_args( (array) $new, cwm_setting_defaults() );
		$old = wp_parse_args( (array) $old, cwm_setting_defaults() );
		update_option( 'blogname', $new['firm_name'] );
		update_option( 'blogdescription', $new['tagline'] );
		if ( $old['show_team'] !== $new['show_team'] || $old['audit_state'] !== $new['audit_state'] ) {
			update_option( 'cwm_flush_rewrite', 1 );
		}
	},
	10,
	2
);

function cwm_settings_page() {
	$tabs = cwm_settings_tabs();
	$tab  = isset( $_GET['tab'] ) && isset( $tabs[ $_GET['tab'] ] ) ? sanitize_key( $_GET['tab'] ) : 'firm'; // phpcs:ignore WordPress.Security.NonceVerification
	$opts = wp_parse_args( (array) get_option( 'cwm_settings', array() ), cwm_setting_defaults() );
	echo '<div class="wrap cwm-settings"><h1>Firm settings</h1>';
	settings_errors();
	echo '<nav class="nav-tab-wrapper">';
	foreach ( $tabs as $k => $t ) {
		echo '<a class="nav-tab' . ( $k === $tab ? ' nav-tab-active' : '' ) . '" href="' . esc_url( admin_url( 'admin.php?page=cwm-settings&tab=' . $k ) ) . '">' . esc_html( $t['title'] ) . '</a>';
	}
	echo '</nav>';
	if ( 'switches' === $tab ) {
		cwm_switch_summary();
	}
	echo '<form method="post" action="options.php" class="cwm-settings__form">';
	settings_fields( 'cwm_settings' );
	echo '<input type="hidden" name="cwm_tab" value="' . esc_attr( $tab ) . '">';
	foreach ( $tabs[ $tab ]['fields'] as $f ) {
		cwm_render_field( $f, isset( $opts[ $f['key'] ] ) ? $opts[ $f['key'] ] : '', 'cwm_settings[' . $f['key'] . ']' );
	}
	submit_button( 'Save settings' );
	echo '</form></div>';
}

/** Plain-language summary of what the public sees right now. */
function cwm_switch_summary() {
	$a     = cwm_audit_state();
	$team  = cwm_team_on();
	$audit = cwm_audit_service_id();
	$lines = array(
		'hidden'  => 'Audit and assurance is <strong>hidden</strong>. The public sees no audit page, menu item, card, footer link, form option or sitemap entry.',
		'partner' => 'Audit and assurance is live in <strong>partner</strong> mode, with the notice naming ' . esc_html( cwm_opt( 'audit_partner' ) ) . '.',
		'live'    => 'Audit and assurance is <strong>live</strong> as your own service' . ( cwm_opt( 'audit_licence' ) ? ', showing your licence details.' : '. Add your licence details below.' ),
	);
	echo '<div class="notice notice-info inline cwm-summary"><p><strong>Right now:</strong> ' . wp_kses_post( $lines[ $a ] ) . '</p>';
	if ( ! $audit ) {
		echo '<p>No service is marked as the audit service yet. Open the Audit and assurance service and tick "This is the audit service".</p>';
	}
	if ( $team ) {
		$count = count(
			get_posts(
				array(
					'post_type'   => 'team_member',
					'post_status' => 'publish',
					'meta_key'    => '_cwm_published',
					'meta_value'  => '1',
					'numberposts' => -1,
					'fields'      => 'ids',
				)
			)
		);
		echo '<p>The team section is <strong>on</strong>. ' . esc_html( $count ) . ' published team member(s) are visible.</p>';
	} else {
		echo '<p>The team section is <strong>off</strong>. No staff names, photos or profiles appear anywhere, and team links redirect to About.</p>';
	}
	echo '</div>';
}
