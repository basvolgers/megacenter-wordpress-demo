<?php
/**
 * Title: Arrangementen (3 pakketten)
 * Slug: megacenter/packages
 * Categories: megacenter-secties
 * Keywords: arrangementen, pakketten, prijzen, combi
 * Description: Drie pakketkaarten met vanaf-prijs, wat erin zit en een knop. De middelste is uitgelicht.
 * Viewport Width: 1440
 *
 * @package Megacenter
 */

$b = 'Megacenter_Blocks';

$cards  = $b::package( 'Vrienden & familie', 'Combi-deal', '€ 39,50', 'p.p.', array( 'Kies 2 activiteiten, bijv. lasergame + glowgolf', 'Burger of pizza met een drankje', 'Vanaf 4 personen' ), array( 'Reserveer de combi', megacenter_page_url( 'reserveren' ) ), '', 'mc-accent-3' );
$cards .= $b::package( 'Kinderfeestjes', 'Kinderfeest Neon', '€ 24,50', 'per kind', array( '2 activiteiten naar keuze', 'Kindermenu, 2 drankjes en een ijsje', 'Feesthost die alles begeleidt' ), array( 'Bekijk kinderfeestjes', megacenter_page_url( 'kinderfeestjes' ) ), 'Populair', 'mc-accent-1' );
$cards .= $b::package( 'Zakelijk', 'Bedrijfsuitje All-in', '€ 69,50', 'p.p.', array( 'Ontvangst met koffie en iets lekkers', '2 activiteiten of een battle', 'Buffet en 3 uur drankjes', 'Vanaf 12 personen' ), array( 'Vraag een voorstel aan', megacenter_page_url( 'zakelijk' ) ), '', 'mc-accent-4' );

$inner  = $b::head( 'Alles geregeld', 'Arrangementen', 'Activiteit, eten en drinken in één keer geregeld. Met heldere prijzen, zonder gedoe.' );
$inner .= $b::group(
	$cards,
	array(
		'className' => 'mc-packages',
		'layout'    => array(
			'type'               => 'grid',
			'columnCount'        => 3,
			'minimumColumnWidth' => '17rem',
		),
	)
);

echo $b::section( $inner, 'mc-packages-section', 'Arrangementen' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
