<?php
/**
 * Admin conveniences: launch checklist, sample-content removal, tidy menus.
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'admin_menu',
	function () {
		add_submenu_page( 'cwm-settings', 'Firm settings', 'Settings and switches', 'manage_options', 'cwm-settings', 'cwm_settings_page' );
		add_submenu_page( 'cwm-settings', 'Launch checklist', 'Launch checklist', 'manage_options', 'cwm-launch', 'cwm_launch_page' );
	},
	20
);

/** Sample items still in the database. */
function cwm_sample_ids() {
	return get_posts(
		array(
			'post_type'   => array( 'post', 'resource' ),
			'post_status' => 'any',
			'meta_key'    => '_cwm_sample',
			'meta_value'  => '1',
			'numberposts' => -1,
			'fields'      => 'ids',
		)
	);
}

function cwm_launch_checks() {
	$d        = cwm_setting_defaults();
	$services = get_posts(
		array(
			'post_type'   => 'service',
			'numberposts' => -1,
		)
	);
	$with_pdf = 0;
	foreach ( $services as $s ) {
		if ( cwm_file_url( cwm_meta( 'pdf', $s->ID ) ) ) {
			++$with_pdf;
		}
	}
	$team_all = get_posts(
		array(
			'post_type'   => 'team_member',
			'post_status' => 'any',
			'numberposts' => -1,
			'fields'      => 'ids',
		)
	);
	$smtp     = defined( 'WPMS_PLUGIN_VER' ) || class_exists( 'FluentMail\App\App' ) || defined( 'POST_SMTP_VER' ) || function_exists( 'wp_mail_smtp' ) || class_exists( 'EasyWPSMTP' );
	$sample   = count( cwm_sample_ids() );
	$s        = admin_url( 'admin.php?page=cwm-settings' );

	return array(
		array( cwm_firm() !== $d['firm_name'], 'Firm name set', 'Currently "' . cwm_firm() . '".', $s ),
		array( cwm_opt( 'phone' ) !== $d['phone'] && cwm_opt( 'email' ) !== $d['email'], 'Real phone and email', 'Shown in the top bar, footer and contact page.', $s ),
		array( (bool) cwm_opt( 'email_contact' ), 'Shared address for form emails', 'Forms go to a shared inbox only, never to individuals.', $s . '&tab=forms' ),
		array( $smtp, 'Email sending plugin (SMTP)', 'Install "WP Mail SMTP" or "FluentSMTP" and connect it to the firm\'s mailbox so form emails are not marked as spam. Every enquiry is also saved under Enquiries.', admin_url( 'plugin-install.php?s=smtp&tab=search' ) ),
		array( (bool) cwm_opt( 'logo' ), 'Logo uploaded (optional)', 'Without one, the built-in mark and the firm name are used.', $s ),
		array( (bool) cwm_file_url( cwm_opt( 'firm_profile' ) ), 'Firm profile PDF (optional)', 'The download buttons stay hidden until it is uploaded.', $s ),
		array( 0 === $sample, 'Sample insights removed', $sample ? $sample . ' sample item(s) are still published. Use the button below once your own articles are in.' : 'None left.', '' ),
		array( 'hidden' === cwm_audit_state(), 'Audit switch set to Hidden for launch', 'Current state: ' . cwm_audit_state() . '.', $s . '&tab=switches' ),
		array( ! cwm_team_on(), 'Team section off for launch', cwm_team_on() ? 'It is on.' : 'Off. No names or photos appear anywhere.', $s . '&tab=switches' ),
		array( 0 === count( $team_all ) || cwm_team_on(), 'No dummy team profiles in the database', count( $team_all ) . ' team member record(s) exist. Delete any test profiles before launch.', admin_url( 'edit.php?post_type=team_member' ) ),
		array( count( $services ) === $with_pdf, 'Service profile PDFs (optional)', $with_pdf . ' of ' . count( $services ) . ' services have a PDF. Buttons hide where there is none.', admin_url( 'edit.php?post_type=service' ) ),
		array( (bool) get_option( 'blog_public' ), 'Search engines allowed', 'Settings > Reading > untick "Discourage search engines" when you go live.', admin_url( 'options-reading.php' ) ),
		array( is_ssl() || 0 === strpos( home_url(), 'https://' ), 'Site served over HTTPS', 'Run AutoSSL in cPanel, then set both addresses in Settings > General to https://.', admin_url( 'options-general.php' ) ),
	);
}

function cwm_launch_page() {
	if ( isset( $_POST['cwm_delete_samples'] ) && check_admin_referer( 'cwm_delete_samples' ) && current_user_can( 'delete_posts' ) ) {
		$n = 0;
		foreach ( cwm_sample_ids() as $id ) {
			$att = (int) get_post_meta( $id, '_cwm_file', true );
			if ( $att && get_post_meta( $att, '_cwm_sample', true ) ) {
				wp_delete_attachment( $att, true );
			}
			if ( wp_delete_post( $id, true ) ) {
				++$n;
			}
		}
		echo '<div class="notice notice-success"><p>' . esc_html( $n ) . ' sample item(s) deleted.</p></div>';
	}
	echo '<div class="wrap cwm-settings"><h1>Launch checklist</h1>';
	echo '<p>Everything the site needs before it goes live. Green items are done.</p>';
	echo '<table class="widefat striped cwm-checklist-table"><tbody>';
	foreach ( cwm_launch_checks() as $c ) {
		echo '<tr><td style="width:28px">' . ( $c[0] ? '<span class="dashicons dashicons-yes-alt" style="color:#1E8E8A"></span>' : '<span class="dashicons dashicons-marker" style="color:#C8962E"></span>' ) . '</td>';
		echo '<td><strong>' . esc_html( $c[1] ) . '</strong><br><span class="description">' . esc_html( $c[2] ) . '</span></td>';
		echo '<td style="width:120px">' . ( $c[3] ? '<a class="button" href="' . esc_url( $c[3] ) . '">Open</a>' : '' ) . '</td></tr>';
	}
	echo '</tbody></table>';
	if ( cwm_sample_ids() ) {
		echo '<form method="post" style="margin-top:20px">';
		wp_nonce_field( 'cwm_delete_samples' );
		echo '<p>Sample insights and downloads were added so every layout could be seen. Delete them once your own content is in.</p>';
		echo '<button class="button button-primary" name="cwm_delete_samples" value="1" onclick="return confirm(\'Delete all sample insights and downloads permanently?\')">Delete sample content</button>';
		echo '</form>';
	}
	echo '<h2 style="margin-top:32px">Where things live</h2><ul class="ul-disc">';
	echo '<li><strong>Firm settings</strong>: name, contacts, logo, firm profile PDF, licence line and the two switches.</li>';
	echo '<li><strong>Pages > Home</strong>: every homepage block (hero, promises, statistics, featured download, testimonials switch, closing block).</li>';
	echo '<li><strong>Services</strong>: each service page, its PDF, FAQs and key contact.</li>';
	echo '<li><strong>Insights</strong>: articles, tax alerts and case analyses. Tick "Case analysis" as a category to get the case template. Pin the homepage feature from the article\'s Insight details box.</li>';
	echo '<li><strong>Article series, ESG topics, ESG glossary, Downloads</strong>: the ESG reporting hub.</li>';
	echo '<li><strong>Team</strong>: profiles, hidden until the team switch is on and each person is published.</li>';
	echo '<li><strong>Enquiries</strong>: every form submission, with CSV export.</li>';
	echo '</ul></div>';
}

/** Dashboard nudge while the checklist is incomplete. */
add_action(
	'admin_notices',
	function () {
		$screen = get_current_screen();
		if ( ! $screen || 'dashboard' !== $screen->id || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$open = array_filter(
			cwm_launch_checks(),
			function ( $c ) {
				return ! $c[0];
			}
		);
		if ( $open ) {
			echo '<div class="notice notice-info"><p><strong>' . count( $open ) . ' launch item(s) open.</strong> <a href="' . esc_url( admin_url( 'admin.php?page=cwm-launch' ) ) . '">Open the launch checklist</a></p></div>';
		}
	}
);

/** Services list: show order, group and role. */
add_filter(
	'manage_service_posts_columns',
	function ( $cols ) {
		$cols['cwm_group'] = 'Homepage group';
		$cols['cwm_role']  = 'Role';
		return $cols;
	}
);
add_action(
	'manage_service_posts_custom_column',
	function ( $col, $id ) {
		if ( 'cwm_group' === $col ) {
			$g = cwm_groups();
			echo esc_html( isset( $g[ cwm_meta( 'group', $id ) ] ) ? $g[ cwm_meta( 'group', $id ) ] : '' );
		} elseif ( 'cwm_role' === $col ) {
			$r = cwm_meta( 'role', $id );
			if ( 'audit' === $r ) {
				echo 'Audit (Switch A: <strong>' . esc_html( cwm_audit_state() ) . '</strong>)';
			} else {
				echo esc_html( ucfirst( $r ) );
			}
		}
	},
	10,
	2
);
add_action(
	'pre_get_posts',
	function ( $q ) {
		if ( is_admin() && $q->is_main_query() && in_array( $q->get( 'post_type' ), array( 'service', 'industry', 'resource', 'team_member', 'testimonial' ), true ) && ! $q->get( 'orderby' ) ) {
			$q->set( 'orderby', 'menu_order title' );
			$q->set( 'order', 'ASC' );
		}
	}
);

/** Team list: published-on-site column. */
add_filter(
	'manage_team_member_posts_columns',
	function ( $cols ) {
		$cols['cwm_pub'] = 'On site';
		return $cols;
	}
);
add_action(
	'manage_team_member_posts_custom_column',
	function ( $col, $id ) {
		if ( 'cwm_pub' === $col ) {
			echo get_post_meta( $id, '_cwm_published', true ) ? ( cwm_team_on() ? 'Yes' : 'Yes, when the team section is on' ) : 'No';
		}
	},
	10,
	2
);

/** Notice on the team screens while Switch B is off. */
add_action(
	'admin_notices',
	function () {
		$screen = get_current_screen();
		if ( $screen && 'team_member' === $screen->post_type && ! cwm_team_on() ) {
			echo '<div class="notice notice-warning"><p>The team section is <strong>off</strong>. Nothing here is visible on the website. Turn it on in <a href="' . esc_url( admin_url( 'admin.php?page=cwm-settings&tab=switches' ) ) . '">Firm settings > Switches</a>.</p></div>';
		}
		if ( $screen && 'service' === $screen->post_type && 'post' === $screen->base ) {
			global $post;
			if ( $post && 'audit' === cwm_meta( 'role', $post->ID ) ) {
				echo '<div class="notice notice-info"><p>This page follows Switch A, now <strong>' . esc_html( cwm_audit_state() ) . '</strong>. Change it in <a href="' . esc_url( admin_url( 'admin.php?page=cwm-settings&tab=switches' ) ) . '">Firm settings > Switches</a>.</p></div>';
			}
		}
	}
);

/** Block editor only where it helps (articles, pages, services). */
add_filter(
	'use_block_editor_for_post_type',
	function ( $use, $type ) {
		return in_array( $type, array( 'post', 'page', 'service', 'team_member' ), true ) ? $use : false;
	},
	10,
	2
);
