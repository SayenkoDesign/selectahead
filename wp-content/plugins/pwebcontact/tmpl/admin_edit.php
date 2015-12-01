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
<form name="edit" method="post" action="<?php echo esc_attr(admin_url( 'admin.php?page=pwebcontact&task=save' )); ?>" id="pweb_form">
    
    <div id="pweb-adminbar">
        
        <div class="pweb-toolbar pweb-clearfix">

            <!-- header for displaying update and error messages after -->
            <h2 style="display:none"></h2>
            <?php $this->_display_messages(); ?>

            <h2><?php _e( 'Edit' ); ?></h2>

            <input type="text" name="title" value="<?php echo esc_attr($this->data->title); ?>" placeholder="<?php esc_attr_e( 'Form name', 'pwebcontact' ); ?>">

            <button type="submit" class="button button-primary" id="pweb-save-button">
                <i class="glyphicon glyphicon-floppy-disk"></i> <span><?php _e( 'Save' ); ?></span>
            </button>
            <button type="button" class="button" id="pweb-close-button" onclick="document.location.href='<?php echo admin_url( 'admin.php?page=pwebcontact' ); ?>'">
                <i class="glyphicon glyphicon-remove"></i> <span><?php _e( 'Close' ); ?></span>
            </button>

            <span id="pweb-save-status"><?php _e( 'Saved on', 'pwebcontact' ); ?> <?php echo get_date_from_gmt($this->data->modify_date); ?></span>
            
            <button class="button button-primary right pweb-buy" id="pweb-buy-button">
                <i class="glyphicon glyphicon-shopping-cart"></i> <?php _e( 'Buy PRO', 'pwebcontact' ); ?>
                <span>&amp; <?php _e( 'Get Support', 'pwebcontact' ); ?></span>
            </button>
            <a class="button button-primary right" id="pweb-docs-button" href="<?php echo $this->documentation_url; ?>" target="_blank">
                <i class="glyphicon glyphicon-question-sign"></i> <span><?php _e( 'Documentation' ); ?></span>
            </a>
        </div>

        <h2 class="nav-tab-wrapper" id="pweb-tabs">
            <a href="#pweb-tab-location" id="pweb-tab-location" class="nav-tab nav-tab-active">
                <i class="glyphicon glyphicon-th-large"></i>
                <?php esc_html_e( 'Location & Effects', 'pwebcontact' ); ?>
            </a>
            <a href="#pweb-tab-fields" id="pweb-tab-fields" class="nav-tab">
                <i class="glyphicon glyphicon-th-list"></i>
                <?php esc_html_e( 'Fields', 'pwebcontact' ); ?>
            </a>
            <a href="#pweb-tab-theme" id="pweb-tab-theme" class="nav-tab">
                <i class="glyphicon glyphicon-tint"></i>
                <?php esc_html_e( 'Theme', 'pwebcontact' ); ?>
            </a>
            <a href="#pweb-tab-email" id="pweb-tab-email" class="nav-tab">
                <i class="glyphicon glyphicon-envelope"></i>
                <?php esc_html_e( 'Email', 'pwebcontact' ); ?>
            </a>
            <a href="#pweb-tab-check" id="pweb-tab-check" class="nav-tab">
                <?php esc_html_e( 'Configuration check', 'pwebcontact' ); ?>
            </a>
            <a href="#pweb-tab-tips" id="pweb-tab-tips" class="nav-tab">
                <i class="glyphicon glyphicon-info-sign"></i>
                <?php esc_html_e( 'Tips', 'pwebcontact' ); ?>
            </a>
            <a href="#pweb-tab-advanced" id="pweb-tab-advanced" class="nav-tab">
                <i class="glyphicon glyphicon-cog"></i>
                <?php esc_html_e( 'Advanced', 'pwebcontact' ); ?>
            </a>
        </h2>
    </div>
    
    <div id="pweb-tabs-content">
        
        <div id="pweb-tab-location-content" class="nav-tab-content nav-tab-content-active pweb-clearfix">
            <?php $this->_load_tmpl('location', __FILE__); ?>
        </div>
        
        <div id="pweb-tab-fields-content" class="nav-tab-content pweb-clearfix">
            <?php $this->_load_tmpl('fields', __FILE__); ?>
        </div>
        
        <div id="pweb-tab-theme-content" class="nav-tab-content pweb-clearfix">
            <?php $this->_load_tmpl('theme', __FILE__); ?>
        </div>
        
        <div id="pweb-tab-email-content" class="nav-tab-content pweb-clearfix">
            <?php $this->_load_tmpl('email', __FILE__); ?>
        </div>
        
        <div id="pweb-tab-check-content" class="nav-tab-content pweb-clearfix">
            <?php $this->_load_tmpl('check', __FILE__); ?>
        </div>
        
        <div id="pweb-tab-tips-content" class="nav-tab-content pweb-clearfix">
            <?php $this->_load_tmpl('tips', __FILE__); ?>
        </div>
        
        <div id="pweb-tab-advanced-content" class="nav-tab-content pweb-clearfix">
            <?php $this->_load_tmpl('advanced', __FILE__); ?>
        </div>
    </div>
    

    <input type="hidden" name="id" value="<?php echo (int)$this->id; ?>">
    <?php wp_nonce_field( 'save-form_'.$this->id ); ?>
    
</form>

<?php if ($this->_get_param('feed', 1, 'settings')) : ?>
<script type="text/javascript">
<?php echo $this->_get_feeds_script(); ?>
</script>
<?php endif; ?>