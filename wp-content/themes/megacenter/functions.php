<?php
/**
 * Megacenter – blokthema. Vormgeving, templates, patronen en stijlvariaties.
 * Inhoud en functionaliteit (activiteiten, formulieren, SEO) zitten in de plugin Megacenter Core.
 *
 * @package Megacenter
 */

defined( 'ABSPATH' ) || exit;

define( 'MEGACENTER_THEME_VERSION', wp_get_theme( 'megacenter' )->get( 'Version' ) );

require_once get_theme_file_path( 'inc/class-megacenter-blocks.php' );

/**
 * Basisinstellingen.
 */
function megacenter_theme_setup() {
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/theme.css' );
	add_theme_support( 'responsive-embeds' );
	remove_theme_support( 'core-block-patterns' );
}
add_action( 'after_setup_theme', 'megacenter_theme_setup' );

/**
 * Versie voor cache-busting (bestandstijd in ontwikkeling).
 *
 * @param string $file Relatief pad.
 * @return string
 */
function megacenter_asset_version( $file ) {
	$path = get_theme_file_path( $file );
	return ( wp_get_development_mode() && file_exists( $path ) ) ? (string) filemtime( $path ) : MEGACENTER_THEME_VERSION;
}

/**
 * Stylesheet en script.
 */
function megacenter_enqueue_assets() {
	// Geminimaliseerde versie (tools/build-css.mjs), behalve als SCRIPT_DEBUG aan staat.
	$css = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) || ! file_exists( get_theme_file_path( 'assets/css/theme.min.css' ) ) ? 'assets/css/theme.css' : 'assets/css/theme.min.css';
	wp_enqueue_style( 'megacenter', get_theme_file_uri( $css ), array(), megacenter_asset_version( $css ) );
	wp_enqueue_script(
		'megacenter',
		get_theme_file_uri( 'assets/js/theme.js' ),
		array(),
		megacenter_asset_version( 'assets/js/theme.js' ),
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);
}
add_action( 'wp_enqueue_scripts', 'megacenter_enqueue_assets' );

/**
 * Vroeg in de <head>: twee fonts preloaden en de .mc-js-class zetten (voor animaties die zonder JS niet nodig zijn).
 */
function megacenter_head_early() {
	foreach ( array( 'bricolage-grotesque-latin-wdth.woff2', 'manrope-latin-wght.woff2' ) as $font ) {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( get_theme_file_uri( 'assets/fonts/' . $font ) )
		);
	}
	wp_print_inline_script_tag( "document.documentElement.classList.add('mc-js');" );
}
add_action( 'wp_head', 'megacenter_head_early', 0 );

/**
 * Blokstijlen: herbruikbare "signatuur"-stijlen die de klant in de editor kan kiezen.
 */
function megacenter_block_styles() {
	$styles = array(
		'core/paragraph' => array(
			'lead'    => __( 'Intro (groot)', 'megacenter' ),
			'kicker'  => __( 'Kicker (boven een kop)', 'megacenter' ),
			'sticker' => __( 'Sticker (schuin label)', 'megacenter' ),
		),
		'core/list'      => array(
			'checks' => __( 'Vinkjes', 'megacenter' ),
			'steps'  => __( 'Genummerde stappen', 'megacenter' ),
			'chips'  => __( 'Chips (horizontaal)', 'megacenter' ),
		),
		'core/image'     => array(
			'frame' => __( 'Kader met gloed', 'megacenter' ),
			'tilt'  => __( 'Schuin kader', 'megacenter' ),
		),
		'core/group'     => array(
			'card'  => __( 'Kaart', 'megacenter' ),
			'glass' => __( 'Glas', 'megacenter' ),
		),
		'core/heading'   => array(
			'outline' => __( 'Omlijnde letters', 'megacenter' ),
		),
		'core/button'    => array(
			'link' => __( 'Tekstlink', 'megacenter' ),
		),
	);
	foreach ( $styles as $block => $list ) {
		foreach ( $list as $name => $label ) {
			register_block_style(
				$block,
				array(
					'name'  => $name,
					'label' => $label,
				)
			);
		}
	}
}
add_action( 'init', 'megacenter_block_styles' );

/**
 * Patrooncategorieën.
 */
function megacenter_pattern_categories() {
	register_block_pattern_category( 'megacenter-secties', array( 'label' => __( 'Megacenter: secties', 'megacenter' ) ) );
	register_block_pattern_category( 'megacenter-paginas', array( 'label' => __( 'Megacenter: complete pagina\'s', 'megacenter' ) ) );
}
add_action( 'init', 'megacenter_pattern_categories' );

/**
 * Woordmerk als fallback voor het Site Logo-blok. Zodra de opdrachtgever een eigen logo uploadt
 * (via het Site Logo-blok of Weergave → Editor), verdwijnt deze fallback automatisch.
 *
 * @param string $block_content HTML.
 * @param array  $block         Blok.
 * @return string
 */
function megacenter_logo_fallback( $block_content, $block ) {
	if ( '' !== trim( $block_content ) ) {
		return $block_content;
	}
	$svg = file_get_contents( get_theme_file_path( 'assets/img/logo-wordmark.svg' ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	if ( ! $svg ) {
		return $block_content;
	}
	$class = 'wp-block-site-logo mc-logo' . ( ! empty( $block['attrs']['className'] ) ? ' ' . $block['attrs']['className'] : '' );
	return sprintf(
		'<div class="%1$s"><a href="%2$s" rel="home" class="mc-logo__link" aria-label="%3$s">%4$s</a></div>',
		esc_attr( $class ),
		esc_url( home_url( '/' ) ),
		/* translators: %s: sitenaam */
		esc_attr( sprintf( __( '%s, naar de homepage', 'megacenter' ), get_bloginfo( 'name' ) ) ),
		$svg // Eigen, vast SVG-bestand uit het thema.
	);
}
add_filter( 'render_block_core/site-logo', 'megacenter_logo_fallback', 10, 2 );

/**
 * Beeld voor patronen: zoekt het demobeeld (geïmporteerd door Megacenter Core) op sleutel,
 * met een grafische placeholder uit het thema als fallback.
 *
 * @param string $key Beeld-sleutel, bijv. "kinderfeest".
 * @return array{id:int,url:string,alt:string}
 */
function megacenter_theme_image( $key ) {
	static $map = null;
	if ( null === $map ) {
		$map = array();
		$ids = get_posts(
			array(
				'post_type'   => 'attachment',
				'post_status' => 'inherit',
				'numberposts' => 100,
				'fields'      => 'ids',
				'meta_key'    => '_megacenter_demo', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			)
		);
		foreach ( $ids as $id ) {
			$map[ preg_replace( '/^media-/', '', (string) get_post_meta( $id, '_megacenter_demo_key', true ) ) ] = (int) $id;
		}
	}
	if ( ! empty( $map[ $key ] ) ) {
		return array(
			'id'  => $map[ $key ],
			'url' => (string) wp_get_attachment_url( $map[ $key ] ),
			'alt' => (string) get_post_meta( $map[ $key ], '_wp_attachment_image_alt', true ),
		);
	}
	return array(
		'id'  => 0,
		'url' => get_theme_file_uri( 'assets/img/placeholder.svg' ),
		'alt' => '',
	);
}

/**
 * Volledige core/image-blokmarkup voor een patroon.
 *
 * @param string $key   Beeld-sleutel.
 * @param string $class Extra class(es).
 * @param string $alt   Alt-tekst (leeg = die van het beeld; "-" = decoratief).
 * @return string
 */
function megacenter_pattern_image( $key, $class = '', $alt = '' ) {
	$img   = megacenter_theme_image( $key );
	$alt   = '-' === $alt ? '' : ( '' !== $alt ? $alt : $img['alt'] );
	$attrs = array(
		'sizeSlug'        => 'full',
		'linkDestination' => 'none',
	);
	if ( $img['id'] ) {
		$attrs = array( 'id' => $img['id'] ) + $attrs;
	}
	if ( $class ) {
		$attrs['className'] = $class;
	}
	return sprintf(
		"<!-- wp:image %1\$s -->\n<figure class=\"wp-block-image size-full%2\$s\"><img src=\"%3\$s\" alt=\"%4\$s\"%5\$s/></figure>\n<!-- /wp:image -->",
		wp_json_encode( $attrs, JSON_UNESCAPED_SLASHES ),
		$class ? ' ' . esc_attr( $class ) : '',
		esc_url( $img['url'] ),
		esc_attr( $alt ),
		$img['id'] ? ' class="wp-image-' . (int) $img['id'] . '"' : ''
	);
}

/**
 * Link naar een pagina op slug (voor patronen), met fallback.
 *
 * @param string $slug Slug.
 * @return string
 */
function megacenter_page_url( $slug ) {
	$page = get_page_by_path( $slug );
	return $page ? (string) get_permalink( $page ) : home_url( '/' . $slug . '/' );
}

/**
 * Archieflink van de activiteiten (ook als de plugin nog niet actief is).
 *
 * @return string
 */
function megacenter_activities_url() {
	$link = post_type_exists( 'activiteit' ) ? get_post_type_archive_link( 'activiteit' ) : '';
	return $link ? $link : home_url( '/activiteiten/' );
}
