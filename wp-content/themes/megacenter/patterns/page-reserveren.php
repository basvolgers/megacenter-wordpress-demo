<?php
/**
 * Title: Pagina: Reserveren
 * Slug: megacenter/page-reserveren
 * Categories: megacenter-paginas
 * Keywords: reserveren, boeken, reservering
 * Description: Reserveringspagina met de module voor het bestaande reserveringssysteem en hulp voor groepen.
 * Block Types: core/post-content
 * Post Types: page
 * Viewport Width: 1440
 *
 * @package Megacenter
 */

$b = 'Megacenter_Blocks';

$out = $b::page_hero(
	array(
		'kicker' => 'Reserveren',
		'title'  => 'Reserveer je FUN',
		'lead'   => 'Kies je activiteit, datum en tijd. Met een groep vanaf 12 personen maken we graag een voorstel op maat.',
		'accent' => 'mc-accent-1',
	)
);

$side  = $b::group(
	$b::h( 'Liever bellen?', 3 ) . $b::p( 'We helpen je graag met de beste tijd en combinatie.' )
	. $b::buttons( array( array( '0548 00 00 00', 'tel:+31548000000', '', $b::bind( array( 'url' => 'phone_url', 'text' => 'phone' ) ) ) ) )
	. $b::block( 'megacenter/opening-hours', array( 'variant' => 'today' ) ),
	array( 'className' => 'is-style-card' )
);
$side .= $b::group(
	$b::h( 'Met een groep?', 3 ) . $b::p( 'Voor feestjes en groepen maken we een voorstel met vaste prijs.' )
	. $b::lst(
		array(
			'<a href="' . esc_url( megacenter_page_url( 'kinderfeestjes' ) ) . '">Kinderfeestjes</a>',
			'<a href="' . esc_url( megacenter_page_url( 'groepsuitjes' ) ) . '">Groepsuitjes &amp; vrijgezellen</a>',
			'<a href="' . esc_url( megacenter_page_url( 'zakelijk' ) ) . '">Zakelijk &amp; teambuilding</a>',
		),
		'mc-link-list'
	),
	array( 'className' => 'is-style-card' )
);

$out .= $b::section(
	$b::columns(
		array(
			array(
				'inner' => $b::block( 'megacenter/reservation' ),
				'width' => '64%',
			),
			array(
				'inner' => $side,
				'width' => '36%',
				'class' => 'mc-contact-side',
			),
		),
		'mc-reserve-cols',
		'top'
	),
	'mc-reserve-section',
	'Reserveren'
);

$faq  = $b::details( 'Moet ik vooraf betalen?', 'Dat hangt af van het reserveringssysteem. Voor groepen werken we met een aanbetaling of factuur.' );
$faq .= $b::details( 'Kan ik mijn reservering wijzigen of annuleren?', 'Tot 24 uur van tevoren kosteloos. Daarna rekenen we de activiteit, tenzij we de plek opnieuw kunnen vullen.' );
$faq .= $b::details( 'Hoe vroeg moet ik er zijn?', 'Kom 15 minuten voor je starttijd, dan heb je tijd voor jassen, schoenen en een drankje.' );

$out .= $b::section( $b::group( $b::kicker( 'Goed om te weten' ) . $b::h( 'Vragen over reserveren' ) . $faq, array( 'className' => 'mc-narrow' ) ), 'mc-faq-section', 'Veelgestelde vragen' );

echo $out; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
