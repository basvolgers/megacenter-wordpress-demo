<?php
/**
 * Lichtgewicht aanvraagformulier: nonce + honeypot + tijdval + server-side validatie.
 * Aanvragen worden opgeslagen als privé post type "aanvraag" en per e-mail gemeld.
 *
 * In productie kan dit 1-op-1 vervangen worden door een formulierplugin (bijv. Gravity Forms of
 * Fluent Forms) als de opdrachtgever koppelingen met CRM of nieuwsbrief wil.
 *
 * @package MegacenterCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Formuliertypes en hun velden.
 *
 * @return array<string,array<string,mixed>>
 */
function megacenter_form_types() {
	return array(
		'kinderfeest' => array(
			'label'  => __( 'Kinderfeestje', 'megacenter-core' ),
			'submit' => __( 'Vraag het feestje aan', 'megacenter-core' ),
			'fields' => array( 'name', 'email', 'phone', 'date', 'count', 'age', 'package', 'message', 'consent' ),
		),
		'groep'       => array(
			'label'  => __( 'Groepsuitje', 'megacenter-core' ),
			'submit' => __( 'Vraag een voorstel aan', 'megacenter-core' ),
			'fields' => array( 'name', 'email', 'phone', 'date', 'count', 'occasion', 'message', 'consent' ),
		),
		'zakelijk'    => array(
			'label'  => __( 'Zakelijk', 'megacenter-core' ),
			'submit' => __( 'Ontvang een voorstel op maat', 'megacenter-core' ),
			'fields' => array( 'company', 'name', 'email', 'phone', 'date', 'count', 'message', 'consent' ),
		),
		'contact'     => array(
			'label'  => __( 'Contact', 'megacenter-core' ),
			'submit' => __( 'Verstuur bericht', 'megacenter-core' ),
			'fields' => array( 'name', 'email', 'phone', 'message', 'consent' ),
		),
	);
}

/**
 * Definitie van alle mogelijke velden.
 *
 * @param string $type Formuliertype.
 * @return array<string,array<string,mixed>>
 */
function megacenter_form_fields( $type ) {
	$count_label = 'kinderfeest' === $type ? __( 'Aantal kinderen', 'megacenter-core' ) : __( 'Aantal personen', 'megacenter-core' );
	return array(
		'company'  => array( 'label' => __( 'Bedrijfsnaam', 'megacenter-core' ), 'type' => 'text', 'required' => true, 'autocomplete' => 'organization' ),
		'name'     => array( 'label' => __( 'Je naam', 'megacenter-core' ), 'type' => 'text', 'required' => true, 'autocomplete' => 'name' ),
		'email'    => array( 'label' => __( 'E-mailadres', 'megacenter-core' ), 'type' => 'email', 'required' => true, 'autocomplete' => 'email' ),
		'phone'    => array( 'label' => __( 'Telefoonnummer', 'megacenter-core' ), 'type' => 'tel', 'required' => false, 'autocomplete' => 'tel' ),
		'date'     => array( 'label' => __( 'Gewenste datum', 'megacenter-core' ), 'type' => 'date', 'required' => 'contact' !== $type ),
		'count'    => array( 'label' => $count_label, 'type' => 'number', 'required' => true, 'min' => 1, 'max' => 500 ),
		'age'      => array( 'label' => __( 'Leeftijd van de jarige', 'megacenter-core' ), 'type' => 'number', 'required' => false, 'min' => 1, 'max' => 18 ),
		'package'  => array(
			'label'    => __( 'Pakket', 'megacenter-core' ),
			'type'     => 'select',
			'required' => false,
			'options'  => array(
				''      => __( 'Weet ik nog niet', 'megacenter-core' ),
				'glow'  => __( 'Glow', 'megacenter-core' ),
				'neon'  => __( 'Neon (populair)', 'megacenter-core' ),
				'ultra' => __( 'Ultra', 'megacenter-core' ),
			),
		),
		'occasion' => array(
			'label'    => __( 'Gelegenheid', 'megacenter-core' ),
			'type'     => 'select',
			'required' => false,
			'options'  => array(
				''            => __( 'Kies…', 'megacenter-core' ),
				'vrienden'    => __( 'Vriendengroep', 'megacenter-core' ),
				'familie'     => __( 'Familie-uitje', 'megacenter-core' ),
				'vrijgezellen' => __( 'Vrijgezellenfeest', 'megacenter-core' ),
				'verjaardag'  => __( 'Verjaardag (volwassen)', 'megacenter-core' ),
				'anders'      => __( 'Iets anders', 'megacenter-core' ),
			),
		),
		'message'  => array( 'label' => 'contact' === $type ? __( 'Je bericht', 'megacenter-core' ) : __( 'Wensen of vragen', 'megacenter-core' ), 'type' => 'textarea', 'required' => 'contact' === $type ),
		'consent'  => array( 'label' => __( 'Ik ga akkoord dat mijn gegevens worden gebruikt om deze aanvraag te beantwoorden.', 'megacenter-core' ), 'type' => 'checkbox', 'required' => true ),
	);
}

/**
 * Formulier verwerken (admin-post.php).
 */
function megacenter_handle_request_form() {
	$return = isset( $_POST['mc_return'] ) ? esc_url_raw( wp_unslash( $_POST['mc_return'] ) ) : home_url( '/' );
	$return = wp_validate_redirect( $return, home_url( '/' ) );
	$anchor = '#aanvraag-formulier';

	if ( ! isset( $_POST['mc_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mc_nonce'] ) ), 'megacenter_aanvraag' ) ) {
		wp_safe_redirect( add_query_arg( 'mc_form', 'verlopen', $return ) . $anchor );
		exit;
	}

	$type  = isset( $_POST['mc_type'] ) ? sanitize_key( wp_unslash( $_POST['mc_type'] ) ) : 'contact';
	$types = megacenter_form_types();
	if ( ! isset( $types[ $type ] ) ) {
		$type = 'contact';
	}

	// Spamfilters: honeypot moet leeg zijn en het formulier moet minimaal 3 seconden open zijn geweest.
	$honeypot = isset( $_POST['mc_website'] ) ? sanitize_text_field( wp_unslash( $_POST['mc_website'] ) ) : '';
	$started  = isset( $_POST['mc_started'] ) ? absint( $_POST['mc_started'] ) : 0;
	if ( '' !== $honeypot || ( $started && ( time() - $started ) < 3 ) ) {
		// Doe alsof het gelukt is; bots krijgen geen signaal.
		wp_safe_redirect( add_query_arg( 'mc_form', 'bedankt', $return ) . $anchor );
		exit;
	}

	$defs   = megacenter_form_fields( $type );
	$values = array();
	$errors = array();
	$raw    = isset( $_POST['mc'] ) && is_array( $_POST['mc'] ) ? wp_unslash( $_POST['mc'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- per veld hieronder.

	foreach ( $types[ $type ]['fields'] as $key ) {
		$def   = $defs[ $key ];
		$value = $raw[ $key ] ?? '';
		switch ( $def['type'] ) {
			case 'email':
				$value = sanitize_email( $value );
				if ( '' !== $value && ! is_email( $value ) ) {
					$errors[ $key ] = __( 'Vul een geldig e-mailadres in, bijvoorbeeld naam@voorbeeld.nl.', 'megacenter-core' );
				}
				break;
			case 'textarea':
				$value = sanitize_textarea_field( $value );
				if ( mb_strlen( $value ) > 3000 ) {
					$errors[ $key ] = __( 'Je bericht is te lang (max. 3000 tekens).', 'megacenter-core' );
				}
				break;
			case 'number':
				$value = '' === trim( (string) $value ) ? '' : absint( $value );
				if ( '' !== $value && ( $value < $def['min'] || $value > $def['max'] ) ) {
					/* translators: 1: minimum, 2: maximum */
					$errors[ $key ] = sprintf( __( 'Vul een getal in tussen %1$d en %2$d.', 'megacenter-core' ), $def['min'], $def['max'] );
				}
				break;
			case 'date':
				$value = sanitize_text_field( $value );
				if ( '' !== $value ) {
					$date = DateTimeImmutable::createFromFormat( '!Y-m-d', $value, wp_timezone() );
					if ( ! $date ) {
						$errors[ $key ] = __( 'Kies een geldige datum.', 'megacenter-core' );
					} elseif ( $date < new DateTimeImmutable( 'today', wp_timezone() ) ) {
						$errors[ $key ] = __( 'Kies een datum vanaf vandaag.', 'megacenter-core' );
					}
				}
				break;
			case 'select':
				$value = sanitize_key( $value );
				if ( ! array_key_exists( $value, $def['options'] ) ) {
					$value = '';
				}
				break;
			case 'checkbox':
				$value = empty( $value ) ? '' : '1';
				break;
			case 'tel':
				$value = sanitize_text_field( $value );
				if ( '' !== $value && ! preg_match( '/^[0-9 +()\-]{8,20}$/', $value ) ) {
					$errors[ $key ] = __( 'Vul een geldig telefoonnummer in, bijvoorbeeld 06 12345678.', 'megacenter-core' );
				}
				break;
			default:
				$value = sanitize_text_field( $value );
				if ( mb_strlen( $value ) > 120 ) {
					$errors[ $key ] = __( 'Dit veld is te lang.', 'megacenter-core' );
				}
		}
		if ( ! empty( $def['required'] ) && ( '' === $value || null === $value ) && ! isset( $errors[ $key ] ) ) {
			$errors[ $key ] = 'checkbox' === $def['type']
				? __( 'Vink dit aan om je aanvraag te kunnen versturen.', 'megacenter-core' )
				/* translators: %s: naam van het veld */
				: sprintf( __( 'Vul "%s" in.', 'megacenter-core' ), $def['label'] );
		}
		$values[ $key ] = $value;
	}

	if ( $errors ) {
		$token = wp_generate_password( 12, false );
		set_transient(
			'megacenter_form_' . $token,
			array(
				'type'   => $type,
				'errors' => $errors,
				'values' => $values,
			),
			10 * MINUTE_IN_SECONDS
		);
		wp_safe_redirect( add_query_arg( 'mc_form', $token, $return ) . $anchor );
		exit;
	}

	$title = sprintf(
		'%s — %s%s',
		$types[ $type ]['label'],
		$values['name'] ?? '',
		! empty( $values['date'] ) ? ' — ' . wp_date( 'j M Y', strtotime( $values['date'] ) ) : ''
	);
	$post_id = wp_insert_post(
		array(
			'post_type'   => 'aanvraag',
			'post_status' => 'private',
			'post_title'  => $title,
		),
		true
	);
	if ( ! is_wp_error( $post_id ) ) {
		update_post_meta( $post_id, '_megacenter_type', $type );
		update_post_meta( $post_id, '_megacenter_status', 'nieuw' );
		update_post_meta( $post_id, '_megacenter_source', $return );
		foreach ( $values as $key => $value ) {
			update_post_meta( $post_id, '_megacenter_' . $key, $value );
		}
	}

	megacenter_send_request_mail( $type, $values, is_wp_error( $post_id ) ? 0 : $post_id );

	wp_safe_redirect( add_query_arg( 'mc_form', 'bedankt', $return ) . $anchor );
	exit;
}
add_action( 'admin_post_megacenter_aanvraag', 'megacenter_handle_request_form' );
add_action( 'admin_post_nopriv_megacenter_aanvraag', 'megacenter_handle_request_form' );

/**
 * E-mailnotificatie naar de beheerder.
 *
 * @param string $type    Type.
 * @param array  $values  Waarden.
 * @param int    $post_id Aanvraag-ID.
 */
function megacenter_send_request_mail( $type, $values, $post_id ) {
	$to    = megacenter_setting( 'notify_email' );
	$to    = $to ? $to : get_option( 'admin_email' );
	$defs  = megacenter_form_fields( $type );
	$label = megacenter_form_types()[ $type ]['label'];
	$lines = array();
	foreach ( $values as $key => $value ) {
		if ( 'consent' === $key ) {
			continue;
		}
		if ( 'select' === $defs[ $key ]['type'] ) {
			$value = $defs[ $key ]['options'][ $value ] ?? $value;
		}
		$lines[] = $defs[ $key ]['label'] . ': ' . ( '' === $value ? '–' : $value );
	}
	if ( $post_id ) {
		$lines[] = '';
		$lines[] = __( 'Bekijk in het beheer:', 'megacenter-core' ) . ' ' . admin_url( 'post.php?post=' . $post_id . '&action=edit' );
	}
	$headers = array();
	if ( ! empty( $values['email'] ) ) {
		$headers[] = 'Reply-To: ' . sanitize_text_field( $values['name'] ?? '' ) . ' <' . $values['email'] . '>';
	}
	/* translators: 1: type aanvraag, 2: naam */
	$subject = sprintf( __( 'Nieuwe aanvraag: %1$s van %2$s', 'megacenter-core' ), $label, $values['name'] ?? '' );
	wp_mail( $to, $subject, implode( "\n", $lines ), $headers );
}

/**
 * Formulier-HTML (gebruikt door het blok megacenter/request-form).
 *
 * @param array $attributes Blok-attributen.
 * @return string
 */
function megacenter_render_request_form( $attributes ) {
	$types = megacenter_form_types();
	$type  = isset( $types[ $attributes['type'] ?? '' ] ) ? $attributes['type'] : 'contact';
	$defs  = megacenter_form_fields( $type );
	$state = isset( $_GET['mc_form'] ) ? sanitize_key( wp_unslash( $_GET['mc_form'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- alleen weergave.

	$errors = array();
	$values = array();
	if ( $state && ! in_array( $state, array( 'bedankt', 'verlopen' ), true ) ) {
		$stored = get_transient( 'megacenter_form_' . $state );
		if ( is_array( $stored ) && $stored['type'] === $type ) {
			$errors = $stored['errors'];
			$values = $stored['values'];
		}
	}

	$uid = 'mc-form-' . $type;
	ob_start();

	if ( 'bedankt' === $state ) {
		?>
		<div class="mc-form__success" role="status" tabindex="-1" id="<?php echo esc_attr( $uid ); ?>-status">
			<?php echo megacenter_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<p class="mc-form__success-title"><?php esc_html_e( 'Top, je aanvraag is binnen!', 'megacenter-core' ); ?></p>
			<p><?php esc_html_e( 'We sturen je binnen één werkdag een voorstel of antwoord. Haast? Bel ons gerust.', 'megacenter-core' ); ?></p>
		</div>
		<?php
		return (string) ob_get_clean();
	}
	?>
	<form class="mc-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" novalidate>
		<?php if ( 'verlopen' === $state ) : ?>
			<div class="mc-form__errors" role="alert"><p><?php esc_html_e( 'Je sessie was verlopen. Probeer het nog een keer, je gegevens zijn niet verstuurd.', 'megacenter-core' ); ?></p></div>
		<?php elseif ( $errors ) : ?>
			<div class="mc-form__errors" role="alert" tabindex="-1" id="<?php echo esc_attr( $uid ); ?>-errors">
				<p><strong><?php esc_html_e( 'Nog even checken:', 'megacenter-core' ); ?></strong></p>
				<ul>
					<?php foreach ( $errors as $key => $message ) : ?>
						<li><a href="#<?php echo esc_attr( $uid . '-' . $key ); ?>"><?php echo esc_html( $message ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<input type="hidden" name="action" value="megacenter_aanvraag">
		<input type="hidden" name="mc_type" value="<?php echo esc_attr( $type ); ?>">
		<input type="hidden" name="mc_return" value="<?php echo esc_url( remove_query_arg( 'mc_form', get_permalink() ? get_permalink() : home_url( add_query_arg( array() ) ) ) ); ?>">
		<input type="hidden" name="mc_started" value="<?php echo esc_attr( (string) time() ); ?>">
		<?php wp_nonce_field( 'megacenter_aanvraag', 'mc_nonce', false ); ?>
		<div class="mc-form__hp" aria-hidden="true">
			<label for="<?php echo esc_attr( $uid ); ?>-website"><?php esc_html_e( 'Laat dit veld leeg', 'megacenter-core' ); ?></label>
			<input type="text" id="<?php echo esc_attr( $uid ); ?>-website" name="mc_website" value="" tabindex="-1" autocomplete="off">
		</div>

		<div class="mc-form__grid">
			<?php
			foreach ( $types[ $type ]['fields'] as $key ) :
				$def      = $defs[ $key ];
				$id       = $uid . '-' . $key;
				$value    = $values[ $key ] ?? '';
				$has_err  = isset( $errors[ $key ] );
				$required = ! empty( $def['required'] );
				$wide     = in_array( $def['type'], array( 'textarea', 'checkbox' ), true ) ? ' mc-form__field--wide' : '';
				$describe = $has_err ? ' aria-describedby="' . esc_attr( $id . '-error' ) . '" aria-invalid="true"' : '';
				?>
				<div class="mc-form__field mc-form__field--<?php echo esc_attr( $def['type'] . $wide ); ?><?php echo $has_err ? ' has-error' : ''; ?>">
					<?php if ( 'checkbox' === $def['type'] ) : ?>
						<input type="checkbox" id="<?php echo esc_attr( $id ); ?>" name="mc[<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( $value, '1' ); ?> <?php echo $required ? 'required' : ''; ?><?php echo $describe; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
						<label for="<?php echo esc_attr( $id ); ?>">
							<?php echo esc_html( $def['label'] ); ?>
							<a href="<?php echo esc_url( get_privacy_policy_url() ? get_privacy_policy_url() : home_url( '/privacyverklaring/' ) ); ?>"><?php esc_html_e( 'Lees de privacyverklaring', 'megacenter-core' ); ?></a>
						</label>
					<?php else : ?>
						<label for="<?php echo esc_attr( $id ); ?>">
							<?php echo esc_html( $def['label'] ); ?>
							<?php if ( ! $required ) : ?>
								<span class="mc-form__optional"><?php esc_html_e( '(optioneel)', 'megacenter-core' ); ?></span>
							<?php endif; ?>
						</label>
						<?php if ( 'textarea' === $def['type'] ) : ?>
							<textarea id="<?php echo esc_attr( $id ); ?>" name="mc[<?php echo esc_attr( $key ); ?>]" rows="4" <?php echo $required ? 'required' : ''; ?><?php echo $describe; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_textarea( (string) $value ); ?></textarea>
						<?php elseif ( 'select' === $def['type'] ) : ?>
							<select id="<?php echo esc_attr( $id ); ?>" name="mc[<?php echo esc_attr( $key ); ?>]"<?php echo $describe; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
								<?php foreach ( $def['options'] as $opt => $label ) : ?>
									<option value="<?php echo esc_attr( $opt ); ?>" <?php selected( (string) $value, (string) $opt ); ?>><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
						<?php else : ?>
							<input
								type="<?php echo esc_attr( $def['type'] ); ?>"
								id="<?php echo esc_attr( $id ); ?>"
								name="mc[<?php echo esc_attr( $key ); ?>]"
								value="<?php echo esc_attr( (string) $value ); ?>"
								<?php echo isset( $def['autocomplete'] ) ? 'autocomplete="' . esc_attr( $def['autocomplete'] ) . '"' : ''; ?>
								<?php echo isset( $def['min'] ) ? 'min="' . (int) $def['min'] . '" max="' . (int) $def['max'] . '" inputmode="numeric"' : ''; ?>
								<?php echo 'date' === $def['type'] ? 'min="' . esc_attr( wp_date( 'Y-m-d' ) ) . '"' : ''; ?>
								<?php echo $required ? 'required' : ''; ?><?php echo $describe; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
						<?php endif; ?>
					<?php endif; ?>
					<?php if ( $has_err ) : ?>
						<p class="mc-form__error" id="<?php echo esc_attr( $id . '-error' ); ?>"><?php echo esc_html( $errors[ $key ] ); ?></p>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="mc-form__footer">
			<div class="wp-block-button"><button type="submit" class="wp-block-button__link wp-element-button"><?php echo esc_html( ! empty( $attributes['submitLabel'] ) ? $attributes['submitLabel'] : $types[ $type ]['submit'] ); ?></button></div>
			<p class="mc-form__note"><?php esc_html_e( 'Vrijblijvend · binnen één werkdag antwoord', 'megacenter-core' ); ?></p>
		</div>
	</form>
	<?php
	return (string) ob_get_clean();
}
