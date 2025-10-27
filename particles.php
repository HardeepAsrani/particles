<?php
/**
 * Particles.
 *
 * @package Codeinwp/particles
 *
 * Plugin Name:         Particles
 * Plugin URI:          https://themeisle.com/plugins/particles/
 * Description:         Particles are everywhere.
 * Version:             1.0.0
 * Author:              ThemeIsle
 * Author URI:          https://themeisle.com
 * License:             GPL-3.0+
 * License URI:         http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:         particles
 * Domain Path:         /languages
 * WordPress Available: yes
 * Requires License:    no
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'PARTICLES_BASEFILE', __FILE__ );
define( 'PARTICLES_URL', plugins_url( '/', __FILE__ ) );
define( 'PARTICLES_PATH', __DIR__ );
define( 'PARTICLES_VERSION', '1.0.0' );
define( 'PARTICLES_PRODUCT_SLUG', basename( dirname( 'PARTICLES_BASEFILE' ) ) );
define( 'PARTICLES_API_KEY', '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' );

$vendor_file = PARTICLES_PATH . '/vendor/autoload.php';

if ( is_readable( $vendor_file ) ) {
	require_once $vendor_file;
}

add_action(
	'plugins_loaded',
	function () {
		new \ThemeIsle\Particles\Main();
	}
);
