<?php
namespace Bigup\Contact_Form;

/**
 * Plugin Name: Bigup Web: Contact Form
 * Plugin URI: https://jeffersonreal.uk
 * Description: An SMTP contact form, useable as a widget and shortcode.
 * Version: 0.5.0
 * Author: Jefferson Real
 * Author URI: https://jeffersonreal.uk
 * License: GPL2
 *
 * @package bigup_contact_form
 * @version 0.5.0
 * @author Jefferson Real <me@jeffersonreal.uk>
 * @copyright Copyright (c) 2021, Jefferson Real
 * @license GPL2+
 * @link https://jeffersonreal.uk
 */

/**
 * Set this plugin's base URL constant for use throughout the plugin.
 * 
 * There is no in-built WP function to get the base URL for a plugin, so this constant allows us to
 * write relative file references, making code portable.
 */
$url = plugin_dir_url( __FILE__ );
define( 'BIGUP_CONTACT_FORM_PLUGIN_URL', $url );
$path = plugin_dir_path( __FILE__ );
define( 'BIGUP_CONTACT_FORM_PLUGIN_PATH', $path );

/**
 * Load PHP autoloader to ready the classes.
 */
require_once( plugin_dir_path( __FILE__ ) . 'classes/autoload.php');

/**
 * Init class which in turn calls all plugin dependencies.
 */
new Init();

/**
 * If the user is on admin page, process the admin settings menu.
 */
if ( is_admin() ){
    new Admin_Settings();
}