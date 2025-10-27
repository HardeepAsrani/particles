<?php
/**
 * Editor Class.
 *
 * @package Codeinwp\Particles
 */

namespace ThemeIsle\Particles;

use ThemeIsle\Particles\Rest;

/**
 * Class Editor
 */
class Editor {

	/**
	 * Main constructor.
	 */
	public function __construct() {
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_editor_assets' ] );
	}

	/**
	 * Enqueue editor assets.
	 */
	public function enqueue_editor_assets(): void {
		$asset_file = include PARTICLES_PATH . '/build/index.asset.php';

		wp_enqueue_style(
			'particles-block-editor',
			PARTICLES_URL . '/build/style-index.css',
			[],
			$asset_file['version']
		);

		wp_enqueue_script(
			'particles-block-editor',
			PARTICLES_URL . '/build/index.js',
			$asset_file['dependencies'],
			$asset_file['version'],
			true
		);

		wp_set_script_translations( 'particles-block-editor', 'particles' );

		wp_localize_script(
			'particles-block-editor',
			'particlesEditor',
			[
				'api' => Rest::get_endpoint(),
			]
		);
	}
}
