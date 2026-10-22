<?php
/**
 * Preview mode: show the site to the client on a staging address before launch.
 * Hidden from search engines, an optional shared password, and a slim banner.
 */

defined( 'ABSPATH' ) || exit;

function cwm_preview_on() {
	return (bool) cwm_opt( 'preview_on' );
}

/** True when this visitor must enter the preview password first. */
function cwm_preview_locked() {
	if ( ! cwm_preview_on() || '' === (string) cwm_opt( 'preview_password' ) || is_user_logged_in() ) {
		return false;
	}
	return ! isset( $_COOKIE['cwm_preview'] ) || ! hash_equals( cwm_preview_token(), (string) $_COOKIE['cwm_preview'] );
}

/** Changes whenever the password changes, so old cookies stop working. */
function cwm_preview_token() {
	return hash_hmac( 'sha256', 'preview|' . cwm_opt( 'preview_password' ), wp_salt( 'auth' ) );
}

/* Keep search engines out while previewing, even when an SEO plugin is active. */
add_filter(
	'wp_robots',
	function ( $robots ) {
		if ( cwm_preview_on() ) {
			$robots = array(
				'noindex'  => true,
				'nofollow' => true,
			);
		}
		return $robots;
	},
	99
);
add_action(
	'send_headers',
	function () {
		if ( cwm_preview_on() ) {
			header( 'X-Robots-Tag: noindex, nofollow', true );
		}
	}
);
add_filter(
	'robots_txt',
	function ( $out ) {
		return cwm_preview_on() ? "User-agent: *\nDisallow: /\n" : $out;
	},
	99
);
add_filter(
	'wp_sitemaps_enabled',
	function ( $on ) {
		return cwm_preview_on() ? false : $on;
	}
);

/* The password gate. */
add_action(
	'template_redirect',
	function () {
		if ( ! cwm_preview_locked() ) {
			return;
		}
		$error = '';
		if ( isset( $_POST['cwm_preview_password'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$ip    = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
			$key   = 'cwm_pv_' . md5( $ip );
			$tries = (int) get_transient( $key );
			if ( $tries >= 10 ) {
				$error = 'Too many attempts. Please wait a few minutes and try again.';
			} elseif ( hash_equals( (string) cwm_opt( 'preview_password' ), trim( wp_unslash( (string) $_POST['cwm_preview_password'] ) ) ) ) { // phpcs:ignore
				setcookie( 'cwm_preview', cwm_preview_token(), time() + 30 * DAY_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, is_ssl(), true );
				delete_transient( $key );
				$back = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : home_url( '/' );
				wp_safe_redirect( $back );
				exit;
			} else {
				set_transient( $key, $tries + 1, 10 * MINUTE_IN_SECONDS );
				$error = 'That password is not right. Please check it and try again.';
			}
		}
		status_header( 401 );
		nocache_headers();
		cwm_preview_gate( $error );
		exit;
	},
	0
);

/* Content must not leak through the REST API or feeds while locked. */
add_filter(
	'rest_authentication_errors',
	function ( $result ) {
		if ( null === $result && cwm_preview_locked() ) {
			return new WP_Error( 'cwm_preview', 'This site is in preview.', array( 'status' => 401 ) );
		}
		return $result;
	}
);

function cwm_preview_gate( $error ) {
	$css = CWM_URI . '/style.css?ver=' . CWM_VERSION;
	?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?php echo esc_html( cwm_firm() ); ?>: preview</title>
<link rel="stylesheet" href="<?php echo esc_url( $css ); ?>">
<link rel="icon" href="<?php echo esc_url( CWM_URI . '/assets/img/favicon.svg' ); ?>">
</head>
<body class="preview-gate">
	<main class="preview-gate__card">
		<div class="preview-gate__brand brand"><?php cwm_logo(); ?></div>
		<h1>Website preview</h1>
		<p>This is the new <?php echo esc_html( cwm_firm() ); ?> website before it goes live. Enter the preview password you were given to look around.</p>
		<form method="post">
			<label for="cwm-pv">Preview password</label>
			<input id="cwm-pv" type="password" name="cwm_preview_password" autocomplete="current-password" required autofocus>
			<?php if ( $error ) : ?>
				<p class="preview-gate__error" role="alert"><?php echo esc_html( $error ); ?></p>
			<?php endif; ?>
			<button class="btn btn--primary btn--lg" type="submit">View the preview</button>
		</form>
	</main>
</body>
</html>
	<?php
}

/** The slim banner shown on every page while in preview. */
function cwm_preview_banner() {
	if ( ! cwm_preview_on() ) {
		return;
	}
	$note = cwm_opt( 'preview_note' );
	$fb   = trim( (string) cwm_opt( 'preview_feedback' ) );
	$href = '';
	$page = isset( $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI'] ) ? set_url_scheme( 'http://' . sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) . esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) : home_url( '/' );
	if ( $fb ) {
		if ( is_email( $fb ) ) {
			$href = 'mailto:' . $fb . '?subject=' . rawurlencode( 'Website feedback: ' . cwm_firm() ) . '&body=' . rawurlencode( "Page: " . $page . "\n\n" );
		} elseif ( preg_match( '/^\+?[0-9 ]{9,16}$/', $fb ) ) {
			$href = 'https://wa.me/' . preg_replace( '/\D/', '', $fb ) . '?text=' . rawurlencode( 'Website feedback on ' . $page . ': ' );
		} else {
			$href = $fb;
		}
	}
	echo '<div class="preview-bar" role="note"><div class="container preview-bar__inner"><span class="preview-bar__tag">Preview</span><span class="preview-bar__short">Not public yet</span><span class="preview-bar__text">' . esc_html( $note ? $note : 'This is a preview of the new website. It is not public yet and search engines cannot see it.' ) . '</span>';
	if ( $href ) {
		echo '<a class="preview-bar__link" href="' . esc_url( $href ) . '" target="_blank" rel="noopener"><span class="hide-xs">Send feedback on this page</span><span class="show-xs">Send feedback</span></a>';
	}
	echo '</div></div>';
}

/* Label form emails sent during preview, so test messages are easy to spot. */
add_filter(
	'wp_mail',
	function ( $args ) {
		if ( cwm_preview_on() && is_string( $args['subject'] ) && 0 !== strpos( $args['subject'], '[Preview]' ) ) {
			$args['subject'] = '[Preview] ' . $args['subject'];
		}
		return $args;
	}
);
