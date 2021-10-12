<?php

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
 * A Pear SMTP Contact Form
 *
 * Provides a PHP Pear SMTP contact form which can be placed as a widget or using
 * a shortcode. The Pear mail library Mail.php must be enabled on the server for
 * this form to work.
 *
 * @package Herringbone
 * @subpackage HB_Contact_Form
 * @copyright Copyright (c) 2021, Jefferson Real
 * @license GPL2+
*/

include( plugin_dir_path( __FILE__ ) . 'parts/class-widget.php' );
include( plugin_dir_path( __FILE__ ) . 'parts/smtp-handler.php' );


/**
 * Init scripts, styles and localize vars to pass to front end.
 */
function hb_contact_form_scriptsNstyles() {

    function localize_vars() {
        return array(
            'plugin_directory' => plugin_dir_url( __FILE__ )
        );  // access in js: hb_contact_form_vars.plugin_directory
    }
    wp_register_script ('hb_contact_form_js', plugins_url ( 'js/ajax-handler.js', __FILE__ ), array( 'jquery' ), '0.5', false);
    wp_localize_script( 'hb_contact_form_js', 'hb_contact_form_vars', localize_vars() );
    wp_register_style( 'hb_contact_form_css', plugins_url ( 'css/hb-contact-form.css', __FILE__ ), array(), '0.1', 'all' );
}
add_action( 'wp_enqueue_scripts', 'hb_contact_form_scriptsNstyles' );


/**
 * Register a shortcode to allow placement of the contact form anywhere.
 */
function shortcode_hb_contact_form( $attributes ) {

    //enqueue contact form and styles
    wp_enqueue_script('hb_contact_form_js');
    wp_enqueue_style('hb_contact_form_css');

    if ( empty( $attributes ) ) {
        $attributes = array();
    }
    if ( empty( $attributes[ 'title' ] ) ) {
        $attributes[ 'title' ] = 'Contact Form';
    }
    if ( empty( $attributes[ 'message' ] ) ) {
        $attributes[ 'message' ] = 'Complete this contact form to send me a message';
    }

    //include the form template with the widget vars
    //custom function defined in hb-contact-form.php
    $hb_form_variables = hb_include_with_variables(
        plugin_dir_path( __FILE__ ) . 'parts/form.php',
        array(
            'title' => $attributes[ 'title' ],
            'message' => $attributes[ 'message' ],
        )
    );

}
add_shortcode( 'hb_contact_form', 'shortcode_hb_contact_form' );


/**
 * Custom function - hb_include_with_variables.
 *
 * This function allows the passing of variables between template parts, e.g:
 * Example - index.php:
 * includeWithVariables('header.php', array('title' => 'Header Title'));
 * Example - header.php:
 * echo $title;
 * demo end.
 *
 */
function hb_include_with_variables( $filePath, $variables = array() )
{
    $output = NULL;
    if( file_exists( $filePath ) ) {

        // Extract variables to local namespace
        extract( $variables );

        // Start output buffering
        ob_start();

        // Include the template file
        include $filePath;

        // End buffering and return its contents
        $output = ob_get_clean();
    }
    return $output;
}
