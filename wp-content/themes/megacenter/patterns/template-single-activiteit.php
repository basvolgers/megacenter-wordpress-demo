<?php
/**
 * Title: Template: activiteit (detail)
 * Slug: megacenter/template-single-activiteit
 * Inserter: no
 *
 * Hero in de accentkleur, inhoud uit de editor, highlights, "Combineer met", arrangement-CTA, FAQ
 * en op mobiel een plakkende reserveerbalk. Alle gegevens komen uit de activiteit (Block Bindings).
 *
 * @package Megacenter
 */

$b = 'Megacenter_Blocks';

$reserve = $b::bind(
	array(
		'url'  => 'reserve_url',
		'text' => 'reserve_label',
	),
	'megacenter/activity'
);

// Hero.
$text  = $b::p( '<a href="' . esc_url( megacenter_activities_url() ) . '">Alle activiteiten</a>', 'mc-backlink' );
$text .= $b::block(
	'post-title',
	array(
		'level'     => 1,
		'className' => 'mc-activity-hero__title',
	)
);
$text .= $b::p( '', 'is-style-lead mc-activity-hero__tagline', array( 'metadata' => $b::bind( array( 'content' => 'megacenter_tagline' ), 'core/post-meta' ) ) );
$text .= $b::block( 'megacenter/activity-facts', array( 'variant' => 'chips' ) );
$text .= $b::buttons(
	array(
		array( 'Reserveer', megacenter_page_url( 'reserveren' ), '', $reserve ),
		array( 'Groepsvoorstel aanvragen', megacenter_page_url( 'groepsuitjes' ) . '#aanvraag-formulier', 'outline' ),
	),
	'mc-hero__buttons'
);

$media  = $b::block(
	'post-featured-image',
	array(
		'aspectRatio' => '4/5',
		'className'   => 'mc-activity-hero__img',
	)
);
$media .= $b::p( '', 'mc-card__badge mc-activity-hero__badge', array( 'metadata' => $b::bind( array( 'content' => 'badge' ), 'megacenter/activity' ) ) );

$hero = $b::group(
	$b::columns(
		array(
			array(
				'inner' => $text,
				'width' => '56%',
				'class' => 'mc-activity-hero__text',
			),
			array(
				'inner' => $b::group( $media, array( 'className' => 'mc-activity-hero__media' ) ),
				'width' => '44%',
			),
		),
		'mc-hero__cols'
	),
	array(
		'align'     => 'full',
		'className' => 'mc-activity-hero',
		'layout'    => array(
			'type'        => 'constrained',
			'contentSize' => '84rem',
		),
	)
);

// Inhoud uit de editor (intro, "Zo werkt het", galerij).
$body = $b::block(
	'post-content',
	array(
		'align'     => 'full',
		'className' => 'mc-activity-content',
		'layout'    => array( 'type' => 'constrained' ),
	)
);

// Highlights.
$highlights = $b::group(
	$b::head( 'Waarom je dit niet wilt missen', 'Highlights' ) . $b::block( 'megacenter/activity-highlights' ),
	array(
		'align'     => 'wide',
		'className' => 'mc-block-section mc-highlights-wrap',
	)
);

// Combineer met.
$combine = $b::group(
	$b::head( 'Nog meer FUN', 'Combineer met', '', array( 'Alle activiteiten', megacenter_activities_url() ) )
	. $b::activity_query(
		array(
			'perPage'                  => 3,
			'megacenterExcludeCurrent' => true,
		),
		'mc-cards-related',
		12,
		3
	),
	array(
		'align'     => 'wide',
		'className' => 'mc-block-section',
	)
);

// FAQ.
$faq = $b::group(
	$b::kicker( 'Goed om te weten' ) . $b::h( 'Veelgestelde vragen' ) . $b::block( 'megacenter/activity-faq' ),
	array( 'className' => 'mc-block-section mc-activity-faq' )
);

// Plakkende reserveerbalk (alleen mobiel zichtbaar).
$sticky = $b::group(
	$b::group(
		$b::p( '', 'mc-sticky-reserve__title', array( 'metadata' => $b::bind( array( 'content' => 'title' ), 'megacenter/activity' ) ) )
		. $b::p( '', 'mc-sticky-reserve__price', array( 'metadata' => $b::bind( array( 'content' => 'price_short' ), 'megacenter/activity' ) ) ),
		array( 'className' => 'mc-sticky-reserve__text' )
	)
	. $b::buttons( array( array( 'Reserveer', megacenter_page_url( 'reserveren' ), '', $b::bind( array( 'url' => 'reserve_url' ), 'megacenter/activity' ) ) ) ),
	array( 'className' => 'mc-sticky-reserve' )
);

echo $b::group( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	$hero . $body . $highlights . $combine . '<!-- wp:pattern {"slug":"megacenter/activity-cta"} /-->' . "\n" . $faq . $sticky,
	array(
		'tagName'   => 'main',
		'className' => 'mc-main mc-activity',
		'layout'    => array( 'type' => 'constrained' ),
	)
);
