<?php
namespace Bigup\Contact_Form;

/**
 * Bigup Contact Form - Form Block.
 *
 * The parent form element that accepts all other blocks as InnerBlocks.
 *
 * @package bigup-contact-form
 * @author Jefferson Real <me@jeffersonreal.uk>
 * @copyright Copyright (c) 2023, Jefferson Real
 * @license GPL2+
 * @link https://jeffersonreal.uk
 * 
 */


function form_block_add_inline_script_public() {
	wp_add_inline_script(
		'bigup_contact_form_public_js',
		'const bigupContactFormWpInlinedPublic = ' . json_encode( array(
			'rest_url'   => get_rest_url( null, 'bigup/contact-form/v1/submit' ),
			'rest_nonce' => wp_create_nonce( 'wp_rest' ),
		) ),
		'before'
	);
}

function form_block_add_inline_script_admin() {
	wp_add_inline_script(
		'bigup_contact_form_admin_js',
		'const bigupContactFormWpInlinedAdmin = ' . json_encode( array(
			'settings_ok' => $this->mail_settings_are_set,
			'rest_url'    => get_rest_url( null, 'bigup/contact-form/v1/submit' ),
			'rest_nonce'  => wp_create_nonce( 'wp_rest' ),
		) ),
		'before'
	);
}


/**
 * Register the block using the metadata loaded from the `block.json` file.
 */
function form_block_init() {

    register_block_type( __DIR__ . '/build' );

    // Enqueue script after register_block_type() so script handle is valid.
    add_action( 'admin_enqueue_scripts', 'form_block_add_inline_script_admin' );
    add_action( 'wp_enqueue_scripts', 'form_block_add_inline_script_public' );
}

add_action( 'init', 'form_block_init' );
