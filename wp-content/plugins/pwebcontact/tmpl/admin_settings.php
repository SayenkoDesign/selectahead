<?php
/**
 * @version 2.0.14
 * @package Perfect Easy & Powerful Contact Form
 * @copyright © 2015 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
 * @license GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @author Piotr Moćko
 */

// No direct access
function_exists('add_action') or die;

?>
<form name="edit" method="post" action="<?php echo esc_attr(admin_url( 'admin.php?page=pwebcontact&task=save_settings' )); ?>" id="pweb_form">
    
    <div id="pweb-adminbar">
        
        <div class="pweb-toolbar pweb-clearfix">

            <!-- header for displaying update and error messages after -->
            <h2 style="display:none"></h2>
            <?php $this->_display_messages(); ?>

            <h2><?php echo $this->_get_name(); ?></h2>

            <button type="submit" class="button button-primary" id="pweb-save-button">
                <i class="glyphicon glyphicon-floppy-disk"></i> <span><?php _e( 'Save' ); ?></span>
            </button>
            <button type="button" class="button" id="pweb-close-button" onclick="document.location.href='<?php echo admin_url( 'admin.php?page=pwebcontact' ); ?>'">
                <i class="glyphicon glyphicon-remove"></i> <span><?php _e( 'Close' ); ?></span>
            </button>

            <span id="pweb-save-status"></span>
            
            <a class="button button-primary right" id="pweb-docs-button" href="<?php echo $this->documentation_url; ?>" target="_blank">
                <i class="glyphicon glyphicon-question-sign"></i> <span><?php _e( 'Documentation' ); ?></span>
            </a>
        </div>
    </div>
    
    <div id="pweb-settings-content">
        <?php $this->_load_tmpl('email', __FILE__); ?>
		<?php $this->_load_tmpl('advanced', __FILE__); ?>
    </div>
    
    <?php wp_nonce_field( 'save-settings' ); ?>
    
</form>