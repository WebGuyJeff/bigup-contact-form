<?php
namespace Bigup\Contact_Form;

/**
 * Bigup Contact Form - Template Builder.
 *
 * This class builds form templates.
 *
 * @package bigup_contact_form
 * @author Jefferson Real <me@jeffersonreal.uk>
 * @copyright Copyright (c) 2023, Jefferson Real
 * @license GPL2+
 * @link https://jeffersonreal.uk
 * 
 */

class Form_Generator {

    
    /**
     * Helper function - include_with_vars.
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
    public static function include_with_vars( $template_path, $variables = array() ) {
        $output = NULL;
        if( file_exists( $template_path ) ) {

            // Extract variables to local namespace.
            extract( $variables );
            // Start output buffering.
            ob_start();
            // Include the template file.
            include $template_path;
            // End buffering and return its contents.
            $output = ob_get_clean();

        }
        return $output;
    }


    /**
     * Get Form
	 * 
	 * Includes the correct template with the variables.
	 * 
	 * @param array $vars Vars passed by caller from widget/shortcode settings.
     */
	public static function get_form( $vars = array() ) {

		$form_template = plugin_dir_path( __DIR__ ) . 'parts/form.php';

		if ( ! isset( $vars[ 'align' ] ) ) {
            $align = '';
        } elseif ( 'middle' === $vars[ 'align' ] ) {
			$align = 'aligncenter';
		} elseif ( 'left' === $vars[ 'align' ] ) {
			$align = 'alignleft';
		} elseif ( 'right' === $vars[ 'align' ] ) {
			$align = 'alignright';
		} else {
			$align = '';
		}

		$vars[ 'files' ]    = ( isset( $vars[ 'files' ] ) ) ? $vars[ 'files' ] : get_option('files');
		$vars[ 'classes' ]  = ( get_option('styles') ) ? 'bigup__form-dark' : 'bigup__form-vanilla';
		$vars[ 'classes' ] .= ' ' . $align;

		// Include the form template with the widget vars.
		$form = self::include_with_vars( $form_template, $vars );
        return $form;
    }

}