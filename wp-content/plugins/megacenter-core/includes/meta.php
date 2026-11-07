<?php
/**
 * Activiteit-velden: registratie (REST + Block Bindings) en een nette meta box.
 *
 * @package MegacenterCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registreert alle activiteit-velden.
 */
function megacenter_register_meta() {
	$auth = static fn( $allowed, $meta_key, $post_id ) => current_user_can( 'edit_post', $post_id );

	$scalar = array(
		'tagline'     => array( 'string', 'sanitize_text_field', __( 'Korte, krachtige ondertitel', 'megacenter-core' ) ),
		'badge'       => array( 'string', 'sanitize_text_field', __( 'Sticker-label, bijv. NIEUW', 'megacenter-core' ) ),
		'age_min'     => array( 'integer', 'absint', __( 'Leeftijd vanaf', 'megacenter-core' ) ),
		'age_max'     => array( 'integer', 'absint', __( 'Leeftijd tot (optioneel)', 'megacenter-core' ) ),
		'duration'    => array( 'integer', 'absint', __( 'Duur in minuten (0 = vrij spelen)', 'megacenter-core' ) ),
		'group_min'   => array( 'integer', 'absint', __( 'Groepsgrootte minimaal', 'megacenter-core' ) ),
		'group_max'   => array( 'integer', 'absint', __( 'Groepsgrootte maximaal', 'megacenter-core' ) ),
		'price_from'  => array( 'number', 'megacenter_sanitize_price', __( 'Prijs vanaf (in euro)', 'megacenter-core' ) ),
		'price_unit'  => array( 'string', 'megacenter_sanitize_price_unit', __( 'Prijseenheid', 'megacenter-core' ) ),
		'accent'      => array( 'string', 'megacenter_sanitize_accent', __( 'Accentkleur', 'megacenter-core' ) ),
		'reserve_url' => array( 'string', 'esc_url_raw', __( 'Eigen reserveringslink (optioneel)', 'megacenter-core' ) ),
	);

	foreach ( $scalar as $key => list( $type, $sanitize, $label ) ) {
		register_post_meta(
			'activiteit',
			'megacenter_' . $key,
			array(
				'type'              => $type,
				'description'       => $label,
				'label'             => $label,
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => $sanitize,
				'auth_callback'     => $auth,
				'default'           => 'string' === $type ? '' : ( 'number' === $type ? 0.0 : 0 ),
			)
		);
	}

	register_post_meta(
		'activiteit',
		'megacenter_highlights',
		array(
			'type'              => 'array',
			'description'       => __( 'Highlights (3–5 korte punten)', 'megacenter-core' ),
			'single'            => true,
			'sanitize_callback' => 'megacenter_sanitize_highlights',
			'auth_callback'     => $auth,
			'show_in_rest'      => array(
				'schema' => array(
					'type'  => 'array',
					'items' => array( 'type' => 'string' ),
				),
			),
		)
	);

	register_post_meta(
		'activiteit',
		'megacenter_faq',
		array(
			'type'              => 'array',
			'description'       => __( 'Veelgestelde vragen', 'megacenter-core' ),
			'single'            => true,
			'sanitize_callback' => 'megacenter_sanitize_faq',
			'auth_callback'     => $auth,
			'show_in_rest'      => array(
				'schema' => array(
					'type'  => 'array',
					'items' => array(
						'type'       => 'object',
						'properties' => array(
							'q' => array( 'type' => 'string' ),
							'a' => array( 'type' => 'string' ),
						),
					),
				),
			),
		)
	);
}
add_action( 'init', 'megacenter_register_meta' );

/**
 * Prijs opschonen: "17,50" en "17.50" worden 17.5.
 *
 * @param mixed $value Waarde.
 * @return float
 */
function megacenter_sanitize_price( $value ) {
	$value = str_replace( array( '€', ' ' ), '', (string) $value );
	$value = str_replace( ',', '.', $value );
	return round( max( 0, (float) $value ), 2 );
}

/**
 * Prijseenheid opschonen.
 *
 * @param mixed $value Waarde.
 * @return string
 */
function megacenter_sanitize_price_unit( $value ) {
	return array_key_exists( (string) $value, megacenter_price_units() ) ? (string) $value : 'pp';
}

/**
 * Accentkleur opschonen.
 *
 * @param mixed $value Waarde.
 * @return string
 */
function megacenter_sanitize_accent( $value ) {
	return array_key_exists( (string) $value, megacenter_accents() ) ? (string) $value : 'accent-1';
}

/**
 * Highlights opschonen (array van korte teksten, max 8).
 *
 * @param mixed $value Waarde.
 * @return string[]
 */
function megacenter_sanitize_highlights( $value ) {
	if ( is_string( $value ) ) {
		$value = preg_split( '/\r\n|\r|\n/', $value );
	}
	$value = array_map( 'sanitize_text_field', (array) $value );
	return array_slice( array_values( array_filter( $value, 'strlen' ) ), 0, 8 );
}

/**
 * FAQ opschonen (array van ['q' => ..., 'a' => ...]).
 *
 * @param mixed $value Waarde.
 * @return array<int,array{q:string,a:string}>
 */
function megacenter_sanitize_faq( $value ) {
	$clean = array();
	foreach ( (array) $value as $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}
		$q = sanitize_text_field( $item['q'] ?? '' );
		$a = sanitize_textarea_field( $item['a'] ?? '' );
		if ( '' !== $q && '' !== $a ) {
			$clean[] = array(
				'q' => $q,
				'a' => $a,
			);
		}
	}
	return array_slice( $clean, 0, 6 );
}

/**
 * Meta box toevoegen.
 */
function megacenter_add_activity_meta_box() {
	add_meta_box(
		'megacenter-activity',
		__( 'Activiteit-gegevens', 'megacenter-core' ),
		'megacenter_render_activity_meta_box',
		'activiteit',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'megacenter_add_activity_meta_box' );

/**
 * Meta box tonen.
 *
 * @param WP_Post $post Post.
 */
function megacenter_render_activity_meta_box( $post ) {
	wp_nonce_field( 'megacenter_activity_meta', 'megacenter_activity_nonce' );
	$m = static fn( $key ) => get_post_meta( $post->ID, 'megacenter_' . $key, true );

	$faq        = (array) $m( 'faq' );
	$highlights = implode( "\n", (array) $m( 'highlights' ) );
	$accent     = megacenter_activity_accent( $post->ID );
	$unit       = (string) $m( 'price_unit' );
	$price      = $m( 'price_from' );
	?>
	<div class="mc-metabox">
		<p class="mc-metabox__intro"><?php esc_html_e( 'Deze gegevens verschijnen automatisch op de activiteitkaart, de detailpagina en in de zoekresultaten. Lege velden worden netjes overgeslagen.', 'megacenter-core' ); ?></p>

		<div class="mc-metabox__grid">
			<p class="mc-field mc-field--wide">
				<label for="mc-tagline"><?php esc_html_e( 'Tagline', 'megacenter-core' ); ?></label>
				<input type="text" id="mc-tagline" name="megacenter[tagline]" value="<?php echo esc_attr( $m( 'tagline' ) ); ?>" maxlength="90" placeholder="<?php esc_attr_e( 'Bijv. Sluip, scoor en win in de neon arena.', 'megacenter-core' ); ?>">
			</p>
			<p class="mc-field">
				<label for="mc-badge"><?php esc_html_e( 'Sticker-label', 'megacenter-core' ); ?></label>
				<input type="text" id="mc-badge" name="megacenter[badge]" value="<?php echo esc_attr( $m( 'badge' ) ); ?>" maxlength="24" placeholder="<?php esc_attr_e( 'Bijv. NIEUW', 'megacenter-core' ); ?>">
			</p>
			<p class="mc-field">
				<label for="mc-age-min"><?php esc_html_e( 'Leeftijd vanaf', 'megacenter-core' ); ?></label>
				<input type="number" id="mc-age-min" name="megacenter[age_min]" value="<?php echo esc_attr( $m( 'age_min' ) ); ?>" min="0" max="99">
			</p>
			<p class="mc-field">
				<label for="mc-age-max"><?php esc_html_e( 'Leeftijd tot (optioneel)', 'megacenter-core' ); ?></label>
				<input type="number" id="mc-age-max" name="megacenter[age_max]" value="<?php echo esc_attr( $m( 'age_max' ) ?: '' ); ?>" min="0" max="99">
			</p>
			<p class="mc-field">
				<label for="mc-duration"><?php esc_html_e( 'Duur (minuten)', 'megacenter-core' ); ?></label>
				<input type="number" id="mc-duration" name="megacenter[duration]" value="<?php echo esc_attr( $m( 'duration' ) ); ?>" min="0" max="600" aria-describedby="mc-duration-help">
				<span class="description" id="mc-duration-help"><?php esc_html_e( '0 = vrij spelen', 'megacenter-core' ); ?></span>
			</p>
			<p class="mc-field">
				<label for="mc-group-min"><?php esc_html_e( 'Groep minimaal', 'megacenter-core' ); ?></label>
				<input type="number" id="mc-group-min" name="megacenter[group_min]" value="<?php echo esc_attr( $m( 'group_min' ) ?: '' ); ?>" min="0" max="500">
			</p>
			<p class="mc-field">
				<label for="mc-group-max"><?php esc_html_e( 'Groep maximaal', 'megacenter-core' ); ?></label>
				<input type="number" id="mc-group-max" name="megacenter[group_max]" value="<?php echo esc_attr( $m( 'group_max' ) ?: '' ); ?>" min="0" max="500">
			</p>
			<p class="mc-field">
				<label for="mc-price"><?php esc_html_e( 'Prijs vanaf (€)', 'megacenter-core' ); ?></label>
				<input type="text" inputmode="decimal" id="mc-price" name="megacenter[price_from]" value="<?php echo esc_attr( '' !== $price && $price ? str_replace( '.', ',', (string) $price ) : '' ); ?>" placeholder="17,50">
			</p>
			<p class="mc-field">
				<label for="mc-unit"><?php esc_html_e( 'Prijseenheid', 'megacenter-core' ); ?></label>
				<select id="mc-unit" name="megacenter[price_unit]">
					<?php foreach ( megacenter_price_units() as $key => $label ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $unit ? $unit : 'pp', $key ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>
			<fieldset class="mc-field mc-field--wide mc-swatches">
				<legend><?php esc_html_e( 'Accentkleur', 'megacenter-core' ); ?></legend>
				<?php foreach ( megacenter_accents() as $key => $label ) : ?>
					<label class="mc-swatch mc-swatch--<?php echo esc_attr( $key ); ?>">
						<input type="radio" name="megacenter[accent]" value="<?php echo esc_attr( $key ); ?>" <?php checked( $accent, $key ); ?>>
						<span><?php echo esc_html( $label ); ?></span>
					</label>
				<?php endforeach; ?>
			</fieldset>
			<p class="mc-field mc-field--wide">
				<label for="mc-highlights"><?php esc_html_e( 'Highlights (één per regel, 3 tot 5)', 'megacenter-core' ); ?></label>
				<textarea id="mc-highlights" name="megacenter[highlights]" rows="5"><?php echo esc_textarea( $highlights ); ?></textarea>
			</p>
			<p class="mc-field mc-field--wide">
				<label for="mc-reserve"><?php esc_html_e( 'Eigen reserveringslink (optioneel)', 'megacenter-core' ); ?></label>
				<input type="url" id="mc-reserve" name="megacenter[reserve_url]" value="<?php echo esc_attr( $m( 'reserve_url' ) ); ?>" placeholder="https://" aria-describedby="mc-reserve-help">
				<span class="description" id="mc-reserve-help"><?php esc_html_e( 'Leeg laten = de algemene reserveringspagina, met deze activiteit alvast geselecteerd.', 'megacenter-core' ); ?></span>
			</p>
		</div>

		<h3 class="mc-metabox__sub"><?php esc_html_e( 'Veelgestelde vragen', 'megacenter-core' ); ?></h3>
		<div class="mc-metabox__faq">
			<?php for ( $i = 0; $i < 3; $i++ ) : ?>
				<div class="mc-faq-item">
					<p class="mc-field">
						<label for="mc-faq-q-<?php echo (int) $i; ?>">
							<?php
							/* translators: %d: nummer van de vraag */
							echo esc_html( sprintf( __( 'Vraag %d', 'megacenter-core' ), $i + 1 ) );
							?>
						</label>
						<input type="text" id="mc-faq-q-<?php echo (int) $i; ?>" name="megacenter[faq][<?php echo (int) $i; ?>][q]" value="<?php echo esc_attr( $faq[ $i ]['q'] ?? '' ); ?>">
					</p>
					<p class="mc-field">
						<label for="mc-faq-a-<?php echo (int) $i; ?>"><?php esc_html_e( 'Antwoord', 'megacenter-core' ); ?></label>
						<textarea id="mc-faq-a-<?php echo (int) $i; ?>" name="megacenter[faq][<?php echo (int) $i; ?>][a]" rows="2"><?php echo esc_textarea( $faq[ $i ]['a'] ?? '' ); ?></textarea>
					</p>
				</div>
			<?php endfor; ?>
		</div>
	</div>
	<?php
}

/**
 * Meta box opslaan.
 *
 * @param int $post_id Post-ID.
 */
function megacenter_save_activity_meta( $post_id ) {
	if ( ! isset( $_POST['megacenter_activity_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['megacenter_activity_nonce'] ) ), 'megacenter_activity_meta' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	// Sanitizen gebeurt per veld via de geregistreerde sanitize_callback.
	$data = isset( $_POST['megacenter'] ) && is_array( $_POST['megacenter'] ) ? wp_unslash( $_POST['megacenter'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

	$keys = array( 'tagline', 'badge', 'age_min', 'age_max', 'duration', 'group_min', 'group_max', 'price_from', 'price_unit', 'accent', 'reserve_url', 'highlights', 'faq' );
	foreach ( $keys as $key ) {
		$value = $data[ $key ] ?? '';
		if ( '' === $value || array() === $value ) {
			delete_post_meta( $post_id, 'megacenter_' . $key );
			continue;
		}
		update_post_meta( $post_id, 'megacenter_' . $key, $value );
	}
}
add_action( 'save_post_activiteit', 'megacenter_save_activity_meta' );
