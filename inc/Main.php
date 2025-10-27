<?php
/**
 * Plugin Class.
 *
 * @package Codeinwp\Particles
 */

namespace ThemeIsle\Particles;

use ThemeIsle\Particles\Editor;
use ThemeIsle\Particles\Rest;

/**
 * Class Main
 */
class Main {

	/**
	 * Instace of Editor class.
	 *
	 * @var Editor
	 */
	public $editor;

	/**
	 * Instance of Rest class.
	 *
	 * @var Rest
	 */
	public $rest;

	/**
	 * Main constructor.
	 */
	public function __construct() {
		$this->editor = new Editor();
		$this->rest   = new Rest();
	}
}
