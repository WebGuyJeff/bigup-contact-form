<?php
namespace Bigup\Contact_Form;

/**
 * Bigup Contact Form - Shortcode.
 *
 * This class handles all aspects of shortcode usage.
 *
 * @package bigup_contact_form
 * @author Jefferson Real <me@jeffersonreal.com>
 * @copyright Copyright (c) 2021, Jefferson Real
 * @license GPL2+
 * @link https://jeffersonreal.com
 * 
 */

class Shortcode { 


    /**
     * This function is called by WordPress when the shortcode is used.
     */
    public static function display_shortcode( $attributes ) {

        //enqueue contact form and styles
        wp_enqueue_script('bigup_contact_form_js');
        wp_enqueue_style('bigup_contact_form_css');

        if ( empty( $attributes ) ) {
            $attributes = array();
        }
        if ( ! isset( $attributes[ 'title' ] ) ) {
            $attributes[ 'title' ] = 'Contact Form';
        }
        if ( ! isset( $attributes[ 'message' ] ) ) {
            $attributes[ 'message' ] = 'Complete this contact form to send me a message';
        }

        //include the form template with the widget vars
        //custom function defined in plugin-entry.php

        $output_with_variables = Form_Template::include_with_variables(
            plugin_dir_path( __DIR__ ) . 'parts/form.php',

            array(
                'title' => $attributes[ 'title' ],
                'message' => $attributes[ 'message' ],
            )
        );

        return $output_with_variables;
    }


}// Class end
