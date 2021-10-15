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


class Form_Handler {


    /**
     * Handle form submission for LOGGED IN USERS ONLY.
     */
    public static function catch_ajax_logged_in() {


echo '<script>console.log("HELLO!!!")</script>';

        status_header(200);

        $form_values = array(
            'submitted_email'     => $_REQUEST[ 'hb_contact_form_email_nonce' ],
            'submitted_name'      => $_REQUEST[ 'hb_contact_form_name_nonce' ],
            'submitted_message'   => $_REQUEST[ 'hb_contact_form_message_nonce' ]
        );

        self::nonce_validation( $form_values );

        //request handlers should exit() when they complete their task
        var_dump( $form_values );
        exit( "Server received the form submission from your browser.");
    }


    /**
     * Handle form submission for ALL USERS.
     */
    public static function catch_ajax_all_users() {

        status_header(200);

        $form_values = array(
            'submitted_email'     => $_REQUEST[ 'hb_contact_form_email_nonce' ],
            'submitted_name'      => $_REQUEST[ 'hb_contact_form_name_nonce' ],
            'submitted_message'   => $_REQUEST[ 'hb_contact_form_message_nonce' ]
        );

        self::nonce_validation( $form_values );

        //request handlers should exit() when they complete their task
        exit;
    }


    /**
     * Validate nonces then pass to SMTP_Send on pass.
     */
    private static function nonce_validation( $form_values ) {

        foreach ( $form_values as $nonce ) {
            if ( !wp_verify_nonce( $nonce, 'hb_contact_form_submit' ) ) {

                // BAD nonce
                $response = array( "result" => "insecure_failed_nonce" );
                echo json_encode( $response );
                exit;
            }
        }

        // GOOD nonce
        $smtp_mailer = new SMTP_Send( $form_values );
    }


}//Class end