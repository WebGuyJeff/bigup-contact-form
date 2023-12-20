<?php
namespace Bigup\Contact_Form;

/**
 * Bigup Contact Form - Initialisation.
 *
 * Setup styles and helper functions for this plugin.
 *
 * @package bigup-contact-form
 * @author Jefferson Real <me@jeffersonreal.uk>
 * @copyright Copyright (c) 2023, Jefferson Real
 * @license GPL2+
 * @link https://jeffersonreal.uk
 * 
 */

// WordPress dependencies.
use function get_bloginfo;
use function wp_localize_script;
use function wp_create_nonce;
use function add_action;
use function add_shortcode;
use function register_rest_route;
use function is_admin;

class Init {

	// Store view (admin || notAdmin).
	private string $view;

	// Store mail settings check result.
	private bool $mail_settings_are_set;

    /**
     * Setup the class.
     */
    public function __construct() {
		$this->view          = ( is_admin() ) ? 'admin' : 'notAdmin';
		// Check if settings have been configured ready to test email sending.
		$settings = get_option( 'bigup_contact_form_settings' );
		$required_smtp = array(
			'username',
			'password',
			'host',
			'port',
		);
		$required_headers = array(
			'to_email',
			'from_email'
		);
		$smtp_ok                     = $this->are_all_set( $settings, $required_smtp );
		$headers_ok                  = $this->are_all_set( $settings, $required_headers );
		$local_mailer_selected       = ( ! empty( $settings['use_local_mail_server'] ) && true === $settings['use_local_mail_server'] );
		$this->mail_settings_are_set = ( $smtp_ok && $headers_ok || $local_mailer_selected && $headers_ok );
    }


	/**
     * Check all test items exist as populated keys in data.
     */
    public function are_all_set( $data, $testItems ) {
		$all_set = true;
		foreach( $testItems as $item ) {
			if ( empty( $data[ $item ] ) ) {
				$all_set = false;
			}
		}
		return $all_set;
	}


	/**
     * Setup the plugin.
     */
    public function setup() {
		if ( $this->view === 'admin' ) {
			new Admin_Settings();
		}
		add_action( 'init', [ new Store_Submissions, 'create_cpt' ], 10, 0 );
        add_action( 'rest_api_init', [ $this, 'register_rest_api_routes' ], 10, 0 );
        add_action( 'wp_enqueue_scripts', [ $this, 'frontend_scripts_and_styles' ], 10, 0 );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts_and_styles' ], 10, 0 );
        add_shortcode( 'bigup_contact_form', [ new Shortcode, 'display_shortcode' ], 10, 0 );
		add_action('widgets_init', function() {
			return register_widget( new Widget );
		}, 10, 0 );
    }


    /**
     * Initialise scripts, styles and localize vars to pass to front end.
     * 
     * wp_localize_script() passes variables to front end script by dumping global
     * js vars inline with the front end html.
     * 
     * WARNING - extensionless php may break form submission
     * if api endpoint url is not adjusted to match.
     */
    public function frontend_scripts_and_styles() {
        wp_register_style( 'bigup_contact_form_public_css', BIGUPCF_URL . 'build/css/bigup-contact-form-public.css', array(), filemtime( BIGUPCF_PATH . 'build/css/bigup-contact-form-public.css' ), 'all' );
        wp_register_script ( 'bigup_contact_form_public_js', BIGUPCF_URL . 'build/js/bigup-contact-form-public.js', array(), filemtime( BIGUPCF_PATH . 'build/js/bigup-contact-form-public.js' ), false );
		wp_add_inline_script(
			'bigup_contact_form_public_js',
			'const bigupContactFormWpInlinedPublic = ' . json_encode( array(
				'rest_url'   => get_rest_url( null, 'bigup/contact-form/v1/submit' ),
				'rest_nonce' => wp_create_nonce( 'wp_rest' ),
			) ),
			'before'
		);
    }


	/**
	 * Register admin scripts and styles.
	 */
	public function admin_scripts_and_styles() {
		wp_register_style( 'bigup_contact_form_admin_css', BIGUPCF_URL . 'build/css/bigup-contact-form-admin.css', array(), filemtime( BIGUPCF_PATH . 'build/css/bigup-contact-form-admin.css' ), 'all' );
		wp_register_script ( 'bigup_contact_form_admin_js', BIGUPCF_URL . 'build/js/bigup-contact-form-admin.js', array(), filemtime( BIGUPCF_PATH . 'build/js/bigup-contact-form-admin.js' ), false );
		wp_add_inline_script(
			'bigup_contact_form_admin_js',
			'const bigupContactFormWpInlinedAdmin = ' . json_encode( array(
				'settings_ok' => $this->mail_settings_are_set,
				'rest_url'    => get_rest_url( null, 'bigup/contact-form/v1/submit' ),
				'rest_nonce'  => wp_create_nonce( 'wp_rest' ),
			) ),
			'before'
		);
		if ( ! wp_script_is( 'bigup_icons', 'registered' ) ) {
			wp_register_style( 'bigup_icons', BIGUPCF_URL . 'dashicons/css/bigup-icons.css', array(), filemtime( BIGUPCF_PATH . 'dashicons/css/bigup-icons.css' ), 'all' );
		}
		if ( ! wp_script_is( 'bigup_icons', 'enqueued' ) ) {
			wp_enqueue_style( 'bigup_icons' );
		}
	}

    /**
     * Register rest api routes.
     * 
     * @link https://developer.wordpress.org/reference/functions/register_rest_route/
     */
    public function register_rest_api_routes() {
        register_rest_route( 'bigup/contact-form/v1', '/submit', array(
            'methods'               => 'POST',
            'callback'              => [ new Form_Controller, 'bigup_contact_form_rest_api_callback' ],
            'permission_callback'   => '__return_true',
        ) );
    }

}
