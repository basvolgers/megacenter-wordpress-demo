<?php
/**
 * Render: reserveringsmodule.
 * 1. Embedcode ingevuld  -> die wordt getoond (widget/iframe van het bestaande systeem).
 * 2. Alleen een link     -> grote knop naar het reserveringssysteem.
 * 3. Niets ingevuld      -> nette placeholder die uitlegt wat hier komt + bel-CTA.
 *
 * @package MegacenterCore
 * @var array $attributes Blok-attributen.
 */

defined( 'ABSPATH' ) || exit;

$megacenter_s        = megacenter_get_settings();
// Let op: niet 'activiteit' gebruiken, dat is de query-variabele van het post type.
$megacenter_slug     = isset( $_GET['voor'] ) ? sanitize_title( wp_unslash( $_GET['voor'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- alleen weergave.
$megacenter_activity = $megacenter_slug ? get_page_by_path( $megacenter_slug, OBJECT, 'activiteit' ) : null;
$megacenter_phone    = megacenter_phone_url();
?>
<div <?php echo megacenter_block_wrapper( 'mc-reserve' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $megacenter_activity ) : ?>
		<p class="mc-reserve__selected">
			<?php echo megacenter_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php
			/* translators: %s: naam van de activiteit */
			echo esc_html( sprintf( __( 'Je reserveert voor: %s', 'megacenter-core' ), get_the_title( $megacenter_activity ) ) );
			?>
		</p>
	<?php endif; ?>

	<?php if ( '' !== trim( $megacenter_s['reserve_embed'] ) ) : ?>
		<div class="mc-reserve__embed">
			<?php
			// Bewust ongefilterd: alleen te bewerken door gebruikers met de capability unfiltered_html.
			echo $megacenter_s['reserve_embed']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
		</div>
	<?php elseif ( $megacenter_s['reserve_url'] ) : ?>
		<div class="mc-reserve__link">
			<p class="mc-reserve__title"><?php esc_html_e( 'Kies je activiteit, datum en tijd in ons reserveringssysteem.', 'megacenter-core' ); ?></p>
			<div class="wp-block-buttons">
				<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $megacenter_s['reserve_url'] ); ?>"><?php esc_html_e( 'Open het reserveringssysteem', 'megacenter-core' ); ?></a></div>
			</div>
		</div>
	<?php else : ?>
		<div class="mc-reserve__placeholder">
			<p class="mc-reserve__label"><?php esc_html_e( 'Plek voor jullie reserveringssysteem', 'megacenter-core' ); ?></p>
			<p class="mc-reserve__title"><?php esc_html_e( 'Hier boek je straks in drie klikken: activiteit, datum, klaar.', 'megacenter-core' ); ?></p>
			<ol class="mc-reserve__steps">
				<li><strong><?php esc_html_e( 'Widget of iframe', 'megacenter-core' ); ?></strong> <?php esc_html_e( 'Jullie bestaande systeem draait direct op deze pagina, in de huisstijl.', 'megacenter-core' ); ?></li>
				<li><strong><?php esc_html_e( 'Of een directe link', 'megacenter-core' ); ?></strong> <?php esc_html_e( 'Elke "Reserveer"-knop gaat naar de juiste activiteit in het systeem.', 'megacenter-core' ); ?></li>
				<li><strong><?php esc_html_e( 'Eén instelling', 'megacenter-core' ); ?></strong> <?php esc_html_e( 'Koppelen gebeurt onder Megacenter → Reserveren. Geen code nodig.', 'megacenter-core' ); ?></li>
			</ol>
			<?php if ( $megacenter_phone ) : ?>
				<div class="wp-block-buttons">
					<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $megacenter_phone, array( 'tel' ) ); ?>">
						<?php
						/* translators: %s: telefoonnummer */
						echo esc_html( sprintf( __( 'Reserveer telefonisch: %s', 'megacenter-core' ), $megacenter_s['phone'] ) );
						?>
					</a></div>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>
