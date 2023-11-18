/**
 * Bigup Contact Form - Client Controller.
 *
 * Control client form submission performed with fetch and the
 * WP REST api. All data transmitted in JSON for extensibility.
 * 
 * @package bigup_contact_form
 * @author Jefferson Real <me@jeffersonreal.uk>
 * @copyright Copyright (c) 2023, Jefferson Real
 * @license GPL2+
 * @link https://jeffersonreal.uk
 */

 const contactForm = () => {
	'use strict'


    /**
     * For debugging, set 'debug = true'. Output will be
     * sent to the console.
     */
    let debug = false


    /**
     * Holds the start time of the request for debugging.
     */
    let start


    /**
     * Log timestamps in debug mode.
     * @returns milliseconds since function call.
     */
     function stopwatch() {
        let elapsed = Date.now() - start
        return elapsed.toString().padStart( 5, '0' )
    }


    /**
     * Allowed MIME type array.
     * 
     * Eventually this should be populated from form plugin settings.
     */
	const allowedMimeTypes = [
		'image/jpeg',																// .jpeg
		'image/png',																// .png
		'image/gif',																// .gif
		'image/webp',																// .webp
		'image/heic',																// .heic
		'image/heif',																// .heif
		'image/avif',																// .avif
		'image/svg+xml',															// .sgv
		'text/plain',																// .txt
		'application/pdf',															// .pdf
		'application/vnd.oasis.opendocument.text',									// .odt
		'application/vnd.oasis.opendocument.spreadsheet',							// .ods
		'application/vnd.openxmlformats-officedocument.wordprocessingml.document',	// .docx
		'application/msword',														// .doc
		'application/vnd.ms-excel',													// .xls
		'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 		// .xlsx
		'application/zip',															// .zip
		'application/vnd.rar'														// .rar
	]


    /**
     * Grab WP localize vars.
     * 
     * wp_localize_bigup_contact_form_vars.*
     * .rest_url;
     * .rest_nonce;
     * .admin_email;
     * 
     */
    const wp = wp_localize_bigup_contact_form_vars


    /**
     * Prepare the form ready for input.
     * 
     */
    function formInit() {

        // Hide the honeypot input field(s)
        let honeypots = document.querySelectorAll( '.saveTheBees' )
		honeypots.forEach( honeypot => {
			if ( honeypot.style.display !== "none" ) {
				honeypot.style.display = "none"
			}
		} )

        // Attach listeners to the form(s)
        document.querySelectorAll( '.bigup__form' ).forEach( form => {

			// Submit.
            form.addEventListener( 'submit', handleFormSubmit )

			// File upload.
			const fileUpload = form.querySelector( '.bigup__customFileUpload_input' )
			fileUpload.addEventListener( 'change', updateFileList )
        } )
    }


    /**
     * True when the form is processing a submission.
     */
    let formBusy = false


    /**
     * Handle the submitted form.
     * 
     * Calls all functions to perform the form submission, and handle
     * user feedback displayed over the form as 'popout messages'.
     * Popout transitions and fetch request are performed asynchronously.
     * 
     * @param {SubmitEvent} event
     * 
     */
    async function handleFormSubmit( event ) {

        event.preventDefault()
        start = Date.now()
        if( debug ) console.log( 'Time | Start/Finish | Function | Target' )
        if( debug ) console.log( stopwatch() + ' |START| handleFormSubmit' )

        const form = event.currentTarget
        const output = form.querySelector( '.bigup__form_output' )
        let classes = [ 'bigup__form-popout', 'bigup__alert' ]

        // boot bots if honeypot is filled.
        if ( form.querySelector( '[name="required_field"]' ).value != '' ) {
            document.documentElement.remove()
            window.location.replace( "https://en.wikipedia.org/wiki/Robot" )
        }

		const formData    = new FormData()
		const textInputs  = form.querySelectorAll( '.bigup__form_input' )
		const fileInput   = form.querySelector( '.bigup__customFileUpload_input' )

		textInputs.forEach( input => {
			formData.append( input.name, input.value )
		} )

		if ( fileInput ) {
			const files = fileInput.files

			// Loop through each of the selected files.
			for( let i = 0; i < files.length; i++ ){
				let file = files[ i ]
				// Check the file type
				if ( allowedMimeTypes.includes( file.type ) ) {

					// Add the file to the form's data.
					formData.append( 'files[]', file, file.name )

				} else {

					// Animate an error message for bad MIME type.
					const fileExt = file.name.split( '.' ).pop()
					classes = [ ...classes, 'bigup__alert-danger' ]
					output.style.display = 'flex'
					await transition( output, 'opacity', '0' )
					await removeChildren( output )
					await popoutsIntoDom( output, [ `The selected file type ".${fileExt}" is not allowed` ], classes )
					await transition( output, 'opacity', '1' )
					await pause( 5000 )
					await transition( output, 'opacity', '0' )
					await removeChildren( output )
					output.style.display = 'none'
					return
				}
			}
		}

        // Fetch params.
        const url = wp.rest_url
        const fetchOptions = {
            method: "POST",
            headers: {
                "X-WP-Nonce"    : wp.rest_nonce,
                "Accept"        : "application/json"
            },
            body: formData,
        }

        // Async form submission timeline
        try {

            formBusy = true
            lockForm( form )
            output.style.display = 'flex'

            await popoutsIntoDom( output, [ "Connecting..." ], classes )

            let [ result, ] = await Promise.all( [
                fetchHttpRequest( url, fetchOptions ),
                transition( output, 'opacity', '1' )
            ] )
            result.class = ( result.ok ) ? 'success' : 'danger'
            classes = [ ...classes, 'bigup__alert-' + result.class ]

			// Animate the popout messages.
            await transition( output, 'opacity', '0' )
            await removeChildren( output )
            await popoutsIntoDom( output, result.output, classes )
            await transition( output, 'opacity', '1' )
            await pause( 5000 )
            await transition( output, 'opacity', '0' )
            await removeChildren( output )

			// Clean up the form.
            if ( result.ok ) {
                let inputs = form.querySelectorAll( '.bigup__form_input' )
                inputs.forEach( input => { input.value = '' } )
				removeChildren( form.querySelector( '.bigup__customFileUpload_fileList' ) )
            }
            output.style.display = 'none'
            formBusy = false

        } catch ( error ) {
            console.error( error )
        } finally {
            if( debug ) console.log( stopwatch() + ' | END | handleFormSubmit' )
        }

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
    async function fetchHttpRequest( url, options ) {

        try {
            if( debug ) console.log( `${stopwatch()} |START| Fetch request` )
            const controller = new AbortController()
            const abort = setTimeout( () => controller.abort(), 14000 )
        
            const response = await fetch( url, { ...options, signal: controller.signal } )
            clearTimeout( abort )
            const result = await response.json()
            result.ok = response.ok
            if ( typeof result.output === 'string' ) result.output = [ result.output ]
            if ( ! result.ok ) throw result
            return result

        } catch ( error ) {
            
            if ( ! error.output ) {
                // error is not a server response, so display a generic error.
                error.output = [ 'Failed to establish a connection to the server.' ]
                error.ok = false
				console.error( error )
            }
            for ( const message in error.output ) {
                console.error( makeHumanReadable( error.output[ message ] ) )
            }
            return error

        } finally {
            if( debug ) console.log( `${stopwatch()} | END | Fetch request` )
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
    function makeHumanReadable( string ) {
        const tags = /(?<!\([^)]*?)<[^>]*?>/g
        const humanReadable = /(\([^\)]*?\))|[ \p{L}\p{N}\p{M}\p{P}]/ug
        const badWhitespaces = /^\s*|\s(?=\s)|\s*$/g
        let notags = string.replace( tags, '' )
        let notagsHuman = notags.match( humanReadable ).join( '' )
        let notagsHumanClean = notagsHuman.replace( badWhitespaces, '' )
        return notagsHumanClean
    }


    /**
     * Remove all child nodes from a dom node.
     * 
     * @param {object} parent The dom node to remove all child nodes from.
     * 
     */
    function removeChildren( parent ) {

        if( debug ) console.log( `${stopwatch()} |START| removeChildren | ${parent.classList}` )
        return new Promise( ( resolve ) => {
            try {
                while ( parent.firstChild ) {
                    parent.removeChild( parent.firstChild )
                }
                resolve( 'Child nodes removed successfully.' )
            } catch ( error ) {
                reject( error )
            } finally {
                if( debug ) console.log( `${stopwatch()} | END | removeChildren | ${parent.classList}` )
            }
        } )
    }


    /**
     * Helper function to async pause.
     * 
     * @param {integer} milliseconds Duration to pause.
     * 
     */
    function pause( milliseconds ) { 
        return new Promise( ( resolve ) => { 
            setTimeout( () => {
                resolve( 'Pause completed successfully.' )
            }, milliseconds )
        } )
    }


    /**
     * Lock the form while the form is processing.
     * 
     * @param {object} form element
     */
    function lockForm( form ) {

        if( debug ) console.log( `${stopwatch()} |START| lockForm | Locked` )

		// Add disabled styles.
		const sections = form.querySelectorAll( '.bigup__form_section' )
        sections.forEach( section => { section.classList.add( 'disabled' ) } )

		// Disable inputs with html flag.
		const inputs = form.querySelectorAll( ':is( input, textarea )' )
        inputs.forEach( input => { input.disabled = true } )

		// Change button label.
		const buttonLabel = form.querySelector( '.bigup__form_submit > *:first-child' )
        let idleText = buttonLabel.innerText
        buttonLabel.innerText = '[Busy]'

		// Revert when we detect form is no longer busy.
        let unlockForm = setInterval( () => {
            if ( ! formBusy ) {
                clearInterval( unlockForm )
                sections.forEach( section => { section.classList.remove( 'disabled' ) } )
				inputs.forEach( input => { input.disabled = false } )
                buttonLabel.innerText = idleText
                if( debug ) console.log( `${stopwatch()} | END | lockForm | Unlocked` )
            }
        }, 250 )
    }


    /**
     * Create an array of popout message elements and insert into dom.
     * 
     * @param {object} parentElement The parent node to append to.
     * @param {array}  messageArray An array of messages as strings.
     * @param {array}  classes An array of classes.
     * 
     */
    function popoutsIntoDom( parentElement, messageArray, classes ) {

        if( debug ) console.log( `${stopwatch()} |START| popoutsIntoDom | ${messageArray[ 0 ]}` )
        return new Promise( ( resolve, reject ) => {
            try {
                if ( ! parentElement || parentElement.nodeType !== Node.ELEMENT_NODE ) {
                    throw new TypeError( `parentElement must be an element node.` )
                } else if ( ! isIterable( messageArray ) ) {
                    throw new TypeError( `messageArray must be non-string iterable. ${typeof messageArray} found.` )
                }
                let popouts = []
                messageArray.forEach( ( message ) => {
                    let p = document.createElement( 'p' )
                    p.innerText = makeHumanReadable( message )
                    classes.forEach( ( className ) => {
                        p.classList.add( className )
                    } )
                    parentElement.appendChild( p )
                    popouts.push( p )
                } )
                resolve( popouts )
            } catch ( error ) {
                reject( error )
            } finally {
                if( debug ) console.log( `${stopwatch()} | END | popoutsIntoDom | ${messageArray[ 0 ]}` )
            }
        } )
    }


    /**
     * Transition a single element node with a callback on completion.
     *
     * No animation is performed here, this function expects a transition
     * duration to be set in CSS, otherwise the promise will not resolve as
     * no 'transitionend' event will be fired.
     * 
     * Built in event listener was failing due to browser not initialising the
     * new dom node in time for the new event listener. This problem wouldn't
     * exist if the nodes weren't being created/removed on the fly.
     * 
     * @param {object} node Element bound using bind() by caller.
     * @param {string} property The css property to transition.
     * @param {string} value The css value to transition to.
     * @return {Promise} A promise that resolves when the transition is complete.
     * 
     */
    function transitionToResolve( property, value ) {

        return new Promise( ( resolve ) => {
            try {
                if( debug ) console.log( `${stopwatch()} |START| transition | ${this.classList} : ${property} : ${value}` )
                this.style[ property ] = value

                // Custom event listener to resolve the promise.
                let transitionComplete = setInterval( () => {
                    let style = getComputedStyle( this )
                    if ( style.opacity === value ) {
                        clearInterval( transitionComplete )
                        if( debug ) console.log( `${stopwatch()} | END | transition | ${this.classList} : ${property} : ${value}` )
                        resolve( 'Transition complete.' )
                    }
                }, 10 )
            } catch ( error ) {
                reject( error )
            }
        } )
    }


    /**
     * Transition node(s) in parallel with resolved promise on completion.
     * Accepts a single node or an array of nodes to provide a common interface
     * for all element transitions.
     * 
     * Expects a transition duration to be set in CSS.
     * 
     * @param {array}  elements An array of elements.
     * @param {string} property The css property to transition.
     * @param {string} value The css value to transition to.
     * @return {Promise} A promise that resolves when all transitions are complete.
     * 
     */
    async function transition( elements, property, value ) {

        if ( ! isIterable( elements ) ) elements = [ elements ]
        if ( isIterable( elements )
            && elements.every( ( element ) => { return element.nodeType === 1 } ) ) {
            // we have an array of element nodes.
            const promises = elements.map( ( node ) => transitionToResolve.bind( node )( property, value ) )
            let result = await Promise.all( promises )
            return result

        } else {
            throw new TypeError( 'elements must be a non-string iterable. ' + typeof elements + ' found.' )
        }
    }


    /**
     * Check if passed variable is iterable.
     * 
     */
    function isIterable( object ) {
        // checks for null and undefined
        if ( object == null ) {
          return false
        }
        return typeof object[ Symbol.iterator ] === 'function'
    }


    /**
     * Fire formInit() on 'doc ready'.
     * 
     */
    let docReady = setInterval( () => {
        if ( document.readyState === 'complete' ) {
            clearInterval( docReady )
            formInit()
        }
    }, 250 )


	/**
	 * Update the file select custom input with details of selected files.
	 */
	const updateFileList = ( event ) => {
		const input  = event.currentTarget
		const output = input.parentElement.nextElementSibling
		const list   = document.createElement( "ul" )
		removeChildren( output )
		output.appendChild( list )
		for ( var i = 0; i < input.files.length; ++i ) {
			list.innerHTML += '<li>' + input.files.item( i ).name + '</li>'
		}
	}
}

contactForm()
