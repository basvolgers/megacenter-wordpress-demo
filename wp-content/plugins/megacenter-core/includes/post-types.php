<?php
/**
 * Post types en taxonomie: activiteit, activiteit_type en aanvraag.
 *
 * @package MegacenterCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registreert de post types en taxonomie.
 */
function megacenter_register_post_types() {
	// Eerst de taxonomie: dan komen /activiteiten/soort/…-regels vóór die van de losse activiteiten.
	register_taxonomy(
		'activiteit_type',
		array( 'activiteit' ),
		array(
			'labels'            => array(
				'name'          => __( 'Soorten', 'megacenter-core' ),
				'singular_name' => __( 'Soort', 'megacenter-core' ),
				'menu_name'     => __( 'Soorten (filters)', 'megacenter-core' ),
				'all_items'     => __( 'Alle soorten', 'megacenter-core' ),
				'edit_item'     => __( 'Soort bewerken', 'megacenter-core' ),
				'add_new_item'  => __( 'Nieuwe soort', 'megacenter-core' ),
				'search_items'  => __( 'Soorten zoeken', 'megacenter-core' ),
				'not_found'     => __( 'Geen soorten gevonden', 'megacenter-core' ),
			),
			'description'       => __( 'Gebruikt voor de filterknoppen op het activiteitenoverzicht.', 'megacenter-core' ),
			'public'            => true,
			'hierarchical'      => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'rewrite'           => array(
				'slug'       => 'activiteiten/soort',
				'with_front' => false,
			),
		)
	);

	register_post_type(
		'activiteit',
		array(
			'labels'          => array(
				'name'                  => __( 'Activiteiten', 'megacenter-core' ),
				'singular_name'         => __( 'Activiteit', 'megacenter-core' ),
				'menu_name'             => __( 'Activiteiten', 'megacenter-core' ),
				'add_new'               => __( 'Nieuwe activiteit', 'megacenter-core' ),
				'add_new_item'          => __( 'Nieuwe activiteit toevoegen', 'megacenter-core' ),
				'edit_item'             => __( 'Activiteit bewerken', 'megacenter-core' ),
				'new_item'              => __( 'Nieuwe activiteit', 'megacenter-core' ),
				'view_item'             => __( 'Activiteit bekijken', 'megacenter-core' ),
				'view_items'            => __( 'Activiteiten bekijken', 'megacenter-core' ),
				'search_items'          => __( 'Activiteiten zoeken', 'megacenter-core' ),
				'not_found'             => __( 'Geen activiteiten gevonden', 'megacenter-core' ),
				'all_items'             => __( 'Alle activiteiten', 'megacenter-core' ),
				'featured_image'        => __( 'Hoofdbeeld', 'megacenter-core' ),
				'set_featured_image'    => __( 'Hoofdbeeld instellen', 'megacenter-core' ),
				'remove_featured_image' => __( 'Hoofdbeeld verwijderen', 'megacenter-core' ),
				'use_featured_image'    => __( 'Gebruiken als hoofdbeeld', 'megacenter-core' ),
				'item_published'        => __( 'Activiteit gepubliceerd.', 'megacenter-core' ),
				'item_updated'          => __( 'Activiteit bijgewerkt.', 'megacenter-core' ),
			),
			'description'     => __( 'De activiteiten van het megacenter. Verschijnen automatisch op de homepage en in het overzicht.', 'megacenter-core' ),
			'public'          => true,
			'show_in_rest'    => true,
			'menu_position'   => 5,
			'menu_icon'       => 'dashicons-tickets-alt',
			'has_archive'     => 'activiteiten',
			'rewrite'         => array(
				'slug'       => 'activiteiten',
				'with_front' => false,
			),
			'supports'        => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', 'page-attributes', 'revisions' ),
			// Startinhoud voor een nieuwe activiteit: de klant hoeft alleen nog in te vullen.
			'template'        => array(
				array(
					'core/paragraph',
					array(
						'className'   => 'is-style-lead',
						'placeholder' => __( 'Vertel in twee zinnen wat bezoekers gaan beleven…', 'megacenter-core' ),
					),
				),
				array(
					'core/heading',
					array(
						'content' => __( 'Zo werkt het', 'megacenter-core' ),
					),
				),
				array(
					'core/list',
					array( 'className' => 'is-style-steps' ),
					array(
						array( 'core/list-item', array( 'placeholder' => __( 'Stap 1…', 'megacenter-core' ) ) ),
						array( 'core/list-item', array( 'placeholder' => __( 'Stap 2…', 'megacenter-core' ) ) ),
						array( 'core/list-item', array( 'placeholder' => __( 'Stap 3…', 'megacenter-core' ) ) ),
					),
				),
			),
		)
	);

	register_post_type(
		'aanvraag',
		array(
			'labels'              => array(
				'name'               => __( 'Aanvragen', 'megacenter-core' ),
				'singular_name'      => __( 'Aanvraag', 'megacenter-core' ),
				'menu_name'          => __( 'Aanvragen', 'megacenter-core' ),
				'edit_item'          => __( 'Aanvraag bekijken', 'megacenter-core' ),
				'all_items'          => __( 'Alle aanvragen', 'megacenter-core' ),
				'search_items'       => __( 'Aanvragen zoeken', 'megacenter-core' ),
				'not_found'          => __( 'Nog geen aanvragen binnen.', 'megacenter-core' ),
				'not_found_in_trash' => __( 'Geen aanvragen in de prullenbak.', 'megacenter-core' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => false,
			'exclude_from_search' => true,
			'menu_position'       => 6,
			'menu_icon'           => 'dashicons-email-alt',
			'supports'            => array( 'title' ),
			'capability_type'     => 'post',
			'capabilities'        => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap'        => true,
		)
	);

	add_post_type_support( 'page', 'excerpt' );
}
add_action( 'init', 'megacenter_register_post_types' );

/**
 * Activiteiten-archief: alles op één pagina, in de ingestelde volgorde.
 *
 * @param WP_Query $query Query.
 */
function megacenter_activity_archive_query( $query ) {
	if ( ! $query->is_main_query() ) {
		return;
	}
	if ( is_admin() ) {
		// Beheerlijst: zelfde volgorde als op de site, tenzij de gebruiker zelf op een kolom sorteert.
		if ( 'activiteit' === $query->get( 'post_type' ) && ! $query->get( 'orderby' ) ) {
			$query->set(
				'orderby',
				array(
					'menu_order' => 'ASC',
					'title'      => 'ASC',
				)
			);
		}
		return;
	}
	if ( $query->is_post_type_archive( 'activiteit' ) || $query->is_tax( 'activiteit_type' ) ) {
		$query->set( 'posts_per_page', 50 );
		$query->set(
			'orderby',
			array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			)
		);
	}
}
add_action( 'pre_get_posts', 'megacenter_activity_archive_query' );

/**
 * Accentkleur als class op elke activiteit (kaarten in Query Loops en detailpagina).
 *
 * @param string[] $classes Classes.
 * @param string[] $css_class Extra classes.
 * @param int      $post_id Post-ID.
 * @return string[]
 */
function megacenter_activity_post_class( $classes, $css_class, $post_id ) {
	if ( 'activiteit' === get_post_type( $post_id ) ) {
		$classes[] = 'mc-' . megacenter_activity_accent( $post_id );
	}
	return $classes;
}
add_filter( 'post_class', 'megacenter_activity_post_class', 10, 3 );

/**
 * Accentkleur ook op de body van een activiteitpagina.
 *
 * @param string[] $classes Classes.
 * @return string[]
 */
function megacenter_activity_body_class( $classes ) {
	if ( is_singular( 'activiteit' ) ) {
		$classes[] = 'mc-' . megacenter_activity_accent( get_queried_object_id() );
	}
	return $classes;
}
add_filter( 'body_class', 'megacenter_activity_body_class' );

/**
 * Query Loop "Combineer met": de huidige activiteit overslaan en de volgende activiteiten tonen
 * (in volgorde, doorlopend), zodat elke detailpagina andere suggesties geeft.
 * Werkt voor elke Query Loop met query-attribuut "megacenterExcludeCurrent": true.
 *
 * @param array    $query Query vars.
 * @param WP_Block $block Blok (post-template).
 * @return array
 */
function megacenter_query_exclude_current( $query, $block ) {
	if ( empty( $block->context['query']['megacenterExcludeCurrent'] ) || ! is_singular() ) {
		return $query;
	}
	$current = get_queried_object_id();
	$ids     = wp_list_pluck( megacenter_get_activities(), 'ID' );
	$pos     = array_search( $current, $ids, true );
	if ( false === $pos ) {
		$query['post__not_in'] = array( $current );
		return $query;
	}
	$query['post__in'] = array_merge( array_slice( $ids, $pos + 1 ), array_slice( $ids, 0, $pos ) );
	$query['orderby']  = 'post__in';
	return $query;
}
add_filter( 'query_loop_block_query_vars', 'megacenter_query_exclude_current', 10, 2 );
