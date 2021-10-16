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

    // grab wp_localize_script variables
    let wp_ajax_url = hb_contact_form_vars.wp_ajax_url;
    let wp_admin_email = hb_contact_form_vars.wp_admin_email;
    let wp_nonce = hb_contact_form_vars.wp_nonce;
    let wp_action = hb_contact_form_vars.wp_action;

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
                form_submit( form );
            } );
        } );
    };


    /**
     * Handle the submitted form.
     * 
     * @param {object} form: The submitted form data.
     * 
     */
    function form_submit( form ) {

        // Remember which form was used
        current_form = form;

        // Get form values
        let form_data = {
            nonce:      wp_nonce,
            action:     wp_action,
            name:       form.querySelector( '[name="HB__form_name"]' ).value,
            email:      form.querySelector( '[name="HB__form_email"]' ).value,
            message:    form.querySelector( '[name="HB__form_message"]' ).value
        };

console.log(form_data);

        // Update button
        form.querySelectorAll( '.jsButtonSubmit' ).disabled = true;
        form.querySelectorAll( '.jsButtonSubmit > *:first-child' )[0].textContent = 'One mo...';



        fetch( wp_ajax_url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Cache-Control': 'no-cache',
            },
            body: new URLSearchParams( {
                action: wp_action,
                nonce:      wp_nonce,
                name:       form.querySelector( '[name="HB__form_name"]' ).value,
                email:      form.querySelector( '[name="HB__form_email"]' ).value,
                message:    form.querySelector( '[name="HB__form_message"]' ).value
            } )
        } )
           .then( ( response ) => response.json() )
            .then( ( data ) => {
                if ( data ) {
                    console.log(data);
                }
            } )
            .catch( ( error ) => {
                console.error( error );
            } );


/*

        // Ajax request
        jQuery.ajax( {
            method: "POST",
            url: wp_ajax_url,
            timeout: 3000,
            data: form_data,
            encode: true,
            dataType: 'json',
            success: ajax_response,
            error: ajax_error
        } );
*/


    } //func end



    /**
     * Do this on successful ajax response.
     * 
     * @param {json} data The json response sent by the server.
     * 
     */
    function ajax_response( data, textStatus, jqXHR ) {

        form = current_form;

        // Get the elems of the form that was used
        let el_success = form.querySelectorAll( '.jsSuccessMessage' )[0];
        let el_error = form.querySelectorAll( '.jsErrorMessage' )[0];
        let el_hide = form.querySelectorAll( '.jsHideOnSuccess' )[0];
        let el_button = form.querySelectorAll( '.jsButtonSubmit > *:first-child' )[0];

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
        // re-enable button
    //    form.querySelectorAll( '.jsButtonSubmit' ).disabled = false;
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

        form = current_form;

        let el_error = form.querySelectorAll( '.jsErrorMessage' )[0];
        let el_button = form.querySelectorAll( '.jsButtonSubmit > *:first-child' )[0];

        let message  = '<p>Sincere apologies, something went wrong.</p>';
            message += '<p>Please <a href="mailto:' + wp_admin_email + '">click ';
            message += 'here</a> to send a message using the email app on your device.</p>';

        let error = '<p class="alert">' + textStatus + ': ' + errorThrown + '</p>';

        el_error.innerHTML = error;
        el_error.innerHTML += message;
        el_error.style.display = 'block';
        el_button.textContent = 'Error';

        // If logged in, dump to console
        if ( document.body.classList.contains( 'logged-in' ) ) {
            console.log( jqXHR );
            console.log( textStatus );
            console.log( errorThrown );
        }
        // re-enable button
    //    form.querySelectorAll( '.jsButtonSubmit' ).disabled = false;
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
