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

use admin_url;
use get_bloginfo;

class Init {


    /**
     * Init scripts, styles and localize vars to pass to front end.
     */
    public static function register_scripts_and_styles() {
        
        function localize_vars() {

            $action = 'hb_contact_form_submit';

            // PRETTY URL WARNING - extensionless php will break form submission
            // if these urls are not adjusted to match.
            // To access in js: hb_contact_form_vars.plugin_directory

            return array(
                'wp_ajax_url'    => admin_url( 'admin-ajax.php' ),
                'wp_admin_email' => get_bloginfo( 'admin_email' ),
                'wp_nonce'       => wp_create_nonce( $action ),
                'wp_action'      => $action
            );
        }
        wp_register_script ( 'hb_contact_form_js', plugins_url ( 'js/form-submit-handler.js', __DIR__ ), array(), '0.5', false );
        wp_localize_script( 'hb_contact_form_js', 'hb_contact_form_vars', localize_vars() );

        wp_register_style( 'hb_contact_form_css', plugins_url ( 'css/hb-contact-form.css', __DIR__ ), array(), '0.1', 'all' );
    }


    /**
     * Helper function - include_with_variables.
     *
     * This function allows the passing of variables between template parts.
     * Example of passing a title from index.php to header.php:
     * 
     * index.php:
     * includeWithVariables('header.php', array('title' => 'Header Title'));
     * 
     * header.php:
     * echo $title;
     */
    public static function include_with_variables( $filePath, $variables = array() )
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


}// Class end