<?php
/**
 * Title: Zo werkt het (3 stappen)
 * Slug: megacenter/steps
 * Categories: megacenter-secties
 * Keywords: stappen, uitleg, hoe werkt het
 * Description: Licht vlak met drie grote, genummerde stappen.
 * Viewport Width: 1440
 *
 * @package Megacenter
 */

$b = 'Megacenter_Blocks';

$steps = array(
	array( 'Kies je FUN', 'Lasergame, bowling of toch sim racing? Combineer gerust: alles zit onder één dak, dus wisselen is zo gebeurd.' ),
	array( 'Reserveer online', 'Kies een datum en tijd en je plek staat vast. Met een groep? Dan maken we binnen één werkdag een voorstel op maat.' ),
	array( 'Beleef het', 'Kom binnen, wij regelen de rest. Eten en drinken staan klaar wanneer jij er zin in hebt.' ),
);
$cols  = array();
foreach ( $steps as $i => list( $title, $text ) ) {
	$cols[] = array(
		'inner' => $b::p( sprintf( '%02d', $i + 1 ), 'mc-step__num' ) . $b::h( esc_html( $title ), 3, 'mc-step__title' ) . $b::p( esc_html( $text ), 'mc-step__text' ),
		'class' => 'mc-step',
	);
}

$inner  = $b::head( 'In drie stappen', 'Zo werkt het' );
$inner .= $b::columns( $cols, 'mc-steps', 'top' );

echo $b::section( $inner, 'mc-panel is-style-section-paper', 'Zo werkt het' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
