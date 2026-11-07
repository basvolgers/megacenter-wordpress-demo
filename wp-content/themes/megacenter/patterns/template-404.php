<?php
/**
 * Title: Template: 404 (pagina niet gevonden)
 * Slug: megacenter/template-404
 * Inserter: no
 *
 * @package Megacenter
 */

$b = 'Megacenter_Blocks';

$inner  = $b::p( '404', 'mc-404__num' );
$inner .= $b::kicker( 'Pagina niet gevonden' );
$inner .= $b::h( 'Oeps. Deze pagina is ontsnapt.', 1, 'mc-404__title' );
$inner .= $b::p( 'Zelfs onze beste escape room-teams vinden hem niet terug. Gelukkig staat de uitgang hieronder.', 'is-style-lead' );
$inner .= $b::lst(
	array(
		'<strong>Hint 1</strong> Check de link nog eens op typfouten.',
		'<strong>Hint 2</strong> Probeer de homepage of het overzicht van activiteiten.',
		'<strong>Hint 3</strong> Nog steeds vast? Bel de spelleider, oftewel: ons.',
	),
	'mc-hints'
);
$inner .= $b::buttons(
	array(
		array( 'Naar de homepage', home_url( '/' ) ),
		array( 'Alle activiteiten', megacenter_activities_url(), 'outline' ),
	)
);

echo $b::group( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	$b::group(
		$inner,
		array(
			'align'     => 'full',
			'className' => 'mc-404',
			'layout'    => array(
				'type'        => 'constrained',
				'contentSize' => '52rem',
			),
		)
	),
	array(
		'tagName'   => 'main',
		'className' => 'mc-main',
		'layout'    => array( 'type' => 'constrained' ),
	)
);
