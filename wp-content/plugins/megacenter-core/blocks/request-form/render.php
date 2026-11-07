<?php
/**
 * Render: aanvraagformulier.
 *
 * @package MegacenterCore
 * @var array $attributes Blok-attributen.
 */

defined( 'ABSPATH' ) || exit;

// Vaste anker-ID: na versturen springt de pagina terug naar het formulier.
$megacenter_extra = array( 'id' => 'aanvraag-formulier' );
?>
<div <?php echo megacenter_block_wrapper( 'mc-request', $megacenter_extra ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php echo megacenter_render_request_form( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- intern ge-escaped. ?>
</div>
