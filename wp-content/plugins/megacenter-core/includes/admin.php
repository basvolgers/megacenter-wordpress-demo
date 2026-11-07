<?php
/**
 * Beheer: dashboard-widget, Gereedschap → Demo-inhoud, overzicht van aanvragen.
 *
 * @package MegacenterCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin-CSS.
 *
 * @param string $hook Huidige pagina.
 */
function megacenter_admin_assets( $hook ) {
	wp_enqueue_style( 'megacenter-core-admin', MEGACENTER_CORE_URL . 'assets/css/admin.css', array(), MEGACENTER_CORE_VERSION );
}
add_action( 'admin_enqueue_scripts', 'megacenter_admin_assets' );

/**
 * Editor: waarden voor de bindings-preview en blok-instellingen.
 */
function megacenter_editor_assets() {
	wp_add_inline_script(
		'megacenter-core-editor',
		'window.megacenterEditor = ' . wp_json_encode(
			array(
				'site'  => megacenter_editor_site_values(),
				'units' => megacenter_price_units(),
			)
		) . ';',
		'before'
	);
}
add_action( 'enqueue_block_editor_assets', 'megacenter_editor_assets' );

/**
 * Dashboard-widget "Zo pas je je website aan".
 */
function megacenter_dashboard_widget() {
	wp_add_dashboard_widget( 'megacenter_help', __( 'Zo pas je je website aan', 'megacenter-core' ), 'megacenter_render_dashboard_widget', null, null, 'normal', 'high' );
}
add_action( 'wp_dashboard_setup', 'megacenter_dashboard_widget' );

/**
 * Inhoud van de dashboard-widget.
 */
function megacenter_render_dashboard_widget() {
	$new   = megacenter_count_new_requests();
	$links = array(
		array( 'tickets-alt', __( 'Activiteiten', 'megacenter-core' ), __( 'Toevoegen, teksten, prijzen, foto\'s', 'megacenter-core' ), admin_url( 'edit.php?post_type=activiteit' ) ),
		array( 'admin-page', __( 'Pagina\'s', 'megacenter-core' ), __( 'Home, kinderfeestjes, zakelijk…', 'megacenter-core' ), admin_url( 'edit.php?post_type=page' ) ),
		array( 'email-alt', __( 'Aanvragen', 'megacenter-core' ), $new ? sprintf( /* translators: %d: aantal */ _n( '%d nieuwe aanvraag', '%d nieuwe aanvragen', $new, 'megacenter-core' ), $new ) : __( 'Alles bijgewerkt', 'megacenter-core' ), admin_url( 'edit.php?post_type=aanvraag' ) ),
		array( 'admin-generic', __( 'Instellingen', 'megacenter-core' ), __( 'Telefoon, adres, openingstijden, reserveren', 'megacenter-core' ), admin_url( 'admin.php?page=megacenter' ) ),
		array( 'menu', __( 'Menu', 'megacenter-core' ), __( 'Hoofdmenu en footer', 'megacenter-core' ), admin_url( 'site-editor.php?p=%2Fnavigation' ) ),
		array( 'art', __( 'Stijlen', 'megacenter-core' ), __( 'Kleuren, lettertypes, huisstijl', 'megacenter-core' ), admin_url( 'site-editor.php?p=%2Fstyles' ) ),
	);
	?>
	<div class="mc-dash">
		<p class="mc-dash__intro"><?php esc_html_e( 'Alles op de website pas je zelf aan, zonder code. De opmaak blijft altijd netjes: je wijzigt alleen teksten en beelden.', 'megacenter-core' ); ?></p>
		<ul class="mc-dash__grid">
			<?php foreach ( $links as list( $icon, $title, $sub, $url ) ) : ?>
				<li>
					<a href="<?php echo esc_url( $url ); ?>">
						<span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>" aria-hidden="true"></span>
						<strong><?php echo esc_html( $title ); ?></strong>
						<span><?php echo esc_html( $sub ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
		<details class="mc-dash__tips">
			<summary><?php esc_html_e( 'Een nieuwe activiteit toevoegen (1 minuut)', 'megacenter-core' ); ?></summary>
			<ol>
				<li><?php esc_html_e( 'Ga naar Activiteiten → Nieuwe activiteit.', 'megacenter-core' ); ?></li>
				<li><?php esc_html_e( 'Vul de titel, een korte intro en het hoofdbeeld in.', 'megacenter-core' ); ?></li>
				<li><?php esc_html_e( 'Vul onder "Activiteit-gegevens" leeftijd, duur, groep en prijs in en kies een accentkleur.', 'megacenter-core' ); ?></li>
				<li><?php esc_html_e( 'Publiceer. De activiteit staat meteen op de homepage, in het overzicht en in de bewegende kop.', 'megacenter-core' ); ?></li>
			</ol>
		</details>
	</div>
	<?php
}

/**
 * Aantal nieuwe aanvragen.
 *
 * @return int
 */
function megacenter_count_new_requests() {
	$ids = get_posts(
		array(
			'post_type'   => 'aanvraag',
			'post_status' => 'private',
			'numberposts' => 99,
			'fields'      => 'ids',
			'meta_key'    => '_megacenter_status', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'  => 'nieuw', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		)
	);
	return count( $ids );
}

/**
 * Teller-bolletje bij het menu "Aanvragen".
 */
function megacenter_request_menu_bubble() {
	global $menu;
	$count = megacenter_count_new_requests();
	if ( ! $count || ! is_array( $menu ) ) {
		return;
	}
	foreach ( $menu as $key => $item ) {
		if ( 'edit.php?post_type=aanvraag' === ( $item[2] ?? '' ) ) {
			$menu[ $key ][0] .= ' <span class="awaiting-mod"><span class="pending-count">' . (int) $count . '</span></span>'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}
	}
}
add_action( 'admin_menu', 'megacenter_request_menu_bubble', 99 );

/**
 * Kolommen in het aanvragen-overzicht.
 *
 * @param array $columns Kolommen.
 * @return array
 */
function megacenter_request_columns( $columns ) {
	return array(
		'cb'        => $columns['cb'] ?? '',
		'title'     => __( 'Aanvraag', 'megacenter-core' ),
		'mc_type'   => __( 'Soort', 'megacenter-core' ),
		'mc_email'  => __( 'E-mail', 'megacenter-core' ),
		'mc_count'  => __( 'Personen', 'megacenter-core' ),
		'mc_status' => __( 'Status', 'megacenter-core' ),
		'date'      => __( 'Ontvangen', 'megacenter-core' ),
	);
}
add_filter( 'manage_aanvraag_posts_columns', 'megacenter_request_columns' );

/**
 * Statussen van een aanvraag.
 *
 * @return array<string,string>
 */
function megacenter_request_statuses() {
	return array(
		'nieuw'      => __( 'Nieuw', 'megacenter-core' ),
		'behandelen' => __( 'In behandeling', 'megacenter-core' ),
		'afgerond'   => __( 'Afgerond', 'megacenter-core' ),
	);
}

/**
 * Kolominhoud.
 *
 * @param string $column  Kolom.
 * @param int    $post_id Post-ID.
 */
function megacenter_request_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'mc_type':
			$type = get_post_meta( $post_id, '_megacenter_type', true );
			echo esc_html( megacenter_form_types()[ $type ]['label'] ?? $type );
			break;
		case 'mc_email':
			$email = get_post_meta( $post_id, '_megacenter_email', true );
			if ( $email ) {
				printf( '<a href="%s">%s</a>', esc_url( 'mailto:' . $email ), esc_html( $email ) );
			}
			break;
		case 'mc_count':
			echo esc_html( (string) get_post_meta( $post_id, '_megacenter_count', true ) );
			break;
		case 'mc_status':
			$status = get_post_meta( $post_id, '_megacenter_status', true );
			printf( '<span class="mc-status-pill mc-status-pill--%1$s">%2$s</span>', esc_attr( $status ), esc_html( megacenter_request_statuses()[ $status ] ?? '' ) );
			break;
	}
}
add_action( 'manage_aanvraag_posts_custom_column', 'megacenter_request_column_content', 10, 2 );

/**
 * Meta box met de details van een aanvraag.
 */
function megacenter_request_meta_box() {
	add_meta_box( 'megacenter-request', __( 'Details van de aanvraag', 'megacenter-core' ), 'megacenter_render_request_meta_box', 'aanvraag', 'normal', 'high' );
}
add_action( 'add_meta_boxes_aanvraag', 'megacenter_request_meta_box' );

/**
 * Details tonen + status wijzigen.
 *
 * @param WP_Post $post Post.
 */
function megacenter_render_request_meta_box( $post ) {
	$type   = get_post_meta( $post->ID, '_megacenter_type', true );
	$defs   = megacenter_form_fields( $type ? $type : 'contact' );
	$status = get_post_meta( $post->ID, '_megacenter_status', true );
	wp_nonce_field( 'megacenter_request_status', 'megacenter_request_nonce' );
	?>
	<table class="widefat striped mc-request-table">
		<tbody>
			<tr><th scope="row"><?php esc_html_e( 'Soort', 'megacenter-core' ); ?></th><td><?php echo esc_html( megacenter_form_types()[ $type ]['label'] ?? $type ); ?></td></tr>
			<?php
			foreach ( $defs as $key => $def ) :
				if ( 'consent' === $key || ! metadata_exists( 'post', $post->ID, '_megacenter_' . $key ) ) {
					continue;
				}
				$value = (string) get_post_meta( $post->ID, '_megacenter_' . $key, true );
				if ( 'select' === $def['type'] ) {
					$value = $def['options'][ $value ] ?? $value;
				}
				?>
				<tr>
					<th scope="row"><?php echo esc_html( $def['label'] ); ?></th>
					<td>
						<?php
						if ( 'email' === $key && $value ) {
							printf( '<a href="%s">%s</a>', esc_url( 'mailto:' . $value ), esc_html( $value ) );
						} elseif ( 'phone' === $key && $value ) {
							printf( '<a href="%s">%s</a>', esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $value ), array( 'tel' ) ), esc_html( $value ) );
						} else {
							echo nl2br( esc_html( '' === $value ? '–' : $value ) );
						}
						?>
					</td>
				</tr>
			<?php endforeach; ?>
			<tr><th scope="row"><?php esc_html_e( 'Verstuurd vanaf', 'megacenter-core' ); ?></th><td><?php echo esc_html( (string) get_post_meta( $post->ID, '_megacenter_source', true ) ); ?></td></tr>
		</tbody>
	</table>
	<p class="mc-request-status">
		<label for="mc-request-status"><strong><?php esc_html_e( 'Status', 'megacenter-core' ); ?></strong></label>
		<select id="mc-request-status" name="megacenter_request_status">
			<?php foreach ( megacenter_request_statuses() as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $status, $key ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<?php
}

/**
 * Status opslaan.
 *
 * @param int $post_id Post-ID.
 */
function megacenter_save_request_status( $post_id ) {
	if ( ! isset( $_POST['megacenter_request_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['megacenter_request_nonce'] ) ), 'megacenter_request_status' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$status = isset( $_POST['megacenter_request_status'] ) ? sanitize_key( wp_unslash( $_POST['megacenter_request_status'] ) ) : 'nieuw';
	if ( array_key_exists( $status, megacenter_request_statuses() ) ) {
		update_post_meta( $post_id, '_megacenter_status', $status );
	}
}
add_action( 'save_post_aanvraag', 'megacenter_save_request_status' );

/**
 * Gereedschap → Demo-inhoud.
 */
function megacenter_tools_page() {
	add_management_page( __( 'Demo-inhoud', 'megacenter-core' ), __( 'Demo-inhoud', 'megacenter-core' ), 'manage_options', 'megacenter-demo', 'megacenter_render_tools_page' );
}
add_action( 'admin_menu', 'megacenter_tools_page' );

/**
 * Pagina Gereedschap → Demo-inhoud.
 */
function megacenter_render_tools_page() {
	$done = isset( $_GET['mc_done'] ) ? sanitize_key( wp_unslash( $_GET['mc_done'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Demo-inhoud', 'megacenter-core' ); ?></h1>
		<?php if ( $done ) : ?>
			<div class="notice notice-success"><p><?php echo 'reset' === $done ? esc_html__( 'Demo-inhoud is opnieuw opgebouwd.', 'megacenter-core' ) : esc_html__( 'Demo-inhoud is bijgewerkt.', 'megacenter-core' ); ?></p></div>
		<?php endif; ?>
		<p><?php esc_html_e( 'Maakt de voorbeeldpagina\'s, 8 activiteiten, beelden, menu\'s en instellingen aan. Veilig om vaker te draaien: bestaande demo-items worden bijgewerkt, niet verdubbeld. Jouw eigen inhoud wordt nooit aangeraakt.', 'megacenter-core' ); ?></p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="megacenter_seed">
			<?php wp_nonce_field( 'megacenter_seed' ); ?>
			<p>
				<button type="submit" class="button button-primary" name="mode" value="seed"><?php esc_html_e( 'Demo-inhoud aanmaken / bijwerken', 'megacenter-core' ); ?></button>
				<button type="submit" class="button" name="mode" value="reset" onclick="return confirm('<?php echo esc_js( __( 'Alle demo-items verwijderen en opnieuw aanmaken?', 'megacenter-core' ) ); ?>');"><?php esc_html_e( 'Reset: verwijderen en opnieuw aanmaken', 'megacenter-core' ); ?></button>
			</p>
		</form>
		<p class="description"><?php esc_html_e( 'Ook via WP-CLI: wp megacenter seed [--reset]', 'megacenter-core' ); ?></p>
	</div>
	<?php
}

/**
 * Seeder starten vanuit het beheer.
 */
function megacenter_handle_seed_request() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Je hebt hier geen rechten voor.', 'megacenter-core' ), 403 );
	}
	check_admin_referer( 'megacenter_seed' );
	$reset = isset( $_POST['mode'] ) && 'reset' === sanitize_key( wp_unslash( $_POST['mode'] ) );
	if ( $reset ) {
		megacenter_demo_reset();
	}
	megacenter_demo_seed();
	wp_safe_redirect( admin_url( 'tools.php?page=megacenter-demo&mc_done=' . ( $reset ? 'reset' : 'seed' ) ) );
	exit;
}
add_action( 'admin_post_megacenter_seed', 'megacenter_handle_seed_request' );
