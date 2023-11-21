import { debug, start, stopwatch } from './_debug'
import { disallowedTypes } from './_file-upload'
import { removeChildren, makeHumanReadable } from './_util'
import { alertsShowWaitHide, alertsShow } from './_alert'


/**
 * Perform a test submission in the admin area.
 */


/**
 * Grab WP localize vars.
 * 
 * wp_localize_bigup_contact_form_vars.rest_url
 * wp_localize_bigup_contact_form_vars.rest_nonce
 * 
 */
const wpLocalized = wp_localize_bigup_contact_form_admin


/**
 * Handle the submitted form.
 * 
 * Perform a test email send and display user feedback as popout alerts.
 * 
 * @param {SubmitEvent} event
 * 
 */
async function submitTest( event ) {

	event.preventDefault()
	if( debug ) start()
	if( debug ) console.log( 'Time | Start/Finish | Function | Target' )
	if( debug ) console.log( stopwatch() + ' |START| handleSubmit' )

	const form = event.currentTarget.closest( 'form' )

	const formData   = new FormData()
	const textInputs = form.querySelectorAll( ':is( input, textarea )' )

	textInputs.forEach( input => {
		formData.append( input.name, input.value )
	} )

	// Fetch params.
	const url = wpLocalized.rest_url
	const fetchOptions = {
		method: "POST",
		headers: {
			"X-WP-Nonce"    : wpLocalized.rest_nonce,
			"Accept"        : "application/json"
		},
		body: formData,
	}


	try {

		// Display pre-fetch alerts in parrallel with fetch.
		const preFetchAlerts = [ { 'text': 'Connecting...', 'type': 'info' } ]
		let [ result, ] = await Promise.all( [
			fetchHttpRequest( url, fetchOptions ),
			alertsShow( form, preFetchAlerts )
		] )

		// Display post-fetch alerts.
		const postFetchAlerts = []
		result.output.forEach( message => postFetchAlerts.push( { 'text': message, 'type': ( result.ok ) ? 'success' : 'danger' } ) )
		alertsShowWaitHide( form, postFetchAlerts, 5000 )

		// Clean up form if email was sent.
		if ( result.ok ) {
			let inputs = form.querySelectorAll( '.bigup__form_input' )
			inputs.forEach( input => { input.value = '' } )
			const fileList = form.querySelector( '.bigup__customFileUpload_output' )
			if ( fileList ) removeChildren( fileList )
		}

	} catch ( error ) {
		console.error( error )
	} finally {
		if( debug ) console.log( stopwatch() + ' | END | handleSubmit' )
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


export { submitTest, wpLocalized }
