<?php
/**
 * Title: Pagina: Privacyverklaring (concept)
 * Slug: megacenter/page-privacy
 * Categories: megacenter-paginas
 * Keywords: privacy, avg, gdpr
 * Description: Korte, duidelijk als concept gemarkeerde privacyverklaring.
 * Block Types: core/post-content
 * Post Types: page
 * Viewport Width: 1440
 *
 * @package Megacenter
 */

$b = 'Megacenter_Blocks';

$out  = $b::p( '<strong>Concepttekst.</strong> Deze verklaring is een voorbeeld voor de demo en moet door de opdrachtgever (juridisch) worden gecontroleerd en aangevuld voordat de site live gaat.', 'mc-concept' );
$out .= $b::p( 'We gaan zorgvuldig om met je gegevens. Op deze pagina lees je welke gegevens we verzamelen, waarom, en wat je rechten zijn.', 'is-style-lead' );
$out .= $b::h( 'Welke gegevens verzamelen we?' );
$out .= $b::p( 'Als je een aanvraag doet via een formulier op deze website, ontvangen we de gegevens die je invult: je naam, e-mailadres, telefoonnummer (optioneel), de gewenste datum, het aantal personen en je bericht.' );
$out .= $b::h( 'Waarvoor gebruiken we ze?' );
$out .= $b::p( 'Alleen om je aanvraag te beantwoorden en je reservering of feest te organiseren. We gebruiken je gegevens niet voor reclame zonder je toestemming en verkopen ze nooit door.' );
$out .= $b::h( 'Hoe lang bewaren we ze?' );
$out .= $b::p( 'Aanvragen die niet tot een reservering leiden, verwijderen we na uiterlijk 12 maanden. Gegevens die we voor de boekhouding nodig hebben, bewaren we zolang de wet dat voorschrijft.' );
$out .= $b::h( 'Cookies en tracking' );
$out .= $b::p( 'Deze website gebruikt geen tracking- of advertentiecookies en laadt geen externe diensten (zoals Google Fonts of kaarten) bij het openen van een pagina. Zie ook de cookieverklaring.' );
$out .= $b::h( 'Je rechten' );
$out .= $b::p( 'Je kunt je gegevens altijd inzien, laten aanpassen of laten verwijderen. Stuur ons daarvoor een e-mail. Ben je het niet eens met hoe we met je gegevens omgaan, dan kun je een klacht indienen bij de Autoriteit Persoonsgegevens.' );

echo $b::group( $out, array( 'className' => 'mc-prose', 'layout' => array( 'type' => 'constrained' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
