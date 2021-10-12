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

// PHPMailer Library Dependency
require_once "Mail.php";

// WordPress Dependencies
use function get_bloginfo;

class SMTP_Send {

    public function compile_email() {


        // Local variables
        $username = //db setting
        $password = //d bsetting
        $site_url = get_bloginfo( 'url' );
        $admin_email = get_bloginfo( 'admin_email' );

        // Specify the recipient email of form entries
        $to = $admin_email;

        // Assign form contents to variables
        $field_name = $_POST[ 'phpInputName' ];
        $field_email = $_POST[ 'phpInputEmail' ];
        $field_message = $_POST[ 'phpInputMessage' ];

        // Build email content from form variables
        $body_message = "[ This message was submitted via the contact form at " . $site_url . " ]\n\n";
        $body_message .= 'From: '.$field_name."\n";
        $body_message .= 'E-mail: '.$field_email."\n\n";
        $body_message .= 'Message: '.$field_message;
        $body = $body_message;

        // Declare mail headers
        $headers = array(
            'From' => $to,
            'To' => $to,
            'Subject' => 'Message from: ' . $field_name . ' via ' . $site_url,
            'Reply-To' => $field_email
        );

        // SMTP Outlook Settings
        $smtp = Mail::factory( 'smtp', array(
            'host' => 'smtp.office365.com',
            'port' => '587',
            'auth' => true,
            'username' => $username,
            'password' => $password
        ) );

        // Compose and send email
        $mail = $smtp->send( $to, $headers, $body );
        $response = array( "result" => "success" );
        echo json_encode( $response );

    }


}//Class end