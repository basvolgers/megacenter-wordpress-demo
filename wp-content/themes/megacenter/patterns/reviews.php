<?php
/**
 * Title: Reviews (placeholder voor echte Google-reviews)
 * Slug: megacenter/reviews
 * Categories: megacenter-secties
 * Keywords: reviews, beoordelingen, google
 * Description: Reviewcomponent. Bewust gevuld met een duidelijk gemarkeerde placeholder: we tonen nooit verzonnen reviews.
 * Viewport Width: 1440
 *
 * @package Megacenter
 */

$b = 'Megacenter_Blocks';

$cards = '';
for ( $i = 0; $i < 3; $i++ ) {
	$card   = $b::p( 'Hier komen jullie echte Google-reviews', 'mc-review__quote' );
	$card  .= $b::p( 'Automatisch ingeladen zodra de site live staat.', 'mc-review__meta' );
	$cards .= $b::group( $card, array( 'className' => 'mc-review is-placeholder' ) );
}

$inner  = $b::head( 'Reviews', 'Wat bezoekers zeggen', 'Placeholder: hier tonen we straks jullie echte beoordelingen. We gebruiken nooit verzonnen reviews.' );
$inner .= $b::group(
	$cards,
	array(
		'className' => 'mc-reviews',
		'layout'    => array(
			'type'               => 'grid',
			'columnCount'        => 3,
			'minimumColumnWidth' => '16rem',
		),
	)
);

echo $b::section( $inner, 'mc-reviews-section', 'Reviews' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
