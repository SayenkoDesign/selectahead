<?php
/**
 * @version 2.1.2
 * @package Perfect Easy & Powerful Contact Form
 * @copyright © 2015 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
 * @license GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @author Piotr Moćko
 */

// TODO map field
// TODO mail to list - allow multiple emails for one option
// TODO subject - input or select
// TODO URL field
// TODO country select field
// TODO custom text - WYSIWYG
// 
// TODO remove fields button

// No direct access
function_exists('add_action') or die;

$this->_set_pro_options('field_types', array(
    'phone',
    'subject',
    'date',
    'password',
    'select',
    'multiple',
    'radio',
    'checkboxes',
    'checkbox',
    'checkbox_modal',
    'email_copy',
    'custom_text',
    'header',
    'captcha',
    'upload',
    'mailto_list',
    'newsletter'
));
?>

<h3 class="pweb-steps">
    <?php printf(__('Step %d of %d', 'pwebcontact'), 2, 4); ?>
    -
    <?php _e('Choose predefined form composition', 'pwebcontact'); ?>

    <?php echo $this->_get_field_control(array(
        'type' => 'filelist',
        'group' => 'load',
        'name' => 'fields',
        'filter' => '\.txt$',
        'directory' => 'media/fields_settings',
        'strip_ext' => true,
        'attributes' => array(
            'data-action' => admin_url( 'admin.php?page=pwebcontact&task=load_fields&ajax=1&_wpnonce='. wp_create_nonce('load-fields') )
        ),
        'options' => array(
            array(
                'name' => '- Select -',
                'value' => ''
            )
        )
    )); ?>
    
    <?php _e('or drag and drop fields', 'pwebcontact'); ?>
        
    <button class="button button-primary pweb-next-tab-button" type="button">
        <?php _e( 'Next', 'pwebcontact' ); ?> <i class="glyphicon glyphicon-chevron-right"></i>
    </button>
</h3>

<div id="pweb_fields_pro_warning" class="pweb-alert pweb-alert-info" style="display:none">
    <?php _e('You need to go PRO :)', 'pwebcontact'); ?>
    <?php _e('You have chosen some PRO fields. You can still save your form, but to display it buy PRO Version', 'pwebcontact'); ?>
    <button class="button button-primary pweb-buy">
        <i class="glyphicon glyphicon-shopping-cart"></i> <?php _e( 'Buy', 'pwebcontact' ); ?>
    </button>
</div>

<div id="pweb_fields" class="pweb-clearfix">
	
	<?php echo $this->_get_field_control(array(
        'type' => 'hidden',
        'name' => 'fields',
        'value' => json_encode( $this->_get_param(null, array(), 'fields') )
    )); ?>
    
    <div class="pweb-fields-container">
        
        <div class="pweb-fields-add-row pweb-clearfix" id="pweb_fields_add_row_before">
            <i class="glyphicon glyphicon-plus"></i> <?php _e('Add row', 'pwebcontact'); ?>
        </div>
        <div class="pweb-fields-rows pweb-clearfix" id="pweb_fields_rows">
            
        </div>
        <div class="pweb-fields-add-row pweb-clearfix" id="pweb_fields_add_row_after">
            <i class="glyphicon glyphicon-plus"></i> <?php _e('Add row', 'pwebcontact'); ?>
        </div>
    </div>
    
    
    
    <div class="pweb-fields-types" id="pweb_fields_types">
        
        <?php $field_type = 'text'; ?>
        <div class="pweb-custom-fields-type pweb-custom-field-type-<?php echo $field_type; ?>" id="pweb_field_type_<?php echo $field_type; ?>">
            <?php _e('Text input', 'pwebcontact'); ?>
            <?php echo $this->_display_badge($field_type); ?>
            
            <div data-type="<?php echo $field_type; ?>" class="pweb-custom-field-container<?php echo $this->_is_pro_field($field_type) ? ' pweb-pro' : ''; ?>">
                <a href="#" class="pweb-custom-field-show-options pweb-has-tooltip" title="<?php _e('Edit'); ?>"><i class="glyphicon glyphicon-edit"></i></a>
                <div class="pweb-custom-field-type">
                    <span><?php _e('Text input field', 'pwebcontact'); ?></span>
                    <?php echo $this->_display_badge($field_type); ?>
                </div>
                <div class="pweb-custom-field-label">
                    <?php _e('Label', 'pwebcontact'); ?> <span></span>
                </div>
                
                <div class="pweb-custom-field-options">
                    <h3><?php _e('Text input field options', 'pwebcontact'); ?></h3>
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'hidden',
                        'name' => 'type',
                        'index' => 'X',
                        'group' => 'fields',
                        'value' => $field_type
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'text',
                        'name' => 'label',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Label',
                        'class' => 'pweb-custom-field-label-input'
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'text',
                        'name' => 'tooltip',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Tooltip',
                        'class' => 'pweb-input-large'
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'radio',
                        'name' => 'required',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Required',
                        'class' => 'pweb-radio-group',
                        'default' => 0,
                        'options' => array(
                            array('value' => 0, 'name' => 'No'),
                            array('value' => 1, 'name' => 'Yes')
                        )
                    )); ?>
                    
                    <div class="pweb-advanced-options">
                        <button type="button" class="button pweb-advanced-options-toggler">
                            <i class="glyphicon glyphicon-cog"></i> <span><?php _e( 'Advanced', 'pwebcontact' ); ?></span> <i class="glyphicon glyphicon-chevron-down"></i>
                        </button>
                        <div class="pweb-advanced-options-content">
                            
                            <?php echo $this->_get_field(array(
                                'disabled' => true,
                                'type' => 'text',
                                'name' => 'alias',
                                'index' => 'X',
                                'group' => 'fields',
                                'class' => 'pweb-custom-field-alias',
                                'label' => 'Alias for email template'
                            )); ?>
                            
                            <?php echo $this->_get_field(array(
                                'disabled' => true,
                                'type' => 'text',
                                'name' => 'value',
                                'index' => 'X',
                                'group' => 'fields',
                                'label' => 'Default value'
                            )); ?>
                            
                            <?php echo $this->_get_field(array(
                                'disabled' => true,
                                'type' => 'text',
                                'name' => 'validation',
                                'index' => 'X',
                                'group' => 'fields',
                                'label' => 'Validation regular expression',
                                'tooltip' => 'JavaScript regular expression for validation of field value',
                                'class' => 'pweb-input-large'
                            )); ?>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        
        <?php $field_type = 'textarea'; ?>
        <div class="pweb-custom-fields-type pweb-custom-field-type-<?php echo $field_type; ?>" id="pweb_field_type_<?php echo $field_type; ?>">
            <?php _e('Multi-line textarea input', 'pwebcontact'); ?>
            <?php echo $this->_display_badge($field_type); ?>
            
            <div data-type="<?php echo $field_type; ?>" class="pweb-custom-field-container<?php echo $this->_is_pro_field($field_type) ? ' pweb-pro' : ''; ?>">
                <a href="#" class="pweb-custom-field-show-options pweb-has-tooltip" title="<?php _e('Edit'); ?>"><i class="glyphicon glyphicon-edit"></i></a>
                <div class="pweb-custom-field-type">
                    <span><?php _e('Multi-line textarea input field', 'pwebcontact'); ?></span>
                    <?php echo $this->_display_badge($field_type); ?>
                </div>
                <div class="pweb-custom-field-label">
                    <?php _e('Label', 'pwebcontact'); ?> <span></span>
                </div>
                
                <div class="pweb-custom-field-options">
                    <h3><?php _e('Multi-line textarea input field options', 'pwebcontact'); ?></h3>
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'hidden',
                        'name' => 'type',
                        'index' => 'X',
                        'group' => 'fields',
                        'value' => $field_type
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'text',
                        'name' => 'label',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Label',
                        'class' => 'pweb-custom-field-label-input'
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'text',
                        'name' => 'tooltip',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Tooltip',
                        'class' => 'pweb-input-large'
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'radio',
                        'name' => 'required',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Required',
                        'class' => 'pweb-radio-group',
                        'default' => 0,
                        'options' => array(
                            array('value' => 0, 'name' => 'No'),
                            array('value' => 1, 'name' => 'Yes')
                        )
                    )); ?>
                    
                    <div class="pweb-advanced-options">
                        <button type="button" class="button pweb-advanced-options-toggler">
                            <i class="glyphicon glyphicon-cog"></i> <span><?php _e( 'Advanced', 'pwebcontact' ); ?></span> <i class="glyphicon glyphicon-chevron-down"></i>
                        </button>
                        <div class="pweb-advanced-options-content">
                            
                            <?php echo $this->_get_field(array(
                                'disabled' => true,
                                'type' => 'text',
                                'name' => 'alias',
                                'index' => 'X',
                                'group' => 'fields',
                                'class' => 'pweb-custom-field-alias',
                                'label' => 'Alias for email template'
                            )); ?>
                            
                            <?php echo $this->_get_field(array(
                                'disabled' => true,
                                'type' => 'text',
                                'name' => 'value',
                                'index' => 'X',
                                'group' => 'fields',
                                'label' => 'Default value'
                            )); ?>
                            
                            <?php echo $this->_get_field(array(
                                'disabled' => true,
                                'type' => 'text',
                                'name' => 'rows',
                                'index' => 'X',
                                'group' => 'fields',
                                'label' => 'Rows number',
                                'tooltip' => 'Max number of vissible rows',
                                'default' => 5,
                                'class' => 'pweb-input-mini'
                            )); ?>
                            
                            <?php echo $this->_get_field(array(
                                'disabled' => true,
                                'type' => 'text',
                                'name' => 'maxlength',
                                'index' => 'X',
                                'group' => 'fields',
                                'label' => 'Characters limit',
                                'tooltip' => 'Set 0 for no limit',
                                'class' => 'pweb-input-mini'
                            )); ?>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        
        <?php $field_type = 'email'; ?>
        <div class="pweb-custom-fields-type pweb-custom-field-type-<?php echo $field_type; ?> pweb-custom-fields-single" id="pweb_field_type_<?php echo $field_type; ?>">
            <?php _e('Email', 'pwebcontact'); ?>
            <?php echo $this->_display_badge($field_type); ?>
            
            <div data-type="<?php echo $field_type; ?>" class="pweb-custom-field-container pweb-custom-fields-single<?php echo $this->_is_pro_field($field_type) ? ' pweb-pro' : ''; ?>">
                <a href="#" class="pweb-custom-field-show-options pweb-has-tooltip" title="<?php _e('Edit'); ?>"><i class="glyphicon glyphicon-edit"></i></a>
                <div class="pweb-custom-field-type">
                    <span><?php _e('Email field', 'pwebcontact'); ?></span>
                    <?php echo $this->_display_badge($field_type); ?>
                </div>
                <div class="pweb-custom-field-label">
                    <?php _e('Label', 'pwebcontact'); ?> <span><?php _e('Email', 'pwebcontact'); ?></span>
                </div>
                
                <div class="pweb-custom-field-options">
                    <h3><?php _e('Email field options', 'pwebcontact'); ?></h3>
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'hidden',
                        'name' => 'type',
                        'index' => 'X',
                        'group' => 'fields',
                        'value' => $field_type
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'text',
                        'name' => 'label',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Label',
                        'default' => 'Email',
                        'class' => 'pweb-custom-field-label-input'
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'text',
                        'name' => 'tooltip',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Tooltip',
                        'class' => 'pweb-input-large'
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'radio',
                        'name' => 'required',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Required',
                        'class' => 'pweb-radio-group',
                        'default' => 1,
                        'options' => array(
                            array('value' => 0, 'name' => 'No'),
                            array('value' => 1, 'name' => 'Yes')
                        )
                    )); ?>
                    
                    <div class="pweb-advanced-options">
                        <button type="button" class="button pweb-advanced-options-toggler">
                            <i class="glyphicon glyphicon-cog"></i> <span><?php _e( 'Advanced', 'pwebcontact' ); ?></span> <i class="glyphicon glyphicon-chevron-down"></i>
                        </button>
                        <div class="pweb-advanced-options-content">
                            
                            <?php echo $this->_get_field(array(
                                'disabled' => true,
                                'type' => 'text',
                                'name' => 'alias',
                                'index' => 'X',
                                'group' => 'fields',
                                'default' => 'email',
                                'class' => 'pweb-custom-field-alias',
                                'label' => 'Alias for email template'
                            )); ?>
                            
                            <?php echo $this->_get_field(array(
                                'disabled' => true,
                                'type' => 'text',
                                'name' => 'value',
                                'index' => 'X',
                                'group' => 'fields',
                                'label' => 'Default value'
                            )); ?>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        
        <?php $field_type = 'name'; ?>
        <div class="pweb-custom-fields-type pweb-custom-field-type-<?php echo $field_type; ?> pweb-custom-fields-single" id="pweb_field_type_<?php echo $field_type; ?>">
            <?php _e('Name', 'pwebcontact'); ?>
            <?php echo $this->_display_badge($field_type); ?>
            
            <div data-type="<?php echo $field_type; ?>" class="pweb-custom-field-container pweb-custom-fields-single<?php echo $this->_is_pro_field($field_type) ? ' pweb-pro' : ''; ?>">
                <a href="#" class="pweb-custom-field-show-options pweb-has-tooltip" title="<?php _e('Edit'); ?>"><i class="glyphicon glyphicon-edit"></i></a>
                <div class="pweb-custom-field-type">
                    <span><?php _e('Name field', 'pwebcontact'); ?></span>
                    <?php echo $this->_display_badge($field_type); ?>
                </div>
                <div class="pweb-custom-field-label">
                    <?php _e('Label', 'pwebcontact'); ?> <span><?php _e('Name', 'pwebcontact'); ?></span>
                </div>
                
                <div class="pweb-custom-field-options">
                    <h3><?php _e('Name field options', 'pwebcontact'); ?></h3>
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'hidden',
                        'name' => 'type',
                        'index' => 'X',
                        'group' => 'fields',
                        'value' => $field_type
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'text',
                        'name' => 'label',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Label',
                        'default' => 'Name',
                        'class' => 'pweb-custom-field-label-input'
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'text',
                        'name' => 'tooltip',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Tooltip',
                        'class' => 'pweb-input-large'
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'radio',
                        'name' => 'required',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Required',
                        'class' => 'pweb-radio-group',
                        'default' => 0,
                        'options' => array(
                            array('value' => 0, 'name' => 'No'),
                            array('value' => 1, 'name' => 'Yes')
                        )
                    )); ?>
                    
                    <div class="pweb-advanced-options">
                        <button type="button" class="button pweb-advanced-options-toggler">
                            <i class="glyphicon glyphicon-cog"></i> <span><?php _e( 'Advanced', 'pwebcontact' ); ?></span> <i class="glyphicon glyphicon-chevron-down"></i>
                        </button>
                        <div class="pweb-advanced-options-content">
                            
                            <?php echo $this->_get_field(array(
                                'disabled' => true,
                                'type' => 'text',
                                'name' => 'alias',
                                'index' => 'X',
                                'group' => 'fields',
                                'default' => 'name',
                                'class' => 'pweb-custom-field-alias',
                                'label' => 'Alias for email template'
                            )); ?>
                            
                            <?php echo $this->_get_field(array(
                                'disabled' => true,
                                'type' => 'text',
                                'name' => 'value',
                                'index' => 'X',
                                'group' => 'fields',
                                'label' => 'Default value'
                            )); ?>
                            
                            <?php echo $this->_get_field(array(
                                'disabled' => true,
                                'type' => 'text',
                                'name' => 'validation',
                                'index' => 'X',
                                'group' => 'fields',
                                'label' => 'Validation regular expression',
                                'tooltip' => 'JavaScript regular expression for validation of field value',
                                'class' => 'pweb-input-large'
                            )); ?>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        
        <?php $field_type = 'button_send'; ?>
        <div class="pweb-custom-fields-type pweb-custom-field-type-<?php echo $field_type; ?> pweb-custom-fields-single" id="pweb_field_type_<?php echo $field_type; ?>">
            <?php _e('Send button', 'pwebcontact'); ?>
            
            <div data-type="<?php echo $field_type; ?>" class="pweb-custom-field-container pweb-custom-fields-single">
                <a href="#" class="pweb-custom-field-show-options pweb-has-tooltip" title="<?php _e('Edit'); ?>"><i class="glyphicon glyphicon-edit"></i></a>
                <div class="pweb-custom-field-type">
                    <span><?php _e('Send button', 'pwebcontact'); ?></span>
                </div>
                <div class="pweb-custom-field-label">
                    <?php _e('Label', 'pwebcontact'); ?> <span><?php _e('Send', 'pwebcontact'); ?></span>
                </div>
                
                <div class="pweb-custom-field-options">
                    <h3><?php _e('Send button options', 'pwebcontact'); ?></h3>
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'hidden',
                        'name' => 'type',
                        'index' => 'X',
                        'group' => 'fields',
                        'value' => $field_type
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'text',
                        'name' => 'label',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Label',
                        'class' => 'pweb-custom-field-label-input',
                        'default' => __('Send', 'pwebcontact')
                    )); ?>
                </div>
            </div>
        </div>
        
        
        
        <div class="pweb-custom-fields-separator"><hr></div>
        
        
        
        <?php $field_type = 'phone'; ?>
        <div class="pweb-custom-fields-type pweb-custom-field-type-<?php echo $field_type; ?>" id="pweb_field_type_<?php echo $field_type; ?>">
            <?php _e('Phone', 'pwebcontact'); ?>
            <?php echo $this->_display_badge($field_type); ?>
            
            <div data-type="<?php echo $field_type; ?>" class="pweb-custom-field-container<?php echo $this->_is_pro_field($field_type) ? ' pweb-pro' : ''; ?>">
                <a href="#" class="pweb-custom-field-show-options pweb-has-tooltip" title="<?php _e('Edit'); ?>"><i class="glyphicon glyphicon-edit"></i></a>
                <div class="pweb-custom-field-type">
                    <span><?php _e('Phone field', 'pwebcontact'); ?></span>
                    <?php echo $this->_display_badge($field_type); ?>
                </div>
                <div class="pweb-custom-field-label">
                    <?php _e('Label', 'pwebcontact'); ?> <span><?php _e('Phone', 'pwebcontact'); ?></span>
                </div>
                
                <div class="pweb-custom-field-options">
                    <h3><?php _e('Phone field options', 'pwebcontact'); ?></h3>
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'hidden',
                        'name' => 'type',
                        'index' => 'X',
                        'group' => 'fields',
                        'value' => $field_type
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'text',
                        'name' => 'label',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Label',
                        'default' => 'Phone',
                        'class' => 'pweb-custom-field-label-input'
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'text',
                        'name' => 'tooltip',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Tooltip',
                        'class' => 'pweb-input-large'
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'radio',
                        'name' => 'required',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Required',
                        'class' => 'pweb-radio-group',
                        'default' => 0,
                        'options' => array(
                            array('value' => 0, 'name' => 'No'),
                            array('value' => 1, 'name' => 'Yes')
                        )
                    )); ?>
                    
                    <div class="pweb-advanced-options">
                        <button type="button" class="button pweb-advanced-options-toggler">
                            <i class="glyphicon glyphicon-cog"></i> <span><?php _e( 'Advanced', 'pwebcontact' ); ?></span> <i class="glyphicon glyphicon-chevron-down"></i>
                        </button>
                        <div class="pweb-advanced-options-content">
                            
                            <?php echo $this->_get_field(array(
                                'disabled' => true,
                                'type' => 'text',
                                'name' => 'alias',
                                'index' => 'X',
                                'group' => 'fields',
                                'class' => 'pweb-custom-field-alias',
                                'label' => 'Alias for email template'
                            )); ?>
                            
                            <?php echo $this->_get_field(array(
                                'disabled' => true,
                                'type' => 'text',
                                'name' => 'value',
                                'index' => 'X',
                                'group' => 'fields',
                                'label' => 'Default value'
                            )); ?>
                            
                            <?php echo $this->_get_field(array(
                                'disabled' => true,
                                'type' => 'text',
                                'name' => 'validation',
                                'index' => 'X',
                                'group' => 'fields',
                                'label' => 'Validation regular expression',
                                'tooltip' => 'JavaScript regular expression for validation of field value',
                                'default' => '/[\d\-\+() ]+/',
                                'class' => 'pweb-input-large'
                            )); ?>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        
        <?php $field_type = 'subject'; ?>
        <div class="pweb-custom-fields-type pweb-custom-field-type-<?php echo $field_type; ?> pweb-custom-fields-single" id="pweb_field_type_<?php echo $field_type; ?>">
            <?php _e('Subject', 'pwebcontact'); ?>
            <?php echo $this->_display_badge($field_type); ?>
            
            <div data-type="<?php echo $field_type; ?>" class="pweb-custom-field-container pweb-custom-fields-single<?php echo $this->_is_pro_field($field_type) ? ' pweb-pro' : ''; ?>">
                <a href="#" class="pweb-custom-field-show-options pweb-has-tooltip" title="<?php _e('Edit'); ?>"><i class="glyphicon glyphicon-edit"></i></a>
                <div class="pweb-custom-field-type">
                    <span><?php _e('Subject field', 'pwebcontact'); ?></span>
                    <?php echo $this->_display_badge($field_type); ?>
                </div>
                <div class="pweb-custom-field-label">
                    <?php _e('Label', 'pwebcontact'); ?> <span><?php _e('Subject', 'pwebcontact'); ?></span>
                </div>
                
                <div class="pweb-custom-field-options">
                    <h3><?php _e('Subject field options', 'pwebcontact'); ?></h3>
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'hidden',
                        'name' => 'type',
                        'index' => 'X',
                        'group' => 'fields',
                        'value' => $field_type
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'text',
                        'name' => 'label',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Label',
                        'class' => 'pweb-custom-field-label-input'
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'text',
                        'name' => 'tooltip',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Tooltip',
                        'class' => 'pweb-input-large'
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'radio',
                        'name' => 'required',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Required',
                        'class' => 'pweb-radio-group',
                        'default' => 0,
                        'options' => array(
                            array('value' => 0, 'name' => 'No'),
                            array('value' => 1, 'name' => 'Yes')
                        )
                    )); ?>
                    
                    <div class="pweb-advanced-options">
                        <button type="button" class="button pweb-advanced-options-toggler">
                            <i class="glyphicon glyphicon-cog"></i> <span><?php _e( 'Advanced', 'pwebcontact' ); ?></span> <i class="glyphicon glyphicon-chevron-down"></i>
                        </button>
                        <div class="pweb-advanced-options-content">
                            
                            <?php echo $this->_get_field(array(
                                'disabled' => true,
                                'type' => 'text',
                                'name' => 'alias',
                                'index' => 'X',
                                'group' => 'fields',
                                'default' => 'subject',
                                'class' => 'pweb-custom-field-alias',
                                'label' => 'Alias for email template'
                            )); ?>
                            
                            <?php echo $this->_get_field(array(
                                'disabled' => true,
                                'type' => 'text',
                                'name' => 'value',
                                'index' => 'X',
                                'group' => 'fields',
                                'label' => 'Default value'
                            )); ?>
                            
                            <?php echo $this->_get_field(array(
                                'disabled' => true,
                                'type' => 'text',
                                'name' => 'validation',
                                'index' => 'X',
                                'group' => 'fields',
                                'label' => 'Validation regular expression',
                                'tooltip' => 'JavaScript regular expression for validation of field value',
                                'class' => 'pweb-input-large'
                            )); ?>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        
        <?php $field_type = 'date'; ?>
        <div class="pweb-custom-fields-type pweb-custom-field-type-<?php echo $field_type; ?>" id="pweb_field_type_<?php echo $field_type; ?>">
            <?php _e('Calendar', 'pwebcontact'); ?>
            <?php echo $this->_display_badge($field_type); ?>
            
            <div data-type="<?php echo $field_type; ?>" class="pweb-custom-field-container<?php echo $this->_is_pro_field($field_type) ? ' pweb-pro' : ''; ?>">
                <a href="#" class="pweb-custom-field-show-options pweb-has-tooltip" title="<?php _e('Edit'); ?>"><i class="glyphicon glyphicon-edit"></i></a>
                <div class="pweb-custom-field-type">
                    <span><?php _e('Calendar field', 'pwebcontact'); ?></span>
                    <?php echo $this->_display_badge($field_type); ?>
                </div>
                <div class="pweb-custom-field-label">
                    <?php _e('Label', 'pwebcontact'); ?> <span></span>
                </div>
                
                <div class="pweb-custom-field-options">
                    <h3><?php _e('Calendar field options', 'pwebcontact'); ?></h3>
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'hidden',
                        'name' => 'type',
                        'index' => 'X',
                        'group' => 'fields',
                        'value' => $field_type
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'text',
                        'name' => 'label',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Label',
                        'class' => 'pweb-custom-field-label-input'
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'text',
                        'name' => 'tooltip',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Tooltip',
                        'class' => 'pweb-input-large'
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'radio',
                        'name' => 'required',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Required',
                        'class' => 'pweb-radio-group',
                        'default' => 0,
                        'options' => array(
                            array('value' => 0, 'name' => 'No'),
                            array('value' => 1, 'name' => 'Yes')
                        )
                    )); ?>
                    
                    <div class="pweb-advanced-options">
                        <button type="button" class="button pweb-advanced-options-toggler">
                            <i class="glyphicon glyphicon-cog"></i> <span><?php _e( 'Advanced', 'pwebcontact' ); ?></span> <i class="glyphicon glyphicon-chevron-down"></i>
                        </button>
                        <div class="pweb-advanced-options-content">
                            
                            <?php echo $this->_get_field(array(
                                'disabled' => true,
                                'type' => 'text',
                                'name' => 'alias',
                                'index' => 'X',
                                'group' => 'fields',
                                'class' => 'pweb-custom-field-alias',
                                'label' => 'Alias for email template'
                            )); ?>
                            
                            <?php echo $this->_get_field(array(
                                'disabled' => true,
                                'type' => 'text',
                                'name' => 'value',
                                'index' => 'X',
                                'group' => 'fields',
                                'label' => 'Default value'
                            )); ?>
                            
                            <?php echo $this->_get_field(array(
                                'disabled' => true,
                                'type' => 'text',
                                'name' => 'format',
                                'index' => 'X',
                                'group' => 'fields',
                                'label' => 'Date format',
                                'default' => 'dd-mm-yy',
                                'desc' => '<a href="http://api.jqueryui.com/datepicker/#utility-formatDate" target="_blank">'.__('Click to see a full list of the possible formats of date', 'pwebcontact').'</a>'
                            )); ?>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        
        <?php $field_type = 'password'; ?>
        <div class="pweb-custom-fields-type pweb-custom-field-type-<?php echo $field_type; ?>" id="pweb_field_type_<?php echo $field_type; ?>">
            <?php _e('Password', 'pwebcontact'); ?>
            <?php echo $this->_display_badge($field_type); ?>
            
            <div data-type="<?php echo $field_type; ?>" class="pweb-custom-field-container<?php echo $this->_is_pro_field($field_type) ? ' pweb-pro' : ''; ?>">
                <a href="#" class="pweb-custom-field-show-options pweb-has-tooltip" title="<?php _e('Edit'); ?>"><i class="glyphicon glyphicon-edit"></i></a>
                <div class="pweb-custom-field-type">
                    <span><?php _e('Password field', 'pwebcontact'); ?></span>
                    <?php echo $this->_display_badge($field_type); ?>
                </div>
                <div class="pweb-custom-field-label">
                    <?php _e('Label', 'pwebcontact'); ?> <span></span>
                </div>
                
                <div class="pweb-custom-field-options">
                    <h3><?php _e('Password field options', 'pwebcontact'); ?></h3>
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'hidden',
                        'name' => 'type',
                        'index' => 'X',
                        'group' => 'fields',
                        'value' => $field_type
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'text',
                        'name' => 'label',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Label',
                        'class' => 'pweb-custom-field-label-input'
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'text',
                        'name' => 'tooltip',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Tooltip',
                        'class' => 'pweb-input-large'
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'radio',
                        'name' => 'required',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Required',
                        'class' => 'pweb-radio-group',
                        'default' => 0,
                        'options' => array(
                            array('value' => 0, 'name' => 'No'),
                            array('value' => 1, 'name' => 'Yes')
                        )
                    )); ?>
                    
                    <div class="pweb-advanced-options">
                        <button type="button" class="button pweb-advanced-options-toggler">
                            <i class="glyphicon glyphicon-cog"></i> <span><?php _e( 'Advanced', 'pwebcontact' ); ?></span> <i class="glyphicon glyphicon-chevron-down"></i>
                        </button>
                        <div class="pweb-advanced-options-content">
                            
                            <?php echo $this->_get_field(array(
                                'disabled' => true,
                                'type' => 'text',
                                'name' => 'alias',
                                'index' => 'X',
                                'group' => 'fields',
                                'class' => 'pweb-custom-field-alias',
                                'label' => 'Alias for email template'
                            )); ?>
                            
                            <?php echo $this->_get_field(array(
                                'disabled' => true,
                                'type' => 'text',
                                'name' => 'value',
                                'index' => 'X',
                                'group' => 'fields',
                                'label' => 'Default value'
                            )); ?>
                            
                            <?php echo $this->_get_field(array(
                                'disabled' => true,
                                'type' => 'text',
                                'name' => 'validation',
                                'index' => 'X',
                                'group' => 'fields',
                                'label' => 'Validation regular expression',
                                'tooltip' => 'JavaScript regular expression for validation of field value',
                                'class' => 'pweb-input-large'
                            )); ?>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        
        <?php $field_type = 'select'; ?>
        <div class="pweb-custom-fields-type pweb-custom-field-type-<?php echo $field_type; ?>" id="pweb_field_type_<?php echo $field_type; ?>">
            <?php _e('Select list', 'pwebcontact'); ?>
            <?php echo $this->_display_badge($field_type); ?>
            
            <div data-type="<?php echo $field_type; ?>" class="pweb-custom-field-container<?php echo $this->_is_pro_field($field_type) ? ' pweb-pro' : ''; ?>">
                <a href="#" class="pweb-custom-field-show-options pweb-has-tooltip" title="<?php _e('Edit'); ?>"><i class="glyphicon glyphicon-edit"></i></a>
                <div class="pweb-custom-field-type">
                    <span><?php _e('Select list field', 'pwebcontact'); ?></span>
                    <?php echo $this->_display_badge($field_type); ?>
                </div>
                <div class="pweb-custom-field-label">
                    <?php _e('Label', 'pwebcontact'); ?> <span></span>
                </div>
                
                <div class="pweb-custom-field-options">
                    <h3><?php _e('Select list field options', 'pwebcontact'); ?></h3>
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'hidden',
                        'name' => 'type',
                        'index' => 'X',
                        'group' => 'fields',
                        'value' => $field_type
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'text',
                        'name' => 'label',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Label',
                        'class' => 'pweb-custom-field-label-input'
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'textarea',
                        'name' => 'values',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Options',
                        'tooltip' => 'Enter each option in new line',
                        'class' => 'pweb-input-large'
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'text',
                        'name' => 'tooltip',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Tooltip',
                        'class' => 'pweb-input-large'
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'radio',
                        'name' => 'required',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Required',
                        'class' => 'pweb-radio-group',
                        'default' => 0,
                        'options' => array(
                            array('value' => 0, 'name' => 'No'),
                            array('value' => 1, 'name' => 'Yes')
                        )
                    )); ?>
                    
                    <div class="pweb-advanced-options">
                        <button type="button" class="button pweb-advanced-options-toggler">
                            <i class="glyphicon glyphicon-cog"></i> <span><?php _e( 'Advanced', 'pwebcontact' ); ?></span> <i class="glyphicon glyphicon-chevron-down"></i>
                        </button>
                        <div class="pweb-advanced-options-content">
                            
                            <?php echo $this->_get_field(array(
                                'disabled' => true,
                                'type' => 'text',
                                'name' => 'alias',
                                'index' => 'X',
                                'group' => 'fields',
                                'class' => 'pweb-custom-field-alias',
                                'label' => 'Alias for email template'
                            )); ?>
                            
                            <?php echo $this->_get_field(array(
                                'disabled' => true,
                                'type' => 'text',
                                'name' => 'default',
                                'index' => 'X',
                                'group' => 'fields',
                                'label' => 'Default option'
                            )); ?>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        
        <?php $field_type = 'multiple'; ?>
        <div class="pweb-custom-fields-type pweb-custom-field-type-<?php echo $field_type; ?>" id="pweb_field_type_<?php echo $field_type; ?>">
            <?php _e('Multiple select list', 'pwebcontact'); ?>
            <?php echo $this->_display_badge($field_type); ?>
            
            <div data-type="<?php echo $field_type; ?>" class="pweb-custom-field-container<?php echo $this->_is_pro_field($field_type) ? ' pweb-pro' : ''; ?>">
                <a href="#" class="pweb-custom-field-show-options pweb-has-tooltip" title="<?php _e('Edit'); ?>"><i class="glyphicon glyphicon-edit"></i></a>
                <div class="pweb-custom-field-type">
                    <span><?php _e('Multiple select list field', 'pwebcontact'); ?></span>
                    <?php echo $this->_display_badge($field_type); ?>
                </div>
                <div class="pweb-custom-field-label">
                    <?php _e('Label', 'pwebcontact'); ?> <span></span>
                </div>
                
                <div class="pweb-custom-field-options">
                    <h3><?php _e('Multiple select list field options', 'pwebcontact'); ?></h3>
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'hidden',
                        'name' => 'type',
                        'index' => 'X',
                        'group' => 'fields',
                        'value' => $field_type
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'text',
                        'name' => 'label',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Label',
                        'class' => 'pweb-custom-field-label-input'
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'textarea',
                        'name' => 'values',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Options',
                        'tooltip' => 'Enter each option in new line',
                        'class' => 'pweb-input-large'
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'text',
                        'name' => 'tooltip',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Tooltip',
                        'class' => 'pweb-input-large'
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'radio',
                        'name' => 'required',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Required',
                        'class' => 'pweb-radio-group',
                        'default' => 0,
                        'options' => array(
                            array('value' => 0, 'name' => 'No'),
                            array('value' => 1, 'name' => 'Yes')
                        )
                    )); ?>
                    
                    <div class="pweb-advanced-options">
                        <button type="button" class="button pweb-advanced-options-toggler">
                            <i class="glyphicon glyphicon-cog"></i> <span><?php _e( 'Advanced', 'pwebcontact' ); ?></span> <i class="glyphicon glyphicon-chevron-down"></i>
                        </button>
                        <div class="pweb-advanced-options-content">
                            
                            <?php echo $this->_get_field(array(
                                'disabled' => true,
                                'type' => 'text',
                                'name' => 'alias',
                                'index' => 'X',
                                'group' => 'fields',
                                'class' => 'pweb-custom-field-alias',
                                'label' => 'Alias for email template'
                            )); ?>
                            
                            <?php echo $this->_get_field(array(
                                'disabled' => true,
                                'type' => 'text',
                                'name' => 'rows',
                                'index' => 'X',
                                'group' => 'fields',
                                'label' => 'Visible rows',
                                'default' => '4',
                                'class' => 'pweb-input-mini'
                            )); ?>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        
        <?php $field_type = 'radio'; ?>
        <div class="pweb-custom-fields-type pweb-custom-field-type-<?php echo $field_type; ?>" id="pweb_field_type_<?php echo $field_type; ?>">
            <?php _e('Radio group', 'pwebcontact'); ?>
            <?php echo $this->_display_badge($field_type); ?>
            
            <div data-type="<?php echo $field_type; ?>" class="pweb-custom-field-container<?php echo $this->_is_pro_field($field_type) ? ' pweb-pro' : ''; ?>">
                <a href="#" class="pweb-custom-field-show-options pweb-has-tooltip" title="<?php _e('Edit'); ?>"><i class="glyphicon glyphicon-edit"></i></a>
                <div class="pweb-custom-field-type">
                    <span><?php _e('Radio group field', 'pwebcontact'); ?></span>
                    <?php echo $this->_display_badge($field_type); ?>
                </div>
                <div class="pweb-custom-field-label">
                    <?php _e('Label', 'pwebcontact'); ?> <span></span>
                </div>
                
                <div class="pweb-custom-field-options">
                    <h3><?php _e('Radio group field options', 'pwebcontact'); ?></h3>
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'hidden',
                        'name' => 'type',
                        'index' => 'X',
                        'group' => 'fields',
                        'value' => $field_type
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'text',
                        'name' => 'label',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Label',
                        'class' => 'pweb-custom-field-label-input'
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'textarea',
                        'name' => 'values',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Options',
                        'tooltip' => 'Enter each option in new line',
                        'class' => 'pweb-input-large'
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'text',
                        'name' => 'tooltip',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Tooltip',
                        'class' => 'pweb-input-large'
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'radio',
                        'name' => 'required',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Required',
                        'class' => 'pweb-radio-group',
                        'default' => 0,
                        'options' => array(
                            array('value' => 0, 'name' => 'No'),
                            array('value' => 1, 'name' => 'Yes')
                        )
                    )); ?>
                    
                    <div class="pweb-advanced-options">
                        <button type="button" class="button pweb-advanced-options-toggler">
                            <i class="glyphicon glyphicon-cog"></i> <span><?php _e( 'Advanced', 'pwebcontact' ); ?></span> <i class="glyphicon glyphicon-chevron-down"></i>
                        </button>
                        <div class="pweb-advanced-options-content">
                            
                            <?php echo $this->_get_field(array(
                                'disabled' => true,
                                'type' => 'text',
                                'name' => 'alias',
                                'index' => 'X',
                                'group' => 'fields',
                                'class' => 'pweb-custom-field-alias',
                                'label' => 'Alias for email template'
                            )); ?>
                            
                            <?php echo $this->_get_field(array(
                                'disabled' => true,
                                'type' => 'text',
                                'name' => 'cols',
                                'index' => 'X',
                                'group' => 'fields',
                                'label' => 'Display options in columns',
                                'class' => 'pweb-input-mini'
                            )); ?>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        
        <?php $field_type = 'checkboxes'; ?>
        <div class="pweb-custom-fields-type pweb-custom-field-type-<?php echo $field_type; ?>" id="pweb_field_type_<?php echo $field_type; ?>">
            <?php _e('Checkboxes group', 'pwebcontact'); ?>
            <?php echo $this->_display_badge($field_type); ?>
            
            <div data-type="<?php echo $field_type; ?>" class="pweb-custom-field-container<?php echo $this->_is_pro_field($field_type) ? ' pweb-pro' : ''; ?>">
                <a href="#" class="pweb-custom-field-show-options pweb-has-tooltip" title="<?php _e('Edit'); ?>"><i class="glyphicon glyphicon-edit"></i></a>
                <div class="pweb-custom-field-type">
                    <span><?php _e('Checkboxes group field', 'pwebcontact'); ?></span>
                    <?php echo $this->_display_badge($field_type); ?>
                </div>
                <div class="pweb-custom-field-label">
                    <?php _e('Label', 'pwebcontact'); ?> <span></span>
                </div>
                
                <div class="pweb-custom-field-options">
                    <h3><?php _e('Checkboxes group field options', 'pwebcontact'); ?></h3>
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'hidden',
                        'name' => 'type',
                        'index' => 'X',
                        'group' => 'fields',
                        'value' => $field_type
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'text',
                        'name' => 'label',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Label',
                        'class' => 'pweb-custom-field-label-input'
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'textarea',
                        'name' => 'values',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Options',
                        'tooltip' => 'Enter each option in new line',
                        'class' => 'pweb-input-large'
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'text',
                        'name' => 'tooltip',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Tooltip',
                        'class' => 'pweb-input-large'
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'radio',
                        'name' => 'required',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Required',
                        'class' => 'pweb-radio-group',
                        'default' => 0,
                        'options' => array(
                            array('value' => 0, 'name' => 'No'),
                            array('value' => 1, 'name' => 'Yes')
                        )
                    )); ?>
                    
                    <div class="pweb-advanced-options">
                        <button type="button" class="button pweb-advanced-options-toggler">
                            <i class="glyphicon glyphicon-cog"></i> <span><?php _e( 'Advanced', 'pwebcontact' ); ?></span> <i class="glyphicon glyphicon-chevron-down"></i>
                        </button>
                        <div class="pweb-advanced-options-content">
                            
                            <?php echo $this->_get_field(array(
                                'disabled' => true,
                                'type' => 'text',
                                'name' => 'alias',
                                'index' => 'X',
                                'group' => 'fields',
                                'class' => 'pweb-custom-field-alias',
                                'label' => 'Alias for email template'
                            )); ?>
                            
                            <?php echo $this->_get_field(array(
                                'disabled' => true,
                                'type' => 'text',
                                'name' => 'cols',
                                'index' => 'X',
                                'group' => 'fields',
                                'label' => 'Display options in columns',
                                'class' => 'pweb-input-mini'
                            )); ?>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        
        <?php $field_type = 'checkbox'; ?>
        <div class="pweb-custom-fields-type pweb-custom-field-type-<?php echo $field_type; ?>" id="pweb_field_type_<?php echo $field_type; ?>">
            <?php _e('Single checkbox', 'pwebcontact'); ?>
            <?php echo $this->_display_badge($field_type); ?>
            
            <div data-type="<?php echo $field_type; ?>" class="pweb-custom-field-container<?php echo $this->_is_pro_field($field_type) ? ' pweb-pro' : ''; ?>">
                <a href="#" class="pweb-custom-field-show-options pweb-has-tooltip" title="<?php _e('Edit'); ?>"><i class="glyphicon glyphicon-edit"></i></a>
                <div class="pweb-custom-field-type">
                    <span><?php _e('Single checkbox field', 'pwebcontact'); ?></span>
                    <?php echo $this->_display_badge($field_type); ?>
                </div>
                <div class="pweb-custom-field-label">
                    <?php _e('Label', 'pwebcontact'); ?> <span></span>
                </div>
                
                <div class="pweb-custom-field-options">
                    <h3><?php _e('Single checkbox field options', 'pwebcontact'); ?></h3>
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'hidden',
                        'name' => 'type',
                        'index' => 'X',
                        'group' => 'fields',
                        'value' => $field_type
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'text',
                        'name' => 'label',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Label',
                        'class' => 'pweb-custom-field-label-input'
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'text',
                        'name' => 'tooltip',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Tooltip',
                        'class' => 'pweb-input-large'
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'radio',
                        'name' => 'required',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Required',
                        'class' => 'pweb-radio-group',
                        'default' => 0,
                        'options' => array(
                            array('value' => 0, 'name' => 'No'),
                            array('value' => 1, 'name' => 'Yes')
                        )
                    )); ?>
                    
                    <div class="pweb-advanced-options">
                        <button type="button" class="button pweb-advanced-options-toggler">
                            <i class="glyphicon glyphicon-cog"></i> <span><?php _e( 'Advanced', 'pwebcontact' ); ?></span> <i class="glyphicon glyphicon-chevron-down"></i>
                        </button>
                        <div class="pweb-advanced-options-content">
                            
                            <?php echo $this->_get_field(array(
                                'disabled' => true,
                                'type' => 'text',
                                'name' => 'alias',
                                'index' => 'X',
                                'group' => 'fields',
                                'class' => 'pweb-custom-field-alias',
                                'label' => 'Alias for email template'
                            )); ?>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        
        <?php $field_type = 'checkbox_modal'; ?>
        <div class="pweb-custom-fields-type pweb-custom-field-type-<?php echo $field_type; ?>" id="pweb_field_type_<?php echo $field_type; ?>">
            <?php _e('Agree to Terms & Conditions', 'pwebcontact'); ?>
            <?php echo $this->_display_badge($field_type); ?>
            
            <div data-type="<?php echo $field_type; ?>" class="pweb-custom-field-container<?php echo $this->_is_pro_field($field_type) ? ' pweb-pro' : ''; ?>">
                <a href="#" class="pweb-custom-field-show-options pweb-has-tooltip" title="<?php _e('Edit'); ?>"><i class="glyphicon glyphicon-edit"></i></a>
                <div class="pweb-custom-field-type">
                    <span><?php _e('Agree to Terms & Conditions field', 'pwebcontact'); ?></span>
                    <?php echo $this->_display_badge($field_type); ?>
                </div>
                <div class="pweb-custom-field-label">
                    <?php _e('Label', 'pwebcontact'); ?> <span><?php _e('Agree to Terms & Conditions', 'pwebcontact'); ?></span>
                </div>
                
                <div class="pweb-custom-field-options">
                    <h3><?php _e('Agree to Terms & Conditions field options', 'pwebcontact'); ?></h3>
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'hidden',
                        'name' => 'type',
                        'index' => 'X',
                        'group' => 'fields',
                        'value' => $field_type
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'text',
                        'name' => 'label',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Label',
                        'default' => 'Agree to Terms & Conditions',
                        'class' => 'pweb-custom-field-label-input'
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'text',
                        'name' => 'tooltip',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Tooltip',
                        'class' => 'pweb-input-large'
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'radio',
                        'name' => 'required',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Required',
                        'class' => 'pweb-radio-group',
                        'default' => 1,
                        'options' => array(
                            array('value' => 0, 'name' => 'No'),
                            array('value' => 1, 'name' => 'Yes')
                        )
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'text',
                        'name' => 'url',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Agree to Terms & Conditions URL',
                        'class' => 'pweb-input-xlarge'
                    )); ?>
                    
                    <div class="pweb-advanced-options">
                        <button type="button" class="button pweb-advanced-options-toggler">
                            <i class="glyphicon glyphicon-cog"></i> <span><?php _e( 'Advanced', 'pwebcontact' ); ?></span> <i class="glyphicon glyphicon-chevron-down"></i>
                        </button>
                        <div class="pweb-advanced-options-content">
                            
                            <?php echo $this->_get_field(array(
                                'disabled' => true,
                                'type' => 'text',
                                'name' => 'alias',
                                'index' => 'X',
                                'group' => 'fields',
                                'class' => 'pweb-custom-field-alias',
                                'label' => 'Alias for email template'
                            )); ?>
                            
                            <?php echo $this->_get_field(array(
                                'disabled' => true,
                                'type' => 'radio',
                                'name' => 'target',
                                'index' => 'X',
                                'group' => 'fields',
                                'label' => 'Open Terms & Conditions in',
                                'class' => 'pweb-radio-group',
                                'default' => 0,
                                'options' => array(
                                    array('value' => 0, 'name' => 'New window'),
                                    array('value' => 1, 'name' => 'Lightbox window')
                                )
                            )); ?>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        
        <?php $field_type = 'email_copy'; ?>
        <div class="pweb-custom-fields-type pweb-custom-field-type-<?php echo $field_type; ?> pweb-custom-fields-single" id="pweb_field_type_<?php echo $field_type; ?>">
            <?php _e('Send copy to yourself', 'pwebcontact'); ?>
            <?php echo $this->_display_badge($field_type); ?>
            
            <div data-type="<?php echo $field_type; ?>" class="pweb-custom-field-container pweb-custom-fields-single<?php echo $this->_is_pro_field($field_type) ? ' pweb-pro' : ''; ?>">
                <a href="#" class="pweb-custom-field-show-options pweb-has-tooltip" title="<?php _e('Edit'); ?>"><i class="glyphicon glyphicon-edit"></i></a>
                <div class="pweb-custom-field-type">
                    <span><?php _e('Send copy to yourself field', 'pwebcontact'); ?></span>
                    <?php echo $this->_display_badge($field_type); ?>
                </div>
                <div class="pweb-custom-field-label">
                    <?php _e('Label', 'pwebcontact'); ?> <span><?php _e('Send copy to yourself', 'pwebcontact'); ?></span>
                </div>
                
                <div class="pweb-custom-field-options">
                    <h3><?php _e('Send copy to yourself field options', 'pwebcontact'); ?></h3>
                    
                    <?php //TODO check if copy to user field is allowed ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'hidden',
                        'name' => 'type',
                        'index' => 'X',
                        'group' => 'fields',
                        'value' => $field_type
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'text',
                        'name' => 'label',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Label',
                        'class' => 'pweb-custom-field-label-input',
                        'default' => __('Send copy to yourself', 'pwebcontact')
                    )); ?>
                </div>
            </div>
        </div>
        
        
        <?php $field_type = 'custom_text'; ?>
        <div class="pweb-custom-fields-type pweb-custom-field-type-<?php echo $field_type; ?>" id="pweb_field_type_<?php echo $field_type; ?>">
            <?php _e('Custom text/html', 'pwebcontact'); ?>
            <?php echo $this->_display_badge($field_type); ?>
            
            <div data-type="<?php echo $field_type; ?>" class="pweb-custom-field-container<?php echo $this->_is_pro_field($field_type) ? ' pweb-pro' : ''; ?>">
                <a href="#" class="pweb-custom-field-show-options pweb-has-tooltip" title="<?php _e('Edit'); ?>"><i class="glyphicon glyphicon-edit"></i></a>
                <div class="pweb-custom-field-type">
                    <span><?php _e('Custom text/html field', 'pwebcontact'); ?></span>
                    <?php echo $this->_display_badge($field_type); ?>
                </div>
                <div class="pweb-custom-field-label">
                    <?php _e('Label', 'pwebcontact'); ?> <span><?php _e('Text/HTML', 'pwebcontact'); ?></span>
                </div>
                
                <div class="pweb-custom-field-options">
                    <h3><?php _e('Custom text/html field options', 'pwebcontact'); ?></h3>
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'hidden',
                        'name' => 'type',
                        'index' => 'X',
                        'group' => 'fields',
                        'value' => $field_type
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'textarea',
                        'name' => 'value',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Text/HTML',
                        'class' => 'pweb-input-large'
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'radio',
                        'name' => 'line_breaks',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Change line breaks to &lt;br&gt;',
                        'class' => 'pweb-radio-group',
                        'default' => 1,
                        'options' => array(
                            array('value' => 0, 'name' => 'No'),
                            array('value' => 1, 'name' => 'Yes')
                        )
                    )); ?>
                </div>
            </div>
        </div>
        
        
        <?php $field_type = 'header'; ?>
        <div class="pweb-custom-fields-type pweb-custom-field-type-<?php echo $field_type; ?>" id="pweb_field_type_<?php echo $field_type; ?>">
            <?php _e('Header', 'pwebcontact'); ?>
            <?php echo $this->_display_badge($field_type); ?>
            
            <div data-type="<?php echo $field_type; ?>" class="pweb-custom-field-container<?php echo $this->_is_pro_field($field_type) ? ' pweb-pro' : ''; ?>">
                <a href="#" class="pweb-custom-field-show-options pweb-has-tooltip" title="<?php _e('Edit'); ?>"><i class="glyphicon glyphicon-edit"></i></a>
                <div class="pweb-custom-field-type">
                    <span><?php _e('Header field', 'pwebcontact'); ?></span>
                    <?php echo $this->_display_badge($field_type); ?>
                </div>
                <div class="pweb-custom-field-label">
                    <?php _e('Label', 'pwebcontact'); ?> <span></span>
                </div>
                
                <div class="pweb-custom-field-options">
                    <h3><?php _e('Header field options', 'pwebcontact'); ?></h3>
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'hidden',
                        'name' => 'type',
                        'index' => 'X',
                        'group' => 'fields',
                        'value' => $field_type
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'text',
                        'name' => 'label',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Header title',
                        'class' => 'pweb-custom-field-label-input',
                    )); ?>
                </div>
            </div>
        </div>
        
        
        <?php $field_type = 'upload'; ?>
        <div class="pweb-custom-fields-type pweb-custom-field-type-<?php echo $field_type; ?> pweb-custom-fields-single" id="pweb_field_type_<?php echo $field_type; ?>">
            <?php _e('Upload', 'pwebcontact'); ?>
            <?php echo $this->_display_badge($field_type); ?>
            
            <div data-type="<?php echo $field_type; ?>" class="pweb-custom-field-container pweb-custom-fields-single<?php echo $this->_is_pro_field($field_type) ? ' pweb-pro' : ''; ?>">
                <a href="#" class="pweb-custom-field-show-options pweb-has-tooltip" title="<?php _e('Edit'); ?>"><i class="glyphicon glyphicon-edit"></i></a>
                <div class="pweb-custom-field-type">
                    <span><?php _e('Upload field', 'pwebcontact'); ?></span>
                    <?php echo $this->_display_badge($field_type); ?>
                </div>
                <div class="pweb-custom-field-label">
                    <?php _e('Label', 'pwebcontact'); ?> <span><?php _e('Attachments', 'pwebcontact'); ?></span>
                </div>

                <div class="pweb-custom-field-options">
                    <h3><?php _e('Upload field options', 'pwebcontact'); ?></h3>
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'hidden',
                        'name' => 'type',
                        'index' => 'X',
                        'group' => 'fields',
                        'value' => $field_type
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'hidden',
                        'name' => 'show_upload',
                        'value' => 1
                    )); ?>
                    
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'text',
                        'name' => 'label',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Label',
                        'class' => 'pweb-custom-field-label-input',
                        'default' => 'Attachments'
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'text',
                        'name' => 'button',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Button label',
                        'default' => 'Add files'
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'text',
                        'name' => 'tooltip',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Tooltip',
                        'class' => 'pweb-input-large'
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'radio',
                        'name' => 'required',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Required',
                        'class' => 'pweb-radio-group',
                        'default' => 0,
                        'options' => array(
                            array('value' => 0, 'name' => 'No'),
                            array('value' => 1, 'name' => 'Yes')
                        )
                    )); ?>
                </div>
            </div>
        </div>
        
        
        <?php $field_type = 'captcha'; ?>
        <div class="pweb-custom-fields-type pweb-custom-field-type-<?php echo $field_type; ?> pweb-custom-fields-single" id="pweb_field_type_<?php echo $field_type; ?>">
            <?php _e('Captcha', 'pwebcontact'); ?>
            <?php echo $this->_display_badge($field_type); ?>
            
            <div data-type="<?php echo $field_type; ?>" class="pweb-custom-field-container pweb-custom-fields-single<?php echo $this->_is_pro_field($field_type) ? ' pweb-pro' : ''; ?>">
                <a href="#" class="pweb-custom-field-show-options pweb-has-tooltip" title="<?php _e('Edit'); ?>"><i class="glyphicon glyphicon-edit"></i></a>
                <div class="pweb-custom-field-type">
                    <span><?php _e('Captcha field', 'pwebcontact'); ?></span>
                    <?php echo $this->_display_badge($field_type); ?>
                </div>
                <div class="pweb-custom-field-label">
                    <?php _e('Label', 'pwebcontact'); ?> <span></span>
                </div>

                <div class="pweb-custom-field-options">
                    <h3><?php _e('Captcha field options', 'pwebcontact'); ?></h3>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'hidden',
                        'name' => 'type',
                        'index' => 'X',
                        'group' => 'fields',
                        'value' => $field_type
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'hidden',
                        'name' => 'captcha',
                        'value' => 'grecaptcha'
                    )); ?>
                    
                    <div class="pweb-field">
                        <a href="https://www.google.com/recaptcha" rel="nofollow" target="_blank">
                            <img src="https://developers.google.com/recaptcha/images/newCaptchaAnchor.gif" alt="Google reCAPTCHA" style="max-width:300px;height:auto;border:0">
                        </a>
                    </div>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'text',
                        'name' => 'label',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Label',
                        'class' => 'pweb-custom-field-label-input'
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'radio',
                        'name' => 'theme',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Theme',
                        'class' => 'pweb-radio-group',
                        'default' => 'light',
                        'options' => array(
                            array('value' => 'light', 'name' => 'Light'),
                            array('value' => 'dark', 'name' => 'Dark')
                        )
                    )); ?>
                </div>
            </div>
        </div>

        <?php $field_type = 'newsletter'; ?>
        <div class="pweb-custom-fields-type pweb-custom-field-type-<?php echo $field_type; ?> pweb-custom-fields-single" id="pweb_field_type_<?php echo $field_type; ?>">
            <?php _e('Newsletter', 'pwebcontact'); ?>
            <?php echo $this->_display_badge($field_type); ?>

            <div data-type="<?php echo $field_type; ?>" class="pweb-custom-field-container pweb-custom-fields-single <?php echo $this->_is_pro_field($field_type) ? ' pweb-pro' : ''; ?>">
                <a href="#" class="pweb-custom-field-show-options pweb-has-tooltip" title="<?php _e('Edit'); ?>"><i class="glyphicon glyphicon-edit"></i></a>
                <div class="pweb-custom-field-type">
                    <span><?php _e('Newsletter field', 'pwebcontact'); ?></span>
                    <?php echo $this->_display_badge($field_type); ?>
                </div>
                <div class="pweb-custom-field-label">
                    <?php _e('Label', 'pwebcontact'); ?> <span></span>
                </div>

                <div class="pweb-custom-field-options">
                    <h3><?php _e('Newsletter field options', 'pwebcontact'); ?></h3>

                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'hidden',
                        'name' => 'type',
                        'index' => 'X',
                        'group' => 'fields',
                        'value' => $field_type
                    )); ?>

                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'select',
                        'name' => 'newsletter_type',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Provider',
                        'class' => 'newsletter_provider',
                        'value' => $field_type,
                        'options' => array(
                            array(
                                'value' => 'mailchimp',
                                'name'  => 'MailChimp'
                            ),
                            array(
                                'value' => 'freshmail',
                                'name'  => 'FreshMail'
                            )
                        )
                    )); ?>

                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'text',
                        'name' => 'mailchimp_apikey',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'API key',
                        'class' => 'newsletter_depends_on_mailchimp pweb-custom-field-label-input'
                    )); ?>

                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'text',
                        'name' => 'freshmail_apikey',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'API key',
                        'class' => 'newsletter_depends_on_freshmail pweb-custom-field-label-input'
                    )); ?>

                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'text',
                        'name' => 'freshmail_secret',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Secret',
                        'class' => 'newsletter_depends_on_freshmail pweb-custom-field-label-input'
                    )); ?>

                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'button',
                        'name' => 'newsletter_ajax',
                        'index' => 'X',
                        'group' => 'fields',
                        'value' => 'Get Lists',
                        'class' => 'newsletter_ajax pweb-custom-field-label-button',
                        'attributes' => array(
                            'data-action' => admin_url( 'admin.php?page=pwebcontact&task=newsletter&ajax=1&_wpnonce='. wp_create_nonce('newsletter') )
                        ),
                    )); ?>

                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'select',
                        'name' => 'newsletter_lists',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Choose list (hold ctrl for windows or cmd for mac for multiple options)',
                        'multiple' => 'multiple',
                        'value' => $field_type,
                        'class' => 'newsletter_lists',
                        'options' => array(
                            array('value'=>'', 'name'=>__('Retrieve list from API'))
                        )
                    )); ?>

                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'radio',
                        'name' => 'newsletter_visibility',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Visibility of lists',
                        'class' => 'pweb-radio-group',
                        'default' => 2,
                        'options' => array(
                            array('value' => 0, 'name' => 'Hidden on front'),
                            array('value' => 1, 'name' => 'Visible on front and checked'),
                            array('value' => 2, 'name' => 'Visible on front and unchecked')
                        )
                    )); ?>

                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'text',
                        'name' => 'label',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Label',
                        'class' => 'pweb-custom-field-label-input'
                    )); ?>

                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'text',
                        'name' => 'tooltip',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Tooltip',
                        'class' => 'pweb-input-large'
                    )); ?>

                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'radio',
                        'name' => 'required',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Required',
                        'class' => 'pweb-radio-group',
                        'default' => 0,
                        'options' => array(
                            array('value' => 0, 'name' => 'No'),
                            array('value' => 1, 'name' => 'Yes')
                        )
                    )); ?>

                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'hidden',
                        'name' => 'alias',
                        'index' => 'X',
                        'group' => 'fields',
                        'class' => 'pweb-custom-field-alias',
                        'label' => '',
                        'value' => 'newsletter'
                    )); ?>

                </div>
            </div>
        </div>

        <?php $field_type = 'mailto_list'; ?>
        <div class="pweb-custom-fields-type pweb-custom-field-type-<?php echo $field_type; ?> pweb-custom-fields-single" id="pweb_field_type_<?php echo $field_type; ?>">
            <?php _e('List of recipients', 'pwebcontact'); ?>
            <?php echo $this->_display_badge($field_type); ?>
            
            <div data-type="<?php echo $field_type; ?>" class="pweb-custom-field-container pweb-custom-fields-single<?php echo $this->_is_pro_field($field_type) ? ' pweb-pro' : ''; ?>">
                <a href="#" class="pweb-custom-field-show-options pweb-has-tooltip" title="<?php _e('Edit'); ?>"><i class="glyphicon glyphicon-edit"></i></a>
                <div class="pweb-custom-field-type">
                    <span><?php _e('List of recipients field', 'pwebcontact'); ?></span>
                    <?php echo $this->_display_badge($field_type); ?>
                </div>
                <div class="pweb-custom-field-label">
                    <?php _e('Label', 'pwebcontact'); ?> <span><?php _e('Contact with', 'pwebcontact'); ?></span>
                </div>
                
                <div class="pweb-custom-field-options">
                    <h3><?php _e('List of recipients field options', 'pwebcontact'); ?></h3>
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'hidden',
                        'name' => 'type',
                        'index' => 'X',
                        'group' => 'fields',
                        'value' => $field_type
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'text',
                        'name' => 'label',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Label',
                        'class' => 'pweb-custom-field-label-input',
                        'default' => 'Contact with'
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'textarea',
                        'name' => 'values',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Recipients',
                        'desc' => 'Shows drop-down list of available recipients in contact form. Put each recipient in new line, separate email address from name with &#x7c; (pipe character). Use following pattern: email&#x7c;name, e.g.: <strong>support@perfect-web.co&#x7c;Support</strong>. Do not enter new line after last recipient! Email addresses will not be visible in contact forms to protect them from spam bots. Only name of recipients will be shown on list.',
                        'class' => 'pweb-filter-emailRecipients pweb-input-large'
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'text',
                        'name' => 'tooltip',
                        'index' => 'X',
                        'group' => 'fields',
                        'label' => 'Tooltip',
                        'class' => 'pweb-input-large'
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'disabled' => true,
                        'type' => 'hidden',
                        'name' => 'required',
                        'index' => 'X',
                        'group' => 'fields',
                        'value' => 1
                    )); ?>
                    
                    <div class="pweb-advanced-options">
                        <button type="button" class="button pweb-advanced-options-toggler">
                            <i class="glyphicon glyphicon-cog"></i> <span><?php _e( 'Advanced', 'pwebcontact' ); ?></span> <i class="glyphicon glyphicon-chevron-down"></i>
                        </button>
                        <div class="pweb-advanced-options-content">
                            
                            <?php echo $this->_get_field(array(
                                'disabled' => true,
                                'type' => 'text',
                                'name' => 'alias',
                                'index' => 'X',
                                'group' => 'fields',
                                'label' => 'Alias for email template',
                                'value' => 'mailto',
                                'readonly' => true
                            )); ?>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    
    
    <div class="pweb-fields-options" id="pweb_fields_options">
        <button type="button" id="pweb_fields_options_close" class="button"><i class="glyphicon glyphicon-chevron-up"></i> <?php _e('Back'); ?></button>
        <div id="pweb_fields_options_content"></div>
        
        
        <div id="pweb_fields_options_content_upload" class="pweb-fields-options-content">
            
            <?php $max_size = $this->_convert_size( ini_get('post_max_size') );
            echo $this->_get_field(array(
                'type' => 'custom',
                'name' => 'upload_max_size',
                'label' => 'Server max upload size',
                'content' => '<span class="badge badge-info">'.$max_size.' MB</span><script type="text/javascript">var pwebUploadMaxSize = '.$max_size.';</script>'
            )); ?>
            
            <?php echo $this->_get_field(array(
                'type' => 'text',
                'name' => 'upload_size_limit',
                'label' => 'File size limit [MB]',
                'tooltip' => 'Set files size in MB for each uploaded file. It can not be grater than server max upload size.',
                'default' => '1',
                'class' => 'pweb-filter-upload-max-size pweb-input-mini'
            )); ?>
            
            <?php echo $this->_get_field(array(
                'type' => 'text',
                'name' => 'upload_files_limit',
                'label' => 'Files limit',
                'tooltip' => 'Set max number of files that can be attached to email. Set 0 for no limit.',
                'default' => '5',
                'class' => 'pweb-filter-float pweb-input-mini'
            )); ?>

            <?php echo $this->_get_field(array(
                'type' => 'text',
                'name' => 'upload_allowed_ext',
                'label' => 'Allowed files extensions',
                'tooltip' => 'List of allowed files extensions separated with pipe | e.g. jpg|gif|png',
                'default' => 'gif|jpe?g|png|docx?|odt|txt|pdf|zip',
                'class' => 'pweb-filter-ext pweb-input-large'
            )); ?>

            <?php echo $this->_get_field(array(
                'type' => 'radio',
                'name' => 'upload_show_limits',
                'label' => 'Show limits in tooltip',
                'tooltip' => 'Show informations about max file size, number of files and allowed types in tooltip.',
                'class' => 'pweb-radio-group',
                'default' => 1,
                'options' => array(
                    array('value' => 0, 'name' => 'No'),
                    array('value' => 1, 'name' => 'Yes')
                )
            )); ?>

            <div class="pweb-advanced-options">
                <button type="button" class="button pweb-advanced-options-toggler">
                    <i class="glyphicon glyphicon-cog"></i> <span><?php _e( 'Advanced', 'pwebcontact' ); ?></span> <i class="glyphicon glyphicon-chevron-down"></i>
                </button>
                <div class="pweb-advanced-options-content">

                    <?php echo $this->_get_field(array(
                        'type' => 'radio',
                        'name' => 'attachment_type',
                        'label' => 'Attachment type in email',
                        'tooltip' => 'Uploaded files can be attached directly in email or as links to files stored on server.',
                        'class' => 'pweb-radio-group',
                        'default' => 1,
                        'options' => array(
                            array('value' => 1, 'name' => 'Files', 'is_parent' => true),
                            array('value' => 2, 'name' => 'Links to files')
                        )
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'type' => 'radio',
                        'name' => 'attachment_delete',
                        'label' => 'Delete files after sending',
                        'tooltip' => 'Delete files from server after email has been sent.',
                        'class' => 'pweb-radio-group',
                        'default' => 1,
                        'parent' => 'attachment_type_1',
                        'options' => array(
                            array('value' => 0, 'name' => 'No'),
                            array('value' => 1, 'name' => 'Yes')
                        )
                    )); ?>
                    
                    <?php echo $this->_get_field(array(
                        'type' => 'radio',
                        'name' => 'upload_autostart',
                        'label' => 'Auto-start upload',
                        'tooltip' => 'Start upload after file has been chosen or droped.',
                        'class' => 'pweb-radio-group',
                        'default' => 1,
                        'options' => array(
                            array('value' => 0, 'name' => 'No'),
                            array('value' => 1, 'name' => 'Yes')
                        )
                    )); ?>
                    
                    <?php $upload_dir = wp_upload_dir();
                    echo $this->_get_field(array(
                        'type' => 'custom',
                        'name' => 'upload_path',
                        'label' => 'Upload path',
                        'content' => $upload_dir['basedir'].'/pwebcontact/'.$this->id.'/'
                            . ' <span class="pweb-text-'. ($this->_check_upload_path() === true ? 'success' : 'danger').'">'
                            . '<i class="glyphicon glyphicon-'. ($this->_check_upload_path() === true ? 'ok-sign' : 'remove-sign').'"></i>'
                            . ($this->_check_upload_path() === true ? __('writable', 'pwebcontact') : __('unwritable', 'pwebcontact')) 
                            . '</span>'
                    )); ?>

                </div>
            </div>
        </div>
        
        
    </div>
    
</div>

<div id="pweb-dialog-fields-load" title="<?php esc_attr_e( 'Load fields', 'pwebcontact' ); ?>" style="display:none">
    <p><?php _e( 'All current fields will be removed!', 'pwebcontact' ); ?></p>
</div>
<div id="pweb-dialog-field-delete" title="<?php esc_attr_e( 'Delete', 'pwebcontact' ); ?>" style="display:none">
    <p><?php _e( 'Are you sure you want to delete this field', 'pwebcontact' ); ?></p>
</div>
