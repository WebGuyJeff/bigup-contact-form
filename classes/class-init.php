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

    /**
     * Initialise all dependencies for the plugin.
     */
    public function __construct() {
		if ( is_admin() ){
			new Admin_Settings();
		}
		add_action( 'init', [ new Store_Submissions, 'create_cpt' ] );
        add_action( 'rest_api_init', [ $this, 'register_rest_api_routes' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'register_scripts_and_styles' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'register_admin_scripts_and_styles' ] );
        add_shortcode( 'bigup_contact_form', [ new Shortcode, 'display_shortcode' ] );
		add_action('widgets_init', function() {
			return register_widget(new Widget);
		} );
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
    public function register_scripts_and_styles() {
        wp_register_style( 'bigup_contact_form_css', plugins_url ( 'build/css/form.css', __DIR__ ), array(), '0.1', 'all' );
        wp_register_script ( 'bigup_contact_form_js', plugins_url ( 'build/js/contact-form.js', __DIR__ ), array(), '0.5', false );
        wp_localize_script(
            'bigup_contact_form_js',
            'wp_localize_bigup_contact_form_vars',
            array(
                'rest_url'    => get_rest_url( null, 'bigup/contact-form/v1/submit' ),
                'rest_nonce'  => wp_create_nonce( 'wp_rest' ),
                'admin_email' => get_bloginfo( 'admin_email' )
            )
        );
    }


	/**
	 * Register admin scripts and styles.
	 */
	public function register_admin_scripts_and_styles() {
		if ( ! wp_script_is( 'bigup_icons', 'registered' ) ) {
			wp_register_style( 'bigup_icons', BIGUP_CONTACT_FORM_PLUGIN_URL . 'dashicons/css/bigup-icons.css', array(), filemtime( BIGUP_CONTACT_FORM_PLUGIN_PATH . 'dashicons/css/bigup-icons.css' ), 'all' );
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
