<?php
namespace Bigup\Contact_Form;

/**
 * Register Gutenberg blocks.
 *
 * @package bigup-contact-form
 */
class Blocks {

	// Blocks root relative path.
	const BIGUPCF_BLOCKS_PATH = BIGUPCF_PATH . 'build/blocks/';

	// Block directory names.
	private array $names = array();


	/**
	 * Setup the class.
	 */
	public function __construct() {

		$all_children = scandir( self::BIGUPCF_BLOCKS_PATH );
		$dir_names    =  array_filter( preg_replace( '/\..*/', '', $all_children ) );
		if ( is_array( $dir_names ) ) {
			$this->names = $dir_names;
		}
	}


	/**
	 * Add inline vars to public js for the main form block.
	 */
	public function form_block_add_inline_script() {
		wp_add_inline_script(
			'bigup-contact-form-form', // Name from block.json with a '-' instead of '/'.
			'const bigupContactFormWpInlinedPublic = ' . json_encode( array(
				'rest_url'   => get_rest_url( null, 'bigup/contact-form/v1/submit' ),
				'rest_nonce' => wp_create_nonce( 'wp_rest' ),
			) ),
			'before'
		);
	}


	/**
	 * Renders a block on the server.
	 * 
 	 * @param string $content The saved content.
	 * 
	 * @return string The content of the block being rendered.
	 */
	function render_block_serverside( $attributes, $content ) {
		return $content;
	}
	

	/**
	 * Register all blocks.
	 */
	public function register_all() {
		if ( count( $this->names ) === 0 ) {
			return;
		}
		foreach( $this->names as $name ) {
			$result = register_block_type_from_metadata( self::BIGUPCF_BLOCKS_PATH . $name,
			array(
				'render_callback' => 'render_block_serverside',
			)
		);

			if ( false === $result ) {
				error_log( "ERROR: Block registration failed for '{$name}'" );

			} elseif ( $name === 'form' ) {
				// Enqueue script after register_block...() so script handle is valid.
				add_action( 'wp_enqueue_scripts', array( &$this, 'form_block_add_inline_script' ) );
			}
		}
	}
}
