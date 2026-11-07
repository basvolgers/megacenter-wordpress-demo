<?php
/**
 * Title: Pagina: Cookieverklaring (concept)
 * Slug: megacenter/page-cookies
 * Categories: megacenter-paginas
 * Keywords: cookies, privacy
 * Description: Korte, duidelijk als concept gemarkeerde cookieverklaring.
 * Block Types: core/post-content
 * Post Types: page
 * Viewport Width: 1440
 *
 * @package Megacenter
 */

$b = 'Megacenter_Blocks';

$out  = $b::p( '<strong>Concepttekst.</strong> Controleer en vul deze verklaring aan zodra er (bijvoorbeeld via het reserveringssysteem) diensten van derden worden toegevoegd.', 'mc-concept' );
$out .= $b::p( 'Goed nieuws: deze website gebruikt geen tracking-, analyse- of advertentiecookies. Daarom zie je ook geen cookiebanner.', 'is-style-lead' );
$out .= $b::h( 'Welke opslag gebruiken we wel?' );
$out .= $b::lst(
	array(
		'<strong>Inlogcookies</strong> alleen voor beheerders van de website (functioneel).',
		'<strong>Demomelding</strong> als je de demomelding wegklikt, onthoudt je browser dat tijdens dit bezoek (sessionStorage, geen cookie).',
	)
);
$out .= $b::h( 'En externe diensten?' );
$out .= $b::p( 'Lettertypes en beelden staan op onze eigen server. Kaarten en routes openen pas als je zelf op "Plan je route" klikt. Komt er later een reserveringswidget van een externe partij op de site, dan vermelden we hier welke cookies die plaatst.' );

echo $b::group( $out, array( 'className' => 'mc-prose', 'layout' => array( 'type' => 'constrained' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
