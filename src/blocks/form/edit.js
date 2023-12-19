import { __ } from '@wordpress/i18n'
import { useBlockProps } from '@wordpress/block-editor'

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {WPElement} Element to render.
 */
export default function Edit() {


const noStyles = false, // Bool - remove all styles on true.
	styles     = true,  // Bool - Apply fancy dark theme on true.
	classesReference = [
		'bigup__form-nostyles', // noStyles === true.
		'bigup__form-dark',     // styles === true.
		'bigup__form-vanilla'   // styles === false.
	]

	const blockProps = useBlockProps( {
		className: classesReference
	} )


	const fancyWrap = () => {
		return (
			<div class='bigup__form_inputWrap'>
				<InnerBlocks/>
				<span className='bigup__form_flag bigup__form_flag-hover'></span>
				<span className='bigup__form_flag bigup__form_flag-focus'></span>
			</div>
		)
	}

	const inputName = () => {
		return (
			<input
				className='bigup__form_input'
				name='name'
				type='text'
				maxlength='100'
				title='Name'
				required aria-label='Name'
				placeholder='Name (required)'
				onfocus='this.placeholder=""'
				onblur='this.placeholder="Name (required)"'
			/>
		)
	}

	const inputEmail = () => {
		return (
			<input
				className='bigup__form_input'
				name='email' type='text'
				maxlength='100' title='Email'
				required aria-label='Email'
				placeholder='Email (required)'
				onfocus='this.placeholder=""'
				onblur='this.placeholder="Email (required)"'
			/>
		)
	}

	const inputMessage = () => {
		return (
			<textarea
				className='bigup__form_input'
				name='message'
				maxlength='5000'
				title='Message'
				rows='8'
				aria-label='Message'
				placeholder='Type your message here...'
				onfocus='this.placeholder=""'
				onblur='this.placeholder="Type your message..."'
			></textarea>
		)
	}

	const inputFiles = () => {
		return (
			<div className='bigup__customFileUpload'>
				<label className='bigup__customFileUpload_label'>
					<input
						className='bigup__customFileUpload_input'
						title='Attach a File'
						type='file'
						name='files'
						multiple
					/>
					<span className='bigup__customFileUpload_icon'>
						{'[FILES ICON]'}
					</span>	
					{'Attach file'}
				</label>
				<div className='bigup__customFileUpload_output'></div>
				<template>
					<span className='bigup__customFileUpload_icon'>
						{'[BIN ICON]'}
					</span>	
				</template>
			</div>
		)
	}


	return (
		<form
			{ ...blockProps }
			method='post'
			acceptCharset='utf-8'
			autocomplete='on'
		>

			<header>
				<h3>Contact Form</h3>
			</header>

			<div className='bigup__form_section'>

				<input
					className='bigup__form_input saveTheBees'
					name='required_field'
					type='text'
					autocomplete='off'
				/>


				{ noStyles ?
					<inputText /> :
					<fancyWrap><inputText /></fancyWrap>
				}

				{ noStyles ?
					<inputEmail /> :
					<fancyWrap><inputEmail /></fancyWrap>
				}

				{ noStyles ?
					<inputMessage /> :
					<fancyWrap><inputMessage /></fancyWrap>
				}

				{ noStyles ?
					<inputFiles /> :
					<fancyWrap><inputFiles /></fancyWrap>
				}

				<button className='button bigup__form_submit' type='submit' value='Submit' disabled>
					<span className='bigup__form_submitLabel-ready'>
						{'Submit'}
					</span>
					<span className='bigup__form_submitLabel-notReady'>
						{'[please wait]'}
					</span>
				</button>

			</div>

			<footer>
				<div className='bigup__alert_output' style='display:none; opacity:0;'></div>
			</footer>

		</form>
	)
}
