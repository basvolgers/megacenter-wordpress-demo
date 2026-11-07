<?php
/**
 * Title: Pagina: Contact & route
 * Slug: megacenter/page-contact
 * Categories: megacenter-paginas
 * Keywords: contact, route, adres, openingstijden
 * Description: Contactpagina: openingstijden, adres en route, contactformulier en veelgestelde vragen.
 * Block Types: core/post-content
 * Post Types: page
 * Viewport Width: 1440
 *
 * @package Megacenter
 */

$b = 'Megacenter_Blocks';

$out = $b::page_hero(
	array(
		'kicker' => 'Contact & route',
		'title'  => 'Kom langs of stel je vraag',
		'lead'   => 'Bel, mail of stuur een bericht. We reageren binnen één werkdag, meestal veel sneller.',
		'extra'  => $b::block( 'megacenter/opening-hours', array( 'variant' => 'today' ) ),
		'accent' => 'mc-accent-3',
	)
);

$hours  = $b::h( 'Openingstijden', 3 ) . $b::block( 'megacenter/opening-hours' );
$where  = $b::h( 'Adres & route', 3 );
$where .= $b::p( 'Adres volgt · Rijssen', 'mc-visit__address', array( 'metadata' => $b::bind( array( 'content' => 'address' ) ) ) );
$where .= $b::p( 'Vlak bij de A1, met gratis parkeren voor de deur.', '', array( 'metadata' => $b::bind( array( 'content' => 'route_text' ) ) ) );
$where .= $b::buttons(
	array(
		array( 'Plan je route', 'https://www.google.com/maps', '', $b::bind( array( 'url' => 'route_url' ) ) ),
	)
);
$where .= $b::p( 'De routeknop opent Google Maps in je eigen app. We laden geen kaart op deze pagina, zo blijft je bezoek privé.', 'mc-note' );
$talk   = $b::h( 'Direct contact', 3 );
$talk  .= $b::buttons(
	array(
		array( '0548 00 00 00', 'tel:+31548000000', 'outline', $b::bind( array( 'url' => 'phone_url', 'text' => 'phone' ) ) ),
		array( 'info@voorbeeld.nl', 'mailto:info@voorbeeld.nl', 'outline', $b::bind( array( 'url' => 'email_url', 'text' => 'email' ) ) ),
	),
	'mc-contact-buttons'
);
$talk  .= $b::block( 'megacenter/socials' );

$side = $b::group( $hours, array( 'className' => 'is-style-card' ) ) . $b::group( $where, array( 'className' => 'is-style-card' ) ) . $b::group( $talk, array( 'className' => 'is-style-card' ) );
$main = $b::kicker( 'Bericht' ) . $b::h( 'Stuur ons een bericht' ) . $b::block( 'megacenter/request-form', array( 'type' => 'contact' ) );

$out .= $b::section(
	$b::columns(
		array(
			array(
				'inner' => $main,
				'width' => '58%',
				'class' => 'mc-form-section__main',
			),
			array(
				'inner' => $side,
				'width' => '42%',
				'class' => 'mc-contact-side',
			),
		),
		'mc-form-section',
		'top'
	),
	'mc-contact',
	'Contact'
);

$faq  = $b::details( 'Waar kan ik parkeren?', 'Je parkeert gratis voor de deur. Kom je met een bus? Laat het ons even weten, dan houden we plek vrij.' );
$faq .= $b::details( 'Kan ik meedoen met een beperking?', 'We willen dat iedereen mee kan doen. Bel ons gerust vooraf, dan bespreken we samen wat er mogelijk is en zorgen we dat alles klaarstaat.' );
$faq .= $b::details( 'Kan ik mijn reservering wijzigen?', 'Tot 24 uur van tevoren kun je kosteloos wijzigen. Bel of mail ons met je reserveringsnummer.' );
$faq .= $b::details( 'Hebben jullie gevonden voorwerpen?', 'Iets laten liggen? Bel of mail ons, we bewaren gevonden voorwerpen een maand.' );

$out .= $b::section( $b::group( $b::kicker( 'FAQ' ) . $b::h( 'Veelgestelde vragen' ) . $faq, array( 'className' => 'mc-narrow' ) ), 'mc-faq-section', 'Veelgestelde vragen' );

echo $out; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
