<?php
/**
 * @version 2.0.4
 * @package Perfect Easy & Powerful Contact Form
 * @copyright © 2014 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
 * @license GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @author Piotr Moćko
 */

// TODO display all fields email templates

// No direct access
function_exists('add_action') or die;

?>

<h3 class="pweb-steps">
    <?php printf(__('Step %d of %d', 'pwebcontact'), 4, 4); ?>
    -
    <?php _e('Choose your answer scheme', 'pwebcontact'); ?>

    <?php echo $this->_get_field_control(array(
        'type' => 'select',
        'group' => 'load',
        'name' => 'email_scheme',
        'options' => array(
            array(
                'name' => '- Select -',
                'value' => ''
            ),
            array(
                'name' => 'Boring',
                'value' => '{"tmpl":"boring","msg":"'.__('Your message has been successfully submitted. Thank you.', 'pwebcontact').'"}'
            ),
            array(
                'name' => 'Fancy',
                'value' => '{"tmpl":"fancy","msg":"'.__('Thanks! Message from you is now on the way to our mailbox. We can’t wait to read it :)', 'pwebcontact').'"}'
            ),
            array(
                'name' => 'Superformal',
                'value' => '{"tmpl":"superformal","msg":"'.__('The message of yours has been consigned.', 'pwebcontact').'"}'
            )
        )
    )); ?>
    
    <?php _e('or define your own', 'pwebcontact'); ?>
    
    <button class="button button-primary pweb-next-tab-button" type="button">
        <?php _e( 'Next', 'pwebcontact' ); ?> <i class="glyphicon glyphicon-chevron-right"></i>
    </button>
</h3>

<?php echo $this->_get_field(array(
    'type' => 'text',
    'name' => 'msg_success',
    'label' => 'Enter message which will appear after successful sending email by contact form',
    'tooltip' => 'If you are using tickets then you can display it in your custom message by adding %s. It will be replaced with defined ticket format. Do not use characters: &#x22; &#x5c;',
    'class' => 'widefat'
)); ?>

<div class="pweb-field pweb-field-text">
    <?php echo $this->_get_label(array(
        'name' => 'email_to',
        'label' => 'Enter one or more Administrator emails to which message should be sent to',
    )); ?>
    <div class="pweb-field-control">
        <?php echo $this->_get_field_control(array(
            'type' => 'text',
            'name' => 'email_to',
            'class' => 'pweb-filter-emails pweb-input-large'
        )); ?>
        
        <?php echo __('or/and', 'pwebcontact') .' '. $this->_get_field_control(array(
            'type' => 'wp_user',
            'name' => 'email_cms_user',
            'label' => 'or choose WordPress Administrator to whom message will be sent to',
            'tooltip' => 'Enable this option to send email to selected WordPress Administrator. Do not use this option if Administrator has the same email address as in above field!'
        )); ?>
    </div>
</div>

<hr>

<div class="pweb-field">
    <h3><?php _e( 'Legend', 'pwebcontact' ); ?></h3>
    <p><strong><?php _e( 'Administrator', 'pwebcontact' ); ?></strong> - <?php _e( 'the owner of website', 'pwebcontact' ); ?></p>
    <p><strong><?php _e( 'User', 'pwebcontact' ); ?></strong> - <?php _e( 'a visitor of webiste who wants to contact with website owner', 'pwebcontact' ); ?></p>
</div>


<div class="pweb-field pweb-field-textarea">
    <h3><?php _e( 'Email message to User', 'pwebcontact' ); ?></h3>
    
    <?php echo $this->_get_field(array(
        'type' => 'radio',
        'name' => 'email_copy',
        'label' => 'Send message to User',
        'default' => 2,
        'class' => 'pweb-radio-group',
        'options' => array(
            array(
                'value' => 0,
                'name' => 'No'
            ),
            array(
                'value' => 2,
                'name' => 'Always'
            ),
            array(
                'value' => 1,
                'name' => 'When field &bdquo;Send copy to yourself&rdquo; is checked'
            )
        )
    )); ?>
    
    <?php echo $this->_get_label(array(
        'name' => 'email_user_tmpl',
        'label' => 'Enter message which will be sent to User as copy'
    )); ?>
    <div class="pweb-field-control">
        <?php echo $this->_get_field_control(array(
            'type' => 'textarea',
            'name' => 'email_user_tmpl',
            'desc' => 'Remeber to create field type of: Send copy to yourself, to use this option.',
            'class' => 'widefat',
            'attributes' => array(
                'rows' => 10,
                'cols' => 50
            )
        )); ?>
        <div><a href="#" class="pweb-email-tmpl-vars"><?php _e('Show email variables', 'pwebcontact'); ?></a></div>
        <div class="pweb-field-desc"><?php _e('Remeber to create field type of: Send copy to yourself, to use this option.', 'pwebcontact'); ?></div>
    </div>
    <div class="pweb-field-control">
        <?php echo $this->_get_label(array(
            'name' => 'email_user_tmpl_format',
            'label' => 'Select format'
        )); ?>
        
        <?php echo $this->_get_field_control(array(
            'type' => 'radio',
            'name' => 'email_user_tmpl_format',
            'default' => 1,
            'class' => 'pweb-radio-group',
            'options' => array(
                array(
                    'value' => 1,
                    'name' => 'Text'
                ),
                array(
                    'value' => 2,
                    'name' => 'HTML'
                )
            )
        )); ?>
        
        <?php echo $this->_get_label(array(
            'name' => 'email_user_tmpl_list',
            'label' => 'Load template'
        )); ?>
        
        <?php echo $this->_get_field_control(array(
            'type' => 'filelist',
            'name' => 'email_user_tmpl_list',
            'filter' => '\.html$',
            'directory' => 'media/email_tmpl',
            'strip_ext' => true,
            'class' => 'pweb-load-email-tmpl',
            'attributes' => array(
                'data-action' => admin_url( 'admin.php?page=pwebcontact&task=load_email&ajax=1&_wpnonce='. wp_create_nonce('load-email') )
            ),
            'options' => array(
                array(
                    'value' => '',
                    'name' => '- Select template -'
                )
            )
        )); ?>
        
        <span class="pweb-field-desc"><?php _e( 'If you have changed format of email then load template again', 'pwebcontact' ); ?></span>
    </div>
</div>

<div class="pweb-advanced-options">
    <button type="button" class="button pweb-advanced-options-toggler">
        <i class="glyphicon glyphicon-cog"></i> <span><?php _e( 'Advanced', 'pwebcontact' ); ?></span> <i class="glyphicon glyphicon-chevron-down"></i>
    </button>
    <div class="pweb-advanced-options-content">
        
        
        <div class="pweb-field pweb-field-textarea">
            <h3><?php _e( 'Email message to Administrator', 'pwebcontact' ); ?></h3>
            <?php echo $this->_get_label(array(
                'name' => 'email_admin_tmpl',
                'label' => 'Enter message which will be sent to Administrator'
            )); ?>
            <div class="pweb-field-control">
                <?php echo $this->_get_field_control(array(
                    'type' => 'textarea',
                    'name' => 'email_admin_tmpl',
                    'class' => 'widefat',
                    'attributes' => array(
                        'rows' => 10,
                        'cols' => 50
                    ),
                    'default' => '{fields}

{lang:Ticket}: {ticket}
{lang:Page title}: {title}
{lang:Page URL}: {url}
{lang:IP}: {ip_address}
{lang:Browser}: {browser}
{lang:Operating system}: {os}
{lang:Screen resolution}: {screen_resolution}

{files}'
                )); ?>
                <div><a href="#" class="pweb-email-tmpl-vars"><?php _e('Show email variables', 'pwebcontact'); ?></a></div>
            </div>
            <div class="pweb-field-control">
                <?php echo $this->_get_label(array(
                    'name' => 'email_admin_tmpl_format',
                    'label' => 'Select format'
                )); ?>

                <?php echo $this->_get_field_control(array(
                    'type' => 'radio',
                    'name' => 'email_admin_tmpl_format',
                    'default' => 1,
                    'class' => 'pweb-radio-group',
                    'options' => array(
                        array(
                            'value' => 1,
                            'name' => 'Text'
                        ),
                        array(
                            'value' => 2,
                            'name' => 'HTML'
                        )
                    )
                )); ?>

                <?php echo $this->_get_label(array(
                    'name' => 'email_admin_tmpl_list',
                    'label' => 'Load template'
                )); ?>

                <?php echo $this->_get_field_control(array(
                    'type' => 'filelist',
                    'name' => 'email_admin_tmpl_list',
                    'filter' => '\.html$',
                    'directory' => 'media/email_tmpl',
                    'strip_ext' => true,
                    'class' => 'pweb-load-email-tmpl',
                    'attributes' => array(
                        'data-action' => admin_url( 'admin.php?page=pwebcontact&task=load_email&ajax=1&_wpnonce='. wp_create_nonce('load-email') )
                    ),
                    'options' => array(
                        array(
                            'value' => '',
                            'name' => '- Select template -'
                        )
                    )
                )); ?>
                
                <span class="pweb-field-desc"><?php _e( 'If you have changed format of email then load template again', 'pwebcontact' ); ?></span>
            </div>
        </div>
        
        <hr>

        <div class="pweb-clearfix">
            <h3><?php _e('Email subject', 'pwebcontact'); ?></h3>
            
            <div class="pweb-width-50">
                <?php echo $this->_get_field(array(
                    'type' => 'text',
                    'name' => 'email_subject',
                    'label' => 'Subject of email',
                    'tooltip' => 'If you are using tickets then by default it will be placed at the beginning of subject. You can display ticket in another place by adding %s. It will be replaced with defined ticket format.',
                    'default' => 'Message sent from ',
                    'class' => 'pweb-input-large'
                )); ?>
            </div>
            <div class="pweb-width-50">
                <?php echo $this->_get_field(array(
                    'type' => 'radio',
                    'name' => 'email_subject_sfx',
                    'label' => 'Email subject suffix',
                    'tooltip' => 'Adds suffix at the end of email subject',
                    'default' => 1,
                    'class' => 'pweb-radio-group',
                    'options' => array(
                        array(
                            'value' => 0,
                            'name' => 'No'
                        ),
                        array(
                            'value' => 1,
                            'name' => 'Site name'
                        ),
                        array(
                            'value' => 2,
                            'name' => 'Page title'
                        )
                    )
                )); ?>
            </div>
        </div>
        
        

        <div class="pweb-clearfix">
            <h3><?php _e('Tickets', 'pwebcontact'); ?></h3>
            
            <div class="pweb-width-50">
                <?php echo $this->_get_field(array(
                    'type' => 'radio',
                    'name' => 'ticket_enable',
                    'label' => 'Enable tickets',
                    'tooltip' => 'Enable unique ID in email subject which you can use as help desk tickets. ID is generated in following date &amp; time based pattern `YYYYMMDDHHMMSS` or as integer counter. This feature will help you keep each client in separate thread in GMail or other inbox.',
                    'default' => 0,
                    'class' => 'pweb-radio-group',
                    'options' => array(
                        array(
                            'value' => 0,
                            'name' => 'No'
                        ),
                        array(
                            'value' => 1,
                            'name' => 'Date-time',
                            'is_parent' => true
                        ),
                        array(
                            'value' => 2,
                            'name' => 'Number counter',
                            'is_parent' => true
                        )
                    )
                )); ?>
            </div>
            <div class="pweb-width-50">
                <?php echo $this->_get_field(array(
                    'type' => 'text',
                    'name' => 'ticket_format',
                    'label' => 'Ticket format',
                    'tooltip' => 'Type pattern of your ticket, e.g. [#%s].<br>Use string %s for date-time ticket or %06d for number counter.',
                    'parent' => array('ticket_enable_1', 'ticket_enable_2')
                )); ?>
            </div>
        </div>

        <hr>

        <div class="pweb-clearfix">
            <h3><?php _e('Email settings', 'pwebcontact'); ?></h3>
            
            <div class="pweb-width-50">
                <?php 
                $host = $_SERVER['SERVER_NAME'];
                $isLocalhsot = ($host == 'localhost' OR $host == '127.0.0.1');
                $domain = str_replace('www.', '', $host);
                if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
                    $domain = $regs['domain'];
                }

                echo $this->_get_field(array(
                    'type' => 'text',
                    'name' => 'email_from',
                    'label' => 'Sender email',
                    'desc' => sprintf(__('Leave blank to use email: &bdquo;%s&rdquo; set in %s.', 'pwebcontact'), $this->_get_param('email_from', get_bloginfo('admin_email'), 'settings'), 
                                        '<a href="'.admin_url('admin.php?page=pwebcontact&task=settings').'" target="_blank">'.__('Contact Form Settings', 'pwebcontact').'</a>')
                                . ($isLocalhsot ? '' : sprintf(__('Sender email should be in the same domain as your website, example: %s'), 'info@'.$domain)),
                    'class' => 'pweb-filter-email'
                )); ?>

                <?php echo $this->_get_field(array(
                    'type' => 'text',
                    'name' => 'email_from_name',
                    'label' => 'Sender name',
                    'desc' => sprintf(__('Leave blank to use name: &bdquo;%s&rdquo; set in %s.', 'pwebcontact'), $this->_get_param('email_from_name', get_bloginfo('name'), 'settings'), 
                                        '<a href="'.admin_url('admin.php?page=pwebcontact&task=settings').'" target="_blank">'.__('Contact Form Settings', 'pwebcontact').'</a>')
                )); ?>
            </div>
            <div class="pweb-width-50">
                <?php echo $this->_get_field(array(
                    'type' => 'text',
                    'name' => 'email_replyto',
                    'label' => 'Reply to email',
                    'tooltip' => 'Enter only one email. Leave blank if you want User to reply to sender',
                    'class' => 'pweb-filter-email'
                )); ?>

                <?php echo $this->_get_field(array(
                    'type' => 'text',
                    'name' => 'email_replyto_name',
                    'label' => 'Reply to name',
                    'tooltip' => 'Enter `Reply to name` if you have set `Reply to email`'
                )); ?>

                <?php echo $this->_get_field(array(
                    'type' => 'text',
                    'name' => 'email_bcc',
                    'label' => 'BCC emails',
                    'tooltip' => 'Add blind carbon copy recipients to the email. To add multiple recipients separate each email with , (coma). Do not add any email address which was already set in another field!',
                    'class' => 'pweb-filter-emails pweb-input-large'
                )); ?>
            </div>
        </div>

        <hr>

        <div class="pweb-clearfix">
            <h3><?php _e('System message', 'pwebcontact'); ?></h3>
            
            <div class="pweb-width-50">
                <?php echo $this->_get_field(array(
                    'type' => 'radio',
                    'name' => 'msg_position',
                    'label' => 'System message position',
                    'tooltip' => 'Display message before or after form, next to send button or in popup layer',
                    'default' => 'after',
                    'options' => array(
                        array(
                            'value' => 'before',
                            'name' => 'Before form'
                        ),
                        array(
                            'value' => 'after',
                            'name' => 'After form'
                        ),
                        array(
                            'value' => 'button',
                            'name' => 'Next to Send buton'
                        ),
                        array(
                            'value' => 'popup',
                            'name' => 'In popup',
                            'is_parent' => true
                        )
                    )
                )); ?>
                
                <?php echo $this->_get_field(array(
                    'type' => 'text',
                    'name' => 'msg_close_delay',
                    'label' => 'Popup message close delay [s]',
                    'tooltip' => 'Set 0 to disable auto-close of popup message',
                    'default' => 10,
                    'class' => 'pweb-filter-int pweb-input-mini',
                    'parent' => array('msg_position_popup')
                )); ?>
            </div>
            <div class="pweb-width-50">
                <?php echo $this->_get_field(array(
                    'type' => 'color',
                    'name' => 'msg_success_color',
                    'label' => 'Success message color',
                    'tooltip' => 'Select custom color of success message',
                    'default' => '#009e0a'
                )); ?>

                <?php echo $this->_get_field(array(
                    'type' => 'color',
                    'name' => 'msg_error_color',
                    'label' => 'Error message color',
                    'tooltip' => 'Select custom color of error message',
                    'default' => '#aa0000'
                )); ?>
            </div>
        </div>

        <hr>

        <div class="pweb-clearfix">
            <h3><?php _e('Others', 'pwebcontact'); ?></h3>
            
            <div class="pweb-width-50">
                <?php echo $this->_get_field(array(
                    'type' => 'radio',
                    'name' => 'reset_form',
                    'label' => 'Reset form',
                    'tooltip' => 'Reset all data filled in by User after email has been successfully sent. Success message will stay.',
                    'default' => 1,
                    'class' => 'pweb-radio-group-vertical',
                    'options' => array(
                        array(
                            'value' => 0,
                            'name' => 'No'
                        ),
                        array(
                            'value' => 1,
                            'name' => 'After successfully sent'
                        ),
                        array(
                            'value' => 2,
                            'name' => 'After closing successfully sent form'
                        ),
                        array(
                            'value' => 3,
                            'name' => 'With reset button',
                            'is_parent' => true
                        )
                    )
                )); ?>

                <?php echo $this->_get_field(array(
                    'type' => 'text',
                    'name' => 'button_reset',
                    'label' => 'Reset button label',
                    'parent' => array('reset_form_3')
                )); ?>
            </div>
            <div class="pweb-width-50">
                <?php echo $this->_get_field(array(
                    'type' => 'radio',
                    'name' => 'tooltips_validation',
                    'label' => 'Show tooltips on validation error',
                    'default' => 1,
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
                            'is_parent' => true
                        )
                    )
                )); ?>
                
                <?php echo $this->_get_field(array(
                    'type' => 'radio',
                    'name' => 'tooltips_focus',
                    'label' => 'Show tooltips on field focus',
                    'default' => 1,
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
                            'is_parent' => true
                        )
                    )
                )); ?>
                
                <?php echo $this->_get_field(array(
                    'type' => 'radio',
                    'name' => 'user_data',
                    'label' => 'Auto fill in logged User data',
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
                        ),
                        array(
                            'value' => 2,
                            'name' => 'Yes and hide fields with User data'
                        )
                    )
                )); ?>
            </div>
        </div>

        <hr>

        <div class="pweb-clearfix">
            <div class="pweb-width-50">
                <?php echo $this->_get_field(array(
                    'type' => 'text_button',
                    'name' => 'adwords_url',
                    'label' => 'Google AdWords Conversion Tracker - image URL',
                    'header' => 'Tracking',
                    'tooltip' => 'Paste URL of image from generated tracking script or you can use <em>Paste</em> button to extract this link from conversion tracking script.',
                    'button' => 'Paste',
                    'class' => 'pweb-input-xlarge'
                )); ?>

                <?php echo $this->_get_field(array(
                    'type' => 'text_button',
                    'name' => 'adcenter_url',
                    'label' => 'Microsoft adCenter Conversion Tracker - MSTag iframe URL',
                    'tooltip' => 'Paste URL of iframe from generated tracking script or you can use <em>Paste</em> button to extract this link from conversion tracking script.',
                    'button' => 'Paste',
                    'class' => 'pweb-input-xlarge'
                )); ?>
            </div>
            <div class="pweb-width-50">
                <?php echo $this->_get_field(array(
                    'type' => 'radio',
                    'name' => 'redirect',
                    'label' => 'Redirect after send',
                    'header' => 'Redirect',
                    'default' => 0,
                    'class' => 'pweb-radio-group',
                    'options' => array(
                        array(
                            'value' => 0,
                            'name' => 'No'
                        ),
                        array(
                            'value' => 1,
                            'name' => 'Yes',
                            'is_parent' => true
                        )
                    )
                )); ?>
                
                <?php echo $this->_get_field(array(
                    'type' => 'text',
                    'name' => 'redirect_url',
                    'label' => 'Redirect URL',
                    'tooltip' => 'Enter URL for redirect to thank you page after successful email sent. Do not encode ampersands &amp;',
                    'class' => 'pweb-filter-url pweb-input-xlarge',
                    'parent' => array('redirect_1')
                )); ?>

                <?php echo $this->_get_field(array(
                    'type' => 'text',
                    'name' => 'redirect_delay',
                    'label' => 'Redirect delay [s]',
                    'tooltip' => 'Enter delay time in seconds before redirect.',
                    'default' => 5,
                    'class' => 'pweb-filter-int pweb-input-mini',
                    'parent' => array('redirect_1')
                )); ?>
            </div>
        </div>

        <hr>

        <div class="pweb-field pweb-desc">
            <h3><?php _e('Google Analytics Tracking', 'pwebcontact'); ?></h3>
            <p>
                <?php _e('Copy one of following codes to below field', 'pwebcontact'); ?> 
                <strong><?php _e('JavaScript on mail success event', 'pwebcontact'); ?></strong>
            </p>
            
            <div class="pweb-advanced-options">
                <a href="#" class="pweb-advanced-options-toggler pweb-advanced-options-open">
                    <span><?php _e( 'Universal', 'pwebcontact' ); ?></span> <i class="glyphicon glyphicon-chevron-down"></i>
                </a>
                <div class="pweb-advanced-options-content">
                    <div class="pweb-field-control">
                        <p>
                            <?php _e('Pageview', 'pwebcontact'); ?>: <code>ga('send', 'pageview', document.location.href+'/email-sent');</code><br>
                            <?php _e('Event', 'pwebcontact'); ?>: <code>ga('send', 'event', 'Contact', 'E-mail sent', document.location.href);</code><br>
                            <?php _e('Event with ticket', 'pwebcontact'); ?>: <code>ga('send', 'event', 'Contact', 'E-mail sent', data.ticket);</code>
                        </p>
                        <div class="pweb-alert pweb-alert-warning">
                            <?php _e('You might need to change in above code function name `ga` to custom, if your Universal Analytics code does not use default function.', 'pwebcontact'); ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="pweb-advanced-options">
                <a href="#" class="pweb-advanced-options-toggler">
                    <span><?php _e( 'Asynchronous', 'pwebcontact' ); ?></span> <i class="glyphicon glyphicon-chevron-down"></i>
                </a>
                <div class="pweb-advanced-options-content">
                    <div class="pweb-field-control">
                        <p>
                            <?php _e('Pageview', 'pwebcontact'); ?>: <code>_gaq.push(['_trackPageview', document.location.href+'/email-sent']);</code><br>
                            <?php _e('Event', 'pwebcontact'); ?>: <code>_gaq.push(['_trackEvent', 'Contact', 'E-mail sent', document.location.href]);</code><br>
                            <?php _e('Event with ticket', 'pwebcontact'); ?>: <code>_gaq.push(['_trackEvent', 'Contact', 'E-mail sent', data.ticket]);</code>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="pweb-advanced-options">
                <a href="#" class="pweb-advanced-options-toggler">
                    <span><?php _e( 'Traditional', 'pwebcontact' ); ?></span> <i class="glyphicon glyphicon-chevron-down"></i>
                </a>
                <div class="pweb-advanced-options-content">
                    <div class="pweb-field-control">
                        <p>
                            <?php _e('Pageview', 'pwebcontact'); ?>: <code>pageTracker._trackPageview(document.location.href+'/email-sent');</code><br>
                            <?php _e('Event', 'pwebcontact'); ?>: <code>pageTracker._trackEvent('Contact', 'E-mail sent', document.location.href);</code><br>
                            <?php _e('Event with ticket', 'pwebcontact'); ?>: <code>pageTracker._trackEvent('Contact', 'E-mail sent', data.ticket);</code>
                        </p>
                    </div> 
                </div>
            </div>
        </div>
        
        
        <?php echo $this->_get_field(array(
            'type' => 'textarea',
            'name' => 'oncomplete',
            'label' => 'JavaScript on mail success event',
            'header' => 'JavaScript events',
            'tooltip' => 'JavaScript code called after successful send of email. This event has one argument `data` type of object with property `ticket`. Do not insert any HTML tags!',
            'class' => 'pweb-filter-javascript widefat',
            'attributes' => array(
                'rows' => 5,
                'cols' => 50
            )
        )); ?>
        
        <?php echo $this->_get_field(array(
            'type' => 'textarea',
            'name' => 'onerror',
            'label' => 'JavaScript on mail error event',
            'tooltip' => 'JavaScript code called after mail send error or invalid captcha. This event has one argument `data` type of object with possible property `invalid`. Do not insert any HTML tags!',
            'class' => 'pweb-filter-javascript widefat',
            'attributes' => array(
                'rows' => 5,
                'cols' => 50
            )
        )); ?>
        
        
    </div>
</div>

<div id="pweb-dialog-email-load" title="<?php esc_attr_e( 'Load email template', 'pwebcontact' ); ?>" style="display:none">
    <p><?php _e( 'Current content of email message will be replaced with selected template!', 'pwebcontact' ); ?></p>
</div>
<div id="pweb-dialog-email-scheme-load" title="<?php esc_attr_e( 'Load email scheme', 'pwebcontact' ); ?>" style="display:none">
    <p><?php _e( 'Current content of message which will appear after successful sending and message which will be sent to User as copy will be replaced with selected scheme!', 'pwebcontact' ); ?></p>
</div>

<div id="pweb-email-tmpl-vars" style="display:none">
    <p><?php _e('Use following syntax in email templates to display data collected by form.', 'pwebcontact'); ?></p>
    <ul>
        <li><code>{lang:Word}</code> - <?php esc_html_e('displays translation of "Word" from language file', 'pwebcontact'); ?></li>
        <li><code>{fields}</code> - <?php esc_html_e('displays all fields, each "Label: Value" pair in new line', 'pwebcontact'); ?></li>
        <li><code>{field_alias.label}</code> - <?php printf(esc_html__('displays field label, will be translated if present in language file, replace %s with alias of field', 'pwebcontact'), '<code>field_alias</code>'); ?></li>
        <li><code>{field_alias.value}</code> - <?php printf(esc_html__('displays field value, replace %s with alias of field', 'pwebcontact'), '<code>field_alias</code>'); ?></li>
        <li><code>{name}</code> - <?php esc_html_e('User name', 'pwebcontact'); ?></li>
        <li><code>{email}</code> - <?php esc_html_e('User email', 'pwebcontact'); ?></li>
        <li><code>{username}</code> - <?php esc_html_e('WordPress Username if logged in', 'pwebcontact'); ?></li>
        <li><code>{url}</code> - <?php esc_html_e('page URL', 'pwebcontact'); ?></li>
        <li><code>{title}</code> - <?php esc_html_e('title of page', 'pwebcontact'); ?></li>
        <li><code>{site_name}</code> - <?php esc_html_e('site name', 'pwebcontact'); ?></li>
        <li><code>{ip_address}</code> - <?php esc_html_e('User IP address', 'pwebcontact'); ?>
            <?php echo $this->_display_badge_pro(); ?>
        </li>
        <li><code>{browser}</code> - <?php esc_html_e('User browser name', 'pwebcontact'); ?>. 
            <?php _e('Requires 3rd-part plugin', 'pwebcontact'); ?> 
            - <a href="http://wordpress.org/plugins/<?php 
                $plugin = 'php-browser-detection';
                $plugin_file = $plugin.'/'.$plugin.'.php';
                echo $plugin; ?>" target="_blank"><?php _e('Details'); ?></a> 
            -
            <?php if (!is_file(WP_PLUGIN_DIR.'/'.$plugin_file)) : ?>
                <a href="<?php echo wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin='.$plugin), 'install-plugin_'.$plugin); ?>" target="_blank"><?php _e('Install Now'); ?></a>
            <?php elseif (is_plugin_inactive($plugin_file)) : ?>
                <a href="<?php  echo wp_nonce_url(self_admin_url('plugins.php?action=activate&plugin='.$plugin_file), 'activate-plugin_'.$plugin_file); ?>" target="_blank"><?php _e('Activate'); ?></a>
            <?php else : ?>
                <strong class="pweb-text-success"><i class="glyphicon glyphicon-ok-sign"></i> <?php _e('Installed'); ?></strong>
            <?php endif; ?>
            <?php echo $this->_display_badge_pro(); ?>
        </li>
        <li><code>{os}</code> - <?php esc_html_e('User operating system name', 'pwebcontact'); ?>
            <?php echo $this->_display_badge_pro(); ?>
        </li>
        <li><code>{screen_resolution}</code> - <?php esc_html_e('User screen resolution', 'pwebcontact'); ?>
            <?php echo $this->_display_badge_pro(); ?>
        </li>
        <li><code>{mailto_name}</code> - <?php esc_html_e('name of recipient selected by User from "Mail to list" field', 'pwebcontact'); ?>
            <?php echo $this->_display_badge_pro(); ?>
        </li>
        <li><code>{ticket}</code> - <?php esc_html_e('formatted ticket', 'pwebcontact'); ?>
            <?php echo $this->_display_badge_pro(); ?>
        </li>
        <li><code>{files}</code> - <?php esc_html_e('links to uploaded files if "Attachment type" set to: "Links to files"', 'pwebcontact'); ?>
            <?php echo $this->_display_badge_pro(); ?>
        </li>
    </ul>
</div>