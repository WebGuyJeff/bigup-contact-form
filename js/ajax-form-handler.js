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
 * 
 */


(function ajax_form_handler() {

    /**
     * Hold the form DOM node that was submitted so the same
     * element can be updated by the ajax callback. This saves
     * passing the object to and from the server inside the cb.
     * This also helps avoid the use of element IDs so this
     * form can exist multiple times in a page.
     * 
     */
    let current_form;

    /**
     * Prepare the form ready for input.
     * 
     */
    function form_init() {

        // Hide the honeypot input field(s)
        let honeypot = document.querySelectorAll( '.jsSaveTheBees' );
        honeypot.forEach( input => { input.style.display = "none" } )

        // Attach 'click' listener with ajax handler callback to the form(s)
        document.querySelectorAll( '.ajaxFormHandler' ).forEach( ( form ) => {
            form.addEventListener( 'submit', ( event ) => {
                // Prevent normal form submit action
                event.preventDefault();
                ajax_form_submit( form );
            } );
        } );
    };


    /**
     * Handle the submitted form.
     * 
     * @param {object} form: The submitted form data.
     * 
     */
    function ajax_form_submit( form ) {


        // Remember which form was used
        current_form = form;

alert('hola2');
console.log(current_form);



        // Change button text
        current_form.querySelectorAll( '.jsButtonSubmit > *:first-child' ).textContent = 'One mo...';

        // grab wp_localize_script variable
        let wp_ajax_url = hb_contact_form_vars.wp_ajax_url;

        // Ajax request
        $.ajax( {
            method: "POST",
            url: wp_ajax_url,
            data: current_form.serialize(),
            dataType: 'json',
            success: ajax_respose,
            error: ajax_error
        } );
    }


    /**
     * Do this on successful ajax response.
     * 
     * @param {json} data The json response sent by the server.
     * 
     */
    function ajax_respose( data, textStatus, jqXHR ) {

        // Get the elems of the form that was used
        let el_success = current_form.querySelectorAll( '.jsSuccessMessage' );
        let el_error = current_form.querySelectorAll( '.jsErrorMessage' );
        let el_hide = current_form.querySelectorAll( '.jsHideOnSuccess' );
        let el_button = current_form.querySelectorAll( '.jsButtonSubmit > *:first-child' );

        // Check json ajax response
        if ( data.result == 'success' ) {

            // Output message
            el_success.textContent = 'Message Sent!';
            el_error.style.display = "none";
            el_hide.style.display = "none";

        } else if ( data.result == 'settings_missing' ) {

            el_error.append( '<p>SMTP settings are incomplete. Please alert website admin.</p>' );
            el_error.style.display = 'block';
            el_button.textContent = 'Error';

        } else if ( data.result == 'settings_invalid' ) {

            el_error.append( '<p>SMTP settings are invalid. Please alert website admin.</p>' );
            el_error.style.display = 'block';
            el_button.textContent = 'Error';

        } else {

            data.errors.forEach( (error, message) => el_error.append( '<p>' + error + ': ' + message + '</p>' ) );
            el_error.style.display = 'block';
            el_button.textContent = 'Error';
        }
    }


    /**
     * Do this on ajax failure.
     * 
     * @param {object} jqXHR        Ajax object containing error data.
     * @param {string} textStatus   Error status.
     * @param {string} errorThrown  Error text name.
     * 
     */
    function ajax_error( jqXHR, textStatus, errorThrown ) {

        let el_error = current_form.querySelectorAll( '.jsErrorMessage' );
        let el_button = current_form.querySelectorAll( '.jsButtonSubmit > *:first-child' );

        let message  = '<p>Sincere apologies, we&apos;re having trouble getting your message';
            message += 'sent to the server. Please <a href="mailto:me@jeffersonreal.com">click';
            message += 'here</a> to send a message using the email client on your device.</p>';

        el_error.append( '<p class="alert">' + textStatus + ': ' + errorThrown + '</p>' );
        el_error.append( message );
        el_error.style.display = 'block';
        el_button.textContent = 'Error';

        // If logged in, dump to console
        if ( document.body.classList.contains( 'logged-in' ) ) {
            console.log( jqXHR );
            console.log( textStatus );
            console.log( errorThrown );
        }
    }


    /**
     * Fire the init function on 'doc ready'.
     * 
     */
    var interval = setInterval( function() {
        if(document.readyState === 'complete') {
            clearInterval( interval );
            /* Start the reactor */
            form_init();
        }
    }, 100);


})();
