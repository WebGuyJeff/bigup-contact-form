<?php
namespace Jefferson\HB_Contact_Form;

/**
 * Herringbone Contact Form Widget.
 *
 * This template defines the contact form widget including settings form,
 * front end html and saving settings.
 *
 * @package Herringbone
 * @subpackage HB_Contact_Form
 * @author Jefferson Real <me@jeffersonreal.com>
 * @copyright Copyright (c) 2021, Jefferson Real
 * @license GPL2+
 */
use WP_Widget;

class Widget extends WP_Widget {


    /**
     * Construct the contact form widget.
     */
    public function __construct() {

        $widget_options = array (
            'classname' => 'Widget',
            'description' => 'Add an SMTP contact form.'
        );
        parent::__construct( 'Widget', 'HB Contact Form', $widget_options );
    }


    /**
     * output the contact form widget settings form.
     */
    public function form( $instance ) {

        $title = ! empty( $instance[ 'title' ] ) ? $instance[ 'title' ] : 'HB Contact Form';
        $message = ! empty( $instance['message'] ) ? $instance['message'] : 'Complete this contact form to send me a message';
        ?>

        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>">Form Title:</label>
            <input class="widefat" type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $title ); ?>" />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'message' ); ?>">Message to Appear Above the Form:</label>
            <input class="widefat" type="text" id="<?php echo $this->get_field_id( 'message' ); ?>" name="<?php echo $this->get_field_name( 'message' ); ?>" value="<?php echo esc_attr( $message ); ?>" />
        </p>

    <?php
    }


    /**
     * display the contact form widget on the front end.
     */
    public function widget( $args, $instance ) {

        //enqueue contact form and styles
        wp_enqueue_script('hb_contact_form_js');
        wp_enqueue_style('hb_contact_form_css');

        //define variables
        $title = apply_filters( 'widget_title', $instance[ 'title' ] );
        $message = $instance[ 'message' ];

        //output front end HTML
        echo $args[ 'before_widget' ];

            //include the form template with the widget vars
            //custom function defined in hb-contact-form.php
            $output_with_variables = Form_Template::include_with_variables(
                plugin_dir_path( __DIR__ ) . 'parts/form.php',

                array(
                    'title' => $title,
                    'message' => $message,
                )
            );
            echo $output_with_variables;

        echo $args[ 'after_widget' ];

    }//function widget() end


    /**
     * define the data saved by the contact form widget.
     */
    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance[ 'title' ] = strip_tags( $new_instance[ 'title' ] );
        $instance[ 'message' ] = strip_tags( $new_instance[ 'message' ] );
        return $instance;
    }

} // Class Widget end


