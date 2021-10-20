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
use WP_REST_Request;
class Form_Receiver {


    /**
     * Rest api handles nonces without needing to involve the submit
     * callback.
     * 
     */
    public static function hb_contact_form_rest_api_callback( WP_REST_Request $request ) {

        // Let user know we're doing something.
        $response = array( 
            'status'  => 200,
            'message' => 'Processing...'
        );
        echo json_encode( $response );

        // if content-type header is json
        if ( $request->get_header( 'Content-Type' ) === 'application/json'){

            // parse json from request.body
            $form_data = $request->get_json_params( $request );
            // make form data available as vars
            extract( $form_data );

            // if field vars are populated.
            if ( isset( $email ) && isset( $name ) && isset( $message ) ) {

                // required fields are populated.

                // send form data to smtp handler
                $form_values = array(
                    'submitted_email'   => $email,
                    'submitted_name'    => $name,
                    'submitted_message' => $message
                );
                $smtp_mailer = new SMTP_Send( $form_values );

            } else {
                // ERROR: One or more fields was empty.
                $response = array( 
                    'status'  => 400, 
                    'message' => 'Name, email and message are required fields.'
                );
                echo json_encode( $response );
            }

        } else {
            // ERROR: content-type header is wrong.
            $response = array(
                'status'  => 400,
                'message' => 'Server received data in a format different than expected.'
            );
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