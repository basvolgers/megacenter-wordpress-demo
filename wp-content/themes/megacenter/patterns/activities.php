<?php
/**
 * Title: Activiteiten-grid
 * Slug: megacenter/activities
 * Categories: megacenter-secties
 * Keywords: activiteiten, grid, kaarten
 * Description: Alle activiteiten als kaarten. Nieuwe activiteiten verschijnen hier automatisch.
 * Viewport Width: 1440
 *
 * @package Megacenter
 */

$b = 'Megacenter_Blocks';

$inner  = $b::head( 'Alles onder één dak', 'Kies je FUN', '', array( 'Bekijk alle activiteiten', megacenter_activities_url() ) );
$inner .= $b::activity_query( array( 'perPage' => 8 ), '', 11 );

echo $b::section( $inner, 'mc-activities', 'Activiteiten' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
