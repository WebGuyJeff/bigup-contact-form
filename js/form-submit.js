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

            let info = response.output;
            let alert_class = ( response.ok ) ? 'success' : 'danger';
            //info = ( typeof info === 'object' ) ? Object.values( info ) : info;

console.log(response.ok);


            let div = document.createElement( 'div' );

            if ( typeof info === 'array' || typeof info === 'object' ) {
                for ( const message in info ) {
                    let p = document.createElement( 'p' );
                    p.innerHTML = info[ message ];
                    p.classList.add( 'alert' );
                    p.classList.add( 'alert-' + alert_class );
                    div.appendChild( p );
                }

            } else if ( typeof info === 'string' ) {
                let p = document.createElement( 'p' );
                p.innerHTML = ( info ) ? info : 'An unknown error has ocurred. Your message may not have been sent.';
                p.classList.add( 'alert' );
                p.classList.add( 'alert-' + alert_class );
                div.appendChild( p );
            }

            remove_all_child_nodes( output );
            output.appendChild( div );
            button_label.textContent = button_label_normal;
            button.disabled = false;


        } ).catch( error => {
            console.log( error );
        } );

    };


    function remove_all_child_nodes( parent ) {
        while ( parent.firstChild ) {
            parent.removeChild( parent.firstChild );
        }
    }


    /**
     * Fire the init function on 'doc ready'.
     * 
     */
    var interval = setInterval( function() {
        if ( document.readyState === 'complete' ) {
            clearInterval( interval );
            /* Start the reactor */
            form_init();
        }
    }, 100);


})();
