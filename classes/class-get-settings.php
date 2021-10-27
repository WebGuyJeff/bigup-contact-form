<?php
namespace Jefferson\HB_Contact_Form;

/**
 * Herringbone Contact From Get Settings and Validate From DB.
 *
 * This class fetches the settings from the database and validates their
 * values before passing them back to caller. If ANY of the settings are
 * invalid, returns false.
 *
 * @package Herringbone
 * @subpackage HB_Contact_Form
 * @author Jefferson Real <me@jeffersonreal.com>
 * @copyright Copyright (c) 2021, Jefferson Real
 * @license GPL2+
 */

// Import PHPMailer for use of the email validation method.
use PHPMailer\PHPMailer\PHPMailer;

// Load Composer's autoloader
require plugin_dir_path( __DIR__ ) . 'vendor/autoload.php';

class Get_Settings {


	/**
	 * Init the class by grabbing the saved options.
	 * 
	 * Performs initial validation to ensure no values are empty.
	 */
	public static function smtp() {
		
		$option_names = [
			'username',
			'password',
			'host',
			'port',
			'auth',
			'from_email',
			'to_email',
		];

		$smtp_settings = Self::get_options_from_database( $option_names );

		if ( Self::validate_settings( $smtp_settings ) ) {
			// settings ok
			return $smtp_settings;
		}
		// settings bad
		error_log( 'HB_Contact_Form - SMTP settings invalid.' );
		return false;
	}


	/**
	 * Get all passed option names from the db.
	 * 
	 * Returns false if ANY option is empty.
	 */
	private static function get_options_from_database( $option_names ) {

		if ( is_array( $option_names ) ) {
			foreach ( $option_names as $option ) {
				$settings[ $option ] = get_option( $option );
			}

		} elseif ( is_string( $option_names ) ) {
			$settings[ $option_names ] = get_option( $option_names );

		} else {
			error_log( 'HB_Contact_Form get_options_from_database expects string or array but ' . gettype( $option_names ) . ' received.' );
			return false;
		}
		return $settings;
	}


	/**
	 * Validate settings
	 * 
	 * Returns false if ANY option is invalid.
	 * This only validates settings and should not manipulate values.
	 */
	private static function validate_settings( $settings ) {

		// check for null values
		if ( in_array( null, $settings, true ) || in_array( '', $settings, true ) ) {
			error_log( 'HB_Contact_Form validate_settings one or more values are null.' );
			return false;
		};

		foreach ( $settings as $name => $value ) {
			$valid = true;
			switch ( $name ) {

				case 'username':
					$valid = ( is_string( $value ) ) ? true : false;
					continue 2;

				case 'password':
					$valid = ( is_string( $value ) ) ? true : false;
					continue 2;

				case 'host':
					if ( is_string( $value ) ) {
						$ip = gethostbyname( $value );
						$valid = ( !filter_var( $ip, FILTER_VALIDATE_IP ) ) ? true : false;
					} else {
						$valid = false;
					}
					continue 2;

				case 'port':
					$port_range = array(
						'options' => array(
							'min_range' => 1,
							'max_range' => 65535,
						)
					);
					$valid = ( filter_var( $value, FILTER_VALIDATE_INT, $port_range ) === FALSE) ? true : false;
					continue 2;

				case 'auth':
					$valid = ( is_bool( $value ) );
					continue 2;

				case 'from_email':
					$valid = ( PHPMailer::validateAddress( $value ) ) ? true : false;
					continue 2;            

				case 'to_email':
					$valid = ( PHPMailer::validateAddress( $value ) ) ? true : false;
					continue 2;
					
			}

			if ( $valid === false ) {
				//settings bad
				return false;
			}
		}
		//settings ok
		return true;
	}


}//Class end