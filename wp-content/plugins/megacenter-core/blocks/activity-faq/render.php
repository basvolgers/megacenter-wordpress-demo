<?php
/**
 * Render: veelgestelde vragen van een activiteit (toetsenbord-bedienbaar via <details>).
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
$megacenter_faq = megacenter_activity_data( $megacenter_id )['faq'];
if ( ! $megacenter_faq ) {
	return;
}
?>
<div <?php echo megacenter_block_wrapper( 'mc-faq' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php foreach ( $megacenter_faq as $megacenter_item ) : ?>
		<details class="wp-block-details mc-faq__item">
			<summary><?php echo esc_html( $megacenter_item['q'] ); ?></summary>
			<p><?php echo nl2br( esc_html( $megacenter_item['a'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
		</details>
	<?php endforeach; ?>
</div>
