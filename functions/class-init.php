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

class Init {


    /**
     * Init scripts, styles and localize vars to pass to front end.
     */
    public static function register_scripts_and_styles() {
   

        function localize_vars() {
            return array(
                'plugin_directory' => plugin_dir_url( __DIR__ )
            );  // access in js: hb_contact_form_vars.plugin_directory
        }
        wp_register_script ('hb_contact_form_js', plugins_url ( 'js/ajax-handler.js', __DIR__ ), array( 'jquery' ), '0.5', false);
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