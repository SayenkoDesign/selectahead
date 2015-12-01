<?php
/**
 * @version 2.1.0
 * @package Perfect Easy & Powerful Contact Form
 * @copyright © 2015 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
 * @license GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @author Piotr Moćko
 */

// No direct access
function_exists('add_action') or die;

?>

<?php echo $this->_get_field(array(
	'type' => 'radio',
	'name' => 'feed',
	'group' => 'settings',
	'label' => 'Updates feed',
	'header' => 'Updates',
	'tooltip' => 'Display news and special offers from Perfect-Web.co website in administration panel of this extension.',
	'default' => 1,
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
    'type' => 'text',
    'name' => 'dlid',
    'group' => 'settings',
    'readonly' => true,
    'desc' => sprintf(__('Enter Download ID which you can find at %s website, to get automatical updates if you have active PRO subscription. You have to first reinstall plugin to PRO version by yourself.', 'pwebcontact'), '<a href="https://www.perfect-web.co/login" target="_blank">Perfect-Web.co</a>'),
    'label' => 'Download ID'
)); ?>


<?php echo $this->_get_field(array(
    'type' => 'radio',
    'name' => 'force_init',
    'group' => 'settings',
	'header' => 'Advanced settings',
    'label' => 'Force to load CSS and JS at all pages',
    'tooltip' => 'Enable this option only if you are displaying contact form inside content by some AJAX plugin',
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