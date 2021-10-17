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
     * form can be updated with response data.
     * 
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

        // Attach submit listener callback to the form(s)
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

        // Update button
//        form.querySelector( '.jsButtonSubmit' ).disabled = true;
        form.querySelector( '.jsButtonSubmit > *:first-child' ).textContent = 'One mo...';

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
            .then( ( response ) => {
                if ( !response.ok ) {
                    throw new Error( "HTTP error, status = " + response.status );
                }
                return response.json();
            } )
            .then( ( json ) => {
                if ( json ) {
                    server_response( json );
                }
            } )
            .catch( ( error ) => {
                console.error( error );
                http_error( error );
            } );

    } //func end



    /**
     * Do this on successful ajax response.
     * 
     * @param {json} data The json response sent by the server.
     * 
     */
    function server_response( data ) {

        form = current_form;

        // Get the elems of the form that was used
        let title = form.querySelector( '.jsSuccessMessage' );
        let output = form.querySelector( '.jsOutput' );
        let hidden_form = form.querySelector( '.jsHideForm' );
        let button = form.querySelector( '.jsButtonSubmit > *:first-child' );

console.log(data);

        // Check json ajax response
/*        if ( data.result == 'success' ) {

            // Output message
            title.textContent = 'Message Sent!';
            output.style.display = "none";
            hidden_form.style.display = "none";

        } else if ( data.result == 'settings_missing' ) {

            output.append( '<p>SMTP settings are incomplete. Please alert website admin.</p>' );
            output.style.display = 'block';
            button.textContent = 'Error';

        } else if ( data.result == 'settings_invalid' ) {

            output.append( '<p>SMTP settings are invalid. Please alert website admin.</p>' );
            output.style.display = 'block';
            button.textContent = 'Error';

        } else {

            data.errors.forEach( (error, message) => output.append( '<p>' + error + ': ' + message + '</p>' ) );
            output.style.display = 'block';
            button.textContent = 'Error';
        }
*/
        // re-enable button
        button.disabled = false;
    }


    /**
     * Do this on server connection failure
     * 
     * @param {object} error An error object return by fetch.
     * 
     */
    function http_error( error ) {

        form = current_form;

        let message = '<p class="alert">Error: ' + error.message + '</p>';
        let fallback  = '<p>Sincere apologies, something went wrong.</p>';
            fallback += '<p>Please <a href="mailto:' + wp_admin_email + '">click ';
            fallback += 'here</a> to send a message using the email app on your device.</p>';

        let output = form.querySelector( '.jsOutput' );
        output.innerHTML = message;
        output.innerHTML += fallback;
        output.style.display = 'block';

        let button = form.querySelector( '.jsButtonSubmit > *:first-child' );
        button.textContent = 'Error';

        // If logged in, dump to console
        if ( document.body.classList.contains( 'logged-in' ) ) {
            console.log( 'Form input error! Error message will follow if captured...' );
            console.log( error.message );
        }
        // re-enable button
        button.disabled = false;
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
