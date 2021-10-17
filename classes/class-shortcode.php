<?php
namespace Jefferson\HB_Contact_Form;

/**
 * Herringbone Contact Form Shortcode.
 *
 * This class handles all aspects of shortcode usage.
 *
 * @package Herringbone
 * @subpackage HB_Contact_Form
 * @author Jefferson Real <me@jeffersonreal.com>
 * @copyright Copyright (c) 2021, Jefferson Real
 * @license GPL2+
 */

class Shortcode { 


    /**
     * This function is called by WordPress when the shortcode is used.
     */
    public static function display_shortcode( $attributes ) {

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
