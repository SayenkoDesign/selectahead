<?php
/**
 * @version 2.1.2
 * @package Perfect Easy & Powerful Contact Form
 * @copyright © 2015 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
 * @license GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @author Piotr Moćko
 */

// No direct access
function_exists('add_action') or die;

?>

<h3 class="pweb-steps">
    <?php printf(__('Step %d of %d', 'pwebcontact'), 3, 4); ?>
    -
    <?php _e('Choose predefined theme or create own in customizer', 'pwebcontact'); ?>
    
    <button class="button button-primary pweb-next-tab-button" type="button">
        <?php _e( 'Next', 'pwebcontact' ); ?> <i class="glyphicon glyphicon-chevron-right"></i>
    </button>
</h3>

<?php $themes = $this->_get_themes(); ?>
<div class="flipster" id="pweb-themes-coverflow">
    <ul>
        <?php foreach ($themes as $theme_name => $theme) : 
            if ($theme_name === 'reset') continue; ?>
        <li<?php if ($theme->is_active === true) echo ' class="pweb-active-theme"'; ?>>
            <div class="pweb-theme" data-name="<?php echo $theme_name; ?>" data-settings='<?php echo $theme->settings; ?>'>
                
                <div class="pweb-theme-image">
                    <div class="pweb-theme-badge<?php echo $theme_name === 'free' ? '-free' : ''; ?>"><?php echo $theme_name === 'free' ? 'Free' : 'Pro'; ?></div>
                    <?php if ($theme->image) : ?>
                    <img src="<?php echo $theme->image; ?>" alt="<?php echo $theme->title; ?>">
                    <?php else : ?>
                    <i class="glyphicon glyphicon-picture"></i>
                    <?php endif; ?>
                </div>
                <div class="pweb-theme-caption">
                    <h3><?php echo $theme->title; ?></h3>
                    <p><?php echo $theme->description; ?></p>
                </div>
            </div>
        </li>
        <?php endforeach; ?>
    </ul>
</div>
<div id="pweb-themes-coverflow-controls">
    <button id="pweb-themes-coverflow-control-prev" class="button" type="button">
        <i class="glyphicon glyphicon-chevron-left"></i> <?php _e( 'Previous', 'pwebcontact' ); ?>
    </button>
    <button id="pweb-themes-coverflow-control-load" class="button button-primary pweb-has-tooltip" type="button" title="<?php esc_attr_e( 'Theme settings would only change colors of your form. It would have no influence on layout and fields.', 'pwebcontact' ); ?>">
        <i class="glyphicon glyphicon-open"></i> <?php esc_html_e( 'Load theme & Save form', 'pwebcontact' ); ?>
    </button>
    <button id="pweb-themes-coverflow-control-next" class="button" type="button">
        <?php _e( 'Next', 'pwebcontact' ); ?> <i class="glyphicon glyphicon-chevron-right"></i>
    </button>
    
    <?php echo $this->_get_field_control(array(
        'type' => 'hidden',
        'name' => 'theme'
    )); ?>
</div>

<div id="pweb-dialog-theme" title="<?php esc_attr_e( 'Load theme settings', 'pwebcontact' ); ?>" style="display:none">
    <p>
        <?php _e('Using our themes requires PRO version.', 'pwebcontact'); ?>
    </p>
</div>


<div id="pweb_theme_warning" class="pweb-alert pweb-alert-info">
    <?php _e('Using our themes requires PRO version.', 'pwebcontact'); ?>
    <?php _e('You can create your own theme for FREE just by editing CSS files.', 'pwebcontact'); ?>
    <a class="button" target="_blank" href="<?php echo admin_url('plugin-editor.php?file='.urlencode('pwebcontact/media/css/themes/free.css').'&amp;plugin='.urlencode('pwebcontact/pwebcontact.php')); ?>">
        <i class="glyphicon glyphicon-edit"></i> <?php _e( 'Edit CSS', 'pwebcontact' ); ?>
    </a>
    <button type="button" class="button button-primary pweb-buy">
        <i class="glyphicon glyphicon-shopping-cart"></i> <?php _e( 'Buy PRO', 'pwebcontact' ); ?>
    </button>
</div>


<div class="pweb-advanced-options">
    <button type="button" class="button button-primary pweb-advanced-options-toggler">
        <i class="glyphicon glyphicon-tint"></i> <span><?php _e( 'Theme customizer', 'pwebcontact' ); ?></span> <i class="glyphicon glyphicon-chevron-down"></i>
    </button>
    <div class="pweb-advanced-options-content">

        <?php if (isset($themes['reset'])) : ?>
        <button id="pweb-themes-coverflow-control-reset" class="button pweb-has-tooltip" data-settings='<?php echo $themes['reset']->settings; ?>' type="button" title="<?php esc_attr_e( 'Reset all theme settings to defaults and unload saved theme.', 'pwebcontact' ); ?>">
            <i class="glyphicon glyphicon-remove"></i> <?php _e( 'Clear theme settings', 'pwebcontact' ); ?>
        </button>
        <?php endif; ?>
        
        <hr>
        
        <div class="pweb-clearfix">
            <div class="pweb-width-33">
                <?php echo $this->_get_field(array(
                    'type' => 'radio',
                    'name' => 'rounded',
                    'header' => 'Common',
                    'label' => 'Display rounded corners',
                    'default' => 0,
                    'class' => 'pweb-radio-group',
                    'options' => array(
                        array(
                            'value' => 0,
                            'name' => 'No'
                        ),
                        array(
                            'value' => 1,
                            'name' => 'Yes'
                        )
                    )
                )); ?>

                <?php echo $this->_get_field(array(
                    'type' => 'radio',
                    'name' => 'shadow',
                    'label' => 'Display shadow',
                    'default' => 0,
                    'class' => 'pweb-radio-group',
                    'options' => array(
                        array(
                            'value' => 0,
                            'name' => 'No'
                        ),
                        array(
                            'value' => 1,
                            'name' => 'Yes'
                        )
                    )
                )); ?>
                
                <?php echo $this->_get_field(array(
                    'type' => 'radio',
                    'name' => 'gradient',
                    'label' => 'Choose design',
                    'default' => 1,
                    'class' => 'pweb-radio-group',
                    'options' => array(
                        array(
                            'value' => 1,
                            'name' => 'Gradients'
                        ),
                        array(
                            'value' => 2,
                            'name' => 'Flat'
                        )
                    )
                )); ?>
            </div>


            <div class="pweb-width-33">
                <?php echo $this->_get_field(array(
                    'type' => 'radio',
                    'name' => 'labels_position',
                    'header' => 'Labels and From size',
                    'label' => 'Labels position',
                    'tooltip' => 'Select placement of fields labels. For mobile devices (phones) labels inline are displayed above fields.',
                    'default' => 'above',
                    'class' => 'pweb-radio-group-vertical',
                    'options' => array(
                        array(
                            'value' => 'inline',
                            'name' => 'Inline with field',
                            'is_parent' => true
                        ),
                        array(
                            'value' => 'above',
                            'name' => 'Above field'
                        ),
                        array(
                            'value' => 'over',
                            'name' => 'Inside field as placeholder'
                        )
                    )
                )); ?>
                
                <?php echo $this->_get_field(array(
                    'type' => 'color',
                    'name' => 'labels_invalid_color',
                    'label' => 'Color of invalid labels',
                    'tooltip' => 'Select text color of invalid labels',
                    'default' => '#AA0000'
                )); ?>

                <?php echo $this->_get_field(array(
                    'type' => 'text',
                    'name' => 'labels_width',
                    'label' => 'Labels width [%]',
                    'tooltip' => 'Set labels width in percents.',
                    'class' => 'pweb-filter-int pweb-input-mini',
                    'parent' => 'labels_position_inline'
                )); ?>

                <?php echo $this->_get_field(array(
                    'type' => 'text',
                    'name' => 'form_width',
                    'label' => 'Form width [px, %]',
                    'tooltip' => 'Width of form is also a width of Lightbox window. If you want to maximize the window then set 100%.',
                    'class' => 'pweb-filter-unit pweb-input-mini'
                )); ?>
            </div>
            
            <div class="pweb-width-33">
                <?php echo $this->_get_field(array(
                    'type' => 'color',
                    'name' => 'text_color',
                    'header' => 'Form text',
                    'label' => 'Color of form text',
                    'tooltip' => 'Select color of text'
                )); ?>
                
                <?php echo $this->_get_field(array(
                    'type' => 'text',
                    'name' => 'form_font_size',
                    'label' => 'Form font size',
                    'tooltip' => 'Size of form font, e.g. 12px, 10pt, 100%',
                    'class' => 'pweb-filter-unit pweb-input-mini'
                )); ?>

                <?php echo $this->_get_field(array(
                    'type' => 'text',
                    'name' => 'form_font_family',
                    'label' => 'Form font family',
                    'tooltip' => 'Name of font used for form. Separate multiple names with coma and wrap name which contains space with single quote.'
                )); ?>
                
                <div class="pweb-alert pweb-alert-info">
                    <?php _e('You can choose color of success and error message by changing Advanced option in `Email` tab', 'pwebcontact'); ?>
                </div>
            </div>
        </div>

        <hr>
        
        <div class="pweb-clearfix">
            <div class="pweb-width-33">
                <?php echo $this->_get_field(array(
                    'type' => 'color',
                    'name' => 'fields_color',
                    'header' => 'Fields',
                    'label' => 'Color of fields',
                    'tooltip' => 'Select background color of fields'
                )); ?>
                
                <?php echo $this->_get_field(array(
                    'type' => 'color',
                    'name' => 'fields_border_color',
                    'label' => 'Color of fields border',
                    'tooltip' => 'Select border color of fields'
                )); ?>

                <?php echo $this->_get_field(array(
                    'type' => 'color',
                    'name' => 'fields_text_color',
                    'label' => 'Color of fields text',
                    'tooltip' => 'Select text color of fields'
                )); ?>
            </div>
            
            <div class="pweb-width-33">
                <?php echo $this->_get_field(array(
                    'type' => 'color',
                    'name' => 'fields_active_color',
                    'header' => 'Fields active',
                    'label' => 'Color of active field',
                    'tooltip' => 'Select background color of active field'
                )); ?>
                
                <?php echo $this->_get_field(array(
                    'type' => 'color',
                    'name' => 'fields_active_border_color',
                    'label' => 'Color of active field border',
                    'tooltip' => 'Select border color of active field'
                )); ?>

                <?php echo $this->_get_field(array(
                    'type' => 'color',
                    'name' => 'fields_active_text_color',
                    'label' => 'Color of active field text',
                    'tooltip' => 'Select text color of active field'
                )); ?>
            </div>
            
            <div class="pweb-width-33">
                <?php echo $this->_get_field(array(
                    'type' => 'color',
                    'name' => 'fields_invalid_color',
                    'header' => 'Fields invalid',
                    'label' => 'Color of invalid fields',
                    'tooltip' => 'Select background color of invalid fields'
                )); ?>
                
                <?php echo $this->_get_field(array(
                    'type' => 'color',
                    'name' => 'fields_invalid_border_color',
                    'label' => 'Color of invalid fields border',
                    'tooltip' => 'Select border color of invalid fields',
                    'default' => '#AA0000'
                )); ?>

                <?php echo $this->_get_field(array(
                    'type' => 'color',
                    'name' => 'fields_invalid_text_color',
                    'label' => 'Color of invalid fields text',
                    'tooltip' => 'Select text color of invalid fields',
                    'default' => '#AA0000'
                )); ?>
            </div>
        </div>
            
        <hr>
        
        <div class="pweb-clearfix">
            <div class="pweb-width-33">
                <?php echo $this->_get_field(array(
                    'type' => 'color',
                    'name' => 'buttons_fields_color',
                    'header' => 'Buttons and Links',
                    'label' => 'Color of buttons and links',
                    'tooltip' => 'Select color of buttons background, links text color and upload progress bar.'
                )); ?>

                <?php echo $this->_get_field(array(
                    'type' => 'color',
                    'name' => 'buttons_text_color',
                    'label' => 'Color of buttons text',
                    'tooltip' => 'Select color of buttons text'
                )); ?>
            </div>
            
            <div class="pweb-width-33">
                <?php echo $this->_get_field(array(
                    'type' => 'color',
                    'name' => 'bg_color',
                    'header' => 'Background',
                    'label' => 'Color of form background and opacity',
                )); ?>

                <?php echo $this->_get_field_control(array(
                    'type' => 'select',
                    'name' => 'bg_opacity',
                    'default' => 0.9,
                    'options' => array(
                        array('value' => 0.1),
                        array('value' => 0.2),
                        array('value' => 0.3),
                        array('value' => 0.4),
                        array('value' => 0.5),
                        array('value' => 0.6),
                        array('value' => 0.7),
                        array('value' => 0.8),
                        array('value' => 0.9),
                        array('value' => 1.0)
                    )
                )); ?>

                <?php echo $this->_get_field(array(
                    'type' => 'select',
                    'name' => 'bg_padding_position',
                    'label' => 'Form padding',
                    'tooltip' => 'Select where you want to create space for background image.',
                    'options' => array(
                        array(
                            'value' => '',
                            'name' => '- Disabled -'
                        ),
                        array(
                            'value' => 'left',
                            'name' => 'Left'
                        ),
                        array(
                            'value' => 'right',
                            'name' => 'Right'
                        ),
                        array(
                            'value' => 'top',
                            'name' => 'Top'
                        ),
                        array(
                            'value' => 'bottom',
                            'name' => 'Bottom'
                        ),
                        array(
                            'value' => 'all',
                            'name' => 'All around'
                        ),
                    )
                )); ?>

                <?php echo $this->_get_field(array(
                    'type' => 'text',
                    'name' => 'bg_padding',
                    'label' => 'Padding value [px, %]',
                    'tooltip' => 'Size of space for background image.',
                    'class' => 'pweb-filter-unit pweb-input-mini'
                )); ?>
            </div>
            
            <div class="pweb-width-33">
                <?php echo $this->_get_field(array(
                    'type' => 'image',
                    'name' => 'bg_image',
                    'header' => 'Background image',
                    'label' => 'Background image',
                    'tooltip' => 'Enter URL of image which will be shown in background of contact form. URL should be relative to WordPress root.',
                    'class' => 'pweb-input-xlarge'
                )); ?>

                <?php echo $this->_get_field(array(
                    'type' => 'select',
                    'name' => 'bg_position',
                    'label' => 'Background image alignment',
                    'tooltip' => 'Select position of background image: horizontal vertical.',
                    'options' => array(
                        array('value' => '', 'name' => '- Use default -'),
                        array('value' => 'left top'),
                        array('value' => 'left center'),
                        array('value' => 'left bottom'),
                        array('value' => 'right top'),
                        array('value' => 'right center'),
                        array('value' => 'right bottom'),
                        array('value' => 'center top'),
                        array('value' => 'center center'),
                        array('value' => 'center bottom')
                    )
                )); ?>
                
                <?php echo $this->_get_field(array(
                    'type' => 'select',
                    'name' => 'bg_repeat',
                    'label' => 'Background repeat',
                    'tooltip' => '',
                    'options' => array(
                        array(
                            'value' => '',
                            'name' => 'No repeat'
                        ),
                        array(
                            'value' => 'repeat',
                            'name' => 'Repeat'
                        ),
                        array(
                            'value' => 'repeat-x',
                            'name' => 'Repeat X'
                        ),
                        array(
                            'value' => 'repeat-y',
                            'name' => 'Repeat Y'
                        )
                    )
                )); ?>
                
                <?php echo $this->_get_field(array(
                    'type' => 'select',
                    'name' => 'bg_size',
                    'label' => 'Background scale',
                    'tooltip' => '',
                    'options' => array(
                        array(
                            'value' => '',
                            'name' => '- Use default -'
                        ),
                        array(
                            'value' => 'cover',
                            'name' => 'Cover'
                        ),
                        array(
                            'value' => 'contain',
                            'name' => 'Contain'
                        )
                    )
                )); ?>
            </div>
        </div>
        
        <hr>
        
        <div class="pweb-clearfix">
            <div class="pweb-width-33">
                <?php echo $this->_get_field(array(
                    'type' => 'color',
                    'name' => 'toggler_bg',
                    'header' => 'Toggler Button and Tab',
                    'label' => 'Color of Toggler',
                    'tooltip' => 'Select background color of Toggler Tab.',
                    'parent' => array('handler_tab', 'handler_button')
                )); ?>

                <?php echo $this->_get_field(array(
                    'type' => 'color',
                    'name' => 'toggler_color',
                    'label' => 'Color of Toggler text',
                    'tooltip' => 'Select text color of Toggler Tab',
                    'parent' => array('handler_tab', 'handler_button')
                )); ?>
                
                <?php echo $this->_get_field(array(
                    'type' => 'radio',
                    'name' => 'toggler_icon',
                    'label' => 'Toggler icon',
                    'tooltip' => 'Select source for Toggler Tab icon.',
                    'default' => 0,
                    'parent' => array('handler_tab', 'handler_button'),
                    'class' => 'pweb-radio-group-vertical',
                    'options' => array(
                        array(
                            'value' => 0,
                            'name' => 'Disabled'
                        ),
                        array(
                            'value' => 'glyphicon',
                            'name' => 'Glyphicons',
                            'is_parent' => true
                        ),
                        array(
                            'value' => 'gallery',
                            'name' => 'Gallery',
                            'is_parent' => true
                        ),
                        array(
                            'value' => 'custom',
                            'name' => 'Custom image',
                            'is_parent' => true
                        )
                    )
                )); ?>

                <?php echo $this->_get_field(array(
                    'type' => 'glyphicon',
                    'name' => 'toggler_glyphicon',
                    'label' => 'Glyphicons',
                    'default' => '2709',
                    'parent' => array('toggler_icon_glyphicon'),
                    'options' => array(
                        array(
                            'value' => '',
                            'name' => ''
                        )
                    )
                )); ?>

                <?php echo $this->_get_field(array(
                    'type' => 'filelist',
                    'name' => 'toggler_icon_gallery_image',
                    'label' => 'Gallery icon',
                    'tooltip' => 'Select image from directory: `wp-content/plugins/pwebcontact/media/images/icons`.',
                    'filter' => '\.(jpg|png|gif)$',
                    'directory' => 'media/images/icons',
                    'parent' => array('toggler_icon_gallery')
                )); ?>

                <?php echo $this->_get_field(array(
                    'type' => 'image',
                    'name' => 'toggler_icon_custom_image',
                    'label' => 'Custom icon',
                    'tooltip' => 'Enter URL with custom image file with icon for Toggler Tab.',
                    'parent' => array('toggler_icon_custom'),
                    'class' => 'pweb-input-xlarge'
                )); ?>
                
                <?php echo $this->_get_field(array(
                    'type' => 'text',
                    'name' => 'toggler_font_size',
                    'label' => 'Toggler font size',
                    'tooltip' => 'Size of Toggler font, e.g. 12px, 10pt, 100%',
                    'class' => 'pweb-filter-unit pweb-input-mini',
                    'parent' => array('handler_tab', 'handler_button')
                )); ?>

                <?php echo $this->_get_field(array(
                    'type' => 'text',
                    'name' => 'toggler_font_family',
                    'label' => 'Toggler font family',
                    'tooltip' => 'Name of font used for Toggler. Separate multiple names with coma and wrap name which contains space with single quote.',
                    'parent' => array('toggler_vertical_0')
                )); ?>
                
                <?php echo $this->_get_field(array(
                    'type' => 'radio',
                    'name' => 'toggler_slide',
                    'label' => 'Slide Toggler Tab with box',
                    'tooltip' => 'Slide `Toggler Tab` with contact form. Works only with `Slide in Box` layout except on left and right side with horizontal `Toggler Tab`.',
                    'default' => 0,
                    'parent' => array('layout_type_slidebox'),
                    'class' => 'pweb-radio-group',
                    'options' => array(
                        array(
                            'value' => 0,
                            'name' => 'No'
                        ),
                        array(
                            'value' => 1,
                            'name' => 'Yes'
                        )
                    )
                )); ?>
            </div>
            
            <div class="pweb-width-33">
                <?php echo $this->_get_field(array(
                    'type' => 'radio',
                    'name' => 'toggler_vertical',
                    'label' => 'Vertical Toggler Tab',
                    'header' => 'Vertical Toggler Tab',
                    'tooltip' => 'Flip toggler tab to vertical orientation. Default vertical text color is white.',
                    'default' => 0,
                    'parent' => array('toggler_position_left', 'toggler_position_right'),
                    'class' => 'pweb-radio-group',
                    'options' => array(
                        array(
                            'value' => 0,
                            'name' => 'No',
                            'is_parent' => true
                        ),
                        array(
                            'value' => 1,
                            'name' => 'Yes',
                            'is_parent' => true,
                            'disabled' => $this->_check_image_text_creation() !== true
                        )
                    ),
                    'html_after' => 
                          '<div class="pweb_params_toggler_vertical_1" style="display:none">'
                            . '<div class="pweb-alert pweb-alert-warning">'
                                . '<strong>' . __('Front-end troubleshooting', 'pwebcontact') . '</strong><br>'
                                . __('If you see rectangles instead of letters then you have to use other `TTF font`.', 'pwebcontact') . '<br>'
                                . __('If text is cut then enlarge height of toggler. Width leave blank.', 'pwebcontact')
                            . '</div>'
                        . '</div>'
                )); ?>

                <?php echo $this->_get_field(array(
                    'type' => 'radio',
                    'name' => 'toggler_rotate',
                    'label' => 'Rotate Vertical Toggler Tab text',
                    'tooltip' => 'Rotate text by 90&deg; to the left or right.',
                    'default' => 1,
                    'parent' => array('toggler_vertical_1'),
                    'options' => array(
                        array(
                            'value' => -1,
                            'name' => '-90&deg; (counter-clockwise)' //TODO add utf8 rotate arrow
                        ),
                        array(
                            'value' => 1,
                            'name' => ' 90&deg; (clockwise)' //TODO add utf8 rotate arrow
                        )
                    )
                )); ?>

                <?php echo $this->_get_field(array(
                    'type' => 'filelist',
                    'name' => 'toggler_font',
                    'label' => 'TTF font for Vertical Toggler Tab text',
                    'tooltip' => 'Select font from directory: `wp-content/plugins/pwebcontact/media/fonts`. If it does not support your language then upload there other font file.',
                    'default' => 'NotoSans-Regular',
                    'filter' => '\.ttf$',
                    'directory' => 'media/fonts',
                    'strip_ext' => true,
                    'parent' => array('toggler_vertical_1')
                )); ?>
                
                <?php echo $this->_get_field(array(
                    'type' => 'text',
                    'name' => 'toggler_width',
                    'header' => 'Toggler size',
                    'label' => 'Toggler width [px]',
                    'tooltip' => 'Toggler Tab width in pixels (without unit), e.g. 100. Leave blank for enabled vertical toggler.',
                    'class' => 'pweb-filter-int pweb-input-mini',
                    'parent' => array('handler_tab', 'handler_button')
                )); ?>

                <?php echo $this->_get_field(array(
                    'type' => 'text',
                    'name' => 'toggler_height',
                    'label' => 'Toggler height [px]',
                    'tooltip' => 'Toggler Tab height in pixels (without unit), e.g. 20. Leave blank for disabled vertical toggler.',
                    'class' => 'pweb-filter-int pweb-input-mini',
                    'parent' => array('handler_tab', 'handler_button')
                )); ?>
            </div>
            
            <div class="pweb-width-33">
                <?php echo $this->_get_field(array(
                    'type' => 'radio',
                    'name' => 'accordion_boxed',
                    'header' => 'Accordion',
                    'label' => 'Show accordion box with arrow',
                    'tooltip' => 'Surround contact form with box and show arrow at top of this box.',
                    'default' => 1,
                    'parent' => array('layout_type_accordion'),
                    'class' => 'pweb-radio-group',
                    'options' => array(
                        array(
                            'value' => 0,
                            'name' => 'No'
                        ),
                        array(
                            'value' => 1,
                            'name' => 'Yes'
                        )
                    )
                )); ?>

                <?php echo $this->_get_field(array(
                    'type' => 'color',
                    'name' => 'modal_bg',
                    'header' => 'Lightbox backdrop',
                    'label' => 'Lightbox backdrop color',
                    'tooltip' => 'Color of background layer under Lightbox window.',
                    'parent' => array('layout_type_modal')
                )); ?>

                <?php echo $this->_get_field(array(
                    'type' => 'select',
                    'name' => 'modal_opacity',
                    'label' => 'Lightbox backdrop opacity',
                    'tooltip' => 'Transparency of background layer under Lightbox window.',
                    'default' => -1,
                    'parent' => array('layout_type_modal'),
                    'options' => array(
                        array('value' => -1, 'name' => '- Use default -'),
                        array('value' => 0, 'name' => 'Hide'),
                        array('value' => 0.1),
                        array('value' => 0.2),
                        array('value' => 0.3),
                        array('value' => 0.4),
                        array('value' => 0.5),
                        array('value' => 0.6),
                        array('value' => 0.7),
                        array('value' => 0.8),
                        array('value' => 0.9),
                        array('value' => 1.0)
                    )
                )); ?>
            </div>
        </div>
        
    </div>
</div>