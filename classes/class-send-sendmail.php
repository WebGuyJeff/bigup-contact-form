<?php
namespace Bigup\Contact_Form;

/**
 * Bigup Contact Form - PHPMailer Handler.
 *
 * This template handles the construction of the email using values submitted
 * via the form, and sends the email via PHPMailer using Sendmail which must
 * be installed on the host server. By default Sendmail is installed on most
 * Linux platforms, so this is a good backup when SMTP isn't an available.
 *
 * @package bigup_contact_form
 * @author Jefferson Real <me@jeffersonreal.uk>
 * @copyright Copyright (c) 2023, Jefferson Real
 * @license GPL2+
 * @link https://jeffersonreal.uk
 * 
 */

// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// WordPress Dependencies
use function get_bloginfo;
use function wp_strip_all_tags;
use function plugin_dir_path;
use function get_site_url;

// Load Composer's autoloader
require plugin_dir_path( __DIR__ ) . 'vendor/autoload.php';


class Send_Sendmail {

    /**
     * Hold the settings retrieved from the database.
     */
    private $settings;

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

        $this->settings = Get_Settings::sendmail();
        if ( true === !! $this->settings ) {
            $this->settings_ok = true;
        }
    }


    /**
     * Compose and send an email.
     */
    public function compose_and_send_email( $form_data ) {

        $mail = new PHPMailer( true );

		extract( $this->settings );
        extract( $form_data[ 'fields' ] );

        // Meta variables
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

			// Check mail() exists (does not gaurantee an MTA is configured!)
			if ( ! function_exists( 'mail' ) ) {
				throw new Exception('Function "mail" is not available on this server.');
			}

            // Server settings.
            $mail->Debugoutput  = 'error_log'; // How to handle debug output
			//$mail->IsSendmail();             // Use the sendmail MTA.
			$mail->isMail();                   // *May work on Linux and Windows servers.

			// * see https://www.php.net/manual/en/function.mail.php

            // Recipients.
            $mail->setFrom( $from_email, $from_name); // Use fixed and owned address to pass SPF checks.
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


error_log( 'LOCAL' );		




            // Send it!
            $sent = $mail->send();
			if ( $sent ) {
				return [ 200, 'Message sent successfully.' ];
			} else {
				throw new Exception('Local mail server send failed.');
			}

        } catch ( Exception $e ) {

            //PHPMailer exceptions are not public-safe - Send to logs.
            error_log( 'Bigup_Contact_Form: ' . $mail->ErrorInfo );
            //Generic public error.
            return [ 500, 'Sending your message failed due to a webserver configuration error.' ];
        }
    }
}