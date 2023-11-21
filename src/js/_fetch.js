import { debug, stopwatch } from './_debug'
import { makeHumanReadable } from './_util'

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


export { fetchHttpRequest }
