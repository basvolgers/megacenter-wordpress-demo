<?php
/**
 * Title: Pagina: Groepsuitjes
 * Slug: megacenter/page-groepsuitjes
 * Categories: megacenter-paginas
 * Keywords: groep, vrienden, familie, vrijgezellen
 * Description: Pagina voor vrienden, familie en vrijgezellen: hero, gelegenheden, arrangementen en aanvraagformulier.
 * Block Types: core/post-content
 * Post Types: page
 * Viewport Width: 1440
 *
 * @package Megacenter
 */

$b = 'Megacenter_Blocks';

$out = $b::page_hero(
	array(
		'kicker'  => 'Groepsuitjes · vanaf 8 personen',
		'title'   => 'Samen uit, samen winnen',
		'lead'    => 'Met vrienden, familie, je sportteam of voor een vrijgezellenfeest: combineer activiteiten met eten en drinken. Wij regelen de rest.',
		'buttons' => array(
			array( 'Vraag een voorstel aan', '#aanvraag-formulier' ),
			array( 'Bekijk activiteiten', megacenter_activities_url(), 'outline' ),
		),
		'chips'   => array( 'Battles met scorebord', 'Eigen plek aan tafel', 'Voorstel binnen één werkdag' ),
		'images'  => array( 'groepen', 'hero-sparklers' ),
		'badge'   => '<strong>Battle</strong> team tegen team',
		'accent'  => 'mc-accent-4',
	)
);

$tiles = array(
	array( 'hero-sparklers', 'accent-1', 'Vriendengroep', 'Lasergame-battle, een rondje bowlen en daarna burgers. Of andersom.' ),
	array( 'bowling-2', 'accent-3', 'Familie-uitje', 'Van oma tot de kleinste: glowgolf en bowling zijn voor iedereen te doen.' ),
	array( 'vrijgezellen', 'accent-2', 'Vrijgezellenfeest', 'Uitdagingen, een sjerp voor de vrijgezel en een tafel vol drankjes.' ),
);
$grid  = '';
foreach ( $tiles as $i => list( $img, $accent, $title, $text ) ) {
	$tile  = megacenter_pattern_image( $img, 'mc-tile__img', '-' );
	$tile .= $b::p( sprintf( '%02d', $i + 1 ), 'mc-tile__num' );
	$tile .= $b::h( '<a href="#aanvraag-formulier">' . esc_html( $title ) . '</a>', 3, 'mc-tile__title' );
	$tile .= $b::p( esc_html( $text ), 'mc-tile__text' );
	$grid .= $b::group( $tile, array( 'className' => 'mc-tile mc-' . $accent ) );
}
$out .= $b::section(
	$b::head( 'Voor elke groep', 'Waar vieren jullie het?' ) . $b::group(
		$grid,
		array(
			'className' => 'mc-tiles mc-tiles-3',
			'layout'    => array(
				'type'               => 'grid',
				'columnCount'        => 3,
				'minimumColumnWidth' => '16rem',
			),
		)
	),
	'mc-audiences',
	'Gelegenheden'
);

$cards  = $b::package( 'Snel geregeld', 'Combi-deal', '€ 39,50', 'p.p.', array( '2 activiteiten naar keuze', 'Burger of pizza met een drankje', 'Vanaf 4 personen' ), array( 'Vraag de combi aan', '#aanvraag-formulier' ), '', 'mc-accent-3' );
$cards .= $b::package( 'Competitie', 'Battle Night', '€ 49,50', 'p.p.', array( 'Lasergame- of escape room-battle', 'Sharing bites en 2 drankjes', 'Eigen scorebord en prijsuitreiking', 'Vanaf 8 personen' ), array( 'Vraag Battle Night aan', '#aanvraag-formulier' ), 'Favoriet', 'mc-accent-1' );
$cards .= $b::package( 'Vrijgezellen', 'Vrijgezellen Deluxe', '€ 64,50', 'p.p.', array( '2 activiteiten, bijv. VR + sim racing', 'Burger of pizza naar keuze', 'Welkomstdrankje + 3 uur drankjes', 'Sjerp en fotomoment voor de vrijgezel' ), array( 'Vraag Deluxe aan', '#aanvraag-formulier' ), '', 'mc-accent-2' );
$out   .= $b::section(
	$b::head( 'Arrangementen', 'Alles in één prijs', 'Alle arrangementen zijn aan te passen. Andere activiteit of extra uurtje? Zeg het maar.' ) . $b::group(
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

$out .= $b::form_section( 'Aanvragen', 'Vraag een voorstel aan', 'Vertel ons met hoeveel jullie zijn en wat jullie willen doen. Je krijgt binnen één werkdag een voorstel.', 'groep' );

echo $out; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
