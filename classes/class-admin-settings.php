<?php
namespace Bigup\Contact_Form;

/**
 * Bigup Contact Form - Admin Settings.
 *
 * Hook into the WP admin area and add menu options and settings
 * pages.
 *
 * @package bigup-contact-form
 * @author Jefferson Real <me@jeffersonreal.uk>
 * @copyright Copyright (c) 2023, Jefferson Real
 * @license GPL2+
 * @link https://jeffersonreal.uk
 */

// WordPress dependencies.
use function menu_page_url;


class Admin_Settings {

    /**
     * Settings page slug to add with add_submenu_page().
     */
    private $admin_label = 'Contact Form';

    /**
     * Settings page slug to add with add_submenu_page().
     */
    private $page_slug = 'bigup-web-contact-form';

    /**
     * The plugin settings saved the wp_options table.
     */
    private $settings;

    /**
     * Settings group name called by settings_fields().
	 * 
	 * To add multiple sections to the same settings page, all settings
	 * registered for that page MUST BE IN THE SAME 'OPTION GROUP'.
     */
    private $group_name = 'group_contact_form_settings';

    /**
     * base64 uri svg icon used next to page title.
     */
    private $icon = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMzIiIGhlaWdodD0iMTMyIj48cGF0aCBmaWxsPSJjdXJyZW50Q29sb3IiIGQ9Ik0wIDB2MTMyaDM1LjRWODcuMmMwLTUuNiAwLTExLjYgMS43LTE2LjcuOC0yLjUgNC40LTMuNyA3LjEtMy43aDM0LjVjMy4yIDAgNi45LjEgOC4yIDEuMiAyLjMgMS44IDEuOSA3LjIgMi4xIDEwLjUuNCA0LjkgMSAxNC4yLS41IDE1LjYtMy4zIDMuNC0yLjggNC05LjIgMTAuMS0xLjggMS40LTYtLjktNS4zLTQuNC43LTMuNiAzLjQtOS43IDMuNC0xMS40IDAtMS43LTIgLjgtMi44IDAtLjMtLjQtLjYtLjktLjgtMS42LS43LTIuNCA0LjgtNy43IDQuMi04LjgtLjktMS4zLTQuMyA3LTYuNCA1LS42LS41LTIuMS00LjktMi44LTUtMSAwIDEuOCA0LjguOCA3LjktLjcgMi0zLjIgMi44LTUuMiAzLTIuNi41LTEzLjMtMTAuMS0xNC05LjUtLjguNyAxMC44IDEwLjcgMTIuNCAxNCAxLjMgMi4xIDIuMyA3LjUgMS43IDguMS0uNi43LTEwLjktNC05LjItMS41IDEuOCAyLjYgMTAgMy4yIDEzLjYgMy44IDEuMS4yIDMgLjEgNC42IDIuNS4zLjQtMi42LS40LTUuMy0xLTIuNi0uMy01LjQtMS01LjktLjgtLjcuNSAyIDMuMiAyLjggMy40IDEuMS40IDExLjUtLjUgMTIuMi0uNyAyLjgtMSAzLjktMS42IDQuMy0yIDUuOC02LjcgOS40LTkgOS42LTEyLjEuMi0zLjEtLjQtMTMgMi4zLTE0LjggMi42LTEuOCA1LjMuMSA2LjUgNS44IDEuMiA1LjcgMy40IDUuNiA0LjQgMTAuOCAxIDUuMi0zLjMgMTUuOS01LjYgMjEuOS0yLjIgNi03LjQgNy42LTEwLjYgOS42LTMuMyAyLTYuNyAzLjUtMTAuOCA0LjMtMi45LjYtNy41IDEuMS05LjkgMS4zSDEzMlYwSDY2czcuNC41IDExLjQgMS4zUzg1IDMuNyA4OC4yIDUuN2MzLjIgMiA4LjQgMy42IDEwLjYgOS42IDIuMyA2IDYuNyAxNi42IDUuNiAyMS44LTEgNS4zLTMuMiA1LjEtNC40IDEwLjgtMS4yIDUuNy0zLjkgNy43LTYuNSA1LjktMi43LTEuOS0yLjEtMTEuOC0yLjMtMTQuOC0uMi0zLjEtMy44LTUuNS05LjYtMTIuMS0uNC0uNS0xLjUtMS4xLTQuMy0yLS43LS4yLTExLTEuMi0xMi4yLS44LS44LjItMy41IDMtMi44IDMuNC41LjMgMy4zLS40IDUuOS0uOSAyLjctLjQgNS42LTEuMyA1LjMtLjlDNzIgMjguMSA3MCAyOCA2OSAyOC4yYy0zLjUuNi0xMS44IDEuMi0xMy42IDMuOC0xLjcgMi42IDguNi0yLjIgOS4yLTEuNS42LjctLjQgNi0xLjcgOC4yLTEuNiAzLjMtMTMuMiAxMy4yLTEyLjQgMTMuOS43LjcgMTEuNC0xMCAxNC05LjUgMiAuMyA0LjUgMSA1LjIgMyAxIDMuMS0xLjcgOC0uOCA3LjguNyAwIDIuMi00LjQgMi44LTUgMi0yIDUuNSA2LjQgNi40IDUgLjYtMS00LjktNi4zLTQuMi04LjcuMi0uNy41LTEuMi44LTEuNS44LTEgMi44IDEuNiAyLjggMCAwLTEuOC0yLjctNy44LTMuNC0xMS40LS43LTMuNiAzLjUtNS45IDUuMy00LjUgNi40IDYgNiA2LjggOS4yIDEwLjEgMS40IDEuNSAxIDEwLjcuNSAxNS43LS4yIDMuMi4yIDguNi0yLjEgMTAuNS0yIDEuNS04LjggMS4xLTEyIDEuMUg0NC4yYy0yLjcgMC02LjMtMS4xLTcuMS0zLjctMS43LTUtMS43LTExLTEuNy0xNi43VjBaIi8+PC9zdmc+Cg==';


    /**
     * Init the class by hooking into the admin interface.
     */
    public function __construct() {
		$this->settings = get_option( 'bigup_contact_form_settings' );
		add_action( 'bigup_below_parent_settings_page_heading', [ &$this, 'echo_plugin_settings_link' ] );
		new Admin_Settings_Parent();
        add_action( 'admin_menu', [ &$this, 'register_admin_menu' ], 99 );
        add_action( 'admin_init', [ &$this, 'register_settings' ] );
    }


    /**
     * Add admin menu option to sidebar
     */
    public function register_admin_menu() {

        add_submenu_page(
            Admin_Settings_Parent::$page_slug,  //parent_slug
            $this->admin_label . ' Settings',   //page_title
            $this->admin_label,                 //menu_title
            'manage_options',                   //capability
            $this->page_slug,                   //menu_slug
            [ &$this, 'create_settings_page' ], //function
            null,                               //position
        );

    }


    /**
     * Echo a link to this plugin's settings page.
     */
    public function echo_plugin_settings_link() {
		?>

		<a href="/wp-admin/admin.php?page=<?php echo $this->page_slug ?>">
			Go to <?php echo $this->admin_label ?> settings
		</a>
		<br>

		<?php
	}


    /**
     * Create Contact Form Settings Page
     */
    public function create_settings_page() {
    	?>

		<div class="wrap">

			<h1>
				<span class="dashicons-bigup-logo" style="font-size: 2em; margin-right: 0.2em;"></span>
				Bigup Web Contact Form Settings
			</h1>

			<?php settings_errors(); // Display the form save notices here. ?>

			<h2>
				Usage
			</h2>
			<p>
				With the settings below complete, you can either set a contact form in the widget
				area using the customizer, or use the shortcode as below in your theme files or via
				the shortcode block in the editor:
			</p>
			<code style="margin: 1em 0 2em 0;display: block;width: fit-content;: 0.5em 0;">
				[bigup_contact_form title="Contact Form" message="Complete this contact form to send me a message"]
			</code>
            <form method="post" action="options.php">

                <?php
                    settings_fields( $this->group_name );
                    do_settings_sections( $this->page_slug );
                    submit_button( 'Save' );
                ?>

            </form>

        </div>

    	<?php
    }


    /**
     * Register all settings fields and call their functions to build the page.
     * 
     * add_settings_section( $id, $title, $callback, $page )
     * add_settings_field( $id, $title, $callback, $page, $section, $args )
     * register_setting( $option_group, $option_name, $sanitize_callback )
     */
    public function register_settings() {

        $group    = $this->group_name;
        $page     = $this->page_slug;

		// A single serialsed array holds all plugin settings.
		register_setting(
			$group,                        // option_group
			'bigup_contact_form_settings', // option_name
			array( $this, 'sanitize' )     // sanitize_callback
		);

        // SMTP Account.
        $section = 'section_smtp';
        add_settings_section( $section, 'SMTP Account', null, $page );
            add_settings_field( 'username', 'Username', [ &$this, 'echo_field_username' ], $page, $section );
            add_settings_field( 'password', 'Password', [ &$this, 'echo_field_password' ], $page, $section );
            add_settings_field( 'host', 'Host', [ &$this, 'echo_field_host' ], $page, $section );
            add_settings_field( 'port', 'Port', [ &$this, 'echo_field_port' ], $page, $section );
            add_settings_field( 'auth', 'Authentication', [ &$this, 'echo_field_auth' ], $page, $section );

        // Local mail server.
        $section = 'section_local_mail_server';
        add_settings_section( $section, 'Local Mail Server', null, $page );
			add_settings_field( 'use_local_mail_server', 'Use Local Mail Server', [ &$this, 'echo_field_use_local_mail_server' ], $page, $section );


        // Message Header.
        $section = 'section_headers';
        add_settings_section( $section, 'Message Headers', [ &$this, 'echo_intro_section_headers' ], $page );
            add_settings_field( 'to_email', 'Recipient Email Address', [ &$this, 'echo_field_to_email' ], $page, $section );
            add_settings_field( 'from_email', 'Sent-from Email Address', [ &$this, 'echo_field_from_email' ], $page, $section );

        // Appearance.
        $section = 'section_appearance';
        add_settings_section( $section, 'Appearance', [ &$this, 'echo_intro_section_appearance' ], $page );
			add_settings_field( 'styles', 'Fancy dark theme', [ &$this, 'echo_field_styles' ], $page, $section );
			add_settings_field( 'nostyles', 'Remove plugin styles', [ &$this, 'echo_field_nostyles' ], $page, $section );

        // Fields.
        $section = 'section_fields';
        add_settings_section( $section, 'Fields', [ &$this, 'echo_intro_section_fields' ], $page );
			add_settings_field( 'files', 'Files', [ &$this, 'echo_field_files' ], $page, $section );
    }


    /**
     * Output Form Fields - SMTP Account Settings
     */
    public function echo_field_username() {
		printf(
			'<input class="regular-text" type="text" name="%s" value="%s">',
			'bigup_contact_form_settings[username]',
			$this->settings['username'] ?? ''
		);
    }
    public function echo_field_password() {
		printf(
			'<input class="regular-text" type="password" name="%s" value="%s">',
			'bigup_contact_form_settings[password]',
			 $this->settings['password'] ?? ''
		);
    }
    public function echo_field_host() {
		printf(
			'<input class="regular-text" type="text" name="%s" value="%s">',
			'bigup_contact_form_settings[host]',
			$this->settings['host'] ?? ''
		);
    }
    public function echo_field_port() {
		printf(
			'<input class="regular-text" type="number" min="25" max="2525" step="1" name="%s" value="%s">',
			'bigup_contact_form_settings[port]',
			$this->settings['port'] ?? ''
		);
    }
    public function echo_field_auth() {
		$setting = 'bigup_contact_form_settings[auth]';
		printf(
			'<input type="checkbox" value="1" id="%s" name="%s" %s><label for="%s">%s</label>',
			$setting,
			$setting,
			isset( $this->settings['auth'] ) ? checked( '1', $this->settings['auth'], false ) : '',
			$setting,
			'Tick if your SMTP provider requires authentication.'
		);
    }


	/**
     * Output Form Fields - Local mail server Settings
     */
    public function echo_field_use_local_mail_server() {
		$setting = 'bigup_contact_form_settings[use_local_mail_server]';
		printf(
			'<input type="checkbox" value="1" id="%s" name="%s" %s><label for="%s">%s</label><p><span style="font-weight:800;">WARNING: </span>%s</p>',
			$setting,
			$setting,
			isset( $this->settings['use_local_mail_server'] ) ? checked( '1', $this->settings['use_local_mail_server'], false ) : '',
			$setting,
			'Try and use a local mail server instead of SMTP (overrides SMTP settings).',
			'Depending on the hosting config, this may return false positives making it look like mail has sent. Please test-send an email to yourself via the contact form. SMTP is highly recommended as it will always alert the user of send failure!'
		);
    }


    /**
     * Output Form Fields - Message Header Settings
     */
    public function echo_intro_section_headers() {
        echo '<p>These can be set to anything, however, setting <b>sent from</b> to an address that doesn&apos;t match the local domain will cause mail to fail SPF checks, not to mention being a form of forgery.</p>';
    }
    public function echo_field_to_email() {
		printf(
			'<input class="regular-text" type="email" name="%s" value="%s">',
			'bigup_contact_form_settings[to_email]',
			$this->settings['to_email'] ?? get_bloginfo( 'admin_email' )
		);
	}
    public function echo_field_from_email() {
		printf(
			'<input class="regular-text" type="email" name="%s" value="%s">',
			'bigup_contact_form_settings[from_email]',
			$this->settings['from_email'] ?? get_bloginfo( 'admin_email' )
		);
	}


    /**
     * Output Form Fields - Appearance Settings
     */
    public function echo_intro_section_appearance() {
        echo '<p>These options determine the appearance of your form.</p>';
    }
    public function echo_field_styles() {
		$setting = 'bigup_contact_form_settings[styles]';
		printf(
			'<input type="checkbox" value="1" id="%s" name="%s" %s><label for="%s">%s</label>',
			$setting,
			$setting,
			isset( $this->settings['styles'] ) ? checked( '1', $this->settings['styles'], false ) : '',
			$setting,
			'Tick to use the fancy dark form theme.',
		);
    }
    public function echo_field_nostyles() {
		$setting = 'bigup_contact_form_settings[nostyles]';
		printf(
			'<input type="checkbox" value="1" id="%s" name="%s" %s><label for="%s">%s</label>',
			$setting,
			$setting,
			isset( $this->settings['nostyles'] ) ? checked( '1', $this->settings['nostyles'], false ) : '',
			$setting,
			'Tick to remove all styles provided by this plugin and allow theme styles to take precedence (overrides "Fancy dark theme" setting).',
		);
	}

	/**
     * Output Form Fields - Fields Settings
     */
    public function echo_intro_section_fields() {
        echo '<p>Customise the fields on the form.</p>';
    }
    public function echo_field_files() {
		$setting = 'bigup_contact_form_settings[files]';
		printf(
			'<input type="checkbox" value="1" id="%s" name="%s" %s><label for="%s">%s</label>',
			$setting,
			$setting,
			isset( $this->settings['files'] ) ? checked( '1', $this->settings['files'], false ) : '',
			$setting,
			'Tick to enable the file select input so users can upload files.',
		);
    }


	public function sanitize( $input ) {
		$sanitized = array();

		if ( isset( $input['username'] ) ) {
			$sanitized['username'] = sanitize_text_field( $input['username'] );
		}

		if ( isset( $input['password'] ) ) {
			$sanitized['password'] = $this->sanitize_password( $input['password'] );
		}

        if ( isset( $input['host'] ) ) {
			$sanitized['host'] = $this->validate_domain( $input['host'] );
		}

		if ( isset( $input['port'] ) ) {
			$sanitized['port'] = $this->sanitise_smtp_port( $input['port'] );
		}

        if ( isset( $input['auth'] ) ) {
			$sanitized['auth'] = $this->sanitise_checkbox( $input['auth'] );
		}

		if ( isset( $input['use_local_mail_server'] ) ) {
			$sanitized['use_local_mail_server'] = $this->sanitise_checkbox( $input['use_local_mail_server'] );
		}

		if ( isset( $input['to_email'] ) ) {
			$sanitized['to_email'] = sanitize_email( $input['to_email'] );
		}

        if ( isset( $input['from_email'] ) ) {
			$sanitized['from_email'] = sanitize_email( $input['from_email'] );
		}

		if ( isset( $input['styles'] ) ) {
			$sanitized['styles'] = $this->sanitise_checkbox( $input['styles'] );
		}

		if ( isset( $input['nostyles'] ) ) {
			$sanitized['nostyles'] = $this->sanitise_checkbox( $input['nostyles'] );
		}

		if ( isset( $input['files'] ) ) {
			$sanitized['files'] = $this->sanitise_checkbox( $input['files'] );
		}

		return $sanitized;
	}


    /**
     * Validate a domain name.
     */
    private function validate_domain( $domain ) {
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
     * Sanitise an SMTP port number.
     */
    private function sanitise_smtp_port( $port ) {
		$port_int    = intval( $port );
		$valid_ports = [ 25, 465, 587, 2525 ];
        if ( in_array( $port_int, $valid_ports, true ) ) {
            return $port_int;
        } else {
            return '';
        }
    }


    /**
     * Validate a checkbox.
     */
    private function sanitise_checkbox( $checkbox ) {
        $bool_checkbox = ( bool )$checkbox;
        return $bool_checkbox;
    }

    /**
     * Validate a checkbox.
     */
    private function sanitize_password( $password ) {
        $trimmed_password = trim( $password );
        return $trimmed_password;
    }
}
