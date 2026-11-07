<?php
/**
 * Title: Kom langs: openingstijden, locatie en FAQ
 * Slug: megacenter/visit
 * Categories: megacenter-secties
 * Keywords: openingstijden, adres, route, faq, contact
 * Description: Drie kaarten: openingstijden (uit de instellingen), locatie met route-knop en veelgestelde vragen.
 * Viewport Width: 1440
 *
 * @package Megacenter
 */

$b = 'Megacenter_Blocks';

$hours  = $b::h( 'Openingstijden', 3 );
$hours .= $b::block( 'megacenter/opening-hours', array( 'variant' => 'today' ) );
$hours .= $b::block( 'megacenter/opening-hours', array( 'showNote' => false ) );

$where  = $b::h( 'Locatie', 3 );
$where .= $b::p( 'Adres volgt · Rijssen', 'mc-visit__address', array( 'metadata' => $b::bind( array( 'content' => 'address' ) ) ) );
$where .= $b::p( 'Vlak bij de A1, met gratis parkeren voor de deur.', 'mc-visit__route', array( 'metadata' => $b::bind( array( 'content' => 'route_text' ) ) ) );
$where .= $b::buttons(
	array(
		array( 'Plan je route', 'https://www.google.com/maps', '', $b::bind( array( 'url' => 'route_url' ) ) ),
		array( 'Bel ons', 'tel:+31548000000', 'outline', $b::bind( array( 'url' => 'phone_url' ) ) ),
	)
);

$faq  = $b::h( 'Snel antwoord', 3 );
$faq .= $b::details( 'Moet ik reserveren?', 'Voor de meeste activiteiten wel, zeker in het weekend. De arcade en de Kids Jungle zijn vrij toegankelijk.' );
$faq .= $b::details( 'Kan ik er ook alleen eten?', 'Zeker. In de bar en het restaurant ben je ook zonder activiteit welkom voor burgers, pizza en sharing.' );
$faq .= $b::details( 'Is er parkeergelegenheid?', 'Ja, je parkeert gratis voor de deur. Ook voor bussen is er plek; laat het ons even weten.' );
$faq .= $b::details( 'Verkopen jullie cadeaubonnen?', 'Ja, cadeaubonnen zijn verkrijgbaar aan de balie, in elk bedrag dat je wilt.' );

$inner  = $b::head( 'Kom langs', 'Tot zo!' );
$inner .= $b::group(
	$b::group( $hours, array( 'className' => 'is-style-card mc-visit__card' ) )
	. $b::group( $where, array( 'className' => 'is-style-card mc-visit__card' ) )
	. $b::group( $faq, array( 'className' => 'is-style-card mc-visit__card mc-visit__faq' ) ),
	array(
		'className' => 'mc-visit',
		'layout'    => array(
			'type'               => 'grid',
			'columnCount'        => 3,
			'minimumColumnWidth' => '18rem',
		),
	)
);

echo $b::section( $inner, 'mc-visit-section', 'Kom langs' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
