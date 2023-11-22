<?php
namespace Bigup\Contact_Form;

/**
 * Plugin Name: Bigup Web: Contact Form
 * Plugin URI: https://jeffersonreal.uk
 * Description: An SMTP/local mailer contact form, including widget and shortcode.
 * Version: 0.6.6
 * Author: Jefferson Real
 * Author URI: https://jeffersonreal.uk
 * License: GPL2
 *
 * @package bigup-contact-form
 * @author Jefferson Real <me@jeffersonreal.uk>
 * @copyright Copyright (c) 2023, Jefferson Real
 * @license GPL2+
 * @link https://jeffersonreal.uk
 */

// Set global constants.
define( 'BIGUPCF_DEBUG', defined( 'WP_DEBUG' ) && WP_DEBUG === true );
define( 'BIGUPCF_PATH', trailingslashit( __DIR__ ) );
define( 'BIGUPCF_URL', trailingslashit( get_site_url( null, strstr( __DIR__, '/wp-content/' ) ) ) );

// Setup PHP namespace.
require_once BIGUPCF_PATH . 'classes/autoload.php';

// Setup the plugin.
$Init = new Init();
$Init->setup();
