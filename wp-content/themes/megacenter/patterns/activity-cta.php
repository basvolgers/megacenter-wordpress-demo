<?php
/**
 * Title: Maak er een arrangement van
 * Slug: megacenter/activity-cta
 * Categories: megacenter-secties
 * Keywords: arrangement, cta, groep, kinderfeest, zakelijk
 * Description: Banner die bezoekers van een activiteit naar de arrangementen voor kinderfeestjes, groepen en zakelijk stuurt.
 * Viewport Width: 1440
 *
 * @package Megacenter
 */

$b = 'Megacenter_Blocks';

$text  = $b::kicker( 'Met een groep?' );
$text .= $b::h( 'Maak er een arrangement van', 2, 'mc-arr__title' );
$text .= $b::p( 'Combineer deze activiteit met een tweede activiteit, eten en drinken. Eén prijs, alles geregeld.', 'mc-arr__text' );

$links = $b::buttons(
	array(
		array( 'Kinderfeestjes', megacenter_page_url( 'kinderfeestjes' ) ),
		array( 'Groepsuitjes', megacenter_page_url( 'groepsuitjes' ), 'outline' ),
		array( 'Zakelijk', megacenter_page_url( 'zakelijk' ), 'outline' ),
	),
	'mc-arr__buttons'
);

echo $b::group( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	$b::columns(
		array(
			array(
				'inner' => $text,
				'width' => '58%',
			),
			array(
				'inner' => $links,
				'width' => '42%',
			),
		),
		'mc-arr__cols'
	),
	array(
		'metadata'     => array( 'name' => 'Arrangement-CTA' ),
		'align'        => 'wide',
		'className'    => 'mc-arr',
		'templateLock' => 'contentOnly',
	)
);
