import { debug, start, stopwatch } from './_debug'
import { fetchHttpRequest } from './_fetch'
import { disallowedTypes } from './_file-upload'
import { removeChildren } from './_util'
import { alertsShowWaitHide, alertsShow } from './_alert'


/**
 * Handle frontend form submissions.
 */


/**
 * Grab WP localize vars.
 * 
 * wp_localize_bigup_contact_form_vars.rest_url
 * wp_localize_bigup_contact_form_vars.rest_nonce
 * 
 */
const wpLocalized = wp_localize_bigup_contact_form_frontend


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
async function submit( event ) {

	event.preventDefault()
	if( debug ) start()
	if( debug ) console.log( 'Time | Start/Finish | Function | Target' )
	if( debug ) console.log( stopwatch() + ' |START| handleSubmit' )

	const form = event.currentTarget

	// boot bots if honeypot is filled.
	if ( form.querySelector( '[name="required_field"]' ).value !== '' ) {
		document.documentElement.remove()
		window.location.replace( "https://en.wikipedia.org/wiki/Robot" )
	}

	const formData   = new FormData()
	const textInputs = form.querySelectorAll( ':is( input, textarea )' )
	const fileInput  = form.querySelector( '.bigup__customFileUpload_input' )

	textInputs.forEach( input => {
		formData.append( input.name, input.value )
	} )

	// Handle attachments if file input present.
	if ( fileInput ) {

		// Check for disallowed MIME types.
		if ( disallowedTypes.detected ) {
			const fileExts   = disallowedTypes.list.join( ', ' )
			const fileAlerts = [ { 'text': `Files of type ".${fileExts}" are not allowed. Please amend your file selection.`, 'type': 'danger' } ]
			const wait       = 5000
			await alertsShowWaitHide( form, fileAlerts, wait )

			// User needs to amend file selection, so we quit here.
			return

		} else {
			// Add files to the form data.
			const files = fileInput.files
			for( let i = 0; i < files.length; i++ ){
				let file = files[ i ]
				formData.append( 'files[]', file, file.name )
			}
		}
	}

	// Fetch params.
	const url = wpLocalized.rest_url
	const fetchOptions = {
		method: "POST",
		headers: {
			"X-WP-Nonce" : wpLocalized.rest_nonce,
			"Accept"     : "application/json"
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


export { submit }
