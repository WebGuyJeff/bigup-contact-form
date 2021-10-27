/**
 * Herringbone Contact Form Client Controller.
 *
 * Control client form submission performed with fetch and the
 * WP REST api. All data transmitted in JSON for adabtability.
 * 
 * @package Herringbone
 * @subpackage HB_Contact_Form
 * @author Jefferson Real <me@jeffersonreal.com>
 * @copyright Copyright (c) 2021, Jefferson Real
 * @license GPL2+
 * 
 */
(function form_sender() {


    /**
     * Grab WP localize vars.
     * 
     * wp_localize_hb_contact_form_vars.*
     * .rest_url;
     * .rest_nonce;
     * .admin_email;
     * 
     */
    wp = wp_localize_hb_contact_form_vars;


    /**
     * Form element vars.
     * 
     * These element vars will be assigned as the form submission
     * is handled and used to provide feedback to the user. They are
     * declared here to provide top-level scope.
     * 
     */
     let button;
     let button_label;
     let output;
     let form_hide;

    /**
     * Prepare the form ready for input.
     * 
     */
    function form_init() {

        // Hide the honeypot input field(s)
        let honeypot = document.querySelectorAll( '.jsSaveTheBees' );
        honeypot.forEach( input => { input.style.display = "none" } );

        // Attach submit listener callback to the form(s)
        document.querySelectorAll( '.jsFormSubmit' ).forEach( form => {
            form.addEventListener( 'submit', handle_form_submit );
        } );
    };


    /**
     * Perform a Fetch request with json response.
     * 
     */
    async function http_request( url, options ) {
        const fetch_response = await fetch( url, options );
        const json = await fetch_response.json();
        json.ok = fetch_response.ok;
        if ( ! json.ok ) {
            json.errors = 'Error ' + fetch_response.status + ': ' + fetch_response.statusText;
        }
        return json;
    }


    /**
     * Handle the submitted form.
     * 
     * Process:
     *      fetch    initiate http request using fetch api.
     *      .then    parse json to js object.
     *      .then    process response to user output.
     *      .catch   process errors that came down chain.
     * 
     * @param {SubmitEvent} event
     * 
     */
    async function handle_form_submit( event ) {

        // prevent normal submit action
        event.preventDefault();

        // get the element the event handler was attached to.
        const form = event.currentTarget;

        // if honeypot has a value ( bye bye bot )
        if ( '' != form.querySelector( '[name="required_field"]' ).value ) {
            document.documentElement.remove();
            window.location.replace( "https://en.wikipedia.org/wiki/Robot" );
        }

        // Get elements of submitted form.
        button = form.querySelector( '.jsButtonSubmit' );
        button_label = form.querySelector( '.jsButtonSubmit > *:first-child' );
        output = form.querySelector( '.jsOutput' );
        form_hide = form.querySelector( '.jsHideForm' );

        // Display pending state to user.
        button.disabled = true;
        let button_label_normal = button_label.textContent;
        button_label.textContent = '[busy]';
        let p = document.createElement( "p" );
        p.innerHTML = "Connecting...";
        remove_all_child_nodes( output );
        output.appendChild( p );


        output.style.display = 'block';

        // Grab `FormData` then convert to plain obj, then to json string.
        const form_data = new FormData( form );
        const plain_obj_data = Object.fromEntries( form_data.entries() );
        const json_string_data = JSON.stringify( plain_obj_data );

        // Fetch options object
        const fetch_options = {
            method: "POST",
            headers: {
                "X-WP-Nonce"    : wp.rest_nonce,
                "Content-Type"  : "application/json",
                "Accept"        : "application/json"
            },
            body: json_string_data,
        };

        // Rest endpoint url
        const url = wp.rest_url;

        // Send form data and handle response.
        http_request( url, fetch_options ).then( response => {

            let alert_class = ( response.ok ) ? 'success' : 'danger';
            let info = ( response.ok ) ? response.message : Object.values( response.errors );

            let div = document.createElement( 'div' );

            if ( Array.isArray( info ) ) {
                info.forEach( message => {
                    let p = document.createElement( 'p' );
                    p.innerHTML = message;
                    p.classList.add( 'alert' );
                    p.classList.add( 'alert-' + alert_class );
                    div.appendChild( p );
                } );

            } else if ( typeof info === 'string' ) {
                let p = document.createElement( 'p' );
                p.innerHTML = ( message ) ? message : "An unknown error has ocurred. Your message may not have been sent.";
                p.classList.add( 'alert' );
                p.classList.add( 'alert-' + alert_class );
                div.appendChild( p );
            }

            remove_all_child_nodes( output );
            output.appendChild( div );

        button_label.textContent = button_label_normal;
        button.disabled = false;
    } );


    function remove_all_child_nodes( parent ) {
        while ( parent.firstChild ) {
            parent.removeChild( parent.firstChild );
        }
    }



/*
    function server_response( data ) {

        form = current_form;

        // Get the elems of the form that was used
        let output = form.querySelector( '.jsOutput' );
        let hidden_form = form.querySelector( '.jsHideForm' );
        let button = form.querySelector( '.jsButtonSubmit > *:first-child' );

console.log(data);

        // Check json ajax response
       if ( data.result == 'success' ) {

            // Output message

            output.append( '<p>Message Delivered!</p>' );
            output.style.display = 'block';
            button.textContent = '👍';

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

        // re-enable button
        button.disabled = false;
    }//server_response end

    function http_error( error ) {

        let form = current_form;

        let email = wp.wp_admin_email;

        let message = '<p class="alert">Error: ' + error.message + '</p>';
        let fallback  = '<p>Sincere apologies, something went wrong.</p>';
            fallback += '<p>Please <a href="mailto:' + email + '">click ';
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
    }//http_error end
*/



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
