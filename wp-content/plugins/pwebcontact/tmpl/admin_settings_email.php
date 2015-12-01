<?php
/**
 * @version 1.0.0
 * @package Perfect Easy & Powerful Contact Form
 * @copyright © 2014 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
 * @license GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @author Piotr Moćko
 */

// No direct access
function_exists('add_action') or die;

$host = $_SERVER['SERVER_NAME'];
$isLocalhsot = ($host == 'localhost' OR $host == '127.0.0.1');
$domain = str_replace('www.', '', $host);
if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
    $domain = $regs['domain'];
}

$php_mail_enabled = (function_exists('mail') AND is_callable('mail'));

?>

<?php echo $this->_get_field(array(
    'type' => 'text',
    'name' => 'email_from',
    'group' => 'settings',
    'label' => 'Sender email',
    'header' => 'Email settings',
    'desc' => sprintf(__('Leave blank to use email: &bdquo;%s&rdquo; set in %s.', 'pwebcontact'), get_bloginfo('admin_email'), 
                        '<a href="'.admin_url('options-general.php').'" target="_blank">'.__('WordPress General Settings', 'pwebcontact').'</a>') 
                . ($isLocalhsot ? '' : ' '.sprintf(__('Sender email should be in the same domain as your website, example: %s'), 'info@'.$domain)),
    'class' => 'pweb-filter-email'
)); ?>

<?php echo $this->_get_field(array(
    'type' => 'text',
    'name' => 'email_from_name',
    'group' => 'settings',
    'label' => 'Sender name',
    'desc' => sprintf(__('Leave blank to use Site Title: &bdquo;%s&rdquo; set in %s.', 'pwebcontact'), get_bloginfo('name'),
                        '<a href="'.admin_url('options-general.php').'" target="_blank">'.__('WordPress General Settings', 'pwebcontact').'</a>')
)); ?>

<?php echo $this->_get_field(array(
    'type' => 'radio',
    'name' => 'server_sender',
    'group' => 'settings',
    'label' => 'Send from one domain',
    'tooltip' => 'Send all emails from one domain. Administrator will receive email from address set in `Sender email` with reply to email address completed by the User. It is useful for some servers which do not allow to send emails from another domains.',
    'default' => 1,
    'class' => 'pweb-radio-group',
    'options' => array(
        array(
            'value' => 0,
            'name' => 'No'
        ),
        array(
            'value' => 1,
            'name' => 'Yes (recomended)'
        )
    )
)); ?>

<?php echo $this->_get_field(array(
    'type' => 'radio',
    'name' => 'mailer',
    'group' => 'settings',
    'label' => 'Mailer type',
    'default' => 'inherit',
    'class' => 'pweb-radio-group',
    'options' => array(
        array(
            'value' => 'mail',
            'name' => 'PHP mail function',
            'disabled' => !$php_mail_enabled
        ),
        array(
            'value' => 'smtp',
            'name' => 'SMTP',
            'is_parent' => true
        ),
        array(
            'value' => 'inherit',
            'name' => 'Inherit WordPress settings'
        )
    )
)); ?>

<?php echo $this->_get_field(array(
    'type' => 'text',
    'name' => 'smtp_username',
    'group' => 'settings',
    'label' => 'SMTP Username',
    'desc' => $isLocalhsot ? '' : sprintf(__('Email account used for authentication should be in the same domain as your website, example: %s'), 'info@'.$domain),
    'parent' => 'mailer_smtp'
)); ?>

<?php echo $this->_get_field(array(
    'type' => 'password',
    'name' => 'smtp_password',
    'group' => 'settings',
    'label' => 'SMTP Password',
    'parent' => 'mailer_smtp'
)); ?>

<?php echo $this->_get_field(array(
    'type' => 'text',
    'name' => 'smtp_host',
    'group' => 'settings',
    'label' => 'SMTP Host',
    'desc' => $isLocalhsot ? '' : sprintf(__('Host used for SMTP should be in the same domain as your website, example: %s'), 'mail.'.$domain),
    'parent' => 'mailer_smtp'
)); ?>

<?php echo $this->_get_field(array(
    'type' => 'radio',
    'name' => 'smtp_secure',
    'group' => 'settings',
    'label' => 'SMTP Security',
    'tooltip' => 'Select the security model that your SMTP server uses.',
    'default' => 'none',
    'class' => 'pweb-radio-group',
    'parent' => 'mailer_smtp',
    'options' => array(
        array(
            'value' => 'none',
            'name' => 'None'
        ),
        array(
            'value' => 'ssl',
            'name' => 'SSL'
        ),
        array(
            'value' => 'tls',
            'name' => 'TLS'
        )
    )
)); ?>

<?php echo $this->_get_field(array(
    'type' => 'text',
    'name' => 'smtp_port',
    'group' => 'settings',
    'label' => 'SMTP Port',
    'tooltip' => 'Enter the port number of your SMTP server. Use 25 for most unsecured servers and 465 for most secure servers.',
    'default' => 25,
    'parent' => 'mailer_smtp',
    'class' => 'pweb-input-mini pweb-filter-int'
)); ?>