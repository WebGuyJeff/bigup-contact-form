<?php
namespace Bigup\Contact_Form;

/**
 * Bigup Contact Form - PHPMailer Handler.
 *
 * This template handles the construction of the email using values submitted
 * via the form, and sends the email via PHPMailer using the SMTP account
 * configured by the user.
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

// WordPress Dependencies
use function get_bloginfo;
use function wp_strip_all_tags;
use function plugin_dir_path;
use function get_site_url;

// Load Composer's autoloader
require plugin_dir_path( __DIR__ ) . 'vendor/autoload.php';


class Send_SMTP {


    /**
     * Hold the SMTP account settings retrieved from the database.
     */
    private $smtp_settings;


    /**
     * Init the class by grabbing the saved options.
     * 
     * Prepares SMTP settings and form data to pass to compose_email.
     * Form data is passed by handler.
     */
    public function __construct() {
        $this->smtp_settings = Get_Settings::smtp();
    }


    /**
     * Compose and send an SMTP email.
     */
    public function compose_and_send_email( $form_data ) {

		// Check settings are ready.
        if ( false === !! $this->smtp_settings || true === $this->smtp_settings[ 'use_local_mail_server' ] ) {
			error_log( 'Bigup_Contact_Form: Invalid SMTP settings retrieved from database.' );
			return [ 500, 'Sending your message failed due to a bad local mailserver configuration.' ];
        }

        $mail = new PHPMailer( true );

        extract( $this->smtp_settings );
        extract( $form_data[ 'fields' ] );

        $site_url  = get_bloginfo( 'url' );
		$site_name = get_bloginfo( 'name' );
        $from_name = ( $site_name ) ? $site_name : 'Bigup Contact Form';

// Build plaintext email body
$plaintext = <<<PLAIN
This message was sent via the contact form at $site_url.

From: $name
E-mail: $email
Message:

$message

You are viewing the plaintext version of this email because you have
disallowed HTML content in your email client. To view this and any future
messages from this sender in complete HTML formatting, try adding the sender
domain to your spam filter whitelist.
PLAIN;
$plaintext_cleaned = wp_strip_all_tags( $plaintext );

// Build html email body
$html = <<<HTML
<table>
    <tr>
        <td height="60px">
            <i>This message was sent via the contact form at $site_url</i>
        </td>
    </tr>
    <tr>
        <td>
            <b>Name: </b>$name
        </td>
    </tr>
    <tr>
        <td>
            <b>Email: </b>$email
        </td>
    </tr>
    <tr>
        <td>
            <br>
            <b>Message: </b>
            <br>
            <br>$message
        </td>
    </tr>
</table>
HTML;

        // Make sure PHP server script limit is higher than mailer timeout!
        set_time_limit( 60 );
		// Ensure PHP time zone is set as SMTP requires accurate times.
		date_default_timezone_set( 'UTC' );

        try {

			// Server settings.

			$port = (int)$port;
			// SMTPS/STARTTLS (ssl/tls).
			if ( $port !== 25 && $port !== 2525 ) {
				$mail->SMTPSecure = ( $port === 465 ) ? 'ssl' : 'tls';
			}
            $mail->SMTPDebug    = SMTP::DEBUG_SERVER; // Debug level: DEBUG_[OFF/SERVER/CONNECTION]
            $mail->Debugoutput  = 'error_log';        // How to handle debug output
			$mail->Helo         = get_site_url();     // Sender's FQDN to identify as
			$mail->isSMTP();                          // Use SMTP
            $mail->Host         = $host;              // SMTP server to send through
            $mail->SMTPAuth     = (bool)$auth;        // Enable SMTP authentication
            $mail->Username     = $username;          // SMTP username
            $mail->Password     = $password;          // SMTP password
            $mail->Port         = $port;              // TCP port
            $mail->Timeout      = 6;                  // Connection timeout (secs)
            $mail->getSMTPInstance()->Timelimit = 8;  // Time allowed for each SMTP command response

            // Recipients.
            $mail->setFrom( $from_email, $from_name); // Use fixed and owned SMTP account address to pass SPF checks.
            $mail->addAddress( $to_email, );
            $mail->addReplyTo( $email, $name );

            // Content.
			$mail->isHTML(true);
            $mail->Subject = 'New website message from ' . $site_url;
			$mail->Body    = $html;
            $mail->AltBody = $plaintext_cleaned;

			// File attachments.
			if ( array_key_exists( 'files', $form_data ) ) {
				foreach ( $form_data[ 'files' ] as $file ) {
					$mail->AddAttachment( $file[ 'tmp_name' ], $file[ 'name' ] );
				}
			}

            // Gotime.
            $sent = $mail->send();
			if ( $sent ) {
				return [ 200, 'Message sent successfully.' ];
			} else {
				throw new Exception( 'SMTP Error: ' . $mail->ErrorInfo );
			}

        } catch ( Exception $e ) {

            //PHPMailer exceptions are not public-safe - Send to logs.
            error_log( 'Bigup_Contact_Form: ' . $mail->ErrorInfo );
            //Generic public error.
            return [ 500, 'Sending your message failed while connecting to the mail server.' ];
		}
    }
}