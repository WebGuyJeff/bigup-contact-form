<?php
namespace Bigup\Contact_Form;

/**
 * Bigup Contact Form - PHPMailer SMTP Test.
 *
 * Test the SMTP account settings provided to ensure a connection
 * can be established. Otherwise the settings are invalid and the
 * form should not be displayed to users.
 *
 * @package bigup_contact_form
 * @author Jefferson Real <me@jeffersonreal.com>
 * @copyright Copyright (c) 2021, Jefferson Real
 * @license GPL2+
 * @link https://jeffersonreal.com
 * 
 */

// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// WordPress Dependencies
use function plugin_dir_path;

// Load Composer's autoloader
require plugin_dir_path( __DIR__ ) . 'vendor/autoload.php';


class SMTP_Test {


    /**
     * Perform a connection to the SMTP server.
     * 
     */
    public static function server_connection( $username, $password, $host, $port, $auth ) {

        //Create a new SMTP instance
        $smtp = new SMTP();

        //Enable connection-level debug output
        $smtp->do_debug = SMTP::DEBUG_CONNECTION;
        $smtp_connection_ok = false;

        try {
            //Connect to an SMTP server
            if (!$smtp->connect('mail.example.com', 25)) {
                throw new Exception('Connect failed');
            }
            //Say hello
            if (!$smtp->hello(gethostname())) {
                throw new Exception('EHLO failed: ' . $smtp->getError()['error']);
            }
            //Get the list of ESMTP services the server offers
            $e = $smtp->getServerExtList();
            //If server can do TLS encryption, use it
            if (is_array($e) && array_key_exists('STARTTLS', $e)) {
                $tlsok = $smtp->startTLS();
                if (!$tlsok) {
                    throw new Exception('Failed to start encryption: ' . $smtp->getError()['error']);
                }
                //Repeat EHLO after STARTTLS
                if (!$smtp->hello(gethostname())) {
                    throw new Exception('EHLO (2) failed: ' . $smtp->getError()['error']);
                }
                //Get new capabilities list, which will usually now include AUTH if it didn't before
                $e = $smtp->getServerExtList();
            }
            //If server supports authentication, do it (even if no encryption)
            if (is_array($e) && array_key_exists('AUTH', $e)) {
                if ($smtp->authenticate('username', 'password')) {
                    echo 'Connected ok!';
                } else {
                    throw new Exception('Authentication failed: ' . $smtp->getError()['error']);
                }
            }
        } catch (Exception $e) {
            echo 'SMTP error: ' . $e->getMessage(), "\n";
        }
        //Whatever happened, close the connection.
        $smtp->quit();
    }


}//Class end