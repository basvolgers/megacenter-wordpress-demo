<?php
/**
 * Title: Pagina: Kinderfeestjes
 * Slug: megacenter/page-kinderfeestjes
 * Categories: megacenter-paginas
 * Keywords: kinderfeest, verjaardag, kinderen, arrangement
 * Description: Arrangementpagina: hero, 3 pakketten, verloop van het feest, leeftijden, FAQ en aanvraagformulier.
 * Block Types: core/post-content
 * Post Types: page
 * Viewport Width: 1440
 *
 * @package Megacenter
 */

$b = 'Megacenter_Blocks';

// 1. Hero.
$out = $b::page_hero(
	array(
		'kicker'  => 'Kinderfeestjes · 4 t/m 14 jaar',
		'title'   => 'Het feestje waar de hele klas over praat',
		'lead'    => 'Jij kiest de FUN, wij regelen de rest: activiteiten, eten, drinken en een feesthost die er een onvergetelijke middag van maakt.',
		'buttons' => array(
			array( 'Vraag je feestje aan', '#aanvraag-formulier' ),
			array( 'Bekijk de pakketten', '#pakketten', 'outline' ),
		),
		'chips'   => array( 'Vanaf € 17,50 per kind', 'Feesthost inbegrepen', 'Eten & drinken geregeld' ),
		'images'  => array( 'kinderfeest', 'kinderfeest-2' ),
		'badge'   => '<strong>Feest!</strong> vanaf 6 kids',
		'accent'  => 'mc-accent-2',
	)
);

// 2. Pakketten.
$cards  = $b::package( 'Instap', 'Glow', '€ 17,50', 'per kind', array( '1 activiteit: Glowgolf, Bowling of Kids Jungle', 'Patat met een snack of pizzapunt', '2 drankjes naar keuze', 'Feestkroon voor de jarige' ), array( 'Kies Glow', '#aanvraag-formulier' ), '', 'mc-accent-3' );
$cards .= $b::package( 'Meest gekozen', 'Neon', '€ 24,50', 'per kind', array( '2 activiteiten, o.a. lasergame (8+)', 'Kindermenu, 2 drankjes en een ijsje', 'Feesthost die alles begeleidt', 'Digitale uitnodiging om te versturen' ), array( 'Kies Neon', '#aanvraag-formulier' ), 'Populair', 'mc-accent-1' );
$cards .= $b::package( 'Alles erop en eraan', 'Ultra', '€ 32,50', 'per kind', array( 'Lasergame + VR Arena of escape room (10+)', 'Pizza party met onbeperkt limonade', 'Taart met sterretjes voor de jarige', 'Cadeautje en een eigen feesttafel' ), array( 'Kies Ultra', '#aanvraag-formulier' ), '', 'mc-accent-2' );

$out .= $b::section(
	$b::head( 'De pakketten', 'Kies je feest', 'Alle pakketten zijn inclusief begeleiding, feesttafel en koffie of thee voor de ouders die blijven.' )
	. $b::group(
		$cards,
		array(
			'className' => 'mc-packages',
			'layout'    => array(
				'type'               => 'grid',
				'columnCount'        => 3,
				'minimumColumnWidth' => '17rem',
			),
		)
	),
	'mc-packages-section',
	'Pakketten',
	array( 'anchor' => 'pakketten' )
);

// 3. Verloop.
$timeline = $b::lst(
	array(
		'<strong>0:00 · Ontvangst</strong> Feestkroon op, jassen in de kluis en de host neemt het over.',
		'<strong>0:15 · Activiteit 1</strong> Glowgolf, bowling, lasergame: jullie kiezen.',
		'<strong>1:00 · Feesttafel</strong> Eten en drinken aan een eigen, versierde tafel.',
		'<strong>1:45 · Activiteit 2</strong> Nog één keer knallen (bij Neon en Ultra).',
		'<strong>2:30 · Taart &amp; ophalen</strong> IJsje of taart, cadeautje en een uitgelaten groep.',
	),
	'is-style-steps mc-timeline'
);
$out     .= $b::section( $b::head( 'Zo verloopt het feest', 'Tweeënhalf uur FUN', 'Een voorbeeldschema van een Neon-feestje. Begin- en eindtijd stemmen we samen af.' ) . $timeline, 'mc-panel is-style-section-paper', 'Verloop' );

// 4. Leeftijden.
$ages  = array(
	array( '4–6', 'jaar', 'Kids Jungle en Glowgolf. Ravotten en de eerste hole-in-one.', 'mc-accent-2' ),
	array( '7–9', 'jaar', 'Bowling met bumpers, Glowgolf en de arcade.', 'mc-accent-3' ),
	array( '8–12', 'jaar', 'Lasergame en arcade: het favoriete feestje van deze leeftijd.', 'mc-accent-1' ),
	array( '12+', 'jaar', 'Escape room, VR Arena en Sim Racing. Stoer genoeg voor tieners.', 'mc-accent-4' ),
);
$cards = '';
foreach ( $ages as list( $range, $unit, $text, $accent ) ) {
	$cards .= $b::group( $b::p( esc_html( $range ) . ' <em>' . esc_html( $unit ) . '</em>', 'mc-age__range' ) . $b::p( esc_html( $text ), 'mc-age__text' ), array( 'className' => 'mc-age is-style-card ' . $accent ) );
}
$out .= $b::section(
	$b::head( 'Leeftijden', 'Welke FUN past bij welke leeftijd?' )
	. $b::group(
		$cards,
		array(
			'className' => 'mc-ages',
			'layout'    => array(
				'type'               => 'grid',
				'columnCount'        => 4,
				'minimumColumnWidth' => '13rem',
			),
		)
	),
	'mc-ages-section',
	'Leeftijden'
);

// 5. FAQ + praktisch.
$faq  = $b::kicker( 'Goed om te weten' ) . $b::h( 'Veelgestelde vragen' );
$faq .= $b::details( 'Met hoeveel kinderen kan ik een feestje geven?', 'Vanaf 6 kinderen. Grote groep? Tot zo\'n 30 kinderen splitsen we slim op, zodat niemand hoeft te wachten.' );
$faq .= $b::details( 'Moeten ouders erbij blijven?', 'Bij kinderen tot 6 jaar vragen we een begeleider per 6 kinderen. Daarboven zorgt de feesthost voor de begeleiding.' );
$faq .= $b::details( 'Mijn kind heeft een allergie. Kan dat?', 'Zeker. Geef het door bij je aanvraag, dan zorgt de keuken voor een passend alternatief.' );
$faq .= $b::details( 'Mag ik zelf taart meenemen?', 'Bij Glow en Neon mag dat, wij zorgen voor bordjes en een mooi moment. Bij Ultra zit de taart er al in.' );
$faq .= $b::details( 'Wat als er kinderen ziek worden?', 'Tot 24 uur van tevoren kun je het aantal kinderen kosteloos aanpassen.' );

$side  = $b::h( 'Praktisch', 3 );
$side .= $b::lst( array( 'Minimaal 6 kinderen', 'Koffie of thee voor begeleiders', 'Allergie of dieet? Geef het door', 'Uitnodiging om te downloaden' ), 'is-style-checks' );
$side .= $b::buttons( array( array( 'Vraag je feestje aan', '#aanvraag-formulier' ) ) );

$out .= $b::section(
	$b::columns(
		array(
			array(
				'inner' => $faq,
				'width' => '62%',
			),
			array(
				'inner' => $b::group( $side, array( 'className' => 'is-style-card mc-accent-1' ) ),
				'width' => '38%',
			),
		),
		'mc-faq-cols',
		'top'
	),
	'mc-faq-section',
	'Veelgestelde vragen'
);

// 6. Aanvraag.
$out .= $b::form_section( 'Aanvragen', 'Vraag je feestje aan', 'Vul het formulier in, dan sturen we binnen één werkdag een voorstel. Vrijblijvend.', 'kinderfeest' );

echo $out; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
