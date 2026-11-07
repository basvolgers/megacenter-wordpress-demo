<?php
/**
 * Hulpfuncties: instellingen, accentkleuren, opmaak van activiteit-gegevens.
 *
 * @package MegacenterCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Dagen van de week (sleutel => label), maandag eerst.
 *
 * @return array<string,string>
 */
function megacenter_days() {
	return array(
		'mon' => __( 'Maandag', 'megacenter-core' ),
		'tue' => __( 'Dinsdag', 'megacenter-core' ),
		'wed' => __( 'Woensdag', 'megacenter-core' ),
		'thu' => __( 'Donderdag', 'megacenter-core' ),
		'fri' => __( 'Vrijdag', 'megacenter-core' ),
		'sat' => __( 'Zaterdag', 'megacenter-core' ),
		'sun' => __( 'Zondag', 'megacenter-core' ),
	);
}

/**
 * Standaardwaarden voor de instellingen. Dit zijn voorbeeldwaarden voor de demo.
 *
 * @return array<string,mixed>
 */
function megacenter_settings_defaults() {
	return array(
		'tagline'          => 'FUN begint hier.',
		'phone'            => '0548 00 00 00',
		'email'            => 'info@voorbeeld.nl',
		'notify_email'     => '',
		'street'           => 'Adres volgt',
		'postcode'         => '',
		'city'             => 'Rijssen',
		'route_text'       => 'Vlak bij de A1, met gratis parkeren voor de deur. Kom je met het OV? Vanaf station Rijssen ben je er met de bus of fiets in een paar minuten.',
		'hours'            => array(
			'mon' => array( 'open' => '14:00', 'close' => '22:00' ),
			'tue' => array( 'open' => '14:00', 'close' => '22:00' ),
			'wed' => array( 'open' => '12:00', 'close' => '22:00' ),
			'thu' => array( 'open' => '14:00', 'close' => '22:00' ),
			'fri' => array( 'open' => '14:00', 'close' => '23:30' ),
			'sat' => array( 'open' => '10:00', 'close' => '23:30' ),
			'sun' => array( 'open' => '10:00', 'close' => '21:00' ),
		),
		'hours_note'       => 'In schoolvakanties elke dag open vanaf 10:00. Groepen vanaf 12 personen ook buiten openingstijden op aanvraag.',
		'reserve_page'     => 0,
		'reserve_url'      => '',
		'reserve_embed'    => '',
		'instagram'        => 'https://www.instagram.com/',
		'facebook'         => 'https://www.facebook.com/',
		'tiktok'           => 'https://www.tiktok.com/',
		'linkedin'         => 'https://www.linkedin.com/',
		'youtube'          => '',
		'demo_bar'         => 1,
		'demo_bar_text'    => 'Demo: naam, teksten, prijzen en beelden zijn voorbeelden.',
		'seo_description'  => 'Lasergame, bowling, escape rooms, sim racing en meer onder één dak in Rijssen. Voor kinderfeestjes, vrienden, vrijgezellen en bedrijfsuitjes. Met eten en drinken erbij.',
	);
}

/**
 * Alle instellingen, aangevuld met standaardwaarden.
 *
 * @return array<string,mixed>
 */
function megacenter_get_settings() {
	$saved = get_option( 'megacenter_settings', array() );
	$saved = is_array( $saved ) ? $saved : array();
	$all   = array_merge( megacenter_settings_defaults(), $saved );
	if ( ! is_array( $all['hours'] ) ) {
		$all['hours'] = megacenter_settings_defaults()['hours'];
	}
	return $all;
}

/**
 * Eén instelling ophalen.
 *
 * @param string $key Sleutel.
 * @return mixed
 */
function megacenter_setting( $key ) {
	$all = megacenter_get_settings();
	return $all[ $key ] ?? '';
}

/**
 * Naam van de locatie (werktitel). Bron: de sitetitel van WordPress.
 *
 * @return string
 */
function megacenter_site_name() {
	$name = get_bloginfo( 'name' );
	return $name ? $name : 'Megacenter Rijssen';
}

/**
 * Telefoonnummer als tel:-link.
 *
 * @return string
 */
function megacenter_phone_url() {
	$digits = preg_replace( '/[^0-9+]/', '', (string) megacenter_setting( 'phone' ) );
	if ( '' === $digits ) {
		return '';
	}
	if ( str_starts_with( $digits, '0' ) ) {
		$digits = '+31' . substr( $digits, 1 );
	}
	return 'tel:' . $digits;
}

/**
 * Volledig adres op één regel.
 *
 * @return string
 */
function megacenter_address_line() {
	$s     = megacenter_get_settings();
	$parts = array_filter( array( $s['street'], trim( $s['postcode'] . ' ' . $s['city'] ) ) );
	return implode( ' · ', $parts );
}

/**
 * URL van de reserveringspagina (algemeen).
 *
 * @return string
 */
function megacenter_reserve_page_url() {
	$page_id = (int) megacenter_setting( 'reserve_page' );
	if ( $page_id && 'publish' === get_post_status( $page_id ) ) {
		return get_permalink( $page_id );
	}
	$page = get_page_by_path( 'reserveren' );
	return $page ? get_permalink( $page ) : home_url( '/reserveren/' );
}

/**
 * Accentkleuren die een activiteit kan krijgen (slug => label).
 *
 * @return array<string,string>
 */
function megacenter_accents() {
	return array(
		'accent-1' => __( 'Limoen', 'megacenter-core' ),
		'accent-2' => __( 'Magenta', 'megacenter-core' ),
		'accent-3' => __( 'IJsblauw', 'megacenter-core' ),
		'accent-4' => __( 'Zon', 'megacenter-core' ),
	);
}

/**
 * Accent-slug van een activiteit (met fallback).
 *
 * @param int $post_id Post-ID.
 * @return string
 */
function megacenter_activity_accent( $post_id ) {
	$accent = (string) get_post_meta( $post_id, 'megacenter_accent', true );
	return array_key_exists( $accent, megacenter_accents() ) ? $accent : 'accent-1';
}

/**
 * Prijs netjes in euro's: 17.5 => "€ 17,50", 10 => "€ 10".
 *
 * @param float|string $amount Bedrag.
 * @return string
 */
function megacenter_format_price( $amount ) {
	$amount = (float) $amount;
	$fmt    = ( floor( $amount ) === $amount ) ? number_format( $amount, 0, ',', '.' ) : number_format( $amount, 2, ',', '.' );
	return '€ ' . $fmt;
}

/**
 * Prijseenheden voor de "vanaf"-prijs.
 *
 * @return array<string,string>
 */
function megacenter_price_units() {
	return array(
		'pp'       => __( 'p.p.', 'megacenter-core' ),
		'kind'     => __( 'per kind', 'megacenter-core' ),
		'baan'     => __( 'per baan', 'megacenter-core' ),
		'sessie'   => __( 'per sessie', 'megacenter-core' ),
		'tegoed'   => __( 'speeltegoed', 'megacenter-core' ),
		'kamer'    => __( 'per kamer', 'megacenter-core' ),
	);
}

/**
 * Alle activiteit-gegevens, ruw en als leesbare labels.
 *
 * @param int $post_id Post-ID.
 * @return array<string,mixed>
 */
function megacenter_activity_data( $post_id ) {
	$get       = static fn( $key ) => get_post_meta( $post_id, 'megacenter_' . $key, true );
	$age_min   = (int) $get( 'age_min' );
	$age_max   = (int) $get( 'age_max' );
	$duration  = (int) $get( 'duration' );
	$group_min = (int) $get( 'group_min' );
	$group_max = (int) $get( 'group_max' );
	$price     = $get( 'price_from' );
	$unit_key  = (string) $get( 'price_unit' );
	$units     = megacenter_price_units();
	$unit      = $units[ $unit_key ] ?? $units['pp'];

	if ( $age_min && $age_max ) {
		/* translators: 1: minimum leeftijd, 2: maximum leeftijd */
		$age = sprintf( __( '%1$d–%2$d jaar', 'megacenter-core' ), $age_min, $age_max );
	} elseif ( $age_min ) {
		/* translators: %d: minimum leeftijd */
		$age = sprintf( __( 'Vanaf %d jaar', 'megacenter-core' ), $age_min );
	} else {
		$age = __( 'Alle leeftijden', 'megacenter-core' );
	}

	if ( $duration ) {
		/* translators: %d: duur in minuten */
		$duration_label = $duration >= 90 && 0 === $duration % 30 ? sprintf( __( '%s uur', 'megacenter-core' ), str_replace( '.', ',', (string) ( $duration / 60 ) ) ) : sprintf( __( '%d min', 'megacenter-core' ), $duration );
	} else {
		$duration_label = __( 'Vrij spelen', 'megacenter-core' );
	}

	if ( $group_min && $group_max ) {
		/* translators: 1: minimum, 2: maximum aantal personen */
		$group = sprintf( __( '%1$d–%2$d pers.', 'megacenter-core' ), $group_min, $group_max );
	} elseif ( $group_max ) {
		/* translators: %d: maximum aantal personen */
		$group = sprintf( __( 'Tot %d pers.', 'megacenter-core' ), $group_max );
	} elseif ( $group_min ) {
		/* translators: %d: minimum aantal personen */
		$group = sprintf( __( 'Vanaf %d pers.', 'megacenter-core' ), $group_min );
	} else {
		$group = __( 'Iedereen', 'megacenter-core' );
	}

	$price_label = ( '' !== $price && null !== $price ) ? sprintf( '%s %s', megacenter_format_price( $price ), $unit ) : '';

	$reserve = (string) $get( 'reserve_url' );
	if ( '' === $reserve ) {
		$reserve = add_query_arg( 'voor', get_post_field( 'post_name', $post_id ), megacenter_reserve_page_url() );
	}

	return array(
		'tagline'        => (string) $get( 'tagline' ),
		'badge'          => (string) $get( 'badge' ),
		'age'            => $age,
		'duration'       => $duration_label,
		'group'          => $group,
		'price'          => $price_label,
		'price_short'    => '' !== $price_label ? sprintf( /* translators: %s: prijs */ __( 'vanaf %s', 'megacenter-core' ), $price_label ) : '',
		'reserve_url'    => $reserve,
		'accent'         => megacenter_activity_accent( $post_id ),
		'highlights'     => array_values( array_filter( (array) $get( 'highlights' ) ) ),
		'faq'            => array_values( array_filter( (array) $get( 'faq' ), static fn( $item ) => is_array( $item ) && ! empty( $item['q'] ) ) ),
	);
}

/**
 * Inline SVG-icoon (eigen, simpele lijn-iconen). Altijd decoratief (aria-hidden).
 *
 * @param string $name Naam van het icoon.
 * @return string
 */
function megacenter_icon( $name ) {
	$paths = array(
		'age'      => '<circle cx="12" cy="7" r="3.2"/><path d="M5.5 20c.6-3.6 3.2-6 6.5-6s5.9 2.4 6.5 6"/>',
		'clock'    => '<circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/>',
		'group'    => '<circle cx="8.5" cy="8.5" r="2.7"/><circle cx="16" cy="9.5" r="2.2"/><path d="M3.5 19c.5-3 2.4-5 5-5s4.5 2 5 5M14 14.3c2.7-.4 4.9 1.4 5.5 4.7"/>',
		'ticket'   => '<path d="M4 7.5h16v3a1.8 1.8 0 0 0 0 3v3H4v-3a1.8 1.8 0 0 0 0-3z"/><path d="M14 7.5v9" stroke-dasharray="1.6 1.8"/>',
		'check'    => '<path d="M5 12.5l4.2 4.2L19 7"/>',
		'arrow'    => '<path d="M5 12h13M13 6.5l5.5 5.5-5.5 5.5"/>',
		'phone'    => '<path d="M6.5 3.8h3l1.5 4-2 1.3a10.5 10.5 0 0 0 5.9 5.9l1.3-2 4 1.5v3a2 2 0 0 1-2.2 2A16.5 16.5 0 0 1 4.5 6a2 2 0 0 1 2-2.2z"/>',
		'mail'     => '<rect x="3.5" y="5.5" width="17" height="13" rx="2"/><path d="M4 7l8 6 8-6"/>',
		'pin'      => '<path d="M12 21s-6.5-5.6-6.5-11a6.5 6.5 0 0 1 13 0c0 5.4-6.5 11-6.5 11z"/><circle cx="12" cy="10" r="2.4"/>',
		'star'     => '<path d="M12 3.8l2.4 5 5.4.7-4 3.7 1 5.4-4.8-2.6-4.8 2.6 1-5.4-4-3.7 5.4-.7z"/>',
		'spark'    => '<path d="M12 3v5M12 16v5M3 12h5M16 12h5M6 6l2.5 2.5M15.5 15.5L18 18M18 6l-2.5 2.5M8.5 15.5L6 18"/>',
		'calendar' => '<rect x="3.5" y="5" width="17" height="15" rx="2"/><path d="M3.5 10h17M8 3v4M16 3v4"/>',
		'car'      => '<path d="M4 15.5l1.6-5A2 2 0 0 1 7.5 9h9a2 2 0 0 1 1.9 1.5l1.6 5V18H4z"/><circle cx="8" cy="15.5" r="1.2"/><circle cx="16" cy="15.5" r="1.2"/>',
		'instagram' => '<rect x="4" y="4" width="16" height="16" rx="4.5"/><circle cx="12" cy="12" r="3.6"/><circle cx="16.8" cy="7.2" r=".6" fill="currentColor"/>',
		'facebook' => '<path d="M14.5 8H16V4.5h-2.3C11.3 4.5 10 6 10 8.4V10H8v3.4h2V20h3.5v-6.6h2.3l.4-3.4h-2.7V8.6c0-.4.4-.6 1-.6z"/>',
		'tiktok'   => '<path d="M14 4v10.2a3.3 3.3 0 1 1-3.3-3.3M14 4c.4 2.3 2 3.9 4.5 4.1"/>',
		'linkedin' => '<rect x="4" y="4" width="16" height="16" rx="2.5"/><path d="M8 10.5V16M8 7.8v.1M11.5 16v-3.2c0-1.4.9-2.3 2-2.3s2 .8 2 2.3V16M11.5 10.5V16"/>',
		'youtube'  => '<rect x="3.5" y="6" width="17" height="12" rx="3.5"/><path d="M10.5 9.5l4 2.5-4 2.5z"/>',
	);
	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}
	return '<svg class="mc-icon mc-icon--' . esc_attr( $name ) . '" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $paths[ $name ] . '</svg>';
}

/**
 * Activiteiten ophalen (gecachet per request), gesorteerd op volgorde.
 *
 * @return WP_Post[]
 */
function megacenter_get_activities() {
	static $cache = null;
	if ( null === $cache ) {
		$cache = get_posts(
			array(
				'post_type'        => 'activiteit',
				'post_status'      => 'publish',
				'numberposts'      => 50,
				'orderby'          => array(
					'menu_order' => 'ASC',
					'title'      => 'ASC',
				),
				'suppress_filters' => false,
			)
		);
	}
	return $cache;
}
