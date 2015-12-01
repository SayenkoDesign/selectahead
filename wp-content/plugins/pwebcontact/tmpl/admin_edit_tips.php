<?php
/**
 * @version 2.0.1
 * @package Perfect Easy & Powerful Contact Form
 * @copyright © 2014 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
 * @license GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @author Piotr Moćko
 */

// No direct access
function_exists('add_action') or die;
?>
<div class="pweb-alert pweb-alert-warning">
    <?php _e( 'All of these tips depend on Contact Form ID and can be applied to this Contact Form instance only.', 'pwebcontact' ); ?>
</div>

<div class="pweb-field pweb-desc">
    <h3>
        <?php _e( 'Open contact form with menu item', 'pwebcontact' ); ?>
        <?php echo $this->_display_badge_pro(); ?>
    </h3>
    <p>
        <?php _e( 'To open contact form with menu item, create menu item type of Links and as URL set', 'pwebcontact' ); ?><br>
        <code>#pwebcontact<?php echo $this->id; ?>_toggler</code> <a href="<?php echo admin_url('nav-menus.php'); ?>" target="_blank"><?php _e( 'Go to Menus', 'pwebcontact' ); ?></a><br>
        <span class="pweb-text-danger"><?php _e( 'Remember that menu item would open contact form only if it has been loaded.', 'pwebcontact' ); ?></span><br>
        <?php esc_html_e( 'If you do not want to display Toggler Tab or Button then select `Location & Effects` > `Form before opening` > `Hidden before opening`.', 'pwebcontact' ); ?>
    </p>
</div>

<hr>

<div class="pweb-field pweb-desc">
    <h3><?php _e( 'Open contact form with custom HTML', 'pwebcontact' ); ?></h3>
    <p>
        <?php _e( 'Open by link', 'pwebcontact' ); ?><br>
        <code>&lt;a href="#" class="pwebcontact<?php echo $this->id; ?>_toggler"&gt;Click here&lt;/a&gt;</code>
    </p>
    <p>
        <?php _e( 'Open by image', 'pwebcontact' ); ?><br>
		<code>&lt;a href="#" class="pwebcontact<?php echo $this->id; ?>_toggler"&gt;&lt;img src="..."&gt;&lt;/a&gt;</code>
    </p>
    <p>
        <?php _e( 'Open on page load by URL', 'pwebcontact' ); ?><br>
		<code>#pwebcontact<?php echo $this->id; ?>:open</code>
    </p>
</div>

<hr>

<div class="pweb-field pweb-desc">
    <h3><?php _e( 'Preload fields values', 'pwebcontact' ); ?></h3>
    <p>
        <?php printf(__( 'Separate each field with %s. Separate field alias and values with %s. Alias has to be lower-case. Separate multiple values with %s.', 'pwebcontact' ), '<code>/</code>', '<code>=</code>', '<code>;</code>'); ?><br>
    </p>
    
    <p>
        <?php _e( 'Open page with contact form by URL containing following fragment with hash and preload fields values', 'pwebcontact' ); ?><br>
        <code>#pwebcontact<?php echo $this->id; ?>:name=Tester/message=Testing/checkboxes=Option A;Option B</code>
    </p>
    <p>
		<?php _e( 'Preload fields values and also open contact form on page load', 'pwebcontact' ); ?><br>
        <code>#pwebcontact<?php echo $this->id; ?>:open:name=Tester/message=Testing/checkboxes=Option A;Option B</code>
    </p>
    <p>
		<?php _e( 'Open contact form with link and always preload fields values from data attribute', 'pwebcontact' ); ?>
        <code>data-pwebcontact-fields</code><br>
        <code>&lt;a href="#" class="pwebcontact<?php echo $this->id; ?>_toggler" data-pwebcontact-fields="name=Tester/message=Testing"&gt;Click here&lt;/a&gt;</code>
    </p>
    <p>
		<?php _e( 'Open contact form with link and preload only once fields values from data attribute', 'pwebcontact' ); ?>
        <code>data-pwebcontact-fields-once</code><br>
        <code>&lt;a href="#" class="pwebcontact<?php echo $this->id; ?>_toggler" data-pwebcontact-fields-once="name=Tester/message=Testing"&gt;Click here&lt;/a&gt;</code>
    </p>
    <p>
		<?php _e( 'Preload fields values from JavaScript code', 'pwebcontact' ); ?><br>
        <code>pwebContact<?php echo $this->id; ?>.preloadFields("name=Tester/message=Testing");</code>					
    </p>
</div>

<hr>

<div class="pweb-field pweb-desc">
    <h3><?php _e( 'JavaScript methods', 'pwebcontact' ); ?></h3>
    <p>
        <?php _e( 'Toggle form', 'pwebcontact' ); ?><br>
        <code>pwebContact<?php echo $this->id; ?>.toggleForm();</code>
    </p>
    <p>
        <?php _e( 'Open form', 'pwebcontact' ); ?><br>
		<code>pwebContact<?php echo $this->id; ?>.toggleForm(1);</code>
    </p>
    <p>
        <?php _e( 'Close form', 'pwebcontact' ); ?><br>
		<code>pwebContact<?php echo $this->id; ?>.toggleForm(0);</code>
    </p>
    <p>
        <?php _e( 'Open form with selected 3rd email from List of recipients field', 'pwebcontact' ); ?>
        <?php echo $this->_display_badge_pro(); ?>
        <br>
		<code>pwebContact<?php echo $this->id; ?>.toggleForm(1, 3);</code>
    </p>
</div>