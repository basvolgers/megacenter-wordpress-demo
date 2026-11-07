<?php
/**
 * Title: Eten & drinken (beeld + tekst)
 * Slug: megacenter/food-teaser
 * Categories: megacenter-secties
 * Keywords: eten, drinken, horeca, restaurant, bar
 * Description: Split-sectie met twee overlappende beelden en een korte tekst over eten en drinken.
 * Viewport Width: 1440
 *
 * @package Megacenter
 */

$b = 'Megacenter_Blocks';

$media = $b::group( megacenter_pattern_image( 'eten', 'mc-stack__main' ) . megacenter_pattern_image( 'bar', 'mc-stack__second' ), array( 'className' => 'mc-stack' ) );

$text  = $b::kicker( 'Eten & drinken' );
$text .= $b::h( 'Eerst spelen, dan smullen' );
$text .= $b::p( 'Burgers, pizza uit de steenoven, sharing bites en cocktails. In de bar, aan de bowlingbaan of aan een lange tafel met je hele groep.', 'is-style-lead' );
$text .= $b::lst( array( 'Burgers, pizza\'s en sharing bites', 'Cocktails, speciaalbier en alcoholvrij', 'Kidsmenu en allergeneninfo aan de bar' ), 'is-style-checks' );
$text .= $b::buttons( array( array( 'Bekijk eten & drinken', megacenter_page_url( 'eten-drinken' ) ) ) );

$inner = $b::columns(
	array(
		array(
			'inner' => $media,
			'width' => '50%',
			'class' => 'mc-split__media',
		),
		array(
			'inner' => $text,
			'width' => '50%',
			'class' => 'mc-split__text',
		),
	),
	'mc-split'
);

echo $b::section( $inner, 'mc-food', 'Eten & drinken' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
