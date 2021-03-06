<?php
/**
 * UIX Helper Functions
 *
 * @package   uix
 * @author    David Cramer
 * @license   GPL-2.0+
 * @copyright 2016 David Cramer
 */

/**
 * UIX Object class autoloader.
 * It locates and finds class via classes folder structure.
 *
 * @since 1.0.0
 *
 * @param string $class class name to be checked and autoloaded.
 */
function uix_autoload_class( $class ) {
	$parts = explode( '\\', $class );
	$name  = array_shift( $parts );
	if ( file_exists( UIX_PATH . 'classes/' . $name ) ) {
		if ( ! empty( $parts ) ) {
			$name .= '/' . implode( '/', $parts );
		}
		$class_file = UIX_PATH . 'classes/' . $name . '.php';
		if ( file_exists( $class_file ) ) {
			include_once $class_file; // @codeCoverageIgnore

			return; // @codeCoverageIgnore
		}
	}
	$name = str_replace( '_', '-', strtolower( $name ) );
	if ( file_exists( UIX_PATH . 'classes/' . $name . '.php' ) ) {
		include_once UIX_PATH . 'classes/' . $name . '.php'; // @codeCoverageIgnore
	}
}

/**
 * UIX Helper to get and work with the overall UI instance.
 *
 * @since 1.0.0
 */
function uix() {
	// @codingStandardsIgnoreStart
	$request_data = array(
		'post'    => $_POST,
		'get'     => $_GET,
		'files'   => $_FILES,
		'request' => $_REQUEST,
		'server'  => $_SERVER,
	);
	// @codingStandardsIgnoreEnd
	// init UI
	return \uix\ui::get_instance( $request_data );
}

/**
 * UIX Helper for shared assets.
 *
 * @since 1.0.0
 */
function uix_share() {
	// init UI share instance.
	return \uix\share\share::get_instance();
}

