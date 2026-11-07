<?php
/**
 * Render: highlights van een activiteit.
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
$megacenter_items = megacenter_activity_data( $megacenter_id )['highlights'];
if ( ! $megacenter_items ) {
	return;
}
?>
<ol <?php echo megacenter_block_wrapper( 'mc-highlights' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php foreach ( $megacenter_items as $megacenter_item ) : ?>
		<li class="mc-highlights__item"><?php echo esc_html( $megacenter_item ); ?></li>
	<?php endforeach; ?>
</ol>
