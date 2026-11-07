<?php
/**
 * Instellingenpagina "Megacenter" (Settings API): één bron van waarheid voor
 * naam, contact, adres, openingstijden, reserveren, socials en de demobalk.
 *
 * @package MegacenterCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registreert de instelling.
 */
function megacenter_register_settings() {
	register_setting(
		'megacenter_settings_group',
		'megacenter_settings',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'megacenter_sanitize_settings',
			'default'           => megacenter_settings_defaults(),
			'show_in_rest'      => false,
		)
	);
}
add_action( 'admin_init', 'megacenter_register_settings' );
add_action( 'rest_api_init', 'megacenter_register_settings' );

/**
 * Menu-item.
 */
function megacenter_settings_menu() {
	add_menu_page(
		__( 'Megacenter-instellingen', 'megacenter-core' ),
		__( 'Megacenter', 'megacenter-core' ),
		'manage_options',
		'megacenter',
		'megacenter_render_settings_page',
		'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill="black" d="M2 16V4h3l5 6 5-6h3v12h-3V9l-5 6-5-6v7z"/></svg>' ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		3
	);
}
add_action( 'admin_menu', 'megacenter_settings_menu' );

/**
 * Instellingen opschonen.
 *
 * @param mixed $input Invoer.
 * @return array<string,mixed>
 */
function megacenter_sanitize_settings( $input ) {
	$input    = is_array( $input ) ? $input : array();
	$current  = megacenter_get_settings();
	$defaults = megacenter_settings_defaults();
	$out      = array();

	$text = array( 'tagline', 'phone', 'street', 'postcode', 'city', 'demo_bar_text' );
	foreach ( $text as $key ) {
		$out[ $key ] = sanitize_text_field( $input[ $key ] ?? $defaults[ $key ] );
	}
	foreach ( array( 'route_text', 'hours_note', 'seo_description' ) as $key ) {
		$out[ $key ] = sanitize_textarea_field( $input[ $key ] ?? $defaults[ $key ] );
	}
	$out['email']        = sanitize_email( $input['email'] ?? '' );
	$out['notify_email'] = sanitize_email( $input['notify_email'] ?? '' );
	$out['reserve_page'] = absint( $input['reserve_page'] ?? 0 );
	$out['reserve_url']  = esc_url_raw( $input['reserve_url'] ?? '' );
	foreach ( array( 'instagram', 'facebook', 'tiktok', 'linkedin', 'youtube' ) as $key ) {
		$out[ $key ] = esc_url_raw( trim( (string) ( $input[ $key ] ?? '' ) ) );
	}
	$out['demo_bar'] = empty( $input['demo_bar'] ) ? 0 : 1;

	$out['hours'] = array();
	foreach ( array_keys( megacenter_days() ) as $day ) {
		$open  = sanitize_text_field( $input['hours'][ $day ]['open'] ?? '' );
		$close = sanitize_text_field( $input['hours'][ $day ]['close'] ?? '' );
		$valid = static fn( $t ) => (bool) preg_match( '/^([01]\d|2[0-3]):[0-5]\d$/', $t );
		$out['hours'][ $day ] = array(
			'open'  => $valid( $open ) ? $open : '',
			'close' => $valid( $close ) ? $close : '',
		);
	}

	// Embedcode van het reserveringssysteem: alleen voor gebruikers die ongefilterde HTML mogen plaatsen.
	if ( current_user_can( 'unfiltered_html' ) ) {
		$out['reserve_embed'] = trim( (string) ( $input['reserve_embed'] ?? '' ) );
	} else {
		$out['reserve_embed'] = $current['reserve_embed'];
	}

	// De werktitel is de sitetitel van WordPress: één plek, overal gebruikt.
	if ( isset( $input['site_name'] ) ) {
		$name = sanitize_text_field( $input['site_name'] );
		if ( '' !== $name ) {
			update_option( 'blogname', $name );
		}
	}

	return $out;
}

/**
 * Pagina tonen.
 */
function megacenter_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$s     = megacenter_get_settings();
	$name  = 'megacenter_settings';
	$field = static function ( $key, $label, $type = 'text', $help = '', $attrs = '' ) use ( $s, $name ) {
		$id = 'mc-set-' . $key;
		printf(
			'<tr><th scope="row"><label for="%1$s">%2$s</label></th><td><input type="%3$s" id="%1$s" name="%4$s[%5$s]" value="%6$s" class="regular-text" %7$s %8$s>%9$s</td></tr>',
			esc_attr( $id ),
			esc_html( $label ),
			esc_attr( $type ),
			esc_attr( $name ),
			esc_attr( $key ),
			esc_attr( $s[ $key ] ?? '' ),
			$help ? 'aria-describedby="' . esc_attr( $id ) . '-help"' : '',
			$attrs, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- vaste, interne attributen.
			$help ? '<p class="description" id="' . esc_attr( $id ) . '-help">' . esc_html( $help ) . '</p>' : ''
		);
	};
	$area  = static function ( $key, $label, $help = '' ) use ( $s, $name ) {
		$id = 'mc-set-' . $key;
		printf(
			'<tr><th scope="row"><label for="%1$s">%2$s</label></th><td><textarea id="%1$s" name="%3$s[%4$s]" rows="3" class="large-text">%5$s</textarea>%6$s</td></tr>',
			esc_attr( $id ),
			esc_html( $label ),
			esc_attr( $name ),
			esc_attr( $key ),
			esc_textarea( $s[ $key ] ?? '' ),
			$help ? '<p class="description">' . esc_html( $help ) . '</p>' : ''
		);
	};
	?>
	<div class="wrap mc-settings">
		<h1><?php esc_html_e( 'Megacenter-instellingen', 'megacenter-core' ); ?></h1>
		<p class="mc-settings__intro"><?php esc_html_e( 'Alles wat op meerdere plekken op de website staat, pas je hier één keer aan: in de header, footer, contactpagina, zoekmachines en het reserveringsblok.', 'megacenter-core' ); ?></p>
		<form method="post" action="options.php">
			<?php settings_fields( 'megacenter_settings_group' ); ?>

			<h2><?php esc_html_e( 'Algemeen', 'megacenter-core' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="mc-set-site_name"><?php esc_html_e( 'Naam (werktitel)', 'megacenter-core' ); ?></label></th>
					<td><input type="text" id="mc-set-site_name" name="megacenter_settings[site_name]" value="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" class="regular-text" aria-describedby="mc-set-site_name-help">
						<p class="description" id="mc-set-site_name-help"><?php esc_html_e( 'Wordt overal gebruikt: titel in de browser, footer, zoekmachines. Gelijk aan de sitetitel van WordPress.', 'megacenter-core' ); ?></p></td>
				</tr>
				<?php
				$field( 'tagline', __( 'Slogan', 'megacenter-core' ) );
				$area( 'seo_description', __( 'Standaard omschrijving voor Google', 'megacenter-core' ), __( 'Max. ±155 tekens. Pagina\'s met een eigen samenvatting gebruiken die.', 'megacenter-core' ) );
				?>
			</table>

			<h2><?php esc_html_e( 'Contact & adres', 'megacenter-core' ); ?></h2>
			<table class="form-table" role="presentation">
				<?php
				$field( 'phone', __( 'Telefoon', 'megacenter-core' ), 'tel' );
				$field( 'email', __( 'E-mail (zichtbaar op de site)', 'megacenter-core' ), 'email' );
				$field( 'notify_email', __( 'E-mail voor aanvragen', 'megacenter-core' ), 'email', __( 'Hier komen nieuwe aanvragen binnen. Leeg = het beheerdersadres.', 'megacenter-core' ) );
				$field( 'street', __( 'Straat + huisnummer', 'megacenter-core' ) );
				$field( 'postcode', __( 'Postcode', 'megacenter-core' ) );
				$field( 'city', __( 'Plaats', 'megacenter-core' ) );
				$area( 'route_text', __( 'Bereikbaarheid & parkeren', 'megacenter-core' ) );
				?>
			</table>

			<h2><?php esc_html_e( 'Openingstijden', 'megacenter-core' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Laat beide velden leeg voor "gesloten". Notatie 13:00.', 'megacenter-core' ); ?></p>
			<table class="form-table mc-hours-table" role="presentation">
				<?php foreach ( megacenter_days() as $day => $label ) : ?>
					<tr>
						<th scope="row"><?php echo esc_html( $label ); ?></th>
						<td>
							<label class="screen-reader-text" for="mc-open-<?php echo esc_attr( $day ); ?>"><?php echo esc_html( $label . ' ' . __( 'open vanaf', 'megacenter-core' ) ); ?></label>
							<input type="time" id="mc-open-<?php echo esc_attr( $day ); ?>" name="megacenter_settings[hours][<?php echo esc_attr( $day ); ?>][open]" value="<?php echo esc_attr( $s['hours'][ $day ]['open'] ?? '' ); ?>">
							<span aria-hidden="true">–</span>
							<label class="screen-reader-text" for="mc-close-<?php echo esc_attr( $day ); ?>"><?php echo esc_html( $label . ' ' . __( 'open tot', 'megacenter-core' ) ); ?></label>
							<input type="time" id="mc-close-<?php echo esc_attr( $day ); ?>" name="megacenter_settings[hours][<?php echo esc_attr( $day ); ?>][close]" value="<?php echo esc_attr( $s['hours'][ $day ]['close'] ?? '' ); ?>">
						</td>
					</tr>
				<?php endforeach; ?>
				<?php $area( 'hours_note', __( 'Toelichting', 'megacenter-core' ) ); ?>
			</table>

			<h2><?php esc_html_e( 'Reserveren', 'megacenter-core' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="mc-set-reserve_page"><?php esc_html_e( 'Reserveringspagina', 'megacenter-core' ); ?></label></th>
					<td>
						<?php
						wp_dropdown_pages(
							array(
								'name'              => 'megacenter_settings[reserve_page]',
								'id'                => 'mc-set-reserve_page',
								'selected'          => (int) $s['reserve_page'],
								'show_option_none'  => esc_html__( '— Automatisch (/reserveren/) —', 'megacenter-core' ),
								'option_none_value' => '0',
							)
						);
						?>
						<p class="description"><?php esc_html_e( 'Alle "Reserveer"-knoppen komen hier uit, tenzij een activiteit een eigen link heeft.', 'megacenter-core' ); ?></p>
					</td>
				</tr>
				<?php $field( 'reserve_url', __( 'Link naar reserveringssysteem', 'megacenter-core' ), 'url', __( 'Bijv. de boekingspagina van jullie huidige systeem. Wordt als grote knop getoond in het reserveringsblok.', 'megacenter-core' ) ); ?>
				<tr>
					<th scope="row"><label for="mc-set-reserve_embed"><?php esc_html_e( 'Embedcode (widget of iframe)', 'megacenter-core' ); ?></label></th>
					<td>
						<?php if ( current_user_can( 'unfiltered_html' ) ) : ?>
							<textarea id="mc-set-reserve_embed" name="megacenter_settings[reserve_embed]" rows="5" class="large-text code" aria-describedby="mc-set-reserve_embed-help"><?php echo esc_textarea( $s['reserve_embed'] ); ?></textarea>
							<p class="description" id="mc-set-reserve_embed-help"><?php esc_html_e( 'Plak hier de code die jullie reserveringssysteem aanlevert. Heeft voorrang op de link hierboven.', 'megacenter-core' ); ?></p>
						<?php else : ?>
							<p class="description"><?php esc_html_e( 'Alleen een beheerder met volledige rechten kan de embedcode aanpassen.', 'megacenter-core' ); ?></p>
						<?php endif; ?>
					</td>
				</tr>
			</table>

			<h2><?php esc_html_e( 'Sociale media', 'megacenter-core' ); ?></h2>
			<table class="form-table" role="presentation">
				<?php
				$field( 'instagram', 'Instagram', 'text' );
				$field( 'facebook', 'Facebook', 'text' );
				$field( 'tiktok', 'TikTok', 'text' );
				$field( 'linkedin', 'LinkedIn', 'text' );
				$field( 'youtube', 'YouTube', 'text', __( 'Leeg = icoon verbergen.', 'megacenter-core' ) );
				?>
			</table>

			<h2><?php esc_html_e( 'Demo', 'megacenter-core' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Demobalk', 'megacenter-core' ); ?></th>
					<td><label><input type="checkbox" name="megacenter_settings[demo_bar]" value="1" <?php checked( (int) $s['demo_bar'], 1 ); ?>> <?php esc_html_e( 'Toon de demomelding bovenaan de website', 'megacenter-core' ); ?></label></td>
				</tr>
				<?php $field( 'demo_bar_text', __( 'Tekst demobalk', 'megacenter-core' ) ); ?>
			</table>

			<?php submit_button( __( 'Instellingen opslaan', 'megacenter-core' ) ); ?>
		</form>
	</div>
	<?php
}
