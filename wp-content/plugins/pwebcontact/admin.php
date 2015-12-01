<?php
/**
 * @version 2.1.2
 * @package Perfect Easy & Powerful Contact Form
 * @copyright © 2015 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
 * @license GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @author Piotr Moćko
 */

// TODO editor button for inserting shortcode
// TODO manage submitted emails

// No direct access
function_exists('add_action') or die;

$pwebcontact_admin = new PWebContact_Admin;

class PWebContact_Admin {

    protected $id = null;
    protected $view = null;
    protected $can_edit = false;
    protected $data = null;

    protected $notifications = array();
    protected $warnings = array();
    protected $errors = array();
    protected $requirements = array();

    protected $documentation_url = '';
    protected $buy_url = '';


    protected static $pro = array(
        'load' => array(),
        'fields' => array(),
        'field_types' => array(),
        'settings' => array(
            'dlid'
        ),
        'params' => array(
            'attachment_delete',
			'attachment_type',
            'bg_color',
			'bg_image',
			'bg_opacity',
			'bg_padding',
			'bg_padding_position',
			'bg_position',
            'bg_repeat',
            'bg_size',
			'buttons_fields_color',
			'buttons_text_color',
            'email_copy::1',
            'fields_border_color',
            'fields_color',
            'fields_text_color',
            'fields_active_border_color',
            'fields_active_color',
            'fields_active_text_color',
            'fields_invalid_border_color',
            'fields_invalid_color',
            'fields_invalid_text_color',
            'form_font_family',
			'form_font_size',
            'gradient',
            'labels_invalid_color',
            'labels_position',
			'labels_width',
            'msg_error_color',
			'msg_success_color',
			'modal_bg',
			'modal_opacity',
            'rounded',
            'shadow',
            'show_upload',
            'text_color',
            'ticket_enable',
			'ticket_format',
            'toggler_bg',
			'toggler_color',
			'toggler_font',
			'toggler_font_family',
			'toggler_font_size',
            'toggler_glyphicon',
            'toggler_icomoon',
			'toggler_icon',
			'toggler_icon_custom_image',
			'toggler_icon_gallery_image',
            'toggler_rotate',
			'toggler_vertical',
            'upload_allowed_ext',
			'upload_autostart',
			'upload_files_limit',
			'upload_max_size',
			'upload_path',
			'upload_show_limits',
			'upload_size_limit'
        )
    );


    function __construct() {

        $source = 'wordpress.org';

        $this->documentation_url = 'https://www.perfect-web.co/wordpress-plugins/contact-form/documentation?utm_source=backend&utm_medium=button&utm_campaign=documentation&utm_content='.$source;
        $this->buy_url = 'https://www.perfect-web.co/wordpress-plugins/contact-form/subscriptions?tmpl=component&utm_source=backend&utm_medium=button&utm_campaign=upgrade_to_pro&utm_content='.$source;

        // initialize admin view
        add_action( 'admin_init', array($this, 'init') );

        // Configuration link in menu
        add_action( 'admin_menu', array($this, 'menu') );


		// Configuration link on plugins list
        add_filter( 'plugin_action_links', array($this, 'action_links'), 10, 2 );
    }


    function init() {


        if (!isset($_GET['page']) OR $_GET['page'] !== 'pwebcontact') {
            return;
        }

        load_plugin_textdomain( 'pwebcontact', false, basename(dirname(__FILE__)).'/languages' );

        $this->can_edit = current_user_can('manage_options');

        $task = isset($_GET['task']) ? $_GET['task'] : 'list';

        if ( $task == 'new' ) {

            if (!$this->can_edit) {
                // redirect to list view
                $this->_redirect('admin.php?page=pwebcontact&error='.
                        urlencode(__('You do not have sufficient permissions to create form!', 'pwebcontact')));
            }

            check_admin_referer( 'new-form' );

            // create new instance of form
            if ($this->_create_form()) {
                // redirect to edit view
                $this->_redirect('admin.php?page=pwebcontact&task=edit&id='.(int)$this->id);
            }
            else {
                $this->_redirect('admin.php?page=pwebcontact&error='.
                        urlencode(__('Failed creating a new form!', 'pwebcontact')));
            }
        }
        elseif ( $task == 'copy' AND isset($_GET['id'])) {

            $this->id = (int)$_GET['id'];
            $this->view = 'edit';

            if (!$this->can_edit OR !$this->id) {
                // redirect to list view
                $this->_redirect('admin.php?page=pwebcontact&error='.
                        urlencode(__('You do not have sufficient permissions to copy form!', 'pwebcontact')));
            }
            else {
                check_admin_referer( 'copy-form_'.$this->id );

                $result = $this->_copy_form();
                $message = __($result ? 'Contact form has been successfully copied.' : 'Failed copying contact form!', 'pwebcontact');

                if ($result) {
                    $this->_redirect('admin.php?page=pwebcontact&task=edit&id='.$this->id.'&notification='.urlencode($message));
                }
                else {
                    $this->_redirect('admin.php?page=pwebcontact&error='.urlencode($message));
                }
            }
        }
        elseif ( $task == 'edit' AND isset($_GET['id'])) {

            $this->id = (int)$_GET['id'];
            $this->view = 'edit';

            if (!$this->can_edit OR !$this->id) {
                // redirect to list view
                $this->_redirect('admin.php?page=pwebcontact&error='.
                        urlencode(__('You do not have sufficient permissions to edit form!', 'pwebcontact')));
            }
            else {
                $this->_load_form();

                // load JS files
                wp_enqueue_script('pwebcontact_flipster_script', plugins_url('media/js/jquery.flipster.js', __FILE__),
                        array(
                            'jquery'
                        ));

                wp_enqueue_script('pwebcontact_admin_script', plugins_url('media/js/jquery.admin-edit.js', __FILE__),
                        array(
                            'jquery',
                            'jquery-ui-tooltip'
                        ));

                wp_enqueue_script('pwebcontact_admin_fields_script', plugins_url('media/js/jquery.admin-fields.js', __FILE__),
                        array(
                            'jquery',
                            'jquery-ui-core',
                            'jquery-ui-widget',
                            'jquery-ui-dialog',
                            'jquery-ui-mouse',
                            'jquery-ui-tooltip',
                            'jquery-ui-sortable',
                            'jquery-ui-draggable',
                            'jquery-ui-droppable'
                        ));

                add_thickbox();

                // load JavaScript translations
                wp_localize_script('pwebcontact_admin_script', 'pwebcontact_l10n', array(
                    'delete' => __('Delete'),
                    'cancel' => __('Cancel'),
                    'ok' => __('OK'),
                    'drag_row' => __('Drag to change order of rows', 'pwebcontact'),
                    'add_column' => __('Add column', 'pwebcontact'),
                    'saving' => __('Saving...', 'pwebcontact'),
                    'saved_on' => __('Saved on', 'pwebcontact'),
                    'error' => __('Error'),
                    'request_error' => __('Request error', 'pwebcontact'),
                    'error_loading_fields_settings' => __('Loading fields settings has failed!', 'pwebcontact'),
                    'missing_theme_settings' => __('File with settings for selected theme does not exists!', 'pwebcontact'),
                    'missing_email_tmpl' => __('Email template in selected format does not exists. Change format or create new file with email template: %s', 'pwebcontact'),
                    'paste_adcenter' => __('Paste Microsoft adCenter conversion tracking script', 'pwebcontact'),
                    'paste_adwords' => __('Paste Google AdWords/Goal Conversion tracking script', 'pwebcontact'),
                    'email_vars' => __('Variables for email message', 'pwebcontact'),
                    'buy_subscription' => esc_html__('Buy PRO & Get support', 'pwebcontact')
                ));

                // load CSS
                wp_enqueue_style('wp-jquery-ui-dialog');

                wp_enqueue_style('pwebcontact_flipster_style', plugins_url('media/css/jquery.flipster.css', __FILE__));
            }
        }
        elseif ( $task == 'save' AND isset($_POST['id'])) {

            $this->id = (int)$_POST['id'];
            $this->view = 'edit';

            if (!$this->can_edit OR !$this->id) {
                // redirect to list view
                $this->_redirect('admin.php?page=pwebcontact&error='.
                        urlencode(__('You do not have sufficient permissions to edit form!', 'pwebcontact')));
            }
            else {

                if (isset($_GET['ajax'])) {
                    check_ajax_referer( 'save-form_'.$this->id );
                    //wp_verify_nonce( $_POST['_wp_nonce'], 'save-form_'.$this->id );
                }
                else {
                    check_admin_referer( 'save-form_'.$this->id );
                }

                $result = $this->_save_form();
                $message = __($result ? 'Contact form has been successfully saved.' : 'Failed saving contact form!', 'pwebcontact');

                if (isset($_GET['ajax'])) {
                    header('Content-type: application/json');
                    die(json_encode(array(
                        'success' => $result,
                        'message' => $message
                    )));
                }
                else {
                    $this->_redirect('admin.php?page=pwebcontact&task=edit&id='.$this->id.
                            '&'.($result ? 'notification' : 'error').'='.urlencode($message));
                }
            }
        }
        elseif ( $task == 'delete' AND isset($_GET['id'])) {

            $this->id = (int)$_GET['id'];
            $this->view = 'list';

            if (!$this->can_edit OR !$this->id) {
                // redirect to list view
                $this->_redirect('admin.php?page=pwebcontact&error='.
                        urlencode(__('You do not have sufficient permissions to delete form!', 'pwebcontact')));
            }
            else {

                if (isset($_GET['ajax'])) {
                    check_ajax_referer( 'delete-form_'.$this->id );
                    //wp_verify_nonce( $_POST['_wp_nonce'], 'delete-form_'.$this->id );
                }
                else {
                    check_admin_referer( 'delete-form_'.$this->id );
                }

                $result = $this->_delete_form();
                $message = __($result ? 'Contact form has been successfully deleted.' : 'Failed deleting contact form!', 'pwebcontact');

                if (isset($_GET['ajax'])) {
                    header('Content-type: application/json');
                    die(json_encode(array(
                        'success' => $result,
                        'message' => $message
                    )));
                }
                else {
                    $this->_redirect('admin.php?page=pwebcontact'.
                            '&'.($result ? 'notification' : 'error').'='.urlencode($message));
                }
            }
        }
        elseif ( $task == 'edit_state' AND isset($_GET['id']) AND isset($_GET['state'])) {

            $this->id = (int)$_GET['id'];
            $this->view = 'list';
            $state = (int)$_GET['state'];

            if (!$this->can_edit OR !$this->id) {
                // redirect to list view
                $this->_redirect('admin.php?page=pwebcontact&error='.
                        urlencode(__('You do not have sufficient permissions to edit form state!', 'pwebcontact')));
            }
            else {

                if (isset($_GET['ajax'])) {
                    check_ajax_referer( 'edit-form-state_'.$this->id );
                    //wp_verify_nonce( $_POST['_wp_nonce'], 'edit-form-state_'.$this->id );
                }
                else {
                    check_admin_referer( 'edit-form-state_'.$this->id );
                }

                $result = $this->_save_form_state($state);
                $message = __($result ? 'Contact form has been successfully '.($state ? 'published' : 'unpublished').'.' : 'Failed changing contact form state!', 'pwebcontact');

                if (isset($_GET['ajax'])) {
                    header('Content-type: application/json');
                    die(json_encode(array(
                        'success' => $result,
                        'message' => $message,
                        'state' => $state
                    )));
                }
                else {
                    $this->_redirect('admin.php?page=pwebcontact'.
                            '&'.($result ? 'notification' : 'error').'='.urlencode($message));
                }
            }
        }
        elseif ( $task == 'debug' AND isset($_GET['state'])) {

            $this->view = 'list';
            $state = (int)$_GET['state'];

            if (!$this->can_edit) {
                // redirect to list view
                $this->_redirect('admin.php?page=pwebcontact&error='.
                        urlencode(__('You do not have sufficient permissions to change debug mode state!', 'pwebcontact')));
            }
            else {

                if (isset($_GET['ajax'])) {
                    check_ajax_referer( 'edit-debug-state' );
                    //wp_verify_nonce( $_POST['_wp_nonce'], 'edit-debug-state' );
                }
                else {
                    check_admin_referer( 'edit-debug-state' );
                }

                $result = update_option('pwebcontact_debug', $state);
                $message = __($result ? 'Debug has been successfully '.($state ? 'enabled' : 'disabled').'.' : 'Failed changing debug mode state!', 'pwebcontact');

                if (isset($_GET['ajax'])) {
                    header('Content-type: application/json');
                    die(json_encode(array(
                        'success' => $result,
                        'message' => $message,
                        'state' => $state
                    )));
                }
                else {
                    $this->_redirect('admin.php?page=pwebcontact'.
                            '&'.($result ? 'notification' : 'error').'='.urlencode($message));
                }
            }
        }
        elseif ( $task == 'settings') {

            $this->view = 'settings';

            if (!$this->can_edit) {
                // redirect to list view
                $this->_redirect('admin.php?page=pwebcontact&error='.
                        urlencode(__('You do not have sufficient permissions to edit settings!', 'pwebcontact')));
            }
            else {
                $this->_load_settings();

                // load JS files
                wp_enqueue_script('pwebcontact_admin_script', plugins_url('media/js/jquery.admin-settings.js', __FILE__),
                        array(
                            'jquery',
                            'jquery-ui-tooltip'
                        ));

                // load JavaScript translations
                wp_localize_script('pwebcontact_admin_script', 'pwebcontact_l10n', array(
                    'saving' => __('Saving...', 'pwebcontact'),
                    'saved_on' => __('Saved on', 'pwebcontact'),
                    'error' => __('Error'),
                    'request_error' => __('Request error', 'pwebcontact')
                ));
            }
        }
        elseif ( $task == 'save_settings') {

            $this->view = 'settings';

            if (!$this->can_edit) {
                // redirect to list view
                $this->_redirect('admin.php?page=pwebcontact&error='.
                        urlencode(__('You do not have sufficient permissions to edit settings!', 'pwebcontact')));
            }
            else {

                if (isset($_GET['ajax'])) {
                    check_ajax_referer( 'save-settings' );
                    //wp_verify_nonce( $_POST['_wp_nonce'], 'save-settings' );
                }
                else {
                    check_admin_referer( 'save-settings' );
                }

                $result = $this->_save_settings();
                $message = __($result ? 'Settings have been successfully saved.' : 'Failed saving settings!', 'pwebcontact');

                if (isset($_GET['ajax'])) {
                    header('Content-type: application/json');
                    die(json_encode(array(
                        'success' => $result,
                        'message' => $message
                    )));
                }
                else {
                    $this->_redirect('admin.php?page=pwebcontact&task=settings'.
                            '&'.($result ? 'notification' : 'error').'='.urlencode($message));
                }
            }
        }
        elseif ( $task == 'list' OR $task == '' ) {

            $this->view = 'list';

            if (!$this->can_edit AND !isset($_GET['error'])) {
                $this->errors[] = __( 'You do not have sufficient permissions to create form!', 'pwebcontact' );
            }

            $this->_check_requirements();
            $this->_load_forms();
			$this->_load_settings();

            // load JS files
            wp_enqueue_script('pwebcontact_admin_script', plugins_url('media/js/jquery.admin-list.js', __FILE__),
                    array('jquery', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-dialog', 'jquery-ui-tooltip'));

            add_thickbox();

            wp_localize_script('pwebcontact_admin_script', 'pwebcontact_l10n', array(
                'delete' => __( 'Delete' ),
                'cancel' => __( 'Cancel' ),
                'request_error' => __('Request error', 'pwebcontact'),
                'buy_subscription' => esc_html__('Buy PRO & Get support', 'pwebcontact')
            ));

            // load CSS
            wp_enqueue_style('wp-jquery-ui-dialog');
        }
        elseif ( $task == 'load_email' ) {

            check_ajax_referer( 'load-email' );
            //wp_verify_nonce( $_POST['_wp_nonce'], 'load-email' );

            $content = '';
            if (isset($_GET['ajax']) AND isset($_POST['format']) AND $_POST['format'] AND isset($_POST['tmpl']) AND $_POST['tmpl']) {

                $path = dirname(__FILE__) .'/media/email_tmpl/'. basename($_POST['tmpl']) . ((int)$_POST['format'] === 2 ? '.html' : '.txt');
                if (function_exists('WP_Filesystem') AND WP_Filesystem()) {
                    global $wp_filesystem;
                    if ($wp_filesystem->is_file($path)) {
                        $content = $wp_filesystem->get_contents($path);
                    }
                }
                elseif (is_file($path)) {
                    $content = file_get_contents($path);
                }
            }

            header('Content-type: text/plain');
            die( $content );
        }
        elseif ( $task == 'load_fields' ) {

            check_ajax_referer( 'load-fields' );
            //wp_verify_nonce( $_POST['_wp_nonce'], 'load-fields' );

            $content = '';
            if (isset($_GET['ajax']) AND isset($_POST['fields']) AND $_POST['fields']) {

                $path = dirname(__FILE__) .'/media/fields_settings/'. basename($_POST['fields']) . '.txt';
                if (function_exists('WP_Filesystem') AND WP_Filesystem()) {
                    global $wp_filesystem;
                    if ($wp_filesystem->is_file($path)) {
                        $content = $wp_filesystem->get_contents($path);
                    }
                }
                elseif (is_file($path)) {
                    $content = file_get_contents($path);
                }
            }

            header('Content-type: application/json');
            die( $content );
        }

        // load CSS
        //wp_enqueue_style('pwebcontact_jquery_ui_style', plugins_url('media/css/ui/jquery-ui-1.10.4.custom.css', __FILE__));
        wp_enqueue_style('pwebcontact_admin_style', plugins_url('media/css/admin.css', __FILE__));
        wp_enqueue_style('pwebcontact_glyphicon_style', plugins_url('media/css/glyphicon.css', __FILE__));

        add_action('admin_head', array($this, 'admin_head'));
    }


    function menu() {

        $title = __('Perfect Easy & Powerful Contact Form', 'pwebcontact');

        if (isset($_GET['task']) AND $_GET['task'] == 'edit') {
            $title = __('Edit') .' &lsaquo; '. $title;
        }

        add_menu_page($title, __('Perfect Contact Forms', 'pwebcontact'),
                'manage_options', 'pwebcontact', array($this, 'configuration'),
                plugins_url('media/images/admin/menu-icon.png', dirname(__FILE__).'/pwebcontact.php'));
    }


    function action_links( $links, $file ) {

        if ( $file == plugin_basename(dirname(__FILE__).'/pwebcontact.php') ) {
            $links[] = '<a href="' . admin_url( 'admin.php?page=pwebcontact' ) . '">'.__( 'Forms list', 'pwebcontact' ).'</a>';
        }

        return $links;
    }


    function admin_head() {

?>
<script type="text/javascript">
var pwebcontact_admin = pwebcontact_admin || {};
pwebcontact_admin.plugin_url = "<?php echo plugins_url('pwebcontact/'); ?>";
pwebcontact_admin.buy_url = "<?php echo $this->buy_url; ?>";
</script>
<?php
    }


    protected function _recursive_stripslashes(&$input) {

        if (is_array($input)) {
            foreach ($input as &$item) {
                $this->_recursive_stripslashes($item);
            }
        }
        elseif (is_string($input)) {
            $input = stripslashes($input);
        }
    }


    protected function _load_forms() {

        global $wpdb;

        if (!is_object($this->data)) {
            $this->data = new stdClass();
        }

        if (!isset($this->data->forms)) {

            $sql =  'SELECT `id`, `title`, `publish`, `position`, `modify_date`, `layout` '.
                    'FROM `'.$wpdb->prefix.'pwebcontact_forms` ';
            $this->data->forms = $wpdb->get_results($sql);

            if ($this->data->forms === null) {
                $this->data->forms = array();
            }
        }
    }


    protected function _load_form() {

        global $wpdb;

        if ($this->data === null AND $this->id) {

            $sql =  $wpdb->prepare('SELECT `title`, `publish`, `position`, `layout`, `modify_date`, `params`, `fields` '.
                    'FROM `'.$wpdb->prefix.'pwebcontact_forms` '.
                    'WHERE `id` = %d', $this->id);
            $this->data = $wpdb->get_row($sql);

            if ($this->data === null) {
                $this->data = false;
            }
            else {
                $this->data->params = $this->data->params ? json_decode( $this->data->params, true ) : array();
                $this->data->params['position'] = $this->data->position;
                $this->data->params['layout_type'] = $this->data->layout;
                $this->_recursive_stripslashes($this->data->params);

                $this->data->fields = $this->data->fields ? json_decode( $this->data->fields, true ) : array();
                $this->_recursive_stripslashes($this->data->fields);

                $this->_load_settings();
            }
        }
    }


    protected function _load_settings() {

        if (!is_object($this->data)) {
            $this->data = new stdClass();
        }
        if (!isset($this->data->settings) OR !is_array($this->data->settings)) {
            $this->data->settings = get_option('pwebcontact_settings', array());
            $this->_recursive_stripslashes($this->data->settings);
        }
    }


    protected function _set_param($key = null, $value = null, $group = 'params') {

        if (!is_object($this->data)) {
            $this->data = new stdClass();
        }
        $this->data->{$group}[$key] = $value;
    }


    protected function _get_param($key = null, $default = null, $group = 'params') {

        if (isset($this->data->{$group})) {
            if ($key === null) {
                return $this->data->{$group};
            }
            elseif (isset($this->data->{$group}[$key]) AND
                $this->data->{$group}[$key] !== null AND
                $this->data->{$group}[$key] !== '') {
                return $this->data->{$group}[$key];
            }
        }
        return $default;
    }


    protected function _get_post($key = null, $default = null) {

        if (isset($_POST[$key]) AND $_POST[$key] !== null AND $_POST[$key] !== '') {
            return $_POST[$key];
        }
        return $default;
    }


    protected function _redirect($url = null)
    {
        $url = admin_url($url);
        if (wp_redirect($url)) {
            die();
        }
        else {
            die('<script>document.location.href="'.$url.'";</script>');
        }
    }


    protected function _check_requirements() {

        if (($result = $this->_check_php_version()) !== true) {
            $this->errors[] = $result;
        }

        if (($result = $this->_check_wp_version()) !== true) {
            $this->errors[] = $result;
        }
    }


    protected function _create_form() {

        global $wpdb;

        $data = array(
            'title' => 'Contact form',
            'publish' => 1,
            'position' => 'footer',
            'layout' => 'slidebox',
            'modify_date' => gmdate('Y-m-d H:i:s'),
            'params' => '{}',
            'fields' => '{}'
        );

        if ($wpdb->insert($wpdb->prefix.'pwebcontact_forms', $data)) {
            $this->id = (int)$wpdb->insert_id;
            return true;
        }
        return false;
    }


    protected function _copy_form() {

        global $wpdb;

        $sql =  $wpdb->prepare('SELECT `title`, `position`, `layout`, `params`, `fields` '.
                    'FROM `'.$wpdb->prefix.'pwebcontact_forms` '.
                    'WHERE `id` = %d', $this->id);
        $data = $wpdb->get_row($sql, ARRAY_A);

        if (!$data) return false;

        $data['title'] .= __( ' (Copy)', 'pwebcontact' );
        $data['publish'] = 0;
        $data['modify_date'] = gmdate('Y-m-d H:i:s');

        if ($wpdb->insert($wpdb->prefix.'pwebcontact_forms', $data)) {
            $this->id = (int)$wpdb->insert_id;
            return true;
        }
        return false;
    }


    protected function _save_settings() {

        return update_option('pwebcontact_settings', $this->_get_post('settings'));
    }


    protected function _save_form() {

        global $wpdb;

        // Get params from request
        $this->data = new stdClass();
        $this->data->params = $this->_get_post('params', array());

        $params =& $this->data->params;

        // TODO Validate params
        // Int
        /*
        zindex
        labels_width
        toggler_width
        toggler_height
        toggler_font_size
        msg_close_delay
        open_delay
        open_count
        cookie_lifetime
        close_delay
        effect_duration*/

        // Unit
        /*
        offset
        bg_padding
        form_width*/

        // URL
        /*
        redirect_url*/

        // Single email
        /*
        email_from
        email_replyto*/

        // Emails
        /*
        email_to
        email_bcc*/

        $this->data->fields = $this->_get_post('fields', array());
        $fields =& $this->data->fields;
        ksort($fields);

        $position = $this->_get_param('position');
        $layout = $this->_get_param('layout_type');

        unset($params['position'], $params['layout_type'], $params['fields']);

        // Update data
        return false !== $wpdb->update($wpdb->prefix.'pwebcontact_forms', array(
                    'title' => $this->_get_post('title'),
                    //'publish' => $this->_get_post('publish', 1),
                    'position' => $position,
                    'layout' => $layout,
                    'modify_date' => gmdate('Y-m-d H:i:s'),
                    'params' => json_encode($params),
                    'fields' => json_encode($fields)
                ), array('id' => $this->id), array('%s', /*'%d',*/ '%s', '%s', '%s', '%s'));
    }


    protected function _save_form_state($state = 1) {

        global $wpdb;

        // Update data
        return false !== $wpdb->update($wpdb->prefix.'pwebcontact_forms', array('publish' => (int)$state), array('id' => $this->id));
    }


    protected function _delete_form() {

        global $wpdb;

        return false !== $wpdb->delete($wpdb->prefix.'pwebcontact_forms', array('id' => $this->id), array('%d'));
    }


    function configuration() {

?>
<div class="wrap pweb-wrap pweb-view-<?php echo $this->view; ?>">

    <?php
    if ($this->view == 'list') :

        if (count($this->data->forms)) :
            $this->_display_forms_list();
        else :
            $this->_display_create_form();
        endif;

    elseif ($this->view == 'edit') :
        $this->_display_edit_form();

    elseif ($this->view == 'settings') :
        $this->_display_settings();

    endif; ?>

    <hr>

    <p class="pweb-copyrights">
		Copyright &copy; 2015
        <a href="https://www.perfect-web.co/wordpress-plugins/contact-form" target="_blank"><strong>Perfect Web sp. z o.o.</strong></a>,
        All rights reserved.
		Distributed under
        <a href="http://www.gnu.org/licenses/gpl-3.0.html" target="_blank"><strong>GNU/GPL</strong></a>.<br>
		All other trademarks and copyrights are property of their respective owners.
	</p>
</div>
<?php

    }


    protected function _load_tmpl($name = '', $preffix = __FILE__) {

        $path = plugin_dir_path(__FILE__).'tmpl/'.basename($preffix, '.php').'_'.$name.'.php';
        if (is_file($path)) {
            include $path;
        }
    }


    protected function _display_messages() {

        if (isset($_GET['error']) AND $_GET['error']) {
            $this->errors[] = urldecode($_GET['error']);
        }

        if (count($this->errors)) {
?>
<div class="error pweb-clearfix"><p><strong><?php echo implode('<br>', $this->errors); ?></strong></p></div>
<?php
		}

        if (count($this->warnings)) {
?>
<div class="error pweb-clearfix"><p><strong><?php echo implode('<br>', $this->warnings); ?></strong></p></div>
<?php
		}

        if (isset($_GET['notification']) AND $_GET['notification']) {
            $this->notifications[] = urldecode($_GET['notification']);
        }
		if (count($this->notifications)) {
?>
<div class="updated pweb-clearfix"><p><strong><?php echo implode('<br>', $this->notifications); ?></strong></p></div>
<?php
		}
    }


    protected function _display_settings() {

        $this->_load_tmpl('settings');
    }


    protected function _display_create_form() {

        $this->_load_tmpl('new');
    }


    protected function _display_forms_list() {

        $this->_load_tmpl('list');
    }


    protected function _display_edit_form() {

        $this->_load_tmpl('edit');
    }


    protected function _get_themes() {

        $themes = array();

        $active_theme = $this->_get_param('theme', 'free');

        $themes_url = plugins_url('/media/themes/', dirname(__FILE__) .'/pwebcontact.php');
        $media_dir = dirname(__FILE__) .'/media/';

        $dir = new DirectoryIterator( $media_dir . 'themes' );
        foreach( $dir as $item ) {

            if ($item->isFile() AND preg_match('/\.json$/i', $item->getFilename())) {

                $basename = $item->getBasename('.json');


                if (function_exists('WP_Filesystem') AND WP_Filesystem()) {
                    global $wp_filesystem;
                    $has_image = $wp_filesystem->is_file( $item->getPath() . '/' . $basename . '.jpg' );
                    $settings = $wp_filesystem->get_contents($item->getPathname());
                }
                else {
                    $has_image = is_file( $item->getPath() . '/' . $basename . '.jpg' );
                    $settings = file_get_contents($item->getPathname());
                }

                if ($settings) {

                    $settings = json_decode($settings);

                    $theme = new stdClass();
                    $theme->title = isset($settings->title) ? $settings->title : ucfirst( str_replace('_', ' ', $basename) );
                    $theme->description = isset($settings->description) ? $settings->description : '';
                    $theme->image = $has_image ? $themes_url . $basename . '.jpg' : null;
                    $theme->settings = isset($settings->params) ? json_encode($settings->params) : '{}';
                    $theme->is_active = ($active_theme === $basename);

                    $themes[$basename] = $theme;
                }
            }
        }

        return $themes;
    }


    protected function _get_plugin_name() {

        $data = get_plugin_data(dirname(__FILE__).'/pwebcontact.php', false, false);
        return $data['Name'];
    }


    protected function _get_version() {

        $data = get_plugin_data(dirname(__FILE__).'/pwebcontact.php', false, false);
        return $data['Version'];
    }


	protected function _get_feeds_script() {

		global $wp_version;

        return
			  '(function(){'
			. 'var pw=document.createElement("script");pw.type="text/javascript";pw.async=true;'
			. 'pw.src="https://www.perfect-web.co/index.php?option=com_ars&view=update&task=stream&format=raw&id=8&version='.$this->_get_version().'&wpversion='.$wp_version.'&uid='.md5(home_url()).'";'
			. 'var s=document.getElementsByTagName("script")[0];s.parentNode.insertBefore(pw,s);'
			. '})();';
    }


    protected function _get_name() {

        $data = get_plugin_data(dirname(__FILE__).'/pwebcontact.php', false, true);
        return $data['Name'];
    }


    protected function _get_field( $opt = array() ) {

        $opt = array_merge(array(
            'id' => null,
            'name' => null,
            'group' => 'params',
            'label' => null,
            'desc' => null,
            'header' => null,
            'parent' => null,
            'disabled' => false,
            'is_pro' => null
        ), $opt);

        extract( $opt );

        if ($is_pro === null) {
            $opt['is_pro'] = $is_pro = in_array($name, self::$pro[$group]);
        }

        if ($parent !== null) {
            $names = array();
            foreach((array)$parent as $parent_name) {
                $names[] = 'pweb_'. $group .'_'.$parent_name;
            }
            $parent = ' pweb-child '.implode(' ', $names);
        }

        return
                '<div class="pweb-field pweb-field-'.$type
                .($parent ? $parent : '')
                .($is_pro === true ? ' pweb-pro' : '')
                .($disabled === true ? ' pweb-disabled' : '')
                .'">'.
                    ($header ? '<h3>'.$header.'</h3>' : '').
                    ($label ? $this->_get_label($opt) : '').
                    '<div class="pweb-field-control">'.
                        $this->_get_field_control($opt).
                        ($desc ? '<div class="pweb-field-desc">'. __($desc, 'pwebcontact') .'</div>' : '').
                    '</div>'.
                '</div>';
    }


    protected function _get_label( $opt = array() ) {

        $opt = array_merge(array(
            'id' => null,
            'name' => null,
            'index' => null,
            'group' => 'params',
            'label' => null,
            'tooltip' => null,
            'required' => false,
            'is_pro' => null
        ), $opt);

        extract( $opt );

        if (empty($id)) {
            $id = 'pweb_'. $group .'_'. ($index !== null ? $index.'_' : '') . $name;
        }
        if ($is_pro === null) {
            $is_pro = in_array($name, self::$pro[$group]);
        }

        return '<label for="'.esc_attr($id).'" id="'.esc_attr($id).'-lbl"' .
                ' class="' . ($tooltip ? 'pweb-has-tooltip' : '') . ($required ? ' required' : '') . '"' .
                ($tooltip ? ' title="'. esc_attr__($tooltip, 'pwebcontact') .'"' : '') .
                '>' .
                __($label, 'pwebcontact') .
                ($required ? ' <span class="pweb-star">*</span>' : '') .

                '</label>' .
                ($is_pro === true ? $this->_display_badge_pro() : '');
    }


    protected function _get_field_control( $opt = array() ) {

        $opt = array_merge(array(
            'type' => 'text',
            'id' => null,
            'name' => null,
            'index' => null,
            'group' => 'params',
            'value' => null,
            'default' => null,
            'class' => null,
            'required' => false,
            'disabled' => false,
            'readonly' => false,
            'attributes' => array(),
            'options' => array(),
            'is_parent' => false,
            'is_pro' => null,
            'html_after' => null
        ), $opt);

        extract( $opt );

        $html = '';


        if (empty($id)) {
            $id = 'pweb_'. $group .'_'. ($index !== null ? $index.'_' : '') . $name;
        }
        $attributes['id'] = $id;

        $field_name = esc_attr($group. ($index !== null ? '['.$index.']' : '') . '['.$name.']');

        if ($is_pro === null) {
            $is_pro = in_array($name, self::$pro[$group]);
        }

        if (!isset($attributes['class'])) {
            $attributes['class'] = '';
        }
        if ($class) {
            $attributes['class'] .= ' '.$class;
        }
        if ($required) {
            $attributes['class'] .= ' required';
            $attributes['required'] = 'required';
        }
        //if ($is_pro === true OR $disabled) {
        if ($disabled) {
            $attributes['disabled'] = 'disabled';
        }
        if ($readonly) {
            $attributes['readonly'] = 'readonly';
        }

        if ($is_parent === true) {
            $attributes['class'] .= ' pweb-parent';
        }
        elseif (count($options)) {
            foreach ($options as $option) {
                if (isset($option['is_parent']) AND $option['is_parent'] === true) {
                    $attributes['class'] .= ' pweb-parent';
                    break;
                }
            }
        }

        if ($value === null) {
            $value = $this->_get_param($name, $default, $group);
        }
        if ($value === null OR $value === '') {
            $value = $default;
        }

        // extend HTML fields with custom types
        switch ($type) {

            case 'filelist' AND isset($directory):

                $type = 'select';

                if (!count($options)) {
                    $options = array(array(
                        'value' => '',
                        'name' => '- Select option -'
                    ));
                }

                if (is_dir( dirname(__FILE__) .'/'. trim($directory, '/\\') )) {
                    $directory = dirname(__FILE__) .'/'. trim($directory, '/\\');
                }
                elseif (is_dir( ABSPATH .'/'. trim($directory, '/\\') )) {
                    $directory = ABSPATH .'/'. trim($directory, '/\\');
                }
                else {
                    $directory = null;
                }

                if ($directory) {
                    $dir = new DirectoryIterator($directory);
                    foreach( $dir as $item )
                    {
                        if ($item->isFile())
                        {
                            if (strpos($item->getFilename(), 'index.') === false AND preg_match('/'.$filter.'/i', $item->getFilename())) {
                                if (isset($strip_ext) AND $strip_ext) {
                                    $pos = strrpos($item->getFilename(), '.', 3);
                                    $file_name = substr($item->getFilename(), 0, $pos);
                                }
                                else {
                                    $file_name = $item->getFilename();
                                }
                                $options[] = array(
                                    'value' => $file_name,
                                    'name' => $file_name
                                );
                            }
                        }
                    }
                }
                break;


            case 'glyphicon':

                $type = 'select';

                $css = file_get_contents( dirname(__FILE__).'/media/css/glyphicon.css' );
                if (preg_match_all('/\.(glyphicon-[^:]+):before\s*\{\s*content:\s*"\\\([^"]+)";\s*\}/i', $css, $matches, PREG_SET_ORDER))
                {
                    $attributes['class'] .= ' pweb-glyphicon-list';

                    foreach ($matches as $icon) {
                        $options[] = array(
                            'value' => $icon[2],
                            'name' => '&#x'.$icon[2].';'
                        );
                    }
                }
                break;


            case 'image':

                $type = 'text';
                break;


            case 'wp_user':

                $type = 'select';
                $blog_id = get_current_blog_id();

                if (!count($options)) {
                    $options = array(array(
                        'value' => '',
                        'name' => '- Select Administrator -'
                    ));
                }

                $users = get_users('blog_id='.$blog_id.'&orderby=display_name&role=administrator');
                if ($users) {
                    foreach ($users as $user) {
                        $options[] = array(
                            'value' => $user->ID,
                            'name' => $user->display_name .' <'. $user->user_email .'>'
                        );
                    }
                }
                break;


            case 'text_button':

                $type = 'text';
                $html_after .= '<button type="button" class="button" id="'.$id.'_btn">'. esc_html__($button, 'pwebcontact') .'</button>';
                break;


            case 'color':

                $type = 'text';
                wp_enqueue_script( 'wp-color-picker' );
                wp_enqueue_style( 'wp-color-picker' );
                $html_after .= '<script type="text/javascript">'
                        . 'jQuery(document).ready(function($){'
                            . '$("#'.$id.'").wpColorPicker({'
                                /*. 'change:function(e,ui){'
                                    //. '$(this).trigger("change")'
                                . '},'
                                . 'clear:function(e,ui){'
                                    //. '$(this).trigger("change")'
                                . '}'*/
                            . '})'
                        . '})'
                    . '</script>';
                break;

            case 'custom':

                $html .= '<div '. $this->_attr_to_str($attributes) .'>'. $content .'</div>';
                break;
        }


        // default HTML field types
        switch ($type) {

            case 'text':
            case 'password':
            case 'email':
            case 'hidden':

                $html .= '<input type="'.$type.'" name="'.$field_name.'" value="'. esc_attr($value) .'"'. $this->_attr_to_str($attributes) .'>';
                break;


            case 'textarea':

                $attributes['cols'] = isset($attributes['cols']) ? $attributes['cols'] : 30;
                $attributes['rows'] = isset($attributes['rows']) ? $attributes['rows'] : 5;

                $html .= '<textarea name="'.$field_name.'"'. $this->_attr_to_str($attributes) .'>'. esc_html($value) .'</textarea>';
                break;

            case 'button':
                $html .= '<button '. $this->_attr_to_str($attributes) .'>'. esc_attr($value) .'</button>';
                break;

            case 'select':

                if (isset($multiple)) {
                    $field_name .= '[]';
                    $attributes['multiple'] = 'multiple';
                    if (!isset($attributes['size']) OR empty($attributes['size'])) {
                        $attributes['size'] = 4;
                    }
                }
                $html .= '<select name="'.$field_name.'"'. $this->_attr_to_str($attributes) .'>';
                foreach ($options as $option) {

                    /*if ($is_pro === false AND !(isset($option['disabled']) AND $option['disabled']) AND in_array($name.':'.$option['value'], self::$pro[$group]) ) {
                        /option['disabled'] = true;
                    }*/
                    if (!isset($option['name'])) {
                        $option['name'] = (string)$option['value'];
                    }

                    $html .= '<option value="'.esc_attr($option['value']).'"'. selected($value, $option['value'], false)
                            . (isset($attributes['disabled']) OR (isset($option['disabled']) AND $option['disabled']) ? ' disabled="disabled"' : '')
                            . '>'. esc_html__($option['name'], 'pwebcontact') .'</option>';
                }
                $html .= '</select>';
                break;


            case 'radio':
            case 'checkbox':

                $html .= '<fieldset'. $this->_attr_to_str($attributes) .'>';

                if ($type == 'checkbox' AND count($options) > 1) {
                    $field_name .= '[]';
                }

                foreach ($options as $option) {

                    /*if ($is_pro === false AND !(isset($option['disabled']) AND $option['disabled']) AND in_array($name.':'.$option['value'], self::$pro[$group]) ) {
                        $option['disabled'] = true;
                    }*/
                    if (isset($option['parent'])) {
                        $names = array();
                        foreach((array)$option['parent'] as $parent_name) {
                            $names[] = 'pweb_'. $group .'_'.$parent_name;
                        }
                        $option['class'] .= ' pweb-child '.implode(' ', $names);
                    }
                    if (isset($option['tooltip'])) {
                        $option['class'] .= ' pweb-has-tooltip';
                    }
                    if ($value == $option['value']) {
                        if (isset($option['disabled']) AND $option['disabled']) {
                            // Select first not disabled option if currently selected option is disabled
                            $html_after .= '<script type="text/javascript">jQuery(document).ready(function($){$("#'.$id.' input").not(":disabled").first().trigger("click");});</script>';
                            $value = null;
                        }
                    }

                    $option['is_pro'] = ($is_pro !== true AND in_array($name.'::'.$option['value'], self::$pro[$group]));

                    $option_id = $id .'_'. preg_replace('/[^a-z0-9-_]/i', '', str_replace(':', '_', $option['value']));

                    $html .= '<div class="pweb-field-option'
                            . (isset($option['class']) ? ' '.esc_attr($option['class']) : '').'"'
                            . (isset($option['tooltip']) ? ' title="'. esc_attr__($option['tooltip'], 'pwebcontact') .'"' : '')
                            . '>';

                    $html .= '<input type="'.$type.'" name="'.$field_name.'" id="'.$option_id.'"'
                            . ' value="'.esc_attr($option['value']).'"'. checked($value, $option['value'], false)
                            . ((isset($attributes['disabled']) OR (isset($option['disabled']) AND $option['disabled'])) ? ' disabled="disabled"' : '')
                            . ' class="'
                            . (($is_parent === true OR (isset($option['is_parent']) AND $option['is_parent'] === true)) ? 'pweb-parent' : '')
                            . ($option['is_pro'] ? ' pweb-pro' : '')
                            . '">';

                    $html .= '<label for="'.$option_id.'" id="'.$option_id.'-lbl"'
                            . '>'. __($option['name'], 'pwebcontact') . (isset($option['after']) ? $option['after'] : '')
                            . ($option['is_pro'] ? $this->_display_badge_pro() : '')
                            . '</label>';

                    $html .= '</div>';
                }
                $html .= '</fieldset>';
                break;
        }

        return $html . $html_after;
    }

    protected function _attr_to_str($attributes = array()) {

        $attr = '';
        foreach ($attributes as $name => $value) {
            $attr .= ' '.$name.'="'.esc_attr($value).'"';
        }
        return $attr;
    }

    protected function _display_badge($field_type = null)
    {
        if (in_array($field_type, self::$pro['field_types'])) {
            return $this->_display_badge_pro();
        }
    }

    protected function _display_badge_pro()
    {
        return ' <span class="pweb-pro pweb-has-tooltip" title="'.__('You need to get PRO version to use this feature', 'pwebcontact').'">'.__('PRO', 'pwebcontact').'</span>';
    }

    protected function _is_pro_field($field_type = null)
    {
        return in_array($field_type, self::$pro['field_types']);
    }

    protected function _set_pro_options($group = null, $options = array())
    {
        self::$pro[$group] = $options;
    }

    private function _convert_size($str)
    {
        $val = trim($str);
        $last = strtolower($str[strlen($str)-1]);
        switch($last)
		{
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;
        }
		$val = $val / 1024 / 1024;

        return $val > 10 ? intval($val) : round($val, 2);
    }


    private function _check_image_text_creation()
	{
		if (!isset($this->requirements['image_text']))
        {
            $this->requirements['image_text'] = true;

            $functions = array(
                'imagecreatetruecolor',
                'imagecolorallocate',
                'imagecolorallocatealpha',
                'imagesavealpha',
                'imagealphablending',
                'imagefill',
                'imagettftext',
                'imagepng',
                'imagedestroy'
            );
            $disabled_functions = array();
            foreach ($functions as $function)
            {
                if (!(function_exists($function) && is_callable($function))) $disabled_functions[] = $function;
            }
            if (count($disabled_functions))
            {
                $this->requirements['image_text'] = sprintf( __('You can not use vertical Toggler Tab, because on this server following PHP functions are disabled or missing: %s. Contact with server administrator to fix it.', 'pwebcontact'), implode(', ', $disabled_functions) );
            }
        }

		return $this->requirements['image_text'];
	}

    private function _check_cache_path()
	{
        if (!isset($this->requirements['cache_path']))
        {
            $this->requirements['cache_path'] = true;

            $path = dirname(__FILE__).'/media/cache/';

            if (function_exists('WP_Filesystem') AND WP_Filesystem()) {
                global $wp_filesystem;

                if (!$wp_filesystem->is_writable($path)) {
                    $wp_filesystem->chmod($path, 0777);
                }
                else {
                    return $this->requirements['cache_path'];
                }

                if (!$wp_filesystem->is_writable($path)) {
                    $this->requirements['cache_path'] = sprintf(__('Cache directory: %s is not writable.', 'pwebcontact'), $path);
                }
            }
            else {
                if (!is_writable($path)) {
                    chmod($path, 0777);
                }
                else {
                    return $this->requirements['cache_path'];
                }

                if (!is_writable($path)) {
                    $this->requirements['cache_path'] = sprintf(__('Cache directory: %s is not writable.', 'pwebcontact'), $path);
                }
            }
        }

        return $this->requirements['cache_path'];
	}

    private function _check_upload_path()
	{
        if (!isset($this->requirements['upload_path']))
        {
            $this->requirements['upload_path'] = true;

            $upload_dir = wp_upload_dir();
            $path = $upload_dir['basedir'].'/pwebcontact/'.$this->id.'/';

            if (function_exists('WP_Filesystem') AND WP_Filesystem()) {
                global $wp_filesystem;

                // create wirtable upload path
                if (!$wp_filesystem->is_dir($path)) {
                    $wp_filesystem->mkdir($path, 0777);
                }
                else {
                    return $this->requirements['upload_path'];
                }

                // check upload path
                if (!$wp_filesystem->is_writable($path)) {
                    $this->requirements['upload_path'] = sprintf(__('Upload directory: %s is not writable.', 'pwebcontact'), $path);
                }
                // copy index.html file to upload path for security
                elseif (!$wp_filesystem->is_file($path.'index.html')) {
                    $wp_filesystem->copy(dirname(__FILE__).'/index.html', $path.'index.html');
                }
            }
            else {
                // create wirtable upload path
                if (!is_dir($path)) {
                    mkdir($path, 0777, true);
                }
                else {
                    return $this->requirements['upload_path'];
                }

                // check upload path
                if (!is_writable($path)) {
                    $this->requirements['upload_path'] = sprintf(__('Upload directory: %s is not writable.', 'pwebcontact'), $path);
                }
                // copy index.html file to upload path for security
                elseif (!is_file($path.'index.html')) {
                    copy(dirname(__FILE__).'/index.html', $path.'index.html');
                }
            }
        }

        return $this->requirements['upload_path'];
	}

    private function _check_mailer()
	{
        if (!isset($this->requirements['mailer']))
        {
            $this->requirements['mailer'] = true;

            $this->_load_settings();
            $mailer = $this->_get_param('mailer', 'inherit', 'settings');

            if ($mailer === 'mail' AND !(function_exists('mail') AND is_callable('mail'))) {
                $this->requirements['mailer'] = sprintf(__('PHP mail function is disabled. Change mailer type to SMTP in %s or ask your server Administrator to enable it.', 'pwebcontact'), '<a href="'.admin_url('admin.php?page=pwebcontact&task=settings').'" target="_blank">'.__('Contact Form Settings', 'pwebcontact').'</a>');
            }
            elseif ($mailer === 'smtp' AND (
                    !$this->_get_param('smtp_username', null, 'settings') OR
                    !$this->_get_param('smtp_password', null, 'settings') OR
                    !$this->_get_param('smtp_host', null, 'settings') OR
                    !$this->_get_param('smtp_port', null, 'settings')
                    )) {
                $this->requirements['mailer'] = sprintf(__('Setup SMTP Authentication in %s. Ask your server Administrator if you do not know the SMTP connection details.', 'pwebcontact'), '<a href="'.admin_url('admin.php?page=pwebcontact&task=settings').'" target="_blank">'.__('Contact Form Settings', 'pwebcontact').'</a>');
            }
        }

        return $this->requirements['mailer'];
    }

    private function _check_php_version()
	{
        if (!isset($this->requirements['php_version']))
        {
            $this->requirements['php_version'] = true;

            if (version_compare( PHP_VERSION, '5.3', '<' )) {
                $this->requirements['php_version'] = sprintf(__('This plugin requires PHP %s or higher.', 'pwebcontact' ), '5.3');
            }
        }

        return $this->requirements['php_version'];
    }

    private function _check_wp_version()
	{
        global $wp_version;

        if (!isset($this->requirements['wp_version']))
        {
            $this->requirements['wp_version'] = true;

            if (version_compare( $wp_version, '3.5', '<' )) {
                $this->requirements['wp_version'] = sprintf(__('This plugin is compatible with WordPress %s or higher.', 'pwebcontact' ), '3.5');
            }
        }

        return $this->requirements['wp_version'];
    }

}