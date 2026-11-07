<?php
/**
 * Render: filterknoppen. Zonder JS zijn het gewone links naar de soort-pagina's;
 * met JS filtert het overzicht direct op de pagina.
 *
 * @package MegacenterCore
 * @var array $attributes Blok-attributen.
 */

defined( 'ABSPATH' ) || exit;

$megacenter_terms = get_terms(
	array(
		'taxonomy'   => 'activiteit_type',
		'hide_empty' => true,
		'orderby'    => 'term_id',
	)
);
if ( is_wp_error( $megacenter_terms ) || ! $megacenter_terms ) {
	return;
}
$megacenter_current = is_tax( 'activiteit_type' ) ? get_queried_object_id() : 0;
$megacenter_total   = (int) wp_count_posts( 'activiteit' )->publish;
?>
<nav <?php echo megacenter_block_wrapper( 'mc-filter', array_filter( array( 'aria-label' => __( 'Filter activiteiten', 'megacenter-core' ), 'data-mc-enhance' => $megacenter_current ? '' : '1' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<ul class="mc-filter__list">
		<li>
			<a class="mc-filter__chip" href="<?php echo esc_url( get_post_type_archive_link( 'activiteit' ) ); ?>" data-filter="all" <?php echo $megacenter_current ? '' : 'aria-current="page"'; ?>>
				<?php esc_html_e( 'Alles', 'megacenter-core' ); ?> <span class="mc-filter__count"><?php echo (int) $megacenter_total; ?></span>
			</a>
		</li>
		<?php foreach ( $megacenter_terms as $megacenter_term ) : ?>
			<li>
				<a class="mc-filter__chip" href="<?php echo esc_url( get_term_link( $megacenter_term ) ); ?>" data-filter="<?php echo esc_attr( 'activiteit_type-' . $megacenter_term->slug ); ?>" <?php echo $megacenter_current === $megacenter_term->term_id ? 'aria-current="page"' : ''; ?>>
					<?php echo esc_html( $megacenter_term->name ); ?> <span class="mc-filter__count"><?php echo (int) $megacenter_term->count; ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
	<p class="screen-reader-text" aria-live="polite" data-mc-filter-status></p>
</nav>
