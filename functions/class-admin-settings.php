<?php
namespace Jefferson\HB_Contact_Form;

/**
 * Herringbone Contact Form Admin Settings.
 *
 * Hook into the WP admin area and add menu options and settings
 * pages.
 *
 * @package Herringbone
 * @subpackage HB_Contact_Form
 * @author Jefferson Real <me@jeffersonreal.com>
 * @copyright Copyright (c) 2021, Jefferson Real
 * @license GPL2+
 */

class Admin_Settings {


    public function __construct() {

        add_action( 'admin_menu', [ &$this, 'register_sub_menu' ], 99 );
        add_action( 'admin_init', [ &$this, 'page_setup' ] );
    }


    public $icon = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxMzIgMTMyIj48cGF0aCBkPSJNMCAwdjEzYzAgNSAwIDEwIDggMTNsNTggMjcgNTgtMjdjOC0zIDgtOCA4LTEzVjBMNzQgMjZjLTggNC04IDktOCAxNCAwLTUgMC0xMC04LTE0em0wIDQwdjEzYzAgNCAwIDEwIDggMTNsNTggMjcgNTgtMjdjOC0zIDgtOSA4LTEzVjQwTDc0IDY2Yy04IDQtOCA5LTggMTMgMC00IDAtOS04LTEzem0wIDM5djE0YzAgNCAwIDkgOCAxM2w1OCAyNiA1OC0yNmM4LTQgOC05IDgtMTNWNzlsLTU4IDI3Yy04IDMtOCA5LTggMTMgMC00IDAtMTAtOC0xM3oiLz48L3N2Zz4=';


    /**
     * Add Herringbone admin menu option to sidebar
     */
    public function register_sub_menu() {
        add_submenu_page(
            'herringbone-settings',             //parent_slug
            'Contact Form Settings',            //page_title
            'Contact Form',                     //menu_title
            'manage_options',                   //capability
            'contact-form-settings',            //menu_slug
            [ &$this, 'create_settings_page' ], //function
            null,                               //position
        );
    }


    /**
     * Create Contact Form Settings Page
     */
    public function create_settings_page() {
    ?>
        <div class="wrap">
            <h1>
                <span>
                    <img style="max-height: 1em;margin-right: 0.5em;vertical-align: middle;" src="
                        <?php echo $this->icon ?>"
                    />
                </span>
                Herringbone Contact Form Settings
            </h1>

            <h2>SMTP Account Settings</h2>

            <form method="post" action="options.php">
            <?php

                echo '<table class="form-table">';
                    settings_fields( 'group_smtp' );
                    do_settings_fields( 'contact-form-settings', 'group_smtp' );
                echo '</table>';

                submit_button( 'Save SMTP Settings' );
                ?>
            </form>
        </div>

        <div class="wrap">

            <h2>Message Headers</h2>

            <form method="post" action="options.php">
            <?php

                echo '<table class="form-table">';
                    settings_fields( 'group_headers' );
                    do_settings_fields( 'contact-form-settings', 'group_headers' );
                echo '</table>';


                //do_settings_sections( 'contact-form-settings' );

                submit_button( 'Save Message Settings' );
            ?>
            </form>
        </div>
    <?php
    }


    /**
     * Form Fields - SMTP Account Settings
     */
    public function echo_field_username() {
        echo '<input type="text" name="username" id="username" value="' . get_option('username') . '" >';
    }
    public function echo_field_password() {
        echo '<input type="password" name="password" id="password" value="' . get_option('password') . '" >';
    }
    public function echo_field_host() {
        echo '<input type="text" name="host" id="host" value="' . get_option('host') . '" >';
    }
    public function echo_field_port() {
        echo '<input type="number" min="1" max="65535" step="1" name="port" id="port" value="' . get_option('port') . '" >';
    }
    public function echo_field_auth() {
        echo '<input type="checkbox" name="auth" id="auth" value="1"' . checked( '1', get_option('auth') ) . '>';
        echo '<label for="auth">Tick if your SMTP provider requires authentication.</label>';
    }


    /**
     * Form Fields - Message Header Settings
     */
    public function echo_intro_group_headers() {
        echo '<p>These email addresses can be set to anything, however, be aware that setting <b>sent from</b> to an address that doesn&apos;t match the SMTP domain will likely cause mail to be spam-filtered.</p>';
    }

    public function echo_field_recipient_email() {
        echo '<input type="email" name="recipient_email" id="recipient_email" value="' . get_option( 'recipient_email', get_bloginfo( 'admin_email' ) ) . '">';
    }
    public function echo_field_sent_from() {
        echo '<input type="email" name="sent_from" id="sent_from" value="' . get_option( 'sent_from', get_bloginfo( 'admin_email' ) ) . '">';
    }


    /**
     * Register all settings fields and call their functions to build the page.
     */
    public function page_setup() {


        //Form Fields - SMTP Account Settings
        add_settings_section( 'group_smtp', 'SMTP Account', null, 'contact-form-settings' );

            add_settings_field( 'username', 'Username', [ &$this, 'echo_field_username' ], 'contact-form-settings', 'group_smtp' );
            register_setting( 'group_smtp', 'username' );

            add_settings_field( 'password', 'Password', [ &$this, 'echo_field_password' ], 'contact-form-settings', 'group_smtp' );
            register_setting( 'group_smtp', 'password' );

            add_settings_field( 'host', 'Host', [ &$this, 'echo_field_host' ], 'contact-form-settings', 'group_smtp' );
            register_setting( 'group_smtp', 'host' );

            add_settings_field( 'port', 'Port', [ &$this, 'echo_field_port' ], 'contact-form-settings', 'group_smtp' );
            register_setting( 'group_smtp', 'port' );

            add_settings_field( 'auth', 'Authentication', [ &$this, 'echo_field_auth' ], 'contact-form-settings', 'group_smtp' );
            register_setting( 'group_smtp', 'auth' );


        //Form Fields - Message Header Settings
        add_settings_section( 'group_headers', 'Message Headers', [ &$this, 'echo_intro_group_headers' ], 'contact-form-settings' );

            add_settings_field( 'recipient_email', 'Recipient Email Address', [ &$this, 'echo_field_recipient_email' ], 'contact-form-settings', 'group_headers' );
            register_setting( 'group_headers', 'recipient_email' );

            add_settings_field( 'sent_from', 'Sent-from Email Address', [ &$this, 'echo_field_sent_from' ], 'contact-form-settings', 'group_headers' );
            register_setting( 'group_headers', 'sent_from' );

    }


}// Class end