<?php
/**
 * Titles, meta descriptions, social previews and structured data.
 * Everything here is built from settings, so Switch A and Switch B flow through automatically.
 * If an SEO plugin (Yoast, Rank Math, SEOPress, AIOSEO) is active, it takes over titles and meta.
 */

defined( 'ABSPATH' ) || exit;

function cwm_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) || defined( 'SEOPRESS_VERSION' ) || defined( 'AIOSEO_VERSION' );
}

add_filter(
	'document_title_separator',
	function () {
		return '|';
	}
);

add_filter(
	'document_title_parts',
	function ( $parts ) {
		$parts['site'] = cwm_firm();
		if ( is_front_page() ) {
			$parts['title']   = cwm_firm();
			$parts['tagline'] = cwm_opt( 'tagline' );
			unset( $parts['site'] );
		} elseif ( is_home() ) {
			$parts['title'] = 'Insights';
		} elseif ( is_singular( 'service' ) ) {
			$parts['title'] = get_the_title() . ' in Kenya';
		}
		return $parts;
	}
);

/** The best description for the current page. */
function cwm_meta_description() {
	if ( is_front_page() ) {
		$d = cwm_meta( 'seo_description', (int) get_option( 'page_on_front' ) );
		return $d ? $d : cwm_opt( 'description' );
	}
	if ( is_singular() ) {
		$id = get_queried_object_id();
		$d  = cwm_meta( 'seo_description', $id );
		if ( ! $d && 'service' === get_post_type( $id ) ) {
			$d = trim( cwm_meta( 'promise', $id ) . ' ' . cwm_meta( 'who_for', $id ) );
		}
		if ( ! $d && cwm_is_case( $id ) ) {
			$d = 'Case analysis: ' . cwm_meta( 'case_name', $id ) . ', ' . cwm_meta( 'case_citation', $id ) . '. ' . wp_strip_all_tags( get_the_excerpt( $id ) );
		}
		if ( ! $d ) {
			$d = has_excerpt( $id ) ? get_the_excerpt( $id ) : wp_trim_words( wp_strip_all_tags( strip_shortcodes( get_post_field( 'post_content', $id ) ) ), 30, '…' );
		}
		if ( ! $d ) {
			$d = cwm_meta( 'intro', $id );
		}
		return $d ? $d : cwm_opt( 'description' );
	}
	if ( is_home() ) {
		return 'Tax alerts, tax case analyses, ESG reporting updates, guides and news from ' . cwm_firm() . '.';
	}
	if ( is_category() ) {
		$d = category_description();
		return $d ? wp_strip_all_tags( $d ) : single_cat_title( '', false ) . ' from ' . cwm_firm() . '.';
	}
	return cwm_opt( 'description' );
}

add_action(
	'wp_head',
	function () {
		if ( cwm_seo_plugin_active() ) {
			return;
		}
		$desc  = wp_strip_all_tags( cwm_meta_description() );
		$desc  = mb_substr( trim( preg_replace( '/\s+/', ' ', $desc ) ), 0, 300 );
		$title = wp_get_document_title();
		$url   = is_singular() ? get_permalink() : ( is_front_page() ? home_url( '/' ) : cwm_current_url() );
		$img   = '';
		if ( is_singular() && has_post_thumbnail() ) {
			$img = get_the_post_thumbnail_url( null, 'large' );
		}
		if ( ! $img ) {
			$img = CWM_URI . '/assets/img/og-default.png';
		}
		echo "\n" . '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
		echo '<meta property="og:site_name" content="' . esc_attr( cwm_firm() ) . '">' . "\n";
		echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
		echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
		echo '<meta property="og:type" content="' . ( is_singular( 'post' ) ? 'article' : 'website' ) . '">' . "\n";
		echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
		echo '<meta property="og:image" content="' . esc_url( $img ) . '">' . "\n";
		echo '<meta property="og:locale" content="en_KE">' . "\n";
		echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
		if ( is_search() || is_404() || cwm_get( 'cwm_sent' ) || cwm_get( 'cwm_err' ) ) {
			echo '<meta name="robots" content="noindex, follow">' . "\n";
		}
		if ( ( is_home() || is_category() ) && ( cwm_get( 'q' ) || cwm_get( 'yr' ) || cwm_get( 'service' ) || cwm_get( 'topic' ) || cwm_get( 'court' ) || cwm_get( 'taxtype' ) || cwm_get( 'outcome' ) ) ) {
			echo '<meta name="robots" content="noindex, follow">' . "\n";
		}
	},
	2
);

/** Structured data (JSON-LD). */
add_action(
	'wp_head',
	function () {
		$graph = array( cwm_schema_org() );

		if ( is_singular( 'service' ) ) {
			$id      = get_queried_object_id();
			$graph[] = array(
				'@type'       => 'Service',
				'@id'         => get_permalink( $id ) . '#service',
				'name'        => get_the_title( $id ),
				'description' => cwm_meta( 'promise', $id ),
				'provider'    => array( '@id' => home_url( '/#organization' ) ),
				'areaServed'  => array(
					'@type' => 'Country',
					'name'  => 'Kenya',
				),
				'url'         => get_permalink( $id ),
			);
			$faqs = (array) cwm_meta( 'faqs', $id );
			if ( $faqs ) {
				$q = array();
				foreach ( $faqs as $f ) {
					$q[] = array(
						'@type'          => 'Question',
						'name'           => $f['q'],
						'acceptedAnswer' => array(
							'@type' => 'Answer',
							'text'  => wp_strip_all_tags( $f['a'] ),
						),
					);
				}
				$graph[] = array(
					'@type'      => 'FAQPage',
					'@id'        => get_permalink( $id ) . '#faq',
					'mainEntity' => $q,
				);
			}
		}

		if ( is_singular( 'post' ) ) {
			$id     = get_queried_object_id();
			$byline = cwm_byline( $id );
			$author = $byline['url'] ? array(
				'@type' => 'Person',
				'name'  => $byline['name'],
				'url'   => $byline['url'],
			) : array(
				'@type' => 'Organization',
				'name'  => $byline['name'],
				'url'   => home_url( '/' ),
			);
			$graph[] = array(
				'@type'            => 'Article',
				'@id'              => get_permalink( $id ) . '#article',
				'headline'         => get_the_title( $id ),
				'datePublished'    => get_the_date( 'c', $id ),
				'dateModified'     => get_the_modified_date( 'c', $id ),
				'author'           => $author,
				'publisher'        => array( '@id' => home_url( '/#organization' ) ),
				'mainEntityOfPage' => get_permalink( $id ),
				'articleSection'   => ( $c = cwm_primary_category( $id ) ) ? $c->name : 'Insights',
				'inLanguage'       => 'en-KE',
			);
		}

		if ( is_singular( 'team_member' ) && cwm_visible_member( get_queried_object_id() ) ) {
			$id      = get_queried_object_id();
			$graph[] = array(
				'@type'    => 'Person',
				'name'     => get_the_title( $id ),
				'jobTitle' => cwm_meta( 'job_title', $id ),
				'worksFor' => array( '@id' => home_url( '/#organization' ) ),
				'url'      => get_permalink( $id ),
			);
		}

		$crumbs = cwm_breadcrumbs();
		if ( count( $crumbs ) > 1 ) {
			$items = array();
			foreach ( $crumbs as $i => $c ) {
				$items[] = array_filter(
					array(
						'@type'    => 'ListItem',
						'position' => $i + 1,
						'name'     => $c['label'],
						'item'     => $c['url'],
					)
				);
			}
			$graph[] = array(
				'@type'           => 'BreadcrumbList',
				'itemListElement' => $items,
			);
		}

		echo '<script type="application/ld+json">' . wp_json_encode(
			array(
				'@context' => 'https://schema.org',
				'@graph'   => $graph,
			),
			JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
		) . '</script>' . "\n";
	},
	3
);

/** The firm as an AccountingService, offering only the services the public can see. */
function cwm_schema_org() {
	$offers = array();
	foreach ( cwm_services() as $s ) {
		$offers[] = array(
			'@type'       => 'Offer',
			'itemOffered' => array(
				'@type' => 'Service',
				'name'  => $s->post_title,
				'url'   => get_permalink( $s ),
			),
		);
	}
	$same = array_values( array_filter( array( cwm_opt( 'linkedin' ), cwm_opt( 'x' ), cwm_opt( 'facebook' ) ) ) );
	$addr = array_values( array_filter( array_map( 'trim', explode( "\n", (string) cwm_opt( 'address' ) ) ) ) );
	$org  = array(
		'@type'           => 'AccountingService',
		'@id'             => home_url( '/#organization' ),
		'name'            => cwm_firm(),
		'slogan'          => cwm_opt( 'tagline' ),
		'description'     => cwm_opt( 'description' ),
		'url'             => home_url( '/' ),
		'logo'            => cwm_opt( 'logo' ) ? cwm_file_url( cwm_opt( 'logo' ) ) : CWM_URI . '/assets/img/mark.png',
		'image'           => CWM_URI . '/assets/img/og-default.png',
		'telephone'       => cwm_opt( 'phone' ),
		'email'           => cwm_opt( 'email' ),
		'areaServed'      => array(
			'@type' => 'Country',
			'name'  => 'Kenya',
		),
		'address'         => array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => isset( $addr[0] ) ? $addr[0] : '',
			'addressLocality' => 'Nairobi',
			'addressCountry'  => 'KE',
		),
		'hasOfferCatalog' => array(
			'@type'           => 'OfferCatalog',
			'name'            => 'Services',
			'itemListElement' => $offers,
		),
	);
	if ( $same ) {
		$org['sameAs'] = $same;
	}
	if ( 'live' === cwm_audit_state() && cwm_opt( 'audit_licence' ) ) {
		$org['hasCredential'] = cwm_opt( 'audit_licence' );
	}
	return $org;
}

/**
 * Breadcrumb trail for the current page.
 *
 * @return array[] label, url
 */
function cwm_breadcrumbs() {
	$c = array(
		array(
			'label' => 'Home',
			'url'   => home_url( '/' ),
		),
	);
	if ( is_front_page() ) {
		return $c;
	}
	if ( is_singular( 'service' ) ) {
		$c[] = array(
			'label' => 'Services',
			'url'   => cwm_page_url( 'services' ),
		);
	} elseif ( is_singular( 'post' ) || is_category() || is_home() ) {
		$c[] = array(
			'label' => 'Insights',
			'url'   => cwm_insights_url(),
		);
		if ( is_singular( 'post' ) ) {
			$cat = cwm_primary_category();
			if ( $cat && $cat->parent ) {
				$parent = get_category( $cat->parent );
				$c[]    = array(
					'label' => $parent->name,
					'url'   => get_category_link( $parent ),
				);
			}
			if ( $cat ) {
				$c[] = array(
					'label' => $cat->name,
					'url'   => get_category_link( $cat ),
				);
			}
		} elseif ( is_category() ) {
			$cat = get_queried_object();
			if ( $cat->parent ) {
				$parent = get_category( $cat->parent );
				$c[]    = array(
					'label' => $parent->name,
					'url'   => get_category_link( $parent ),
				);
			}
		}
	} elseif ( is_singular( 'team_member' ) ) {
		$c[] = array(
			'label' => 'About',
			'url'   => cwm_page_url( 'about' ),
		);
		$c[] = array(
			'label' => 'Our team',
			'url'   => cwm_page_url( 'team' ),
		);
	} elseif ( is_page() ) {
		foreach ( array_reverse( get_post_ancestors( get_queried_object_id() ) ) as $a ) {
			$c[] = array(
				'label' => get_the_title( $a ),
				'url'   => get_permalink( $a ),
			);
		}
	}
	if ( is_singular() ) {
		$c[] = array(
			'label' => get_the_title( get_queried_object_id() ),
			'url'   => '',
		);
	} elseif ( is_category() ) {
		$c[] = array(
			'label' => single_cat_title( '', false ),
			'url'   => '',
		);
	} elseif ( is_search() ) {
		$c[] = array(
			'label' => 'Search',
			'url'   => '',
		);
	}
	if ( is_home() ) {
		$c[ count( $c ) - 1 ]['url'] = '';
	}
	return $c;
}
