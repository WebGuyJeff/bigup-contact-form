<?php
namespace Bigup\Contact_Form;

/**
 * A library of helper functions for WordPress.
 *
 * @package lonewolf
 * @author Jefferson Real <me@jeffersonreal.uk>
 * @copyright Copyright 2023 Jefferson Real
 */


/**
 * Utility methods.
 */
class Util {


	/**
	 * Retrieve file contents the 'WordPress way'.
	 * 
	 * @param string $path File system path.
	 */
	public static function get_contents( $path ) {
		include_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
		include_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
		if ( ! class_exists( 'WP_Filesystem_Direct' ) ) {
			return false;
		}
		$wp_filesystem = new \WP_Filesystem_Direct( null );
		$string        = $wp_filesystem->get_contents( $path );
		return $string;
	}

}