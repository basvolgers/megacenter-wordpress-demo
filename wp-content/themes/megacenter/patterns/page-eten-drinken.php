<?php
/**
 * Title: Pagina: Eten & drinken
 * Slug: megacenter/page-eten-drinken
 * Categories: megacenter-paginas
 * Keywords: eten, drinken, restaurant, bar, menu
 * Description: Horecapagina: hero, restaurant/bar/arrangementen, voorbeeldkaart en CTA.
 * Block Types: core/post-content
 * Post Types: page
 * Viewport Width: 1440
 *
 * @package Megacenter
 */

$b = 'Megacenter_Blocks';

$out = $b::page_hero(
	array(
		'kicker'  => 'Eten & drinken',
		'title'   => 'Eerst spelen, dan smullen',
		'lead'    => 'Burgers, pizza uit de steenoven, sharing bites en cocktails. Aan de bar, aan de baan of aan een lange tafel met je hele groep.',
		'buttons' => array(
			array( 'Reserveer een tafel', megacenter_page_url( 'reserveren' ) ),
			array( 'Bekijk arrangementen', megacenter_page_url( 'groepsuitjes' ), 'outline' ),
		),
		'chips'   => array( 'Ook zonder activiteit welkom', 'Kidsmenu', 'Alcoholvrije cocktails' ),
		'images'  => array( 'pizza', 'bar' ),
		'badge'   => '<strong>Vers</strong> uit de steenoven',
		'accent'  => 'mc-accent-4',
	)
);

$spots = array(
	array( 'eten', 'Restaurant', 'Burgers, pizza\'s, salades en een kidsmenu. Lunch, diner of tussendoor.' ),
	array( 'bar', 'Bar', 'Cocktails, speciaalbier en mocktails. Met zicht op de banen en de arena.' ),
	array( 'hittesteen', 'Arrangementen', 'Hittesteen, buffet of BBQ aan een lange tafel: ideaal voor groepen na de activiteit.' ),
);
$cards = '';
foreach ( $spots as list( $img, $title, $text ) ) {
	$cards .= $b::group( megacenter_pattern_image( $img, 'mc-spot__img' ) . $b::h( esc_html( $title ), 3 ) . $b::p( esc_html( $text ) ), array( 'className' => 'is-style-card mc-spot' ) );
}
$out .= $b::section(
	$b::head( 'Drie plekken', 'Waar heb je zin in?' ) . $b::group(
		$cards,
		array(
			'className' => 'mc-spots',
			'layout'    => array(
				'type'               => 'grid',
				'columnCount'        => 3,
				'minimumColumnWidth' => '16rem',
			),
		)
	),
	'mc-spots-section',
	'Plekken'
);

$menu  = $b::lst(
	array(
		'<strong>Smash burger</strong> dubbel, cheddar, friet en slaw <em>€ 15,50</em>',
		'<strong>Pizza Margherita</strong> uit de steenoven <em>€ 12,50</em>',
		'<strong>Sharing board</strong> voor 4, warm en koud <em>€ 24,50</em>',
		'<strong>Kidsmenu</strong> met verrassing <em>€ 8,50</em>',
	),
	'mc-menu'
);
$menu2 = $b::lst(
	array(
		'<strong>Signature cocktail</strong> wisselend, vraag de bar <em>€ 9,50</em>',
		'<strong>Mocktail</strong> alcoholvrij en net zo feestelijk <em>€ 6,50</em>',
		'<strong>Speciaalbier van de tap</strong> lokaal en wisselend <em>€ 5,50</em>',
		'<strong>Hittesteen-arrangement</strong> vanaf 8 personen <em>€ 29,50 p.p.</em>',
	),
	'mc-menu'
);
$out  .= $b::section(
	$b::head( 'Voorbeeldkaart', 'Uit de keuken & van de bar', 'Een greep uit de kaart. Allergieën of dieetwensen? Vraag het ons, we denken graag mee.' )
	. $b::columns(
		array(
			array( 'inner' => $menu ),
			array( 'inner' => $menu2 ),
		),
		'mc-menu-cols',
		'top'
	),
	'mc-panel is-style-section-paper',
	'Kaart'
);

$out .= '<!-- wp:pattern {"slug":"megacenter/cta-band"} /-->' . "\n";

echo $out; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
