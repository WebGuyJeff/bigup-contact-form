<?php
namespace Bigup\Contact_Form;

/**
 * Bigup Contact Form - Store_Submissions.
 *
 * Handle logging form submissions with a custom post type.
 *
 * @package bigup_contact_form
 * @author Jefferson Real <me@jeffersonreal.uk>
 * @license GPL2+
 * @link https://jeffersonreal.uk
 */

// WordPress dependencies.
use function register_post_type;
use function register_taxonomy_for_object_type;
use function add_meta_box;
use function add_action;
use function wp_insert_post;
use function is_wp_error;
use function get_error_message;
use function sanitize_title;

class Store_Submissions {

	/**
	 * @var string $prefix Prefix for storing custom fields in the postmeta table
	 */
	private $prefix = '_bufe_';

	/**
	 * @var string Post type ID
	 */
	private $post_type = 'bigup_form_entry';

	/**
	 * @var string Metabox ID
	 */
	private $metabox = 'form-entry-fields';

	/**
	 * @var array $custom_fields Defines the custom fields available
	 */
	private $custom_fields = array(
		array(
			"name"        => "_name",
			"title"       => "Name",
			"description" => "",
			"type"        => "text"
		),
		array(
			"name"        => "_email",
			"title"       => "Email",
			"description" => "",
			"type"        => "text"
		),
		array(
			"name"        => "_message",
			"title"       => "Message",
			"description" => "",
			"type"        => "textarea"
		),
		array(
			"name"        => "_files",
			"title"       => "Files",
			"description" => "",
			"type"        => "textarea"
		),
		array(
			"name"        => "_send-result",
			"title"       => "Send Result",
			"description" => "The response from the mailer.",
			"type"        => "text"
		)
	);

	/**
	 * Register the custom post type.
	 */
	public function create_cpt() {
		register_post_type( $this->post_type,
			array(
				'labels' => array(
					'name'               => 'Form Entries',
					'singular_name'      => 'Form Entry',
					'add_new'            => 'New Form Entry',
					'add_new_item'       => 'Add New Form Entry',
					'edit_item'          => 'Edit Form Entry',
					'new_item'           => 'New Form Entry',
					'view_item'          => 'View Form Entries',
					'search_items'       => 'Search Form Entries',
					'not_found'          => 'No Form Entries Found',
					'not_found_in_trash' => 'No Form Entries found in Trash',
				),
				'supports'            => array( 'title', 'custom-fields' ),
				'description'         => 'This is a log of submitted form entries.',
				'public'              => true,
				'exclude_from_search' => true,
				'publicly_queryable'  => false,
				'query_var'           => false,
				'show_in_menu'        => true,
				'menu_position'       => 2,
				'menu_icon'           => 'dashicons-email-alt',
				'hierarchical'        => false,
				'taxonomies'          => ['category'],
				'show_in_rest'        => false,
				'delete_with_user'    => false,
				// 'capabilities'        => array( 'create_posts' => false )
			)
		);
		register_taxonomy_for_object_type( 'category', $this->post_type );
		add_action( 'admin_menu', array( &$this, 'create_custom_fields' ) );
		add_action( 'save_post', array( &$this, 'save_custom_fields' ), 1, 2 );
		add_action( 'do_meta_boxes', array( &$this, 'remove_default_custom_fields' ), 10, 3 );
	}

	/**
	 * Remove default Custom Fields meta box
	 */
	public function remove_default_custom_fields( $type, $context, $post ) {
		foreach ( array( 'normal', 'advanced', 'side' ) as $context ) {
			remove_meta_box( 'postcustom', $this->post_type, $context );
		}
	}

	/**
	 * Create new Custom Fields meta box
	 */
	public function create_custom_fields() {
		add_meta_box( $this->metabox, 'Form Entry', array( &$this, 'display_custom_fields' ), $this->post_type, 'normal', 'high' );
	}

	/**
	 * Display the new Custom Fields meta box
	 */
	public function display_custom_fields() {
		global $post;
		?>
		<div class="form-wrap">
			<?php
			wp_nonce_field( $this->metabox, $this->metabox . '_wpnonce', false, true );
			foreach ( $this->custom_fields as $field ) {
				?>
				<div class="form-field form-required">
					<?php
					switch ( $field[ 'type' ] ) {
						case "text": {
							echo '<label for="' . $this->prefix . $field[ 'name' ] .'"><b>' . $field[ 'title' ] . '</b></label>';
							echo '<input type="text" name="' . $this->prefix . $field[ 'name' ] . '" id="' . $this->prefix . $field[ 'name' ] . '" value="' . htmlspecialchars( get_post_meta( $post->ID, $this->prefix . $field[ 'name' ], true ) ) . '" />';
							break;
						}
						case "textarea": {
							echo '<label for="' . $this->prefix . $field[ 'name' ] .'"><b>' . $field[ 'title' ] . '</b></label>';
							echo '<textarea name="' . $this->prefix . $field[ 'name' ] . '" id="' . $this->prefix . $field[ 'name' ] . '" columns="30" rows="3">' . htmlspecialchars( get_post_meta( $post->ID, $this->prefix . $field[ 'name' ], true ) ) . '</textarea>';
							break;
						}
						case 'checkbox': {
							echo '<label for="' . $this->prefix . $field[ 'name' ] .'" style="display:inline;"><b>' . $field[ 'title' ] . '</b></label>';
							echo '<input type="checkbox" name="' . $this->prefix . $field[ 'name' ] . '" id="' . $this->prefix . $field[ 'name' ] . '" value="yes"';
							if ( get_post_meta( $post->ID, $this->prefix . $field['name'], true ) == "yes" )
								echo ' checked="checked"';
							echo '" style="width: auto;" />';
							break;
						}
						default: {
							echo '<label>Custom field output error: Field type "' . $field[ 'type' ] . '" not found.</label>';
							error_log( 'Bigup_Contact_Form: field output type "' . $field[ 'type' ] . '" not found.' );
							break;
						}
					}
					?>
					<?php if ( $field[ 'description' ] ) echo '<p>' . $field[ 'description' ] . '</p>'; ?>
				</div>
				<?php
			} ?>
		</div>
		<?php
	}

	/**
	 * Save the new Custom Fields values
	 */
	function save_custom_fields( $post_id, $post ) {
		if ( ! isset( $_POST[ $this->metabox . '_wpnonce' ] )
			|| ! wp_verify_nonce( $_POST[ $this->metabox . '_wpnonce' ], $this->metabox )
			|| ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		foreach ( $this->custom_fields as $field ) {
			if ( isset( $_POST[ $this->prefix . $field[ 'name' ] ] ) && trim( $_POST[ $this->prefix . $field['name'] ] ) ) {
				$value = $_POST[ $this->prefix . $field[ 'name' ] ];
				// Auto-paragraphs for message body
				if ( $field[ 'type' ] == "textarea" ) $value = wpautop( $value );
					update_post_meta( $post_id, $this->prefix . $field[ 'name' ], $value );
			} else {
				delete_post_meta( $post_id, $this->prefix . $field[ 'name' ] );
			}
		}
	}

	/**
	 * Log a new form submission.
	 */
	public static function log_form_entry( $data, $result ) {

		$file_info = '';
		if ( array_key_exists( 'files', $data ) ) {
			$number_of_files = count( $data[ 'files' ] ) - 1;
			for ( $n = 0; $n <= $number_of_files; $n++ ) {
				$file_info .= $data[ 'files' ][ $n ][ 'name' ] . "\n";
			}
		}

		$result = wp_insert_post( array(
			'post_type'              => 'bigup_form_entry',
			'post_status'            => 'publish',
			'post_title'             => date( 'd-m-Y_' ) . sanitize_title( $data[ 'fields' ][ 'name' ] ),
			'meta_input'             => array(
				'_bufe__name'        => $data[ 'fields' ][ 'name' ],
				'_bufe__email'       => $data[ 'fields' ][ 'email' ],
				'_bufe__message'     => $data[ 'fields' ][ 'message' ],
				'_bufe__files'       => $file_info,
				'_bufe__send-result' => implode( " | ", $result )
			)
		), true ); // Return error.
		if ( is_wp_error( $result ) ) error_log( $result->get_error_message() );
	}
}
