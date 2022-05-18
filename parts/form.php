<?php

/**
 * Bigup Contact Form - HTML Template.
 *
 * This template defines the front end form HTML.
 * 
 * Note: FormData will only use input fields that use the name attribute.
 *
 * @package bigup_contact_form
 * @author Jefferson Real <me@jeffersonreal.uk>
 * @copyright Copyright (c) 2021, Jefferson Real
 * @license GPL2+
 * @link https://jeffersonreal.uk
 * 
 */

//Variables passed from the calling file using Init::includeWithVariables()
//$title
//$message
//$align (not applicable for widget)
//$files (not applicable for widget)

$dark_styles  = get_option('styles');
$classes      = ( $dark_styles ) ? 'bigup__form-dark' : 'bigup__form-vanilla';
$classes     .= ' ' . $align;

?>

<form class="bigup__form <?php echo esc_attr( $classes ); ?>" method="post" accept-charset="utf-8" autocomplete="on">

    <header class="bigup__form_section">
        <?php
            $title   = ( ! empty( $title ) ) ? '<h3 id="aria_form-title" class="bigup__form_title">' . $title . '</h3>' . "\n" : '';
            $message = ( ! empty( $message ) ) ? '<p id="aria_form-desc" class="bigup__form_message">' . $message . '</p>' . "\n" : '';
            echo $title . $message;
        ?>
    </header>

    <fieldset class="bigup__form_section">

        <input
            class="bigup__form_input saveTheBees"
            name="required_field"
            type="text"
            autocomplete="off"
        >

        <div class="bigup__form_inputWrap bigup__form_inputWrap-short">
            
            <input
                class="bigup__form_input"
                name="name"
                type="text"
                maxlength="100"
                title="Name"
                required aria-label="Name"
                placeholder="Name (required)"
                onfocus="this.placeholder=''"
                onblur="this.placeholder='Name (required)'"
            >

            <span class="bigup__form_flag bigup__form_flag-hover"></span>
            <span class="bigup__form_flag bigup__form_flag-focus"></span>

        </div>

        <div class="bigup__form_inputWrap bigup__form_inputWrap-short">

            <input
                class="bigup__form_input"
                name="email" type="text"
                maxlength="100" title="Email"
                required aria-label="Email"
                placeholder="Email (required)"
                onfocus="this.placeholder=''"
                onblur="this.placeholder='Email (required)'"
            >

            <span class="bigup__form_flag bigup__form_flag-hover"></span>
            <span class="bigup__form_flag bigup__form_flag-focus"></span>

        </div>

        <div class="bigup__form_inputWrap bigup__form_inputWrap-wide">

            <textarea
                class="bigup__form_input"
                name="message"
                maxlength="5000"
                title="Message"
                rows="8"
                aria-label="Message"
                placeholder="Type your message here..."
                onfocus="this.placeholder=''"
                onblur="this.placeholder='Type your message...'"
            ></textarea>

            <span class="bigup__form_flag bigup__form_flag-hover"></span>
            <span class="bigup__form_flag bigup__form_flag-focus"></span>

        </div>

		<?php
		if ( 'true' === $files ) {
			?>

			<div class="bigup__customFileUpload">
				<label class="bigup__customFileUpload_label">
					<input
						class="bigup__customFileUpload_input"
						title="Attach a File"
						type="file"
						name="files"
						multiple
						onChange="form_sender.updateFileList( this )"
					>
					<span class="bigup__customFileUpload_icon">
						<svg xmlns="http://www.w3.org/2000/svg" height="1em"  viewBox="0 0 512 512">
							<path fill="currentColor" preserveAspectRatio="xMidYMid meet" d="M512 144v288c0 26.5-21.5 48-48 48h-416C21.5 480 0 458.5 0 432v-352C0 53.5 21.5 32 48 32h160l64 64h192C490.5 96 512 117.5 512 144z"/>
						</svg>
					</span>	
					<?php _e( 'Attach file', 'bigup_contact_form' ); ?>
				</label>
				<div class="bigup__customFileUpload_fileList"></div>
			</div>

			<?php
		}
		?>

        <button class="button bigup__form_submit" type="submit" value="Submit">
            <span>
                <?php _e( 'Submit', 'bigup_contact_form' ); ?>
            </span>
        </button>

    </fieldset>

    <footer class="bigup__form_section">
        <div class="bigup__form_output" style="display:none; opacity:0;"></div>
    </footer>

</form>