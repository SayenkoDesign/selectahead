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

<div style="display:none">
<?php echo $this->_get_field_control(array(
    'type' => 'radio',
    'name' => 'layout_type',
    'class' => 'pweb-related',
    'default' => 'slidebox',
    'required' => true,
    'options' => array(
        array(
            'value' => 'slidebox',
            'name' => 'Slide Box',
            'class' => 'pweb-related-slidebox',
            'is_parent' => true
        ),
        array(
            'value' => 'modal',
            'name' => 'Lightbox',
            'class' => 'pweb-related-modal pweb-related-modal-button',
            'is_parent' => true
        ),
        array(
            'value' => 'accordion',
            'name' => 'Accordion',
            'class' => 'pweb-related-accordion',
            'is_parent' => true
        ),
        array(
            'value' => 'static',
            'name' => 'Static',
            'class' => 'pweb-related-static'
        )
    )
)); ?>
</div>


<?php echo $this->_get_field(array(
    'type' => 'radio',
    'name' => 'effect',
    'label' => 'How do you want to display form after opening?',
    'class' => 'pweb-related',
    'default' => 'slidebox:slide_in',
    'required' => true,
    'options' => array(
        array(
            'value' => 'slidebox:slide_in',
            'name' => 'Slidebox - Slide-in from page edge',
            'class' => 'pweb-effect-slide-in pweb-related-slidebox',
            'is_parent' => true
        ),
        array(
            'value' => 'modal:fade',
            'name' => 'Lightbox - Fade-in',
            'class' => 'pweb-effect-modal-fade pweb-related-modal pweb-related-modal-button',
            'is_parent' => true
        ),
        // TODO css for modal drop
        /*array(
            'value' => 'modal:drop',
            'name' => 'Lightbox - Drop from top and fade-in',
            'class' => 'pweb-effect-modal-drop pweb-related-modal pweb-related-modal-button',
            'is_parent' => true
        ),*/
        array(
            'value' => 'modal:rotate',
            'name' => 'Lightbox - Move from toggler, rotate, enlarge and fade-in',
            'class' => 'pweb-effect-modal-rotate pweb-related-modal pweb-related-modal-button',
            'is_parent' => true
        ),
        array(
            'value' => 'modal:square',
            'name' => 'Lightbox - Move from toggler, enlarge and fade-in',
            'class' => 'pweb-effect-modal-square pweb-related-modal pweb-related-modal-button',
            'is_parent' => true
        ),
        array(
            'value' => 'modal:smooth',
            'name' => 'Lightbox - Move from toggler, change height and fade-in',
            'class' => 'pweb-effect-modal-smooth pweb-related-modal pweb-related-modal-button',
            'is_parent' => true
        ),
        array(
            'value' => 'accordion:slide_down',
            'name' => 'Accordion - Slide-down inside page content or sidebar',
            'class' => 'pweb-effect-slide-down pweb-related-accordion',
            'is_parent' => true
        ),
        array(
            'value' => 'static:none',
            'name' => 'Static form inside page content or sidebar',
            'class' => 'pweb-effect-none pweb-related-static'
        )
    )
)); ?>

<div class="pweb-advanced-options">
    <button type="button" class="button pweb-advanced-options-toggler">
        <i class="glyphicon glyphicon-cog"></i> <span><?php _e( 'Advanced', 'pwebcontact' ); ?></span> <i class="glyphicon glyphicon-chevron-down"></i>
    </button>
    <div class="pweb-advanced-options-content">
        <?php echo $this->_get_field(array(
            'type' => 'select',
            'name' => 'effect_transition',
            'label' => 'Effect transition',
            'default' => -1,
            'parent' => array('layout_type_slidebox'),
            'options' => array(
                array('value' => -1, 'name' => '- Use default -'),
                array('value' => 'linear'),
                array('value' => 'swing'),
                array('value' => -2, 'name' => '- jQuery UI Effects -', 'disabled' => true),
                array('value' => 'easeInQuad'),
                array('value' => 'easeOutQuad'),
                array('value' => 'easeInOutQuad'),
                array('value' => 'easeInCubic'),
                array('value' => 'easeOutCubic'),
                array('value' => 'easeInOutCubic'),
                array('value' => 'easeInQuart'),
                array('value' => 'easeOutQuart'),
                array('value' => 'easeInOutQuart'),
                array('value' => 'easeInQuint'),
                array('value' => 'easeOutQuint'),
                array('value' => 'easeInOutQuint'),
                array('value' => 'easeInExpo'),
                array('value' => 'easeOutExpo'),
                array('value' => 'easeInOutExpo'),
                array('value' => 'easeInSine'),
                array('value' => 'easeOutSine'),
                array('value' => 'easeInOutSine'),
                array('value' => 'easeInCirc'),
                array('value' => 'easeOutCirc'),
                array('value' => 'easeInOutCirc'),
                array('value' => 'easeInElastic'),
                array('value' => 'easeOutElastic'),
                array('value' => 'easeInOutElastic'),
                array('value' => 'easeInBack'),
                array('value' => 'easeOutBack'),
                array('value' => 'easeInOutBack'),
                array('value' => 'easeInBounce'),
                array('value' => 'easeOutBounce'),
                array('value' => 'easeInOutBounce')
            )
        )); ?>

        <?php echo $this->_get_field(array(
            'type' => 'text',
            'name' => 'effect_duration',
            'label' => 'Effect duration [ms]',
            'tooltip' => 'Set time of effect duration, e.g. 1000 (1s delay).',
            'default' => 400,
            'class' => 'pweb-filter-int pweb-input-mini',
            'parent' => array('layout_type_slidebox', 'layout_type_modal', 'layout_type_accordion')
        )); ?>
        
    </div>
</div>