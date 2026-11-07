<?php
/**
 * Plugin Name:       Megacenter Core
 * Description:       Activiteiten, arrangementen, instellingen, aanvraagformulier, SEO-basis en demo-inhoud voor de Megacenter-website. Alles wat een themawissel moet overleven.
 * Version:           1.0.0
 * Requires at least: 6.7
 * Requires PHP:      8.1
 * Author:            Bas
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       megacenter-core
 *
 * @package MegacenterCore
 */

defined( 'ABSPATH' ) || exit;

define( 'MEGACENTER_CORE_VERSION', '1.0.0' );
define( 'MEGACENTER_CORE_FILE', __FILE__ );
define( 'MEGACENTER_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'MEGACENTER_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once MEGACENTER_CORE_DIR . 'includes/helpers.php';
require_once MEGACENTER_CORE_DIR . 'includes/post-types.php';
require_once MEGACENTER_CORE_DIR . 'includes/meta.php';
require_once MEGACENTER_CORE_DIR . 'includes/settings.php';
require_once MEGACENTER_CORE_DIR . 'includes/bindings.php';
require_once MEGACENTER_CORE_DIR . 'includes/blocks.php';
require_once MEGACENTER_CORE_DIR . 'includes/forms.php';
require_once MEGACENTER_CORE_DIR . 'includes/seo.php';
require_once MEGACENTER_CORE_DIR . 'includes/frontend.php';
require_once MEGACENTER_CORE_DIR . 'includes/admin.php';
require_once MEGACENTER_CORE_DIR . 'includes/seeder.php';

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once MEGACENTER_CORE_DIR . 'includes/cli.php';
}

/**
 * Bij activeren: post types registreren en permalinks verversen.
 */
function megacenter_core_activate() {
	megacenter_register_post_types();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'megacenter_core_activate' );

/**
 * Bij deactiveren: permalinks verversen.
 */
function megacenter_core_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'megacenter_core_deactivate' );
