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
        let output = form.querySelector( '.HB__form_output' );

        // Display pending state to user.
        button.disabled = true;
        button_label.textContent = '[busy]';
        let p = document.createElement( "p" );
        p.classList.add( 'alert' );
        p.classList.add( 'alert-hover' );
        p.innerHTML = "Connecting...";
        remove_all_child_nodes( output );
        output.appendChild( p );
        output.style.display = 'flex';

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
        let result = {};
        try {
            result = await fetch_http_request( url, fetch_options );
            result.output = ( typeof result.output === 'string' ) ? [ result.output ] : result.output;
            if ( ! result.ok ) {
                throw result;
            }
            result.class = 'success';
    
        } catch ( error ) {
            if ( ! error.output ) {
                // error is not a server response.
                console.error( error );
                result.output = [ 'Failed to establish a connection to the server.' ];

            } else {
                // error is the thrown result and contains server message(s).
                for ( const message in error.output ) {
                    console.error( make_human_readable( error.output[ message ] ) );
                }
            }
            result.class = 'danger';

        } finally {
            // build result output and insert into dom.
            let div = document.createElement( 'div' );

            for ( const message in result.output ) {
                let p = document.createElement( 'p' );
                p.innerHTML = make_human_readable( result.output[ message ] );
                p.classList.add( 'alert' );
                p.classList.add( 'alert-hover' );
                p.classList.add( 'alert-' + result.class );
                div.appendChild( p );
            }
            remove_all_child_nodes( output );
            output.appendChild( div );
            // hide the message after timer runs out.
            setTimeout( () => {
                output.style.display = 'none';
                button_label.textContent = button_idle_text;
                button.disabled = false;
            }, 5000)
        }
    };


    /**
     * Perform a Fetch request with timeout and json response.
     * 
     * Timeouts:
     *     6s for webserver to SMTP server.
     *     8s for SMTP send response to webserver.
     *     14s for front end as fallback in lieu of server response.
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
        const timeoutId = setTimeout( () => {
                controller.abort();
        }, 14000 );
        const fetch_response = await fetch( url, {
            ...options,
            signal: controller.signal
        } );
        clearTimeout( timeoutId );
        // parse response body as JSON.
        const response_body = await fetch_response.json();
        // copy response 'ok' flag to new object.
        response_body.ok = fetch_response.ok;
        // if output is empty, fallback to string from http status.
        if ( ! response_body.output ) {
            response_body.output = [ 'Error ' + fetch_response.status + ': ' + fetch_response.statusText ];
            response_body.ok = false;
        }
        return response_body;
    }


    /**
     * Clean strings for human output
     * 
     * This function uses regex patterns to clean strings in 3 stages:
     * 
     * 1) Remove all html tags.
     * 2) Remove anything that is not:
     *      (\([^\)]*?\)) - content enclosed in ()
     *      ' '   - spaces
     *      \p{L} - letters
     *      \p{N} - numbers
     *      \p{M} - marks (accents etc)
     *      \p{P} - punctuation
     * 3) Trim and replace multiple spaces with a single space.
     * 
     * @link https://www.regular-expressions.info/unicode.html#category
     * @param {*} string The dirty string.
     * @returns          The cleaned string.
     */
    function make_human_readable( string ) {
        const tags = /<[^>]*>/gm;
        const human_readable = /(\([^\)]*?\))|[ \p{L}\p{N}\p{M}\p{P}]/ugm;
        const bad_whitespaces = /^\s*|\s(?=\s)|\s*$/gm;
        let notags = string.replace( tags, '' );
        let notags_human = notags.match( human_readable ).join('');
        let notags_human_clean = notags_human.replace( bad_whitespaces, '' );
        return notags_human_clean;
    }

///(?:(?!(<[^>]*?>))|(\(.*?\))|(<.*?>))/gu


    /**
     * Remove all child nodes from a dom node.
     * 
     */
     function remove_all_child_nodes( parent ) {
        while ( parent.firstChild ) {
            parent.removeChild( parent.firstChild );
        }
    }


    /**
     * Fire form_init() on 'doc ready'.
     * 
     */
    var interval = setInterval( function() {
        if ( document.readyState === 'complete' ) {
            clearInterval( interval );
            form_init();
        }
    }, 100);


})();
