<?php
/**
 * Render: sociale media-iconen uit de instellingen.
 *
 * @package MegacenterCore
 * @var array $attributes Blok-attributen.
 */

defined( 'ABSPATH' ) || exit;

$megacenter_s        = megacenter_get_settings();
$megacenter_networks = array(
	'instagram' => 'Instagram',
	'facebook'  => 'Facebook',
	'tiktok'    => 'TikTok',
	'linkedin'  => 'LinkedIn',
	'youtube'   => 'YouTube',
);
$megacenter_links    = array_filter( array_intersect_key( $megacenter_s, $megacenter_networks ) );
if ( ! $megacenter_links ) {
	return;
}
?>
<ul <?php echo megacenter_block_wrapper( 'mc-socials' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php foreach ( $megacenter_links as $megacenter_key => $megacenter_url ) : ?>
		<li>
			<a href="<?php echo esc_url( $megacenter_url ); ?>" class="mc-socials__link">
				<?php echo megacenter_icon( $megacenter_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span class="screen-reader-text">
					<?php
					/* translators: 1: naam van de locatie, 2: naam sociaal netwerk */
					echo esc_html( sprintf( __( '%1$s op %2$s', 'megacenter-core' ), megacenter_site_name(), $megacenter_networks[ $megacenter_key ] ) );
					?>
				</span>
			</a>
		</li>
	<?php endforeach; ?>
</ul>
