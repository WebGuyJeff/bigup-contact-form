import { submitTest, wpLocalized } from './_submit-test'

/**
 * Admin client view.
 */


/**
 * Prepare the admin SMTP test button.
 * 
 */
function init() {

	const button = document.querySelector( '.bigup__smtpTest_button' )

	if ( ! button ) return

	/*
	 * Will need to change submit function to handle submit from button, otherwise backend form will have mutiple handlers.
	 */
	button.addEventListener( 'click', submitTest )

	// Enable the submit button now js is ready (disabled by default).
	if ( wpLocalized.settings_ok ) {
		button.disabled = false
	}
}


// Initialise view on 'doc ready'.
let docReady = setInterval( () => {
	if ( document.readyState === 'complete' ) {
		clearInterval( docReady )
		init()
	}
}, 250 )
