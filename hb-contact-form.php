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
 * An SMTP Form Plugin
 *
 * Provides a PHPMailer SMTP contact form which can be placed as a widget or using
 * a shortcode. The dependency PHPMailer is included with this plugin.
 * 
 * This core file acts as a loader by:
 * 
 *      1. include the php autoloader to ready the classes.
 *      2. Call the Init class which in turn calls all plugin dependencies.
 *      3. If the user is on admin page, process the admin settings menu.
 *
 * @package Herringbone
 * @subpackage HB_Contact_Form
 * @copyright Copyright (c) 2021, Jefferson Real
 * @license GPL2+
*/



//==========delete me
error_log('===[ hb-contact-form ]===');
//==========delete me


/**
 * Load PHP autoloader.
 */
require_once( plugin_dir_path( __FILE__ ) . 'classes/autoload.php');


/**
 * Init the plugin.
 */
new Init();


/**
 * Add the admin user interface.
 */
if ( is_admin() ){
    new Admin_Settings();
}