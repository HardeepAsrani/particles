<?php
/**
 * Particles Constants.
 * 
 * Adds Particles constants for PHPStan to use.
 * 
 * @package Codeinwp\Particles
 */

define( 'PARTICLES_BASEFILE', dirname( __DIR__, 2 ) . '/particles.php' );
define( 'PARTICLES_URL', plugins_url( '/', dirname( __DIR__, 2 ) . '/particles.php' ) );
define( 'PARTICLES_PATH', dirname( __DIR__, 2 ) );
