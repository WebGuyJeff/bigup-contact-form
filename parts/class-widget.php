<?php

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


class HB_Contact_Form_Widget extends WP_Widget {


    /**
     * Construct the contact form widget.
     */
    function __construct() {

    $widget_options = array (
        'classname' => 'HB_Contact_Form_Widget',
        'description' => 'Add an SMTP contact form.'
    );
    parent::__construct( 'HB_Contact_Form_Widget', 'HB Contact Form', $widget_options );

    }


    /**
     * output the contact form widget settings form.
     */
    function form( $instance ) {

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
    function widget( $args, $instance ) {

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
            $hb_form_variables = hb_include_with_variables(
                plugin_dir_path( __FILE__ ) . 'hb-contact-form.php',
                array(
                    'title' => $title,
                    'message' => $message,
                )
            );

        echo $args[ 'after_widget' ];

    }//function widget() end


    /**
     * define the data saved by the contact form widget.
     */
    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance[ 'title' ] = strip_tags( $new_instance[ 'title' ] );
        $instance[ 'message' ] = strip_tags( $new_instance[ 'message' ] );
        return $instance;
    }

} // Class HB_Contact_Form_Widget end


/**
 * Register and load the contact form widget.
 */
function hb_contact_form_load_widget() {
    register_widget( 'HB_Contact_Form_Widget' );
}
add_action( 'widgets_init', 'hb_contact_form_load_widget' );
