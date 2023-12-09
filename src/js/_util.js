import { debug, stopwatch } from './_debug'


/**
 * Remove all child nodes from a dom node.
 * 
 * @param {object} parent The dom node to remove all child nodes from.
 * 
 */
function removeChildren( parent ) {

	if( debug ) console.log( `${stopwatch()} |START| removeChildren | ${parent.classList}` )
	return new Promise( ( resolve, reject ) => {
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
	if ( typeof string !== 'string' ) {
		console.error( `makeHumanReadable expects a string, but ${typeof string} received.`, string )
		return 'error getting message'
	}
	const tags = /(?<!\([^)]*?)<[^>]*?>/g
	const humanReadable = /(\([^\)]*?\))|[ \p{L}\p{N}\p{M}\p{P}]/ug
	const badWhitespaces = /^\s*|\s(?=\s)|\s*$/g
	let notags = string.replace( tags, '' )
	let notagsHuman = notags.match( humanReadable ).join( '' )
	let notagsHumanClean = notagsHuman.replace( badWhitespaces, '' )
	return notagsHumanClean
}


export { removeChildren, makeHumanReadable }
