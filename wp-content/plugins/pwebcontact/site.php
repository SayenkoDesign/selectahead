<?php
/**
 * @version 2.1.2
 * @package Perfect Easy & Powerful Contact Form
 * @copyright © 2015 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
 * @license GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @author Piotr Moćko
 */

// TODO rotate toggler with CSS
// TODO store submitted emails in database

// No direct access
function_exists('add_action') or die;


class PWebContact
{
	// multiple instances
	protected static $params 		= array();
	protected static $fields 		= array();
    protected static $forms 		= array();
	// only one instance
    protected static $settings      = null;
	protected static $data 			= array();
	protected static $email_tmpls 	= array();
	protected static $email_vars 	= array();
	protected static $logs 			= array();
	
    protected static $headers       = array();
	protected static $sys_info 		= null;
	protected static $loaded 		= array();
    

    public static function init() 
	{
        if (!isset(self::$loaded['init'])) 
		{
			self::$loaded['init'] = true;
            
            
            load_plugin_textdomain( 'pwebcontact', false, basename(dirname(__FILE__)).'/languages' );
            
            $media_path = dirname(__FILE__) . '/media/';
            $media_url = plugins_url('media/', dirname(__FILE__) . '/pwebcontact.php');
            
            $debug = ((defined('WP_DEBUG') AND WP_DEBUG === true) OR isset($_GET['debug']) OR get_option('pwebcontact_debug', false));
            define('PWEBCONTACT_DEBUG', $debug);
            
            // Register scripts
            wp_register_script('pwebcontact-bootstrap', $media_url.'bootstrap/js/bootstrap'.($debug ? '' : '.min').'.js', array('jquery'), '3.3.5', true);
            wp_register_script('pwebcontact-bootstrap-2', $media_url.'bootstrap-2.3.2/js/bootstrap'.($debug ? '' : '.min').'.js', array('jquery'), '2.3.2', true);
            
            
            wp_register_script('pwebcontact-jquery-cookie', $media_url.'js/jquery.cookie'.($debug ? '' : '.min').'.js', array('jquery'), '1.4.1', true);
            
            wp_register_script('pwebcontact-jquery-validate', $media_url.'js/jquery.validate'.($debug ? '' : '.min').'.js', array('jquery'), '1.13.1', true);
            
            wp_register_script('pwebcontact', $media_url.'js/jquery.pwebcontact'.(file_exists($media_path.'js/jquery.pwebcontact.js') ? '' : '.min').'.js', array('jquery'), null, true);
            
            
            // Register styles
            wp_register_style('pwebcontact-bootstrap', $media_url.'bootstrap/css/bootstrap'.($debug ? '' : '.min').'.css', array(), '3.3.5');
            wp_register_style('pwebcontact-bootstrap-theme', $media_url.'bootstrap/css/bootstrap-theme'.($debug ? '' : '.min').'.css', array(), '3.3.5');
            wp_register_style('pwebcontact-bootstrap-2', $media_url.'bootstrap-2.3.2/css/bootstrap'.($debug ? '' : '.min').'.css', array(), '2.3.2');
            wp_register_style('pwebcontact-bootstrap-2-responsive', $media_url.'bootstrap-2.3.2/css/bootstrap-responsive'.($debug ? '' : '.min').'.css', array(), '2.3.2');
            wp_register_style('pwebcontact-bootstrap-custom', $media_url.'css/bootstrap-custom.css');
            wp_register_style('pwebcontact-bootstrap-custom-rtl', $media_url.'css/bootstrap-custom-rtl.css');

            wp_register_style('pwebcontact-glyphicon', $media_url.'css/glyphicon.css', array(), '3.3.5');

            wp_register_style('pwebcontact-layout', $media_url.'css/layout.css');
            wp_register_style('pwebcontact-layout-rtl', $media_url.'css/layout-rtl.css');
            
            wp_register_style('pwebcontact-animations', $media_url.'css/animations.css');
            
            
            wp_register_style('pwebcontact-custom', $media_url.'css/custom.css');
            
            // Register styles for Internet Explorer
            wp_register_style('pwebcontact-ie8', $media_url.'css/ie8.css');
            wp_style_add_data('pwebcontact-ie8', 'conditional', 'lt IE 9');
            
            
            // get forms and initialize them
            $position = null;
            $settings = self::getSettings();
            if (!$settings->get('force_init', 0)) {
                // get forms published in footer
                $position = 'footer';
            }
            $forms = self::getForms($position);
            foreach ($forms as $form) {
                self::initForm($form->id, $form->position);
            }
            
            // check if there are any forms published and load IE CSS in header
            add_action('wp_head', array('PWebContact', 'displayHeader'));
            
            // TODO check if there is any form published not in footer with option to load CSS in header on all pages
        }
    }
    
    
    protected static function getForms($position = null)
    {
        global $wpdb;
        
        $sql =  $wpdb->prepare('SELECT `id`, `position` '.
                    'FROM `'.$wpdb->prefix.'pwebcontact_forms` '.
                    'WHERE `publish` = %d', 1)
                . ($position ? ' AND `position` = "' . addslashes($position) . '"' : '');

        return $wpdb->get_results($sql);
    }
    
    
    public static function displayFormsInFooter()
    {
        $forms = self::getForms('footer');
        foreach ($forms as $form) {
            self::displayForm($form->id);
        }
    }
    
    
    public static function displayFormByShortcode($atts, $content = null, $tag) {
        
        extract( shortcode_atts( array (
            'id' => 0
        ), $atts ) );
        
        $output = '';
        
        if ($id AND self::initForm($id, 'shortcode') !== false AND self::isFormReady($id)) {
            ob_start();
            self::displayForm($id);
            $output = ob_get_clean();
        }
        
        return $output;
    }
    
    
    public static function displayForm($form_id = 0) 
	{
		if (!isset(self::$forms[$form_id]) OR self::$forms[$form_id] === false) {
            // form is not initialized or disabled
            return;
        }
        
        $params = self::getParams($form_id);
        
        // Get JavaScript init code
        $script = self::getScript($form_id);

        // Load fields
        $fields = self::getFields($form_id);
        
        $layout = $params->get('layout_type', 'slidebox');

        // Display HTML code
        require (dirname(__FILE__) .'/tmpl/'. $params->get('layout', 'default') .'.php'); //WP
                
        // Disable form to load it only once
        self::$forms[$form_id] = false;
    }
    
    
    public static function isFormReady($form_id = 0) 
	{
		return (isset(self::$forms[$form_id]) AND self::$forms[$form_id] === true);
    }

	public static function initForm($form_id = 0, $position = 'footer') 
	{
		if (isset(self::$forms[$form_id])) {
            // form already initialized
            return null;
        }
        
        if (!is_object( $params = self::getParams($form_id) )) {
            
            // disable form, it does not exists in database
            self::$forms[$form_id] = false;
            return false;
        }
        $params->def('debug', PWEBCONTACT_DEBUG);
        
        if ($params->get('position', 'footer') !== $position) {
            // form is assigned to another position
            return false;
        }
        
        // Get layout name
        $layout = $params->get('layout_type', 'slidebox');
        
        // TODO Show or hide module on Mobile browser
        
        if (!$params->get('publish')) { //WP
            // form is disabled
            self::$forms[$form_id] = false;
            return false;
        }
        
        // Load form
        self::$forms[$form_id] = true;
        
        // Position and offset
        $toggler_position = explode(':', $params->get('toggler_position', 'left:top'));
        $params->set('toggler_position', $toggler_position[0]);
        $params->def('toggler_offset_position', array_key_exists(1, $toggler_position) ? $toggler_position[1] : 'top');

        // Auto RTL
        if ($params->get('rtl', 2) == 2) {
            if (!is_rtl())
                $params->set('rtl', 0);
            else {
                switch ($params->get('toggler_position')) {
                    case 'left':
                        $params->set('toggler_position', 'right');
                        break;
                    case 'right':
                        $params->set('toggler_position', 'left');
                        break;
                    case 'top':
                    case 'bottom':
                        switch ($params->get('toggler_offset_position')) {
                            case 'left':
                                $params->set('toggler_offset_position', 'right');
                                break;
                            case 'right':
                                $params->set('toggler_offset_position', 'left');
                        }
                }
                $params->set('toggler_rotate', 0 - $params->get('toggler_rotate', 1));
            }
        }

        // Set static position for static and accordion layouts
        if (in_array($layout, array('static', 'accordion'))) {
            $params->set('toggler_position', 'static');
        }
        
        // Disable vertical toggler if position is not left or right
        if (!in_array($params->get('toggler_position'), array('left', 'right'))) {
            $params->set('toggler_vertical', 0);
        }
        // Disable sliding of toggler if it is not vertical and position is left or right
        elseif (!$params->get('toggler_vertical', 0)) {
            $params->set('toggler_slide', 0);
        }

        // Toggler position
        if ($layout == 'slidebox') {
            if ($params->get('handler', 'tab') == 'hidden') {
                $params->set('toggler_vertical', 0);
                $params->set('toggler_slide', 0);
            }
        }
        elseif ($layout == 'accordion') {
            if ($params->get('handler', 'tab') == 'button') {
                $params->def('toggler_offset_position', 'fixed');
            }
        }

        // Disable auto-open for static layout
        if ($layout == 'static') {
            $params->set('open_toggler', 0);
        }

        // Toggler tab name
        $toggler_name = explode('|', $params->get('toggler_name', 'Contact form')); //WP
        $params->def('toggler_name_open', str_replace('"', '', __($toggler_name[0], 'pwebcontact'))); //WP
        $params->def('toggler_name_close', array_key_exists(1, $toggler_name) ? str_replace('"', '', __($toggler_name[1], 'pwebcontact')) : null); //WP
        
        // Theme
        $params->set('theme', 'free');
        
        // Set media path
        $media_path = dirname(__FILE__) . '/media/'; //WP
        $params->set('media_path', $media_path);
        $media_url = plugins_url('media/', dirname(__FILE__) . '/pwebcontact.php'); //WP
        $params->set('media_url', $media_url);

        // Load CSS and JS files and JS translations
        self::initHeader($form_id);

        // Module CSS classes
        self::initCssClassess($form_id);
        
        return true;
	}
	

	public static function setLog($log) 
	{
		return self::$logs[] = $log;
	}
    
    
    protected static function recursive_stripslashes(&$input) {
        
        if (is_array($input)) {
            foreach ($input as &$item) {
                self::recursive_stripslashes($item);
            }
        }
        elseif (is_string($input)) {
            $input = stripslashes($input);
        }
    }
	

	public static function setParams(&$params) 
	{
		$form_id = (int)$params->get('id');
		self::$params[$form_id] = $params;
	}


	public static function getParams($form_id = 0, $global = false)
	{
		global $wpdb;
        
        $form_id = (int)$form_id;
		if (!isset(self::$params[$form_id]))
		{
            $sql =  $wpdb->prepare('SELECT `params`, `fields`, `publish`, `position`, `layout`, `modify_date` '.
                    'FROM `'.$wpdb->prefix.'pwebcontact_forms` '.
                    'WHERE `id` = %d', $form_id);
            
            $data = $wpdb->get_row($sql);
            
            if ($data === null) return false;
			
            $data->params = $data->params ? json_decode( $data->params, true ) : array();
            self::recursive_stripslashes($data->params);
            
			$params = new PWebContact_Params( $data->params );
			$params->def('id', (int)$form_id);
            $params->def('publish', $data->publish);
            $params->def('position', $data->position);
            $params->def('layout_type', $data->layout);
            $params->def('cache_key', md5($data->modify_date));
				
			self::$params[$form_id] = $params;
            
            
            $data->fields = $data->fields ? json_decode( $data->fields, true ) : array();
            self::recursive_stripslashes($data->fields);
            
            self::$fields[$form_id] = $data->fields;
            
            if ($global === true) {
                self::$params[0] =& self::$params[$form_id];
                self::$fields[0] =& self::$fields[$form_id];
            }
		}
		return self::$params[$form_id];
	}
    
    
    public static function getSettings()
	{
		if (self::$settings === null)
		{
            $settings_a = get_option('pwebcontact_settings', array());
            foreach ($settings_a as &$value) {
                $value = stripslashes($value);
            }
            
            self::$settings = new PWebContact_Params( $settings_a );
		}
		return self::$settings;
	}


	public static function getFields($form_id = 0) 
	{
		if (!isset(self::$fields[$form_id])) 
		{
			self::getParams($form_id);
		}
		return self::$fields[$form_id];
	}


	public static function initCssClassess($form_id = 0)
	{
		$params = self::getParams($form_id);
		
        $moduleClasses = array();
        $positionClasses = array();
        $togglerClasses = array();
        $boxClasses = array();
        
        $layout = $params->get('layout_type', 'slidebox');
        $moduleClasses[] = 'pweb-'.$layout;
        
        if (($class = $params->get('theme'))) $moduleClasses[] = 'pweb-theme-'.$class;
        
		$moduleClasses[] = 'pweb-labels-above';
        
		if ($layout != 'static') 
		{
			if (in_array($layout, array('slidebox', 'modal')))
			{
				$positionClasses[] = 'pweb-'.$params->get('toggler_position', 'left');
				$positionClasses[] = 'pweb-offset-'.$params->get('toggler_offset_position', 'top');
                
					$moduleClasses[] = 'pweb-horizontal';
				
				if ($layout == 'slidebox')
				{
					if ($params->get('handler', 'tab') == 'hidden' AND $params->get('toggler_offset_position') == 'fixed') $moduleClasses[] = 'pweb-toggler-hidden';
					if ($params->get('toggler_slide')) $moduleClasses[] = 'pweb-toggler-slide';
					if (!$params->get('debug')) $boxClasses[] = 'pweb-init';
				}
			}
			elseif ($layout == 'accordion') 
			{
				if ($params->get('accordion_boxed', 1)) $boxClasses[] = 'pweb-accordion-boxed';
				if (!$params->get('debug')) $boxClasses[] = 'pweb-init';
			}
			
            if (($class = $params->get('theme'))) $togglerClasses[] = 'pweb-theme-'.$class;
        }
        
		if ($params->get('user_data', 1) == 2) {
			$user = wp_get_current_user(); //WP
			if ($user->ID) $moduleClasses[] = 'pweb-hide-user';
		}
        
        
        if ($params->get('rtl', 0)) $moduleClasses[] = $togglerClasses[] = 'pweb-rtl';
        
		if ($moduleclass_sfx = $params->get('moduleclass_sfx')) {
			$moduleclasses_sfx = explode(' ', $moduleclass_sfx);
			for ($i = 0; $i < count($moduleclasses_sfx); $i++) 
				if (strpos($moduleclasses_sfx[$i], 'icon-') !== false) 
					unset($moduleclasses_sfx[$i]);
			$moduleClasses[] = $togglerClasses[] = htmlspecialchars(implode(' ', $moduleclasses_sfx));
		}
		
		$params->def('positionClass', implode(' ', $positionClasses));
		$params->def('togglerClass', implode(' ', $togglerClasses));
		$params->def('moduleClass', implode(' ', $moduleClasses));
		$params->def('boxClass', implode(' ', $boxClasses));
	}


	public static function compileCustomCSS($form_id = 0)
	{
		$params 		= self::getParams($form_id);
		$form_id 		= (int)$params->get('id');
		$media_url 		= $params->get('media_url');
		$layout 		= $params->get('layout_type', 'slidebox');
		$css 			= null;
		$declarations 	= array();
        
        // Form width
		if ($value = $params->get('form_width')) {
			if ($layout != 'slidebox' OR strpos($value, 'px') !== false)
				$css .= '#pwebcontact'.$form_id.'_box{max-width:'.$value.'}';
		}

		// Position offset
		if ($value = $params->get('offset'))
			$css .= '#pwebcontact'.$form_id.'{'.$params->get('toggler_offset_position', '').':'.$value.'}';

		// Layer level
		if ($value = (int)$params->get('zindex')) {
			// Slide box and Lightbox toggler
			$css .=  '#pwebcontact'.$form_id.'.pweb-left,'
					.'#pwebcontact'.$form_id.'.pweb-right,'
					.'#pwebcontact'.$form_id.'.pweb-top,'
					.'#pwebcontact'.$form_id.'.pweb-bottom'
					.'{z-index:'.$value.'}';
			// Lightbox window
			if (($layout == 'modal' OR $params->get('load_modal_backdrop')) AND $value > 1030) {
				$css .= 'body.pweb-modal-open > .modal-backdrop{z-index:'.($value+10).'}';
				$css .= '.pwebcontact-modal.modal{z-index:'.($value+20).'}';
                $css .= '.pweb-modal.modal{z-index:'.($value+21).'}';
				$css .= '.ui-effects-transfer.pweb-genie{z-index:'.($value+19).'}';
			}
		}
		
		
		if ($layout == 'slidebox' OR (in_array($layout, array('accordion', 'modal')) AND in_array($params->get('handler', 'tab'), array('button', 'tab'))) )
		{
			// Toggler
            
			if ($value = $params->get('toggler_width'))
				$declarations[] = 'width:'.(int)$value.'px';
			if ($value = $params->get('toggler_height'))
				$declarations[] = 'height:'.(int)$value.'px';
            
			if (count($declarations)) {
				$css .= '#pwebcontact'.$form_id.'_toggler{'.implode(';', $declarations).'}';
				$declarations = array();
			}
			
		}
		

        if ($layout == 'modal') 
		{
			// Modal transfer effect
			if (($value = (float)$params->get('effect_duration', 400)) !== 400) {
				$declarations[0] = 'animation-duration:'.$value.'ms';
				$declarations[] = '-o-'.$declarations[0];
				$declarations[] = '-ms-'.$declarations[0];
				$declarations[] = '-moz-'.$declarations[0];
				$declarations[] = '-webkit-'.$declarations[0];
			}
			if (isset($container_bg))
				$declarations[] = $container_bg;
			if (count($declarations)) {
                if (($class = $params->get('theme'))) 
					$css .= '.pweb-theme-'.$class;
				$css .= '.ui-effects-transfer.pweb-genie.pwebcontact'.$form_id.'-genie{'.implode(';', $declarations).'}';
				$declarations = array();
			}

            // Allow to scroll reCAPTCHA v2 images
            if ($params->get('captcha') == 'grecaptcha')
                $css .= '#pwebcontact'.$form_id.'_box{margin-bottom:600px !important}';
		}
		
		// Disable Boostrap 2 Glyphicons
		if (!$params->get('boostrap2_glyphicons', 0))
			$css .= '[class^="icon-"],[class*=" icon-"]{background-image:none !important}';

        unset($declarations);

		return $css;
	}


	public static function initHeader($form_id = 0) 
	{
		$params 	= self::getParams($form_id);
		$media_url 	= $params->get('media_url');
		$media_path = $params->get('media_path');
		$layout 	= $params->get('layout_type', 'slidebox');
		$debug 		= $params->get('debug');
        $bootstrap  = false;

        
		// jQuery
		if ($params->get('load_jquery', 1)) {
            wp_enqueue_script('jquery');
		}
        // Bootstrap JS
		if ($params->get('load_bootstrap', 1)) {
            if ($params->get('bootstrap_version', 2) == 2) {
                wp_enqueue_script('pwebcontact-bootstrap-2');
                $bootstrap = 2;
            }
            elseif ($params->get('bootstrap_version', 2) == 3) {
                wp_enqueue_script('pwebcontact-bootstrap');
                $bootstrap = 3;
            }
		}
		
		// Bootstrap CSS
		if ($params->get('load_bootstrap_css', 2) == 1) {
            
			if ($params->get('bootstrap_version', 2) == 2) {
                wp_enqueue_style('pwebcontact-bootstrap-2');
                wp_enqueue_style('pwebcontact-bootstrap-2-responsive');
            }
            elseif ($params->get('bootstrap_version', 2) == 3) {
                wp_enqueue_style('pwebcontact-bootstrap');
                wp_enqueue_style('pwebcontact-bootstrap-theme');
            }
		}
		elseif ($params->get('load_bootstrap_css', 2) == 2) {
            wp_enqueue_style('pwebcontact-bootstrap-custom');
            if ($params->get('rtl', 0)) 
                wp_enqueue_style('pwebcontact-bootstrap-custom-rtl');
		}


		// CSS layout
		wp_enqueue_style('pwebcontact-layout');
		if ($params->get('rtl', 0))
			wp_enqueue_style('pwebcontact-layout-rtl');

        
		wp_enqueue_style('pwebcontact-animations');


        if ($layout == 'slidebox') 
		{
			if (($effect = $params->get('effect_transition')) > 0 AND strpos($effect, 'ease') !== false AND $params->get('load_jquery_ui', 1)) {
				wp_enqueue_script('jquery-ui-core');
			}
		}
		elseif ($layout == 'accordion' OR ($layout == 'modal' AND $params->get('effect') != 'modal:fade' AND $params->get('effect') != 'modal:drop'))
		{
			if ($params->get('load_jquery_ui_effects', 1)) {
				wp_enqueue_script('jquery-effects-core');
                wp_enqueue_script('jquery-effects-transfer');
			}
			if ($layout == 'modal') {
				wp_enqueue_style('pwebcontact-animations');
			}
		}
        
        // Load jQuery Cookie for auto-open count
        if ($params->get('open_toggler') AND $params->get('open_count') AND $params->get('load_jquery_cookie', 1)) {
			wp_enqueue_script('pwebcontact-jquery-cookie');
        }
        
        // Load jQuery Form Validator
		if ($params->get('load_jquery_validate', 1)) 
			wp_enqueue_script('pwebcontact-jquery-validate');
		
		wp_enqueue_script('pwebcontact');


		// CSS styles
        if (($theme = $params->get('theme')) !== null AND file_exists($media_path.'css/themes/'.$theme.'.css')) {
            wp_register_style('pwebcontact-theme-'.$theme, $media_url.'css/themes/'.$theme.'.css');
			wp_enqueue_style('pwebcontact-theme-'.$theme);
        }
		
        // Custom styles
        if (is_file($media_path.'css/custom.css')) {
            wp_enqueue_style('pwebcontact-custom');
        }
        
		// Set theme custom styles
        if ($params->get('cache_css', 1))
        {
            $path = $params->get('media_path').'cache/';
            $file = $params->get('cache_key').'-'.$form_id.'.css';
            
            require_once ABSPATH . 'wp-admin/includes/file.php';

            if (function_exists('WP_Filesystem') AND WP_Filesystem()) {
            
                global $wp_filesystem;
                
                if (!$wp_filesystem->is_file($path . $file)) {

                    $css = self::compileCustomCSS($form_id);

                    // set write permissions to cache folder
                    if (!$wp_filesystem->is_writable($path)) {
                        $wp_filesystem->chmod($path, 0777);
                    }

                    // write cache file
                    if (!$wp_filesystem->put_contents($path.$file, $css)) {
                        wp_add_inline_style('pwebcontact', $css);
                        $file = false;
                    }
                    else {
                        // delete old cached files
                        $dir = $wp_filesystem->dirlist($path);
                        foreach ($dir as $item) {
                            if ($item['name'] !== $file AND preg_match('/^[a-f0-9]{32}\-'.$form_id.'\.css$/i', $item['name']) === 1) {
                                $wp_filesystem->delete( $path . $item['name'] );
                            }
                        }
                    }
                }
            }
            else {
                
                if (!is_file($path . $file)) {

                    $css = self::compileCustomCSS($form_id);

                    // set write permissions to cache folder
                    if (!is_writable($path)) {
                        chmod($path, 0777);
                    }

                    // write cache file
                    if (!file_put_contents($path.$file, $css)) {
                        wp_add_inline_style('pwebcontact', $css);
                        $file = false;
                    }
                    else {
                        // delete old cached files
                        if (is_dir($path)) {
                            $dir = new DirectoryIterator($path);
                            foreach ($dir as $fileinfo) {
                                if ($fileinfo->isFile() AND $fileinfo->getFilename() !== $file AND preg_match('/^[a-f0-9]{32}\-'.$form_id.'\.css$/i', $fileinfo->getFilename()) === 1) {
                                    unlink( $fileinfo->getPathname() );
                                }
                            }
                        }
                    }
                }
            }
            
            if ($file !== false) {
                wp_register_style('pwebcontact-'.$file, $media_url.'cache/'.$file);
                wp_enqueue_style('pwebcontact-'.$file);
            }
        }
        else {
            $css = self::compileCustomCSS($form_id);
            wp_add_inline_style('pwebcontact', $css);
        }
        
        // CSS IE
		wp_enqueue_style('pwebcontact-ie8');


		if (!isset(self::$loaded['text'])) 
		{
			self::$loaded['text'] = true;
			
            wp_localize_script('pwebcontact', 'pwebcontact_l10n = pwebcontact_l10n || {}; pwebcontact_l10n.form', array(
                'INIT'          => __('Initializing form...', 'pwebcontact'), //TODO is rather unused
                'SENDING'       => __('Sending...', 'pwebcontact'),
                'SEND_ERR'      => __('Wait a few seconds before sending next message', 'pwebcontact'),
                'REQUEST_ERR'   => __('Request error: ', 'pwebcontact'),
                'COOKIES_ERR'   => __('Enable cookies and refresh page to use this form', 'pwebcontact')
            ));
		}
	}
    
    
    public static function displayHeader()
    {
        global $wpdb;
        
        if (!isset(self::$loaded['ie_css'])) 
		{
			self::$loaded['ie_css'] = true;
            
            $sql =  $wpdb->prepare('SELECT COUNT(`id`) '.
                    'FROM `'.$wpdb->prefix.'pwebcontact_forms` '.
                    'WHERE `publish` = %d', 1);

            if ($wpdb->get_var($sql) > 0)
            {
                $media_url = plugins_url('media/', dirname(__FILE__) . '/pwebcontact.php'); //WP

                echo
                     '<!--[if IE 8]>'."\r\n"
                    .'<style type="text/css">'
                        .'.pwebcontact-form .pweb-input,'
                        .'.pwebcontact-form select,'
                        .'.pwebcontact-form textarea,'
                        .'.pwebcontact-form .btn'
                        .'{behavior:url('.$media_url.'css/PIE.htc)}'
                    .'</style>'."\r\n"
                    .'<![endif]-->'
                    ."\r\n";

                echo
                     '<!--[if lt IE 9]>'."\r\n"
                    //.'<link rel="stylesheet" href="'.$media_url.'css/ie8.css" />'."\r\n"
                    .'<style type="text/css">'
                        .'.pwebcontact_toggler,'
                        .'.pwebcontact-container'
                        .'{behavior:url('.$media_url.'css/PIE.htc)}'
                    .'</style>'."\r\n"
                    .'<![endif]-->'
                    ."\r\n";
            }
		}
    }


	public static function getScript($form_id = 0) 
	{
		$params = self::getParams($form_id);
		
		$layout 	= $params->get('layout_type', 'slidebox');
		$position 	= $params->get('toggler_position', 'left');
		
		$options = array();	
		$options[] = 'id:'.$form_id;
		
		if ($params->get('debug', 0))
			$options[] = 'debug:1';
		if ($params->get('cache', 0) == 2)
			$options[] = 'reloadToken:1';
		if (($value = (int)$params->get('bootstrap_version', 2)) != 2)
			$options[] = 'bootstrap:'.$value;
		
		$options[] = 'ajaxUrl:"'.admin_url( 'admin-ajax.php?action=pwebcontact_', is_ssl() ? 'https' : 'http' ).'"';
		
        if ($value = $params->get('theme'))
			$options[] = 'theme:"'.$value.'"';
        
		$options[] = 'layout:"'.$layout.'"';
		$options[] = 'position:"'.$position.'"';
		$options[] = 'offsetPosition:"'.$params->get('toggler_offset_position').'"';
		
        if (!$params->get('msg_scroll', 1))
			$options[] = 'msgScroll:0';
		if (($value = $params->get('msg_position', 'after')) != 'after')
			$options[] = 'msgPosition:"'.$value.'"';
		if (($value = (int)$params->get('msg_close_delay', 10)) != 10)
			$options[] = 'msgCloseDelay:'.$value;
		
		if (($value = (intval($params->get('tooltips_focus', 1)) | (intval($params->get('tooltips_validation', 1)) << 1)) ) !== 3)
			$options[] = 'tooltips:'.$value;
		
		if ($value = $params->get('toggler_name_close') AND !$params->get('toggler_vertical', 0))
			$options[] = 'togglerNameClose:"'.$value.'"';
		
		
		if (($open = (int)$params->get('open_toggler')) > 0)
		{
			$max_count = (int)$params->get('open_count');
			if ($max_count == 0) {
				$options[] = 'openAuto:'.$open;
			} elseif ($max_count > 0) {
                // cookie
                 $options[] = 'openAuto:'.$open;
                $options[] = 'maxAutoOpen:'.$max_count;
                if (($value = (int)$params->get('cookie_lifetime', 30)) != 30)
                    $options[] = 'cookieLifetime:'.($value*3600*24);
                if (($value = home_url('', 'relative')) != '/') //WP
                    $options[] = 'cookiePath:"'.$value.'"';
                //$options[] = 'cookieDomain:"'.str_replace('www.', '', $_SERVER['HTTP_HOST']).'"';
			}
			if (($value = (int)$params->get('open_delay')) > 0) {
				$options[] = 'openDelay:'.$value;
			}
		}
		
		if ($params->get('close_toggler', 0))
			$options[] = 'closeAuto:1';
		if (($value = (int)$params->get('close_delay')) > 0)
			$options[] = 'closeDelay:'.$value;
		
		if (!$params->get('close_other', 1))
			$options[] = 'closeOther:0';
		
		// reset form after email has been sent
		if (($value = (int)$params->get('reset_form', 1)) != 1)
			$options[] = 'reset:'.$value;
		
		// redirect after email has been sent
		if ($value = $params->get('redirect_url')) {
			$options[] = 'redirectURL:"'.$value.'"';
			if (($value = (int)$params->get('redirect_delay', 5)) != 5) {
				$options[] = 'redirectDelay:'.$value;
			}
		}
		
		
		// On complete event
		$options2 = array();
		// Google AdWords Conversion Tracking
		if ($value = $params->get('adwords_url')) {
			$options2[] = '$("<img/>",{"src":"'.$value.'","width":1,"height":1,"border":0}).appendTo(this.Msg);';
		}
		// Microsoft adCenter Conversion Tracking
		if ($value = $params->get('adcenter_url')) {
			$options2[] = '$("<iframe/>",{"src":"'.$value.'","width":1,"height":1,"frameborder":0,"scrolling":"no"}).css({"visibility":"hidden","display":"none"}).appendTo(this.Msg);';
		}
		// After email sent success
		if ($value = $params->get('oncomplete')) {
			$options2[] = 'try{'.strip_tags($value)."\r\n".'}catch(e){this.debug(e)}';
		}
		if (count($options2)) $options[] = 'onComplete:function(data){'.implode('', $options2).'}';
		
		// On error event
		if ($value = $params->get('onerror')) {
			$options[] = 'onError:function(data){try{'.strip_tags($value)."\r\n".'}catch(e){this.debug(e)}}';
		}
		
		// On load, open and close events
		if ($value = $params->get('onload')) {
			$options[] = 'onLoad:function(){try{'.strip_tags($value)."\r\n".'}catch(e){this.debug(e)}}';
		}
		if ($value = $params->get('onopen')) {
			$options[] = 'onOpen:function(){try{'.strip_tags($value)."\r\n".'}catch(e){this.debug(e)}}';
		}
		if ($value = $params->get('onclose')) {
			$options[] = 'onClose:function(){try{'.strip_tags($value)."\r\n".'}catch(e){this.debug(e)}}';
		}
		
		
		// Slide Box
		if ($layout == 'slidebox') 
		{
			// Form width
			if (($value = $params->get('form_width')) AND strpos($value, 'px') !== false)
				$options[] = 'slideWidth:'.(int)$value;
			if (($value = (int)$params->get('effect_duration')) > 0) //NEW
				$options[] = 'slideDuration:'.$value;
			if (($value = $params->get('effect_transition')) != -1 AND $value != -2 AND $value) { //NEW
				$options[] = 'slideTransition:"'.$value.'"';
			}
		}
		// Lightbox window
		else if ($layout == 'modal') 
		{
			if ($params->get('modal_disable_close'))
				$options[] = 'modalClose:0';
			if (($value = $params->get('effect_duration', 400)) != 400)
				$options[] = 'modalEffectDuration:'.(int)$value;
			if (($value = $params->get('effect', 'modal:fade')) != 'modal:fade')
				$options[] = 'modalEffect:"'.substr($value, strpos($value, ':')+1).'"';
		}
        // Accordion
		else if ($layout == 'accordion') 
		{
			if (($value = $params->get('effect_duration', 400)) != 500)
				$options[] = 'accordionDuration:'.(int)$value;
		}
        
        
		// Custom validation rules and calendar fields
		$fields = self::getFields($form_id);
		$rules = $calendars = array();
		foreach ($fields as $field)
		{
			
            if (isset($field['validation']) AND $field['validation']) 
			{
				$options2 = array('name:"'.$field['alias'].'"', 'regexp:'.str_replace('\\\\', '\\', $field['validation'])); //WP
				$rules[] = '{'.implode(',',$options2).'}';
			}
		}
		if (count($rules)) {
			$options[] = 'validatorRules:['.implode(',',$rules).']';
		}
		
		
		// JavaScript initialization
		$script = 
		'jQuery(document).ready(function($){'.
			'pwebContact'.$form_id.'=new pwebContact({'.implode(',', $options).'})'. 
		'});';
		
		
		if ($params->get('debug') AND !isset(self::$loaded['debug_js']))
		{
			self::$loaded['debug_js'] = true;
			
			$script = 
			'jQuery(document).ready(function($){'.
				'if(typeof pwebContact'.$form_id.'Count=="undefined"){'.
					// Check if document header has been loaded
					'if(typeof pwebContact=="undefined")alert("Perfect Contact Form Debug: Contact form module has been loaded incorrect.");'.
					// Check if one module instance has been loaded only once
					'pwebContact'.$form_id.'Count=$(".pwebcontact'.$form_id.'_form").length;'.
					'if(pwebContact'.$form_id.'Count>1)'.
						'alert("Perfect Contact Form Debug: Contact form module ID '.$form_id.' has been loaded "+pwebContact'.$form_id.'Count+" times. You can have multiple contact forms, but one instance of module can be loaded only once!")'.
				'}'.
			'});'.
			$script
			;
		}

		return $script;
	}




	public static function getHiddenFields($form_id = 0) 
	{
		$params = self::getParams($form_id);
		$html = '';
		
		// CMS and extension version
		if ($params->get('debug') AND ($info = self::getSystemInfo())) {
			$html .= "\r\n<!-- ".implode(', ', $info)." -->\r\n";
		}
		
		return $html;
	}
    
    
    public static function setHeader($name, $value, $replace = false) 
    {
		if (!isset(self::$headers[$name]) OR $replace)
		{
			self::$headers[(string)$name] = (string)$value;
		}
    }
    
    
    public static function getHeaders() 
    {
		return self::$headers;
    }
	
	
	public static function initAjaxResponse() 
	{
		global $wp_version;
        
        if (function_exists('exceptions_error_handler'))
			@set_error_handler('exceptions_error_handler');
		
		$form_id = isset($_POST['mid']) ? (int)$_POST['mid'] : (isset($_GET['mid']) ? (int)$_GET['mid'] : 0);
		$params = self::getParams($form_id, true);
		
		// Language
		if ($params->get('rtl', 2) == 2) {
			if (is_rtl()) //WP
				$params->set('rtl', 1);
			else
				$params->set('rtl', 0);
		}
		
		// Debug
		if (isset($_POST['debug']) AND (int)$_POST['debug'] === 1) $params->set('debug', 1); //WP
		if (!defined('PWEBCONTACT_DEBUG')) define('PWEBCONTACT_DEBUG', $params->get('debug'));
		
		if (PWEBCONTACT_DEBUG) {
			self::$logs[] = 'WordPress version '.$wp_version;
			self::$logs[] = 'Contact Form version '.self::getVersion();
			self::$logs[] = 'Ajax response';
		}
		
		// Set media path
		$params->set('media_url', 	plugins_url('media/', dirname(__FILE__) . '/pwebcontact.php')); //WP
		$params->set('media_path', 	dirname(__FILE__) . '/media/'); //WP
        
	}


	public static function closeAjaxResponse( $data = array() ) 
	{
		if (defined('PWEBCONTACT_DEBUG') AND PWEBCONTACT_DEBUG) 
		{
			self::$logs[] = 'Ajax response exit';
		}
        
        $data['debug'] = null;
        if (count(self::$logs)) {
            $data['debug'] = self::$logs;
            foreach ($data['debug'] as &$value) {
                $value = esc_html($value);
            }
        }
        
        // Internet Explorer < 10
		if (!isset($_SERVER['HTTP_ACCEPT']) OR strpos($_SERVER['HTTP_ACCEPT'], 'application/json') === false) {
			// Change response Content-Type
			self:setHeader('Content-Type', 'text/plain', true);
		}
        
        // Send headers
        foreach (self::getHeaders() as $header => $value) {
            header($header . ($value ? ': '.$value : ''));
        }
        
        // Response
        $response = new stdClass();
        $response->success = true;
        $response->message = null;
        $response->data = $data;
        
		echo json_encode($response);
        
        die();
	}


	public static function checkToken()
	{
		$response = true;
        
        //TODO refresh token before each request
		return $response;
        
        $form_id = isset($_POST['mid']) ? (int)$_POST['mid'] : 0;
		$token = wp_create_nonce('pwebcontact'.$form_id);
        
		try {
			if (!isset($_POST[$token]) OR (int)$_POST[$token] !== 1)
			{
                $response = array('status' => 302, 'msg' => __('Invalid security token. Refresh page and try again', 'pwebcontact'));
			}
		} catch (Exception $e) {
			$response = array('status' => 302, 'msg' => __('WordPress error', 'pwebcontact'), 'debug' => array($e->getMessage().' in '.$e->getFile().' on line '.$e->getLine()));
		}
		
		return $response;
	}


	public static function getTokenAjax() 
	{
        $form_id = isset($_POST['mid']) ? (int)$_POST['mid'] : 0;
        
		$response = array('status' => 103, 'token' => wp_create_nonce('pwebcontact'.$form_id));
        
        self::closeAjaxResponse($response);
	}


    
	


	public static function sendEmailAjax() 
	{
		self::initAjaxResponse();
		if (($response = self::checkToken()) !== true) return $response;
		
		if (PWEBCONTACT_DEBUG) self::$logs[] = 'Sending emails';
		
		$params = self::getParams();
		
		try {			
			$response = self::sendEmail();
		} catch (Exception $e) {
			self::$logs[] = $e->getMessage().' in '.$e->getFile().' on line '.$e->getLine();
			$response = array('status' => 300, 'msg' => __('WordPress error', 'pwebcontact'));
		}
		
		
		self::closeAjaxResponse($response);
	}
    
    
    public static function setupMailer($phpmailer) 
    {
        $settings = self::getSettings();
        
        if ($settings->get('mailer') == 'smtp' AND $settings->get('smtp_host'))
        {
            $phpmailer->isSMTP();
            $phpmailer->Host = $settings->get('smtp_host');
            $phpmailer->Port = $settings->get('smtp_port', 25);
            
            $phpmailer->SMTPSecure = $settings->get('smtp_secure', 'none');
            $phpmailer->SMTPAuth = ($settings->get('smtp_username') AND $settings->get('smtp_password'));
            
            if ($phpmailer->SMTPAuth)
            {
                $phpmailer->Username = $settings->get('smtp_username');
                $phpmailer->Password = $settings->get('smtp_password');
            }
        }
        elseif ($settings->get('mailer') == 'mail') 
        {
            $phpmailer->isMail();
        }
    }
    

	public static function sendEmail() 
	{		
		add_action('phpmailer_init', array('PWebContact', 'setupMailer'));
        
        $user 		= wp_get_current_user();
		$params 	= self::getParams();
        $settings   = self::getSettings();
		$form_id 	= (int)$params->get('id');
		
		// mail from
		$global_name  = trim( $params->get( 'email_from_name', $settings->get('email_from_name', get_bloginfo('name')) ) );
		$global_email = $params->get( 'email_from', $settings->get('email_from', get_bloginfo('admin_email')) );
		if (!$global_email) {
			if (PWEBCONTACT_DEBUG) self::$logs[] = 'Invalid Global Configuration';
			return array('status' => 303, 'msg' => __('MOD_PWEBCONTACT_GLOBAL_CONFIG_ERR', 'pwebcontact'));
		}
		
		$data 		=& self::$data;
		$email_vars =& self::$email_vars;
		
		// Get inputs
		$data = array(
			'fields'			=> isset($_POST['fields'])              ? (array)$_POST['fields'] : array(),
			'mailto'			=> isset($_POST['mailto'])              ? (int)$_POST['mailto'] : null,
			'title' 			=> isset($_POST['title'])               ? (string)$_POST['title'] : null,
			'url' 				=> isset($_POST['url'])                 ? (string)$_POST['url'] : null,
            'screen_resolution' => isset($_POST['screen_resolution'])   ? (string)$_POST['screen_resolution'] : null,
			'attachments' 		=> isset($_POST['attachments'])         ? (array)$_POST['attachments'] : array()
		);

		$data['ip_address'] 	= self::detectIP();
		$data['browser'] 		= self::detectBrowser();
		$data['os'] 			= self::detectOS();
		
		$data['user_id'] 		= $user->ID; //WP
		$data['user_subject'] 	= '';
		
		// init email variables
		$email_vars = array(
			'name'				=> '',
			'email'				=> '',
			'username' 			=> $user->display_name, //WP
            'ip_address' 		=> 'Requires PRO version',
			'browser' 			=> 'Requires PRO version',
			'os' 				=> 'Requires PRO version',
			'screen_resolution' => 'Requires PRO version',
			'mailto_name'		=> 'Requires PRO version',
			'ticket'			=> 'Requires PRO version',
            'url' 				=> $data['url'],
			'title' 			=> $data['title'],
			'site_name' 		=> get_bloginfo('name') //WP
		);
		
		$fields = self::getFields($form_id);
		
		$user_email = null;
		$user_name 	= null;
		$user_cc 	= array();
		$email_to 	= array();
		
		$invalid_fields = array();
		
		// init email variables for fields and validate them
		foreach ($fields as $field)
		{
			// skip all separators which does not have any data
			if (in_array($field['type'], array('page', 'row', 'column', 'button_send', 'email_copy', 'captcha', 'custom_text', 'header'))) continue;
			
                // get field from request
                if (isset($data['fields'][$field['alias']])) {
                    $value = $data['fields'][$field['alias']];
                    if (is_string($value)) {
                        $value = stripslashes( $value );
                    }
                    elseif (is_array($value)) {
                        foreach ($value as &$val) {
                            $val = stripslashes($val);
                        }
                    }
                    $data['fields'][$field['alias']] = $value;
                } 
                else {
                    $data['fields'][$field['alias']] = $value = null;
                }

                // is required
                if (isset($field['required']) AND $field['required'] AND ($value === null OR $value === '')) {
                    // required field is empty
                    $invalid_fields[] = 'field-'.$field['alias'];
                    continue;
                }

                if ($field['type'] == 'email') 
                {
                    // Validate email
                    if ($value AND is_email($value) === false) {
                        $invalid_fields[] = 'field-'.$field['alias'];
                    } else {
                        if (!$user_email) 
                            $email_vars['email'] = $user_email = $value;
                        else 
                            $user_cc[] = $value;
                    }
                }
                elseif ($field['type'] == 'checkbox' OR $field['type'] == 'checkbox_modal') 
                {
                    $data['fields'][$field['alias']] = $value ? 'Yes' : 'No';
                }
                else 
                {
                    if ($field['type'] == 'name') {
                        if (!$user_name) 
                            $email_vars['name'] = $user_name = $value;
                    }
                    elseif ($field['type'] == 'subject') {
                        $data['user_subject'] .= ' '.$value;
                    }

                    // validate fields with regular expression
                    if (in_array($field['type'], array('text', 'name', 'phone', 'subject', 'password')) AND isset($field['validation']) AND $field['validation'] AND $value 
                            AND !preg_match($field['validation'], $value)) { //WP
                        $invalid_fields[] = 'field-'.$field['alias'];
                    }
                }
		}
		
		if ($data['mailto'] === 0) {
			$data['mailto'] = null;
		}
		
		// invalid fields
		if (count($invalid_fields)) {
			if (PWEBCONTACT_DEBUG) self::$logs[] = 'Invalid fields';
			return array('status' => 200, 'msg' => __('Invalid fields values', 'pwebcontact'), 'invalid' => $invalid_fields);
		}
		
		
		// CMS
		if ($params->get('email_to')) {
			$email_to = array_merge($email_to, explode(',', $params->get('email_to')));
		}
		if ($params->get('email_cms_user')) 
		{
			$user_info = get_userdata( (int)$params->get('email_cms_user') );
            if ($user_info !== false AND $user_info->email) {
                $email_to[] = $user_info->email;
            }
		}
		
		if (!count($email_to)) {
			$email_to[] = $global_email;
		}
		
		
		// ticket
		$data['ticket'] = '';
		
		// success message
		if (!isset($success_msg)) $success_msg = __($params->get('msg_success', 'Message successfully sent', 'pwebcontact'));
		// clean subject
		//$success_msg = str_replace(array('"','\\'), '', $success_msg);
		
		// email subject
		if (!isset($data['subject'])) {
            $data['subject'] = __($params->get('email_subject', 'Message sent from'), 'pwebcontact');
        }
        
        
		// email subject suffix
		switch ($params->get('email_subject_sfx', 2))
		{
			case 1:
				$data['subject'] = trim($data['subject']) .' '. $email_vars['site_name'];
				break;
			case 2:
				$data['subject'] = trim($data['subject']) .' '. $data['title'];
		}
        
        $email_copy = ($params->get('email_copy', 2) == 2);
        
		// User email copy or auto-reply
		if ($user_email AND $email_copy) 
		{
			$headers = array();
            
            // set sender
            $headers[] = 'From: ' . ( $global_name ? $global_name.' <'.$global_email.'>' : $global_email );
			if (PWEBCONTACT_DEBUG) 
            {
                self::$logs[] = 'User email: '.$user_email;
                self::$logs[] = 'User email subject: '.$data['subject'];
                self::$logs[] = 'User email sender: '. ( $global_name ? $global_name.' <'.$global_email.'>' : $global_email );
            }
            
			// set reply to
			if ($replyto_email = $params->get('email_replyto')) 
			{
                $replyto_name = trim( $params->get('email_replyto_name', $global_name) );
                $headers[] = 'Reply-To: '. ( $replyto_name ? $replyto_name.' <'.$replyto_email.'>' : $replyto_email );
                if (PWEBCONTACT_DEBUG) {
                    self::$logs[] = 'User email reply-to: '. ( $replyto_name ? $replyto_name.' <'.$replyto_email.'>' : $replyto_email );
                }
			}
            
			// Add carbon copy recipients
			if (count($user_cc))
			{
				$headers[] = 'CC: '.implode(', ', $user_cc);
				if (PWEBCONTACT_DEBUG) self::$logs[] = 'User CC recipients: '.implode(', ', $user_cc);
			}
			
            // TODO option to include or not attachments in email to user
            $attachments = array();

            // set email format
            $is_html = ((int)$params->get('email_user_tmpl_format', 1) == 2);
            $headers[] = 'Content-Type: '.($is_html ? 'text/html' : 'text/plain');

            // load email body template
            $body = $params->get('email_user_tmpl');

            self::parseTmplVars($body, $is_html, get_bloginfo('language'));

            // set body text direction
            $body = ($params->get('rtl', 0) ? "\xE2\x80\x8F" : "\xE2\x80\x8E") . $body;

            if (PWEBCONTACT_DEBUG) self::$logs[] = 'User email ready';

            // send User email
            $result = wp_mail($user_email, $data['subject'], $body, $headers, $attachments);

            if ($result !== true) 
            {
                return array('status' => 305, 'msg' => __('Error sending email to user', 'pwebcontact'));
            } 
            elseif (PWEBCONTACT_DEBUG) self::$logs[] = 'User email sent successfully';
		}


		// Demo: do not send email to Admin
		if ($params->get('demo', 0)) {
			if (PWEBCONTACT_DEBUG) self::$logs[] = 'DEMO mode is not sending email to Admin';
			return array('status' => 102, 'msg' => $success_msg, 'ticket' => $data['ticket']);
		}


		// Administrator email
        $headers = array();

		if (PWEBCONTACT_DEBUG) 
        {
            self::$logs[] = 'Admin emails: '.implode(', ', $email_to);
            self::$logs[] = 'Admin email subject: '.$data['subject'];
        }

		// set sender
		if ($user_email AND !$settings->get('server_sender', 0)) {
            $headers[] = 'From: '. ( $user_name ? $user_name.' <'.$user_email.'>' : $user_email );
			if (PWEBCONTACT_DEBUG) {
                self::$logs[] = 'Admin email Sender: '. ( $user_name ? $user_name.' <'.$user_email.'>' : $user_email );
            }
		} else {
            $headers[] = 'From: ' . ( $global_name ? $global_name.' <'.$global_email.'>' : $global_email );
			if (PWEBCONTACT_DEBUG) {
                self::$logs[] = 'Admin email Sender: '. ( $global_name ? $global_name.' <'.$global_email.'>' : $global_email );
            }
			
			// set reply to
			if ($user_email) {
                $headers[] = 'Reply-To: '. ( $user_name ? $user_name.' <'.$user_email.'>' : $user_email );
                if (PWEBCONTACT_DEBUG) {
                    self::$logs[] = 'Admin email reply-to: '. ( $user_name ? $user_name.' <'.$user_email.'>' : $user_email );
                }
			}
		}

        // Add User email as blind carbon copy in debug mode
		if (PWEBCONTACT_DEBUG AND $user_email)
		{
            $headers[] = 'BCC: '.$user_email;
			self::$logs[] = 'Admin BCC debug recipient: '.$user_email;
		}

		// Add blind carbon copy recipients
		if ($params->get('email_bcc')) 
		{
			$headers[] = 'BCC: '.$params->get('email_bcc');
			if (PWEBCONTACT_DEBUG) self::$logs[] = 'Admin BCC recipients: '.$params->get('email_bcc');
		}
		
        $attachments = array();

		// set email format
        $is_html = ((int)$params->get('email_admin_tmpl_format', 1) == 2);
        $headers[] = 'Content-Type: '.($is_html ? 'text/html' : 'text/plain');
        
        // load email body template
        $body = $params->get('email_admin_tmpl');

        // TODO allow to change Administrator email language
		self::parseTmplVars($body, $is_html, get_bloginfo('language'));

		// set body text direction
		$body = ($params->get('rtl', 0) ? "\xE2\x80\x8F" : "\xE2\x80\x8E") . $body;

		if (PWEBCONTACT_DEBUG) self::$logs[] = 'Admin email ready';

		// send Admin email
		$result = wp_mail($email_to, $data['subject'], $body, $headers, $attachments);
        
		if ($result !== true) 
		{
			return array('status' => 306, 'msg' => __('Error sending email to admin', 'pwebcontact'));
		}
		elseif (PWEBCONTACT_DEBUG) self::$logs[] = 'Admin email sent successfully';


		return array('status' => 100, 'msg' => $success_msg, 'ticket' => $data['ticket']);
	}
	
	
	protected static function getSystemInfo() 
	{
		global $wp_version;
        
        if (!self::$sys_info) 
		{
			self::$sys_info = array(
                'WordPress version '.$wp_version, 
                'Contact Form version '.self::getVersion()
            );
		}
		
		return self::$sys_info;
	}
    
    
    protected static function getVersion() {
        
        require_once ABSPATH.'wp-admin/includes/plugin.php';
        
        $data = get_plugin_data(dirname(__FILE__).'/pwebcontact.php');
        return $data['Version'];
    }


	protected static function parseTmplVars(&$content, $is_html = true, $lang_code = 'en-GB')
	{
		$cache_key = $lang_code .'_'. (int)$is_html .'_'. md5($content);
		
		if (!isset(self::$email_tmpls[$cache_key]))
		{
			$params = self::getParams();
            $form_id = (int)$params->get('id');
			
			$patterns = $replacements = $fields_replacements = array();
			
			// text direction
			if ($is_html) 
			{
				$patterns[] = '{dir}';
				$replacements[] = $params->get('rtl', 0) ? 'rtl' : 'ltr';
			}
			
			// Language variables
			if (preg_match_all('/{lang:([^}]+)}/i', $content, $lang_vars, PREG_SET_ORDER))
			{
				foreach ($lang_vars as $variable)
				{
					$patterns[] 	= $variable[0];
					$replacements[] = __($variable[1], 'pwebcontact');
				}
			}
			
			
			// Varaibles with fields
			$cache_fields_key = $lang_code .'_'. (int)$is_html .'_fields';
			$search_fields = strpos($content, '{fields}') !== false;
			$fields = self::getFields($form_id);
			
			foreach ($fields as $field)
			{
				// skip all separators which does not have any data
                if (!isset($field['alias']) OR in_array($field['type'], array('page', 'row', 'column', 'button_send', 'email_copy', 'captcha', 'upload', 'custom_text', 'header'))) {
                    continue;
                }
				
                
                if (isset(self::$data['fields'][$field['alias']])) {
					$value = self::$data['fields'][$field['alias']];
				} 
                else {
					$value = null;
				}
				
				switch ($field['type'])
				{
					case 'textarea':
						if ($is_html AND $value) 
							$value = nl2br($value);
						break;
					case 'checkboxes':
					case 'multiple':
						if (is_array($value)) {
							foreach ($value as &$val)
								$val = __($val, 'pwebcontact');
							$value = implode(', ', $value);
						}
						break;
					case 'checkbox':
                    case 'checkbox_modal':
					case 'radio':
					case 'select':
						if ($value) 
							$value = __($value, 'pwebcontact');
						break;
                    case 'mailto_list':
                        $value = self::$email_vars['mailto_name'];
				}
				
				$patterns[] 	= '{'.$field['alias'].'.value}';
				$replacements[] = $value;
				
				$patterns[] 	= '{'.$field['alias'].'.label}';
				$replacements[] = $name = __($field['label'], 'pwebcontact');
				
				if ($search_fields AND !isset(self::$email_tmpls[$cache_fields_key])) {
					//TODO test RTL if need to change position of sprintf arguments
					$fields_replacements[] = sprintf(__($is_html ? '<strong>%s</strong>: %s' : '%s: %s', 'pwebcontact'), $name, $value);
				}
			}
			
			
			// all fields
			if ($search_fields) 
			{
				if (!isset(self::$email_tmpls[$cache_fields_key])) {
					self::$email_tmpls[$cache_fields_key] = implode($is_html ? '<br>' : "\r\n", $fields_replacements);
				}
				$patterns[] 	= '{fields}';
				$replacements[] = self::$email_tmpls[$cache_fields_key];
			}


			// attachments
			if (strpos($content, '{files}') !== false)
			{
				$patterns[] = '{files}';
				
					$replacements[] = '';
			}
			
			
			// system
			foreach (self::$email_vars as $variable => $value)
			{
				$patterns[] 	= '{'.$variable.'}';
				$replacements[] = $value;
			}
			
			
			// replace email variables with values
			$content = str_replace($patterns, $replacements, $content);
			
			
			self::$email_tmpls[$cache_key] = $content;
		}
		else $content = self::$email_tmpls[$cache_key];
	}


	protected static function detectBrowser()
	{
		return null;
	}


	public static function detectIP()
	{
		$ip = null;
        
        
		return $ip;
	}


	protected static function detectOS()
	{
		$os_name = null;
        
        
		return $os_name;
	}
}

class PWebContact_Params {
    
    protected $data = array();
    
    public function __construct($params) {
        
        if (is_string($params)) {
            $params = json_decode( $params, true );
        }
        
        $this->data = $params;
    }
    
    public function def($key = null, $value = null) {
        
        $this->data[$key] = $value;
    }
    
    public function set($key = null, $value = null) {
        
        $old_value = $this->get($key);
        $this->data[$key] = $value;
        
        return $old_value;
    }
    
    public function get($key = null, $default = null) {
        
        if ($key) {
            if (isset($this->data[$key]) AND $this->data[$key] !== null AND $this->data[$key] !== '') {
                return $this->data[$key];
            }
            return $default;
        }
        else return $this->data;
    }
}

if (!function_exists('exceptions_error_handler'))
{
	function exceptions_error_handler($errno, $errstr, $errfile, $errline)
	{
		if (!(error_reporting() & $errno)) {
			// This error code is not included in error_reporting
			return;
		}
		throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
	}
}

