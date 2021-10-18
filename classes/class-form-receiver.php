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
use function plugin_dir_path;
use function wp_verify_nonce;


class Form_Receiver {


    /**
     * Handle form submission for LOGGED IN USERS ONLY.
     */
    public static function catch_form_submission_logged_in() {

        status_header(200);

        $form_values = array(
            'nonce'               => $_REQUEST[ 'nonce' ],
            'action'              => $_REQUEST[ 'action' ],
            'submitted_email'     => $_REQUEST[ 'email' ],
            'submitted_name'      => $_REQUEST[ 'name' ],
            'submitted_message'   => $_REQUEST[ 'message' ]
        );

        if ( self::is_nonce_valid( $form_values[ 'nonce' ], $form_values[ 'action' ] ) ) {
            // Good nonce
            $response = array( "response" => "Processing..." );
            echo json_encode( $response );
            $smtp_mailer = new SMTP_Send( $form_values );
        } else {
            // Bad nonce
            $response = array( "result" => "insecure_failed_nonce" );
            echo json_encode( $response );
        }

        exit; //request handlers should exit() when done
    }


    /**
     * Handle form submission for ALL USERS.
     */
    public static function catch_form_submission_all_users() {

        status_header(200);

        $form_values = array(
            'nonce'               => $_REQUEST[ 'nonce' ],
            'action'              => $_REQUEST[ 'action' ],
            'submitted_email'     => $_REQUEST[ 'email' ],
            'submitted_name'      => $_REQUEST[ 'name' ],
            'submitted_message'   => $_REQUEST[ 'message' ]
        );

        if ( self::is_nonce_valid( $form_values[ 'nonce' ], $form_values[ 'action' ] ) ) {
            // Good nonce
            $response = array( "response" => "Processing..." );
            echo json_encode( $response );
            $smtp_mailer = new SMTP_Send( $form_values );
        } else {
            // Bad nonce
            $response = array( "result" => "insecure_failed_nonce" );
            echo json_encode( $response );
        }

        exit; //request handlers should exit() when done
    }


    /**
     * Validate WordPress nonces.
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