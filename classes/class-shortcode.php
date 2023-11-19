<?php
namespace Bigup\Contact_Form;

/**
 * Bigup Contact Form - Shortcode.
 *
 * This class handles all aspects of shortcode usage.
 *
 * @package bigup-contact-form
 * @author Jefferson Real <me@jeffersonreal.uk>
 * @copyright Copyright (c) 2023, Jefferson Real
 * @license GPL2+
 * @link https://jeffersonreal.uk
 * 
 */

class Shortcode { 


    /**
     * This function is called by WordPress when the shortcode is used.
     */
    public static function display_shortcode( $attributes= array() ) {

        //enqueue contact form and styles
        wp_enqueue_script('bigup_contact_form_js');
        wp_enqueue_style('bigup_contact_form_css');

        if ( ! isset( $attributes[ 'title' ] ) ) {
            $attributes[ 'title' ] = 'Contact Form';
        }

        if ( ! isset( $attributes[ 'message' ] ) ) {
            $attributes[ 'message' ] = 'Complete this contact form to send a message';
        }

		//get the form markup built with the passed vars.
		$form = Form_Generator::get_form( $attributes );
        return $form;
    }

}
