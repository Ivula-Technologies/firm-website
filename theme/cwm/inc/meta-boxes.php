<?php
/**
 * Editable fields for services, industries, insights, pages and the rest.
 */

defined( 'ABSPATH' ) || exit;

function cwm_fmt_help() {
	return 'Leave a blank line between paragraphs. Start a line with "- " to make a bullet.';
}

function cwm_team_options() {
	$out = array();
	foreach ( preg_split( '/\r?\n/', (string) cwm_opt( 'author_teams' ) ) as $t ) {
		$t = trim( $t );
		if ( $t ) {
			$out[ $t ] = cwm_firm() . ' ' . $t;
		}
	}
	return $out;
}

function cwm_seo_field() {
	return array( 'key' => 'seo_description', 'label' => 'Search description', 'type' => 'textarea', 'rows' => 2, 'help' => 'About 150 characters for Google and link previews. Leave empty to use the summary.' );
}

/** Every meta box, keyed by ID. */
function cwm_meta_boxes() {
	$boxes = array();

	$boxes['cwm_service'] = array(
		'title'  => 'Service page content',
		'screen' => 'service',
		'fields' => array(
			array(
				'key'     => 'role',
				'label'   => 'Special role',
				'type'    => 'select',
				'options' => array(
					''      => 'None',
					'audit' => 'This is the audit service (controlled by Switch A)',
					'tax'   => 'This is the Tax service (shows "Recent tax decisions")',
					'esg'   => 'This is the ESG service (linked from the ESG reporting hub)',
				),
			),
			array( 'key' => 'group', 'label' => 'Homepage group', 'type' => 'select', 'options' => cwm_groups() ),
			array( 'key' => 'icon', 'label' => 'Icon', 'type' => 'icon' ),
			array( 'key' => 'promise', 'label' => 'One-line promise', 'help' => 'Shown in the banner and on the service card.' ),
			array( 'key' => 'who_for', 'label' => 'Who it is for', 'type' => 'textarea', 'rows' => 3, 'help' => 'The overview. The main text box above adds more detail under it if you need it.' ),
			array(
				'key'       => 'subservices',
				'label'     => 'What we do',
				'type'      => 'repeater',
				'add_label' => 'Add sub-service',
				'fields'    => array(
					array( 'key' => 'title', 'label' => 'Sub-service' ),
					array( 'key' => 'text', 'label' => 'One or two sentences', 'type' => 'textarea', 'rows' => 2 ),
					array( 'key' => 'assurance', 'label' => 'Offers assurance or verification (hidden while Switch A is Hidden)', 'type' => 'checkbox' ),
				),
			),
			array(
				'key'       => 'steps',
				'label'     => 'How we work (three to five steps)',
				'type'      => 'repeater',
				'max'       => 5,
				'add_label' => 'Add step',
				'fields'    => array(
					array( 'key' => 'title', 'label' => 'Step' ),
					array( 'key' => 'text', 'label' => 'Description', 'type' => 'textarea', 'rows' => 2 ),
				),
			),
			array( 'key' => 'pdf', 'label' => 'Service profile (PDF)', 'type' => 'media', 'library' => 'application/pdf', 'help' => 'The "Download service profile" button only shows when a file is chosen.' ),
			array(
				'key'       => 'faqs',
				'label'     => 'FAQs',
				'type'      => 'repeater',
				'add_label' => 'Add question',
				'fields'    => array(
					array( 'key' => 'q', 'label' => 'Question' ),
					array( 'key' => 'a', 'label' => 'Answer', 'type' => 'textarea', 'rows' => 3 ),
				),
			),
			array( 'key' => 'key_contact', 'label' => 'Key contact (optional)', 'type' => 'post', 'post_type' => 'team_member', 'help' => 'Shows only while the team section is on and this person is published.' ),
			cwm_seo_field(),
		),
	);

	$boxes['cwm_industry'] = array(
		'title'  => 'Industry details',
		'screen' => 'industry',
		'fields' => array(
			array( 'key' => 'icon', 'label' => 'Icon', 'type' => 'icon' ),
			array( 'key' => 'summary', 'label' => 'Short paragraph', 'type' => 'textarea', 'rows' => 4 ),
			array( 'key' => 'services', 'label' => 'Relevant services', 'type' => 'posts', 'post_type' => 'service', 'help' => 'The audit service is left out automatically while Switch A is Hidden.' ),
		),
	);

	$boxes['cwm_resource'] = array(
		'title'  => 'Download details',
		'screen' => 'resource',
		'fields' => array(
			array( 'key' => 'file', 'label' => 'File', 'type' => 'media', 'help' => 'PDF, Word or Excel. The download is hidden until a file is chosen.' ),
			array(
				'key'     => 'kind',
				'label'   => 'Type',
				'type'    => 'select',
				'options' => array(
					'publication' => 'Publication',
					'checklist'   => 'Checklist',
					'template'    => 'Template',
					'guide'       => 'Guide',
				),
			),
			array( 'key' => 'summary', 'label' => 'Short description', 'type' => 'textarea', 'rows' => 3 ),
			array( 'key' => 'esg_hub', 'label' => 'Show in the ESG reporting hub', 'type' => 'checkbox' ),
		),
	);

	$boxes['cwm_testimonial'] = array(
		'title'  => 'Testimonial details',
		'screen' => 'testimonial',
		'fields' => array(
			array( 'key' => 'role', 'label' => 'Role and organisation', 'help' => 'The quote goes in the main text box; the title is the client\'s name or organisation.' ),
		),
	);

	$boxes['cwm_team'] = array(
		'title'  => 'Profile',
		'screen' => 'team_member',
		'fields' => array(
			array( 'key' => 'published', 'label' => 'Published on site (only takes effect while the team section is on)', 'type' => 'checkbox' ),
			array( 'key' => 'job_title', 'label' => 'Job title' ),
			array( 'key' => 'qualifications', 'label' => 'Qualifications', 'placeholder' => 'CPA (K), ACCA' ),
			array( 'key' => 'expertise', 'label' => 'Expertise', 'type' => 'textarea', 'rows' => 2, 'help' => 'One area per line.' ),
			array( 'key' => 'email', 'label' => 'Email', 'type' => 'email' ),
			array( 'key' => 'phone', 'label' => 'Phone' ),
			array( 'key' => 'linkedin', 'label' => 'LinkedIn URL', 'type' => 'url' ),
		),
	);

	$boxes['cwm_post'] = array(
		'title'  => 'Insight details',
		'screen' => 'post',
		'fields' => array(
			array( 'key' => 'featured', 'label' => 'Pin as the featured insight on the homepage (replaces any earlier pin)', 'type' => 'checkbox' ),
			array( 'key' => 'start_here', 'label' => 'Pin to "Start here" in the ESG reporting hub', 'type' => 'checkbox' ),
			array( 'key' => 'author_team', 'label' => 'Byline', 'type' => 'select', 'options' => cwm_team_options(), 'empty' => 'Automatic from category' ),
			array( 'key' => 'named_author', 'label' => 'Named author (optional)', 'type' => 'post', 'post_type' => 'team_member', 'help' => 'Shows only while the team section is on and the person is published; otherwise the team byline is used.' ),
			array( 'key' => 'series_part', 'label' => 'Part number in its series', 'type' => 'number', 'help' => 'Pick the series in the "Article series" box, then number the part here.' ),
			array( 'key' => 'pdf', 'label' => 'PDF download (optional)', 'type' => 'media', 'library' => 'application/pdf' ),
			cwm_seo_field(),
		),
	);

	$boxes['cwm_case'] = array(
		'title'  => 'Case analysis (used when the category is "Case analysis")',
		'screen' => 'post',
		'intro'  => 'Fill these in for a decided tax case. The main text box above is "Our analysis". The page shows: case details, what this means for you, the issues, the facts, the decision, our analysis, the PDF and related cases.',
		'fields' => array(
			array( 'key' => 'case_name', 'label' => 'Case name', 'placeholder' => 'ABC Limited v Commissioner of Domestic Taxes' ),
			array( 'key' => 'case_citation', 'label' => 'Citation', 'placeholder' => 'TAT Appeal No. 000 of 2026' ),
			array( 'key' => 'case_court', 'label' => 'Court or tribunal', 'type' => 'select', 'options' => cwm_case_options( 'court' ), 'empty' => '— Choose —' ),
			array( 'key' => 'case_date', 'label' => 'Date of decision', 'type' => 'date' ),
			array( 'key' => 'case_taxtype', 'label' => 'Tax type', 'type' => 'select', 'options' => cwm_case_options( 'taxtype' ), 'empty' => '— Choose —' ),
			array( 'key' => 'case_outcome', 'label' => 'Outcome', 'type' => 'select', 'options' => cwm_case_options( 'outcome' ), 'empty' => '— Choose —' ),
			array( 'key' => 'case_link', 'label' => 'Link to the full decision', 'type' => 'url', 'placeholder' => 'https://new.kenyalaw.org/...' ),
			array( 'key' => 'case_means', 'label' => 'What this means for you (three bullets, one per line)', 'type' => 'textarea', 'rows' => 3 ),
			array( 'key' => 'case_issues', 'label' => 'The issues', 'type' => 'textarea', 'rows' => 4, 'help' => cwm_fmt_help() ),
			array( 'key' => 'case_facts', 'label' => 'The facts in brief', 'type' => 'textarea', 'rows' => 4, 'help' => cwm_fmt_help() ),
			array( 'key' => 'case_decision', 'label' => 'The decision', 'type' => 'textarea', 'rows' => 4, 'help' => cwm_fmt_help() ),
		),
	);

	// Pages.
	$boxes['cwm_home'] = array(
		'title'    => 'Homepage content',
		'screen'   => 'page',
		'template' => 'front',
		'fields'   => array(
			array( 'key' => 'hero_title', 'label' => 'Hero headline', 'default' => 'Count what matters.' ),
			array( 'key' => 'hero_text', 'label' => 'Hero sentence', 'type' => 'textarea', 'rows' => 2, 'default' => 'Accounting, tax, advisory and ESG support for Kenyan businesses that want to grow, and to do it right.' ),
			array( 'key' => 'hero_image', 'label' => 'Hero image', 'type' => 'media', 'library' => 'image', 'help' => 'One landscape image, at least 1600px wide. Leave empty to use the built-in Nairobi illustration. No photos of identifiable staff.' ),
			array( 'key' => 'services_title', 'label' => 'Services heading', 'default' => 'Three things to account for' ),
			array( 'key' => 'services_text', 'label' => 'Services introduction', 'type' => 'textarea', 'rows' => 2, 'default' => 'Every business has its money, its obligations and its impact to answer for. We help with all three.' ),
			array( 'key' => 'group_money', 'label' => 'Money: line under the heading', 'default' => 'What comes in, what goes out, and who gets paid.' ),
			array( 'key' => 'group_obligations', 'label' => 'Obligations: line under the heading', 'default' => 'What the law, KRA and your stakeholders expect of you.' ),
			array( 'key' => 'group_impact', 'label' => 'Impact: line under the heading', 'default' => 'Where the business is going, and what it leaves behind.' ),
			array( 'key' => 'why_title', 'label' => 'Why choose us heading', 'default' => 'Our four promises' ),
			array(
				'key'       => 'promises',
				'label'     => 'Promises (three or four)',
				'type'      => 'repeater',
				'max'       => 4,
				'add_label' => 'Add promise',
				'fields'    => array(
					array( 'key' => 'icon', 'label' => 'Icon', 'type' => 'icon' ),
					array( 'key' => 'title', 'label' => 'Title' ),
					array( 'key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 2 ),
				),
			),
			array( 'key' => 'stats_show', 'label' => 'Show the statistics strip', 'type' => 'checkbox' ),
			array(
				'key'       => 'stats',
				'label'     => 'Statistics (up to four)',
				'type'      => 'repeater',
				'max'       => 4,
				'add_label' => 'Add figure',
				'fields'    => array(
					array( 'key' => 'figure', 'label' => 'Figure', 'placeholder' => '120+' ),
					array( 'key' => 'label', 'label' => 'Label', 'placeholder' => 'clients served' ),
				),
			),
			array( 'key' => 'insights_title', 'label' => 'Latest insights heading', 'default' => 'Latest insights' ),
			array( 'key' => 'download', 'label' => 'Featured download', 'type' => 'post', 'post_type' => 'resource', 'help' => 'Pick one item from Downloads. The block hides if it has no file.' ),
			array( 'key' => 'industries_title', 'label' => 'Industries heading', 'default' => 'Industries we serve' ),
			array( 'key' => 'testimonials_show', 'label' => 'Show testimonials', 'type' => 'checkbox' ),
			array( 'key' => 'testimonials_title', 'label' => 'Testimonials heading', 'default' => 'What our clients say' ),
			array( 'key' => 'closing_title', 'label' => 'Closing heading', 'default' => 'Let\'s look at your numbers together.' ),
			array( 'key' => 'closing_text', 'label' => 'Closing text', 'type' => 'textarea', 'rows' => 2, 'default' => 'Tell us where you are and what is worrying you. We will reply within one working day, in plain language.' ),
			cwm_seo_field(),
		),
	);

	$boxes['cwm_about'] = array(
		'title'    => 'Who we are',
		'screen'   => 'page',
		'template' => 'about',
		'intro'    => 'The main text box above is the firm\'s story.',
		'fields'   => array(
			array( 'key' => 'intro', 'label' => 'Banner line', 'default' => 'A Kenyan professional services firm that counts what matters.' ),
			array(
				'key'    => 'three',
				'label'  => 'The three things we count',
				'type'   => 'repeater',
				'max'    => 3,
				'fields' => array(
					array( 'key' => 'icon', 'label' => 'Icon', 'type' => 'icon' ),
					array( 'key' => 'title', 'label' => 'Title' ),
					array( 'key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 2 ),
				),
			),
			array( 'key' => 'mission', 'label' => 'Mission', 'type' => 'textarea', 'rows' => 2 ),
			array( 'key' => 'vision', 'label' => 'Vision', 'type' => 'textarea', 'rows' => 2 ),
			array(
				'key'    => 'values',
				'label'  => 'Values',
				'type'   => 'repeater',
				'fields' => array(
					array( 'key' => 'icon', 'label' => 'Icon', 'type' => 'icon' ),
					array( 'key' => 'title', 'label' => 'Value' ),
					array( 'key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 2 ),
				),
			),
			cwm_seo_field(),
		),
	);

	$boxes['cwm_approach'] = array(
		'title'    => 'Our approach',
		'screen'   => 'page',
		'template' => 'approach',
		'intro'    => 'The main text box above appears under the steps.',
		'fields'   => array(
			array( 'key' => 'intro', 'label' => 'Banner line' ),
			array(
				'key'    => 'steps',
				'label'  => 'Steps',
				'type'   => 'repeater',
				'fields' => array(
					array( 'key' => 'icon', 'label' => 'Icon', 'type' => 'icon' ),
					array( 'key' => 'title', 'label' => 'Step' ),
					array( 'key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 2 ),
				),
			),
			cwm_seo_field(),
		),
	);

	$boxes['cwm_esg'] = array(
		'title'    => 'ESG reporting hub',
		'screen'   => 'page',
		'template' => 'esg-hub',
		'intro'    => 'The main text box above is the introduction. Articles come from the "ESG reporting" category; pin "Start here" articles from each article\'s Insight details box; add glossary terms under ESG glossary and checklists under Downloads.',
		'fields'   => array(
			array( 'key' => 'intro', 'label' => 'Banner line' ),
			cwm_seo_field(),
		),
	);

	foreach ( array( 'services' => 'Services overview', 'industries' => 'Industries', 'contact' => 'Contact', 'proposal' => 'Request a proposal', 'team' => 'Our team' ) as $tpl => $title ) {
		$boxes[ 'cwm_' . $tpl ] = array(
			'title'    => $title . ' page',
			'screen'   => 'page',
			'template' => $tpl,
			'fields'   => array(
				array( 'key' => 'intro', 'label' => 'Banner line' ),
				cwm_seo_field(),
			),
		);
	}

	$boxes['cwm_page_cta'] = array(
		'title'    => 'Closing call to action',
		'screen'   => 'page',
		'context'  => 'side',
		'fields'   => array(
			array( 'key' => 'cta_title', 'label' => 'Heading', 'placeholder' => 'Let\'s look at your numbers together.' ),
			array( 'key' => 'cta_text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3 ),
		),
	);

	$boxes['cwm_page_seo'] = array(
		'title'    => 'Search description',
		'screen'   => 'page',
		'template' => 'default',
		'context'  => 'side',
		'fields'   => array( cwm_seo_field() ),
	);

	return $boxes;
}

/** Which template key a page uses, for deciding which boxes to show. */
function cwm_page_template_key( $post ) {
	if ( (int) get_option( 'page_on_front' ) === (int) $post->ID ) {
		return 'front';
	}
	$tpl = get_page_template_slug( $post );
	if ( ! $tpl ) {
		return 'default';
	}
	return str_replace( array( 'page-templates/', '.php' ), '', $tpl );
}

add_action(
	'add_meta_boxes',
	function ( $post_type, $post ) {
		foreach ( cwm_meta_boxes() as $id => $box ) {
			if ( $box['screen'] !== $post_type ) {
				continue;
			}
			if ( 'page' === $post_type && isset( $box['template'] ) ) {
				$key = cwm_page_template_key( $post );
				if ( $box['template'] !== $key && ! ( 'default' === $box['template'] && in_array( $key, array( 'sitemap' ), true ) ) ) {
					continue;
				}
			}
			add_meta_box( $id, $box['title'], 'cwm_render_meta_box', $post_type, isset( $box['context'] ) ? $box['context'] : 'normal', 'high', array( 'box' => $id ) );
		}
	},
	10,
	2
);

function cwm_render_meta_box( $post, $args ) {
	$boxes = cwm_meta_boxes();
	$box   = $boxes[ $args['args']['box'] ];
	wp_nonce_field( 'cwm_save_' . $args['args']['box'], $args['args']['box'] . '_nonce' );
	echo '<div class="cwm-box">';
	if ( ! empty( $box['intro'] ) ) {
		echo '<p class="cwm-box__intro">' . esc_html( $box['intro'] ) . '</p>';
	}
	foreach ( $box['fields'] as $f ) {
		$value = get_post_meta( $post->ID, '_cwm_' . $f['key'], true );
		if ( 'repeater' === ( isset( $f['type'] ) ? $f['type'] : '' ) && ! is_array( $value ) ) {
			$value = array();
		}
		cwm_render_field( $f, $value, 'cwm[' . $f['key'] . ']' );
	}
	echo '</div>';
}

add_action(
	'save_post',
	function ( $post_id, $post ) {
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( empty( $_POST['cwm'] ) || ! is_array( $_POST['cwm'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}
		$input = $_POST['cwm']; // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
		foreach ( cwm_meta_boxes() as $id => $box ) {
			if ( $box['screen'] !== $post->post_type || empty( $_POST[ $id . '_nonce' ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ $id . '_nonce' ] ) ), 'cwm_save_' . $id ) ) {
				continue;
			}
			foreach ( $box['fields'] as $f ) {
				if ( ! array_key_exists( $f['key'], $input ) ) {
					continue;
				}
				$value = cwm_sanitize_field( $f, $input[ $f['key'] ] );
				update_post_meta( $post_id, '_cwm_' . $f['key'], $value );
			}
		}
	},
	5,
	2
);
