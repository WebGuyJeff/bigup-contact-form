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
        let button = form.querySelector( '.jsButtonSubmit' );
        let button_label = form.querySelector( '.jsButtonSubmit > *:first-child' );
        let button_idle_text = button_label.textContent;
        let output = form.querySelector( '.jsOutput' );
        let form_hide = form.querySelector( '.jsHideForm' );

        // Display pending state to user.
        button.disabled = true;
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

        const fetch_options = {
            method: "POST",
            headers: {
                "X-WP-Nonce"    : wp.rest_nonce,
                "Content-Type"  : "application/json",
                "Accept"        : "application/json"
            },
            body: json_string_data,
        };

        const url = wp.rest_url;

        // Send form data and handle response.
        let result = await fetch_http_request( url, fetch_options );


// https://www.smashingmagazine.com/2020/11/comparison-async-await-versus-then-catch/

        console.log( result );


        let div = document.createElement( 'div' );

        if ( typeof result === 'array' || typeof result === 'object' ) {
            for ( const message in result ) {
                let p = document.createElement( 'p' );
                p.innerHTML = result[ message ];
                p.classList.add( 'alert' );
                p.classList.add( 'alert-' + result['class'] );
                div.appendChild( p );
            }

        } else if ( typeof result === 'string' ) {
            let p = document.createElement( 'p' );
            p.innerHTML = ( result ) ? result : 'An unknown error has ocurred. Your message may not have been sent.';
            p.classList.add( 'alert' );
            p.classList.add( 'alert-' + result['class'] );
            div.appendChild( p );
        }

        remove_all_child_nodes( output );
        output.appendChild( div );
        button_label.textContent = button_idle_text;
        button.disabled = false;


    };


    /**
     * Perform a Fetch request with timeout and json response.
     * 
     * controller === abort controller to abort fetch request.
     * timeoutId === abort wrapped in a timer.
     * signal: controller.signal === attach timeout to fetch request.
     * clearTimeout( timeoutId ) === cancel the timer on response.
     * 
     * @param {string} url      The WP plugin REST endpoint url.
     * @param {object} options  An object of fetch API options.
     * @return {object}         An object of message strings and ok flag.
     * 
     */
     async function fetch_http_request( url, options ) {
        const controller = new AbortController();
        const timeoutId = setTimeout( () => controller.abort(), 8000 );
        const fetch_response = await fetch( url, {
            ...options,
            signal: controller.signal
        } );
        clearTimeout( timeoutId );
        // parse response body as JSON.
        const json = await fetch_response.json();
        // attach response 'ok' flag to new object.
        json.ok = fetch_response.ok;
        // if output is empty, create error string from http status.
        if ( ! json.output ) {
            json.output = 'Error ' + fetch_response.status + ': ' + fetch_response.statusText;
            json.ok = false;
        }
        return json;

    }.then( ( response ) => {

        if ( ! response.ok ) {
            throw new Error( response.errors );
        }
        let info = [];
        info['response'] = response.output;
        info['class'] = 'success';
        return info;

    } ).catch( ( error ) => {
        let info = [];
        if ( typeof error !== 'string' ) {
            for ( const message in error ) {
                info.push( message );
            }
        } else if ( error === '' ) {
            info = 'An error was thrown with no explanation.';
        } else {
            info = error;
        }
        info['class'] = 'danger';
        console.log( info );
        return info;
    } );


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
