<?php
/**
 * Title: Voor wie? (4 tegels)
 * Slug: megacenter/audiences
 * Categories: megacenter-secties
 * Keywords: doelgroepen, tegels, kinderfeest, zakelijk
 * Description: Vier grote tegels naar kinderfeestjes, groepsuitjes, zakelijk en eten & drinken.
 * Viewport Width: 1440
 *
 * @package Megacenter
 */

$b = 'Megacenter_Blocks';

$tiles = array(
	array( 'kinderfeest-2', 'accent-2', 'Kinderfeestjes', 'Het feestje waar de hele klas over praat. Met eten, drinken en een feesthost.', 'kinderfeestjes' ),
	array( 'groepen', 'accent-4', 'Groepsuitjes & vrijgezellen', 'Vrienden, familie of vrijgezellen: combineer activiteiten met een hapje en een drankje.', 'groepsuitjes' ),
	array( 'zakelijk', 'accent-3', 'Zakelijk & teambuilding', 'Bedrijfsuitje, teambuilding of personeelsfeest. Wij regelen alles, vlak bij de A1.', 'zakelijk' ),
	array( 'eten', 'accent-1', 'Eten & drinken', 'Burgers, pizza en cocktails. Tussendoor, na afloop of als compleet arrangement.', 'eten-drinken' ),
);

$grid = '';
foreach ( $tiles as $i => list( $img, $accent, $title, $text, $slug ) ) {
	$tile  = megacenter_pattern_image( $img, 'mc-tile__img', '-' );
	$tile .= $b::p( sprintf( '%02d', $i + 1 ), 'mc-tile__num' );
	$tile .= $b::h( '<a href="' . esc_url( megacenter_page_url( $slug ) ) . '">' . esc_html( $title ) . '</a>', 3, 'mc-tile__title' );
	$tile .= $b::p( esc_html( $text ), 'mc-tile__text' );
	$grid .= $b::group( $tile, array( 'className' => 'mc-tile mc-' . $accent ) );
}

$inner  = $b::head( 'Voor wie?', 'Iedereen speelt mee', 'Van de eerste verjaardag tot het jubileum van de zaak: hier heeft iedereen zijn eigen soort FUN.' );
$inner .= $b::group(
	$grid,
	array(
		'className' => 'mc-tiles',
		'layout'    => array(
			'type'               => 'grid',
			'columnCount'        => 4,
			'minimumColumnWidth' => '15rem',
		),
	)
);

echo $b::section( $inner, 'mc-audiences', 'Voor wie?' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
