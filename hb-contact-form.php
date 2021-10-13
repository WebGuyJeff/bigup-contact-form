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
add_action( 'widgets_init', [ new Widget, '__construct' ] );


/**
 * Add the admin user interface.
 */
if ( is_admin() ){
    new Admin_Settings();
}