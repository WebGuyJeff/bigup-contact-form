<?php
namespace Bigup\Contact_Form;

/**
 * Bigup Contact Form - Get Settings and Validate From DB.
 *
 * This class fetches the settings from the database and validates their
 * values before passing them back to caller. If ANY of the settings are
 * invalid, returns false.
 *
 * @package bigup_contact_form
 * @author Jefferson Real <me@jeffersonreal.uk>
 * @copyright Copyright (c) 2023, Jefferson Real
 * @license GPL2+
 * @link https://jeffersonreal.uk
 * 
 */

// Import PHPMailer for use of the email validation method.
use PHPMailer\PHPMailer\PHPMailer;

// Load Composer's autoloader (includes vendor/PHPMailer)
require plugin_dir_path( __DIR__ ) . 'vendor/autoload.php';

class Get_Settings {


	/**
	 * Get the SMTP options.
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
			'use_sendmail',
			'from_email',
			'to_email',
		];

		$smtp_settings = Self::get_options_from_database( $option_names );

		if ( Self::validate_settings( $smtp_settings ) ) {
			// settings ok
			return $smtp_settings;
		}
		// settings bad
		error_log( 'Bigup_Contact_Form: SMTP settings invalid.' );
		return false;
	}

	/**
	 * Get the Sendmail options.
	 * 
	 * Performs initial validation to ensure no values are empty.
	 */
	public static function sendmail() {
		
		$option_names = [
			'use_sendmail',
			'from_email',
			'to_email',
		];

		$sendmail_settings = Self::get_options_from_database( $option_names );

		if ( Self::validate_settings( $sendmail_settings ) ) {
			// settings ok
			return $sendmail_settings;
		}
		// settings bad
		error_log( 'Bigup_Contact_Form: Sendmail settings invalid.' );
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
			error_log( 'Bigup_Contact_Form: get_options_from_database expects string or array but ' . gettype( $option_names ) . ' received.' );
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

		// Tailored validation.
		foreach ( $settings as $name => $value ) {

			$valid = true;
			switch ( $name ) {
				case 'username':
					$valid = ( is_string( $value ) ) ? true : false;
					break;

				case 'password':
					$valid = ( is_string( $value ) ) ? true : false;
					break;

				case 'host':
					if ( is_string( $value ) ) {
						$ip = gethostbyname( $value );
						$valid = ( filter_var( $ip, FILTER_VALIDATE_IP ) ) ? true : false;
					} else {
						$valid = false;
					}
					break;

				case 'port':
					$port_range = [
						'options' => [
							'min_range' => 1,
							'max_range' => 65535,
						]
					];
					$valid = ( filter_var( $value, FILTER_VALIDATE_INT, $port_range ) === FALSE ) ? false : true;
					break;

				case 'auth':
					$valid = ( is_bool( (bool)$value ) ) ? true : false;
					break;

				case 'use_sendmail':
					$valid = ( is_bool( (bool)$value ) ) ? true : false;
					break;

				case 'from_email':
					$valid = ( PHPMailer::validateAddress( $value ) ) ? true : false;
					break;            

				case 'to_email':
					$valid = ( PHPMailer::validateAddress( $value ) ) ? true : false;
					break;	

				case 'styles':
					$valid = ( is_bool( (bool)$value ) ) ? true : false;
					break;

			}
			if ( $valid === false ) {
				//settings bad - we're done here
				error_log( 'Bigup_Contact_Form: Setting "' . $name . '" has failed validation.');
				return false;
			}
		}
		//settings ok
		return true;
	}


}//Class end