<?php
/**
 * Title: Lichtkrant met activiteiten
 * Slug: megacenter/band
 * Categories: megacenter-secties
 * Keywords: marquee, band, lichtkrant
 * Description: Schuine, bewegende band met alle activiteiten.
 * Viewport Width: 1440
 *
 * @package Megacenter
 */

$b = 'Megacenter_Blocks';

echo $b::group( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	$b::block( 'megacenter/marquee' ),
	array(
		'metadata'  => array( 'name' => 'Lichtkrant' ),
		'align'     => 'full',
		'className' => 'mc-band',
	)
);
