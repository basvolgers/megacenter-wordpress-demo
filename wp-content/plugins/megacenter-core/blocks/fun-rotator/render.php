<?php
/**
 * Render: FUN-kop met roterend woord.
 *
 * @package MegacenterCore
 * @var array    $attributes Blok-attributen.
 * @var WP_Block $block      Blok.
 */

defined( 'ABSPATH' ) || exit;

$megacenter_words = array();
if ( ! empty( $attributes['words'] ) ) {
	$megacenter_words = array_filter( array_map( 'trim', explode( ',', $attributes['words'] ) ) );
} else {
	foreach ( megacenter_get_activities() as $megacenter_activity ) {
		$megacenter_words[] = get_the_title( $megacenter_activity );
	}
}
if ( ! $megacenter_words ) {
	$megacenter_words = array( __( 'jou', 'megacenter-core' ) );
}

$megacenter_level = max( 1, min( 3, (int) ( $attributes['level'] ?? 1 ) ) );
$megacenter_tag   = 'h' . $megacenter_level;
$megacenter_last  = array_pop( $megacenter_words );
$megacenter_sr    = $megacenter_words
	/* translators: 1: opsomming, 2: laatste item */
	? sprintf( __( '%1$s en %2$s', 'megacenter-core' ), implode( ', ', $megacenter_words ), $megacenter_last )
	: $megacenter_last;
$megacenter_words[] = $megacenter_last;
?>
<<?php echo esc_attr( $megacenter_tag ); ?> <?php echo megacenter_block_wrapper( 'mc-rotator' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<span class="mc-rotator__lead"><?php echo esc_html( $attributes['prefix'] ?? '' ); ?></span>
	<span class="mc-rotator__words" data-mc-rotator aria-hidden="true">
		<?php foreach ( $megacenter_words as $megacenter_i => $megacenter_word ) : ?>
			<span class="mc-rotator__word<?php echo 0 === $megacenter_i ? ' is-active' : ''; ?>"><?php echo esc_html( $megacenter_word ); ?></span>
		<?php endforeach; ?>
	</span>
	<span class="screen-reader-text"><?php echo esc_html( $megacenter_sr ); ?></span>
</<?php echo esc_attr( $megacenter_tag ); ?>>
