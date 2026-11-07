<?php
/**
 * Render: openingstijden uit de instellingen.
 * Varianten: table (volledig, vandaag uitgelicht), compact (dagen gegroepeerd), today (status).
 *
 * @package MegacenterCore
 * @var array $attributes Blok-attributen.
 */

defined( 'ABSPATH' ) || exit;

$megacenter_s       = megacenter_get_settings();
$megacenter_days    = megacenter_days();
$megacenter_variant = $attributes['variant'] ?? 'table';
$megacenter_today   = strtolower( wp_date( 'D' ) );
$megacenter_fmt     = static function ( $day ) use ( $megacenter_s ) {
	$h = $megacenter_s['hours'][ $day ] ?? array();
	if ( empty( $h['open'] ) || empty( $h['close'] ) ) {
		return __( 'Gesloten', 'megacenter-core' );
	}
	return $h['open'] . ' – ' . $h['close'];
};

if ( 'today' === $megacenter_variant ) {
	$megacenter_h   = $megacenter_s['hours'][ $megacenter_today ] ?? array();
	$megacenter_now = wp_date( 'H:i' );
	if ( ! empty( $megacenter_h['open'] ) && $megacenter_now >= $megacenter_h['open'] && $megacenter_now < $megacenter_h['close'] ) {
		/* translators: %s: sluitingstijd */
		$megacenter_text  = sprintf( __( 'Nu open · tot %s', 'megacenter-core' ), $megacenter_h['close'] );
		$megacenter_state = 'is-open';
	} elseif ( ! empty( $megacenter_h['open'] ) && $megacenter_now < $megacenter_h['open'] ) {
		/* translators: %s: openingstijd */
		$megacenter_text  = sprintf( __( 'Vandaag open vanaf %s', 'megacenter-core' ), $megacenter_h['open'] );
		$megacenter_state = 'is-later';
	} else {
		$megacenter_text  = __( 'Nu gesloten', 'megacenter-core' );
		$megacenter_state = 'is-closed';
	}
	printf(
		'<p %1$s><span class="mc-status__dot" aria-hidden="true"></span>%2$s</p>',
		megacenter_block_wrapper( 'mc-status ' . $megacenter_state ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		esc_html( $megacenter_text )
	);
	return;
}

if ( 'compact' === $megacenter_variant ) {
	$megacenter_short  = array(
		'mon' => __( 'Ma', 'megacenter-core' ),
		'tue' => __( 'Di', 'megacenter-core' ),
		'wed' => __( 'Wo', 'megacenter-core' ),
		'thu' => __( 'Do', 'megacenter-core' ),
		'fri' => __( 'Vr', 'megacenter-core' ),
		'sat' => __( 'Za', 'megacenter-core' ),
		'sun' => __( 'Zo', 'megacenter-core' ),
	);
	$megacenter_groups = array();
	foreach ( array_keys( $megacenter_days ) as $megacenter_day ) {
		$megacenter_val  = $megacenter_fmt( $megacenter_day );
		$megacenter_last = count( $megacenter_groups ) - 1;
		if ( $megacenter_last >= 0 && $megacenter_groups[ $megacenter_last ]['val'] === $megacenter_val ) {
			$megacenter_groups[ $megacenter_last ]['to'] = $megacenter_day;
		} else {
			$megacenter_groups[] = array(
				'from' => $megacenter_day,
				'to'   => $megacenter_day,
				'val'  => $megacenter_val,
			);
		}
	}
	?>
	<dl <?php echo megacenter_block_wrapper( 'mc-hours mc-hours--compact' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<?php foreach ( $megacenter_groups as $megacenter_g ) : ?>
			<div class="mc-hours__row">
				<dt><?php echo esc_html( $megacenter_short[ $megacenter_g['from'] ] . ( $megacenter_g['from'] !== $megacenter_g['to'] ? '–' . $megacenter_short[ $megacenter_g['to'] ] : '' ) ); ?></dt>
				<dd><?php echo esc_html( $megacenter_g['val'] ); ?></dd>
			</div>
		<?php endforeach; ?>
	</dl>
	<?php
	return;
}
?>
<div <?php echo megacenter_block_wrapper( 'mc-hours mc-hours--table' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<table>
		<caption class="screen-reader-text"><?php esc_html_e( 'Openingstijden per dag', 'megacenter-core' ); ?></caption>
		<tbody>
		<?php foreach ( $megacenter_days as $megacenter_day => $megacenter_label ) : ?>
			<tr<?php echo $megacenter_day === $megacenter_today ? ' class="is-today"' : ''; ?>>
				<th scope="row">
					<?php echo esc_html( $megacenter_label ); ?>
					<?php if ( $megacenter_day === $megacenter_today ) : ?>
						<span class="mc-hours__today"><?php esc_html_e( 'vandaag', 'megacenter-core' ); ?></span>
					<?php endif; ?>
				</th>
				<td><?php echo esc_html( $megacenter_fmt( $megacenter_day ) ); ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php if ( ! empty( $attributes['showNote'] ) && $megacenter_s['hours_note'] ) : ?>
		<p class="mc-hours__note"><?php echo esc_html( $megacenter_s['hours_note'] ); ?></p>
	<?php endif; ?>
</div>
