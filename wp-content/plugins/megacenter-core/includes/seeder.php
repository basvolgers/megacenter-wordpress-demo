<?php
/**
 * Demo-seeder: maakt idempotent pagina's, activiteiten, beelden, menu's en instellingen aan.
 * Alles krijgt meta _megacenter_demo (+ unieke _megacenter_demo_key), zodat bijwerken en resetten veilig is.
 *
 * Starten: blueprint (runPHP), Gereedschap → Demo-inhoud, of `wp megacenter seed [--reset]`.
 *
 * @package MegacenterCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Zoekt een demo-item op sleutel.
 *
 * @param string $post_type Post type.
 * @param string $key       Unieke sleutel.
 * @return int
 */
function megacenter_demo_find( $post_type, $key ) {
	$ids = get_posts(
		array(
			'post_type'        => $post_type,
			'post_status'      => 'any',
			'numberposts'      => 1,
			'fields'           => 'ids',
			'meta_key'         => '_megacenter_demo_key', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'       => $key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'suppress_filters' => true,
		)
	);
	return $ids ? (int) $ids[0] : 0;
}

/**
 * Maakt of werkt een demo-post bij.
 *
 * @param string $key  Unieke sleutel.
 * @param array  $args wp_insert_post-argumenten.
 * @return int
 */
function megacenter_demo_upsert( $key, $args ) {
	$existing = megacenter_demo_find( $args['post_type'], $key );
	if ( $existing ) {
		$args['ID'] = $existing;
	}
	$id = wp_insert_post( wp_slash( $args ), true );
	if ( is_wp_error( $id ) ) {
		return 0;
	}
	update_post_meta( $id, '_megacenter_demo', 1 );
	update_post_meta( $id, '_megacenter_demo_key', $key );
	return (int) $id;
}

/**
 * Demo-beelden importeren zonder zware beeldbewerking: de WebP-bestanden en hun kleinere varianten
 * zijn al gemaakt door tools/images.mjs; de seeder kopieert ze en schrijft de metadata (srcset werkt).
 *
 * @return array<string,int> Sleutel => attachment-ID.
 */
function megacenter_demo_import_media() {
	$dir      = MEGACENTER_CORE_DIR . 'demo-content/images/';
	$manifest = json_decode( (string) file_get_contents( $dir . 'manifest.json' ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	if ( ! is_array( $manifest ) ) {
		return array();
	}
	$manifest = array_column( $manifest, null, 'key' );

	// Optionele lokale map met eigen beeld van de opdrachtgever (niet in git). Vervangt beelden met dezelfde sleutel.
	$private_dir = MEGACENTER_CORE_DIR . 'demo-content/images-private/';
	if ( file_exists( $private_dir . 'manifest.json' ) ) {
		$private = json_decode( (string) file_get_contents( $private_dir . 'manifest.json' ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		foreach ( is_array( $private ) ? $private : array() as $item ) {
			$manifest[ $item['key'] ] = array( 'dir' => $private_dir ) + $item;
		}
	}

	$uploads = wp_upload_dir();
	$target  = trailingslashit( $uploads['basedir'] ) . 'megacenter-demo/';
	wp_mkdir_p( $target );

	$map = array();
	foreach ( $manifest as $item ) {
		$key      = 'media-' . $item['key'];
		$existing = megacenter_demo_find( 'attachment', $key );
		$source   = $item['dir'] ?? $dir;
		$files    = array_merge( array( $item['file'] ), wp_list_pluck( $item['sizes'], 'file' ) );
		foreach ( $files as $file ) {
			if ( ! file_exists( $target . $file ) && file_exists( $source . $file ) ) {
				copy( $source . $file, $target . $file );
			}
		}
		$args = array(
			'post_type'      => 'attachment',
			'post_mime_type' => 'image/webp',
			'post_title'     => ucfirst( str_replace( '-', ' ', $item['key'] ) ),
			// Eigen slug-prefix: voorkomt dat een beeld de URL van een pagina "inpikt" (bijv. /zakelijk/).
			'post_name'      => 'mc-demo-' . $item['key'],
			'post_status'    => 'inherit',
			'post_content'   => '',
			'post_excerpt'   => '',
			'guid'           => trailingslashit( $uploads['baseurl'] ) . 'megacenter-demo/' . $item['file'],
		);
		if ( $existing ) {
			$args['ID'] = $existing;
		}
		$id = wp_insert_attachment( $args, $target . $item['file'], 0, true );
		if ( is_wp_error( $id ) ) {
			continue;
		}
		$sizes = array();
		foreach ( $item['sizes'] as $size ) {
			$sizes[ 'mc-' . $size['width'] ] = array(
				'file'      => $size['file'],
				'width'     => (int) $size['width'],
				'height'    => (int) $size['height'],
				'mime-type' => 'image/webp',
				'filesize'  => (int) $size['filesize'],
			);
		}
		wp_update_attachment_metadata(
			$id,
			array(
				'width'      => (int) $item['width'],
				'height'     => (int) $item['height'],
				'file'       => 'megacenter-demo/' . $item['file'],
				'filesize'   => (int) filesize( $target . $item['file'] ),
				'sizes'      => $sizes,
				'image_meta' => array( 'credit' => $item['credit'] ),
			)
		);
		update_post_meta( $id, '_wp_attachment_image_alt', $item['alt'] );
		update_post_meta( $id, '_megacenter_demo', 1 );
		update_post_meta( $id, '_megacenter_demo_key', $key );
		update_post_meta( $id, '_megacenter_credit', $item['credit'] );
		if ( 'og-default' === $item['key'] ) {
			update_post_meta( $id, '_megacenter_demo_key', 'og-default' );
		}
		$map[ $item['key'] ] = (int) $id;
	}
	return $map;
}

/**
 * Soorten (filters) aanmaken.
 *
 * @return array<string,int> Slug => term-ID.
 */
function megacenter_demo_terms() {
	$terms = array(
		'actie'        => array( 'Actie', 'Adrenaline, tempo en een flinke portie spanning.' ),
		'samen-spelen' => array( 'Samen spelen', 'Activiteiten waarbij je samen meer bereikt dan alleen.' ),
		'competitie'   => array( 'Competitie', 'Scorebord aan: wie gaat er met de eer vandoor?' ),
		'voor-kids'    => array( 'Voor kids', 'Speciaal leuk voor de jongste bezoekers.' ),
	);
	$map   = array();
	foreach ( $terms as $slug => list( $name, $desc ) ) {
		$term = get_term_by( 'slug', $slug, 'activiteit_type' );
		if ( $term ) {
			$map[ $slug ] = (int) $term->term_id;
			continue;
		}
		$res = wp_insert_term(
			$name,
			'activiteit_type',
			array(
				'slug'        => $slug,
				'description' => $desc,
			)
		);
		if ( ! is_wp_error( $res ) ) {
			add_term_meta( $res['term_id'], '_megacenter_demo', 1, true );
			$map[ $slug ] = (int) $res['term_id'];
		}
	}
	return $map;
}

/**
 * Block-markup voor een afbeelding uit de demo-media.
 *
 * @param array<string,int> $media Media-map.
 * @param string            $key   Beeld-sleutel.
 * @return string
 */
function megacenter_demo_image_block( $media, $key ) {
	if ( empty( $media[ $key ] ) ) {
		return '';
	}
	$id  = $media[ $key ];
	$url = wp_get_attachment_url( $id );
	$alt = get_post_meta( $id, '_wp_attachment_image_alt', true );
	return sprintf(
		"<!-- wp:image {\"id\":%1\$d,\"sizeSlug\":\"full\",\"linkDestination\":\"none\"} -->\n<figure class=\"wp-block-image size-full\"><img src=\"%2\$s\" alt=\"%3\$s\" class=\"wp-image-%1\$d\"/></figure>\n<!-- /wp:image -->",
		$id,
		esc_url( $url ),
		esc_attr( $alt )
	);
}

/**
 * Inhoud (block-markup) van een demo-activiteit.
 *
 * @param array             $a     Activiteit-data.
 * @param array<string,int> $media Media-map.
 * @return string
 */
function megacenter_demo_activity_content( $a, $media ) {
	$out   = array();
	$out[] = "<!-- wp:paragraph {\"className\":\"is-style-lead\"} -->\n<p class=\"is-style-lead\">" . esc_html( $a['lead'] ) . "</p>\n<!-- /wp:paragraph -->";
	foreach ( $a['paragraphs'] as $p ) {
		$out[] = "<!-- wp:paragraph -->\n<p>" . esc_html( $p ) . "</p>\n<!-- /wp:paragraph -->";
	}
	$out[] = "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">Zo werkt het</h2>\n<!-- /wp:heading -->";
	$items = '';
	foreach ( $a['steps'] as list( $title, $text ) ) {
		$items .= "<!-- wp:list-item -->\n<li><strong>" . esc_html( $title ) . '</strong> ' . esc_html( $text ) . "</li>\n<!-- /wp:list-item -->";
	}
	$out[] = "<!-- wp:list {\"className\":\"is-style-steps\"} -->\n<ul class=\"wp-block-list is-style-steps\">" . $items . "</ul>\n<!-- /wp:list -->";

	$gallery = array_filter( array_map( static fn( $k ) => megacenter_demo_image_block( $media, $k ), $a['gallery'] ) );
	if ( $gallery ) {
		$out[] = "<!-- wp:gallery {\"columns\":" . count( $gallery ) . ",\"linkTo\":\"none\",\"align\":\"wide\",\"className\":\"mc-gallery\"} -->\n<figure class=\"wp-block-gallery alignwide has-nested-images columns-" . count( $gallery ) . " is-cropped mc-gallery\">" . implode( "\n\n", $gallery ) . "</figure>\n<!-- /wp:gallery -->";
	}
	return implode( "\n\n", $out );
}

/**
 * Patroon-inhoud ophalen en geneste patroon-verwijzingen uitklappen tot echte blokken.
 *
 * @param string $slug  Patroonnaam.
 * @param int    $depth Recursiediepte.
 * @return string
 */
function megacenter_demo_pattern_content( $slug, $depth = 0 ) {
	$registry = WP_Block_Patterns_Registry::get_instance();
	if ( ! $registry->is_registered( $slug ) && function_exists( '_register_theme_block_patterns' ) ) {
		_register_theme_block_patterns();
	}
	$pattern = $registry->get_registered( $slug );
	if ( ! $pattern || $depth > 4 ) {
		return '';
	}
	return (string) preg_replace_callback(
		'#<!-- wp:pattern \{"slug":"([^"]+)"\} /-->#',
		static fn( $m ) => megacenter_demo_pattern_content( $m[1], $depth + 1 ),
		$pattern['content']
	);
}

/**
 * Pagina's (inhoud komt uit de paginapatronen van het thema).
 *
 * @return array<string,array<string,string>>
 */
function megacenter_demo_pages() {
	return array(
		'home'              => array( 'title' => 'Home', 'pattern' => 'megacenter/page-home', 'template' => '', 'excerpt' => '' ),
		'kinderfeestjes'    => array( 'title' => 'Kinderfeestjes', 'pattern' => 'megacenter/page-kinderfeestjes', 'template' => 'page-landing', 'excerpt' => 'Kinderfeestjes met lasergame, glowgolf, bowling of Kids Jungle. Inclusief eten, drinken en een feesthost. Vanaf € 17,50 per kind.' ),
		'groepsuitjes'      => array( 'title' => 'Groepsuitjes', 'pattern' => 'megacenter/page-groepsuitjes', 'template' => 'page-landing', 'excerpt' => 'Groepsuitje met vrienden, familie of vrijgezellen: combineer activiteiten met eten en drinken. Vraag vrijblijvend een voorstel aan.' ),
		'zakelijk'          => array( 'title' => 'Zakelijk', 'pattern' => 'megacenter/page-zakelijk', 'template' => 'page-landing', 'excerpt' => 'Bedrijfsuitje, teambuilding, personeelsfeest of vergaderen met een activiteit. Alles onder één dak, vlak bij de A1.' ),
		'eten-drinken'      => array( 'title' => 'Eten & drinken', 'pattern' => 'megacenter/page-eten-drinken', 'template' => 'page-landing', 'excerpt' => 'Burgers, pizza\'s, sharing en cocktails. Eet tussendoor, na afloop of als arrangement met je activiteit.' ),
		'contact'           => array( 'title' => 'Contact & route', 'pattern' => 'megacenter/page-contact', 'template' => 'page-landing', 'excerpt' => 'Openingstijden, adres, route en parkeren. Stel je vraag via het formulier of bel ons.' ),
		'reserveren'        => array( 'title' => 'Reserveren', 'pattern' => 'megacenter/page-reserveren', 'template' => 'page-landing', 'excerpt' => 'Reserveer je activiteit online of bel ons. Met een groep vanaf 12 personen? Vraag een voorstel op maat aan.' ),
		'privacyverklaring' => array( 'title' => 'Privacyverklaring', 'pattern' => 'megacenter/page-privacy', 'template' => '', 'excerpt' => 'Hoe wij omgaan met je gegevens (concepttekst).' ),
		'cookieverklaring'  => array( 'title' => 'Cookieverklaring', 'pattern' => 'megacenter/page-cookies', 'template' => '', 'excerpt' => 'Deze website gebruikt geen tracking-cookies (concepttekst).' ),
	);
}

/**
 * Navigatiemenu-markup.
 *
 * @param array<int,array{0:string,1:string,2?:int,3?:string}> $items Label, URL, optioneel pagina-ID, optioneel class.
 * @return string
 */
function megacenter_demo_nav_markup( $items ) {
	$out = '';
	foreach ( $items as $item ) {
		$attrs = array(
			'label'          => $item[0],
			'url'            => $item[1],
			'kind'           => empty( $item[2] ) ? 'custom' : 'post-type',
			'isTopLevelLink' => true,
		);
		if ( ! empty( $item[2] ) ) {
			$attrs['type'] = 'page';
			$attrs['id']   = $item[2];
		}
		if ( ! empty( $item[3] ) ) {
			$attrs['className'] = $item[3];
		}
		$out .= '<!-- wp:navigation-link ' . wp_json_encode( $attrs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . " /-->\n";
	}
	return $out;
}

/**
 * Alles aanmaken of bijwerken.
 *
 * @return array<string,int> Telling per soort.
 */
function megacenter_demo_seed() {
	if ( function_exists( 'set_time_limit' ) ) {
		set_time_limit( 300 ); // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged
	}
	// De seeder schrijft alleen eigen, vertrouwde block-markup: kses tijdelijk uit (zoals importers doen).
	kses_remove_filters();

	// Permalinks eerst, zodat alle links die hieronder worden opgeslagen al "mooi" zijn.
	global $wp_rewrite;
	if ( ! get_option( 'permalink_structure' ) ) {
		$wp_rewrite->set_permalink_structure( '/%postname%/' );
	}
	megacenter_register_post_types();

	// 1. Algemene instellingen.
	update_option( 'blogname', 'Megacenter Rijssen' );
	update_option( 'blogdescription', 'FUN begint hier.' );
	update_option( 'timezone_string', 'Europe/Amsterdam' );
	update_option( 'date_format', 'j F Y' );
	update_option( 'time_format', 'H:i' );
	update_option( 'start_of_week', 1 );
	update_option( 'blog_public', 0 );
	update_option( 'default_comment_status', 'closed' );
	if ( ! get_option( 'megacenter_settings' ) ) {
		update_option( 'megacenter_settings', megacenter_settings_defaults() );
	}

	// 2. Beelden en soorten.
	$media = megacenter_demo_import_media();
	$terms = megacenter_demo_terms();

	// 3. Activiteiten.
	$activities = require MEGACENTER_CORE_DIR . 'demo-content/activities.php';
	foreach ( $activities as $a ) {
		$id = megacenter_demo_upsert(
			'activity-' . $a['slug'],
			array(
				'post_type'    => 'activiteit',
				'post_status'  => 'publish',
				'post_title'   => $a['title'],
				'post_name'    => $a['slug'],
				'post_excerpt' => $a['excerpt'],
				'post_content' => megacenter_demo_activity_content( $a, $media ),
				'menu_order'   => $a['order'],
			)
		);
		if ( ! $id ) {
			continue;
		}
		foreach ( $a['meta'] as $key => $value ) {
			if ( '' === $value || 0 === $value || array() === $value ) {
				delete_post_meta( $id, 'megacenter_' . $key );
			} else {
				update_post_meta( $id, 'megacenter_' . $key, $value );
			}
		}
		if ( ! empty( $media[ $a['image'] ] ) ) {
			set_post_thumbnail( $id, $media[ $a['image'] ] );
		}
		wp_set_object_terms( $id, array_values( array_intersect_key( $terms, array_flip( $a['types'] ) ) ), 'activiteit_type' );
	}

	// 4. Pagina's (inhoud uit de paginapatronen van het actieve thema).
	$pages = array();
	foreach ( megacenter_demo_pages() as $slug => $page ) {
		$content = megacenter_demo_pattern_content( $page['pattern'] );
		if ( '' === $content ) {
			$content = "<!-- wp:paragraph -->\n<p>" . esc_html__( 'Activeer het Megacenter-thema en draai de seeder opnieuw voor de volledige inhoud.', 'megacenter-core' ) . "</p>\n<!-- /wp:paragraph -->";
		}
		$id = megacenter_demo_upsert(
			'page-' . $slug,
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => $page['title'],
				'post_name'    => $slug,
				'post_excerpt' => $page['excerpt'],
				'post_content' => $content,
			)
		);
		if ( $id ) {
			update_post_meta( $id, '_wp_page_template', $page['template'] );
			$pages[ $slug ] = $id;
		}
	}

	// 5. Leespagina's en instellingen die naar pagina's verwijzen.
	if ( ! empty( $pages['home'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $pages['home'] );
	}
	if ( ! empty( $pages['privacyverklaring'] ) ) {
		update_option( 'wp_page_for_privacy_policy', $pages['privacyverklaring'] );
	}
	$settings                 = megacenter_get_settings();
	$settings['reserve_page'] = $pages['reserveren'] ?? 0;
	update_option( 'megacenter_settings', $settings );

	// 6. Menu's (wp_navigation). Het hoofdmenu als laatste, zodat het ook de standaard-fallback is.
	$archive = get_post_type_archive_link( 'activiteit' );
	$link    = static fn( $slug ) => isset( $pages[ $slug ] ) ? get_permalink( $pages[ $slug ] ) : home_url( '/' . $slug . '/' );
	$navs    = array(
		'legal'   => array(
			'Juridisch',
			array(
				array( 'Privacyverklaring', $link( 'privacyverklaring' ), $pages['privacyverklaring'] ?? 0 ),
				array( 'Cookieverklaring', $link( 'cookieverklaring' ), $pages['cookieverklaring'] ?? 0 ),
			),
		),
		'footer'  => array(
			'Footer',
			array(
				array( 'Alle activiteiten', $archive ),
				array( 'Kinderfeestjes', $link( 'kinderfeestjes' ), $pages['kinderfeestjes'] ?? 0 ),
				array( 'Groepsuitjes', $link( 'groepsuitjes' ), $pages['groepsuitjes'] ?? 0 ),
				array( 'Zakelijk', $link( 'zakelijk' ), $pages['zakelijk'] ?? 0 ),
				array( 'Eten & drinken', $link( 'eten-drinken' ), $pages['eten-drinken'] ?? 0 ),
				array( 'Reserveren', $link( 'reserveren' ), $pages['reserveren'] ?? 0 ),
				array( 'Contact & route', $link( 'contact' ), $pages['contact'] ?? 0 ),
			),
		),
		'primary' => array(
			'Hoofdmenu',
			array(
				array( 'Activiteiten', $archive ),
				array( 'Kinderfeestjes', $link( 'kinderfeestjes' ), $pages['kinderfeestjes'] ?? 0 ),
				array( 'Groepsuitjes', $link( 'groepsuitjes' ), $pages['groepsuitjes'] ?? 0 ),
				array( 'Zakelijk', $link( 'zakelijk' ), $pages['zakelijk'] ?? 0 ),
				array( 'Eten & drinken', $link( 'eten-drinken' ), $pages['eten-drinken'] ?? 0 ),
				array( 'Contact', $link( 'contact' ), $pages['contact'] ?? 0 ),
				array( 'Reserveer', $link( 'reserveren' ), $pages['reserveren'] ?? 0, 'mc-nav-cta' ),
			),
		),
	);
	foreach ( $navs as $key => list( $title, $items ) ) {
		$nav_id = megacenter_demo_upsert(
			'nav-' . $key,
			array(
				'post_type'    => 'wp_navigation',
				'post_status'  => 'publish',
				'post_title'   => $title,
				'post_content' => megacenter_demo_nav_markup( $items ),
			)
		);
		if ( $nav_id ) {
			update_option( 'megacenter_nav_' . $key, $nav_id );
		}
	}

	// 7. Editor-welkomstpopups uit voor beheerders: de opdrachtgever kan meteen aan de slag in de demo.
	foreach ( get_users( array( 'role' => 'administrator', 'fields' => 'ID' ) ) as $user_id ) {
		$key   = $GLOBALS['wpdb']->get_blog_prefix() . 'persisted_preferences';
		$prefs = (array) get_user_meta( $user_id, $key, true );
		$prefs['core/edit-post'] = array_merge( (array) ( $prefs['core/edit-post'] ?? array() ), array( 'welcomeGuide' => false, 'welcomeGuideTemplate' => false, 'metaBoxesMainIsOpen' => true ) );
		$prefs['core/edit-site'] = array_merge( (array) ( $prefs['core/edit-site'] ?? array() ), array( 'welcomeGuide' => false, 'welcomeGuideStyles' => false, 'welcomeGuidePage' => false, 'welcomeGuideTemplate' => false ) );
		$prefs['_modified']      = gmdate( 'c' );
		update_user_meta( $user_id, $key, $prefs );
	}

	flush_rewrite_rules( false );
	update_option( 'megacenter_demo_seeded', time() );
	kses_init();

	return array(
		'media'      => count( $media ),
		'activities' => count( $activities ),
		'pages'      => count( $pages ),
	);
}

/**
 * Alle demo-items verwijderen (eigen inhoud blijft staan).
 *
 * @return int Aantal verwijderde items.
 */
function megacenter_demo_reset() {
	$count = 0;
	$ids   = get_posts(
		array(
			'post_type'        => array( 'page', 'activiteit', 'attachment', 'wp_navigation' ),
			'post_status'      => 'any',
			'numberposts'      => 500,
			'fields'           => 'ids',
			'meta_key'         => '_megacenter_demo', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'suppress_filters' => true,
		)
	);
	foreach ( $ids as $id ) {
		if ( 'attachment' === get_post_type( $id ) ) {
			wp_delete_attachment( $id, true );
		} else {
			wp_delete_post( $id, true );
		}
		++$count;
	}
	$terms = get_terms(
		array(
			'taxonomy'   => 'activiteit_type',
			'hide_empty' => false,
			'meta_key'   => '_megacenter_demo', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		)
	);
	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			wp_delete_term( $term->term_id, 'activiteit_type' );
			++$count;
		}
	}
	foreach ( array( 'megacenter_nav_primary', 'megacenter_nav_footer', 'megacenter_nav_legal', 'megacenter_demo_seeded' ) as $option ) {
		delete_option( $option );
	}
	return $count;
}
