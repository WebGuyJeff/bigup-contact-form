/**
 * Herringbone Contact Form js.
 *
 * This js handles the front end form submission using ajax.
 *
 * @package Herringbone
 * @subpackage HB_Contact_Form
 * @author Jefferson Real <me@jeffersonreal.com>
 * @copyright Copyright (c) 2021, Jefferson Real
 * @license GPL2+
 */

;(function($) {

    // wp_localize_script variable
    let wp_ajax_url = hb_contact_form_vars.wp_ajax_url;

    function formInit() {

        // Hide the honeypot input field
        let honeypot = document.getElementById( 'jsSaveTheBees' );
        honeypot.style.display = 'none';

        // Attach a submit handler to the form
        $( '#jsFormHandler' ).submit( function ( form ) {

            // Stop form from submitting normally
            form.preventDefault();
            $form = $( this );

            // Change button text to show progress
            $( '#jsButtonSubmit' ).text( 'One mo...' );

            // Post the form using Ajax
            $.ajax( {
                type: "POST",
                // Post to WP ajax handler
                url: wp_ajax_url,
                data: $form.serialize(),
                // Use success callback to call this function
                success: afterFormSubmission,
                // Expect post response in json
                dataType: 'json'
            } );
        } );


        // Action upon ajax post response
        function afterFormSubmission( data ) {

            // Check json post response
            if ( data.result == 'success' ) {

                // Show success message and hide form & error
                $( '#jsSuccessMessage' ).text( 'Message Sent!' );
                $( '#jsErrorMessage' ).hide();
                $( '#jsHideOnSuccess' ).hide();

            } else if ( data.result == 'settings_missing' ) {

                // Display error
                $( '#jsErrorMessage' ).append( '<p>SMTP settings are incomplete. Please alert website admin.</p>' );
                $( '#jsErrorMessage' ).show();
                $( '#jsButtonSubmit' ).text('Error');

            } else if ( data.result == 'settings_invalid' ) {

                // Display error
                $( '#jsErrorMessage' ).append( '<p>SMTP settings are invalid. Please alert website admin.</p>' );
                $( '#jsErrorMessage' ).show();
                $( '#jsButtonSubmit' ).text('Error');

            } else {

                // Display error
                $( '#jsErrorMessage' ).append( '<ul></ul>' );

                jQuery.each(data.errors, function (key, val) {
                    $( '#jsErrorMessage ul' ).append( '<li>' + key + ':' + val + '</li>' );
                });

                $( '#jsErrorMessage' ).show();
                $( '#jsButtonSubmit' ).text('Error');

            }
        }
    };

    // Poll for doc ready state
    var interval = setInterval( function() {
        if(document.readyState === 'complete') {
            clearInterval( interval );
            /* Start the reactor */
            formInit();
        }
    }, 100);

})(jQuery);
