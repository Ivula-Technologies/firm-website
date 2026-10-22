<?php
/**
 * Privacy: legal page links, consent-gated analytics and a click-to-load map.
 * The site sets no non-essential cookies unless a visitor accepts them.
 */

defined( 'ABSPATH' ) || exit;

/** URL of a legal page by key: privacy, cookies, terms, sitemap. */
function cwm_legal_url( $key ) {
	if ( 'privacy' === $key ) {
		$id = (int) get_option( 'wp_page_for_privacy_policy' );
		if ( $id && 'publish' === get_post_status( $id ) ) {
			return get_permalink( $id );
		}
	}
	$slugs = array(
		'privacy' => 'privacy-policy',
		'cookies' => 'cookie-policy',
		'terms'   => 'terms-of-use',
		'sitemap' => 'site-map',
	);
	$page  = isset( $slugs[ $key ] ) ? get_page_by_path( $slugs[ $key ] ) : null;
	return $page ? get_permalink( $page ) : home_url( '/' );
}

/** Cookie banner, only when something optional (analytics) is configured. */
add_action(
	'wp_footer',
	function () {
		$ga = trim( (string) cwm_opt( 'ga_id' ) );
		if ( ! $ga || ! preg_match( '/^G-[A-Z0-9]+$/i', $ga ) ) {
			return;
		}
		?>
		<div class="cookie-banner" id="cookie-banner" hidden role="dialog" aria-live="polite" aria-label="Cookie choice">
			<p>We use analytics cookies to understand which pages help people most. They are off unless you accept. <a href="<?php echo esc_url( cwm_legal_url( 'cookies' ) ); ?>">Cookie policy</a></p>
			<div class="cookie-banner__actions">
				<button type="button" class="btn btn--ghost" data-consent="no">Decline</button>
				<button type="button" class="btn btn--primary" data-consent="yes">Accept</button>
			</div>
		</div>
		<script>
		(function(){
			var KEY='cwm_consent', ID=<?php echo wp_json_encode( $ga ); ?>;
			function load(){var s=document.createElement('script');s.async=true;s.src='https://www.googletagmanager.com/gtag/js?id='+ID;document.head.appendChild(s);window.dataLayer=window.dataLayer||[];window.gtag=function(){dataLayer.push(arguments);};gtag('js',new Date());gtag('config',ID,{anonymize_ip:true});}
			var v=null;try{v=localStorage.getItem(KEY);}catch(e){}
			if(v==='yes'){load();return;}
			if(v==='no'){return;}
			var b=document.getElementById('cookie-banner');b.hidden=false;
			b.addEventListener('click',function(e){var c=e.target.getAttribute('data-consent');if(!c)return;try{localStorage.setItem(KEY,c);}catch(e){}b.hidden=true;if(c==='yes')load();});
		})();
		</script>
		<?php
	},
	50
);

/** Google map that only loads when the visitor asks for it. */
function cwm_map() {
	$q = trim( (string) cwm_opt( 'map_query' ) );
	if ( ! $q ) {
		return;
	}
	$src = 'https://www.google.com/maps?q=' . rawurlencode( $q ) . '&output=embed';
	echo '<div class="map" data-src="' . esc_url( $src ) . '">';
	echo '<div class="map__placeholder">' . cwm_icon( 'map-pin' ) . '<p><strong>' . esc_html( cwm_firm() ) . '</strong><br>' . nl2br( esc_html( cwm_opt( 'address' ) ) ) . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput
	echo '<button type="button" class="btn btn--ghost map__load">Show map</button> ';
	echo '<a class="btn btn--link" href="' . esc_url( 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $q ) ) . '" target="_blank" rel="noopener">Open in Google Maps ' . cwm_icon( 'external-link' ) . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput
	echo '<p class="map__note">The map loads from Google and may set cookies.</p></div></div>';
}
