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

<?php echo $this->_get_field(array(
    'type' => 'radio',
    'name' => 'handler',
    'label' => 'How do you want to display your form before it is opened?',
    'class' => 'pweb-related',
    'default' => 'tab',
    'required' => true,
    'options' => array(
        array(
            'value' => 'button',
            'name' => 'Toggler Button',
            'class' => 'pweb-layout_type-button pweb-related-accordion pweb-related-modal-button',
            'is_parent' => true
        ),
        array(
            'value' => 'tab',
            'name' => 'Toggler Tab',
            'class' => 'pweb-layout_type-tab pweb-related-slidebox pweb-related-modal',
            'is_parent' => true
        ),
        array(
            'value' => 'static',
            'name' => 'Always opened inside page content or sidebar',
            'class' => 'pweb-layout_type-static pweb-related-static'
        ),
        array(
            'value' => 'hidden',
            'name' => 'Hidden before opening',
            'class' => 'pweb-layout_type-hidden pweb-related-modal pweb-related-accordion pweb-related-slidebox pweb-related-modal-button'
        )
    )
)); ?>



<?php echo $this->_get_field(array(
    'type' => 'text',
    'name' => 'toggler_name',
    'label' => 'Define text shown on Toggler Tab or Button',
    'default' => 'Contact us',
    'parent' => array('handler_button', 'handler_tab')
)); ?>

<div class="pweb-advanced-options">
    <button type="button" class="button pweb-advanced-options-toggler">
        <i class="glyphicon glyphicon-cog"></i> <span><?php _e( 'Advanced', 'pwebcontact' ); ?></span> <i class="glyphicon glyphicon-chevron-down"></i>
    </button>
    <div class="pweb-advanced-options-content">
        <?php echo $this->_get_field(array(
            'type' => 'radio',
            'name' => 'toggler_position',
            'label' => 'Toggler Tab position',
            'parent' => 'handler_tab',
            'default' => 'left',
            'class' => 'pweb-radio-group',
            'options' => array(
                array(
                    'value' => 'left',
                    'name' => 'Left',
                    'after' => '<br><span class="pwebposition-left"><span></span><span>&varr;</span></span>',
                    'is_parent' => true
                ),
                array(
                    'value' => 'right',
                    'name' => 'Right',
                    'after' => '<br><span class="pwebposition-right"><span></span><span>&varr;</span></span>',
                    'is_parent' => true
                ),
                array(
                    'value' => 'top:left',
                    'name' => 'Top left',
                    'after' => '<br><span class="pwebposition-top-left"><span></span><span>&harr;</span></span>'
                ),
                array(
                    'value' => 'top:right',
                    'name' => 'Top right',
                    'after' => '<br><span class="pwebposition-top-right"><span></span><span>&harr;</span></span>'
                ),
                array(
                    'value' => 'bottom:left',
                    'name' => 'Bottom left',
                    'after' => '<br><span class="pwebposition-bottom-left"><span></span><span>&harr;</span></span>'
                ),
                array(
                    'value' => 'bottom:right',
                    'name' => 'Bottom right',
                    'after' => '<br><span class="pwebposition-bottom-right"><span></span><span>&harr;</span></span>'
                )
            ),
            'html_after' => 
                  '<div class="pweb_params_toggler_position_left pweb_params_toggler_position_right" style="display:none">'
                    . '<div class="pweb-alert pweb-alert-info">'
                        . __('You can flip Toggler Tab to vertical position by changing Advanced option in `Theme` tab', 'pwebcontact')
                    . '</div>'
                . '</div>'
        )); ?>
        
        <div class="pweb-clearfix">
            <div class="pweb-width-50">
            <?php echo $this->_get_field(array(
                'type' => 'text',
                'name' => 'offset',
                'label' => 'Position offset [px, %]',
                'tooltip' => 'Set length of red arrow in above `Toggler Tab position` with unit, e.g. 150px, 25%',
                'class' => 'pweb-filter-unit pweb-input-mini',
                'parent' => array('layout_type_slidebox', 'handler_tab')
            )); ?>
            </div>
            <div class="pweb-width-50">
            <?php echo $this->_get_field(array(
                'type' => 'text',
                'name' => 'zindex',
                'label' => 'Layer level (CSS z-index)',
                'tooltip' => 'Default level is 1030. If contact form appears under menu or other layers then enlarge default value.',
                'class' => 'pweb-filter-int pweb-input-mini',
                'parent' => array('layout_type_slidebox', 'layout_type_modal')
            )); ?>
            </div>
        </div>


        <?php echo $this->_get_field(array(
            'type' => 'radio',
            'name' => 'open_toggler',
            'label' => 'Auto-open',
            'header' => 'Auto open and close',
            'tooltip' => 'Open contact form automatically after page has been loaded or when User scrolls down the page. On exit is experimental and might not work in old browsers.',
            'default' => 0,
            'parent' => array('layout_type_slidebox', 'layout_type_modal', 'layout_type_accordion'),
            'class' => 'pweb-radio-group',
            'options' => array(
                array(
                    'value' => 0,
                    'name' => 'No'
                ),
                array(
                    'value' => 1,
                    'name' => 'On load',
                    'is_parent' => true
                ),
                array(
                    'value' => 2,
                    'name' => 'On scroll',
                    'is_parent' => true
                ),
                array(
                    'value' => 3,
                    'name' => 'On exit',
                    'is_parent' => true
                )
            )
        )); ?>
        
        <?php echo $this->_get_field(array(
            'type' => 'text',
            'name' => 'open_delay',
            'label' => 'Auto-open delay [ms]',
            'tooltip' => 'Set delay in mili-seconds for auto-open, e.g. 1000 (1s delay). Set 0 for no delay.',
            'default' => 1000,
            'class' => 'pweb-filter-int pweb-input-mini',
            'parent' => array('open_toggler_1', 'open_toggler_2', 'open_toggler_3')
        )); ?>
        
        <?php echo $this->_get_field(array(
            'type' => 'text',
            'name' => 'open_count',
            'label' => 'Auto-open count',
            'tooltip' => 'Set number of auto-open times. Counter is incremented after page refresh. Set 0 for unlimited auto-popups. Information is stored in cookie and will be cleared when cookie expires.',
            'default' => 1,
            'class' => 'pweb-filter-int pweb-input-mini',
            'parent' => array('open_toggler_1', 'open_toggler_2', 'open_toggler_3')
        )); ?>
        
        <?php echo $this->_get_field(array(
            'type' => 'text',
            'name' => 'cookie_lifetime',
            'label' => 'Cookie lifetime [days]',
            'tooltip' => 'Set 0 that cookie would expire when the browser is closed or number of days after which cookie would expire.',
            'default' => 30,
            'class' => 'pweb-filter-int pweb-input-mini',
            'parent' => array('open_toggler_1', 'open_toggler_2', 'open_toggler_3')
        )); ?>



        <?php echo $this->_get_field(array(
            'type' => 'radio',
            'name' => 'close_toggler',
            'label' => 'Auto-close',
            'tooltip' => 'Close contact form automatically after sending email',
            'default' => 0,
            'parent' => array('layout_type_slidebox', 'layout_type_modal', 'layout_type_accordion'),
            'class' => 'pweb-radio-group',
            'options' => array(
                array(
                    'value' => 0,
                    'name' => 'No'
                ),
                array(
                    'value' => 1,
                    'name' => 'On mail success',
                    'is_parent' => true
                )
            )
        )); ?>
        
        <?php echo $this->_get_field(array(
            'type' => 'text',
            'name' => 'close_delay',
            'label' => 'Auto-close delay [ms]',
            'tooltip' => 'Set delay in mili-seconds for auto-close, e.g. 1000 (1s delay). Set 0 to disable.',
            'default' => 0,
            'class' => 'pweb-filter-int pweb-input-mini',
            'parent' => array('close_toggler_1')
        )); ?>
        
        <?php echo $this->_get_field(array(
            'type' => 'radio',
            'name' => 'close_other',
            'label' => 'Close other Perfect Contact Forms and Boxes',
            'tooltip' => 'Close all other Slide in Boxes and Accordion from Perfect-Web when opening another one.',
            'default' => 0,
            'parent' => array('layout_type_slidebox', 'layout_type_accordion'),
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
            'name' => 'modal_disable_close',
            'label' => 'Disable manual closing of Lightbox',
            'tooltip' => 'Hide close button of Lightbox window and disable closing when clicking backdrop. Window can only be closed with Auto-close event, page refresh or custom link.',
            'default' => 0,
            'parent' => array('layout_type_modal'),
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
            'type' => 'textarea',
            'name' => 'onload',
            'label' => 'JavaScript on load event',
            'header' => 'JavaScript events',
            'tooltip' => 'JavaScript code called on page load. Do not insert any HTML tags!',
            'class' => 'pweb-filter-javascript widefat',
            'attributes' => array(
                'rows' => 5,
                'cols' => 50
            )
        )); ?>
        
        <?php echo $this->_get_field(array(
            'type' => 'textarea',
            'name' => 'onopen',
            'label' => 'JavaScript on open event',
            'tooltip' => 'JavaScript code called on form open. Do not insert any HTML tags!',
            'class' => 'pweb-filter-javascript widefat',
            'parent' => array('layout_type_slidebox', 'layout_type_modal', 'layout_type_accordion'),
            'attributes' => array(
                'rows' => 5,
                'cols' => 50
            )
        )); ?>
        
        <?php echo $this->_get_field(array(
            'type' => 'textarea',
            'name' => 'onclose',
            'label' => 'JavaScript on close event',
            'tooltip' => 'JavaScript code called on form close. Do not insert any HTML tags!',
            'class' => 'pweb-filter-javascript widefat',
            'parent' => array('layout_type_slidebox', 'layout_type_modal', 'layout_type_accordion'),
            'attributes' => array(
                'rows' => 5,
                'cols' => 50
            )
        )); ?>
    </div>
</div>