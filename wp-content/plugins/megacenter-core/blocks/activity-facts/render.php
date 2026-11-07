<?php
/**
 * Render: kerngegevens van een activiteit.
 *
 * @package MegacenterCore
 * @var array    $attributes Blok-attributen.
 * @var WP_Block $block      Blok.
 */

defined( 'ABSPATH' ) || exit;

$megacenter_id = megacenter_block_activity_id( $block );
if ( ! $megacenter_id ) {
	return;
}
$megacenter_d       = megacenter_activity_data( $megacenter_id );
$megacenter_variant = 'chips' === ( $attributes['variant'] ?? 'card' ) ? 'chips' : 'card';

if ( 'chips' === $megacenter_variant ) :
	$megacenter_rows = array(
		array( 'age', __( 'Leeftijd', 'megacenter-core' ), $megacenter_d['age'] ),
		array( 'clock', __( 'Duur', 'megacenter-core' ), $megacenter_d['duration'] ),
		array( 'group', __( 'Groep', 'megacenter-core' ), $megacenter_d['group'] ),
		array( 'ticket', __( 'Prijs', 'megacenter-core' ), $megacenter_d['price_short'] ),
	);
	?>
	<dl <?php echo megacenter_block_wrapper( 'mc-facts mc-facts--chips' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<?php foreach ( $megacenter_rows as list( $megacenter_icon, $megacenter_label, $megacenter_value ) ) : ?>
			<?php if ( $megacenter_value ) : ?>
				<div class="mc-facts__item">
					<?php echo megacenter_icon( $megacenter_icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- vaste SVG. ?>
					<dt><?php echo esc_html( $megacenter_label ); ?></dt>
					<dd><?php echo esc_html( $megacenter_value ); ?></dd>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>
	</dl>
<?php else : ?>
	<div <?php echo megacenter_block_wrapper( 'mc-facts mc-facts--card' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<ul class="mc-facts__list">
			<li><?php echo megacenter_icon( 'age' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span class="screen-reader-text"><?php esc_html_e( 'Leeftijd:', 'megacenter-core' ); ?> </span><?php echo esc_html( $megacenter_d['age'] ); ?></li>
			<li><?php echo megacenter_icon( 'clock' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span class="screen-reader-text"><?php esc_html_e( 'Duur:', 'megacenter-core' ); ?> </span><?php echo esc_html( $megacenter_d['duration'] ); ?></li>
			<li><?php echo megacenter_icon( 'group' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span class="screen-reader-text"><?php esc_html_e( 'Groep:', 'megacenter-core' ); ?> </span><?php echo esc_html( $megacenter_d['group'] ); ?></li>
		</ul>
		<?php if ( $megacenter_d['price_short'] ) : ?>
			<p class="mc-facts__price"><?php echo esc_html( $megacenter_d['price_short'] ); ?></p>
		<?php endif; ?>
	</div>
<?php endif; ?>
