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

?>

<form class="HB__form ajaxFormHandler" method="post" autocomplete="on">

    <header class="HB__form_section">
        <h3 id="aria_form-title" class="HB__form_title jsSuccessMessage"><?php echo $title ?></h3>
        <p id="aria_form-desc" class="HB__form_message"><?php echo $message ?></p>
    </header>

    <div class="HB__form_section jsHideOnSuccess">

        <input class="HB__form_input jsSaveTheBees" name="required_field" type="text" autocomplete="off">

        <div class="HB__form_inputWrap HB__form_inputWrap-short">
            <?php wp_nonce_field('hb_contact_form_submit', 'hb_contact_form_name_nonce'); ?>
            <input class="HB__form_input" name="hb_contact_form_name_nonce" type="text" placeholder="Name (required)" maxlength="50" required aria-label="Name" autofocus>
            <span class="HB__form_hoverFlag">Tap</span>
            <span class="HB__form_focusFlag">Type</span>
        </div>

        <div class="HB__form_inputWrap HB__form_inputWrap-short">
            <?php wp_nonce_field('hb_contact_form_submit', 'hb_contact_form_email_nonce'); ?>
            <input class="HB__form_input" name="hb_contact_form_email_nonce" type="email" placeholder="Email (required)" maxlength="50" required aria-label="Email">
            <span class="HB__form_hoverFlag">Tap</span>
            <span class="HB__form_focusFlag">Type</span>
        </div>

        <div class="HB__form_inputWrap HB__form_inputWrap-wide">
            <?php wp_nonce_field('hb_contact_form_submit', 'hb_contact_form_message_nonce'); ?>
            <textarea class="HB__form_input" name="hb_contact_form_message_nonce" placeholder="Type your message here..." maxlength="3000" rows="8" aria-label="Message"></textarea>
            <span class="HB__form_hoverFlag">Tap</span>
            <span class="HB__form_focusFlag">Type</span>
        </div>

        <button class="button jsButtonSubmit" type="submit" value="Submit">
            <span>
                Submit
            </span>
        </button>

    </div>

    <footer>
        <div class="HB__form_section HB__form_section-hidden jsErrorMessage" style="display:none;">
            <p>
                Sincere apologies, something went wrong. Please refresh and try again, or
                <a href="mailto:me@jeffersonreal.com?subject=FormErr%20New%20Website%20Message">click here</a>
                to send an email.
            </p>
        </div>
    </footer>

</form>