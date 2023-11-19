<?php
namespace Bigup\Contact_Form;

/**
 * Bigup Contact Form - PHPMailer SMTP Test.
 *
 * Test the SMTP account settings provided to ensure a connection
 * can be established. Otherwise the settings are invalid and the
 * form should not be displayed to users.
 *
 * @package bigup-contact-form
 * @author Jefferson Real <me@jeffersonreal.uk>
 * @copyright Copyright (c) 2023, Jefferson Real
 * @license GPL2+
 * @link https://jeffersonreal.uk
 * 
 */

// Import PHPMailer classes into the global namespace.
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader.
require BIGUPCF_PATH . 'vendor/autoload.php';

class SMTP_Test {

    /**
     * Perform a test connection to the SMTP server.
     * 
     */
    public static function server_connection( $username, $password, $host, $port, $auth ) {

		$test              = array();
		$test[ 'filled' ]  = array();
		$test[ 'empty' ]   = array();
		$test[ 'invalid' ] = array();

		$settings = array(
			'username' => $username,
			'password' => $password,
			'host'     => $host,
			'port'     => (int)$port,
			'auth'     => (bool)$auth
		);

		// Sort exisitng and empty.
		foreach ( $settings as $key => $value ) {
			if ( isset( $key ) ) {
				$test[ 'filled' ][] = $key;
			} else {
				// auth is allowed to be null.
				if ( $key !== 'auth' ) {
					$test[ 'empty' ][] = $key;
				}
			}
		}

		// Ensure PHP time zone is set as SMTP requires accurate times.
		date_default_timezone_set( 'UTC' );
        // Create a new PHPMailer instance.
		$mail = new PHPMailer( true );

        // Perform tests...
		try {

			// Test if settings are incomplete or not provided.
			if ( ! $test[ 'filled' ] ) {
				// No settings exist - assume user has not configured or is using local mailer (pass).
				$test[ 'message' ] = 'No SMTP settings provided, so nothing to test.';
				$test[ 'pass' ]    = true;

			} elseif ( $test[ 'filled' ] && $test[ 'empty' ] ) {
				// Settings are incomplete, assume config as been attempted (fail).
				$test[ 'pass' ]    = false;
				$test[ 'invalid' ] = $test[ 'empty' ];
				throw new Exception( 'SMTP partial settings error: Provide all account settings and try again.' );
			}

			// DEBUG
            $mail->SMTPDebug    = SMTP::DEBUG_SERVER;          // Debug level: DEBUG_[OFF/SERVER/CONNECTION]
            $mail->Debugoutput  = 'error_log';                 // How to handle debug output


			// Setup mailer.

			// SMTPS/STARTTLS (ssl/tls).
			if ( $settings['port'] !== 25 && $settings['port'] !== 2525 ) {
				$mail->SMTPSecure = ( $settings['port'] === 465 ) ? 'ssl' : 'tls';
			}
			$mail->isSMTP();
			$mail->Helo     = gethostname();
			$mail->Host     = $settings['host'];
			$mail->Port     = $settings['port'];
			$mail->SMTPAuth = $settings['auth'];
			$mail->Username = $settings['username'];
			$mail->Password = $settings['password'];
			$mail->Timeout  = 10;
			$mail->getSMTPInstance()->Timelimit = 8;

			if ( ! $mail->smtpConnect() ) {
				$test[ 'invalid' ] = array( 'host', 'port', 'auth', 'username', 'password' );
				throw new Exception( 'SMTP connection error: ' . $mail->ErrorInfo );
			} else {
				$test[ 'pass' ]    = true;
				$test[ 'message' ] = 'SMTP test successful';
			}

		} catch ( Exception $e ) {
			//PHPMailer generated errors.
			$test[ 'pass' ]    = false;
			$exception         = $e->errorMessage(); // notice 'errorMessage' not 'getMessage'.
			$test[ 'message' ] = $exception ?? 'There was a problem connecting to the SMTP server, but no error was returned';
			$test[ 'invalid' ] = $exception ? $test[ 'invalid' ] : array( 'host', 'port', 'auth', 'username', 'password' );

		} catch ( \Exception $e ) { // The leading slash means the Global PHP Exception class will be caught.
			// Errors from anything else.
			$exception         = $e->getMessage();
			$test[ 'pass' ]    = false;
			$test[ 'message' ] = $exception ?? 'There was a problem connecting to the SMTP server, but no error was returned';
			$test[ 'invalid' ] = $exception ? $test[ 'invalid' ] : array( 'host', 'port', 'auth', 'username', 'password' );
		}

		// Close the connection.
		$mail->smtpClose();

		if ( ! isset($test[ 'pass' ]) ) {
			$test[ 'pass' ]    = false;
			$test[ 'message' ] = 'Unknown result';
		}

		return $test;
    }
}
