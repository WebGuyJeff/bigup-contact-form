<?php
namespace Jefferson\HB_Contact_Form;

/**
 * Herringbone Contact Form Initialisation.
 *
 * Setup styles and helper functions for this plugin.
 *
 * @package Herringbone
 * @subpackage HB_Contact_Form
 * @author Jefferson Real <me@jeffersonreal.com>
 * @copyright Copyright (c) 2021, Jefferson Real
 * @license GPL2+
 */

// WordPress dependencies.
use get_bloginfo;
use wp_localize_script;
use wp_create_nonce;
use add_action;
use add_shortcode;


class Init {


    /**
     * Use this function to initialise all dependencies for the plugin.
     */
    public function __construct() {

        /**
         * Add hook to register rest api endpoints.
         * 
         * @link https://developer.wordpress.org/reference/functions/register_rest_route/
         */
        add_action( 'rest_api_init', [ $this, 'register_rest_api_routes' ] );

        /**
         * Init scripts/styles and localize vars to pass to front end.
         */
        add_action( 'wp_enqueue_scripts', [ $this, 'register_scripts_and_styles' ] );

        /**
         * Register a shortcode to allow placement of form anywhere.
         */
        add_shortcode( 'hb_contact_form', [ new Shortcode, 'display_shortcode' ] );

        /**
         * Register and load the contact form widget.
         */
        add_action( 'widgets_init', [ new Widget, '__construct' ] );
        
    }


    /**
     * Init scripts, styles and localize vars to pass to front end.
     * 
     * wp_localize_script() passes variables to front end script by dumping global
     * js vars inline with the front end html. Not pretty, but it works. Don't use
     * clashable var names!
     * 
     * WARNING - extensionless php may break form submission
     * if api endpoint url is not adjusted to match.
     */
    public function register_scripts_and_styles() {

        wp_register_style( 'hb_contact_form_css', plugins_url ( 'css/hb-contact-form.css', __DIR__ ), array(), '0.1', 'all' );
        wp_register_script ( 'hb_contact_form_js', plugins_url ( 'js/form-sender.js', __DIR__ ), array(), '0.5', false );
        wp_localize_script(
            'hb_contact_form_js',
            'wp_localize_hb_contact_form_vars',
            array(
                'rest_url'    => rest_url(),
                'rest_nonce'  => wp_create_nonce( 'wp_rest' ),
                'admin_email' => get_bloginfo( 'admin_email' )
            )
        );
    }


    /**
     * Register rest api routes.
     * 
     */
    public function register_rest_api_routes() {

        register_rest_route( 'Jefferson/HB_Contact_Form', '/submit', array(
            'methods'  => 'POST',
            'callback' => [ new Form_Receiver, 'hb_contact_form_rest_api_callback' ],
        ) );
    }


}// Class end