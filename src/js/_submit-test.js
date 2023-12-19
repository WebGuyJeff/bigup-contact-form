import { debug, start, stopwatch } from './_debug'
import { fetchHttpRequest } from './_fetch'
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
const wpLocalized = bigupContactFormWpInlinedAdmin


/**
 * Test values to pass in fetch request.
 * 
 * Backend controller expects values for 'email', 'name' and 'message'.
 */
const testValues = {
	'email': 'test@email.test',
	'name': 'Test Email',
	'message': 'This is a test message. If you receive this, your email settings are OK! 🥳',
}


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

	// Set test values in formData.
	const formData   = new FormData()
	for ( const prop in testValues ) {
		formData.append( prop, testValues[ prop ] )
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

	} catch ( error ) {
		console.error( error )
	} finally {
		if( debug ) console.log( stopwatch() + ' | END | handleSubmit' )
	}

}


export { submitTest, wpLocalized }
