<?php
/**
 * @version 2.0.0
 * @package Perfect Easy & Powerful Contact Form
 * @copyright © 2014 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
 * @license GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @author Piotr Moćko
 */

// No direct access
function_exists('add_action') or die;
?>

<h3>
    <?php _e('Check below if you can publish your form', 'pwebcontact'); ?>
</h3>


<div id="pweb-cog-check-success" class="pweb-alert pweb-alert-success" style="display:block">
    <i class="glyphicon glyphicon-ok"></i>
    <?php _e('Congratulations! All options you had to choose to get your form working properly are chosen.', 'pwebcontact'); ?>
</div>

<div id="pweb-cog-check-warning" class="pweb-alert pweb-alert-success" style="display:none">
    <i class="glyphicon glyphicon-ok"></i> 
    <?php _e('Congratulations your form is ready! But you have chosen some PRO options so you need to BUY Pro Version in order to enjoy using them', 'pwebcontact'); 
        //TODO check email template for: ip_address, browser, os, screen_resolution, mailto_name, ticket
    ?>
    
    <button class="button button-primary pweb-buy">
        <i class="glyphicon glyphicon-shopping-cart"></i> <?php _e( 'Buy PRO', 'pwebcontact' ); ?>
    </button>
</div>

<div id="pweb-cog-check-error" class="pweb-alert pweb-alert-danger" style="display:none">
    <i class="glyphicon glyphicon-warning-sign"></i>
    <?php _e('There are still some options required to get your form working', 'pwebcontact'); ?>
</div>

<button type="button" class="button button-primary" id="pweb-cog-check-save">
    <i class="glyphicon glyphicon-floppy-disk"></i> <span><?php _e( 'Save' ); ?></span>
</button>

<div id="pweb-cog-check">
    
    <div class="pweb-alert pweb-alert-danger" id="pweb-email-to-warning" style="display:none">
        <i class="glyphicon glyphicon-warning-sign"></i>
        <?php _e('Enter one or more emails to which message should be sent to in `Email` tab.', 'pwebcontact'); ?>
    </div>

    <?php if (($result = $this->_check_mailer()) !== true) : ?>
    <div class="pweb-alert pweb-alert-danger">
        <i class="glyphicon glyphicon-warning-sign"></i> <?php echo $result; ?>
    </div>
    <?php endif; ?>
    
    
    <?php 
    //TODO check if copy to user field is allowed 
    //TODO warn about shortcode and widget position
    //TODO warn about browser detection if used in email template and 3-rd part plugin not installed
    ?>


    <?php if (($result = $this->_check_cache_path()) !== true) : ?>
    <div class="pweb-alert pweb-alert-danger">
        <i class="glyphicon glyphicon-warning-sign"></i> <?php echo $result; ?>
    </div>
    <?php endif; ?>


    <?php if (($result = $this->_check_image_text_creation()) !== true) : ?>
    <div class="pweb-alert pweb-alert-warning">
        <i class="glyphicon glyphicon-warning-sign"></i> <?php echo $result; ?>
    </div>
    <?php endif; ?>

</div>