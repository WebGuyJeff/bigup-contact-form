<?php
namespace Bigup\Contact_Form;

/**
 * Bigup Contact Form - POST handler.
 *
 * Handle backend form data validation, sanitization and response
 * messaging before passing to mail handler.
 * 
 * Note: Rest api handles nonces automatically.
 *
 * @package bigup-contact-form
 * @author Jefferson Real <me@jeffersonreal.uk>
 * @copyright Copyright (c) 2023, Jefferson Real
 * @license GPL2+
 * @link https://jeffersonreal.uk
 * 
 */

// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require BIGUPCF_PATH . 'vendor/autoload.php';

// WordPress Dependencies
use WP_REST_Request;
use get_option;

class Form_Controller {

    /**
     * Receive form submissions.
     */
    public function bigup_contact_form_rest_api_callback( WP_REST_Request $request ) {

        // Check header is multipart/form-data.
        if ( ! str_contains( $request->get_header( 'Content-Type' ), 'multipart/form-data' ) ) {
            $this->send_json_response( [ 405, 'Sending your message failed due to a malformed request from your browser' ] );
			exit; //request handlers should exit() when done
        }

		$data = [];

		// Get the form file data.
		$file_data = $request->get_file_params();
		if ( array_key_exists( 'files', $file_data ) ) {
			$number_of_files = count( $file_data[ 'files' ][ 'name' ] ) - 1;
			for ( $n = 0; $n <= $number_of_files; $n++ ) {
				$data[ 'files' ][ $n ] = [
					'name'   => $file_data[ 'files' ][ 'name' ][ $n ],
					'tmp_name'    => $file_data[ 'files' ][ 'tmp_name' ][ $n ]
				];
			}
		}

		// Get the form text data.
		$text_data = $request->get_body_params();
		$data[ 'fields' ] = [
			'email'   => $text_data[ 'email' ],
			'name'    => $text_data[ 'name' ],
			'message' => $text_data[ 'message' ]
		];
		$data_sanitised = $this->sanitise_user_input( $data );
		$data_validated = $this->validate_user_input( $data );
		$form_values_ok = true;
		$errors = [];

		// Collect sanitise errors.
		if ( $data_sanitised[ 'modified_by_sanitise' ] ) {
			foreach ( $data_sanitised[ 'modified_by_sanitise' ] as $field ) {
				$errors[] = $field[ 'error' ];
			}
			$form_values_ok = false;
		}

		// Collect validation errors.
		if ( $data_validated[ 'validation_errors' ] ) {
			foreach ( $data_validated[ 'validation_errors' ] as $error ) {
				$errors[] = $error;
			}
			$form_values_ok = false;
		}

		// Return data to client if there are errors.
		if ( false === !! $form_values_ok ) {
			$this->send_json_response( [ 400, $errors ] );
        	exit; //request handlers should exit() when done
		}

		$saved_settings   = get_option( 'bigup_contact_form_settings' );
		$use_local_mailer = $saved_settings['use_local_mail_server'];
		$mail_handler     = ( isset( $use_local_mailer ) && true === $use_local_mailer ) ? new Send_Local() : new Send_SMTP();
		$result           = $mail_handler->compose_and_send_email( $data );
		$this->send_json_response( $result );

		// Log form entry post.
		Store_Submissions::log_form_entry( $data, $result );

        exit; // Request handlers should exit() when done.
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
     * @param array $form_data_array: Associative array of form input data.
     * @return array $form_data_array: Contains cleaned values and sanitisation info.
     * 
     */
    public function sanitise_user_input( $form_data_array ) {

        $modified = [];

        foreach ( $form_data_array[ 'fields' ] as $field => $value ) {

            $old = trim( $form_data_array[ 'fields' ][ $field ] );
            $new = '';

            switch ( $field ) {
                case 'name':
                    // Remove tags and non-unicode language chars.
                    $pattern = "/<[^>]*>|[^ \p{L}\p{N}\p{M}\p{P}]/u";
                    $invalid_chars = '';
                    if ( preg_match_all( $pattern, $old, $matches ) ) {
                        foreach ( $matches[0] as $match ) {
                            $invalid_chars .= $match;
                        }
                        $new = preg_filter( $pattern, '', $old );
                    } else {
                        $new = $old;
                    }
                    break;

                case 'email':
                    // Email sanitisation is futile.
                    $new = $old;
                    break;

                case 'message':
                    // Remove tags apart from these.
                    $pattern = "/(?:(?!(<(\/*)(a|b|br|code|div|h[1-6]|img|li|p|pre|q|span|small|strong|u|ul|ol)(>| [^>]*?>))))(<.*?>)/";
                    $invalid_chars = '';
                    if ( preg_match_all( $pattern, $old, $matches ) ) {
                        foreach ( $matches[0] as $match ) {
                            $invalid_chars .= $match;
                        }
                        $new = preg_filter( $pattern, '', $old );
                    } else {
                        $new = $old;
                    }
                    break;
            }

            // Store disallowed input errors.
            if ( $old !== $new ) {
                $modified[ $field ][ 'error' ] = ucfirst( $field ) . ' contains invalid input (' . $invalid_chars . ')';
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
    private function send_json_response( $public_status ) {
        
        if ( ! is_array( $public_status ) ) {
            error_log( 'Bigup_Contact_Form: send_json_response expects array but ' . gettype( $public_status ) . ' received.' );
            $public_status = null;
            $public_status[ 0 ] = 500;
            $public_status[ 1 ] = 'Sending your message failed due to an unexpected error.';
        }

        // Ensure response headers haven't already sent to browser.
        if ( ! headers_sent() ) {
            header( 'Content-Type: application/json; charset=utf-8' );
            status_header( $public_status[ 0 ] );
        }

        // Create response body.
        $public_output[ 'ok' ] = ( $public_status[ 0 ] < 300 ) ? true : false;
        $public_output[ 'output' ] = $public_status[ 1 ];

        // PHPMailer debug ($mail->SMTPDebug) gets dumped to output buffer
        // and breaks JSON response. Using ob_clean() before output prevents this.
        ob_clean();
        echo json_encode( $public_output );
    }

}//Class end