<?php
namespace Jefferson\HB_Contact_Form;

/**
 * Herringbone Contact Form Template Builder.
 *
 * This class builds form templates.
 *
 * @package Herringbone
 * @subpackage HB_Contact_Form
 * @author Jefferson Real <me@jeffersonreal.com>
 * @copyright Copyright (c) 2021, Jefferson Real
 * @license GPL2+
 */

class Form_Template {

    
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


}//Class end