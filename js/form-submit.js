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

    let start;

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

        start = Date.now();
        console.log( stopwatch() + ' |#####| FUNCTION START');

        // get the element the event handler was attached to.
        const form = event.currentTarget;

        // if honeypot has a value ( bye bye bot )
        if ( '' != form.querySelector( '[name="required_field"]' ).value ) {
            document.documentElement.remove();
            window.location.replace( "https://en.wikipedia.org/wiki/Robot" );
        }

        // Get elements of submitted form.
        const button = form.querySelector( '.jsButtonSubmit' );
        const button_label = form.querySelector( '.jsButtonSubmit > *:first-child' );
        const output = form.querySelector( '.HB__form_output' );

        let classes = [ 'HB__form-popout', 'alert' ];
        const pending_text = "Connecting...";

        // Grab `FormData` then convert to plain obj, then to json string.
        const form_data = new FormData( form );
        const plain_obj_data = Object.fromEntries( form_data.entries() );
        const json_string_data = JSON.stringify( plain_obj_data );

        // Fetch params.
        const url = wp.rest_url;
        const fetch_options = {
            method: "POST",
            headers: {
                "X-WP-Nonce"    : wp.rest_nonce,
                "Content-Type"  : "application/json",
                "Accept"        : "application/json"
            },
            body: json_string_data,
        };

        output.style.display = 'flex';
        toggle_button( button, button_label, '[busy]' );


        let popouts_pending = await insert_popouts_into_dom( output, [ pending_text ], classes );

        // Start fetch and CSS transitions simultaneously.
        let [ ,,result ] = await Promise.all( [
            css_transition( output, 'opacity', '1' ),
            transition_array_of_elements( popouts_pending, 'opacity', '1' ),
            fetch_http_request( url, fetch_options )
        ] );

        result.class = ( result.ok ) ? 'success' : 'danger';
        classes = [ ...classes, 'alert-' + result.class ];

        await transition_array_of_elements( popouts_pending, 'opacity', '0' );
        await remove_all_child_nodes( output );

        let popouts_response = await insert_popouts_into_dom( output, result.output, classes );
        await transition_array_of_elements( popouts_response, 'opacity', '1' );
        await pause( 5000 );

        await transition_array_of_elements( popouts_response, 'opacity', '0' );

        await css_transition( output, 'opacity', '0' );

        await remove_all_child_nodes( output );
        output.style.display = 'none';
        toggle_button( button, button_label, button_idle_text );

        console.log( stopwatch() + ' |#####| FUNCTION END');
    };

    function stopwatch() {
        let elapsed = Date.now() - start;
        return elapsed;
    }


    /**
     * Perform a Fetch request with timeout and json response.
     * 
     * Timeouts:
     *     6s for webserver to SMTP server.
     *     8s for SMTP send response to webserver.
     *     14s for front end as fallback in lieu of server response.
     * 
     * controller - abort controller to abort fetch request.
     * abort - abort wrapped in a timer.
     * signal: controller.signal - attach timeout to fetch request.
     * clearTimeout( timeoutId ) - cancel the timer on response.
     * 
     * @param {string} url      The WP plugin REST endpoint url.
     * @param {object} options  An object of fetch API options.
     * @return {object}         An object of message strings and ok flag.
     * 
     */
    async function fetch_http_request( url, options ) {

        const controller = new AbortController();
        const abort = setTimeout( () => controller.abort(), 14000 );

        try {
            const response = await fetch( url, {
                ...options,
                signal: controller.signal
            } );
            clearTimeout( abort );

            // parse response body as JSON.
            const result = await response.json();
            result.ok = response.ok;
            if ( typeof result.output === 'string' ) result.output = [ result.output ];
            if ( ! result.ok ) throw result;
            return result;

        } catch ( error ) {

            if ( ! error.output ) {
                // error is not a server response.
                error.output = [ 'Failed to establish a connection to the server.' ];
                error.ok = false;
            }
            for ( const message in error.output ) {
                console.error( make_human_readable( error.output[ message ] ) );
            }
            return error;
        }
    }


    /**
     * Clean strings for human output.
     * 
     * This function uses regex patterns to clean strings in 3 stages:
     * 
     * 1) Remove all html tags not inside brackets ()
     *      (?<!\([^)]*?) - do not match if preceeded by a '('
     *      <[^>]*?> - match all <>
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
     * @param {string} string The dirty string.
     * @returns The cleaned string.
     * 
     */
    function make_human_readable( string ) {
        const tags = /(?<!\([^)]*?)<[^>]*?>/g;
        const human_readable = /(\([^\)]*?\))|[ \p{L}\p{N}\p{M}\p{P}]/ug;
        const bad_whitespaces = /^\s*|\s(?=\s)|\s*$/g;
        let notags = string.replace( tags, '' );
        let notags_human = notags.match( human_readable ).join('');
        let notags_human_clean = notags_human.replace( bad_whitespaces, '' );
        return notags_human_clean;
    }


    /**
     * Remove all child nodes from a dom node.
     * 
     * @param {object} parent The dom node to remove all child nodes from.
     * 
     */
    function remove_all_child_nodes( parent ) {
        return new Promise( ( resolve ) => {
            while ( parent.firstChild ) {
                parent.removeChild( parent.firstChild );
            }
            resolve();
        } );
    }


    /**
     * Perform a CSS transition with a callback on completion.
     * 
     * @link https://gist.github.com/davej/44e3bbec414ed4665220
     * @param {object} element   The dom element to perform the transition on.
     * @param {string} property  The CSS property to transition.
     * @param {string} value     The CSS value to transition to.
     * 
     */
    function css_transition( element, property, value ) {
        return new Promise( ( resolve, reject ) => {
            try {

console.log( stopwatch() + ' |START| ' + element + ' : ' + property + ' : ' + value );

                element.style[ property ] = value;
                const resolve_and_cleanup = e => {
                    if ( e.propertyName !== property ) reject( new Error( 'Property name does not match.' ) );
                    element.removeEventListener( 'transitionend', resolve_and_cleanup );

console.log( stopwatch() + ' |END  | ' + element + ' : ' + property + ' : ' + value );

                    resolve();

                }
                element.addEventListener( 'transitionend', resolve_and_cleanup );
            } catch ( error ) {
                console.error( error );
                reject( error );
            }
        } );
    }


    /**
     * Helper function to async pause.
     * 
     * @param {integer} milliseconds Duration to pause.
     * 
     */
    function pause( milliseconds ) { 
        return new Promise( resolve => { 
            setTimeout( () => {
                resolve();
            }, milliseconds )
        } );
    }


    /**
     * Toggle a button between two states and return the old label.
     * 
     * @param {object} button The button element.
     * @param {object} button_label The button label element.
     * @param {string} text The text to set the button to.
     * 
     */
    function toggle_button( button, button_label, text ) {
        if ( button_label.innerText === text ) return text;
        let old_text = button_label.innerText;
        button.disabled = ( button.disabled === true ) ? false : true;
        button_label.innerText = text;
        return old_text;
    }


    /**
     * Create an array of popout message elements and insert into dom.
     * 
     * @param {object} parent_element The parent node to append to.
     * @param {array}  message_array An array of messages as strings.
     * @param {array}  classes An array of classes.
     * 
     */
    function insert_popouts_into_dom( parent_element, message_array, class_array ) {
        return new Promise( ( resolve, reject ) => {
            try {
                if ( ! parent_element || parent_element.nodeType !== Node.ELEMENT_NODE ) {
                    throw new TypeError( 'parent_element must be an element' );
                } else if ( ! Array.isArray( message_array ) ) {
                    throw new TypeError( 'message_array must be an array' );
                }
                popouts = [];
                message_array.forEach( ( message ) => {
                    let p = document.createElement( 'p' );
                    p.innerText = make_human_readable( message );
                    class_array.forEach( ( class_name ) => {
                        p.classList.add( class_name );
                    } );
                    parent_element.appendChild( p );
                    popouts.push( p );
                } );
                resolve( popouts );
            } catch ( error ) {
                console.error( error );
                reject( error );
            }
        } );
    }


    /**
     * Transition an array of elements. Expects a transition duration
     * to be set in CSS.
     * 
     * @param {array}  elements_array An array of elements.
     * @param {string} css_property The css property to transition.
     * @param {string} property_value The css value to transition to.
     * 
     */
    function transition_array_of_elements( elements_array, css_property, property_value ) {
        return new Promise( ( resolve, reject ) => {
            try {
                if ( ! elements_array || ! Array.isArray( elements_array ) ) {
                    throw new TypeError( 'elements_array must be an array' );
                }
                element_transitions = [];
                elements_array.forEach( ( element ) => {
                    element_transitions.push( css_transition( element, css_property, property_value ) );
                } );
                Promise.all( element_transitions ).then( resolve() );
            } catch ( error ) {
                console.error( error );
                reject( error );
            }
        } );
    }


    /**
     * Popout an alert element for a set time.
     * 
     * @param {object} element The dom element to popout.
     * @param {integer} milliseconds The display duration.
     * 
     */
    async function popout_alert( element, milliseconds ) {
        await css_transition( element, 'opacity', '1' );
        await pause( milliseconds );
        await css_transition( element, 'opacity', '0' );
    }


    /**
     * Fire form_init() on 'doc ready'.
     * 
     */
    let interval = setInterval( function() {
        if ( document.readyState === 'complete' ) {
            clearInterval( interval );
            form_init();
        }
    }, 100);

})();
