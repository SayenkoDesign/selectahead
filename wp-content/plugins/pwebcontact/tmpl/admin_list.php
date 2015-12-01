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
<div class="pweb-version pweb-clearfix">
    <?php _e('Version'); ?> 
    <?php echo $this->_get_version(); ?>
</div>

<h2>
    <?php echo $this->_get_name(); ?>
    
    <?php if ($this->can_edit) : ?>
        <a class="add-new-h2" href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=pwebcontact&task=new' ), 'new-form'); ?>">
            <i class="glyphicon glyphicon-plus"></i> <?php echo esc_html_x('Add New', 'link'); ?></a>
    
        <a class="add-new-h2" href="<?php echo admin_url( 'admin.php?page=pwebcontact&task=settings' ); ?>">
            <i class="glyphicon glyphicon-cog"></i> <?php _e( 'Settings' ); ?></a>
    <?php endif; ?>
    
    <a class="add-new-h2" href="<?php echo $this->documentation_url; ?>" target="_blank">
        <i class="glyphicon glyphicon-question-sign"></i> <?php _e( 'Documentation' ); ?></a>
    
    <?php $debug = get_option('pwebcontact_debug', 0); ?>
    <a href="#" class="add-new-h2 pweb-action-toggle-state pweb-has-tooltip pweb-text-<?php echo $debug ? 'success' : 'danger'; ?>" 
       title="Enable this option if contact form is not showing or sending emails. Then reload front-end page and try to send email again. It would display more informations."
       data-action="<?php echo admin_url( 'admin.php?page=pwebcontact&task=debug&ajax=1&_wpnonce='. wp_create_nonce('edit-debug-state').'&state=' ); ?>"
       data-state="<?php echo $debug; ?>">
        <i class="glyphicon glyphicon-<?php echo $debug ? 'ok-sign' : 'remove-sign'; ?>"></i> <?php _e( 'Debug mode' ); ?></a>
</h2>


<?php $this->_display_messages(); ?>

<div class="theme-browser pweb-panels pweb-clearfix">
<div class="themes">

<?php if (isset($this->data->forms)) :
    foreach ($this->data->forms as $form) : ?>
    <div class="theme pweb-panel-box">
        <div class="theme-screenshot pweb-layout-<?php echo $form->layout; ?>" onclick="document.location.href='<?php echo admin_url( 'admin.php?page=pwebcontact&task=edit&id='.(int)$form->id ); ?>'">
		
        </div>
        <h3 class="theme-name">
            <span class="pweb-save-date">
                <?php _e( 'Saved on', 'pwebcontact' ); ?><br><?php echo get_date_from_gmt($form->modify_date); ?>
            </span>
            <span class="pweb-form-title">
                <?php echo $form->title ? esc_html($form->title) : '&nbsp;'; ?>
            </span>
        </h3>
        <div class="theme-name pweb-position">
            <?php if ($form->position == 'shortcode') : ?>
                <input type="text" class="pweb-shortcode pweb-has-tooltip" readonly="readonly"
                       title="<?php esc_attr_e( 'Copy shortcode and paste into blog post or page.', 'pwebcontact' ); ?>" 
                       value="[pwebcontact id=<?php echo (int)$form->id; ?>]">
                <?php _e( 'shortcode', 'pwebcontact' ); ?>
            <?php elseif ($form->position == 'widget') : ?>
                <?php _e( 'Display in', 'pwebcontact' ); ?>
                <a href="<?php echo admin_url('widgets.php'); ?>"><?php _e( 'widget', 'pwebcontact' ); ?></a>
            <?php elseif ($form->position == 'footer') : ?>
                <?php _e( 'Display on all pages', 'pwebcontact' ); ?>
            <?php endif; ?>
        </div>
        <div class="theme-name pweb-actions">
            <button type="button"<?php if (!$this->can_edit) echo ' disabled="disabled"'; ?> class="button button-primary" 
                    onclick="document.location.href='<?php echo admin_url( 'admin.php?page=pwebcontact&task=edit&id='.(int)$form->id ); ?>'">
                <i class="glyphicon glyphicon-edit"></i> <?php _e( 'Edit' ); ?>
            </button>
            <button type="button"<?php if (!$this->can_edit) echo ' disabled="disabled"'; ?> 
                    class="button pweb-action-toggle-state pweb-text-<?php echo $form->publish ? 'success' : 'danger'; ?> pweb-has-tooltip" 
                    title="<?php esc_attr_e( 'Toggle form publish state', 'pwebcontact' ); ?>" 
                    data-action="<?php echo admin_url( 'admin.php?page=pwebcontact&task=edit_state&id='.(int)$form->id.'&ajax=1&_wpnonce='. wp_create_nonce('edit-form-state_'.$form->id).'&state=' ); ?>"
                    data-state="<?php echo $form->publish; ?>">
                <i class="glyphicon glyphicon-<?php echo $form->publish ? 'ok-sign' : 'remove-sign'; ?>"></i> 
            </button>
            <button type="button"<?php if (!$this->can_edit) echo ' disabled="disabled"'; ?> 
                    class="button pweb-has-tooltip" title="<?php esc_attr_e( 'Copy' ); ?>" 
                    onclick="document.location.href='<?php echo admin_url( 'admin.php?page=pwebcontact&task=copy&id='.(int)$form->id.'&_wpnonce='. wp_create_nonce('copy-form_'.$form->id) ); ?>'">
                <i class="glyphicon glyphicon-file"></i> 
            </button>
            <button type="button"<?php if (!$this->can_edit) echo ' disabled="disabled"'; ?> 
                    class="button pweb-action-delete pweb-has-tooltip" title="<?php esc_attr_e( 'Delete' ); ?>" 
                    data-form-title="<?php echo esc_attr($form->title); ?>" 
                    data-action="<?php echo admin_url( 'admin.php?page=pwebcontact&task=delete&id='.(int)$form->id.'&ajax=1&_wpnonce='. wp_create_nonce('delete-form_'.$form->id) ); ?>">
                <i class="glyphicon glyphicon-trash"></i> 
            </button>
        </div>
    </div>
<?php endforeach; 
endif; ?>

    <div class="theme active pweb-panel-box pweb-panel-pro">
        <div class="theme-screenshot pweb-buy">
			<i class="glyphicon glyphicon-shopping-cart"></i>
		</div>
        <h3 class="theme-name">
            <a class="button button-primary right pweb-buy" href="<?php echo $this->buy_url; ?>" target="_blank">
                <i class="glyphicon glyphicon-shopping-cart"></i> <?php _e( 'Buy', 'pwebcontact' ); ?>
            </a>
            <?php _e( 'Get PRO version & support', 'pwebcontact' ); ?>
        </h3>
    </div>
</div>
</div>

<div id="pweb-dialog-delete" title="<?php esc_attr_e( 'Delete', 'pwebcontact' ); ?>" style="display:none">
    <p><?php _e( 'Are you sure you want to delete form:', 'pwebcontact' ); ?> 
    <span class="pweb-dialog-form-title"></span>?</p>
</div>

<?php if ($this->_get_param('feed', 1, 'settings')) : ?>
<script type="text/javascript">
<?php echo $this->_get_feeds_script(); ?>
</script>
<?php endif; ?>