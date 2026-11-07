<?php
/**
 * SEO-basis: meta description, Open Graph, JSON-LD en canonical voor archieven.
 * Schakelt zichzelf uit zodra Yoast SEO, Rank Math, SEOPress of AIOSEO actief is.
 *
 * @package MegacenterCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Is er een SEO-plugin actief die dit overneemt?
 *
 * @return bool
 */
function megacenter_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'SEOPRESS_VERSION' ) || defined( 'AIOSEO_VERSION' );
}

/**
 * Titelscheiding: "Lasergame · Megacenter Rijssen".
 *
 * @return string
 */
function megacenter_title_separator() {
	return '·';
}
add_filter( 'document_title_separator', 'megacenter_title_separator' );

/**
 * Omschrijving voor de huidige pagina.
 *
 * @return string
 */
function megacenter_seo_description() {
	$desc = '';
	if ( is_singular() ) {
		$post = get_queried_object();
		if ( $post instanceof WP_Post && ! is_front_page() ) {
			$desc = has_excerpt( $post ) ? $post->post_excerpt : '';
			if ( ! $desc && 'activiteit' === $post->post_type ) {
				$desc = (string) get_post_meta( $post->ID, 'megacenter_tagline', true );
			}
			if ( ! $desc ) {
				$desc = wp_trim_words( wp_strip_all_tags( strip_shortcodes( excerpt_remove_blocks( $post->post_content ) ) ), 28, '…' );
			}
		}
	} elseif ( is_post_type_archive( 'activiteit' ) ) {
		$desc = __( 'Lasergame, bowling, escape rooms, sim racing, VR en meer. Bekijk alle activiteiten, filter op wat bij jullie past en reserveer direct.', 'megacenter-core' );
	} elseif ( is_tax( 'activiteit_type' ) ) {
		$term = get_queried_object();
		$desc = $term && $term->description ? $term->description : '';
	}
	if ( ! $desc ) {
		$desc = (string) megacenter_setting( 'seo_description' );
	}
	$desc = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $desc ) ) );
	return mb_strlen( $desc ) > 160 ? mb_substr( $desc, 0, 157 ) . '…' : $desc;
}

/**
 * Deelbeeld (uitgelichte afbeelding of standaardbeeld).
 *
 * @return array{url:string,width:int,height:int}|null
 */
function megacenter_seo_image() {
	$id = is_singular() ? get_post_thumbnail_id( get_queried_object_id() ) : 0;
	if ( ! $id ) {
		$demo = get_posts(
			array(
				'post_type'   => 'attachment',
				'post_status' => 'inherit',
				'numberposts' => 1,
				'fields'      => 'ids',
				'meta_key'    => '_megacenter_demo_key', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'  => 'og-default', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);
		$id   = $demo ? (int) $demo[0] : 0;
	}
	if ( ! $id ) {
		return null;
	}
	$src = wp_get_attachment_image_src( $id, 'full' );
	return $src ? array(
		'url'    => $src[0],
		'width'  => (int) $src[1],
		'height' => (int) $src[2],
	) : null;
}

/**
 * Meta-tags in de <head>.
 */
function megacenter_seo_head() {
	if ( megacenter_seo_plugin_active() ) {
		return;
	}
	$desc  = megacenter_seo_description();
	$title = wp_get_document_title();
	$url   = is_singular() ? get_permalink() : ( is_post_type_archive( 'activiteit' ) ? get_post_type_archive_link( 'activiteit' ) : ( is_tax() ? get_term_link( get_queried_object() ) : home_url( '/' ) ) );
	$url   = is_wp_error( $url ) ? home_url( '/' ) : $url;
	$image = megacenter_seo_image();

	echo "\n<!-- Megacenter SEO -->\n";
	if ( $desc ) {
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( $desc ) );
	}
	if ( is_post_type_archive( 'activiteit' ) || is_tax( 'activiteit_type' ) ) {
		printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $url ) );
	}
	printf( '<meta property="og:locale" content="nl_NL">' . "\n" );
	printf( '<meta property="og:type" content="%s">' . "\n", esc_attr( is_singular( 'activiteit' ) ? 'article' : 'website' ) );
	printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( megacenter_site_name() ) );
	printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );
	if ( $desc ) {
		printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $desc ) );
	}
	printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $url ) );
	if ( $image ) {
		printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $image['url'] ) );
		printf( '<meta property="og:image:width" content="%d">' . "\n", (int) $image['width'] );
		printf( '<meta property="og:image:height" content="%d">' . "\n", (int) $image['height'] );
	}
	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";

	$schema = megacenter_seo_schema();
	if ( $schema ) {
		echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "</script>\n";
	}
}
add_action( 'wp_head', 'megacenter_seo_head', 2 );

/**
 * Gestructureerde data (JSON-LD).
 *
 * @return array|null
 */
function megacenter_seo_schema() {
	$s = megacenter_get_settings();

	if ( is_front_page() ) {
		$map   = array(
			'mon' => 'Monday',
			'tue' => 'Tuesday',
			'wed' => 'Wednesday',
			'thu' => 'Thursday',
			'fri' => 'Friday',
			'sat' => 'Saturday',
			'sun' => 'Sunday',
		);
		$hours = array();
		foreach ( $map as $day => $schema_day ) {
			if ( ! empty( $s['hours'][ $day ]['open'] ) && ! empty( $s['hours'][ $day ]['close'] ) ) {
				$hours[] = array(
					'@type'     => 'OpeningHoursSpecification',
					'dayOfWeek' => 'https://schema.org/' . $schema_day,
					'opens'     => $s['hours'][ $day ]['open'],
					'closes'    => $s['hours'][ $day ]['close'],
				);
			}
		}
		$image = megacenter_seo_image();
		$data  = array(
			'@context'                  => 'https://schema.org',
			'@type'                     => 'EntertainmentBusiness',
			'@id'                       => home_url( '/#organisatie' ),
			'name'                      => megacenter_site_name(),
			'description'               => megacenter_seo_description(),
			'url'                       => home_url( '/' ),
			'telephone'                 => $s['phone'],
			'email'                     => $s['email'],
			'priceRange'                => '€€',
			'address'                   => array(
				'@type'           => 'PostalAddress',
				'streetAddress'   => $s['street'],
				'postalCode'      => $s['postcode'],
				'addressLocality' => $s['city'],
				'addressCountry'  => 'NL',
			),
			'openingHoursSpecification' => $hours,
			'sameAs'                    => array_values( array_filter( array( $s['instagram'], $s['facebook'], $s['tiktok'], $s['linkedin'], $s['youtube'] ), static fn( $u ) => $u && ! preg_match( '#^https?://(www\.)?[a-z]+\.com/?$#', $u ) ) ),
		);
		if ( $image ) {
			$data['image'] = $image['url'];
		}
		return $data;
	}

	if ( is_singular( 'activiteit' ) ) {
		return array(
			'@context'        => 'https://schema.org',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => array(
				array(
					'@type'    => 'ListItem',
					'position' => 1,
					'name'     => megacenter_site_name(),
					'item'     => home_url( '/' ),
				),
				array(
					'@type'    => 'ListItem',
					'position' => 2,
					'name'     => __( 'Activiteiten', 'megacenter-core' ),
					'item'     => get_post_type_archive_link( 'activiteit' ),
				),
				array(
					'@type'    => 'ListItem',
					'position' => 3,
					'name'     => get_the_title( get_queried_object_id() ),
					'item'     => get_permalink( get_queried_object_id() ),
				),
			),
		);
	}
	return null;
}
