<?php
/**
 * Reusable bits of markup.
 */

defined( 'ABSPATH' ) || exit;

/** The built-in mark: three ascending bars for money, obligations and impact. */
function cwm_mark_svg( $variant = 'dark' ) {
	$uid = 'g' . wp_rand( 1000, 9999 );
	return '<svg class="brand__mark" viewBox="0 0 48 48" aria-hidden="true" focusable="false">'
		. '<defs><linearGradient id="' . $uid . '" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="' . ( 'light' === $variant ? '#2A6FD6' : '#0B2545' ) . '"/><stop offset="1" stop-color="#1F5FBF"/></linearGradient></defs>'
		. '<rect width="48" height="48" rx="12" fill="url(#' . $uid . ')"/>'
		. '<rect x="11" y="25" width="7" height="13" rx="2" fill="#fff" fill-opacity=".55"/>'
		. '<rect x="20.5" y="18" width="7" height="20" rx="2" fill="#fff" fill-opacity=".8"/>'
		. '<rect x="30" y="10" width="7" height="28" rx="2" fill="#fff"/>'
		. '</svg>';
}

/** Logo: uploaded image if set, otherwise the mark plus the firm name from settings. */
function cwm_logo( $variant = 'dark' ) {
	$id = (int) ( 'light' === $variant && cwm_opt( 'logo_footer' ) ? cwm_opt( 'logo_footer' ) : cwm_opt( 'logo' ) );
	if ( $id && wp_get_attachment_url( $id ) ) {
		echo '<img class="brand__img" src="' . esc_url( wp_get_attachment_url( $id ) ) . '" alt="' . esc_attr( cwm_firm() ) . '" height="48">';
		return;
	}
	echo cwm_mark_svg( $variant ); // phpcs:ignore WordPress.Security.EscapeOutput
	echo '<span class="brand__text"><span class="brand__name">' . esc_html( cwm_firm() ) . '</span><span class="brand__tag">' . esc_html( cwm_opt( 'tagline' ) ) . '</span></span>';
}

/** "Download firm profile" button, hidden without a file. */
function cwm_profile_button( $class = 'btn btn--ghost' ) {
	$url = cwm_file_url( cwm_opt( 'firm_profile' ) );
	if ( ! $url ) {
		return;
	}
	$size = cwm_file_size( cwm_opt( 'firm_profile' ) );
	echo '<a class="' . esc_attr( $class ) . '" href="' . esc_url( $url ) . '" download>' . cwm_icon( 'download' ) . ' Download firm profile (PDF' . ( $size ? ', ' . esc_html( $size ) : '' ) . ')</a>'; // phpcs:ignore WordPress.Security.EscapeOutput
}

/** Page banner with breadcrumbs. */
function cwm_banner( $title, $line = '', $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'eyebrow' => '',
			'icon'    => '',
			'extra'   => '',
			'class'   => '',
		)
	);
	echo '<section class="banner ' . esc_attr( $args['class'] ) . '"><div class="container banner__inner">';
	cwm_breadcrumbs_html();
	echo '<div class="banner__body">';
	if ( $args['icon'] ) {
		echo '<span class="banner__icon">' . cwm_icon( $args['icon'] ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
	echo '<div>';
	if ( $args['eyebrow'] ) {
		echo '<p class="eyebrow eyebrow--light">' . esc_html( $args['eyebrow'] ) . '</p>';
	}
	echo '<h1 class="banner__title">' . esc_html( $title ) . '</h1>';
	if ( $line ) {
		echo '<p class="banner__line">' . esc_html( $line ) . '</p>';
	}
	echo $args['extra']; // phpcs:ignore WordPress.Security.EscapeOutput
	echo '</div></div></div></section>';
}

function cwm_breadcrumbs_html() {
	$crumbs = cwm_breadcrumbs();
	if ( count( $crumbs ) < 2 ) {
		return;
	}
	echo '<nav class="breadcrumbs" aria-label="Breadcrumb"><ol>';
	foreach ( $crumbs as $c ) {
		echo '<li>' . ( $c['url'] ? '<a href="' . esc_url( $c['url'] ) . '">' . esc_html( $c['label'] ) . '</a>' : '<span aria-current="page">' . esc_html( wp_trim_words( $c['label'], 8, '…' ) ) . '</span>' ) . '</li>';
	}
	echo '</ol></nav>';
}

/** Section heading with gold accent line. */
function cwm_section_head( $eyebrow, $title, $text = '', $link = array(), $class = '' ) {
	echo '<div class="section-head ' . esc_attr( $class ) . '"><div>';
	if ( $eyebrow ) {
		echo '<p class="eyebrow">' . esc_html( $eyebrow ) . '</p>';
	}
	echo '<h2 class="section-title">' . esc_html( $title ) . '</h2>';
	if ( $text ) {
		echo '<p class="section-text">' . esc_html( $text ) . '</p>';
	}
	echo '</div>';
	if ( $link ) {
		echo '<a class="link-arrow" href="' . esc_url( $link[1] ) . '">' . esc_html( $link[0] ) . cwm_icon( 'arrow-right' ) . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
	echo '</div>';
}

/** Category label (with a "Case analysis" marker). */
function cwm_post_label( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	if ( cwm_is_case( $post_id ) ) {
		return '<span class="tag tag--case">' . cwm_icon( 'gavel' ) . 'Case analysis</span>';
	}
	$cat = cwm_primary_category( $post_id );
	return $cat ? '<span class="tag">' . esc_html( $cat->name ) . '</span>' : '';
}

/** Insight card. */
function cwm_insight_card( $post_id = null, $variant = '' ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$cat     = cwm_primary_category( $post_id );
	$slug    = $cat ? $cat->slug : 'insights';
	$icons   = array(
		'tax-alerts'    => 'receipt-text',
		'case-analysis' => 'gavel',
		'esg-reporting' => 'leaf',
		'guides'        => 'book-open',
		'news-events'   => 'newspaper',
	);
	$icon    = isset( $icons[ $slug ] ) ? $icons[ $slug ] : 'file-text';
	$series  = cwm_series_info( $post_id );
	echo '<article class="card card--insight ' . esc_attr( $variant ) . ' cat-' . esc_attr( $slug ) . '">';
	echo '<a class="card__media" href="' . esc_url( get_permalink( $post_id ) ) . '" tabindex="-1" aria-hidden="true">';
	if ( has_post_thumbnail( $post_id ) ) {
		echo get_the_post_thumbnail( $post_id, 'card', array( 'alt' => '' ) );
	} else {
		echo '<span class="card__art">' . cwm_icon( $icon ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
	echo '</a><div class="card__body">';
	echo '<div class="card__meta">' . cwm_post_label( $post_id ) . '<time datetime="' . esc_attr( get_the_date( 'c', $post_id ) ) . '">' . esc_html( get_the_date( 'j M Y', $post_id ) ) . '</time></div>'; // phpcs:ignore WordPress.Security.EscapeOutput
	echo '<h3 class="card__title"><a href="' . esc_url( get_permalink( $post_id ) ) . '">' . esc_html( get_the_title( $post_id ) ) . '</a></h3>';
	if ( $series ) {
		echo '<p class="card__series">' . esc_html( $series['term']->name ) . ' · Part ' . (int) ( $series['index'] + 1 ) . ' of ' . (int) $series['total'] . '</p>';
	}
	if ( cwm_is_case( $post_id ) && cwm_meta( 'case_citation', $post_id ) ) {
		echo '<p class="card__citation">' . esc_html( cwm_meta( 'case_citation', $post_id ) ) . '</p>';
	} elseif ( 'card--compact' !== $variant ) {
		echo '<p class="card__excerpt">' . esc_html( wp_strip_all_tags( get_the_excerpt( $post_id ) ) ) . '</p>';
	}
	echo '<span class="card__more">' . esc_html( cwm_reading_time( $post_id ) ) . ' min read</span>';
	echo '</div></article>';
}

/** Service card. */
function cwm_service_card( $s ) {
	echo '<article class="card card--service">';
	echo '<span class="card__icon">' . cwm_icon( cwm_meta( 'icon', $s->ID ) ? cwm_meta( 'icon', $s->ID ) : 'circle-check' ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput
	echo '<h3 class="card__title"><a class="stretched" href="' . esc_url( get_permalink( $s ) ) . '">' . esc_html( $s->post_title ) . '</a></h3>';
	echo '<p>' . esc_html( cwm_meta( 'promise', $s->ID ) ) . '</p>';
	echo '<span class="link-arrow">Find out more' . cwm_icon( 'arrow-right' ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput
	echo '</article>';
}

/** Share buttons. */
function cwm_share( $post_id = null ) {
	$url   = rawurlencode( get_permalink( $post_id ) );
	$title = rawurlencode( get_the_title( $post_id ) );
	$links = array(
		'linkedin' => array( 'https://www.linkedin.com/sharing/share-offsite/?url=' . $url, 'LinkedIn' ),
		'x-brand'  => array( 'https://x.com/intent/post?url=' . $url . '&text=' . $title, 'X' ),
		'whatsapp' => array( 'https://wa.me/?text=' . $title . '%20' . $url, 'WhatsApp' ),
		'mail'     => array( 'mailto:?subject=' . $title . '&body=' . $url, 'Email' ),
	);
	echo '<div class="share"><span class="share__label">Share</span>';
	foreach ( $links as $icon => $l ) {
		echo '<a href="' . esc_url( $l[0] ) . '" target="_blank" rel="noopener" aria-label="Share on ' . esc_attr( $l[1] ) . '">' . cwm_icon( $icon ) . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
	echo '<button type="button" class="share__copy" data-url="' . esc_url( get_permalink( $post_id ) ) . '" aria-label="Copy link">' . cwm_icon( 'link' ) . '</button>'; // phpcs:ignore WordPress.Security.EscapeOutput
	echo '</div>';
}

/** Numbered pagination that keeps the active filters. */
function cwm_pagination() {
	$args = array();
	foreach ( array( 'q', 'yr', 'service', 'topic', 'court', 'taxtype', 'outcome' ) as $k ) {
		if ( cwm_get( $k ) ) {
			$args[ $k ] = cwm_get( $k );
		}
	}
	$links = paginate_links(
		array(
			'type'      => 'array',
			'add_args'  => $args,
			'prev_text' => cwm_icon( 'chevron-left' ) . '<span class="sr-only">Previous</span>',
			'next_text' => '<span class="sr-only">Next</span>' . cwm_icon( 'chevron-right' ),
		)
	);
	if ( $links ) {
		echo '<nav class="pagination" aria-label="Pages"><ul><li>' . implode( '</li><li>', $links ) . '</li></ul></nav>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
}

/** Default hero art (inline SVG so it uses the site fonts). */
function cwm_hero_art() {
	$file = CWM_DIR . '/assets/img/hero-nairobi.svg';
	if ( file_exists( $file ) ) {
		echo file_get_contents( $file ); // phpcs:ignore
	}
}

/** Key contact block (only with Switch B on and the person published). */
function cwm_key_contact( $member_id ) {
	$id = cwm_visible_member( $member_id );
	if ( ! $id ) {
		return;
	}
	echo '<aside class="key-contact">';
	echo '<p class="eyebrow">Key contact</p>';
	echo '<div class="key-contact__body">';
	if ( has_post_thumbnail( $id ) ) {
		echo get_the_post_thumbnail( $id, 'thumbnail', array( 'alt' => get_the_title( $id ) ) );
	}
	echo '<div><h3><a href="' . esc_url( get_permalink( $id ) ) . '">' . esc_html( get_the_title( $id ) ) . '</a></h3>';
	echo '<p>' . esc_html( cwm_meta( 'job_title', $id ) ) . '</p>';
	if ( cwm_meta( 'email', $id ) ) {
		echo '<p><a href="mailto:' . esc_attr( cwm_meta( 'email', $id ) ) . '">' . esc_html( cwm_meta( 'email', $id ) ) . '</a></p>';
	}
	echo '</div></div></aside>';
}

/** Resource download card. */
function cwm_resource_card( $r, $class = '' ) {
	$file = cwm_file_url( cwm_meta( 'file', $r->ID ) );
	if ( ! $file ) {
		return;
	}
	$kinds = array(
		'publication' => 'Publication',
		'checklist'   => 'Checklist',
		'template'    => 'Template',
		'guide'       => 'Guide',
	);
	$kind  = cwm_meta( 'kind', $r->ID );
	$ext   = strtoupper( pathinfo( $file, PATHINFO_EXTENSION ) );
	$size  = cwm_file_size( cwm_meta( 'file', $r->ID ) );
	echo '<article class="resource ' . esc_attr( $class ) . '">';
	echo '<span class="resource__icon">' . cwm_icon( 'checklist' === $kind ? 'list-checks' : ( 'template' === $kind ? 'file-spreadsheet' : 'file-text' ) ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput
	echo '<div class="resource__body"><p class="resource__kind">' . esc_html( isset( $kinds[ $kind ] ) ? $kinds[ $kind ] : 'Download' ) . '</p>';
	echo '<h3>' . esc_html( $r->post_title ) . '</h3>';
	if ( cwm_meta( 'summary', $r->ID ) ) {
		echo '<p>' . esc_html( cwm_meta( 'summary', $r->ID ) ) . '</p>';
	}
	echo '</div><a class="btn btn--primary" href="' . esc_url( $file ) . '" download>' . cwm_icon( 'download' ) . ' Download ' . esc_html( $ext ) . ( $size ? ' <span class="muted-light">(' . esc_html( $size ) . ')</span>' : '' ) . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput
	echo '</article>';
}
