<?php
/**
 * Kleine bouwstenen voor patronen: genereert exact de block-markup die de editor zelf opslaat,
 * zodat patronen geldig blijven en de patroonbestanden leesbaar zijn.
 *
 * @package Megacenter
 */

defined( 'ABSPATH' ) || exit;

/**
 * Bouwstenen voor block-markup.
 */
class Megacenter_Blocks {

	/**
	 * Attributen als JSON voor de blokcommentaar.
	 *
	 * @param array $attrs Attributen.
	 * @return string
	 */
	public static function json( $attrs ) {
		$attrs = array_filter( $attrs, static fn( $v ) => null !== $v && '' !== $v && array() !== $v );
		if ( ! $attrs ) {
			return '';
		}
		$json = wp_json_encode( $attrs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
		return ' ' . str_replace( array( '--', '<', '>' ), array( '\\u002d\\u002d', '\\u003c', '\\u003e' ), $json );
	}

	/**
	 * Bindings-metadata.
	 *
	 * @param array  $map    Attribuut => sleutel.
	 * @param string $source Bron.
	 * @return array
	 */
	public static function bind( $map, $source = 'megacenter/site' ) {
		$bindings = array();
		foreach ( $map as $attr => $key ) {
			$bindings[ $attr ] = array(
				'source' => $source,
				'args'   => array( 'key' => $key ),
			);
		}
		return array( 'bindings' => $bindings );
	}

	/**
	 * Class-attribuut.
	 *
	 * @param string ...$classes Classes.
	 * @return string
	 */
	private static function cls( ...$classes ) {
		$classes = trim( implode( ' ', array_filter( $classes ) ) );
		return $classes ? ' class="' . esc_attr( $classes ) . '"' : '';
	}

	/**
	 * Alinea.
	 *
	 * @param string $html  Inhoud (vertrouwde HTML).
	 * @param string $class Class.
	 * @param array  $extra Extra attributen (bijv. metadata).
	 * @return string
	 */
	public static function p( $html, $class = '', $extra = array() ) {
		return '<!-- wp:paragraph' . self::json( array( 'className' => $class ) + $extra ) . " -->\n<p" . self::cls( $class ) . '>' . $html . "</p>\n<!-- /wp:paragraph -->\n";
	}

	/**
	 * Kicker boven een kop.
	 *
	 * @param string $text Tekst.
	 * @return string
	 */
	public static function kicker( $text ) {
		return self::p( esc_html( $text ), 'is-style-kicker' );
	}

	/**
	 * Kop.
	 *
	 * @param string $html  Inhoud.
	 * @param int    $level Niveau.
	 * @param string $class Class.
	 * @param array  $extra Extra attributen.
	 * @return string
	 */
	public static function h( $html, $level = 2, $class = '', $extra = array() ) {
		$attrs = array();
		if ( 2 !== $level ) {
			$attrs['level'] = $level;
		}
		$attrs['className'] = $class;
		return '<!-- wp:heading' . self::json( $attrs + $extra ) . " -->\n<h{$level}" . self::cls( 'wp-block-heading', $class ) . '>' . $html . "</h{$level}>\n<!-- /wp:heading -->\n";
	}

	/**
	 * Knoppen.
	 *
	 * @param array  $buttons Lijst: [text, url, style?, bindings?].
	 * @param string $class   Class.
	 * @return string
	 */
	public static function buttons( $buttons, $class = '' ) {
		$out = '<!-- wp:buttons' . self::json( array( 'className' => $class ) ) . " -->\n<div" . self::cls( 'wp-block-buttons', $class ) . '>';
		foreach ( $buttons as $b ) {
			$style = ! empty( $b[2] ) ? 'is-style-' . $b[2] : '';
			$attrs = array( 'className' => $style );
			if ( ! empty( $b[3] ) ) {
				$attrs['metadata'] = $b[3];
			}
			$out .= '<!-- wp:button' . self::json( $attrs ) . " -->\n<div" . self::cls( 'wp-block-button', $style ) . '><a class="wp-block-button__link wp-element-button" href="' . esc_url( $b[1] ) . '">' . esc_html( $b[0] ) . "</a></div>\n<!-- /wp:button -->";
		}
		return $out . "</div>\n<!-- /wp:buttons -->\n";
	}

	/**
	 * Lijst.
	 *
	 * @param string[] $items Items (vertrouwde HTML).
	 * @param string   $class Class.
	 * @param bool     $ordered Genummerd.
	 * @return string
	 */
	public static function lst( $items, $class = '', $ordered = false ) {
		$tag   = $ordered ? 'ol' : 'ul';
		$attrs = array( 'className' => $class );
		if ( $ordered ) {
			$attrs = array( 'ordered' => true ) + $attrs;
		}
		$out = '<!-- wp:list' . self::json( $attrs ) . " -->\n<{$tag}" . self::cls( 'wp-block-list', $class ) . '>';
		foreach ( $items as $item ) {
			$out .= "<!-- wp:list-item -->\n<li>" . $item . "</li>\n<!-- /wp:list-item -->";
		}
		return $out . "</{$tag}>\n<!-- /wp:list -->\n";
	}

	/**
	 * Uitklapvraag.
	 *
	 * @param string $question Vraag.
	 * @param string $answer   Antwoord.
	 * @return string
	 */
	public static function details( $question, $answer ) {
		return "<!-- wp:details -->\n<details class=\"wp-block-details\"><summary>" . esc_html( $question ) . '</summary>' . self::p( esc_html( $answer ) ) . "</details>\n<!-- /wp:details -->\n";
	}

	/**
	 * Groep.
	 *
	 * @param string $inner Inhoud.
	 * @param array  $attrs Attributen (tagName, align, anchor, className, templateLock, layout, metadata).
	 * @return string
	 */
	public static function group( $inner, $attrs = array() ) {
		$tag   = $attrs['tagName'] ?? 'div';
		$align = ! empty( $attrs['align'] ) ? 'align' . $attrs['align'] : '';
		$id    = ! empty( $attrs['anchor'] ) ? ' id="' . esc_attr( $attrs['anchor'] ) . '"' : '';
		$attrs += array( 'layout' => array( 'type' => 'default' ) );
		return '<!-- wp:group' . self::json( $attrs ) . " -->\n<{$tag}{$id}" . self::cls( 'wp-block-group', $align, $attrs['className'] ?? '' ) . ">\n" . $inner . "</{$tag}>\n<!-- /wp:group -->\n";
	}

	/**
	 * Kolommen.
	 *
	 * @param array  $cols   Kolommen: ['inner' =>, 'width' =>, 'class' =>].
	 * @param string $class  Class.
	 * @param string $valign Verticale uitlijning.
	 * @return string
	 */
	public static function columns( $cols, $class = '', $valign = 'center' ) {
		$out = '<!-- wp:columns' . self::json(
			array(
				'verticalAlignment' => $valign,
				'className'         => $class,
			)
		) . " -->\n<div" . self::cls( 'wp-block-columns', $valign ? 'are-vertically-aligned-' . $valign : '', $class ) . ">\n";
		foreach ( $cols as $col ) {
			$width = $col['width'] ?? '';
			$out  .= '<!-- wp:column' . self::json(
				array(
					'verticalAlignment' => $valign,
					'width'             => $width,
					'className'         => $col['class'] ?? '',
				)
			) . " -->\n<div" . self::cls( 'wp-block-column', $valign ? 'is-vertically-aligned-' . $valign : '', $col['class'] ?? '' ) . ( $width ? ' style="flex-basis:' . esc_attr( $width ) . '"' : '' ) . ">\n" . $col['inner'] . "</div>\n<!-- /wp:column -->\n";
		}
		return $out . "</div>\n<!-- /wp:columns -->\n";
	}

	/**
	 * Zelfsluitend (dynamisch) blok.
	 *
	 * @param string $name  Bloknaam.
	 * @param array  $attrs Attributen.
	 * @return string
	 */
	public static function block( $name, $attrs = array() ) {
		return '<!-- wp:' . $name . self::json( $attrs ) . " /-->\n";
	}

	/**
	 * Sectie: volle breedte, inhoud max. 84rem, inhoud bewerkbaar maar opmaak vergrendeld (contentOnly).
	 *
	 * @param string $inner  Inhoud.
	 * @param string $class  Extra class.
	 * @param string $name   Naam in de lijstweergave.
	 * @param array  $extra  Extra attributen.
	 * @return string
	 */
	public static function section( $inner, $class, $name, $extra = array() ) {
		return self::group(
			$inner,
			$extra + array(
				'tagName'      => 'section',
				'metadata'     => array( 'name' => $name ),
				'align'        => 'full',
				'className'    => trim( 'mc-section ' . $class ),
				'templateLock' => 'contentOnly',
				'layout'       => array(
					'type'        => 'constrained',
					'contentSize' => '84rem',
				),
			)
		);
	}

	/**
	 * Kop van een sectie: kicker, titel, intro en optioneel een "meer"-link.
	 *
	 * @param string      $kicker Kicker.
	 * @param string      $title  Titel.
	 * @param string      $intro  Intro.
	 * @param array|null  $more   [tekst, url].
	 * @param int         $level  Kopniveau.
	 * @return string
	 */
	public static function head( $kicker, $title, $intro = '', $more = null, $level = 2 ) {
		$text = self::kicker( $kicker ) . self::h( esc_html( $title ), $level ) . ( $intro ? self::p( esc_html( $intro ), 'mc-section-head__intro' ) : '' );
		$text = self::group( $text, array( 'className' => 'mc-section-head__text' ) );
		if ( $more ) {
			$text .= self::p( '<a href="' . esc_url( $more[1] ) . '">' . esc_html( $more[0] ) . '</a>', 'mc-more' );
		}
		return self::group(
			$text,
			array(
				'className' => 'mc-section-head',
				'layout'    => array(
					'type'              => 'flex',
					'flexWrap'          => 'wrap',
					'justifyContent'    => 'space-between',
					'verticalAlignment' => 'bottom',
				),
			)
		);
	}

	/**
	 * Hero voor subpagina's: tekst links, beeldcollage rechts.
	 *
	 * @param array $a kicker, title, lead, buttons, chips, images (1–2 sleutels), badge, accent.
	 * @return string
	 */
	public static function page_hero( $a ) {
		$text = self::kicker( $a['kicker'] ) . self::h( esc_html( $a['title'] ), 1, 'mc-page-hero__title' ) . self::p( esc_html( $a['lead'] ), 'is-style-lead' );
		if ( ! empty( $a['buttons'] ) ) {
			$text .= self::buttons( $a['buttons'], 'mc-hero__buttons' );
		}
		if ( ! empty( $a['chips'] ) ) {
			$text .= self::lst( array_map( 'esc_html', $a['chips'] ), 'is-style-chips' );
		}
		if ( ! empty( $a['extra'] ) ) {
			$text .= $a['extra'];
		}
		if ( empty( $a['images'] ) ) {
			return self::group(
				self::group( $text, array( 'className' => 'mc-page-hero__text' ) ),
				array(
					'tagName'      => 'section',
					'metadata'     => array( 'name' => 'Hero' ),
					'align'        => 'full',
					'className'    => trim( 'mc-page-hero mc-page-hero-compact ' . ( $a['accent'] ?? '' ) ),
					'templateLock' => 'contentOnly',
					'layout'       => array(
						'type'        => 'constrained',
						'contentSize' => '84rem',
					),
				)
			);
		}
		$media = megacenter_pattern_image( $a['images'][0], 'mc-collage__main' );
		if ( ! empty( $a['images'][1] ) ) {
			$media .= megacenter_pattern_image( $a['images'][1], 'mc-collage__second' );
		}
		if ( ! empty( $a['badge'] ) ) {
			$media .= self::p( $a['badge'], 'mc-collage__badge' );
		}
		return self::group(
			self::columns(
				array(
					array(
						'inner' => $text,
						'width' => '54%',
						'class' => 'mc-page-hero__text',
					),
					array(
						'inner' => self::group( $media, array( 'className' => 'mc-collage mc-collage-landscape' ) ),
						'width' => '46%',
						'class' => 'mc-page-hero__media',
					),
				),
				'mc-hero__cols'
			),
			array(
				'tagName'      => 'section',
				'metadata'     => array( 'name' => 'Hero' ),
				'align'        => 'full',
				'className'    => trim( 'mc-page-hero ' . ( $a['accent'] ?? '' ) ),
				'templateLock' => 'contentOnly',
				'layout'       => array(
					'type'        => 'constrained',
					'contentSize' => '84rem',
				),
			)
		);
	}

	/**
	 * Formuliersectie: tekst + formulier + belkaart.
	 *
	 * @param string $kicker Kicker.
	 * @param string $title  Titel.
	 * @param string $intro  Intro.
	 * @param string $type   Formuliertype.
	 * @return string
	 */
	public static function form_section( $kicker, $title, $intro, $type ) {
		$side  = self::h( 'Liever even bellen?', 3 );
		$side .= self::p( 'We denken graag met je mee over datum, aantallen en wensen.' );
		$side .= self::buttons( array( array( '0548 00 00 00', 'tel:+31548000000', 'outline', self::bind( array( 'url' => 'phone_url', 'text' => 'phone' ) ) ) ) );
		$side .= self::block( 'megacenter/opening-hours', array( 'variant' => 'today' ) );

		$main  = self::kicker( $kicker ) . self::h( esc_html( $title ) ) . self::p( esc_html( $intro ), 'is-style-lead' );
		$main .= self::block( 'megacenter/request-form', array( 'type' => $type ) );

		return self::section(
			self::columns(
				array(
					array(
						'inner' => $main,
						'width' => '64%',
						'class' => 'mc-form-section__main',
					),
					array(
						'inner' => self::group( $side, array( 'className' => 'is-style-card mc-form-section__side' ) ),
						'width' => '36%',
						'class' => 'mc-form-section__aside',
					),
				),
				'mc-form-section',
				'top'
			),
			'mc-form-wrap',
			'Aanvraagformulier'
		);
	}

	/**
	 * Arrangement-kaart.
	 *
	 * @param string   $label    Label boven de titel.
	 * @param string   $title    Titel.
	 * @param string   $price    Prijs, bijv. "€ 39,50".
	 * @param string   $unit     Eenheid, bijv. "p.p.".
	 * @param string[] $items    Inbegrepen.
	 * @param array    $button   [tekst, url].
	 * @param string   $sticker  Sticker (optioneel, maakt de kaart "uitgelicht").
	 * @param string   $accent   Accent-class.
	 * @return string
	 */
	public static function package( $label, $title, $price, $unit, $items, $button, $sticker = '', $accent = '' ) {
		$inner  = $sticker ? self::p( esc_html( $sticker ), 'is-style-sticker mc-package__sticker' ) : '';
		$inner .= self::p( esc_html( $label ), 'mc-package__label' );
		$inner .= self::h( esc_html( $title ), 3, 'mc-package__title' );
		$inner .= self::p( ( $unit ? '<em>vanaf</em> ' : '' ) . esc_html( $price ) . ( $unit ? ' <em>' . esc_html( $unit ) . '</em>' : '' ), 'mc-package__price' );
		$inner .= self::lst( array_map( 'esc_html', $items ), 'is-style-checks mc-package__list' );
		$inner .= self::buttons( array( array( $button[0], $button[1], $sticker ? '' : 'outline' ) ), 'mc-package__cta' );
		return self::group( $inner, array( 'className' => trim( 'mc-package ' . $accent . ( $sticker ? ' is-featured' : '' ) ) ) );
	}

	/**
	 * Activiteitkaart voor in een Query Loop (post-template).
	 *
	 * @return string
	 */
	public static function activity_card() {
		$inner  = self::block(
			'post-featured-image',
			array(
				'aspectRatio' => '4/3',
				'className'   => 'mc-card__media',
			)
		);
		$inner .= self::p( '', 'mc-card__badge', array( 'metadata' => self::bind( array( 'content' => 'badge' ), 'megacenter/activity' ) ) );
		$body   = self::block(
			'post-title',
			array(
				'level'     => 3,
				'isLink'    => true,
				'className' => 'mc-card__title',
			)
		);
		$body  .= self::p( '', 'mc-card__tagline', array( 'metadata' => self::bind( array( 'content' => 'megacenter_tagline' ), 'core/post-meta' ) ) );
		$body  .= self::block( 'megacenter/activity-facts' );
		$inner .= self::group( $body, array( 'className' => 'mc-card__body' ) );
		return self::group( $inner, array( 'className' => 'mc-card' ) );
	}

	/**
	 * Query Loop met activiteitkaarten.
	 *
	 * @param array  $query Query-instellingen.
	 * @param string $class Extra class.
	 * @param int    $id    Query-ID.
	 * @param int    $columns Aantal kolommen.
	 * @return string
	 */
	public static function activity_query( $query, $class = '', $id = 10, $columns = 4 ) {
		$query += array(
			'perPage'  => 8,
			'pages'    => 0,
			'offset'   => 0,
			'postType' => 'activiteit',
			'order'    => 'asc',
			'orderBy'  => 'menu_order',
			'author'   => '',
			'search'   => '',
			'exclude'  => array(),
			'sticky'   => '',
			'inherit'  => false,
		);
		$template = '<!-- wp:post-template' . self::json(
			array(
				'layout' => array(
					'type'               => 'grid',
					'columnCount'        => $columns,
					'minimumColumnWidth' => '16rem',
				),
			)
		) . " -->\n" . self::activity_card() . "<!-- /wp:post-template -->\n";
		$attrs    = array(
			'queryId'   => $id,
			'query'     => $query,
			'align'     => 'wide',
			'className' => trim( 'mc-cards ' . $class ),
		);
		return '<!-- wp:query' . self::json( $attrs ) . " -->\n<div class=\"wp-block-query alignwide " . esc_attr( trim( 'mc-cards ' . $class ) ) . "\">" . $template . "</div>\n<!-- /wp:query -->\n";
	}

	/**
	 * Template-markup voor het activiteitenoverzicht (archief of soort).
	 *
	 * @param bool $is_tax Soort-pagina.
	 * @return string
	 */
	public static function archive_template( $is_tax ) {
		$b = self::class;
		$hero  = $b::kicker( 'Activiteiten' );
		$hero .= $is_tax
			? $b::block(
				'query-title',
				array(
					'type'       => 'archive',
					'showPrefix' => false,
					'className'  => 'mc-page-hero__title',
				)
			) . $b::block( 'term-description' )
			: $b::h( 'Kies je FUN', 1, 'mc-page-hero__title' ) . $b::p( 'Van neon arena tot ballenbak: alles onder één dak. Filter op wat bij jullie past en reserveer direct.', 'is-style-lead' );
		$hero .= $b::block( 'megacenter/activity-filter' );

		$out  = $b::group(
			$b::group( $hero, array( 'className' => 'mc-page-hero__text' ) ),
			array(
				'align'     => 'full',
				'className' => 'mc-page-hero mc-page-hero-compact mc-archive-hero',
				'layout'    => array(
					'type'        => 'constrained',
					'contentSize' => '84rem',
				),
			)
		);
		$out .= $b::activity_query( array( 'inherit' => true ), 'mc-cards-archive', 13 );
		$out .= '<!-- wp:pattern {"slug":"megacenter/cta-band"} /-->' . "\n";

		return $b::group(
			$out,
			array(
				'tagName'   => 'main',
				'className' => 'mc-main mc-archive',
				'layout'    => array( 'type' => 'constrained' ),
			)
		);
	}
}
