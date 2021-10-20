<?php
namespace Jefferson\HB_Contact_Form;

/**
 * Herringbone Contact Form POST handler.
 *
 * This template defines the front end form HTML
 *
 * @package Herringbone
 * @subpackage HB_Contact_Form
 * @author Jefferson Real <me@jeffersonreal.com>
 * @copyright Copyright (c) 2021, Jefferson Real
 * @license GPL2+
 */

// WordPress Dependencies
use function wp_verify_nonce;
use function get_json_params;

class Form_Receiver {


    /**
     * Rest api handles nonces without needing to involve the submit
     * callback.
     * 
     */
    public static function hb_contact_form_rest_api_callback( $request ) {

        $form_data = $request->get_json_params( $request );
        extract( $form_data );

        if ( isset( $email ) || isset( $name ) || isset( $message ) ) {

            $form_values = array(
                'submitted_email'   => $email,
                'submitted_name'    => $name,
                'submitted_message' => $message
            );

            $response = array( "response" => "Processing..." );
            echo json_encode( $response );
            $smtp_mailer = new SMTP_Send( $form_values );

        } else {
            $response = array( "response" => "Error: One or more submitted fields did not contain a value." );
            echo json_encode( $response );
        }

        exit; //request handlers should exit() when done
    }


    /**
     * Validate WordPress nonces.
     * 
     * Only required for manual nonce validation when using wp_admin.php.
     * 
     */
    private static function is_nonce_valid( $nonce_to_check, $action ) {

        $nonce_ok = true;

        if ( is_string( $nonce_to_check ) && !wp_verify_nonce( $nonce_to_check, $action ) ) {
            // Bad nonce
            $nonce_ok = false;

        } else if ( is_array( $nonce_to_check ) ) {
            foreach ( $nonce_to_check as $nonce ) {
                if ( !wp_verify_nonce( $nonce, $action ) ) {
                    // Bad nonce
                    $nonce_ok = false;
                }
            }
        }

        if ( $nonce_ok ) {
            return true;
        } else {
            return false;
        }
    }


}//Class end