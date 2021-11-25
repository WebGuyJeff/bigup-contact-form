<?php

/**
 * Herringbone Contact Form HTML Template.
 *
 * This template defines the front end form HTML
 *
 * @package Herringbone
 * @subpackage HB_Contact_Form
 * @author Jefferson Real <me@jeffersonreal.com>
 * @copyright Copyright (c) 2021, Jefferson Real
 * @license GPL2+
 */

//Variables passed from the calling file using Init::includeWithVariables()
//$title
//$message

$admin_email = get_bloginfo( 'admin_email' );

?>

<form class="HB__form jsFormSubmit" method="post" accept-charset="utf-8" autocomplete="on">

    <header class="HB__form_section">
        <?php
            $title   = ( isset( $title ) ) ? '<h3 id="aria_form-title" class="HB__form_title">' . $title . '</h3>' . "\n" : '';
            $message = ( isset( $message ) ) ? '<p id="aria_form-desc" class="HB__form_message">' . $message . '</p>' . "\n" : '';
            echo $title . $message;
        ?>
    </header>

    <fieldset class="HB__form_section">

        <input class="HB__form_input jsSaveTheBees" name="required_field" type="text" autocomplete="off">

        <div class="HB__form_inputWrap HB__form_inputWrap-short">
            
            <input
                class="HB__form_input"
                name="name"
                type="text"
                placeholder="Name (required)"
                maxlength="100"
                title="Name"
                required aria-label="Name"
            >

            <span class="HB__form_hoverFlag">Tap</span>
            <span class="HB__form_focusFlag">Type</span>
        </div>

        <div class="HB__form_inputWrap HB__form_inputWrap-short">

            <input
                class="HB__form_input"
                name="email" type="text"
                placeholder="Email (required)"
                maxlength="100" title="Email"
                required aria-label="Email"
            >

            <span class="HB__form_hoverFlag">Tap</span>
            <span class="HB__form_focusFlag">Type</span>
        </div>

        <div class="HB__form_inputWrap HB__form_inputWrap-wide">

            <textarea
                class="HB__form_input"
                name="message"
                placeholder="Type your message here..."
                maxlength="5000"
                title="Message"
                rows="8"
                aria-label="Message"
            ></textarea>

            <span class="HB__form_hoverFlag">Tap</span>
            <span class="HB__form_focusFlag">Type</span>
        </div>

        <button class="button jsButtonSubmit" type="submit" value="Submit">
            <span>
                Submit
            </span>
        </button>

    </fieldset>

    <footer class="HB__form_section">
        <div class="HB__form_output" style="display:none; opacity:0;"></div>
    </footer>

</form>