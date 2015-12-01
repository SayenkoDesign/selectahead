<?php

class FM_Admin {
	public static $instance = null;
	protected $version = '1.7.79';
	public $update_path = 'http://api.web-dorado.com/v1/_id_/allversions';
	public $updates = array();
	public $fm_plugins = array();
	public $prefix = "fm_";
	protected $notices = null;
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}
	
	private function __construct() {
		$this->notices = new FM_Notices();
		add_action( 'admin_menu', array( $this, 'check_for_update' ), 25 );
		add_action( 'admin_init', array( $this, 'admin_notice_ignore' ) );
		add_action( 'admin_notices', array($this, 'fm_admin_notices') );
	}

	public function get_plugin_data( $name ) {
	
		$fm_plugins = array(
			'form-maker/form-maker.php'          => array(
				'id'          => 31,
				'url'         => 'https://web-dorado.com/products/wordpress-form.html',
				'description' => 'WordPress Form Maker is a fresh and innovative form builder. This form builder is for generating various kinds of forms.',
				'icon'        => '',
				'image'       => plugins_url( 'assets/form-maker.png', __FILE__ )
			),
			'form-maker-export-import/fm_exp_imp.php'                     => array(
				'id'          => 66,
				'url'         => 'https://web-dorado.com/products/wordpress-form/add-ons/export-import.html',
				'description' => 'Form Maker Export/Import WordPress plugin allows exporting and importing forms with/without submissions.',
				'icon'        => '',
				'image'       => plugins_url( 'assets/import_export.png', __FILE__ ),
			),
			'form-maker-mailchimp/fm_mailchimp.php'                     => array(
				'id'          => 101,
				'url'         => 'https://web-dorado.com/products/wordpress-form/add-ons/mailchimp.html',
				'description' => 'This add-on is an integration of the Form Maker with MailChimp which allows to add contacts to your subscription lists just from submitted forms.',
				'icon'        => '',
				'image'       => plugins_url( 'assets/mailchimp.png', __FILE__ ),
			),
			'form-maker-reg/fm_reg.php'                     => array(
				'id'          => 103,
				'url'         => 'https://web-dorado.com/products/wordpress-form/add-ons/registration.html',
				'description' => 'User Registration add-on integrates with Form maker forms allowing users to create accounts at your website.',
				'icon'        => '',
				'image'       => plugins_url( 'assets/reg.png', __FILE__ ),
			),
			'form-maker-post-generation/fm_post_generation.php' => array(
				'id'          => 105,
				'url'         => 'https://web-dorado.com/products/wordpress-form/add-ons/post-generation.html',
				'description' => 'Post Generation add-on allows creating a post, page or custom post based on the submitted data.',
				'icon'        => '',
				'image'       => plugins_url( 'assets/post-generation-update.png', __FILE__ ),
			),
			'form-maker-conditional-emails/fm_conditional_emails.php' => array(
				'id'          => 109,
				'url'         => 'https://web-dorado.com/products/wordpress-form/add-ons/conditional-emails.html',
				'description' => 'Conditional Emails add-on allows to send emails to different recipients depending on the submitted data .',
				'icon'        => '',
				'image'       => plugins_url( 'assets/conditional-emails-update.png', __FILE__ ),
			),
			'form-maker-dropbox-integration/fm_dropbox_integration.php' => array(
				'id'          => 115,
				'url'         => 'https://web-dorado.com/products/wordpress-form/add-ons/dropbox.html',
				'description' => 'The Form Maker Dropbox Integration addon is extending the Form Maker capabilities allowing to store the form attachments straight to your Dropbox account.',
				'icon'        => '',
				'image'       => plugins_url( 'assets/dropbox-integration-update.png', __FILE__ ),
			),
			'form-maker-pdf-integration/fm_pdf_integration.php' => array(
				'id'          => 121,
				'url'         => '',
				'description' => '',
				'icon'        => '',
				'image'       => '',
			),
			'form-maker-gdrive-integration/fm_gdrive_integration.php' => array(
				'id'          => 123,
				'url'         => 'https://web-dorado.com/products/wordpress-form/add-ons/google-drive.html',
				'description' => '
The Google Drive Integration add-on integrates Form Maker with Google Drive and allows you to send the file uploads to the Google Drive.',
				'icon'        => '',
				'image'       => plugins_url( 'assets/google_drive_integration.png', __FILE__ ),
			)
		);
	
		return $fm_plugins[ $name ];
	}
	
	public function get_remote_version( $id ) {
		$userhash = 'nohash';
		if(file_exists(WD_FM_DIR.'/.keep') && is_readable(WD_FM_DIR.'/.keep')){
			$f = fopen(WD_FM_DIR.'/.keep', 'r');
			$userhash = fgets($f);
			fclose($f);
		}
		
		$this->update_path .= '/'.$userhash;
		$request = wp_remote_get( ( str_replace( '_id_', $id, $this->update_path ) ) );
		if ( ! is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) === 200 ) {
			return json_decode( $request['body'], true );
		}

		return false;
	}


	public function check_for_update() {
		global $menu;
		$fm_plugins  = array();
		$request_ids   = array();

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$all_plugins = get_plugins();

		foreach ( $all_plugins as $name => $plugin ) {
			if ( strpos( $name, "fm_" ) !== false or  $name == "form-maker/form-maker.php" ) {

				$data = $this->get_plugin_data( $name );
				if ( $data['id'] > 0 ) {
					$request_ids[] = $data['id'].':'.$plugin['Version'];
					$fm_plugins[ $data['id'] ] = $plugin;
					$fm_plugins[ $data['id'] ]['fm_data'] = $data;
				}
			}
		}

		$this->fm_plugins = $fm_plugins;
		if ( false === $updates_available = get_transient( 'fm_update_check' ) ) {
			$updates_available = array();
			if ( count( $request_ids ) > 0 ) {
				$remote_version    = $this->get_remote_version( implode( '_', $request_ids ) );
				if ( isset( $remote_version['body'] ) ) {
					foreach ( $remote_version['body'] as $id=>$updated_plugin ) {
						if ( isset( $updated_plugin[0]['version'] ) && version_compare( $fm_plugins[$id]['Version'], $updated_plugin[0]['version'], '<' ) ) {
							$updates_available [ $id ] = $updated_plugin;
						}
					}
				}
			}
			set_transient( 'fm_update_check', $updates_available, 12 * 60 * 60 );
		}
		$this->updates = $updates_available;
		$updates_count     = is_array( $updates_available ) ? count( $updates_available ) : 0;
		add_submenu_page('manage_fm', 'Updates', 'Updates' . ' ' . '<span class="update-plugins count-' . $updates_count . '" title="title"><span class="update-count">' . $updates_count . '</span></span>', 'manage_options','updates_fm',	'updates_fm');
		
		$uninstall_page = add_submenu_page('manage_fm', 'Uninstall', 'Uninstall', 'manage_options', 'uninstall_fm', 'form_maker');
		add_action('admin_print_styles-' . $uninstall_page, 'form_maker_styles');
		add_action('admin_print_scripts-' . $uninstall_page, 'form_maker_scripts');
		
		if ( $updates_count > 0 ) {
			foreach ( $menu as $key => $value ) {

				if ( $menu[ $key ][2] == 'manage_fm' || $menu[ $key ][2] == 'updates_fm' ) {
					$menu[ $key ][0] .= ' ' . '<span class="update-plugins count-' . $updates_count . '" title="title">
                                                    <span class="update-count">' . $updates_count . '</span></span>';

					return;
				}

			}
		}

	}
	
	public function plugin_updated() {
		delete_transient( 'fm_update_check' );
	}
	
	function fm_admin_notices( ) {
		// Notices filter and run the notices function.

		$admin_notices = apply_filters( 'fm_admin_notices', array() );
		$this->notices->admin_notice( $admin_notices );

	}
	
	// Ignore function that gets ran at admin init to ensure any messages that were dismissed get marked
	public function admin_notice_ignore() {

		$slug = ( isset( $_GET['fm_admin_notice_ignore'] ) ) ? $_GET['fm_admin_notice_ignore'] : '';
		// If user clicks to ignore the notice, run this action
		if ( isset($_GET['fm_admin_notice_ignore']) && current_user_can( 'manage_options'  ) ) {

			$admin_notices_option = get_option( 'fm_admin_notice', array() );
			$admin_notices_option[ $_GET[ 'fm_admin_notice_ignore' ] ][ 'dismissed' ] = 1;
			update_option( 'fm_admin_notice', $admin_notices_option );
			$query_str = remove_query_arg( 'fm_admin_notice_ignore' );
			wp_redirect( $query_str );
			exit;
		}
	}
}

?>