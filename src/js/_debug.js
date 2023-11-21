/**
 * Set 'debug = true' and output will be sent to the console.
 */
let debug = false

/**
 * Holds the start time of the script.
 */
let startTime = ''

/**
 * Set the start time of the script.
 */
const start = () => startTime = Date.now()

/**
 * Get timestamps.
 * 
 * @return milliseconds since function call.
 */
const stopwatch = () => {
	let elapsed = Date.now() - startTime
	return elapsed.toString().padStart( 5, '0' )
}


export { debug, start, stopwatch }
