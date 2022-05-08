<?php
namespace Bigup\Contact_Form;

/**
 * Bigup Contact Form - Admin Settings.
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
 * register_setting( 'option_group', 'from_email' );
 *
 * @package bigup_contact_form
 * @author Jefferson Real <me@jeffersonreal.uk>
 * @copyright Copyright (c) 2021, Jefferson Real
 * @license GPL2+
 * @link https://jeffersonreal.uk
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
    public $icon = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMzIiIGhlaWdodD0iMTMyIj48cGF0aCBmaWxsPSJjdXJyZW50Q29sb3IiIGQ9Ik0wIDB2MTMyaDM1LjRWODcuMmMwLTUuNiAwLTExLjYgMS43LTE2LjcuOC0yLjUgNC40LTMuNyA3LjEtMy43aDM0LjVjMy4yIDAgNi45LjEgOC4yIDEuMiAyLjMgMS44IDEuOSA3LjIgMi4xIDEwLjUuNCA0LjkgMSAxNC4yLS41IDE1LjYtMy4zIDMuNC0yLjggNC05LjIgMTAuMS0xLjggMS40LTYtLjktNS4zLTQuNC43LTMuNiAzLjQtOS43IDMuNC0xMS40IDAtMS43LTIgLjgtMi44IDAtLjMtLjQtLjYtLjktLjgtMS42LS43LTIuNCA0LjgtNy43IDQuMi04LjgtLjktMS4zLTQuMyA3LTYuNCA1LS42LS41LTIuMS00LjktMi44LTUtMSAwIDEuOCA0LjguOCA3LjktLjcgMi0zLjIgMi44LTUuMiAzLTIuNi41LTEzLjMtMTAuMS0xNC05LjUtLjguNyAxMC44IDEwLjcgMTIuNCAxNCAxLjMgMi4xIDIuMyA3LjUgMS43IDguMS0uNi43LTEwLjktNC05LjItMS41IDEuOCAyLjYgMTAgMy4yIDEzLjYgMy44IDEuMS4yIDMgLjEgNC42IDIuNS4zLjQtMi42LS40LTUuMy0xLTIuNi0uMy01LjQtMS01LjktLjgtLjcuNSAyIDMuMiAyLjggMy40IDEuMS40IDExLjUtLjUgMTIuMi0uNyAyLjgtMSAzLjktMS42IDQuMy0yIDUuOC02LjcgOS40LTkgOS42LTEyLjEuMi0zLjEtLjQtMTMgMi4zLTE0LjggMi42LTEuOCA1LjMuMSA2LjUgNS44IDEuMiA1LjcgMy40IDUuNiA0LjQgMTAuOCAxIDUuMi0zLjMgMTUuOS01LjYgMjEuOS0yLjIgNi03LjQgNy42LTEwLjYgOS42LTMuMyAyLTYuNyAzLjUtMTAuOCA0LjMtMi45LjYtNy41IDEuMS05LjkgMS4zSDEzMlYwSDY2czcuNC41IDExLjQgMS4zUzg1IDMuNyA4OC4yIDUuN2MzLjIgMiA4LjQgMy42IDEwLjYgOS42IDIuMyA2IDYuNyAxNi42IDUuNiAyMS44LTEgNS4zLTMuMiA1LjEtNC40IDEwLjgtMS4yIDUuNy0zLjkgNy43LTYuNSA1LjktMi43LTEuOS0yLjEtMTEuOC0yLjMtMTQuOC0uMi0zLjEtMy44LTUuNS05LjYtMTIuMS0uNC0uNS0xLjUtMS4xLTQuMy0yLS43LS4yLTExLTEuMi0xMi4yLS44LS44LjItMy41IDMtMi44IDMuNC41LjMgMy4zLS40IDUuOS0uOSAyLjctLjQgNS42LTEuMyA1LjMtLjlDNzIgMjguMSA3MCAyOCA2OSAyOC4yYy0zLjUuNi0xMS44IDEuMi0xMy42IDMuOC0xLjcgMi42IDguNi0yLjIgOS4yLTEuNS42LjctLjQgNi0xLjcgOC4yLTEuNiAzLjMtMTMuMiAxMy4yLTEyLjQgMTMuOS43LjcgMTEuNC0xMCAxNC05LjUgMiAuMyA0LjUgMSA1LjIgMyAxIDMuMS0xLjcgOC0uOCA3LjguNyAwIDIuMi00LjQgMi44LTUgMi0yIDUuNSA2LjQgNi40IDUgLjYtMS00LjktNi4zLTQuMi04LjcuMi0uNy41LTEuMi44LTEuNS44LTEgMi44IDEuNiAyLjggMCAwLTEuOC0yLjctNy44LTMuNC0xMS40LS43LTMuNiAzLjUtNS45IDUuMy00LjUgNi40IDYgNiA2LjggOS4yIDEwLjEgMS40IDEuNSAxIDEwLjcuNSAxNS43LS4yIDMuMi4yIDguNi0yLjEgMTAuNS0yIDEuNS04LjggMS4xLTEyIDEuMUg0NC4yYy0yLjcgMC02LjMtMS4xLTcuMS0zLjctMS43LTUtMS43LTExLTEuNy0xNi43VjBaIi8+PC9zdmc+Cg==';


    /**
     * Init the class by hookinBar in the lounge of Cornelia Diamond Golf Resort into the admin interface.
     */
    public function __construct() {
        add_action( 'admin_menu', [ &$this, 'register_admin_menu' ], 99 );
        add_action( 'admin_init', [ &$this, 'register_settings' ] );
    }


    /**
     * Add admin menu option to sidebar
     */
    public function register_admin_menu() {

		// Add Bigup Web parent menu, if it doesn't exist.
		$parent_menu = menu_page_url( 'bigup-web-settings', false );
		if ( false === !! $parent_menu ) {
			add_menu_page(
				'Bigup Web Settings',             //page_title
				'Bigup Web',		              //menu_title
				'manage_options',	              //capability
				'bigup-web-settings',             //menu_slug
				[ &$this, 'create_parent_page' ], //function
				$this->icon,		              //icon_url
				4					              //position
			);
		}

		// Add sub menu for this plugin.
        add_submenu_page(
            'bigup-web-settings',               //parent_slug
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
    public function create_parent_page() {
		?>

		<h1>
			<span>
				<img style="max-height: 2em;margin-right: 0.5em;vertical-align: middle;" src="<?php echo $this->icon ?>"/>
			</span>
			Bigup Web Settings
		</h1>

		<div class="wrap">
			<a href="/wp-admin/admin.php?page=contact-form-settings">
				Go to contact form settings
			</a>
		</div>

		<?php
	}


    /**
     * Create Contact Form Settings Page
     */
    public function create_settings_page() {
    	?>

        <h1>
            <span>
                <img style="max-height: 2em;margin-right: 0.5em;vertical-align: middle;" src="<?php echo $this->icon ?>"/>
            </span>
            Bigup Web Contact Form Settings
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
     * Output Form Fields - SMTP Account Settings
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
     * Output Form Fields - Message Header Settings
     */
    public function echo_intro_section_headers() {
        echo '<p>These can be set to anything, however, setting <b>sent from</b> to an address that doesn&apos;t match the local domain will cause mail to fail SPF checks, not to mention being a form of forgery.</p>';
    }
    public function echo_field_to_email() {
        echo '<input type="email" name="to_email" id="to_email" value="' . get_option( 'to_email', get_bloginfo( 'admin_email' ) ) . '">';
    }
    public function echo_field_from_email() {
        echo '<input type="email" name="from_email" id="from_email" value="' . get_option( 'from_email', get_bloginfo( 'admin_email' ) ) . '">';
    }


    /**
     * Output Form Fields - Message Header Settings
     */
    public function echo_intro_section_appearance() {
        echo '<p>These options determine the appearance of your form.</p>';
    }
    public function echo_field_styles() {
        echo '<input type="checkbox" name="styles" id="styles" value="1"' . checked( '1', get_option('styles'), false ) . '>';
        echo '<label for="styles">Tick to use the fancy dark form theme.</label>';
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
            register_setting( $group, 'username', [ &$this, 'validate_text' ] );

            add_settings_field( 'password', 'Password', [ &$this, 'echo_field_password' ], $page, $section );
            register_setting( $group, 'password', [ &$this, 'validate_text' ] );

            add_settings_field( 'host', 'Host', [ &$this, 'echo_field_host' ], $page, $section );
            register_setting( $group, 'host', [ &$this, 'validate_domain' ] );

            add_settings_field( 'port', 'Port', [ &$this, 'echo_field_port' ], $page, $section );
            register_setting( $group, 'port', [ &$this, 'validate_port' ] );

            add_settings_field( 'auth', 'Authentication', [ &$this, 'echo_field_auth' ], $page, $section );
            register_setting( $group, 'auth', [ &$this, 'sanitise_checkbox' ] );

        /**
         * Register section and fields - Message Header Settings
         */
        $section = 'section_headers';
        add_settings_section( $section, 'Message Headers', [ &$this, 'echo_intro_section_headers' ], $page );

            add_settings_field( 'to_email', 'Recipient Email Address', [ &$this, 'echo_field_to_email' ], $page, $section );
            register_setting( $group, 'to_email', 'sanitize_email' );

            add_settings_field( 'from_email', 'Sent-from Email Address', [ &$this, 'echo_field_from_email' ], $page, $section );
            register_setting( $group, 'from_email', 'sanitize_email' );

        /**
         * Register section and fields - Appearance Settings
         */
        $section = 'section_appearance';
        add_settings_section( $section, 'Appearance', [ &$this, 'echo_intro_section_appearance' ], $page );

			add_settings_field( 'styles', 'Fancy Dark Theme', [ &$this, 'echo_field_styles' ], $page, $section );
			register_setting( $group, 'styles', [ &$this, 'sanitise_checkbox' ] );
    }


    /**
     * Validate a text field.
     */
    function validate_text( $text ) {
 
        $clean_text = sanitize_text_field( $text );
        return $clean_text;
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
    function sanitise_checkbox( $checkbox ) {

        $bool_checkbox = (bool)$checkbox;
        return $bool_checkbox;
    }

}// Class end
