<?php

/**
 * Bigup Contact Form - HTML Template.
 *
 * This template defines the front end form HTML
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
                placeholder="Name (required)"
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

        <div class="bigup__form_inputWrap bigup__form_inputWrap-wide">

			<label
				for="fileToUpload"
			>
				Attach a file
			</label>
			<input
				class="bigup__form_input"
				title="Attach a File"
				type="file"
				name="fileToUpload"
			>

		</div>

        <button class="button bigup__form_submit" type="submit" value="Submit">
            <span>
                Submit
            </span>
        </button>

    </fieldset>

    <footer class="bigup__form_section">
        <div class="bigup__form_output" style="display:none; opacity:0;"></div>
    </footer>

</form>