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
            'herringbone-settings',                   //parent_slug
            'Contact Form Settings',                  //page_title
            'Contact Form',                           //menu_title
            'manage_options',                         //capability
            'contact-form-settings',                  //menu_slug
            [ &$this, 'contact_form_settings_page' ], //function
            null,                                     //position
        );
    }


    /**
     * Create Contact Form Settings Page
     */
    public function contact_form_settings_page() { ?>
        <div class="wrap">
            <h1>Herringbone Contact Form Settings</h1>
            <form method="post" action="options.php">
                    <?php
                            settings_fields( 'section' );
                            do_settings_sections( 'contact_form_options' );
                            submit_button();
                    ?>
            </form>
        </div>
    <?php }

    /**
     * Add options fields to the admin page
     */

    public function setting_username() {
        echo '<input type="text" name="username" id="username" value="' . get_option('username') . '" />';
    }

    public function setting_password() {
        echo '<input type="text" name="password" id="password" value="' . get_option('password') . '" />';
    }

    public function setting_recipient_email() {
        echo '<input type="text" name="recipient_email" id="recipient_email" value="' . get_option('recipient_email') . '" />';
    }



    /**
     * Tell WordPress to build the admin page
     */
    public function page_setup() {
        add_settings_section( 'section', 'SMTP Account', null, 'contact_form_options' );
        add_settings_field( 'username', 'Username', [ &$this, 'setting_username' ], 'contact_form_options', 'section' );
        add_settings_field( 'password', 'Password', [ &$this, 'setting_password' ], 'contact_form_options', 'section' );
        add_settings_field( 'recipient_email', 'Recipient Email', [ &$this, 'setting_recipient_email' ], 'contact_form_options', 'section' );

        register_setting( 'section', 'username' );
        register_setting( 'section', 'password' );
        register_setting( 'section', 'recipient_email' );
    }

}// Class end