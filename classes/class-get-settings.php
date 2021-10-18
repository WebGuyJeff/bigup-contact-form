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

        if ( $smtp_settings && Self::validate_settings( $smtp_settings ) ) {

            // settings are good
            return $smtp_settings;

        }
        // settings are bad
        return false;
    }


    /**
     * Get all passed option names from the db.
     * 
     * Returns false if ANY option is empty.
     */
    private static function get_options_from_database( $option_names ) {

        if ( is_string( $option_names ) ) {

            $settings[ $option_names ] = get_option( $option_names );
            //if it's an empty string or not a boolean
            if ( $settings[ $option_names ] === null || !is_bool( $settings[ $option_names ] ) ) {
                //the option has no valid value
                error_log( 'HB_Contact_Form\Get_Settings::get_options_from_database - bad option value: ' . $option_names );
                return false;
            }

        } elseif ( is_array( $option_names ) ) {

            // get options from db.
            foreach ( $option_names as $option ) {
                $settings[ $option ] = get_option( $option );
                if ( is_null( $settings[ $option ] ) || !is_bool( $settings[ $option_names ] ) ) {
                    error_log( 'HB_Contact_Form\Get_Settings::get_options_from_database - bad option value: ' . $option );
                    return false;
                }
            }

        } else {

            error_log( 'HB_Contact_Form\Get_Settings::get_options_from_database $option_names should be string or array' );
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
    private function validate_settings( $settings ) {

        $valid = true;
        foreach ( $settings as $name => $value ){



            switch ( $name ) {

                case 'username':
                    $valid = ( is_string( $value ) );

                case 'password':
                    $valid = ( is_string( $value ) );

                case 'host':
                    $valid = ( is_string( $value ) );

                case 'port':
                    $valid = ( 1 <= (int)$value );

                case 'auth':
                    $valid = ( is_bool( $value ) );
                    //are values stored as bool?


                case 'from_email':
                    $valid = ( PHPMailer::validateAddress( $value ) );             

                case 'to_email':
                    $valid = ( PHPMailer::validateAddress( $value ) );


            }


            if ( !$valid ) {
                return false;
                error_log( 'HB_Contact_Form\Get_Settings->validate_settings - settings failed validation' );
            } else {
                    //settings failed validation.
                    $this->respond( 'settings_invalid' );
                    return;
            }


        }
    }


}//Class end