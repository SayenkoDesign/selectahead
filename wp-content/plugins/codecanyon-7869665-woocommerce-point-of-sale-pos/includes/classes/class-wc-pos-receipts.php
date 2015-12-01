<?php
/**
 * Add extra profile fields for users in admin.
 *
 * @author    Actuality Extensions
 * @package   WoocommercePointOfSale/Classes/profile
 * @category	Class
 * @since     0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WC_Pos_Receipts' ) ) :

/**
 * WC_Pos_Receipts Class
 */
class WC_Pos_Receipts {

	/**
	 * @var WC_Pos_Receipts The single instance of the class
	 * @since 1.9
	 */
	protected static $_instance = null;

	/**
	 * Main WC_Pos_Receipts Instance
	 *
	 * Ensures only one instance of WC_Pos_Receipts is loaded or can be loaded.
	 *
	 * @since 1.9
	 * @static
	 * @return WC_Pos_Receipts Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.9
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'woocommerce' ), '1.9' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.9
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'woocommerce' ), '1.9' );
	}

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		
	}

	public function get_data($ids = ''){
        global $wpdb;
        $filter = '';
        if( !empty($ids) ){
          if(is_array($ids)){
            $ids = implode(',', array_map('intval', $ids));
            $filter .= "WHERE ID IN  == ($ids)";
          }else{
            $filter .= "WHERE ID = $ids";
          }
        }
        if( isset($_REQUEST['s']) && !empty($_REQUEST['s']) && $_REQUEST['page'] == WC_POS()->id_receipts  ){
          $s = $_REQUEST['s'];
          $filter = "WHERE lower( concat(name) ) LIKE lower('%$s%')";
        }
        $table_name = $wpdb->prefix . "wc_poin_of_sale_receipts";
        $db_data = $wpdb->get_results("SELECT * FROM $table_name $filter");
        $data = array();

        foreach ($db_data as $value) {
          $data[] = get_object_vars($value);
        }
        return $data;
  }
  public function get_data_names(){
    $data = $this->get_data();
    $names_list = array();
    foreach ($data as $value) {
      $names_list[$value['ID']] = $value['name'];
    }
    return $names_list;
  }

	public function display_single_receipt_page()
	{
		# receipt
		global $user_ID;
		$receipt_ID = isset($_GET['id']) ? (int) $_GET['id'] : 0;
		$user_ID    = isset($user_ID) ? (int) $user_ID : 0;
		$form_action = 'save_receipt';

		if(!$receipt_ID){
			$receipt_options = array(
				'name'                         => '',
				'print_outlet_address'         => 'yes',
				'print_outlet_contact_details' => 'yes',
				'telephone_label'              => __( 'Tel', 'wc_point_of_sale' ),
				'fax_label'                    => __( 'Fax', 'wc_point_of_sale' ),
				'email_label'                  => __( 'Email', 'wc_point_of_sale' ),
				'website_label'                => __( 'Website', 'wc_point_of_sale' ),
				'receipt_title'                => __( 'Receipt', 'wc_point_of_sale' ),
				'order_number_label'           => __( 'Order Number', 'wc_point_of_sale' ),
				'order_date_label'             => __( 'Order Date', 'wc_point_of_sale' ),
				'print_order_time'             => 'yes',
				'print_server'                 => 'yes',
				'served_by_label'              => __( 'Served by', 'wc_point_of_sale' ),
				'tax_label'                    => __( 'Tax', 'wc_point_of_sale' ),
				'total_label'                  => __( 'Total', 'wc_point_of_sale' ),
				'payment_label'                => __( 'Sales', 'wc_point_of_sale' ),
				'print_number_items'           => 'yes',
				'items_label'                  => __( 'Number of Items', 'wc_point_of_sale' ),
				'print_barcode'                => 'yes',
				'print_tax_number'             => 'no',
				'tax_number_label'             => __( 'Tax Number', 'wc_point_of_sale' ),
				'header_text'                  => '',
				'footer_text'                  => '',
				'logo'                         => '',
			);
		}else{
			$receipt_options = $this->get_data($receipt_ID);
			$receipt_options = $receipt_options[0];
		}
			$args = array (
					'post_type' => 'shop_order',
					'posts_per_page' => 1,
					'orderby' => 'post_date',
	    		'order' => 'DESC',
			);

		$shop_order_query = new WP_Query( $args );
		$products_array   = array();

		$order = new WC_Order($shop_order_query->post->ID);
		?>
		
		<div class="wrap">
			<h2><?php
				if($_GET['action'] == 'edit'){
					_e('Edit Receipt Template', 'wc_point_of_sale');
					echo ' <a href="' . esc_url( admin_url( 'admin.php?page='.WC_POS()->id_receipts.'&action=add' ) ) . '" class="add-new-h2">' . __( 'Add New', 'wc_point_of_sale' ) . '</a>';
				}
				elseif($_GET['action'] == 'add'){
					_e('Add New Receipt Template', 'wc_point_of_sale');
				}
			?></h2>
			<?php echo $this->display_messages();?>
			<div id="lost-connection-notice" class="error hidden">
				<p><span class="spinner"></span> <?php _e( '<strong>Connection lost.</strong> Saving has been disabled until you&#8217;re reconnected.' ); ?>
				<span class="hide-if-no-sessionstorage"><?php _e( 'We&#8217;re backing up this post in your browser, just in case.' ); ?></span>
				</p>
			</div>
			<form action="" method="post" id="edit_wc_pos_receipt">
				<?php wp_nonce_field('wc_point_of_sale_edit_receipt'); ?>
				<input type="hidden" id="user-id" name="user_ID" value="<?php echo (int) $user_ID ?>" />
				<input type="hidden" id="receipt-id" name="receipt_ID" value="<?php echo (int) $receipt_ID ?>" />
				<input type="hidden" id="hiddenaction" name="action" value="<?php echo esc_attr( $form_action ) ?>" />
				<input type="hidden" id="referredby" name="referredby" value="<?php echo esc_url(wp_get_referer()); ?>" />
				<div id="poststuff">
					<div id="post-body" class="metabox-holder columns-2">
						<div id="postbox-container-2" class="postbox-container">
							<div class="postbox ">
								<h3 class="hndle">
									<label  class="receipt_labels" for="receipt_header_text"><?php _e( 'General Details', 'wc_point_of_sale' ); ?></label>
								</h3>
								<div class="inside">
								<table id="receipt_options">
									<tr>
										<td>
											<label class="receipt_labels" for="receipt_name"><?php _e( 'Receipt Name', 'wc_point_of_sale' ); ?></label>
										</td>
										<td>
											<input type="text" id="receipt_name" name="receipt_name" value="<?php echo $receipt_options['name']; ?>">
										</td>
									</tr>
									<tr>
										<td>
											<label class="receipt_labels" for="receipt_print_outlet_address"><?php _e( 'Print Outlet Address', 'wc_point_of_sale' ); ?></label>
										</td>
										<td>
											<input type="checkbox" id="receipt_print_outlet_address" value="yes" name="receipt_print_outlet_address" <?php echo ($receipt_options['print_outlet_address'] == 'yes')? 'checked="checked"' : ''; ?>>
										</td>
									</tr>
									<tr>
										<td>
											<label class="receipt_labels" for="receipt_print_outlet_contact_details"><?php _e( 'Print Outlet Contact Details', 'wc_point_of_sale' ); ?></label>
										</td>
										<td>
											<input type="checkbox" id="receipt_print_outlet_contact_details" value="yes" name="receipt_print_outlet_contact_details" <?php echo ($receipt_options['print_outlet_contact_details'] == 'yes')? 'checked="checked"' : ''; ?>>
										</td>
									</tr>
									<tr class="show_receipt_print_outlet_contact_details">
										<td>
											<label class="receipt_labels" for="receipt_telephone_label"><?php _e( 'Telephone Label', 'wc_point_of_sale' ); ?></label>
										</td>
										<td>
											<input type="text" id="receipt_telephone_label" name="receipt_telephone_label" value="<?php echo $receipt_options['telephone_label']; ?>">
										</td>
									</tr>
									<tr class="show_receipt_print_outlet_contact_details">
										<td>
											<label class="receipt_labels" for="receipt_fax_label"><?php _e( 'Fax Label', 'wc_point_of_sale' ); ?></label>
										</td>
										<td>
											<input type="text" id="receipt_fax_label" name="receipt_fax_label" value="<?php echo $receipt_options['fax_label']; ?>">
										</td>
									</tr>
									<tr class="show_receipt_print_outlet_contact_details">
										<td>
											<label class="receipt_labels" for="receipt_email_label"><?php _e( 'Email Label', 'wc_point_of_sale' ); ?></label>
										</td>
										<td>
											<input type="text" id="receipt_email_label" name="receipt_email_label" value="<?php echo $receipt_options['email_label']; ?>">
										</td>
									</tr>
									<tr class="show_receipt_print_outlet_contact_details">
										<td>
											<label class="receipt_labels" for="receipt_website_label"><?php _e( 'Website Label', 'wc_point_of_sale' ); ?></label>
										</td>
										<td>
											<input type="text" id="receipt_website_label" name="receipt_website_label" value="<?php echo $receipt_options['website_label']; ?>">
										</td>
									</tr>
									<tr>
										<td>
											<label class="receipt_labels" for="receipt_receipt_title"><?php _e( 'Receipt Title', 'wc_point_of_sale' ); ?></label>
										</td>
										<td>
											<input type="text" id="receipt_receipt_title" name="receipt_receipt_title" value="<?php echo $receipt_options['receipt_title']; ?>">
										</td>
									</tr>
									<tr>
										<td>
											<label class="receipt_labels" for="receipt_order_number_label"><?php _e( 'Order Number Label', 'wc_point_of_sale' ); ?></label>
										</td>
										<td>
											<input type="text" id="receipt_order_number_label" name="receipt_order_number_label" value="<?php echo $receipt_options['order_number_label']; ?>">
										</td>
									</tr>
									<tr>
										<td>
											<label class="receipt_labels" for="receipt_order_date_label"><?php _e( 'Order Date Label', 'wc_point_of_sale' ); ?></label>
										</td>
										<td>
											<input type="text" id="receipt_order_date_label" name="receipt_order_date_label" value="<?php echo $receipt_options['order_date_label']; ?>">
										</td>
									</tr>
									<tr>
										<td>
											<label class="receipt_labels" for="receipt_print_order_time"><?php _e( 'Print Order Time', 'wc_point_of_sale' ); ?></label>
										</td>
										<td>
											<input type="checkbox" id="receipt_print_order_time" value="yes"  name="receipt_print_order_time" <?php echo ($receipt_options['print_order_time'] == 'yes')? 'checked="checked"' : ''; ?>>
										</td>
									</tr>
									<tr>
										<td>
											<label class="receipt_labels" for="receipt_print_server"><?php _e( 'Print Server', 'wc_point_of_sale' ); ?></label>
										</td>
										<td>
											<input type="checkbox" id="receipt_print_server" value="yes" name="receipt_print_server" <?php echo ($receipt_options['print_server'] == 'yes')? 'checked="checked"' : ''; ?>>
										</td>
									</tr>
									<tr class="show_receipt_print_server">
										<td>
											<label class="receipt_labels" for="receipt_served_by_label"><?php _e( 'Served By Label', 'wc_point_of_sale' ); ?></label>
										</td>
										<td>
											<input type="text" id="receipt_served_by_label" name="receipt_served_by_label" value="<?php echo $receipt_options['served_by_label']; ?>">
										</td>
									</tr>
									<tr>
										<td>
											<label class="receipt_labels" for="receipt_tax_label"><?php _e( 'Tax Label', 'wc_point_of_sale' ); ?></label>
										</td>
										<td>
											<input type="text" id="receipt_tax_label" name="receipt_tax_label" value="<?php echo $receipt_options['tax_label']; ?>">
										</td>
									</tr>
									<tr>
										<td>
											<label class="receipt_labels" for="receipt_total_label"><?php _e( 'Total Label', 'wc_point_of_sale' ); ?></label>
										</td>
										<td>
											<input type="text" id="receipt_total_label" name="receipt_total_label" value="<?php echo $receipt_options['total_label']; ?>">
										</td>
									</tr>
									<tr>
										<td>
											<label class="receipt_labels" for="receipt_payment_label"><?php _e( 'Payment Label', 'wc_point_of_sale' ); ?></label>
										</td>
										<td>
											<input type="text" id="receipt_payment_label" name="receipt_payment_label" value="<?php echo $receipt_options['payment_label']; ?>">
										</td>
									</tr>
									<tr>
										<td>
											<label class="receipt_labels" for="receipt_print_number_items"><?php _e( 'Print Number of Items', 'wc_point_of_sale' ); ?></label>
										</td>
										<td>
											<input type="checkbox" id="receipt_print_number_items" value="yes" name="receipt_print_number_items" <?php echo ($receipt_options['print_number_items'] == 'yes')? 'checked="checked"' : ''; ?>>
										</td>
									</tr>
									<tr class="show_receipt_print_number_items">
										<td>
											<label class="receipt_labels" for="receipt_items_label"><?php _e( 'Number of Items Label', 'wc_point_of_sale' ); ?></label>
										</td>
										<td>
											<input type="text" id="receipt_items_label" name="receipt_items_label" value="<?php echo $receipt_options['items_label']; ?>">
										</td>
									</tr>
									<tr>
										<td>
											<label class="receipt_labels" for="receipt_print_barcode"><?php _e( 'Print Order Barcode', 'wc_point_of_sale' ); ?></label>
										</td>
										<td>
											<input type="checkbox" id="receipt_print_barcode" value="yes" name="receipt_print_barcode" <?php echo ($receipt_options['print_barcode'] == 'yes')? 'checked="checked"' : ''; ?>>
										</td>
									</tr>
									<tr>
										<td>
											<label class="receipt_labels" for="receipt_print_tax_number"><?php _e( 'Print Tax Number', 'wc_point_of_sale' ); ?></label>
										</td>
										<td>
											<input type="checkbox" id="receipt_print_tax_number" value="yes" name="receipt_print_tax_number" <?php echo ($receipt_options['print_tax_number'] == 'yes')? 'checked="checked"' : ''; ?> >
										</td>
									</tr>
									<tr class="show_receipt_print_tax_number">
										<td>
											<label class="receipt_labels" for="receipt_tax_number_label"><?php _e( 'Tax Number Label', 'wc_point_of_sale' ); ?></label>
										</td>
										<td>
											<input type="text" id="receipt_tax_number_label" name="receipt_tax_number_label" value="<?php echo $receipt_options['tax_number_label']; ?>" >
										</td>
									</tr>
								</table>

								</div>
							</div>
							<div class="postbox ">
								<h3 class="hndle">
									<label  class="receipt_labels" for="receipt_header_text"><?php _e( 'Header Text', 'wc_point_of_sale' ); ?></label>
								</h3>
								<div class="inside">
									<div class="postarea edit-form-section">

										<?php wp_editor( $receipt_options['header_text'], 'receipt_header_text', array(
												'dfw' => false,
												'editor_height' => 200,
												'media_buttons' => false,
												'textarea_name' => 'receipt_header_text',
												'tinymce' => array(
													'resize' => false,
													'add_unload_trigger' => false,
												),
											) ); ?>
								  </div>
								</div>
							</div>
							<div class="postbox ">
								<h3 class="hndle">
									<label  class="receipt_labels" for="receipt_footer_text"><?php _e( 'Footer Text', 'wc_point_of_sale' ); ?></label>
								</h3>
								<div class="inside">
									<div class="postarea edit-form-section">

										<?php wp_editor( $receipt_options['footer_text'], 'receipt_footer_text', array(
												'dfw' => false,
												'editor_height' => 200,
												'media_buttons' => false,
												'textarea_name' => 'receipt_footer_text',
												'tinymce' => array(
													'resize' => false,
													'add_unload_trigger' => false,
												),
											) ); ?>
								  </div>
								</div>
							</div>

						</div><!-- /postbox-container-2 -->
						<div id="postbox-container-1" class="postbox-container">
							<div class="postbox ">
								<h3 class="hndle">
									<label class="receipt_labels" for="receipt_logo"><?php _e( 'Receipt Logo', 'wc_point_of_sale' ); ?></label>
								</h3>
								<div class="inside">
									<p class="hide-if-no-js">
										<?php $receipt_logo_style = (!$receipt_options['logo']) ? 'style="display: none;"' : ''; ?>
											<a class="set_receipt_logo" id="set_receipt_logo_img" href="#" title="<?php _e( 'Set Receipt Logo', 'wc_point_of_sale' ); ?>" <?php echo $receipt_logo_style; ?> >
												<?php $attachment_image_logo = wp_get_attachment_image_src( $receipt_options['logo'], 'full' ); ?>
												<img src="<?php echo $attachment_image_logo[0] ?>" style="max-height: 60px;">
											</a>
											<input type="hidden" name="receipt_logo" id="receipt_logo" value="<?php echo $receipt_options['logo']; ?>">
											<a class="remove_receipt_logo" href="#" title="<?php _e( 'Remove Receipt Logo', 'wc_point_of_sale' ); ?>" <?php echo $receipt_logo_style; ?> >
												<?php _e( 'Remove Receipt Logo', 'wc_point_of_sale' ); ?>
											</a>
											<a class="set_receipt_logo" id="set_receipt_logo_text" href="#" title="<?php _e( 'Set Receipt Logo', 'wc_point_of_sale' ); ?>" <?php echo ($receipt_options['logo']) ? 'style="display: none;"' : ''; ?> >
												<?php _e( 'Set Receipt Logo', 'wc_point_of_sale' ); ?>
											</a>
									</p>
								</div>

							</div>
							<div class="postbox ">
								<h3 class="hndle">
									<label class="receipt_labels" for="receipt_logo"><?php _e( 'Preview', 'wc_point_of_sale' ); ?></label>

								</h3>
								<div class="inside">
								<?php $preview = true; require_once( dirname(realpath(dirname(__FILE__) ) ).'/views/html-print-receipt.php' ); ?>
								</div>
								<div id="major-publishing-actions">
									<div id="publishing-action">
										<span class="spinner"></span>
										<input type="submit" accesskey="p" value="Save" class="button button-primary button-large" id="save_receipt">
									</div>
									<div class="clear"></div>
								</div>
							</div>
						</div><!-- /postbox-container-1 -->
					</div>
				</div>

			</form>
		</div>
		<?php
	}

	function display_messages()
	{
		$i = 0;
		if(isset($_GET['message']) && !empty($_GET['message']) ) $i = $_GET['message'];
		$messages = array(
			 0 => '', // Unused. Messages start at index 1.
			 1 => '<div id="message" class="updated"><p>'.  __('Receipt Template created.') . '</p></div>',
			 2 => '<div id="message" class="updated"><p>'. __('Receipt Template updated.') . '</p></div>',
			 3 => '<div id="message" class="updated"><p>'. __('Receipt Template deleted.') . '</p></div>',
		);
		return $messages[$i];
	}
	public function save_receipt()
	{
		global $wpdb;
		check_admin_referer( 'wc_point_of_sale_edit_receipt' );
		$new = false;
		$data = array(
			'name'                         => isset($_POST['receipt_name']) ? $_POST['receipt_name'] : '',
			'print_outlet_address'         => isset($_POST['receipt_print_outlet_address']) ? $_POST['receipt_print_outlet_address'] : '',
			'print_outlet_contact_details' => isset($_POST['receipt_print_outlet_contact_details']) ? $_POST['receipt_print_outlet_contact_details'] : '',
			'telephone_label'              => isset($_POST['receipt_telephone_label']) ? $_POST['receipt_telephone_label'] : '',
			'fax_label'                    => isset($_POST['receipt_fax_label']) ? $_POST['receipt_fax_label'] : '',
			'email_label'                  => isset($_POST['receipt_email_label']) ? $_POST['receipt_email_label'] : '',
			'website_label'                => isset($_POST['receipt_website_label']) ? $_POST['receipt_website_label'] : '',
			'receipt_title'                => isset($_POST['receipt_receipt_title']) ? $_POST['receipt_receipt_title'] : '',
			'order_number_label'           => isset($_POST['receipt_order_number_label']) ? $_POST['receipt_order_number_label'] : '',
			'order_date_label'             => isset($_POST['receipt_order_date_label']) ? $_POST['receipt_order_date_label'] : '',
			'print_order_time'             => isset($_POST['receipt_print_order_time']) ? $_POST['receipt_print_order_time'] : '',
			'print_server'                 => isset($_POST['receipt_print_server']) ? $_POST['receipt_print_server'] : '',
			'served_by_label'              => isset($_POST['receipt_served_by_label']) ? $_POST['receipt_served_by_label'] : '',
			'tax_label'                    => isset($_POST['receipt_tax_label']) ? $_POST['receipt_tax_label'] : '',
			'total_label'                  => isset($_POST['receipt_total_label']) ? $_POST['receipt_total_label'] : '',
			'payment_label'                => isset($_POST['receipt_payment_label']) ? $_POST['receipt_payment_label'] : '',
			'print_number_items'           => isset($_POST['receipt_print_number_items']) ? $_POST['receipt_print_number_items'] : '',
			'items_label'                  => isset($_POST['receipt_items_label']) ? $_POST['receipt_items_label'] : '',
			'print_barcode'                => isset($_POST['receipt_print_barcode']) ? $_POST['receipt_print_barcode'] : '',
			'print_tax_number'             => isset($_POST['receipt_print_tax_number']) ? $_POST['receipt_print_tax_number'] : '',
			'tax_number_label'             => isset($_POST['receipt_tax_number_label']) ? $_POST['receipt_tax_number_label'] : '',
			'header_text'                  => isset($_POST['receipt_header_text']) ? wpautop($_POST['receipt_header_text']) : '',
			'footer_text'                  => isset($_POST['receipt_footer_text']) ? wpautop($_POST['receipt_footer_text']) : '',
			'logo'                         => isset($_POST['receipt_logo']) ? $_POST['receipt_logo'] : '',
		);
		$table_name = $wpdb->prefix . "wc_poin_of_sale_receipts";
		if(isset($_POST['receipt_ID']) && !empty($_POST['receipt_ID'])){
			$rows_affected = $wpdb->update( $table_name, $data, array( 'ID' => $_POST['receipt_ID'] ) );
			return wp_redirect( add_query_arg( array( "page" => WC_POS()->id_receipts, "action" => 'edit', 'id' => $_POST['receipt_ID'], "message" => 2 ), 'admin.php' ) );
		}
		else{
			$rows_affected = $wpdb->insert( $table_name, $data );
			return wp_redirect( add_query_arg( array( "page" => WC_POS()->id_receipts, "action" => 'edit', 'id' => $wpdb->insert_id, "message" => 1 ), 'admin.php' ) );
		}
	}
	function display_receipt_table()
	{
		?>
		<div class="wrap">
			<h2><?php
					_e('Receipt Templates', 'wc_point_of_sale');
					echo ' <a href="' . esc_url( admin_url( 'admin.php?page='.WC_POS()->id_receipts.'&action=add' ) ) . '" class="add-new-h2">' . __( 'Add New', 'wc_point_of_sale' ) . '</a>';
					if ( ! empty( $_REQUEST['s'] ) )
						printf( ' <span class="subtitle">' . __('Search results for &#8220;%s&#8221;') . '</span>', $_REQUEST['s'] );
			?>
			</h2>
			<?php echo $this->display_messages();?>
			<div id="lost-connection-notice" class="error hidden">
				<p><span class="spinner"></span> <?php _e( '<strong>Connection lost.</strong> Saving has been disabled until you&#8217;re reconnected.' ); ?>
				<span class="hide-if-no-sessionstorage"><?php _e( 'We&#8217;re backing up this post in your browser, just in case.' ); ?></span>
				</p>
			</div>
			<?php 
			$receipts_table = WC_POS()->receipts_table();
			$receipts_table->views();
			?>
			<form action="" method="post" id="edit_wc_pos_receipt">
			<?php
				$receipts_table->search_box( 'Search', 'wc_pos_receipts_is_search' );
				$receipts_table->prepare_items();
				$receipts_table->display();
			?>
			</form>
		</div>
		<?php
	}
	function delete_receipt($ids = 0)
	{
		global $wpdb;
		if( $ids ){
          if(is_array($ids)){
            $ids = implode(',', array_map('intval', $ids));
            $filter .= "WHERE ID IN ($ids)";
          }else{
            $filter .= "WHERE ID = $ids";
          }
      $table_name = $wpdb->prefix . "wc_poin_of_sale_receipts";
    	$query = "DELETE FROM $table_name $filter";
    	if( $wpdb->query( $query ) ) {
				return wp_redirect( add_query_arg( array( "page" => WC_POS()->id_receipts, "message" => 3 ), 'admin.php' ) );
			}
    }
			return wp_redirect( add_query_arg( array( "page" => WC_POS()->id_receipts ), 'admin.php' ) );
	}


}

endif;