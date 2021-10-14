<?php
namespace Jefferson\HB_Contact_Form;

/*
 * Plugin Name: Herringbone Contact Form
 * Plugin URI: https://jeffersonreal.com
 * Description: A Pear Mail SMTP contact form
 * Version: 0.12
 * Author: Jefferson Real
 * Author URI: https://jeffersonreal.com
 * License: GPL2
 *
 *
 * An SMTP Contact Form
 *
 * Provides a PHPMailer SMTP contact form which can be placed as a widget or using
 * a shortcode. The dependency PHPMailer is included with this plugin.
 * 
 * This core file acts as a loader init the plugin.
 *
 * @package Herringbone
 * @subpackage HB_Contact_Form
 * @copyright Copyright (c) 2021, Jefferson Real
 * @license GPL2+
*/


/**
 * Load the PHP autoloader from it's own file
 */
require_once( plugin_dir_path( __FILE__ ) . 'functions/autoload.php');


/**
 * Load the PHP autoloader from it's own file
 */
require_once( plugin_dir_path( __FILE__ ) . 'functions/autoload.php');


/**
 * Add hooks to safely handle ajax form submission the WordPress way.
 * 
 * The first is called for logged in users only, the second for all users submitting the form.
 * If both logged in and not logged in users are to submit, both actions must be included!
 */
add_action( "wp_ajax_hb_contact_form_submit", "form_submission_logged_in_users_only" );
add_action( "wp_ajax_nopriv_hb_contact_form_submit", "form_submission_all_users" );


/**
 * Init scripts, styles and localize vars to pass to front end.
 */
add_action( 'wp_enqueue_scripts', [ new Init, 'register_scripts_and_styles' ] );


/**
 * Register a shortcode to allow placement of form anywhere.
 */
add_shortcode( 'hb_contact_form', [ new Shortcode, 'display_shortcode' ] );


/**
 * Register and load the contact form widget.
 */
add_action( 'widgets_init', new Widget );


/**
 * Add the admin user interface.
 */
if ( is_admin() ){
    new Admin_Settings();
}