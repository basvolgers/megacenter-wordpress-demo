<?php
/**
 * Title: CTA-band: Klaar voor FUN?
 * Slug: megacenter/cta-band
 * Categories: megacenter-secties
 * Keywords: cta, reserveren, afsluiter
 * Description: Grote, schuine afsluiter in limoen met reserveer- en belknop.
 * Viewport Width: 1440
 *
 * @package Megacenter
 */

$b = 'Megacenter_Blocks';

$inner  = $b::h( 'Klaar voor FUN?', 2, 'mc-cta__title' );
$inner .= $b::p( 'Reserveer je activiteit online of vraag een voorstel aan voor je groep. We reageren binnen één werkdag.', 'mc-cta__text' );
$inner .= $b::buttons(
	array(
		array( 'Reserveer nu', megacenter_page_url( 'reserveren' ), '', $b::bind( array( 'url' => 'reserve_url' ) ) ),
		array( 'Bel ons', 'tel:+31548000000', 'outline', $b::bind( array( 'url' => 'phone_url' ) ) ),
	),
	'mc-cta__buttons'
);

echo $b::section( $inner, 'mc-cta', 'Klaar voor FUN?' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
