<?php
/**
 * Front-end: demobalk en koppeling van de seeder-menu's aan de navigatieblokken.
 *
 * @package MegacenterCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Vroeg in de <head>: weggeklikte demobalk direct verbergen (geen flits) en .js-class zetten.
 */
function megacenter_head_inline_script() {
	if ( ! (int) megacenter_setting( 'demo_bar' ) ) {
		return;
	}
	wp_print_inline_script_tag( "try{if(sessionStorage.getItem('mcDemoBar')==='0'){document.documentElement.classList.add('mc-demo-hidden')}}catch(e){}" );
}
add_action( 'wp_head', 'megacenter_head_inline_script', 1 );

/**
 * Demobalk direct na <body>.
 */
function megacenter_demo_bar() {
	if ( ! (int) megacenter_setting( 'demo_bar' ) ) {
		return;
	}
	$text = (string) megacenter_setting( 'demo_bar_text' );
	?>
	<div class="mc-demobar" role="region" aria-label="<?php esc_attr_e( 'Demomelding', 'megacenter-core' ); ?>">
		<p class="mc-demobar__text"><span class="mc-demobar__tag"><?php esc_html_e( 'Demo', 'megacenter-core' ); ?></span> <?php echo esc_html( preg_replace( '/^Demo:\s*/i', '', $text ) ); ?></p>
		<button type="button" class="mc-demobar__close" data-mc-demobar-close>
			<span aria-hidden="true">×</span><span class="screen-reader-text"><?php esc_html_e( 'Demomelding sluiten', 'megacenter-core' ); ?></span>
		</button>
	</div>
	<?php
	wp_print_inline_script_tag( "document.querySelector('[data-mc-demobar-close]').addEventListener('click',function(){document.documentElement.classList.add('mc-demo-hidden');try{sessionStorage.setItem('mcDemoBar','0')}catch(e){}});" );
}
add_action( 'wp_body_open', 'megacenter_demo_bar', 5 );

/**
 * Basisstijlen van de plugin (demobalk, formulier) op elke pagina; klein bestand.
 */
function megacenter_enqueue_front() {
	wp_enqueue_style( 'megacenter-core-blocks' );
}
add_action( 'wp_enqueue_scripts', 'megacenter_enqueue_front' );

/**
 * Navigatieblokken zonder menu krijgen het juiste seeder-menu:
 * class "mc-nav-primary" → hoofdmenu, "mc-nav-footer" → footermenu, "mc-nav-legal" → juridisch.
 * Zo kan het thema vaste template-parts hebben, terwijl de klant de menu's gewoon in de editor beheert.
 *
 * @param array $parsed_block Blok.
 * @return array
 */
function megacenter_navigation_ref( $parsed_block ) {
	if ( 'core/navigation' !== ( $parsed_block['blockName'] ?? '' ) || ! empty( $parsed_block['attrs']['ref'] ) || ! empty( $parsed_block['innerBlocks'] ) ) {
		return $parsed_block;
	}
	$class = $parsed_block['attrs']['className'] ?? '';
	$map   = array(
		'mc-nav-primary' => 'megacenter_nav_primary',
		'mc-nav-footer'  => 'megacenter_nav_footer',
		'mc-nav-legal'   => 'megacenter_nav_legal',
	);
	foreach ( $map as $needle => $option ) {
		if ( str_contains( $class, $needle ) ) {
			$id = (int) get_option( $option );
			if ( $id && 'wp_navigation' === get_post_type( $id ) && 'publish' === get_post_status( $id ) ) {
				$parsed_block['attrs']['ref'] = $id;
			}
			break;
		}
	}
	return $parsed_block;
}
add_filter( 'render_block_data', 'megacenter_navigation_ref' );
