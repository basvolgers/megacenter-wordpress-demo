<?php
/**
 * Dynamische blokken (PHP-render, geen build-stap). De editor toont ze via ServerSideRender.
 *
 * @package MegacenterCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Scripts/stijlen registreren en alle blokken uit /blocks registreren.
 */
function megacenter_register_blocks() {
	$css = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? 'blocks.css' : 'blocks.min.css';
	wp_register_style( 'megacenter-core-blocks', MEGACENTER_CORE_URL . 'assets/css/' . $css, array(), MEGACENTER_CORE_VERSION . '.' . filemtime( MEGACENTER_CORE_DIR . 'assets/css/' . $css ) );

	wp_register_script(
		'megacenter-core-editor',
		MEGACENTER_CORE_URL . 'assets/js/editor.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render', 'wp-data', 'wp-core-data', 'wp-i18n' ),
		MEGACENTER_CORE_VERSION,
		true
	);
	wp_register_script( 'megacenter-core-filter', MEGACENTER_CORE_URL . 'assets/js/filter.js', array(), MEGACENTER_CORE_VERSION, array( 'strategy' => 'defer' ) );

	foreach ( glob( MEGACENTER_CORE_DIR . 'blocks/*/block.json' ) as $file ) {
		register_block_type( dirname( $file ) );
	}
}
add_action( 'init', 'megacenter_register_blocks' );

/**
 * Eigen blokcategorie in de inserter.
 *
 * @param array $categories Categorieën.
 * @return array
 */
function megacenter_block_category( $categories ) {
	array_unshift(
		$categories,
		array(
			'slug'  => 'megacenter',
			'title' => __( 'Megacenter', 'megacenter-core' ),
			'icon'  => null,
		)
	);
	return $categories;
}
add_filter( 'block_categories_all', 'megacenter_block_category' );

/**
 * Helper voor render.php: wrapper-attributen met extra class.
 *
 * @param string $class Extra class.
 * @param array  $extra Extra attributen.
 * @return string
 */
function megacenter_block_wrapper( $class, $extra = array() ) {
	return get_block_wrapper_attributes( array_merge( array( 'class' => $class ), $extra ) );
}

/**
 * Bepaalt de activiteit voor een blok (context of huidige post).
 *
 * @param WP_Block|null $block Blok.
 * @return int
 */
function megacenter_block_activity_id( $block ) {
	$post_id = 0;
	if ( $block instanceof WP_Block && ! empty( $block->context['postId'] ) ) {
		$post_id = (int) $block->context['postId'];
	}
	if ( ! $post_id ) {
		$post_id = (int) get_the_ID();
	}
	return 'activiteit' === get_post_type( $post_id ) ? $post_id : 0;
}
