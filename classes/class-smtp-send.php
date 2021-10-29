<?php
namespace Jefferson\HB_Contact_Form;

/**
 * Herringbone Contact Form PHPMailer Handler.
 *
 * This template handles the construction of the email using values submitted
 * via the form, and sends the email via PHPMailer using the SMTP account
 * configured by the user.
 *
 * @package Herringbone
 * @subpackage HB_Contact_Form
 * @author Jefferson Real <me@jeffersonreal.com>
 * @copyright Copyright (c) 2021, Jefferson Real
 * @license GPL2+
 */

// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// WordPress Dependencies
use function get_bloginfo;
use function wp_strip_all_tags;
use function plugin_dir_path;

// Load Composer's autoloader
require plugin_dir_path( __DIR__ ) . 'vendor/autoload.php';


class SMTP_Send {


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

        if ( $this->smtp_settings ) {
            $this->settings_ok = true;
        }
    }


    /**
     * Compose and send an SMTP email.
     */
    public function compose_and_send_smtp_email( $email_values ) {

        $mail = new PHPMailer( true );

        extract( $this->smtp_settings );
        extract( $email_values );

        // Meta variables
        $site_url = get_bloginfo( 'url' );

        // Build plaintext email body
        $n = "\n";
        $plaintext  = "This message was sent via the contact form at {$site_url}";
        $plaintext .= "{$n}{$n}From: {$name}";
        $plaintext .= "{$n}E-mail: {$email}";
        $plaintext .= "{$n}{$n}{$message}";

        $plaintext_cleaned = wp_strip_all_tags( $plaintext );

        // Build html email body
        $html  = "<h3>This message was sent via the contact form at {$site_url}</h3>";
        $html .= "<table><tr>";
        $html .= "<td><b>From: </b>{$name}</td>";
        $html .= "<td><b>E-mail: </b>{$email}</td>";
        $html .= "<td><b>Message: </b><br><br>{$message}</td>";
        $html .= "</tr></table>";

        $html_encoded = htmlentities( $html, ENT_QUOTES | ENT_IGNORE, "UTF-8" );

        try {
            //Server settings
            $mail->SMTPDebug    = SMTP::DEBUG_OFF;           //debug output level: DEBUG_[OFF/SERVER/CONNECTION]
            $mail->Debugoutput  = 'error_log';
            $mail->isSMTP();                                 //Use SMTP
            $mail->Host         = $host;                       //SMTP server to send through
            $mail->SMTPAuth     = (bool)$auth;                 //Enable SMTP authentication
            $mail->Username     = $username;                   //SMTP username
            $mail->Password     = $password;                   //SMTP password
            $mail->SMTPSecure   = PHPMailer::ENCRYPTION_SMTPS; //Enable implicit TLS encryption
            $mail->Port         = $port;                       //TCP port
        
            //Recipients
            $mail->setFrom( $from_email, 'Mailer'); //Use fixed address in your domain to pass SPF checks.
            $mail->addAddress( $to_email, );
            $mail->addReplyTo( $email, $name );

            //Content
            $mail->isHTML(true);
            $mail->Subject = 'Message from: ' . $name . ' via ' . $site_url;
            $mail->Body    = $html_encoded;
            $mail->AltBody = $plaintext_cleaned;
        
            //Gotime
            $mail->send();
            return [ 200, 'Message sent successfully.' ];

        } catch ( Exception $e ) {

            //PHPMailer exceptions are not public-safe - Send to logs.
            error_log( 'HB_Contact_Form: ' . $mail->ErrorInfo );
            //Generic public error.
            return [ 500, 'Something went wrong and your message could not be sent.' ];
        }
    }


}//Class end