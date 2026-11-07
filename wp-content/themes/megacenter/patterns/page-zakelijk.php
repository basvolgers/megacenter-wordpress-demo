<?php
/**
 * Title: Pagina: Zakelijk
 * Slug: megacenter/page-zakelijk
 * Categories: megacenter-paginas
 * Keywords: zakelijk, bedrijfsuitje, teambuilding, personeelsfeest, vergaderen
 * Description: Zakelijke pagina: hero, aanbod, werkwijze, arrangementen en aanvraagformulier.
 * Block Types: core/post-content
 * Post Types: page
 * Viewport Width: 1440
 *
 * @package Megacenter
 */

$b = 'Megacenter_Blocks';

$out = $b::page_hero(
	array(
		'kicker'  => 'Zakelijk · uitjes, teambuilding & events',
		'title'   => 'Teamwork, maar dan leuk',
		'lead'    => 'Een bedrijfsuitje waar collega\'s het nog maanden over hebben. Van vergadering met activiteit tot personeelsfeest voor het hele bedrijf, vlak bij de A1.',
		'buttons' => array(
			array( 'Ontvang een voorstel', '#aanvraag-formulier' ),
			array( 'Bekijk activiteiten', megacenter_activities_url(), 'outline' ),
		),
		'chips'   => array( 'Eén aanspreekpunt', 'Voorstel binnen één werkdag', 'Gratis parkeren' ),
		'images'  => array( 'zakelijk', 'escaperoom' ),
		'badge'   => '<strong>All-in</strong> vanaf 12 pers.',
		'accent'  => 'mc-accent-3',
	)
);

$offers = array(
	array( 'Bedrijfsuitje', 'Een middag of avond vol FUN, met eten en drinken erbij. Perfect als bedankje voor het team.' ),
	array( 'Teambuilding', 'Escape room-battle of lasergame met opdrachten: samenwerken, communiceren en lachen.' ),
	array( 'Personeelsfeest', 'Een complete avond met buffet, bar, battles en muziek. Voor kleine en grote teams.' ),
	array( 'Vergaderen + activiteit', 'Eerst scherp vergaderen, dan samen ontladen. Vergaderruimte met scherm en lunch.' ),
	array( 'Interactieve quiz', 'Een quizmaster, teams met tablets en vragen over jullie eigen bedrijf. Gegarandeerd hilarisch.' ),
	array( 'Op maat', 'Iets anders in gedachten? Jubileum, klantenevent of kick-off: we denken graag mee.' ),
);
$cards  = '';
foreach ( $offers as $i => list( $title, $text ) ) {
	$cards .= $b::group( $b::p( sprintf( '%02d', $i + 1 ), 'mc-offer__num' ) . $b::h( esc_html( $title ), 3 ) . $b::p( esc_html( $text ) ), array( 'className' => 'is-style-card mc-offer' ) );
}
$out .= $b::section(
	$b::head( 'Het aanbod', 'Wat kunnen we voor je doen?' ) . $b::group(
		$cards,
		array(
			'className' => 'mc-offers',
			'layout'    => array(
				'type'               => 'grid',
				'columnCount'        => 3,
				'minimumColumnWidth' => '16rem',
			),
		)
	),
	'mc-offers-section',
	'Aanbod'
);

$steps = array(
	array( 'Vertel je wensen', 'Datum, aantal collega\'s, budget en sfeer. Via het formulier of even bellen.' ),
	array( 'Voorstel op maat', 'Binnen één werkdag een helder voorstel met programma en vaste prijs.' ),
	array( 'Wij regelen de rest', 'Eén aanspreekpunt op de dag zelf. Jij hoeft alleen maar mee te doen.' ),
);
$cols  = array();
foreach ( $steps as $i => list( $title, $text ) ) {
	$cols[] = array(
		'inner' => $b::p( sprintf( '%02d', $i + 1 ), 'mc-step__num' ) . $b::h( esc_html( $title ), 3, 'mc-step__title' ) . $b::p( esc_html( $text ), 'mc-step__text' ),
		'class' => 'mc-step',
	);
}
$out .= $b::section( $b::head( 'Werkwijze', 'Zo organiseren we het' ) . $b::columns( $cols, 'mc-steps', 'top' ), 'mc-panel is-style-section-paper', 'Werkwijze' );

$cards  = $b::package( 'Vergaderen', 'Vergaderen + FUN', '€ 54,50', 'p.p.', array( 'Vergaderruimte met scherm (dagdeel)', 'Koffie, thee en lunch', '1 activiteit als afsluiter' ), array( 'Vraag dit aan', '#aanvraag-formulier' ), '', 'mc-accent-3' );
$cards .= $b::package( 'Bedrijfsuitje', 'Bedrijfsuitje All-in', '€ 69,50', 'p.p.', array( 'Ontvangst met koffie en iets lekkers', '2 activiteiten of een battle', 'Buffet en 3 uur drankjes', 'Vanaf 12 personen' ), array( 'Vraag All-in aan', '#aanvraag-formulier' ), 'Meest gekozen', 'mc-accent-1' );
$cards .= $b::package( 'Personeelsfeest', 'Feest op maat', 'Op aanvraag', '', array( 'Exclusief (een deel van) de locatie', 'Buffet, bar en DJ op maat', 'Battles met prijsuitreiking' ), array( 'Bespreek je feest', '#aanvraag-formulier' ), '', 'mc-accent-2' );
$out   .= $b::section(
	$b::head( 'Arrangementen', 'Heldere prijzen', 'Alle prijzen zijn exclusief btw en gebaseerd op minimaal 12 personen. Op maat? Altijd mogelijk.' ) . $b::group(
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
	'Arrangementen'
);

$out .= $b::form_section( 'Offerte', 'Ontvang een voorstel op maat', 'Laat je gegevens achter, dan sturen we binnen één werkdag een voorstel met programma en prijs.', 'zakelijk' );

echo $out; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
