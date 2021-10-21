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
use WP_REST_Request;


class Form_Receiver {


    /**
     * A list of allowed html elements used to sanitize email content.
     */
    private $allowed_html_tags = array(
        'a'         => array(
            'href'      => true,
        ),
        'b'         => array(),
        'br'        => array(),
        'code'      => array(),
        'h1'        => array(),
        'h2'        => array(),
        'h3'        => array(),
        'h4'        => array(),
        'h5'        => array(),
        'h6'        => array(),
        'i'         => array(),
        'img'       => array(
            'alt'       => true,
            'align'     => true,
            'border'    => true,
            'height'    => true,
            'src'       => true,
            'width'     => true,
        ),
        'li'        => array(),
        'p'         => array(),
        'pre'       => array(),
        'q'         => array(),
        'span'      => array(),
        'small'     => array(),
        'strong'    => array(),
        'u'         => array(),
        'ul'        => array(),
        'ol'        => array(),
    );


    /**
     * Receive form submissions.
     *
     * Handle backend form data validation, sanitization and response
     * messaging before passing to SMTP handler.
     * 
     * Note: Rest api handles nonces automatically.
     * 
     */
    public static function hb_contact_form_rest_api_callback( WP_REST_Request $request ) {

        // Let user know we're doing something.
        $response = array( 
            'status'     => 200,
            'statusText' => 'OK',
            'message'    => 'Processing...',
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
                    'status'     => 400,
                    'statusText' => 'Bad Request',
                    'message'    => 'Name, email and message are required fields.'
                );
                echo json_encode( $response );
            }

        } else {
            // ERROR: content-type header is wrong.
            $response = array(
                'status'      => 405,
                'statusText'  => 'Method not allowed',
                'description' => 'Server received disallowed data type',
            );
            echo json_encode( $response );
        }
        exit; //request handlers should exit() when done
    }





    private function respond( $result ) {

        error_log( 'Jefferson\HB_Contact_Form\SMTP_Send\respond: ' . $result );
        
                switch ( $result ) {
        
                    case 'success':
                        $response = array( "result" => "success" );
                        echo json_encode( $response );
                        break;
        
                    case 'settings_missing':
                        $response = array( "result" => "settings_missing" );
                        echo json_encode( $response );
                        break;
        
                    case 'settings_invalid':
                        $response = array( "result" => "settings_invalid" );
                        echo json_encode( $response );
                        break;
        
                    case 'email_invalid':
                        $response = array( "result" => "email_invalid" );
                        echo json_encode( $response );
                        break;
        
                    default:
                        //send raw data as result
                        $response = array( "result" => $result );
                        echo json_encode( $response );
                }
            }
        






}//Class end