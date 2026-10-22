<?php
/**
 * A small field framework used by Firm settings and every meta box.
 * No plugins needed: text, textarea, select, checkbox, media, post pickers and repeaters.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render one field.
 *
 * @param array  $f     Field definition.
 * @param mixed  $value Current value.
 * @param string $name  Input name.
 */
function cwm_render_field( $f, $value, $name ) {
	$type  = isset( $f['type'] ) ? $f['type'] : 'text';
	$id    = 'cwm_' . preg_replace( '/[^a-z0-9_]/i', '_', $name );
	$label = isset( $f['label'] ) ? $f['label'] : '';
	$help  = isset( $f['help'] ) ? $f['help'] : '';
	if ( ( null === $value || '' === $value ) && isset( $f['default'] ) && 'repeater' !== $type ) {
		$value = $f['default'];
	}

	echo '<div class="cwm-field cwm-field--' . esc_attr( $type ) . '">';
	if ( 'checkbox' !== $type && $label ) {
		echo '<label class="cwm-field__label" for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label>';
	}

	switch ( $type ) {
		case 'textarea':
			$rows = isset( $f['rows'] ) ? (int) $f['rows'] : 4;
			echo '<textarea class="widefat" rows="' . esc_attr( $rows ) . '" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '">' . esc_textarea( (string) $value ) . '</textarea>';
			break;

		case 'select':
		case 'icon':
			$options = 'icon' === $type ? cwm_icon_choices() : $f['options'];
			echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '"' . ( 'icon' === $type ? ' class="cwm-icon-select"' : '' ) . '>';
			if ( ! empty( $f['empty'] ) ) {
				echo '<option value="">' . esc_html( $f['empty'] ) . '</option>';
			}
			foreach ( $options as $k => $v ) {
				echo '<option value="' . esc_attr( $k ) . '"' . selected( (string) $value, (string) $k, false ) . '>' . esc_html( $v ) . '</option>';
			}
			echo '</select>';
			if ( 'icon' === $type ) {
				echo '<span class="cwm-icon-preview" aria-hidden="true">' . cwm_icon( $value ? $value : 'circle-check' ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput
			}
			break;

		case 'checkbox':
			echo '<label class="cwm-check"><input type="hidden" name="' . esc_attr( $name ) . '" value="0"><input type="checkbox" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="1"' . checked( (string) $value, '1', false ) . '> ' . esc_html( $label ) . '</label>';
			break;

		case 'media':
			$url   = $value ? wp_get_attachment_url( (int) $value ) : '';
			$thumb = $value ? wp_get_attachment_image( (int) $value, 'thumbnail', true ) : '';
			echo '<div class="cwm-media" data-type="' . esc_attr( isset( $f['library'] ) ? $f['library'] : '' ) . '">';
			echo '<input type="hidden" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( (string) $value ) . '">';
			echo '<span class="cwm-media__preview">' . ( $url ? $thumb . '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . esc_html( wp_basename( $url ) ) . '</a>' : '<em>No file selected</em>' ) . '</span> '; // phpcs:ignore WordPress.Security.EscapeOutput
			echo '<button type="button" class="button cwm-media__choose">Choose file</button> ';
			echo '<button type="button" class="button-link-delete cwm-media__remove"' . ( $url ? '' : ' hidden' ) . '>Remove</button>';
			echo '</div>';
			break;

		case 'post':
			$posts = get_posts(
				array(
					'post_type'   => $f['post_type'],
					'post_status' => array( 'publish', 'draft', 'private' ),
					'numberposts' => 200,
					'orderby'     => 'title',
					'order'       => 'ASC',
				)
			);
			echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '"><option value="">' . esc_html( isset( $f['empty'] ) ? $f['empty'] : '— None —' ) . '</option>';
			foreach ( $posts as $p ) {
				echo '<option value="' . esc_attr( $p->ID ) . '"' . selected( (int) $value, $p->ID, false ) . '>' . esc_html( $p->post_title ) . ( 'publish' !== $p->post_status ? ' (' . esc_html( $p->post_status ) . ')' : '' ) . '</option>';
			}
			echo '</select>';
			break;

		case 'posts':
			$value = (array) $value;
			$posts = get_posts(
				array(
					'post_type'   => $f['post_type'],
					'post_status' => 'publish',
					'numberposts' => 200,
					'orderby'     => array(
						'menu_order' => 'ASC',
						'title'      => 'ASC',
					),
				)
			);
			echo '<input type="hidden" name="' . esc_attr( $name ) . '[]" value="">';
			echo '<div class="cwm-checklist">';
			foreach ( $posts as $p ) {
				echo '<label><input type="checkbox" name="' . esc_attr( $name ) . '[]" value="' . esc_attr( $p->ID ) . '"' . checked( in_array( $p->ID, array_map( 'intval', $value ), true ), true, false ) . '> ' . esc_html( $p->post_title ) . '</label>';
			}
			echo '</div>';
			break;

		case 'repeater':
			$rows = is_array( $value ) ? array_values( $value ) : array();
			$max  = isset( $f['max'] ) ? (int) $f['max'] : 0;
			echo '<div class="cwm-repeater" data-max="' . esc_attr( $max ) . '" data-name="' . esc_attr( $name ) . '">';
			echo '<div class="cwm-repeater__rows">';
			foreach ( $rows as $i => $row ) {
				cwm_render_repeater_row( $f, $row, $name . '[' . $i . ']' );
			}
			echo '</div>';
			echo '<script type="text/html" class="cwm-repeater__tpl">';
			cwm_render_repeater_row( $f, array(), $name . '[__i__]' );
			echo '</script>';
			echo '<button type="button" class="button cwm-repeater__add">+ ' . esc_html( isset( $f['add_label'] ) ? $f['add_label'] : 'Add row' ) . '</button>';
			echo '</div>';
			break;

		default:
			$input_type = in_array( $type, array( 'url', 'email', 'number', 'date' ), true ) ? $type : 'text';
			echo '<input class="widefat" type="' . esc_attr( $input_type ) . '" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( (string) $value ) . '"' . ( isset( $f['placeholder'] ) ? ' placeholder="' . esc_attr( $f['placeholder'] ) . '"' : '' ) . '>';
	}

	if ( $help ) {
		echo '<p class="description">' . wp_kses_post( $help ) . '</p>';
	}
	echo '</div>';
}

function cwm_render_repeater_row( $f, $row, $prefix ) {
	echo '<div class="cwm-repeater__row">';
	echo '<div class="cwm-repeater__tools"><button type="button" class="button-link cwm-repeater__up" aria-label="Move up">▲</button><button type="button" class="button-link cwm-repeater__down" aria-label="Move down">▼</button><button type="button" class="button-link-delete cwm-repeater__remove">Remove</button></div>';
	echo '<div class="cwm-repeater__fields">';
	foreach ( $f['fields'] as $sub ) {
		cwm_render_field( $sub, isset( $row[ $sub['key'] ] ) ? $row[ $sub['key'] ] : '', $prefix . '[' . $sub['key'] . ']' );
	}
	echo '</div></div>';
}

/**
 * Sanitize one field's submitted value.
 */
function cwm_sanitize_field( $f, $raw ) {
	$type = isset( $f['type'] ) ? $f['type'] : 'text';
	switch ( $type ) {
		case 'textarea':
			return current_user_can( 'unfiltered_html' ) ? trim( wp_unslash( (string) $raw ) ) : trim( wp_kses_post( wp_unslash( (string) $raw ) ) );
		case 'checkbox':
			return ( '1' === (string) $raw ) ? '1' : '';
		case 'media':
		case 'post':
			return $raw ? (string) absint( $raw ) : '';
		case 'number':
			return ( '' === $raw || null === $raw ) ? '' : (string) floatval( $raw );
		case 'posts':
			return array_values( array_filter( array_map( 'absint', (array) $raw ) ) );
		case 'url':
			return esc_url_raw( trim( wp_unslash( (string) $raw ) ) );
		case 'email':
			return sanitize_text_field( wp_unslash( (string) $raw ) );
		case 'select':
		case 'icon':
			$raw = sanitize_text_field( wp_unslash( (string) $raw ) );
			$ok  = 'icon' === $type ? cwm_icon_choices() : $f['options'];
			return array_key_exists( $raw, $ok ) ? $raw : '';
		case 'repeater':
			$out = array();
			foreach ( (array) $raw as $k => $row ) {
				if ( '__i__' === (string) $k || ! is_array( $row ) ) {
					continue;
				}
				$clean = array();
				$empty = true;
				foreach ( $f['fields'] as $sub ) {
					$clean[ $sub['key'] ] = cwm_sanitize_field( $sub, isset( $row[ $sub['key'] ] ) ? $row[ $sub['key'] ] : '' );
					if ( '' !== $clean[ $sub['key'] ] && 'checkbox' !== $sub['type'] && 'select' !== $sub['type'] && 'icon' !== $sub['type'] ) {
						$empty = false;
					}
				}
				if ( ! $empty ) {
					$out[] = $clean;
				}
			}
			if ( ! empty( $f['max'] ) ) {
				$out = array_slice( $out, 0, (int) $f['max'] );
			}
			return $out;
		default:
			return sanitize_text_field( wp_unslash( (string) $raw ) );
	}
}

/** Admin assets for fields. */
add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}
		if ( in_array( $screen->base, array( 'post', 'appearance_page_cwm-settings', 'toplevel_page_cwm-settings' ), true ) || false !== strpos( $hook, 'cwm-settings' ) ) {
			wp_enqueue_media();
			wp_enqueue_style( 'cwm-admin', CWM_URI . '/assets/css/admin.css', array(), CWM_VERSION );
			wp_enqueue_script( 'cwm-admin', CWM_URI . '/assets/js/admin.js', array(), CWM_VERSION, true );
			$icons = array();
			foreach ( array_keys( cwm_icon_choices() ) as $k ) {
				$icons[ $k ] = cwm_icon( $k );
			}
			wp_add_inline_script( 'cwm-admin', 'window.cwmIcons=' . wp_json_encode( $icons ) . ';', 'before' );
		}
	}
);
