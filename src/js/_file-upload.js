import { alertsShowWaitHide } from './_alert'
import { removeChildren } from './_util'


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


const disallowedTypes = {
	'detected': false,
	'list': []
}


/**
 * Remove a file from the selected file list.
 */
const removeFromFileList = ( event ) => {
	event.preventDefault()
	const button  = event.currentTarget,
		input     = button.closest( '.bigup__customFileUpload' ).querySelector( 'input' ),
		{ files } = input,
		filename  = button.nextElementSibling.innerText

	// Create a new file list and append it to the input.
	const dt = new DataTransfer()
	for ( let i = 0; i < files.length; i++ ) {
		const file = files[ i ]
		if ( file.name !== filename ) {
			dt.items.add( file ) // here you exclude the file. thus removing it.
		}
	}
	input.files = dt.files // Assign the updated list.
	updateFileList( input )
}


/**
 * Update the visible list with selected files.
 */
const updateFileList = async ( input ) => {
	const { files }  = input,
		wrapper      = input.closest( '.bigup__customFileUpload' ),
		output       = wrapper.querySelector( '.bigup__customFileUpload_output' ),
		form         = input.closest( 'form' ),
		ul           = document.createElement( "ul" ),
		iconTemplate = wrapper.querySelector( 'template' )
	removeChildren( output )

	disallowedTypes.detected = false
	disallowedTypes.list = []

	// Loop through files.
	for ( var i = 0; i < files.length; ++i ) {
		const file = files[ i ]

		// Check for disallowed MIME types.
		let className = 'bigup__goodFileType'
		if ( ! allowedMimeTypes.includes( file.type ) ) {
			disallowedTypes.detected = true
			disallowedTypes.list.push( file.name.split( '.' ).pop() )
			className = 'bigup__badFileType'
		}

		// Create list element for file.
		const li   = document.createElement( 'li' ),
			span   = document.createElement( 'span' ),
			button = document.createElement( 'button' ),
			icon   = iconTemplate.content.cloneNode( true )
		button.appendChild( icon )
		span.innerText = file.name
		li.classList.add( className )
		li.appendChild( button )
		li.appendChild( span )
		ul.appendChild( li )
		output.appendChild( ul )
		button.addEventListener( 'click', removeFromFileList )
	}

	// Insert list into DOM.
	output.appendChild( ul )

	// Alert user of any disallowed MIME types.
	if ( disallowedTypes.detected ) {
		const fileExts   = disallowedTypes.list.join( ', ' )
		const fileAlerts = [ { 'text': `Files of type ".${fileExts}" are not allowed`, 'type': 'danger' } ]
		const wait       = 5000
		await alertsShowWaitHide( form, fileAlerts, wait )
	}
}


/**
 * Handle a file upload input.
 */
const fileUpload = ( event ) => {
	const input = event.currentTarget
	updateFileList( input )
}


export { fileUpload, disallowedTypes }
