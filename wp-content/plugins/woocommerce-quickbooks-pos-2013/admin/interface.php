<?php
/*
 * Queue Admin JS/CSS 
 */
//Initialize Admin JS, CSS

add_action('admin_enqueue_scripts', 'sod_qbpos_admin_js');
function sod_qbpos_admin_js(){
	wp_enqueue_script( 'quickbooks_jquery', plugins_url('/js/admin.js', __FILE__) );
	wp_enqueue_script( 'jquery_chosen', plugins_url('/js/jquery.chosen.js', __FILE__) );
	$connected = get_option('quickbookspos_connected');
	$generated = $connected['OwnerID'] == false ? get_option('webconnector_generated'):'1';
	$site_data = array(
		'siteurl'=>get_site_url(),
		'requeue_nonce'=>wp_create_nonce('requeue_nonce'),
		'refresh_nonce'=>wp_create_nonce('refresh_nonce'),
		'sync_item_nonce'=>wp_create_nonce('sync_item_nonce'),
		'create_qwc_nonce'=>wp_create_nonce('create_qwc_nonce'),
		'check_qwc_nonce'=>wp_create_nonce('check_qwc_nonce'),
		'refresh_account_nonce'=>wp_create_nonce('refresh_account_nonce'),
		'wc_generated'=>$generated//get_option('webconnector_generated')
	);
	$site_data = apply_filters('sod_qbpos_jquery_variables',$site_data );
	wp_localize_script( 'quickbooks_jquery', 'site', $site_data);
	
	//wp_register_style( 'qbconnector', plugins_url('/admin/css/admin.css', __FILE__) );
	wp_enqueue_style( 'qbconnector',plugins_url('/css/admin.css', __FILE__) );
	wp_enqueue_style( 'chosen',plugins_url('/css/chosen.css', __FILE__) );
}
/**
 * Admin Notices
 */
add_action( "admin_print_styles", 'quickbooks_admin_notices_styles' );
function quickbooks_admin_install_notice() {
	?>
	<div id="message" class="updated quickbooks-message wc-connect">
		<div class="squeezer">
			<h4><?php _e( '<strong>Take control of your order flow!</strong> &#8211; Create a QWC file and get connected', 'woocommerce' ); ?></h4>
			<p class="submit"><a href="<?php echo add_query_arg('tab', 'sod_web_qbconnector_setup', admin_url('admin.php?page=quickbooks_setup')); ?>" class="button-primary"><?php _e( 'Generate Webconnector File', 'woocommerce' ); ?></a></p>
		</div>
	</div>
	<?php
}
function quickbooks_admin_notices_styles() {
	
	// Installed notices
	if( isset($_GET['page']) && $_GET['page']=="quickbooks_setup"):
		
		if (get_option('webconnector_generated')!=1 || !get_option('quickbookspos_connected')) {
			add_action( 'admin_notices', 'quickbooks_admin_install_notice' );	
		}
	endif;
}
/*
 * Add the Plugin Admin Tabs
 */
function sod_qbpos_options_tabs() {
	global $quickbooks;
	
	$current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'sod_qbconnector_setup';//$this->general_settings_key;
	$tabs = array(
		'sod_qbconnector_setup'=>'Quickbooks Setup',
		'sod_qbconnector_productssync'=>'Product Sync',
		'sod_web_qbconnector_setup'=>'Webconnector',
		'sod_quickbooks_status'=>'History'
		);
		screen_icon('quickbooks');
	$tabs = apply_filters('sod_qbpos_admin_tabs', $tabs);
	echo '<h2 class="nav-tab-wrapper">';
	foreach ( $tabs as $tab_key => $tab_caption ) {
		$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
		echo '<a class="nav-tab ' . $active . '" href="?page=quickbooks_setup&tab=' . $tab_key . '">' . $tab_caption . '</a>';	
	}
	echo '</h2>
	<div id="setup_wrapper" class="drawer">';
	
	echo '</div>';
}
/*
 * Product Grid and Order Grids
 * 
 *
 * /*Adds QB Status Column to Orders Woocommerce Grid*/
add_filter('manage_edit-shop_order_columns', 'sod_qbconnector_edit_custom_order_columns',20);
function sod_qbconnector_edit_custom_order_columns($columns){
		//$columns = array();
	$columns["qb_status"] = __("Quickbooks Status", 'woocommerce');
	return $columns;
}
/*Adds QB Status Column to Products Woocommerce Grid*/
add_filter('manage_edit-product_columns', 'sod_qbconnector_product_edit_custom_order_columns',20);
function sod_qbconnector_product_edit_custom_order_columns($columns){
		//$columns = array();
	$columns["in_qb"] = __("Quickbooks Sync", 'woocommerce');
	return $columns;
}
/*Adds Start/Stop Sync Buttons to Woocommerce Products Grid*/
add_action('manage_product_posts_custom_column', 'sod_qbconnector_product_order_columns', 20);
function sod_qbconnector_product_order_columns($column) {
	global $post;
	$meta = get_post_meta($post->ID,'_sync_status',true);
	switch ($column) {
		case "in_qb" :
			if($meta=="on"){
				$checked='checked="checked"';
			}else{
				$checked='';
			};
			$html = '<div id="qb-status">
						<input name="product_sync" class="switch" data-id="'.$post->ID.'" id="product_sync'.$post->ID.'" '. $checked.' type="checkbox"/>
					</div>';
			echo $html;
		break;
	}
}
/*Adds QB Status Column to Orders Woocommerce Grid*/
add_action('manage_shop_order_posts_custom_column', 'sod_qbconnector_order_columns', 20);
function sod_qbconnector_order_columns($column) {
	global $post, $quickbooks;
	$quickbooks			= new SOD_QuickbooksPOS_Data;
	$order  			= new WC_Order( $post->ID );
	$payments_array 	= $quickbooks->settings->payment_mappings;
	$qbpos_data 		= get_post_meta($post->ID, '_qbpos_data', true);
	$cust_list_id 		= get_post_meta($post->ID, '_customerPOSListID', true);
	$payment_id 		= isset($payments_array[$order->payment_method])  ? $payments_array[$order->payment_method] : false;
	$shipping_method 	= isset($order->shipping_method) ? $order->shipping_method : false;
	$cust_ident 		= isset($cust_list_id) ? $cust_list_id : false ;
	$listids 			= array();
	$initial_queue		= get_post_meta($post->ID, '_qbpos_initial_queue', true) ? get_post_meta($post->ID,'_qbpos_initial_queue', true) : false;
	$use_stored_xml 	= get_post_meta($post->ID, '_qbpos_use_stored_xml', true) == "yes" ? 'checked="checked"':"";
	$auto_post_fail		= get_post_meta($post->ID,'_qbpos_auto_add_failed', true) ? get_post_meta($post->ID,'_qbpos_auto_add_failed', true) : false;
	$error_msg			= get_post_meta($post->ID,'_qbpos_error_msg', true) ? get_post_meta($post->ID,'_qbpos_error_msg', true) : false;
	$requeued_status	= get_post_meta($post->ID,'_qbpos_order_requeued', true) ? get_post_meta($post->ID,'_qbpos_order_requeued', true) : false;
	switch ($column) {
		case "qb_status" :
			if($order->get_items()):
				foreach($order->get_items() as $item){
					if($item['variation_id']!=="0" && $item['variation_id']!==""):
							$qbdata = get_post_meta($item['variation_id'], '_qbpos_data', true);
							if(isset($qbdata['ListID'])):
								if($qbdata['ListID']):
									$listids[$item['variation_id']] = $qbdata['ListID'];
								endif;
							endif;
					else:
						if($item['product_id']):
							$qbdata = get_post_meta($item['product_id'], '_qbpos_data', true);
							if(isset($qbdata['ListID'])):
								if($qbdata['ListID']):
									$listids[$item['product_id']] = $qbdata['ListID'];
								endif;
							endif;
						endif;
					endif;		
				}
			endif;
			 ?>
			<ul id="validity-checks">
				<?php if($payment_id):
					$error = false;
					?>
					<li class="success payment"></li>
				<?php else:
					$error = true;
				?>
					<li class="error payment"></li>
				<?php endif; ?>
				
				<?php if($cust_ident):
					$error = false;
					?>
					<li class="success customer">
						
					</li>
				<?php else:
					$error = false;
				?>
					<li class="alert customer"></li>
				<?php endif; ?>
				<?php 
				if($order->get_items()):
					if(count($listids) == count($order->get_items())):
						$error = false;
						?>
						<li class="success items"></li>
					<?php else:
						$error = true;
					?>
						<li class="error items"></li>
					<?php endif;
				else:?>
					<li class="error no-items"></li>
				 <?php 
				endif;?>
			</ul>
				
			<?php 
			echo '<ul id="quickbooks">';
			
			if($qbpos_data && $requeued_status != "yes"):
				echo sprintf( __('<mark class="%s">%s</mark>', 'woocommerce'), sanitize_title('posted'), __('posted', 'woocommerce') );
			elseif($auto_post_fail == 'yes' && $requeued_status != "yes"):
				echo sprintf( __('<mark class="%s">%s</mark>', 'woocommerce'), sanitize_title('requeue'), __('failed', 'woocommerce') );
			elseif($initial_queue == 'yes' && $requeued_status != "yes"):
				echo sprintf( __('<mark class="%s">%s</mark>', 'woocommerce'), sanitize_title('requeued'), __('queued', 'woocommerce') );
			elseif($requeued_status == 'yes'):
				echo sprintf( __('<mark class="%s">%s</mark>', 'woocommerce'), sanitize_title('requeued'), __('requeued', 'woocommerce') );
			else:
				echo sprintf( __('<mark class="%s">%s</mark>', 'woocommerce'), sanitize_title('requeue'), __('Not Posted', 'woocommerce') );
			endif;
			echo '</ul>';
			
			if($error_msg):
				echo '<p class="">'.$error_msg.'</p>';
			endif;
			//if($initial_queue != 'yes' && $requeued_status != "yes" ):
				if($qbpos_data == false):
				echo '<p class="requeue">
							<a href="#" id="'.$post->ID.'" class="button">'.__('Requeue Order').'</a>
					</p>';
				endif;
			//endif;
			break;
			
	}
}
/*
 * Product and Order Meta Boxes 
 * 
/*Adds QB Meta Box to Woocommerce Order*/
add_action( 'add_meta_boxes', 'sod_qbconnector_meta_boxes' );
function sod_qbconnector_meta_boxes() {
	add_meta_box( 'sod_qbpos_orders', __('Quickbooks', 'woocommerce'), 'sod_qbconnector_meta_box', 'shop_order', 'side', 'default');
	//add_meta_box( 'sod_qbpos_products', __('Quickbooks Sync', 'woocommerce'), 'sod_qbconnector_product_meta_box', 'product', 'side', 'default');
}
/*
 * Meta Box for product Edit page
 * Allows you to turn inventory syncing on/off
 */
add_action('woocommerce_product_write_panel_tabs','sod_qbconnector_product_data_tab',99);
function sod_qbconnector_product_data_tab(){?>
	<li class="quickbooks_data_tab quickbooks_options">
		<a href="#quickbooks_data">
			<?php _e('QBPOS', 'sod_qbconnector'); ?>
		</a>
	</li>
<?php }
add_action('woocommerce_product_after_variable_attributes', 'sod_qbpos_display_variation_qb_data', 50, 2);
function sod_qbpos_display_variation_qb_data($loop, $variation_data){
	
	$qb_data 	= isset($variation_data['_qbpos_data'][0]) ? maybe_unserialize($variation_data['_qbpos_data'][0]) : false;
	$to_display = array('ListID', 'Name', 'ItemType', 'SalesPrice', 'QuantityOnHand');
	$to_display = apply_filters('sod_qbpos_variation_data_to_display', $to_display);
	$disabled  	= apply_filters('sod_qbpos_disable_variation_fields', 'disabled="disabled"');
	$i=0;
	
	if($qb_data):
		foreach($qb_data as $key=>$value){
			if(in_array($key, $to_display)):
				if(!is_array($value)):
				if($i == 2 ) echo "<tr>";
			  ?> 
			 
					<td>
						<label><?php echo $key . ' '; ?><a class="tips" data-tip="<?php _e('This is a QuickBooks Field that can\'t be edited, but is used as a reference to see what QBPOS data is stored for the item.', 'sod_qbconnector'); ?>" href="#">[?]</a></label>
						<input style="background:#e6e6e6;" type="text" size="5" <?php echo $disabled;?> name="<?php echo $key;?>" value="<?php if (isset($value)) echo $value; ?>" />
					</td>
			<?php 
					
	  			$i++;
				if($i == 2 ) echo "</tr>";
				if($i > 2) $i = 1;
				endif;
			endif;
		}?>
		<tr><td colspan="2"><a href="#" class="resync_product_data left button" rel="<?php echo $variation_data['variation_post_id'];?>"> <?php _e('Clear Stored QBPOS Data and Recheck?','sod_qbconnector');?></a></td></tr>
		
	<?php 
	endif;
}
add_action('woocommerce_process_product_meta','sod_qbpos_save_product_meta', 10, 2);
function sod_qbpos_save_product_meta($post_id, $post){
	$sync_status 	= get_post_meta($post_id, '_sync_status', true);
	$quickbooks 	= new SOD_QuickbooksPOS_Data;
	$Queue 			= new QuickBooks_WebConnector_Queue($quickbooks->dsn);
	
	if($sync_status == "on"):
		
		$Queue->enqueue('QBPOS_ITEM_INVENTORY_QUERY', $post_id, 99, NULL, $quickbooks->user);
		
	endif;
	
	if(isset($_POST['qbpos_item_type'])):
	
		update_post_meta($post_id,'_qbpos_item_type', $_POST['qbpos_item_type']);
		
	endif;
	
	if(isset($_POST['qbpos_item_number'])):
	
		update_post_meta($post_id,'_qbpos_item_number', $_POST['qbpos_item_number']);
		
	endif;
}
add_action('woocommerce_product_write_panels','sod_qbconnector_product_meta_box',99);
function sod_qbconnector_product_meta_box($post) {
	global $quickbooks, $post;
	$meta 	 			= get_post_meta($post->ID,'_sync_status',true);
	$qb_data 			= get_post_meta($post->ID,'_qbpos_data',true);
	$item_types 		= array( "Inventory" => "Inventory", "NonInventory" => "Non-Inventory", "Service" => "Service");
	$item_type 			= get_post_meta($post->ID,'_qbpos_item_type',true);
	$qbpos_item_number 	= get_post_meta($post->ID,'_qbpos_item_number',true);
	$data_to_show		= array('ListID','ALU','UPC','ItemNumber','Desc1','QuantityOnHand','ItemType','Price1','Price2','Price3','Price4','Price5','TaxCode');
	if($meta=="on"){
		$checked='checked="checked"';
	}else{
		$checked='';
	};?>
	<div id="quickbooks_data" class="panel woocommerce_options_panel">
		
		<p class="form-field">
			<label for="product_sync<?php echo $post->ID;?>"><?php _e('Turn On QuickBooks POS Sync', 'sod_qbconnector'); ?></label>
			<input name="product_sync" class="switch checkbox" data-id="<?php echo $post->ID;?>" id="product_sync<?php echo $post->ID;?>" <?php echo $checked;?> type="checkbox"/>
		</p>
		<p class="form-field">
			<label for="qbpos_item_type"><?php _e('QBPOS Item Type','sod_qbconnector');?></label>
			<?php 
				$disabled = isset($item_type) ? 'disabled="disabled"' : "";
				$disabled = apply_filters('sod_qbpos_enable_item_type', $disabled);
			?>
			<select id="qbpos_item_type" <?php echo $disabled;?> name="qbpos_item_type">
				<?php foreach($item_types as $key=>$value){
					$selected = $item_type == $value ? 'selected="selected"':"";
					echo '<option '.$selected.' value="'.$value.'">'.$key.'</option>';
				};?>
			</select>
		</p>
		<p class="form-field">
			<?php 
				$disabled = isset($qbpos_item_number) ? 'disabled="disabled"' : "";
				$disabled = apply_filters('sod_qbpos_enable_item_number', $disabled);
			?>
			<label for="qbpos_item_number"><?php _e('Manually enter QBPOS Item Number to link with', 'sod_qbconnector'); ?></label>
			<input name="qbpos_item_number" <?php echo $disabled;?> id="qbpos_item_number" value='<?php echo $qbpos_item_number;?>' type="text"/>
		</p>
		<hr class="light-grey"/>
		<div>
		<h4 class="left"><?php _e('Most Recent QuickBooks POS Data','sod_qbconnector');?></h4>
		<a href="#" class="resync_product_data left" rel="<?php echo $post->ID;?>"> <?php _e('Clear Stored QuickBooks POS Data and recheck','sod_qbconnector');?></a>
		<div class="clear"></div>
		<p><?php _e('The most recent data that was synced from QuickBooks POS. This data is read only and can\'t be changed. If you would like to update this data, please update the data in QuickBooks POS and then re-sync your inventory.','sod_qbconnector');?></p>
	<?php if($qb_data){
		foreach($qb_data as $qb_key=>$qb_value){
			if(in_array($qb_key, $data_to_show)):?>
			<p class="form-field">
				<label for="<?php echo $qb_key;?>"><?php echo $qb_key;?></label>
				<input name="<?php echo $qb_key;?>" class=""  id="<?php echo $qb_key;?>" type="text" readonly="readonly" value="<?php echo $qb_value;?>"/>
			</p>
			<?php endif;?>
	<?php }
	}
	?></div>
	</div>
<?php 	
}
/**
 * Order totals meta box
 * Displays the quickbooks posting details on the order view page
 */
function sod_qbconnector_meta_box(){
	global $post;
	$quickbooks			= new SOD_QuickbooksPOS_Data;
	$order  			= new WC_Order( $post->ID );
	$payments_array 	= $quickbooks->settings->payment_mappings;
	$qbpos_data 		= get_post_meta($post->ID, '_qbpos_data', true);
	$cust_list_id 		= get_post_meta($post->ID, '_customerPOSListID', true);
	$payment_id 		= isset($payments_array[$order->payment_method])  ? $payments_array[$order->payment_method] : false;
	$shipping_method 	= isset($order->shipping_method) ? $order->shipping_method : false;
	$cust_ident 		= isset($cust_list_id) ? $cust_list_id : false ;
	$listids 			= array();
	$initial_queue		= get_post_meta($post->ID, '_qbpos_initial_queue', true) ? get_post_meta($post->ID,'_qbpos_initial_queue', true) : false;
	$use_stored_xml 	= get_post_meta($post->ID, '_qbpos_use_stored_xml', true) == "yes" ? 'checked="checked"':"";
	$auto_post_fail		= get_post_meta($post->ID,'_qbpos_auto_add_failed', true) ? get_post_meta($post->ID,'_qbpos_auto_add_failed', true) : false;
	$error_msg			= get_post_meta($post->ID,'_qbpos_error_msg', true) ? get_post_meta($post->ID,'_qbpos_error_msg', true) : false;
	$requeued_status	= get_post_meta($post->ID,'_qbpos_order_requeued', true) ? get_post_meta($post->ID,'_qbpos_order_requeued', true) : false;
	if($order->get_items()):
		foreach($order->get_items() as $item){
			if($item['variation_id']!=="0" && $item['variation_id']!==""):
					$qbdata = get_post_meta($item['variation_id'], '_qbpos_data', true);
					if(isset($qbdata['ListID'])):
						$listids[$item['variation_id']] = $qbdata['ListID'];
					endif;
			else:
				if($item['product_id']):
					$qbdata = get_post_meta($item['product_id'], '_qbpos_data', true);
					if(isset($qbdata['ListID'])):
						$listids[$item['product_id']] = $qbdata['ListID'];
					endif;
				endif;
			endif;		
		}
	endif;
	
	 ?>
	<ul id="validity-checks">
		<?php if($payment_id):
			$error = false;
			?>
			<li class="success"><?php _e('Payment Method\'s successfully mapped');?></li>
		<?php else:
			$error = true;
		?>
			<li class="error"><?php _e('Payment method no mapped');?></li>
		<?php endif; ?>
		
		<?php if($cust_ident):
			$error = false;
			?>
			<li class="success"><?php _e('Customer exists in QBPOS');?></li>
		<?php else:
			$error = false;
		?>
			<li class="alert"><?php _e('Customer will be created in QBPOS');?></li>
		<?php endif; ?>
		<?php 
		if($order->get_items()):
			if(count($listids) == count($order->get_items())):
				$error = false;
				?>
				<li class="success"><?php _e('All products on order appear to be synced with QBPOS');?></li>
			<?php else:
				$error = true;
			?>
				<li class="error"><?php _e('Some products on the order have not been synced. The order will not post');?></li>
			<?php endif;
		else:?>
			<li class="error"><?php _e('No Products on order');?></li>
		 <?php 
		endif;?>
	</ul>
		
	<?php 
	echo '<ul id="quickbooks">';
	
	if($qbpos_data && $requeued_status != "yes"):
		echo sprintf( __('<mark class="%s">%s</mark>', 'woocommerce'), sanitize_title('posted'), __('posted', 'woocommerce') );
	elseif($auto_post_fail == 'yes' && $requeued_status != "yes"):
		echo sprintf( __('<mark class="%s">%s</mark>', 'woocommerce'), sanitize_title('requeue'), __('failed', 'woocommerce') );
	elseif($requeued_status == 'yes'):
		echo sprintf( __('<mark class="%s">%s</mark>', 'woocommerce'), sanitize_title('requeued'), __('requeued', 'woocommerce') );
	else:
		echo sprintf( __('<mark class="%s">%s</mark>', 'woocommerce'), sanitize_title('requeue'), __('Not Posted', 'woocommerce') );
	endif;
	echo '</ul>';
	
	if($error_msg):
		echo '<p class="">'.$error_msg.'</p>';
	endif;
	if($quickbooks->settings->post_orders =='on'):
			//if($initial_queue != 'yes' && $requeued_status != "yes" ):
			if($qbpos_data == false):
			echo '<p class="requeue">
						<a href="#" id="'.$post->ID.'" class="button">'.__('Requeue Order').'</a>
				</p>';
			endif;
		//endif;	
	endif;
}


/*
 * Admin Menu Items
/*/
add_action('admin_menu', 'sod_qbconnector_admin_menu');
function sod_qbconnector_admin_menu() {
	global $menu;
	add_submenu_page('woocommerce', __('Quickbooks POS', 'woocommerce'), __('Quickbooks POS', 'woocommerce'), 'administrator', 'quickbooks_setup', 'sod_qbconnector_setup');
}

/*
 * Plugin Options Init
 */
add_action( 'admin_init', 'sod_qbpos_default_settings' );
function sod_qbpos_default_settings() {
	register_setting('sod_qbpos_defaults', 'sod_qbpos_defaults' );
	register_setting('sod_qbpos_webconnector', 'sod_qbpos_webconnector' );
	register_setting( 'sod_qbpos_inv_defaults', 'sod_qbpos_inv_defaults' );
	register_setting( 'sod_quickbooks_status', 'sod_quickbooks_status' );
	/*
	 * Setup Settings Section
	 */
	add_settings_section( 'sod_qbconnector_setup_description', 'Quickbooks Setup', 'sod_qbconnector_setup_description', 'sod_qbconnector_setup');
	add_settings_section( 'sod_web_qbconnector_setup_description', '', 'sod_web_qbconnector_setup_description', 'sod_web_qbconnector_setup');
	//add_settings_section( 'sod_qbpos_account_settings', 'Default Account Options', 'sod_qbpos_account_settings', 'sod_qbconnector_setup');
	add_settings_section( 'sod_qbpos_posting_settings', 'Order Posting Options', 'sod_qbpos_posting_settings', 'sod_qbconnector_setup');
	add_settings_section( 'sod_qbconnector_productsync_description', 'Product Validation', 'sod_qbconnector_productssync_description', 'sod_qbconnector_productssync');
	add_settings_section( 'sod_qbpos_payments_settings', 'Payment Method Mappings', 'sod_qbpos_payments_settings', 'sod_qbconnector_setup');
	add_settings_section( 'sod_qbpos_departments_settings', 'Department / Category Mappings', 'sod_qbpos_departments_settings', 'sod_qbconnector_setup');
	
	add_settings_section( 'sod_qbpos_salestax_settings', 'Sales Code Mappings', 'sod_qbpos_salestax_settings', 'sod_qbconnector_setup');
	add_settings_section( 'sod_qbpos_pricelevels_settings', 'Price Levels Settings', 'sod_qbpos_pricelevels_settings', 'sod_qbconnector_setup');
	add_settings_section( 'sod_qbpos_inventory_settings', 'Product Sync Settings', 'sod_qbpos_inventory_settings', 'sod_qbconnector_productssync');
	add_settings_section( 'sod_qbpos_webconnector', 'Webconnector Configuration', 'sod_web_qbconnector_setup_description', 'sod_web_qbconnector_setup');
	add_settings_section( 'sod_qbpos_webconnector', 'WebConnector QWC File Generation', 'sod_qbpos_qwc_settings', 'sod_web_qbconnector_setup');
	add_settings_section( 'sod_quickbooks_status_grid', 'WebConnector History', 'sod_quickbooks_status_grid', 'sod_quickbooks_status');
}
function sod_quickbooks_status_grid(){
	global $wpdb;
	$types = array('log','queue');
	$log_selected_class="";
	$queue_selected_class="";
	
	add_query_arg('type',$types);
	 $pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
	 $limit = 50;
	 $offset = ( $pagenum - 1 ) * $limit;
	 $type = isset($_GET['type']) ?$_GET['type']:"queue";
	 if(isset($type)):
		if($type=="log"){
			$log_selected_class='class="selected"';
			$total = count($wpdb->get_results( "SELECT quickbooks_log_id FROM quickbooks_log"));
			$rows = $wpdb->get_results( "SELECT quickbooks_log_id AS ID, log_datetime AS Date, msg AS Message FROM quickbooks_log LIMIT $limit OFFSET $offset" );
			$columns = array("ID","Date","Message");   	
		  }elseif($type=="queue"){
		  	$queue_selected_class='class="selected"';
			$total = count($wpdb->get_results( "SELECT quickbooks_queue_id FROM quickbooks_queue"));
			$rows = $wpdb->get_results( "SELECT quickbooks_queue_id AS ID, dequeue_datetime AS Date,qb_action AS Action,qb_status AS Status, msg AS Message FROM quickbooks_queue LIMIT $limit OFFSET $offset" );
			$columns = array("ID","Date","Action","Status","Message");   
		  };
	endif;
	 
	 $num_of_pages = ceil( $total / $limit );
	 $page_links = paginate_links( array(
	    'base' => add_query_arg( 'pagenum', '%#%' ),
	    'format' => '',
	    'prev_text' => __( '&laquo;', 'aag' ),
	    'next_text' => __( '&raquo;', 'aag' ),
	    'total' => $num_of_pages,
	    'current' => $pagenum
	 ));?>
	 
	 	<div id="quickbooks_table">	 
			<?php 
				if ( $page_links ) {
				    echo '<div class="tablenav"><div class="tablenav-pages" style="margin: 1em 0">' . $page_links . '</div></div>';
				}
			?>
			<div id="record_types">
				<h3><?php _e('Data');?>: </h3>
				<ul>
					<li <?php echo $queue_selected_class;?>><a href="admin.php?page=quickbooks_setup&tab=sod_quickbooks_status&pagenum=1&type=queue" data-type="open" class="coupon-filter"><?php _e('Quickbooks POS Queue','qbconnector');?></a></li>
					<li <?php echo $log_selected_class;?>><a href="admin.php?page=quickbooks_setup&tab=sod_quickbooks_status&pagenum=1&type=log" class="coupon-filter" data-type="open"><?php _e('Quickbooks POS Log','qbconnector');?></a></li>
					
				</ul>
			</div>
		 	<table class="wp-list-table widefat pages" cellspacing="0">
				<thead>
					<tr>
						<th scope="col" class="manage-column desc" style=""></th>
						<?php foreach($columns as $column){?>
						<th scope="col" id="<?php echo strtolower($column);?>" class="manage-column column-<?php echo strtolower($column);?>" style="">
							<?php echo $column;?>
						</th>
					<?php }?>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th scope="col" class="manage-column desc" style=""></th>
						<?php foreach($columns as $column){?>
						<th scope="col" id="<?php echo strtolower($column);?>" class="manage-column column-<?php echo strtolower($column);?>" style="">
							<?php echo $column;?>
						</th>
					<?php }?>
					</tr>
				</tfoot>
				<tbody id="the-list">
					<?php 
						switch($type){
							case "queue":
								sod_qbconnector_get_queue_rows($rows);
								break;
								case "log":
								sod_qbconnector_get_log_rows($rows);	
								break;		
					
				}?>
			</tbody>
	
<?php }
function sod_qbconnector_action_translater($item){
	$action = array(
				'QBPOS_CHECK_CONNECTION' =>'Check Connection',
				'QBPOS_ADD_CUSTOMER' =>'Add Customer',
				//Add Customer then receipt			
				'QBPOS_ADD_CUST_RCPT'=>'Add Customer for Receipt',
				//Add Receipt for Customer
				'QBPOS_ADD_RCPT_CUST'=>'Add Customer for Receipt',
				//Add Receipt
				'QBPOS_ADD_RECEIPT'=>'Add Receipt',
				'QBPOS_CUST_QUERY' =>'Customer Query',
				'QBPOS_ITEM_INVENTORY_QUERY'=>'Inventory Items Query',
				'QBPOS_ITEM_INVENTORY_ADD'=>'Add Inventory Item',
				'QBPOS_ITEM_INV_UPDATE'=>'Update Inventory Item',
				'QBPOS_INVENTORY_SYNC_START'=>'Inventory Sync Start',
				'QBPOS_NONINV_SYNC_START'=>'Non-Inventory Sync Start',
				'QBPOS_NONINV_ADD' =>'Add Non-Inventory Item',
				'QBPOS_NONINV_UPDATE'=>'Update Non-Inventory Item',
				'QBPOS_CUST_ACCT_SETUP'=>'Setup Customer Accounts',
				'QBPOS_PAYMENT_METHODS'=>'Setup Payment Methods',
				'QBPOS_ALL_INV_SETUP'=>'Setup Receivables Accounts',
				'QBPOS_DEPARTMENTS'=>'Setup Departments',
				'QBPOS_PRICELEVELS'=>'Setup Price Levels',
				'QBPOS_VENDORS'=>'Setup Vendors',
				'QBPOS_TAX_RECORDDS'=>'Setup Taxes',
				'QBPOS_SHIPPING'=>'Setup Shipping'
		);
		$action = apply_filters('sod_qbpos_action_translater', $action);
		if(array_key_exists($item, $action)){
			return $action[$item];
		}else{
			return false;
		}
		
}
function sod_qbconnector_get_queue_rows($rows){
	foreach ($rows as $key => $data){
		switch($data->Status){
			case"s":
				$data->Status = "Success";
			break;
			case"q":
				$data->Status ="Queued";
			break;
			case"e":
				$data->Status ='Error';
			break;
			case"h":
				$data->Status = "Handled";
			break;
		};?>
		<tr class="hentry alternate iedit author-self" valign="top">
			<td></td>
			<td class="id column-id">
				<strong>
					<label id="id"><?php echo $data->ID;?></label>
				</strong>
			</td>
			<td class="date column-date" style="min-width:125px;">
				<label id="date" ><?php echo $data->Date;?></label>
			</td>
			<td class="action column-action" style="min-width:125px;">
				<label id="action"><?php echo sod_qbconnector_action_translater($data->Action);?></label>
			</td>
			<td class="status column-status" style="min-width:125px;">
				<label id="status"><?php echo $data->Status;?></label>
			</td>
			<td class="message column-message">
				<label id="message"><?php echo $data->Message;?></label>
			</td>
		</tr>
<?php 
}
}

function sod_qbconnector_get_log_rows($rows){
	foreach ($rows as $key => $data){
	?>
		<tr class="hentry alternate iedit author-self" valign="top">
			<td></td>
			<td class="id column-id">
				<strong>
					<label id="id"><?php echo $data->ID;?></label>
				</strong>
			</td>
			<td class="date column-date" style="min-width:125px;">
				<label id="date" ><?php echo $data->Date;?></label>
			</td>
			<td class="message column-message">
				<label id="message"><?php echo $data->Message;?></label>
			</td>
		</tr>
<?php }
}

function sod_qbconnector_setup_description() {
	$html =  '<div class="desc" style="float:left;">
				<div class="left">
					<label>'.__('General Quickbooks Account Mappings and Options. Make sure that <em><strong>ALL</strong></em> options are selected before you start syncing.', 'sod_qbconnector').'</label>
				</div>
				<div style="clear:both;"></div>
				<div class="left" style="padding:15px 0; margin:0 20px; float:left;">
					<a href="#" class="recheck_defaults left button" >'. __('Recheck All Department / Accounts','sod_qbconnector') . '</a>
				</div>
				<div class="left" style="padding:15px 0;float:left;">
					<a href="#" class="recover_orders left button" >'. __('Recover Orders','sod_qbconnector') . '</a>
				</div>
			</div><div style="clear:both;"></div>';
	echo $html;
	}
function sod_qbconnector_productssync_description() {
		$html =  '<div class="desc">
				<div class="left" style="width:500px;">
					<label>'.__('Click the button to re-validate <strong>ALL</strong> of your website products. This process will check to make sure that ALL of your products exist in QuickBooks. This will help to ensure that orders will post successfully. This will NOT update the pricing / inventory counts on your website. This is only going to update the internal QuickBooks data used to programmatically interact with QuickBooks. <br/><br/><em>Note: Depending on the number of products you have, this can take a while. After clicking this button, please start the web connector. <strong>If you have a large catalog, this will take a LONG time.</strong></em>', 'sod_qbconnector').'</label>
				</div>
				<div class="left" style="clear:both;padding:15px 0;">
					<a href="#" class="revalidate left button" >'. __('Validate All Products ','sod_qbconnector') . '</a>
				</div>
			</div><div style="clear:both;"></div>';
	echo $html;
}
function sod_web_qbconnector_setup_description(){
	$html = '<div id="faqs"">
			<h3>Quickbooks Web Connector Setup</h3>
			<h4>What\'s the QuickBooks Web Connector?</h4>
			<div>
				<p>The QuickBooks Web Connector is a software application that runs on Microsoft Windows that enables specially designed web-based applications to exchange data with QuickBooks products.
				Quickbooks Webconnector QWC file setup and generation. You should be able to find it on your computer by going to <em>Quickbooks > Web Connector</em>
				</p>
			</div>
			<h4>Why\'s everything disabled?</h4>
			<div>
				<p>All of the setup fields are disabled until you generate and download a QuickBooks Web Connector file.</p>
			</div>
			<h4>What do I do after I download the qwc file?</h4>
			<div>
				<p>After you\'ve downloaded the Woocommerce qwc file to connect QuickBooks to your website, open up the QuickBooks Web Connector application from your computer.
				Then click the <em>Add Application</em> button in the QuickBooks Web Connector and select the qwc file that you just downloaded. Once the Web Connector loads your file, your website will start syncing with QuickBooks. 
				After the website verifies that it has a connection to QuickBooks the rest of the setup fields will become enabled.
				</p>
			</div>
			<h4>What username and password do I use?</h4>
			<div>
				<p>This is completely up to you. The username and password are <em><strong>ONLY</strong></em> used by the webconnector. It should <em><strong>not</strong></em> be your Wordpress credentials or your Quickbooks credentials. This is just so the
				webconnector can communicate with QuickBooks.
				</p>
			</div>
			<h4>What ssl path do I use?</h4>
			<div>
				<p>
					The SSL path is the https address for the root of your wordpress install. You don\'t need to have a dedicated ssl. Most shared certificates will work.
				</p>
			</div>
			<h4>What Computer Name do I use?</h4>
			<div>
				<p>
					The computer name is the name of the pc that QuickBooks POS is running on. To find this, go to the PC that has QuickBooks POS installed on it and go to <em>Control Panel > System</em>, which should list the PC name.
				</p>
			</div>
			<h4>What\'s the Company Data?</h4>
			<div>
				<p>
					The company data is the name of the company data file that QuickBooks POS is using. If you open up Point-of-Sale and look in the upper left-hand corner of the program, you\'ll see the name of the company file. Make sure to enter the name of the company data in lower case.
				</p>
			</div>
			</div>';
	
	echo $html;

}

/*
 * Settings Form using the WP Settings API
 */ 	
function sod_qbconnector_setup(){
	global $quickbooks;
	
	$quickbooks->check_defaults();
	$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'sod_qbconnector_setup';
		?>
		<div class="wrap">
			<?php sod_qbpos_options_tabs();?>
			<?php if($tab=="sod_qbconnector_setup"){?>
					<div class="quickbooks-webconnector">
						<a href="http://marketplace.intuit.com/webconnector/" target="_blank">Click Here to Download QuickBooks Web Connector</a>
					</div>
				<form method="post" action="options.php" class="quickbooks">
					<?php 
						wp_nonce_field( 'sod_qbpos_defaults' );
						settings_fields('sod_qbpos_defaults');
						do_settings_sections( $tab );
						
					?>
					<input type="submit" class="button-primary" value="<?php _e('Save Settings') ?>" />
				</form>
			<?php }elseif ($tab =='sod_quickbooks_status') {?>
				<div class="quickbooks-webconnector">
					<a href="http://marketplace.intuit.com/webconnector/" target="_blank">Click Here to Download QuickBooks Web Connector</a>
				</div>
					<?php 
						wp_nonce_field( 'sod_quickbooks_status' );
						settings_fields('sod_quickbooks_status');
						do_settings_sections( $tab );
					?>
					
				</form>
			<?php }elseif ($tab =='sod_qbconnector_productssync') {?>
				<div class="quickbooks-webconnector">
					<a href="http://marketplace.intuit.com/webconnector/" target="_blank">Click Here to Download QuickBooks Web Connector</a>
				</div>
				<form method="post" action="options.php" class="quickbooks">
					
					<?php 
						wp_nonce_field( 'sod_qbpos_inv_defaults' );
						settings_fields('sod_qbpos_inv_defaults');
						do_settings_sections( $tab );
					?>
					<input type="submit" class="button-primary" value="<?php _e('Save Settings') ?>" />
				</form>
			<?php }elseif ($tab =='sod_web_qbconnector_setup') {?>
				<div class="quickbooks-webconnector">
					<a href="http://marketplace.intuit.com/webconnector/" target="_blank">Click Here to Download QuickBooks Web Connector</a>
				</div>
				<?php 
				wp_nonce_field( 'sod_qbpos_webconnector' );
				settings_fields('sod_qbpos_webconnector');
				do_settings_sections( $tab );
			}?>
		</div>
		<div class="clear"></div>
<?php 
}
/*
 * Plugin Settings form builder
 * See /admin/settings.php for the array of fields
 */
function sod_form_builder($meta_box, $post_id=0){
	global $quickbooks;
	$quickbooks = new SOD_QuickBooksPOS_Data;
	
	// Use nonce for verification
	echo '<input type="hidden" name="sod_qb_product_meta" value="', wp_create_nonce(basename(__FILE__)), '" />';
	echo '<table class="form-table">';
	$meta_array = get_option($meta_box['meta_key'],array());
	
	$section =$meta_box['section'];
	foreach ($meta_box['fields'] as $field) {
    $id = $field['id'];
		
    $meta = '';
	if($id && !empty($id) && $id !==""):
		if(array_key_exists($id,$meta_array)):
			$meta = $meta_array[$id];
		else:
			$meta = '';
		
		endif;
	endif; 
	echo '<tr>
			<th  scope="rpw" class="label">';
			
			 echo '<label for="'.$meta_box['meta_key'].'[' . $field['id'] . ']">';
			 if(isset($field['desc'])):
			 	//echo '<a class="tips" style="float:left; margin:0 10px 0 0;" data-tip="'.$field['desc']. '" href="#">[?]</a>';
			 endif;
			 echo '<h4 style="float:left;">' . $field['name'] . '</h4>';
			 
			 
			 '</label>';
			 
             if(array_key_exists('refresh_link',$field)){
					echo '<span style="clear:both; float:left;">
								<a href="#" class="refresh" data-option="'.$field['refresh_link']['option'].'">'.$field['refresh_link']['title'].'</a>	
						</span><br/>';	
				};
			// if(array_key_exists('add_link',$field)){
					// echo '<span>
								// <a href="#" class="add" data-option="'.$field['add_link']['option'].'">'.$field['add_link']['title'].'</a>	
						// </span><br/>';	
				// };
//            
           
            echo '</th><td>';
    switch ($field['type']) {
        case 'text':
			echo '<input type="text" name="'.$meta_box['meta_key'].'[', $field['id'], ']" id="'.$meta_box['meta_key'].'[', $field['id'], ']" value="', $meta ? $meta : $field['std'], '" />';
			if(array_key_exists('suffix',$field)):
				echo '<span class="right suffix">'.$field['suffix'].'</span>';
			endif;
            break;
		 case 'label':
            echo '<label name="'.$meta_box['meta_key'].'[', $field['id'], ']" id="'.$meta_box['meta_key'].'[', $field['id'], ']">',$field['text'] , '</label>';
            break;
        case 'textarea':
            echo '<textarea name="'.$meta_box['meta_key'].'[', $field['id'], ']" id="'.$meta_box['meta_key'].'[', $field['id'], ']" cols="60" rows="4" style="width:97%">', $meta ? $meta : $field['std'], '</textarea>', '<br />', $field['desc'];
            break;
        case 'qbdata-select':
			if(empty($field['options'])){
				echo '<mark class="refreshing">Retrieving Accounts from Quickbooks</mark>';
			} else {
			// if(array_key_exists('add_link',$field)){
				// echo '<input type="text" name="'.$meta_box['meta_key'].'[', $field['id'], ']" id="'.$meta_box['meta_key'].'[', $field['id'], ']" value="', $meta ? $meta : $field['std'], '" />';
			// }
            echo '<select name="'.$meta_box['meta_key'].'[', $field['id'], ']" class="'. $field['class'].'">';
			    foreach ($field['options'] as $key=>$value) {
                    	//echo '<option value="'.$value['ListID'].'"',$quickbooks->$section->$id == $value['ListID'] ? ' selected="selected"' : '',' >'.$key.'</option>';
                 	if($id):
						
						$selector = $quickbooks->$section->$id;
					else:
						$selector="";	
					endif;
			    	echo '<option value="'.$value['ListID'].'"',$selector== $value['ListID'] ? ' selected="selected"' : '',' >'.$key.'</option>';
                //}
                }
                echo '</select>';
				
			}
            break;
		case 'select':
		
			//$quickbooks->$section->$id
			if(empty($field['options'])){
				echo '<mark class="refreshing">Retrieving Accounts from Quickbooks</mark>';
			} else {
			   	echo '<select name="'.$meta_box['meta_key'].'[', $field['id'], ']" class="'. $field['class'].'">';
			    foreach ($field['options'] as $key=>$value) {
                    echo '<option value="'.$key.'"',$quickbooks->$section->$id == $key ? ' selected="selected"' : '',' >'.$value.'</option>';
                }
	            echo '</select>';
			}
			break;
		case 'select-array':
			
			if(empty($field['options'])){
				echo '<mark class="refreshing">Retrieving Accounts from Quickbooks</mark>';
			} else {
			foreach($field['options'] as $key=>$value){
				$array = $quickbooks->$section->$id;
				
				echo '<select name="'.$meta_box['meta_key'].'[', $field['id'], '][',$key,']" class="'. $value['class'].'">';
				    foreach ($value['options'] as $option=>$option_value) {
				    	
	                    echo '<option value="'.$option.'"',$array[$key] == $option ? ' selected="selected"' : '',' >'.$option_value.'</option>';
	                }
	            echo '</select>';
            }
			}
			break;
		case 'mapping':
		echo '<div class="mapping">';
			echo '<div class="mapping-heading">';
			echo 	'<h4>'.$field['options']['labels']['heading'].'</h4>';
			echo 	'<h4>'.$field['options']['selects']['heading'].'</h4>';
			echo '</div>';
			
			foreach($field['options']['labels']['data'] as $key=>$value){
				echo '<div class="mapping-row">';
					echo '<div class="label">';
					if(is_object($value)){
						echo '	<label id="'.$key.'">'.$value->title.'</label>';
					}elseif ($key){
						echo '	<label id="'.$key.'">'.$value.'</label>';
					}else{
						echo '	<label id="'.$value.'">'.$value.'</label>';
					}
					echo '</div>';
					echo '<div class="select">';
					if(empty($field['options']['selects']['data'])){
						echo '<mark class="refreshing">Retrieving Accounts from Quickbooks</mark>';
					} else {
						
						if(is_object($value)){
							$temp = '';
							echo '	<select name="'.$meta_box['meta_key'].'[', $field['id'], '][',$key,']" class="',$field['options']['selects']['class'],'">';
						}else{
							$temp = str_replace(array('(',')','%','.'),'',$value);
							echo '	<select name="'.$meta_box['meta_key'].'[', $field['id'], '][',str_replace(" ","_",strtolower($temp)),']" class="',$field['options']['selects']['class'],'">';
							
						};
							echo '<option value="" >Select an Option</option>';
								foreach($field['options']['selects']['data'] as $option=>$option_value){
									
									if(is_array($option_value)){
										if($id && $key && !$temp):
											$array = $quickbooks->$section->$id;
											$selector = $array[$key];
										elseif ($id && $temp):
											$new_key = str_replace(" ","_",strtolower($temp));
											$array = $quickbooks->$section->$id;
											$selector = $array[$new_key];
										else:
											$selector="";
										endif;
										
										if(array_key_exists('ListID', $option_value)):
											echo '<option value="'.$option_value['ListID'].'"', $selector== $option_value['ListID'] ? ' selected="selected"' : '',' >'.$option.'</option>';
										endif;
										//echo '<option value="'.$option_value['ListID'].'"',$quickbooks->$section->$id->$key == $option_value['ListID'] ? ' selected="selected"' : '',' >'.$option.'</option>';	
									}else{
										
										$index = str_replace(" ","_",strtolower($temp));
										$array  = $quickbooks->$section->$id;
										echo '<option value="'.$option.'"',$array[$index] == $option ? ' selected="selected"' : '',' >'.$option_value.'</option>';
									}
									
								}
						echo '	</select>';
						if(array_key_exists('refresh_link',$field)){
						echo '<span>
								<a href="#" class="refresh" data-option="'.$field['refresh_link']['option'].'">'.$field['refresh_link']['title'].'</a>	
						</span>';	
				}
					}
					echo '</div>';
				echo '</div>';
			echo '</div>';
				}
			break;
			
		case 'textbox_mapping':
		echo '<div class="mapping">';
			echo '<div class="mapping-heading">';
			echo 	'<h4>'.$field['options']['labels']['heading'].'</h4>';
			echo 	'<h4>'.$field['options']['selects']['heading'].'</h4>';
			echo '</div>';
			foreach($field['options']['labels']['data'] as $key=>$value){
				echo '<div class="mapping-row">';
					echo '<div class="label">';
					if(is_object($value)){
						echo '	<label id="'.$key.'">'.$value->title.'</label>';
						
					}else{
						echo '	<label id="'.$value.'">'.$value.'</label>';
					}
					echo '</div>';
					echo '<div class="text_box">';
						$stored = $quickbooks->$section->$id;
						echo '<input type="text" name="'.$meta_box['meta_key'].'[', $field['id'], '][',$key,']" id="'.$meta_box['meta_key'].'[', $field['id'], '][',$key,']" value="'.$stored[$key].'" size="30" />';
					echo '</div>';
				echo '</div>';
			echo '</div>';
				}
			break;
        case 'radio':
            foreach ($field['options'] as $option) {
                echo '<input type="radio" name="'.$meta_box['meta_key'].'[', $field['id'], ']" value="', $option['value'], '"', $meta == $option['value'] ? ' checked="checked"' : '', ' />', $option['name'];
            }
            break;
        case 'checkbox':
			echo '
            		<input type="checkbox"   name="'.$meta_box['meta_key'].'[', $field['id'], ']" id="'.$meta_box['meta_key'].'[', $field['id'], ']"', $meta ? ' checked="checked"' : '', ' />
            	
            	';
            break;
    }
    echo     '<td>',
        '</tr>';
}
echo '</table>';
}
/*
 * Ajax Calls 
 */

// Check qwc download
add_action('wp_ajax_check_qwc', 'check_qwc');
function check_qwc(){
	$nonce = $_POST['check_qwc_nonce'];
	//echo $nonce;
	// check to see if the submitted nonce matches with the
	// generated nonce we created earlier
	if ( ! wp_verify_nonce( $nonce, 'check_qwc_nonce' ) )
    die ( 'Busted!');
	$check = get_option('webconnector_generated');
	$connected = get_option('quickbookspos_connected');
	if($connected){
		echo $check;	
	}else{
		return false;
	}
	
}
/* Post QWC details to db when generating file*/
add_action('wp_ajax_create_qwc', 'create_qwc');  
function create_qwc(){
	global $wpdb;
	$nonce = $_POST['create_qwc_nonce'];
	//echo $nonce;
	// check to see if the submitted nonce matches with the
	// generated nonce we created earlier
	if ( ! wp_verify_nonce( $nonce, 'create_qwc_nonce' ) )
    die ( 'Busted!');
	
	$webconnector = new SOD_QuickbooksPOS_Data;
	$args = array(
			'frequency'		=> '',
			'username'		=> $_POST['username'],
			'password'		=> $webconnector->encode($_POST['password']),
			'ssl'			=> $_POST['ssl'],
			'computer_name'	=> $_POST['computer_name'],
			'company_data'	=> $_POST['company_data'],
			'version'		=> $_POST['version'],
			'server'		=> 'Computer Name='.$_POST['computer_name'].';Company Data='.$_POST['company_data'].';Version='.$_POST['version']
	);
	update_option("sod_qbpos_webconnector", $args);
	// $wpdb->get_results( $wpdb->prepare( "DELETE FROM quickbooks_user" ) );
	// $wpdb->get_results( $wpdb->prepare( "UPDATE quickbooks_queue SET qb_username = '%s'", $_POST['username'] ) );
	$dsn = 'mysql://'.DB_USER.':'.DB_PASSWORD.'@'.DB_HOST.'/'.DB_NAME;
	$server = 'Computer Name='.$_POST['computer_name'].';Company Data='.$_POST['company_data'].';Version='.$_POST['version'];
	QuickBooks_Utilities::createUser($dsn, $_POST['username'], $_POST['password'],$company_file=$server);
	update_option('webconnector_generated',1);
	$connector = get_option('quickbookspos_connected');
	$Queue = new QuickBooks_WebConnector_Queue($webconnector->dsn);
	$Queue->enqueue('QBPOS_CHECK_CONNECTION',uniqid(),0, NULL, $quickbooks->user);	
	echo "User Created";
}

/*
 * Refresh Accounts to pull back from QB
 */
add_action('wp_ajax_refresh_accounts', 'refresh_accounts');
function refresh_accounts(){
	$nonce = $_POST['refresh_account_nonce'];
	if ( ! wp_verify_nonce( $nonce, 'refresh_account_nonce' ) )
    die ( 'Busted!');
	$option = $_POST['option'];
	delete_option($option);
	delete_option('qbpos_started_setup');
	$quickbooks = new SOD_QuickbooksPOS_Data;
	$quickbooks->check_defaults();
}
add_action('wp_ajax_validate_products', 'sod_qbpos_validate_products');
function sod_qbpos_validate_products(){
	$nonce = $_POST['refresh_account_nonce'];
	if ( ! wp_verify_nonce( $nonce, 'refresh_account_nonce' ) )
    die ( 'Busted!');
	$quickbooks = new SOD_QuickbooksPOS_Data;
	$Queue = new QuickBooks_WebConnector_Queue($quickbooks->dsn);
	$args = array( 
		'post_type'=> array(
			'product',
		),
		'posts_per_page'=>-1,
		 'post_status'=>'publish'
	);
	$query = new WP_Query();
	
	$query->query($args);
	
	$products = $query->posts;
	if($products){
		
		foreach($products as $product){
			if(function_exists('get_product')):
				$_wc_product = get_product( $product->ID );
			else:
				if($product->post_parent > 0):
					$_wc_product = new WC_Product_Variation( $product->ID );	
				else:
					$_wc_product = new WC_Product( $product->ID );
				endif;
					
			endif;
			$sync_status 	= get_post_meta( $product->ID, '_sync_status', TRUE);
			$sku = $_wc_product->get_sku();
			if($sku !='' && $sync_status == 'on'):
				$Queue->enqueue('QBPOS_ITEM_INVENTORY_QUERY', $product->ID, 999, NULL, $quickbooks->user);
			endif;
		}
	}
	$args = array( 
		'post_type'=> array(
			'product_variation'
		),
		'posts_per_page'=>-1,
		 'post_status'=>'publish'
	);
	$query = new WP_Query();
	
	$query->query($args);
	
	$products = $query->posts;
	if($products){
		
		foreach($products as $product){
			if(function_exists('get_product')):
				$_wc_product = get_product( $product->ID );
			else:
				if($product->post_parent > 0):
					$_wc_product = new WC_Product_Variation( $product->ID );	
				else:
					$_wc_product = new WC_Product( $product->ID );
				endif;
			endif;
			$sku 			= $_wc_product->get_sku();
			$post 			= get_post($product->ID);
			$sync_status 	= get_post_meta( $post->post_parent, '_sync_status', TRUE);
			if($sku !='' && $sync_status == 'on' ):
				$Queue->enqueue('QBPOS_ITEM_INVENTORY_QUERY', $product->ID, 9999, NULL, $quickbooks->user);
			endif;
		}
	}	
}
add_action('wp_ajax_prod_refresh', 'prod_refresh');
function prod_refresh(){
	$nonce = $_POST['refresh_nonce'];
	if ( ! wp_verify_nonce( $nonce, 'refresh_nonce' ) )
    die ( 'Busted!');
	$option = $_POST['id'];
	delete_post_meta($option,'_qbpos_data');
	delete_post_meta($option,'_qbpos_item_type');
	delete_post_meta($option,'_qbpos_fullname');
	delete_post_meta($option,'_qbpos_item_number');
	$quickbooks = new SOD_QuickbooksPOS_Data;
	$Queue = new QuickBooks_WebConnector_Queue($quickbooks->dsn);
	$Queue->enqueue('QBPOS_ITEM_INVENTORY_QUERY', $option, 9999, NULL, $quickbooks->user);
}
/*
 * Turn SYNC on/off for individual items
 */
add_action('wp_ajax_sync_item', 'sync_item');
function sync_item(){
	$nonce = $_POST['sync_item_nonce'];
	if ( ! wp_verify_nonce( $nonce, 'sync_item_nonce' ) )
    die ( 'Busted!');
	$ID = $_POST['id'];
	$sync_status = $_POST['sync_status'];
	if($sync_status=="checked"){
		$sync = "on";
	}else{
		$sync = "";
	}
	update_post_meta($ID,'_sync_status',$sync); 
}
add_action('wp_ajax_recheck_defaults', 'recheck_defaults');
function recheck_defaults(){
	$nonce = $_POST['refresh_account_nonce'];
	if ( ! wp_verify_nonce( $nonce, 'refresh_account_nonce' ) )
    die ( 'Busted!');
	$quickbooks = new SOD_QuickbooksPOS_Data;
	$quickbooks->recheck_defaults();
}


function update_account(){
	if ( ! wp_verify_nonce( $nonce, 'update_account_nonce' ) )
    die ( 'Busted!');
	$ID = $_POST['id'];
	$listid = $_POST['listid'];
	$account = $_POST['account'];
	update_post_meta($ID,$account,$listid);
}
add_action('wp_ajax_use_stored_xml', 'sod_qbpos_use_stored_xml');
function sod_qbpos_use_stored_xml(){
	$nonce = $_POST['requeue_nonce'];
	if ( ! wp_verify_nonce( $nonce, 'requeue_nonce' ) )
    die ( 'Busted!');
	$ID = $_POST['order_id'];
	$sync_status = $_POST['sync_status'];
	if($sync_status=="checked"){
		update_post_meta($ID,'_qbpos_use_stored_xml','yes');
	}else{
		delete_post_meta($ID,'_qbpos_use_stored_xml');
	}
	
}
add_action('wp_ajax_recover_orders', 'sod_quickbooks_recover_orders');
function sod_quickbooks_recover_orders(){
	//Validate permissions	
	$nonce = $_POST['refresh_account_nonce'];
	// check to see if the submitted nonce matches with the
	// generated nonce we created earlier
    if ( ! wp_verify_nonce( $nonce, 'refresh_account_nonce' ) )
	die ( 'Busted!');
	/*
	 * Move to requeueing order
	 */
	$quickbooks = new SOD_QuickbooksPOS_Data;
	$orders 	= $quickbooks->posts_without_meta('_qbpos_data','shop_order','','','');
	$Queue 		= new QuickBooks_WebConnector_Queue($quickbooks->dsn);
	if($orders):
		foreach($orders as $order){
			$cust_list_id = get_post_meta($order->ID,'_customerPOSListID', true);
			$quickbooks->ID = $order->ID;
			update_post_meta($order->ID, '_qbpos_order_requeued','yes');
			if($quickbooks->settings->post_orders =='on'){
			/*1. Check for customer ListID 
			 * if exists, send directly as receipt;
			 */
				if($cust_list_id){
					$Queue->enqueue('QBPOS_ADD_RECEIPT',$order->ID,8, NULL, $quickbooks->user);
				}else{
					/*2. If No CustomerListID
					 * send as add customer request, then add SO/SR request
					 */
					$Queue->enqueue('QBPOS_CUST_QUERY',$order->ID,6, NULL, $quickbooks->user);
				}
			} //If not posting orders, do nothing;
			
		}
	endif;	
}
add_action('wp_ajax_requeue_order', 'sod_qbpos_requeue_order');  
function sod_qbpos_requeue_order(){
	//Validate permissions	
	$nonce = $_POST['requeue_nonce'];
	// check to see if the submitted nonce matches with the
	// generated nonce we created earlier
    if ( ! wp_verify_nonce( $nonce, 'requeue_nonce' ) )
	die ( 'Busted!');
	/*
	 * Move to requeueing order
	 */
	$order_id = $_POST['order_id'];
	$cust_list_id = get_post_meta($order_id,'_customerPOSListID', true);
	$quickbooks = new SOD_QuickbooksPOS_Data;
	$quickbooks->ID = $order_id;
	$Queue = new QuickBooks_WebConnector_Queue($quickbooks->dsn);
	update_post_meta($order_id, '_qbpos_order_requeued','yes');
	if($quickbooks->settings->post_orders =='on'){
			/*1. Check for customer ListID 
			 * if exists, send directly as receipt;
			 */
			if($cust_list_id){
				$Queue->enqueue('QBPOS_ADD_RECEIPT',$order_id,8, NULL, $quickbooks->user);
			}else{
				/*2. If No CustomerListID
				 * send as add customer request, then add SO/SR request
				 */
				$Queue->enqueue('QBPOS_CUST_QUERY',$order_id,6, NULL, $quickbooks->user);
			}
	} //If not posting orders, do nothing;

}
