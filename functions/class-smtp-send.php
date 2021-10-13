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

//Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require 'vendor/autoload.php';

//https://github.com/PHPMailer/PHPMailer



// PHPMailer Library Dependency
require_once "Mail.php";

// WordPress Dependencies
use function get_bloginfo;

class SMTP_Send {

    public function compile_email() {

        // Assign form contents to variables
        $name = $_POST[ 'phpInputName' ];
        $email = $_POST[ 'phpInputEmail' ];
        $message = $_POST[ 'phpInputMessage' ];

        // Local variables
        $smtp_username = //db setting
        $smtp_password = //db setting
        $smtp_host     = 'smtp.office365.com';
        $smtp_port     = '587';
        $smtp_auth     = true;

        $site_url         = get_bloginfo( 'url' );
        $site_name        = get_bloginfo( 'name' );
        $site_admin_email = get_bloginfo( 'admin_email' );

        // Specify the recipient email of form entries
        $headers_from     = //db setting || $site_admin_email;
        $headers_to       = //db setting || $site_admin_email;
        $headers_subject  = 'Message from: ' . $name . ' via ' . $site_url;
        $headers_reply_to = $email;

        // Build email content from form inputs
        $n = "\n";
        $body_message  = "This message was sent via the contact form at {$site_url}";
        $body_message .= "{$n}{$n}From: {$name}";
        $body_message .= "{$n}E-mail: {$email}";
        $body_message .= "{$n}{$n}{$message}";
        $body = $body_message;

        // Declare mail headers
        $headers = array(
            'To'        => $headers_to,
            'From'      => $headers_from,
            'Reply-To'  => $headers_reply_to,
            'Subject'   => $headers_subject
        );

        // Declare SMTP account settings
        $smtp = Mail::factory( 'smtp', array(
            'host'      => $smtp_host,
            'port'      => $smtp_port,
            'auth'      => $smtp_auth,
            'username'  => $smtp_username,
            'password'  => $smtp_password
        ) );

        // Compose/send email and send a response to caller (front end js)
        $mail = $smtp->send( $headers_to, $headers, $body );
        $response = array( "result" => "success" );
        echo json_encode( $response );

    }


}//Class end