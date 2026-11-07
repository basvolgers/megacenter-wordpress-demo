<?php
/**
 * Render: lichtkrant. Decoratief (aria-hidden): dezelfde activiteiten staan als echte links verderop.
 *
 * @package MegacenterCore
 * @var array $attributes Blok-attributen.
 */

defined( 'ABSPATH' ) || exit;

$megacenter_items = array();
if ( ! empty( $attributes['items'] ) ) {
	$megacenter_items = array_filter( array_map( 'trim', explode( ',', $attributes['items'] ) ) );
} else {
	foreach ( megacenter_get_activities() as $megacenter_activity ) {
		$megacenter_items[] = get_the_title( $megacenter_activity );
	}
}
if ( ! $megacenter_items ) {
	return;
}
$megacenter_list = '';
foreach ( $megacenter_items as $megacenter_item ) {
	$megacenter_list .= '<li>' . esc_html( $megacenter_item ) . '<span class="mc-marquee__star">✦</span></li>';
}
?>
<div <?php echo megacenter_block_wrapper( 'mc-marquee', array( 'aria-hidden' => 'true' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="mc-marquee__track">
		<ul class="mc-marquee__list"><?php echo $megacenter_list; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- hierboven ge-escaped. ?></ul>
		<ul class="mc-marquee__list"><?php echo $megacenter_list; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></ul>
	</div>
</div>
