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
use function get_option;
use function is_email;
use function wp_strip_all_tags;
use function wp_kses;
use function plugin_dir_path;

// Load Composer's autoloader
require plugin_dir_path( __DIR__ ) . 'vendor/autoload.php';


class SMTP_Send {


    /**
     * Holds submitted form values passed by Form_Receiver.
     */
    private $form_values;


    /**
     * Holds contact form databse options.
     */
    private $options = array(
        'username' => '',
        'password' => '',
        'host' => '',
        'port' => '',
        'auth' => '',
        'from_email' => '',
        'to_email' => '',
    );


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
     * Init the class by grabbing the saved options.
     * 
     * Performs initial validation to ensure no values are empty.
     */
    public function __construct( $form_values ) {
        
error_log( '__construct' );

        // form data passed by handler.
        $this->form_values = $form_values;

        $smtp_settings = Get_Settings::smtp();

        if ( $smtp_settings ) {
            
            //send email

        } else {

            //smtp settings are bad
            return;
        }
    }


    private function validate_options( $options ) {

        extract( $this->options );

        $valid = ( is_string( $username )
                && is_string( $password )
                && is_string( $host )
                && ( 1 <= (int)$port )
                && PHPMailer::validateAddress( $from_email )
                && PHPMailer::validateAddress( $to_email ) );

        if ( $valid ) {
            $this->compose_email( $options );

        } else {
                //settings failed validation.
                $this->respond( 'settings_invalid' );
                return;
        }
    }


    private function compose_email( $options ) {

error_log( 'compose_email' );

        $mail = new PHPMailer( true );

        extract( $this->options );
        extract( $this->form_values );

        // clean name
        $submitted_name = substr( strip_tags( $submitted_name ), 0, 255 );

        // Make sure address is valid
        if ( !PHPMailer::validateAddress( $submitted_email ) ) {
            respond( 'email_invalid' );
            return;
        }

        // Meta variables
        $site_url         = get_bloginfo( 'url' );
        $site_name        = get_bloginfo( 'name' );

        // Build plaintext email body
        $n = "\n";
        $plaintext  = "This message was sent via the contact form at {$site_url}";
        $plaintext .= "{$n}{$n}From: {$submitted_name}";
        $plaintext .= "{$n}E-mail: {$submitted_email}";
        $plaintext .= "{$n}{$n}{$submitted_message}";

        $plaintext_cleaned = wp_strip_all_tags( $plaintext );

        // Build html email body
        $html  = "<h3>This message was sent via the contact form at {$site_url}</h3>";
        $html .= "<table><tr>";
        $html .= "<td><b>From: </b>{$submitted_name}</td>";
        $html .= "<td><b>E-mail: </b>{$submitted_email}</td>";
        $html .= "<td><b>Message: </b><br><br>{$submitted_message}</td>";
        $html .= "</tr></table>";

        $html_cleaned = wp_kses( $html, $allowed_html_tags, [ 'http', 'https', 'ftp', 'ftps', 'mailto', 'tel', 'webcal' ] );
        $html_encoded = htmlentities( $html_cleaned, ENT_QUOTES | ENT_IGNORE, "UTF-8" );

        try {
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;              //Enable verbose debug output
            $mail->isSMTP();                                    //Send using SMTP
            $mail->Host       = $host;                          //Set the SMTP server to send through
            $mail->SMTPAuth   = (bool)$auth;                    //Enable SMTP authentication
            $mail->Username   = $username;                      //SMTP username
            $mail->Password   = $password;                      //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;    //Enable implicit TLS encryption
            $mail->Port       = $port;                          //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        
            //Recipients
            $mail->setFrom( $from_email, 'HB Mailbot');
            $mail->addAddress( $to_email, );
            $mail->addReplyTo( $submitted_email, $submitted_name );
        
            //Attachments
            //$mail->addAttachment('/var/tmp/file.tar.gz');
            //$mail->addAttachment('/tmp/image.jpg', 'mybutt.jpg'); //Optional name
        
            //Content
            $mail->isHTML(true);
            $mail->Subject = 'Message from: ' . $submitted_name . ' via ' . $site_url;
            $mail->Body    = $html_encoded;
            $mail->AltBody = $plaintext_cleaned;
        
            $mail->send();
            respond( 'success' );

        } catch (Exception $e) {

            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }


    private function respond( $response ) {

error_log( 'respond' );
error_log( $response );

        switch ( $response ) {

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
        }
    }


}//Class end