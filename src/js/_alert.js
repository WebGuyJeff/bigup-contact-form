import { debug, stopwatch } from './_debug'
import { formLock } from './_form-lock'
import { removeChildren, makeHumanReadable } from './_util'


/**
 * Show alerts in a form.
 * 
 * @param {object} form The target form.
 * @param {array} alerts Alert objects to be displayed.
 */
const alertsShow = async ( form, alerts ) => {

	const output = form.querySelector( '.bigup__alert_output' )
	if ( ! output ) return

	// Show.
	if( debug ) console.log( `${stopwatch()} |START| alertsShowWaitHide | ${alerts[ 0 ]}` )
	formLock( form, true )
	output.style.display = 'flex'
	await transition( output, 'opacity', '0' )
	await removeChildren( output )
	await popoutsIntoDom( output, alerts )
	await transition( output, 'opacity', '1' )
	return( 'alert alertsShow complete' )
}


/**
 * Show, wait then hide alerts in a form.
 * 
 * @param {object} form The target form.
 * @param {array} alerts Alert objects to be displayed.
 * @param {int} wait Time to wait.
 */
const alertsShowWaitHide = async ( form, alerts, wait ) => {

	const output = form.querySelector( '.bigup__alert_output' )
	if ( ! output ) return

	// Show.
	if( debug ) console.log( `${stopwatch()} |START| alertsShowWaitHide | ${alerts[ 0 ]}` )
	formLock( form, true )
	output.style.display = 'flex'
	await transition( output, 'opacity', '0' )
	await removeChildren( output )
	await popoutsIntoDom( output, alerts )
	await transition( output, 'opacity', '1' )
	// Wait.
	await pause( wait )
	// Hide.
	await transition( output, 'opacity', '0' )
	await removeChildren( output )
	output.style.display = 'none'
	formLock( form, false )
	if( debug ) console.log( `${stopwatch()} | END | alertsShowWaitHide | ${alerts[ 0 ]}` )
	return( 'alert alertsShowWaitHide complete' )
}


/**
 * Pause with promise.
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
 * Check if passed variable is iterable.
 * 
 */
function isIterable( object ) {
	// Check for null and undefined.
	if ( object === null || object === undefined ) {
		return false
	}
	return typeof object[ Symbol.iterator ] === 'function'
}



/**
 * Create an array of popout message elements and insert into dom.
 * 
 * @param {object} parentElement The parent node to append to.
 * @param {array}  alerts An array of alerts as objects.
 * 
 */
function popoutsIntoDom( output, alerts ) {

	const classBlock  = 'bigup__alert',
		classModifier = {
		'danger': '-danger',
		'success': '-success',
		'info': '-info',
		'warning': '-warning'
	}

	if( debug ) console.log( `${stopwatch()} |START| popoutsIntoDom | ${alerts[ 0 ]}` )
	return new Promise( ( resolve, reject ) => {
		try {
			if ( ! output || output.nodeType !== Node.ELEMENT_NODE ) {
				throw new TypeError( `output must be an element node.` )
			} else if ( ! isIterable( alerts ) ) {
				throw new TypeError( `'alerts' must be non-string iterable. ${typeof alerts} found.` )
			}
			let popouts = []
			alerts.forEach( ( alert ) => {
				let p = document.createElement( 'p' )
				p.innerText = makeHumanReadable( alert.text )
				const classNames = [ classBlock, classBlock + classModifier[ alert.type ] ]
				classNames.forEach( ( className ) => p.classList.add( className ) )
				output.appendChild( p )
				popouts.push( p )
			} )
			resolve( popouts )
		} catch ( error ) {
			reject( error )
		} finally {
			if( debug ) console.log( `${stopwatch()} | END | popoutsIntoDom | ${alerts[ 0 ]}` )
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

	return new Promise( ( resolve, reject ) => {
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


export { alertsShowWaitHide, alertsShow }
