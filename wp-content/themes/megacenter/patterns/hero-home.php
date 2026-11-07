<?php
/**
 * Title: Home: hero met roterende kop
 * Slug: megacenter/hero-home
 * Categories: megacenter-secties
 * Keywords: hero, home, kop, fun
 * Description: Grote openingssectie met roterend activiteitwoord, twee knoppen, USP-chips en een beeldcollage.
 * Viewport Width: 1440
 *
 * @package Megacenter
 */

$b = 'Megacenter_Blocks';

$text  = $b::kicker( 'Indoor entertainment · Rijssen' );
$text .= $b::block( 'megacenter/fun-rotator', array( 'className' => 'mc-hero__title' ) );
$text .= $b::p( 'Lasergame, bowling, escape rooms, sim racing en meer, onder één dak in Rijssen.', 'is-style-lead mc-hero__lead' );
$text .= $b::buttons(
	array(
		array( 'Reserveer', megacenter_page_url( 'reserveren' ), '', $b::bind( array( 'url' => 'reserve_url' ) ) ),
		array( 'Ontdek alle activiteiten', megacenter_activities_url(), 'outline' ),
	),
	'mc-hero__buttons'
);
$text .= $b::lst( array( 'Alles onder één dak', 'Voor kids, vrienden én collega\'s', 'Eten &amp; drinken erbij' ), 'is-style-chips mc-hero__usps' );

$media  = megacenter_pattern_image( 'hero-arcade', 'mc-collage__main' );
$media .= megacenter_pattern_image( 'hero-vr', 'mc-collage__second' );
$media .= $b::p( '<strong>Nieuw</strong> Sim Racing', 'mc-collage__badge' );
$media  = $b::group( $media, array( 'className' => 'mc-collage' ) );

echo $b::group( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	$b::columns(
		array(
			array(
				'inner' => $text,
				'width' => '56%',
				'class' => 'mc-hero__text',
			),
			array(
				'inner' => $media,
				'width' => '44%',
				'class' => 'mc-hero__media',
			),
		),
		'mc-hero__cols'
	),
	array(
		'tagName'      => 'section',
		'metadata'     => array( 'name' => 'Hero' ),
		'align'        => 'full',
		'className'    => 'mc-hero',
		'templateLock' => 'contentOnly',
		'layout'       => array(
			'type'        => 'constrained',
			'contentSize' => '84rem',
		),
	)
);
