<?php
namespace Jefferson\HB_Contact_Form;

/**
 * Herringbone Contact Form Admin Settings.
 *
 * Hook into the WP admin area and add menu options and settings
 * pages.
 * 
 * ###########
 * # WARNING #
 * ###########
 * 
 * To add multiple sections to the same settings page, all settings registered
 * for that page MUST BE IN THE SAME 'OPTION GROUP'. In the register_setting
 * function call this is the first argument as follows:
 * 
 * register_setting( 'option_group', 'sent_from' );
 *
 * @package Herringbone
 * @subpackage HB_Contact_Form
 * @author Jefferson Real <me@jeffersonreal.com>
 * @copyright Copyright (c) 2021, Jefferson Real
 * @license GPL2+
 */

class Admin_Settings {


    /**
     * Settings group name called by settings_fields().
     */
    public $group_name = 'group_contact_form_settings';


    /**
     * Settings page slug to add with add_submenu_page().
     */
    public $page_slug = 'contact-form-settings';

    /**
     * base64 uri svg icon used next to page title.
     */
    public $icon = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxMzIgMTMyIj48cGF0aCBkPSJNMCAwdjEzYzAgNSAwIDEwIDggMTNsNTggMjcgNTgtMjdjOC0zIDgtOCA4LTEzVjBMNzQgMjZjLTggNC04IDktOCAxNCAwLTUgMC0xMC04LTE0em0wIDQwdjEzYzAgNCAwIDEwIDggMTNsNTggMjcgNTgtMjdjOC0zIDgtOSA4LTEzVjQwTDc0IDY2Yy04IDQtOCA5LTggMTMgMC00IDAtOS04LTEzem0wIDM5djE0YzAgNCAwIDkgOCAxM2w1OCAyNiA1OC0yNmM4LTQgOC05IDgtMTNWNzlsLTU4IDI3Yy04IDMtOCA5LTggMTMgMC00IDAtMTAtOC0xM3oiLz48L3N2Zz4=';


    /**
     * Init the class by hooking into the admin interface.
     */
    public function __construct() {
        add_action( 'admin_menu', [ &$this, 'register_sub_menu' ], 99 );
        add_action( 'admin_init', [ &$this, 'register_settings' ] );
    }


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

        <h1>
            <span>
                <img style="max-height: 1em;margin-right: 0.5em;vertical-align: middle;" src="
                    <?php echo $this->icon ?>"
                />
            </span>
            Herringbone Contact Form Settings
        </h1>

        <div class="wrap">
            <form method="post" action="options.php">

                <?php
                    /* Setup hidden input functionality */
                    settings_fields( $this->group_name );

                    /* Print the input fields */
                    do_settings_sections( $this->page_slug );

                    /* Print the submit button */
                    submit_button( 'Save' );
                ?>

            </form>
        </div>

    <?php
    }


    /**
     * Form Fields - SMTP Account Settings
     */
    public function echo_field_username() {
        echo '<input type="text" name="username" id="username" value="' . get_option('username') . '" required>';
    }
    public function echo_field_password() {
        echo '<input type="password" name="password" id="password" value="' . get_option('password') . '" required>';
    }
    public function echo_field_host() {
        echo '<input type="text" name="host" id="host" value="' . get_option('host') . '" required>';
    }
    public function echo_field_port() {
        echo '<input type="number" min="1" max="65535" step="1" name="port" id="port" value="' . get_option('port') . '" required>';
    }
    public function echo_field_auth() {
        echo '<input type="checkbox" name="auth" id="auth" value="1"' . checked( '1', get_option('auth'), false ) . '>';
        echo '<label for="auth">Tick if your SMTP provider requires authentication.</label>';
    }


    /**
     * Form Fields - Message Header Settings
     */
    public function echo_intro_section_headers() {
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
     * 
     * add_settings_section( $id, $title, $callback, $page )
     * add_settings_field( $id, $title, $callback, $page, $section, $args )
     * register_setting( $option_group, $option_name, $sanitize_callback )
     */
    public function register_settings() {

        $group = $this->group_name;
        $page = $this->page_slug;


        /**
         * Register section and fields - SMTP Account Settings
         */
        $section = 'section_smtp';
        add_settings_section( $section, 'SMTP Account', null, $page );

            add_settings_field( 'username', 'Username', [ &$this, 'echo_field_username' ], $page, $section );
            register_setting( $group, 'username', 'sanitize_text_field' );

            add_settings_field( 'password', 'Password', [ &$this, 'echo_field_password' ], $page, $section );
            register_setting( $group, 'password', 'sanitize_text_field' );

            add_settings_field( 'host', 'Host', [ &$this, 'echo_field_host' ], $page, $section );
            register_setting( $group, 'host', [ &$this, 'validate_domain' ] );

            add_settings_field( 'port', 'Port', [ &$this, 'echo_field_port' ], $page, $section );
            register_setting( $group, 'port', [ &$this, 'validate_port' ] );

            add_settings_field( 'auth', 'Authentication', [ &$this, 'echo_field_auth' ], $page, $section );
            register_setting( $group, 'auth', [ &$this, 'validate_checkbox' ] );


        /**
         * Register section and fields - Message Header Settings
         */
        $section = 'section_headers';
        add_settings_section( $section, 'Message Headers', [ &$this, 'echo_intro_section_headers' ], $page );

            add_settings_field( 'recipient_email', 'Recipient Email Address', [ &$this, 'echo_field_recipient_email' ], $page, $section );
            register_setting( $group, 'recipient_email', 'sanitize_email' );

            add_settings_field( 'sent_from', 'Sent-from Email Address', [ &$this, 'echo_field_sent_from' ], $page, $section );
            register_setting( $group, 'sent_from', 'sanitize_email' );

    }

    /**
     * Validate a domain name.
     */
    function validate_domain( $domain ) {
 
        $ip = gethostbyname( $domain );
        $ip = filter_var( $ip, FILTER_VALIDATE_IP );

        if ( $domain == '' || $domain == null ) {
            return '';
        } elseif ( $ip ) {
            return $domain;
        } else {
            return 'INVALID DOMAIN';
        }
    }

    /**
     * Validate a port number.
     */
    function validate_port( $port ) {

        $port = ( is_string( $port ) ) ? (int)$port : $port;

        if ( is_int( $port )
            && $port >= 1
            && $port <= 65535 ) {
            return $port;
        } else {
            return '';
        }
    }

    /**
     * Validate a checkbox.
     */
    function validate_checkbox( $checkbox ) {

        $checkbox_ok = ( 1 == $checkbox ) ? $checkbox : null;
        return $checkbox_ok;
    }

}// Class end
