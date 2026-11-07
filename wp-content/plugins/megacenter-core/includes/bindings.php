<?php
/**
 * Block Bindings-bronnen:
 * - megacenter/activity: opgemaakte activiteit-gegevens ("Vanaf 8 jaar", "€ 17,50 p.p.", reserveerlink).
 * - megacenter/site: naam, contactgegevens, adres en reserveerlink uit de instellingen.
 *
 * Daarnaast gebruiken de templates de ingebouwde bron core/post-meta voor ruwe velden (tagline).
 *
 * @package MegacenterCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registreert de bronnen.
 */
function megacenter_register_binding_sources() {
	if ( ! function_exists( 'register_block_bindings_source' ) ) {
		return;
	}
	register_block_bindings_source(
		'megacenter/activity',
		array(
			'label'              => __( 'Activiteit-gegevens', 'megacenter-core' ),
			'get_value_callback' => 'megacenter_binding_activity_value',
			'uses_context'       => array( 'postId', 'postType' ),
		)
	);
	register_block_bindings_source(
		'megacenter/site',
		array(
			'label'              => __( 'Megacenter-instellingen', 'megacenter-core' ),
			'get_value_callback' => 'megacenter_binding_site_value',
		)
	);
}
add_action( 'init', 'megacenter_register_binding_sources' );

/**
 * Waarde voor megacenter/activity.
 *
 * @param array    $source_args Argumenten (key).
 * @param WP_Block $block_instance Blok.
 * @param string   $attribute_name Attribuut.
 * @return string|null
 */
function megacenter_binding_activity_value( array $source_args, $block_instance, string $attribute_name ) {
	$post_id = $block_instance->context['postId'] ?? get_the_ID();
	if ( ! $post_id || 'activiteit' !== get_post_type( $post_id ) ) {
		return null;
	}
	$data = megacenter_activity_data( (int) $post_id );
	$key  = $source_args['key'] ?? '';

	if ( 'reserve_url' === $key ) {
		return esc_url_raw( $data['reserve_url'] );
	}
	if ( 'reserve_label' === $key ) {
		/* translators: %s: naam van de activiteit */
		return esc_html( sprintf( __( 'Reserveer %s', 'megacenter-core' ), get_the_title( $post_id ) ) );
	}
	if ( 'title' === $key ) {
		return esc_html( get_the_title( $post_id ) );
	}
	if ( isset( $data[ $key ] ) && is_string( $data[ $key ] ) ) {
		return esc_html( $data[ $key ] );
	}
	return null;
}

/**
 * Waarde voor megacenter/site.
 *
 * @param array $source_args Argumenten (key).
 * @return string|null
 */
function megacenter_binding_site_value( array $source_args ) {
	$key = $source_args['key'] ?? '';
	$s   = megacenter_get_settings();

	switch ( $key ) {
		case 'name':
			return esc_html( megacenter_site_name() );
		case 'tagline':
			return esc_html( $s['tagline'] );
		case 'phone':
			return esc_html( $s['phone'] );
		case 'phone_url':
			return esc_url_raw( megacenter_phone_url(), array( 'tel' ) );
		case 'email':
			return esc_html( $s['email'] );
		case 'email_url':
			return $s['email'] ? 'mailto:' . sanitize_email( $s['email'] ) : '';
		case 'address':
			return esc_html( megacenter_address_line() );
		case 'route_text':
			return esc_html( $s['route_text'] );
		case 'route_url':
			$query = rawurlencode( trim( megacenter_site_name() . ' ' . megacenter_address_line() ) );
			return esc_url_raw( 'https://www.google.com/maps/dir/?api=1&destination=' . $query );
		case 'hours_note':
			return esc_html( $s['hours_note'] );
		case 'reserve_url':
			return esc_url_raw( megacenter_reserve_page_url() );
		case 'copyright':
			/* translators: 1: jaartal, 2: naam */
			return esc_html( sprintf( __( '© %1$s %2$s', 'megacenter-core' ), wp_date( 'Y' ), megacenter_site_name() ) );
	}
	return null;
}

/**
 * Waarden die de editor nodig heeft om bindings netjes te tonen (geen gevoelige gegevens).
 *
 * @return array<string,string>
 */
function megacenter_editor_site_values() {
	$keys = array( 'name', 'tagline', 'phone', 'phone_url', 'email', 'email_url', 'address', 'route_text', 'route_url', 'hours_note', 'reserve_url', 'copyright' );
	$out  = array();
	foreach ( $keys as $key ) {
		$out[ $key ] = html_entity_decode( (string) megacenter_binding_site_value( array( 'key' => $key ) ), ENT_QUOTES );
	}
	return $out;
}
