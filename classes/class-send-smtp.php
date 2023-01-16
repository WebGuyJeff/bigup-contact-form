<?php
namespace Bigup\Contact_Form;

/**
 * Bigup Contact Form - PHPMailer Handler.
 *
 * This template handles the construction of the email using values submitted
 * via the form, and sends the email via PHPMailer using the SMTP account
 * configured by the user.
 *
 * @package bigup_contact_form
 * @author Jefferson Real <me@jeffersonreal.uk>
 * @copyright Copyright (c) 2021, Jefferson Real
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
     * A checkable boolean to indicate settings are valid and this class is ok to run.
     */
    public $settings_ok;

    /**
     * Init the class by grabbing the saved options.
     * 
     * Prepares SMTP settings and form data to pass to compose_email.
     * Form data is passed by handler.
     */
    public function __construct() {
        
        $this->smtp_settings = Get_Settings::smtp();
        if ( true === !! $this->smtp_settings ) {
			if ( true === !! $this->smtp_settings[ 'use_sendmail' ] ) {
				error_log( 'Bigup_Contact_Form: Invalid attempt to use SMTP - "Use Sendmail" is true in settings.' );
				$this->settings_ok = false;
			}
            $this->settings_ok = true;
        }
    }


    /**
     * Compose and send an SMTP email.
     */
    public function compose_and_send_smtp_email( $form_data ) {

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

        try {
            // Server settings.
            $mail->SMTPDebug    = SMTP::DEBUG_SERVER;          // Debug level: DEBUG_[OFF/SERVER/CONNECTION]
            $mail->Debugoutput  = 'error_log';                 // How to handle debug output
			$mail->Helo         = get_site_url();              // Sender's FQDN to identify as
			$mail->isSMTP();                                   // Use SMTP
            $mail->Host         = $host;                       // SMTP server to send through
            $mail->SMTPAuth     = (bool)$auth;                 // Enable SMTP authentication
            $mail->Username     = $username;                   // SMTP username
            $mail->Password     = $password;                   // SMTP password
            $mail->SMTPSecure   = PHPMailer::ENCRYPTION_SMTPS; // TLS: Implicit/Explicit SMTPS/STARTTLS
            $mail->Port         = $port;                       // TCP port
            $mail->Timeout      = 6;                           // Connection timeout (secs)
            $mail->getSMTPInstance()->Timelimit = 8;           // Time allowed for each SMTP command response

            // Recipients.
            $mail->setFrom( $from_email, $from_name); // Use fixed and owned SMTP account address to pass SPF checks.
            $mail->addAddress( $to_email, );
            $mail->addReplyTo( $email, $name );

            // Content.
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
            $mail->send();
            return [ 200, 'Message sent successfully.' ];

        } catch ( Exception $e ) {

            //PHPMailer exceptions are not public-safe - Send to logs.
            error_log( 'Bigup_Contact_Form: ' . $mail->ErrorInfo );
            //Generic public error.
            return [ 500, 'Sending your message failed while connecting to the mail server.' ];
        }
    }


}//Class end