<?php
/**
 * WP-CLI: wp megacenter seed [--reset]
 *
 * @package MegacenterCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Demo-inhoud beheren.
 */
class Megacenter_CLI_Command {

	/**
	 * Maakt de demo-inhoud aan of werkt hem bij.
	 *
	 * ## OPTIONS
	 *
	 * [--reset]
	 * : Verwijdert eerst alle demo-items en maakt ze daarna opnieuw aan.
	 *
	 * ## EXAMPLES
	 *
	 *     wp megacenter seed
	 *     wp megacenter seed --reset
	 *
	 * @param array $args       Argumenten.
	 * @param array $assoc_args Opties.
	 */
	public function seed( $args, $assoc_args ) {
		if ( ! empty( $assoc_args['reset'] ) ) {
			$removed = megacenter_demo_reset();
			WP_CLI::log( sprintf( '%d demo-items verwijderd.', $removed ) );
		}
		$result = megacenter_demo_seed();
		WP_CLI::success( sprintf( 'Klaar: %d beelden, %d activiteiten, %d pagina\'s.', $result['media'], $result['activities'], $result['pages'] ) );
	}
}

WP_CLI::add_command( 'megacenter', 'Megacenter_CLI_Command' );
