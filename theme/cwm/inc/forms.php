<?php
/**
 * Contact, proposal, service enquiry and newsletter forms.
 * Every submission is saved under Enquiries (so nothing is lost if email fails)
 * and emailed to the shared address in Firm settings.
 */

defined( 'ABSPATH' ) || exit;

function cwm_form_types() {
	return array(
		'contact'    => 'Contact',
		'enquiry'    => 'Service enquiry',
		'proposal'   => 'Proposal request',
		'newsletter' => 'Newsletter',
	);
}

/** Field definitions per form. */
function cwm_form_fields( $type ) {
	$services = cwm_service_choices();
	$name     = array( 'key' => 'name', 'label' => 'Your name', 'type' => 'text', 'required' => true, 'autocomplete' => 'name' );
	$org      = array( 'key' => 'organisation', 'label' => 'Organisation', 'type' => 'text', 'autocomplete' => 'organization' );
	$email    = array( 'key' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true, 'autocomplete' => 'email' );
	$phone    = array( 'key' => 'phone', 'label' => 'Phone', 'type' => 'tel', 'autocomplete' => 'tel' );
	$consent  = array( 'key' => 'consent', 'type' => 'consent', 'required' => true );

	switch ( $type ) {
		case 'contact':
			return array(
				$name,
				$org,
				$email,
				$phone,
				array( 'key' => 'service', 'label' => 'Service of interest', 'type' => 'select', 'options' => $services + array( 'other' => 'Something else' ), 'empty' => 'Choose a service', 'wide' => true ),
				array( 'key' => 'message', 'label' => 'How can we help?', 'type' => 'textarea', 'required' => true, 'wide' => true ),
				$consent,
			);
		case 'enquiry':
			return array(
				$name,
				$org,
				$email,
				$phone,
				array( 'key' => 'service', 'label' => 'Service', 'type' => 'select', 'options' => $services + array( 'other' => 'Something else' ), 'wide' => true ),
				array( 'key' => 'message', 'label' => 'Tell us a little about your needs', 'type' => 'textarea', 'required' => true, 'wide' => true ),
				$consent,
			);
		case 'proposal':
			$sectors = array();
			foreach ( get_posts( array( 'post_type' => 'industry', 'numberposts' => -1, 'orderby' => 'menu_order', 'order' => 'ASC' ) ) as $i ) {
				$sectors[ sanitize_title( $i->post_title ) ] = $i->post_title;
			}
			return array(
				array( 'key' => 'organisation', 'label' => 'Organisation', 'type' => 'text', 'required' => true, 'autocomplete' => 'organization' ),
				array( 'key' => 'sector', 'label' => 'Sector', 'type' => 'select', 'options' => $sectors + array( 'other' => 'Other' ), 'empty' => 'Choose a sector', 'required' => true ),
				array(
					'key'      => 'size',
					'label'    => 'Size',
					'type'     => 'select',
					'empty'    => 'Number of staff',
					'required' => true,
					'options'  => array(
						'1-10'    => '1 to 10 staff',
						'11-50'   => '11 to 50 staff',
						'51-200'  => '51 to 200 staff',
						'201-500' => '201 to 500 staff',
						'500+'    => 'More than 500 staff',
					),
				),
				array( 'key' => 'services', 'label' => 'Services required', 'type' => 'checkboxes', 'options' => $services, 'required' => true, 'wide' => true ),
				array(
					'key'     => 'timeline',
					'label'   => 'Timeline',
					'type'    => 'select',
					'empty'   => 'When do you need us?',
					'options' => array(
						'urgent'    => 'Urgent, within two weeks',
						'month'     => 'Within a month',
						'quarter'   => 'Within three months',
						'exploring' => 'Just exploring',
					),
				),
				array(
					'key'     => 'heard',
					'label'   => 'How did you hear of us?',
					'type'    => 'select',
					'empty'   => 'Choose one',
					'options' => array(
						'referral' => 'Referral from a client or friend',
						'search'   => 'Google or another search engine',
						'linkedin' => 'LinkedIn',
						'social'   => 'Other social media',
						'event'    => 'Event or seminar',
						'other'    => 'Other',
					),
				),
				$name,
				$email,
				$phone,
				array( 'key' => 'details', 'label' => 'Anything else we should know?', 'type' => 'textarea', 'wide' => true ),
				array( 'key' => 'file', 'label' => 'Attach a brief or terms of reference (PDF or Word, up to 10 MB)', 'type' => 'file', 'wide' => true ),
				$consent,
			);
		case 'newsletter':
			return array( $email );
	}
	return array();
}

/**
 * Render a form.
 *
 * @param string $type    Form type.
 * @param array  $preset  Preset values, e.g. array( 'service' => 'tax' ).
 */
function cwm_form( $type, $preset = array() ) {
	$fields = cwm_form_fields( $type );
	$state  = cwm_form_state( $type );
	$values = array_merge( $preset, $state['values'] );
	$uid    = 'f-' . $type . '-' . wp_rand( 100, 999 );

	if ( $state['sent'] ) {
		echo '<div class="form-success" role="status" id="form-' . esc_attr( $type ) . '">' . cwm_icon( 'circle-check' ); // phpcs:ignore WordPress.Security.EscapeOutput
		if ( 'newsletter' === $type ) {
			echo '<p><strong>You are subscribed.</strong> Thank you.</p></div>';
		} else {
			echo '<div><p><strong>Thank you. We have your message.</strong></p><p>We reply within one working day. If it is urgent, call us on <a href="' . esc_url( cwm_tel_url() ) . '">' . esc_html( cwm_opt( 'phone' ) ) . '</a>.</p></div></div>';
		}
		return;
	}

	$is_nl = 'newsletter' === $type;
	echo '<form class="cwm-form cwm-form--' . esc_attr( $type ) . '" id="form-' . esc_attr( $type ) . '" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"' . ( 'proposal' === $type ? ' enctype="multipart/form-data"' : '' ) . ' novalidate>';
	if ( $state['errors'] ) {
		echo '<div class="form-error" role="alert"><p><strong>Please check the form.</strong></p><ul>';
		foreach ( $state['errors'] as $e ) {
			echo '<li>' . esc_html( $e ) . '</li>';
		}
		echo '</ul></div>';
	}
	echo '<input type="hidden" name="action" value="cwm_form">';
	echo '<input type="hidden" name="cwm_type" value="' . esc_attr( $type ) . '">';
	echo '<input type="hidden" name="cwm_return" value="' . esc_url( cwm_current_url() ) . '">';
	echo '<input type="hidden" name="cwm_ts" value="' . esc_attr( cwm_form_token() ) . '">';
	echo '<div class="hp" aria-hidden="true"><label>Leave this empty <input type="text" name="cwm_website" tabindex="-1" autocomplete="off"></label></div>';

	if ( $is_nl ) {
		echo '<div class="newsletter-row"><label class="sr-only" for="' . esc_attr( $uid ) . '-email">Email address</label>';
		echo '<input id="' . esc_attr( $uid ) . '-email" type="email" name="f[email]" required placeholder="Your email address" autocomplete="email" value="' . esc_attr( isset( $values['email'] ) ? $values['email'] : '' ) . '">';
		echo '<button class="btn btn--primary" type="submit">Subscribe</button></div>';
		echo '<p class="form-note">By subscribing you agree to our <a href="' . esc_url( cwm_legal_url( 'privacy' ) ) . '">privacy policy</a>. Unsubscribe at any time.</p>';
		echo '</form>';
		return;
	}

	echo '<div class="form-grid">';
	foreach ( $fields as $f ) {
		$fid   = $uid . '-' . $f['key'];
		$val   = isset( $values[ $f['key'] ] ) ? $values[ $f['key'] ] : '';
		$req   = ! empty( $f['required'] );
		$wide  = ! empty( $f['wide'] ) || 'consent' === $f['type'];
		echo '<div class="form-field' . ( $wide ? ' form-field--wide' : '' ) . '">';
		switch ( $f['type'] ) {
			case 'textarea':
				echo '<label for="' . esc_attr( $fid ) . '">' . esc_html( $f['label'] ) . ( $req ? ' <span class="req">*</span>' : '' ) . '</label>';
				echo '<textarea id="' . esc_attr( $fid ) . '" name="f[' . esc_attr( $f['key'] ) . ']" rows="5"' . ( $req ? ' required' : '' ) . '>' . esc_textarea( $val ) . '</textarea>';
				break;
			case 'select':
				echo '<label for="' . esc_attr( $fid ) . '">' . esc_html( $f['label'] ) . ( $req ? ' <span class="req">*</span>' : '' ) . '</label>';
				echo '<select id="' . esc_attr( $fid ) . '" name="f[' . esc_attr( $f['key'] ) . ']"' . ( $req ? ' required' : '' ) . '>';
				if ( ! empty( $f['empty'] ) ) {
					echo '<option value="">' . esc_html( $f['empty'] ) . '</option>';
				}
				foreach ( $f['options'] as $k => $v ) {
					echo '<option value="' . esc_attr( $k ) . '"' . selected( $val, $k, false ) . '>' . esc_html( $v ) . '</option>';
				}
				echo '</select>';
				break;
			case 'checkboxes':
				echo '<fieldset><legend>' . esc_html( $f['label'] ) . ( $req ? ' <span class="req">*</span>' : '' ) . '</legend><div class="check-grid">';
				foreach ( $f['options'] as $k => $v ) {
					echo '<label class="check"><input type="checkbox" name="f[' . esc_attr( $f['key'] ) . '][]" value="' . esc_attr( $k ) . '"' . checked( in_array( $k, (array) $val, true ), true, false ) . '> <span>' . esc_html( $v ) . '</span></label>';
				}
				echo '</div></fieldset>';
				break;
			case 'file':
				echo '<label for="' . esc_attr( $fid ) . '">' . esc_html( $f['label'] ) . '</label>';
				echo '<input class="file-input" id="' . esc_attr( $fid ) . '" type="file" name="cwm_file" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" data-max="10485760">';
				break;
			case 'consent':
				echo '<label class="check check--consent"><input type="checkbox" name="f[consent]" value="1" required' . checked( $val, '1', false ) . '> <span>I agree to ' . esc_html( cwm_firm() ) . ' using these details to respond to me, as set out in the <a href="' . esc_url( cwm_legal_url( 'privacy' ) ) . '">privacy policy</a>. <span class="req">*</span></span></label>';
				break;
			default:
				echo '<label for="' . esc_attr( $fid ) . '">' . esc_html( $f['label'] ) . ( $req ? ' <span class="req">*</span>' : '' ) . '</label>';
				echo '<input id="' . esc_attr( $fid ) . '" type="' . esc_attr( $f['type'] ) . '" name="f[' . esc_attr( $f['key'] ) . ']" value="' . esc_attr( $val ) . '"' . ( $req ? ' required' : '' ) . ( isset( $f['autocomplete'] ) ? ' autocomplete="' . esc_attr( $f['autocomplete'] ) . '"' : '' ) . '>';
		}
		echo '</div>';
	}
	echo '</div>';
	$label = 'proposal' === $type ? 'Send my request' : 'Send message';
	echo '<div class="form-actions"><button class="btn btn--primary" type="submit">' . esc_html( $label ) . ' ' . cwm_icon( 'send' ) . '</button><p class="form-note">We reply within one working day. Fields marked * are required.</p></div>'; // phpcs:ignore WordPress.Security.EscapeOutput
	echo '</form>';
}

function cwm_current_url() {
	$uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';
	return remove_query_arg( array( 'cwm_sent', 'cwm_err' ), home_url( $uri ) );
}

/** Timestamp token to stop instant bot submissions. */
function cwm_form_token() {
	$t = time();
	return $t . '.' . substr( wp_hash( 'cwm_form' . $t ), 0, 12 );
}

/** Success or error state for a form after a redirect. */
function cwm_form_state( $type ) {
	$state = array(
		'sent'   => false,
		'errors' => array(),
		'values' => array(),
	);
	if ( cwm_get( 'cwm_sent' ) === $type ) {
		$state['sent'] = true;
	}
	$err = sanitize_key( cwm_get( 'cwm_err' ) );
	if ( $err ) {
		$data = get_transient( 'cwm_form_' . $err );
		if ( $data && $data['type'] === $type ) {
			$state['errors'] = $data['errors'];
			$state['values'] = $data['values'];
		}
	}
	return $state;
}

add_action( 'admin_post_nopriv_cwm_form', 'cwm_handle_form' );
add_action( 'admin_post_cwm_form', 'cwm_handle_form' );

function cwm_handle_form() {
	// phpcs:disable WordPress.Security.NonceVerification -- public forms; protected by honeypot, timing token and rate limit.
	$type   = isset( $_POST['cwm_type'] ) ? sanitize_key( wp_unslash( $_POST['cwm_type'] ) ) : '';
	$return = isset( $_POST['cwm_return'] ) ? esc_url_raw( wp_unslash( $_POST['cwm_return'] ) ) : home_url( '/' );
	$return = wp_validate_redirect( $return, home_url( '/' ) );
	if ( ! array_key_exists( $type, cwm_form_types() ) ) {
		wp_safe_redirect( home_url( '/' ) );
		exit;
	}
	$anchor = '#form-' . $type;

	// Bots: honeypot filled, or submitted too fast, or a forged token. Pretend success.
	$ts    = isset( $_POST['cwm_ts'] ) ? sanitize_text_field( wp_unslash( $_POST['cwm_ts'] ) ) : '';
	$parts = explode( '.', $ts );
	$valid = 2 === count( $parts ) && hash_equals( substr( wp_hash( 'cwm_form' . $parts[0] ), 0, 12 ), $parts[1] ) && ( time() - (int) $parts[0] ) >= 3;
	if ( ! empty( $_POST['cwm_website'] ) || ! $valid ) {
		wp_safe_redirect( add_query_arg( 'cwm_sent', $type, $return ) . $anchor );
		exit;
	}

	// Rate limit: 6 submissions per 10 minutes per visitor.
	$ip_key = 'cwm_rl_' . substr( md5( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' ), 0, 16 );
	$count  = (int) get_transient( $ip_key );
	if ( $count >= 6 ) {
		cwm_form_fail( $type, array( 'Too many messages in a short time. Please wait a few minutes or call us.' ), array(), $return, $anchor );
	}
	set_transient( $ip_key, $count + 1, 10 * MINUTE_IN_SECONDS );

	$raw    = isset( $_POST['f'] ) && is_array( $_POST['f'] ) ? wp_unslash( $_POST['f'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$values = array();
	$errors = array();
	foreach ( cwm_form_fields( $type ) as $f ) {
		$k = $f['key'];
		if ( 'file' === $f['type'] ) {
			continue;
		}
		if ( 'checkboxes' === $f['type'] ) {
			$v = array_values( array_intersect( array_map( 'sanitize_key', (array) ( isset( $raw[ $k ] ) ? $raw[ $k ] : array() ) ), array_map( 'strval', array_keys( $f['options'] ) ) ) );
		} elseif ( 'textarea' === $f['type'] ) {
			$v = sanitize_textarea_field( isset( $raw[ $k ] ) ? $raw[ $k ] : '' );
		} elseif ( 'email' === $f['type'] ) {
			$v = sanitize_email( isset( $raw[ $k ] ) ? $raw[ $k ] : '' );
		} else {
			$v = sanitize_text_field( isset( $raw[ $k ] ) ? $raw[ $k ] : '' );
		}
		if ( 'select' === $f['type'] && '' !== $v && ! array_key_exists( $v, $f['options'] ) ) {
			$v = '';
		}
		$values[ $k ] = $v;
		if ( ! empty( $f['required'] ) && ( '' === $v || array() === $v ) ) {
			$errors[] = 'consent' === $f['type'] ? 'Please tick the consent box so we can reply to you.' : sprintf( '%s is required.', $f['label'] );
		} elseif ( 'email' === $f['type'] && '' !== $v && ! is_email( $v ) ) {
			$errors[] = 'Please enter a valid email address.';
		}
	}
	if ( isset( $values['message'] ) && strlen( $values['message'] ) > 5000 ) {
		$errors[] = 'Your message is too long. Please keep it under 5,000 characters.';
	}

	// Attachment (proposal form only).
	$file = null;
	if ( 'proposal' === $type && ! empty( $_FILES['cwm_file']['name'] ) ) {
		$file = cwm_store_upload( $_FILES['cwm_file'], $errors ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	}

	if ( $errors ) {
		if ( $file ) {
			wp_delete_file( $file['path'] );
		}
		cwm_form_fail( $type, $errors, $values, $return, $anchor );
	}

	cwm_save_enquiry( $type, $values, $file, $return );
	wp_safe_redirect( add_query_arg( 'cwm_sent', $type, $return ) . $anchor );
	exit;
	// phpcs:enable
}

function cwm_form_fail( $type, $errors, $values, $return, $anchor ) {
	$token = strtolower( wp_generate_password( 16, false ) );
	set_transient(
		'cwm_form_' . $token,
		array(
			'type'   => $type,
			'errors' => $errors,
			'values' => $values,
		),
		15 * MINUTE_IN_SECONDS
	);
	wp_safe_redirect( add_query_arg( 'cwm_err', $token, $return ) . $anchor );
	exit;
}

/** Private folder for proposal attachments. */
function cwm_private_dir() {
	$up  = wp_upload_dir();
	$dir = trailingslashit( $up['basedir'] ) . 'cwm-private';
	if ( ! is_dir( $dir ) ) {
		wp_mkdir_p( $dir );
	}
	if ( ! file_exists( $dir . '/.htaccess' ) ) {
		file_put_contents( $dir . '/.htaccess', "Require all denied\nDeny from all\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		file_put_contents( $dir . '/index.php', "<?php\n// Silence.\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	}
	return $dir;
}

function cwm_store_upload( $f, &$errors ) {
	if ( ! empty( $f['error'] ) ) {
		$errors[] = UPLOAD_ERR_INI_SIZE === (int) $f['error'] || UPLOAD_ERR_FORM_SIZE === (int) $f['error'] ? 'The attachment is larger than 10 MB.' : 'The attachment could not be uploaded. Please try again or email it to us.';
		return null;
	}
	if ( (int) $f['size'] > 10 * MB_IN_BYTES ) {
		$errors[] = 'The attachment is larger than 10 MB.';
		return null;
	}
	$allowed = array(
		'pdf'  => 'application/pdf',
		'doc'  => 'application/msword',
		'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
	);
	$check   = wp_check_filetype_and_ext( $f['tmp_name'], $f['name'], $allowed );
	if ( empty( $check['ext'] ) || ! isset( $allowed[ $check['ext'] ] ) ) {
		$errors[] = 'Please attach a PDF or Word document.';
		return null;
	}
	$name = sanitize_file_name( pathinfo( $f['name'], PATHINFO_FILENAME ) );
	$dest = cwm_private_dir() . '/' . gmdate( 'Ymd-His' ) . '-' . wp_generate_password( 8, false ) . '-' . $name . '.' . $check['ext'];
	if ( ! @move_uploaded_file( $f['tmp_name'], $dest ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors
		$errors[] = 'The attachment could not be saved. Please email it to us instead.';
		return null;
	}
	return array(
		'path' => $dest,
		'name' => $name . '.' . $check['ext'],
	);
}

/** Save the enquiry, email the firm and (optionally) confirm to the sender. */
function cwm_save_enquiry( $type, $values, $file, $page ) {
	$types  = cwm_form_types();
	$fields = cwm_form_fields( $type );
	$who    = trim( ( isset( $values['name'] ) ? $values['name'] : '' ) . ( ! empty( $values['organisation'] ) ? ', ' . $values['organisation'] : '' ) );
	$title  = $types[ $type ] . ': ' . ( $who ? $who : $values['email'] );

	$id = wp_insert_post(
		array(
			'post_type'   => 'enquiry',
			'post_status' => 'private',
			'post_title'  => $title,
		)
	);
	if ( $id && ! is_wp_error( $id ) ) {
		update_post_meta( $id, '_cwm_type', $type );
		update_post_meta( $id, '_cwm_values', $values );
		update_post_meta( $id, '_cwm_page', $page );
		if ( $file ) {
			update_post_meta( $id, '_cwm_file', $file );
		}
	}

	// Email body.
	$lines = array();
	foreach ( $fields as $f ) {
		if ( in_array( $f['type'], array( 'file', 'consent' ), true ) || ! isset( $values[ $f['key'] ] ) ) {
			continue;
		}
		$lines[] = $f['label'] . ': ' . cwm_enquiry_value( $f, $values[ $f['key'] ] );
	}
	if ( $file ) {
		$lines[] = 'Attachment: ' . $file['name'] . ' (attached, also saved in WordPress under Enquiries)';
	}
	$lines[] = '';
	$lines[] = 'Sent from: ' . $page;
	if ( $id && ! is_wp_error( $id ) ) {
		$lines[] = 'View in WordPress: ' . admin_url( 'post.php?post=' . $id . '&action=edit' );
	}

	$to = cwm_opt( 'email_contact' );
	if ( 'proposal' === $type && cwm_opt( 'email_proposals' ) ) {
		$to = cwm_opt( 'email_proposals' );
	} elseif ( 'newsletter' === $type ) {
		$to = cwm_opt( 'email_newsletter' );
	}
	if ( ! $to && 'newsletter' !== $type ) {
		$to = cwm_opt( 'email' ) ? cwm_opt( 'email' ) : get_option( 'admin_email' );
	}
	if ( $to ) {
		$headers = array( 'Reply-To: ' . ( $who ? $who : 'Sender' ) . ' <' . $values['email'] . '>' );
		wp_mail( array_map( 'trim', explode( ',', $to ) ), '[' . cwm_firm() . '] ' . $title, implode( "\n", $lines ), $headers, $file ? array( $file['path'] ) : array() );
	}

	if ( cwm_opt( 'autoreply' ) && 'newsletter' !== $type && ! empty( $values['email'] ) ) {
		$first = isset( $values['name'] ) ? strtok( $values['name'], ' ' ) : '';
		$body  = ( $first ? 'Dear ' . $first . ",\n\n" : "Hello,\n\n" )
			. 'Thank you for contacting ' . cwm_firm() . ". We have received your message and will reply within one working day.\n\n"
			. 'If it is urgent, call us on ' . cwm_opt( 'phone' ) . ".\n\n"
			. cwm_firm() . "\n" . cwm_opt( 'tagline' ) . "\n" . home_url( '/' );
		wp_mail( $values['email'], 'We have your message: ' . cwm_firm(), $body );
	}
}

function cwm_enquiry_value( $f, $v ) {
	if ( 'checkboxes' === $f['type'] ) {
		$labels = array();
		foreach ( (array) $v as $k ) {
			$labels[] = isset( $f['options'][ $k ] ) ? $f['options'][ $k ] : $k;
		}
		return implode( ', ', $labels );
	}
	if ( 'select' === $f['type'] && isset( $f['options'][ $v ] ) ) {
		return $f['options'][ $v ];
	}
	return (string) $v;
}

/* ---------- Admin: enquiries list, details and export ---------- */

add_filter(
	'manage_enquiry_posts_columns',
	function () {
		return array(
			'cb'       => '<input type="checkbox">',
			'title'    => 'Enquiry',
			'cwm_type' => 'Form',
			'cwm_mail' => 'Email',
			'cwm_svc'  => 'Service',
			'date'     => 'Received',
		);
	}
);

add_action(
	'manage_enquiry_posts_custom_column',
	function ( $col, $id ) {
		$v = (array) get_post_meta( $id, '_cwm_values', true );
		if ( 'cwm_type' === $col ) {
			$t = cwm_form_types();
			$k = get_post_meta( $id, '_cwm_type', true );
			echo esc_html( isset( $t[ $k ] ) ? $t[ $k ] : $k );
		} elseif ( 'cwm_mail' === $col && ! empty( $v['email'] ) ) {
			echo '<a href="mailto:' . esc_attr( $v['email'] ) . '">' . esc_html( $v['email'] ) . '</a>';
		} elseif ( 'cwm_svc' === $col ) {
			$s = isset( $v['service'] ) ? $v['service'] : ( isset( $v['services'] ) ? implode( ', ', (array) $v['services'] ) : '' );
			echo esc_html( $s );
		}
	},
	10,
	2
);

add_action(
	'add_meta_boxes_enquiry',
	function () {
		add_meta_box( 'cwm_enquiry', 'Details', 'cwm_enquiry_box', 'enquiry', 'normal', 'high' );
		remove_meta_box( 'submitdiv', 'enquiry', 'side' );
	}
);

function cwm_enquiry_box( $post ) {
	$type   = get_post_meta( $post->ID, '_cwm_type', true );
	$values = (array) get_post_meta( $post->ID, '_cwm_values', true );
	$file   = get_post_meta( $post->ID, '_cwm_file', true );
	echo '<table class="widefat striped"><tbody>';
	echo '<tr><th>Received</th><td>' . esc_html( get_the_date( 'j F Y, g:i a', $post ) ) . '</td></tr>';
	foreach ( cwm_form_fields( $type ) as $f ) {
		if ( 'file' === $f['type'] || ! isset( $values[ $f['key'] ] ) ) {
			continue;
		}
		$label = 'consent' === $f['type'] ? 'Consent given' : $f['label'];
		$val   = 'consent' === $f['type'] ? ( $values[ $f['key'] ] ? 'Yes' : 'No' ) : cwm_enquiry_value( $f, $values[ $f['key'] ] );
		echo '<tr><th style="width:220px">' . esc_html( $label ) . '</th><td>' . nl2br( esc_html( $val ) ) . '</td></tr>';
	}
	if ( $file ) {
		$url = wp_nonce_url( admin_url( 'admin-post.php?action=cwm_file&id=' . $post->ID ), 'cwm_file_' . $post->ID );
		echo '<tr><th>Attachment</th><td><a class="button" href="' . esc_url( $url ) . '">Download ' . esc_html( $file['name'] ) . '</a></td></tr>';
	}
	echo '<tr><th>Sent from</th><td>' . esc_html( get_post_meta( $post->ID, '_cwm_page', true ) ) . '</td></tr>';
	echo '</tbody></table>';
	echo '<p><a class="button button-link-delete" href="' . esc_url( get_delete_post_link( $post->ID ) ) . '">Move to bin</a></p>';
}

add_action(
	'admin_post_cwm_file',
	function () {
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		check_admin_referer( 'cwm_file_' . $id );
		if ( ! current_user_can( 'edit_post', $id ) ) {
			wp_die( 'Not allowed.' );
		}
		$file = get_post_meta( $id, '_cwm_file', true );
		if ( ! $file || ! file_exists( $file['path'] ) || 0 !== strpos( realpath( $file['path'] ), realpath( cwm_private_dir() ) ) ) {
			wp_die( 'File not found.' );
		}
		nocache_headers();
		header( 'Content-Type: ' . wp_check_filetype( $file['path'] )['type'] );
		header( 'Content-Disposition: attachment; filename="' . basename( $file['name'] ) . '"' );
		header( 'Content-Length: ' . filesize( $file['path'] ) );
		readfile( $file['path'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		exit;
	}
);

/** Delete stored attachments with their enquiry. */
add_action(
	'before_delete_post',
	function ( $id ) {
		if ( 'enquiry' !== get_post_type( $id ) ) {
			return;
		}
		$file = get_post_meta( $id, '_cwm_file', true );
		if ( $file && ! empty( $file['path'] ) && file_exists( $file['path'] ) ) {
			wp_delete_file( $file['path'] );
		}
	}
);

/** CSV export button on the Enquiries screen. */
add_action(
	'restrict_manage_posts',
	function ( $post_type ) {
		if ( 'enquiry' !== $post_type ) {
			return;
		}
		echo '<select name="cwm_form_type"><option value="">All forms</option>';
		foreach ( cwm_form_types() as $k => $v ) {
			echo '<option value="' . esc_attr( $k ) . '"' . selected( cwm_get( 'cwm_form_type' ), $k, false ) . '>' . esc_html( $v ) . '</option>';
		}
		echo '</select>';
		$url = wp_nonce_url( admin_url( 'admin-post.php?action=cwm_export&type=' . rawurlencode( cwm_get( 'cwm_form_type' ) ) ), 'cwm_export' );
		echo ' <a class="button" href="' . esc_url( $url ) . '">Export CSV</a> ';
	}
);

add_action(
	'pre_get_posts',
	function ( $q ) {
		if ( is_admin() && $q->is_main_query() && 'enquiry' === $q->get( 'post_type' ) && cwm_get( 'cwm_form_type' ) ) {
			$q->set( 'meta_key', '_cwm_type' );
			$q->set( 'meta_value', sanitize_key( cwm_get( 'cwm_form_type' ) ) );
		}
	}
);

add_action(
	'admin_post_cwm_export',
	function () {
		check_admin_referer( 'cwm_export' );
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( 'Not allowed.' );
		}
		$type = sanitize_key( cwm_get( 'type' ) );
		$args = array(
			'post_type'   => 'enquiry',
			'post_status' => 'private',
			'numberposts' => -1,
		);
		if ( $type ) {
			$args['meta_key']   = '_cwm_type';
			$args['meta_value'] = $type;
		}
		$rows = get_posts( $args );
		$keys = array();
		foreach ( $rows as $r ) {
			$keys = array_unique( array_merge( $keys, array_keys( (array) get_post_meta( $r->ID, '_cwm_values', true ) ) ) );
		}
		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="enquiries-' . ( $type ? $type . '-' : '' ) . gmdate( 'Y-m-d' ) . '.csv"' );
		$out = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		fputcsv( $out, array_merge( array( 'Received', 'Form' ), $keys ) );
		foreach ( $rows as $r ) {
			$v    = (array) get_post_meta( $r->ID, '_cwm_values', true );
			$line = array( get_the_date( 'Y-m-d H:i', $r ), get_post_meta( $r->ID, '_cwm_type', true ) );
			foreach ( $keys as $k ) {
				$cell   = isset( $v[ $k ] ) ? ( is_array( $v[ $k ] ) ? implode( '; ', $v[ $k ] ) : $v[ $k ] ) : '';
				$line[] = preg_match( '/^[=+\-@]/', $cell ) ? "'" . $cell : $cell; // Guard against spreadsheet formulas.
			}
			fputcsv( $out, $line );
		}
		fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		exit;
	}
);

/** Unread count bubble on the Enquiries menu. */
add_action(
	'admin_menu',
	function () {
		global $menu;
		$since = (int) get_user_meta( get_current_user_id(), 'cwm_enquiries_seen', true );
		$new   = get_posts(
			array(
				'post_type'   => 'enquiry',
				'post_status' => 'private',
				'numberposts' => 50,
				'fields'      => 'ids',
				'date_query'  => array(
					array(
						'column' => 'post_date_gmt',
						'after'  => gmdate( 'Y-m-d H:i:s', $since ),
					),
				),
			)
		);
		if ( ! $new ) {
			return;
		}
		foreach ( $menu as $i => $item ) {
			if ( 'edit.php?post_type=enquiry' === $item[2] ) {
				$menu[ $i ][0] .= ' <span class="awaiting-mod">' . count( $new ) . '</span>'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
			}
		}
	},
	99
);
add_action(
	'load-edit.php',
	function () {
		if ( 'enquiry' === cwm_get( 'post_type' ) ) {
			update_user_meta( get_current_user_id(), 'cwm_enquiries_seen', time() );
		}
	}
);
