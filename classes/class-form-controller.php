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

// Load Composer's autoloader
require plugin_dir_path( __DIR__ ) . 'vendor/autoload.php';

// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// WordPress Dependencies
use function sanitize_email;
use function wp_kses;
use WP_REST_Request;

class Form_Controller {


    /**
     * A list of allowed html elements used to sanitize message body.
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
    public function hb_contact_form_rest_api_callback( WP_REST_Request $request ) {

        // if content-type header is json
        if ( $request->get_header( 'Content-Type' ) === 'application/json'){

            // parse json from request.body
            $form_data = $request->get_json_params( $request );

            // object to vars
            extract( $form_data );
            // vars to array
            $data[ 'fields' ] = [
                'email'   => $email,
                'name'    => $name,
                'message' => $message
            ];

            /**
             * Sanitise and validate.
             * 
             * In this instance, sanitisation is treated as a validation check. Any
             * sanitisation required, is passed back to the user for human correction
             * as not to pass unexpected values to the mailer. The returned array WILL
             * have it's values modified.
             * 
             * @param array $data_clean: The sanitised array.
             * @param array $data_clean_valid: The sanitised AND validated array.
             * 
             */
            $data_clean = $this->sanitise_user_input( $data );
            $data_clean_valid = $this->validate_user_input( $data_clean );

            $form_values_ok = true;
            $errors = [];

            // Collect sanitise errors.
            if ( $data_clean_valid[ 'modified_by_sanitise' ] ) {
                foreach ( $data_clean_valid[ 'modified_by_sanitise' ] as $field ) {
                    $errors[] = $field[ 'error' ];
                }
                $form_values_ok = false;
            }

            // Collect validation errors.
            if ( $data_clean_valid[ 'validation_errors' ] ) {
                foreach ( $data_clean_valid[ 'validation_errors' ] as $error ) {
                    $errors[] = $error;
                }
                $form_values_ok = false;
            }

            if ( $form_values_ok ) {
                /**
                 * Send checked form values to mailer.
                 * 
                 * Form values have now passed all checks, so the original array $data[ 'fields' ]
                 * is passed to the mailer as the validation data in $data_clean_valid is
                 * now surplus.
                 * 
                 */
                $smtp_handler = new SMTP_Send();
                if ( $smtp_handler->settings_ok ) {
                    $send_result = $smtp_handler->compose_and_send_smtp_email( $data[ 'fields' ] );
                    $this->send_json_response( $send_result );
                } else {
                    $this->send_json_response( [ 500, 'Bad SMTP configuration. Please alert site admin.' ] );
                }

            } else {
                // BAD: validation fail
                $this->send_json_response( [ 400, $errors ] );
            }

        } else {
            // BAD: wrong type header
            $this->send_json_response( [ 405, 'Server received disallowed data type' ] );
        }
        exit; //request handlers should exit() when done
    }


    /**
     * Sanitise user input.
     * 
     * Returns the array with cleaned values and data indicating invalid
     * input. Does not validate values and will return empty array keys in
     * cases where all characters are invalid. The array will be returned
     * with a sub-array bearing key 'modified_by_sanitise' containing error
     * messages and the before/after values for use on the front end.
     * 
     * @param array $raw_form_data: Associative array of form input data.
     * @return array $form_data: Contains cleaned values and sanitisation info.
     * 
     */
    public function sanitise_user_input( $form_data_array ) {

        $modified = [];

        foreach ( $form_data_array[ 'fields' ] as $field => $value ) {

            $old = $form_data_array[ 'fields' ][ $field ];
            $new = '';
            
            switch ( $field ) {
                case 'name':
                    // should names be filtered?
                    $new = filter_var( $old, FILTER_SANITIZE_STRING );
                    continue 2;

                case 'email':
                    $old = strtolower( $old );
                    $new = sanitize_email( $old );
                    continue 2;

                case 'message':
                    $new = wp_kses( $value, $this->allowed_html_tags );
                    continue 2;
            }

            // if the value was modified, generate an error message indicating the disallowed chars.
            if ( $old !== $new ) {
                $invalid_chars = str_replace( str_split( strtolower( $new ) ), '', strtolower( $old ) );
                $modified[ $field ][ 'error' ] = "{$field} contains invalid characters ( {$invalid_chars} ).";
                $modified[ $field ][ 'old' ] = $old;
                $modified[ $field ][ 'new' ] = $new;
            }
            $form_values[ $field ] = $new;
        }

        if ( $modified ) {
            $form_data_array[ 'modified_by_sanitise' ] = $modified;
        } else {
            $form_data_array[ 'modified_by_sanitise' ] = false;
        }

        return $form_data_array;
    }


    /**
     * Validate user input.
     * 
     * Should be performed AFTER any sanitisation as a final pre-flight check.
     * Returns true on success, otherwise false.
     * Note: Never modifies or returns values.
     * 
     * @param array $form_values: An associative array of form field values. 
     * 
     */
    public function validate_user_input( $form_data_array ) {

        foreach ( $form_data_array[ 'fields' ] as $field => $value ) {   
            
            switch ( $field ) {
                case 'name':
                    if ( strlen( $value ) < 2 || strlen( $value ) > 50 ) {
                        $results[] = 'Name should be 2-50 characters.';
                    }
                    continue 2; // returns parsing to the loop.

                case 'email':
                    if ( ! PHPMailer::validateAddress( $value ) ) {
                        $results[] = 'Email address is invalid.';
                    }
                    continue 2;

                case 'message':
                    if ( strlen( $value ) < 10 || strlen( $value ) > 3000 ) {
                        $results[] = 'Message body should be 10-3000 characters.';
                    }
                    continue 2;
            }
        }
        if ( isset( $results[ 0 ] ) ) {
            $form_data_array[ 'validation_errors' ] = $results;
        } else {
            $form_data_array[ 'validation_errors' ] = false;
        }
        return $form_data_array;
    }


    /**
     * Send JSON response to client.
     * 
     * Sets the response header to the passed http status code and a
     * response body containing an array of status code, status text
     * and human-readable description of the status or error.
     *  
     * @param array $info: [ int(http-code), str(human readable message) ].
     * 
     */
    private function send_json_response( $info ) {
        
        if ( is_array( $info ) ) {

            $codes = [
                200 => 'OK',
                400 => 'Bad Request',
                405 => 'Method Not Allowed',
                500 => 'Internal Server Error',
            ];
            if ( ! headers_sent() ) {
                header( 'Content-Type: application/json' );
                status_header( $info[ 0 ], $codes[ $info[ 0 ] ] );
            }

            if ( $info[ 0 ] < 300 ) {
                $payload[ 'ok' ] = true;
                $payload[ 'message' ] = $info[ 1 ];

            } else {
                $payload[ 'ok' ] = false;
                $payload[ 'errors' ] = $info[ 1 ];
            }

            echo json_encode( $payload );

        } else {
            error_log( 'Form_Controller\send_json_response expects array but ' . gettype( $info ) . ' received.' );
        }   
    }


}//Class end