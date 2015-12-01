<?php
/*
Plugin Name: SOD QuickBooks Point of Sale 2013 Connector 
Plugin URI: http://www.61extensions.com
Description: A Quickbooks connector for woocommerce.
Version: 1.0.8
Author: 61 Designs
Author URI: http://www.61extensions.com
License: Commercial License
Requires at least: 3.1
Tested up to: 3.3
Copyright Sixty-One Designs, Inc 2014
*/

/**
 * Required functions
 **/
include(plugin_dir_path( __FILE__ ).'/admin/interface.php');
include(plugin_dir_path( __FILE__ ).'/admin/settings.php');
include(plugin_dir_path( __FILE__ ).'QuickBooks.php');
include(plugin_dir_path( __FILE__ ).'/classes/sod.quickbookspos.data.woocommerce.php');
include(plugin_dir_path( __FILE__ ).'/classes/sod.quickbookspos.webconnector.php');

/*
 * Initialize Plugin 
 */
register_activation_hook( __FILE__, 'sod_quiickbooks_activation' );

//Generate a unique access key for the QB WebConnector
function sod_quiickbooks_activation(){
	global $quickbooks;
	if (class_exists( 'Woocommerce' ) ) :
		$quickbooks->dsn = 'mysql://'.DB_USER.':'.DB_PASSWORD.'@'.DB_HOST.'/'.DB_NAME;
		if(!QuickBooks_Utilities::initialized($quickbooks->dsn)):
				QuickBooks_Utilities::initialize($quickbooks->dsn);
		endif;
		if(!get_option('_qbpos_connector_key')):
			/****Generate unique api key for url access **/
			$length = 12;
			$options = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
			$code = "";
			for($i = 0; $i < $length; $i++)
			{
				$key = rand(0, strlen($options) - 1);
				$code .= $options[$key];
			}
			update_option('_qbpos_connector_key',$code);
		endif;
		global $wpdb;
  		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		$quickbooks_config = "CREATE TABLE quickbooks_config (
			  quickbooks_config_id int(10) unsigned NOT NULL AUTO_INCREMENT ,
			  qb_username varchar(40) COLLATE utf8_unicode_ci NOT NULL ,
			  module varchar(40) COLLATE utf8_unicode_ci NOT NULL ,
			  cfgkey varchar(40) COLLATE utf8_unicode_ci NOT NULL ,
			  cfgval varchar(40) COLLATE utf8_unicode_ci NOT NULL ,
			  cfgtype varchar(40) COLLATE utf8_unicode_ci NOT NULL ,
			  cfgopts text COLLATE utf8_unicode_ci NOT NULL ,
			  write_datetime datetime NOT NULL ,
			  mod_datetime datetime NOT NULL ,
			  PRIMARY KEY (quickbooks_config_id)
			);";
		dbDelta($quickbooks_config);
		$quickbooks_log = "CREATE TABLE quickbooks_log (
			  quickbooks_log_id int(10) unsigned NOT NULL AUTO_INCREMENT ,
			  quickbooks_ticket_id int(10) unsigned DEFAULT NULL ,
			  batch int(10) unsigned NOT NULL ,
			  msg text COLLATE utf8_unicode_ci NOT NULL ,
			  log_datetime datetime NOT NULL ,
			  PRIMARY KEY  (quickbooks_log_id) ,
			  KEY quickbooks_ticket_id (quickbooks_ticket_id) ,
			  KEY batch (batch)
			);";
		dbDelta($quickbooks_log);
		$quickbooks_oauth = "CREATE TABLE quickbooks_oauth (
			  quickbooks_oauth_id int(10) unsigned NOT NULL AUTO_INCREMENT ,
			  app_username varchar(255) COLLATE utf8_unicode_ci NOT NULL ,
			  app_tenant varchar(255) COLLATE utf8_unicode_ci NOT NULL ,
			  oauth_request_token varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  oauth_request_token_secret varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  oauth_access_token varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  oauth_access_token_secret varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  qb_realm varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  qb_flavor varchar(12) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  qb_user varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  request_datetime datetime NOT NULL ,
			  access_datetime datetime DEFAULT NULL ,
			  touch_datetime datetime DEFAULT NULL ,
			  PRIMARY KEY  (quickbooks_oauth_id)
			);";
		dbDelta($quickbooks_oauth);
		$quickbooks_recur = "CREATE TABLE quickbooks_recur (
			  quickbooks_recur_id int(10) unsigned NOT NULL AUTO_INCREMENT ,
			  qb_username varchar(40) COLLATE utf8_unicode_ci NOT NULL ,
			  qb_action varchar(32) COLLATE utf8_unicode_ci NOT NULL ,
			  ident varchar(40) COLLATE utf8_unicode_ci NOT NULL ,
			  extra text COLLATE utf8_unicode_ci ,
			  qbxml text COLLATE utf8_unicode_ci ,
			  priority int(10) unsigned DEFAULT '0' ,
			  run_every int(10) unsigned NOT NULL ,
			  recur_lasttime int(10) unsigned NOT NULL ,
			  enqueue_datetime datetime NOT NULL ,
			  PRIMARY KEY  (quickbooks_recur_id) ,
			  KEY  qb_username (qb_username,qb_action,ident) ,
			  KEY  priority (priority)
			);";
		dbDelta($quickbooks_recur);
		$sql = "CREATE TABLE quickbooks_queue (
			  quickbooks_queue_id int(10) NOT NULL AUTO_INCREMENT,
			  quickbooks_ticket_id int(10) DEFAULT NULL,
			  qb_username varchar(40) NOT NULL,
			  qb_action varchar(32) NOT NULL,
			  ident varchar(40) NOT NULL,
			  extra text,
			  qbxml text,
			  priority int(10) DEFAULT '0',
			  qb_status char(1) NOT NULL,
			  msg text,
			  enqueue_datetime datetime NOT NULL,
			  dequeue_datetime datetime DEFAULT NULL,
			  PRIMARY KEY  (quickbooks_queue_id),
			  KEY quickbooks_ticket_id (quickbooks_ticket_id),
			  KEY priority (priority),
			  KEY qb_username (qb_username,qb_action,ident,qb_status),
			  KEY qb_status (qb_status)
			); ";
		dbDelta($sql);
		$quickbooks_ticket = "CREATE TABLE quickbooks_ticket (
			  quickbooks_ticket_id int(10) unsigned NOT NULL AUTO_INCREMENT ,
			  qb_username varchar(40) COLLATE utf8_unicode_ci NOT NULL ,
			  ticket char(36) COLLATE utf8_unicode_ci NOT NULL ,
			  processed int(10) unsigned DEFAULT '0' ,
			  lasterror_num varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  lasterror_msg varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  ipaddr char(15) COLLATE utf8_unicode_ci NOT NULL ,
			  write_datetime datetime NOT NULL ,
			  touch_datetime datetime NOT NULL ,
			  PRIMARY KEY  (quickbooks_ticket_id) ,
			  KEY  ticket (ticket)
			);";
		dbDelta($quickbooks_ticket);
		$quickbooks_user = "CREATE TABLE quickbooks_user (
			  qb_username varchar(40) COLLATE utf8_unicode_ci NOT NULL ,
			  qb_password varchar(255) COLLATE utf8_unicode_ci NOT NULL ,
			  qb_company_file varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  qbwc_wait_before_next_update int(10) unsigned DEFAULT '0' ,
			  qbwc_min_run_every_n_seconds int(10) unsigned DEFAULT '0' ,
			  status char(1) COLLATE utf8_unicode_ci NOT NULL ,
			  write_datetime datetime NOT NULL ,
			  touch_datetime datetime NOT NULL ,
			  PRIMARY KEY  (qb_username)
			);";
		dbDelta($quickbooks_user);
	endif;
}
register_deactivation_hook(__FILE__, 'sod_qbpos_deactivation');
function sod_qbpos_deactivation(){
	global $wpdb;
	$delete_all = apply_filters('sod_qbpos_deactivation', $delete_all);
	if($delete_all):
	    $tables = array(
	    	'quickbooks_user',
	    	'quickbooks_ticket',
	    	'quickbooks_recur',
	    	'quickbooks_queue',
	    	'quickbooks_notify',
	    	'quickbooks_log',
	    	'quickbooks_ident',
			'quickbooks_connection',
			'quickbooks_config'
		);
		$tables = apply_filters('sod_qbpos_truncate_tables', $tables);
	    //Delete any options thats stored also?
	    $options = array(
			'webconnector_generated',
			'qbpos_started_setup',
			'quickbookspos_connected',
			'_sod_qbpos_salestax_records',
			'_sod_qbpos_price_levels',
			'_sod_qbpos_customer_accounts',
			'_sod_qbpos_shipping_options',
			'_sod_qbpos_departments',
			'_sod_qbpos_vendors',
		);
		$options = apply_filters('sod_qbpos_delete_options', $options);
		foreach($options as $option){
			delete_option($option);
		}	
		foreach($tables as $table){
			$wpdb->query("DROP TABLE IF EXISTS $table");
		}
	endif;
}
/*Parse SOAP Server Request*/ 
add_action("parse_request", "sod_qbposconnector_request");
function sod_qbposconnector_request(){
	global $woocommerce;
	$webconnector = new SOD_Webconnector;
	$quickbooks = new SOD_QuickbooksPOS_Data;
	$quickbooks->dsn = 'mysql://'.DB_USER.':'.DB_PASSWORD.'@'.DB_HOST.'/'.DB_NAME;
	$key = get_option('_qbpos_connector_key');
	$map = $webconnector->getFunctionMappings();
	$errmap = $webconnector->getErrorMappings();
	$hooks = array(	);
	$log_level = QUICKBOOKS_LOG_VERBOSE;
	$soapserver = QUICKBOOKS_SOAPSERVER_BUILTIN;		// A pure-PHP SOAP server (no PHP ext/soap extension required, also makes debugging easier)
	$soap_options = array();
	$handler_options = array();		// See the comments in the QuickBooks/Server/Handlers.php file
	$driver_options = array(
		'max_log_history' => 100,    // Limit the number of quickbooks_log entries to 1024
    	'max_queue_history' => 1000     // Limit the number of QuickBooks_WebConnector_Queue entries to 64
    );
	$callback_options = array();
	if(isset($_GET["qbposconnector"])){
		
		//var_dump($item);
		$check_key = $_GET['qbposconnector'];
		if ($check_key==$key){
			$connector = get_option('quickbooks_connected');
			
			$options = get_option('sod_quickbooks_product_sync');
			if(!$options){
					$Queue = new QuickBooks_WebConnector_Queue($quickbooks->dsn);
					$options['last_run']=time();	
					$quickbooks = new SOD_QuickbooksPOS_Data;
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
					update_option('sod_quickbooks_product_sync',$options);
				}else{
					$frequency = !empty($quickbooks->inventory_settings->inv_sync_frequency) ? $quickbooks->inventory_settings->inv_sync_frequency : 5;
					$next_time = (int)$options['last_run'] + (int)($frequency *60);
					if($next_time < time()){
						$Queue = new QuickBooks_WebConnector_Queue($quickbooks->dsn);
						$options['last_run']=time();
						$quickbooks = new SOD_QuickbooksPOS_Data;
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
								$sku 			= $_wc_product->get_sku();
								if($sku !='' && $sync_status == 'on'):
									$Queue->enqueue('QBPOS_ITEM_INVENTORY_QUERY', $product->ID, 9999, NULL, $quickbooks->user);
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
						update_option('sod_quickbooks_product_sync',$options);
						
					}
				}
			do_action('sod_qbpos_main_queue');
			$Server = new QuickBooks_WebConnector_Server($quickbooks->dsn, $map, $errmap, $hooks, $log_level, $soapserver, 'QBPOS_WSDL', $soap_options, $handler_options, $driver_options, $callback_options);
			$response = $Server->handle(true, true);
			die;
		}else{
			die("Nothing here");
		}
	}else{
		
	}
}

/*
 * Add the query variable for the qbconnector
 */
add_filter('query_vars', 'sod_qbposconnector_query_vars');
function sod_qbposconnector_query_vars($vars) {
	$vars[] = 'qbposconnector';
	return $vars;
}
add_filter( 'display_post_states', 'qbpos_exclude' );
function qbpos_exclude( $states ) {
    global $post;
	
    $show_custom_state = null !== get_post_meta( $post->ID, '_exclude_from_website' );
    if ( $show_custom_state ) {
      $states[] = __( 'Exclude From Website' );
	}
    return null;
  }
/*
 * WooCommerce Hooks
 */
//1. Add new order to queue when order is placed
add_action('woocommerce_order_status_completed', 'sod_quickbooks_send_order', 12);
function sod_quickbooks_send_order($order_id){
	global $woocommerce, $post, $quickbooks;
	$order 				= new SOD_QuickbooksPOS_Data;
	$order->ID 			= $post->ID;
	$Queue 				= new QuickBooks_WebConnector_Queue($quickbooks->dsn);
	$qbpos_data 		= get_post_meta($post->ID, '_qbpos_data', true);
	$cust_list_id 		= get_post_meta($post->ID, '_customerPOSListID', true);
	$already_queued		= get_post_meta($order_id, '_qb_initial_queue', true);
	if($order->settings->order_status_trigger == "completed" && $already_queued != "yes" ):
		if(!$qbpos_data):
	//if($error = false):
			if($order->settings->post_orders =='on'){
				/*1. Check for customer ListID 
				 * if exists, send directly as receipt;
				 */
				if($cust_list_id ){
					$Queue->enqueue('QBPOS_ADD_RECEIPT',$order_id,8, NULL, $quickbooks->user);
				}else{
					/*2. If No CustomerListID
					 * send as add customer request, then add SO/SR request
					 */
					$Queue->enqueue('QBPOS_CUST_QUERY',$order_id,6, NULL, $quickbooks->user);
				}
				update_post_meta($order_id, '_qbpos_initial_queue', 'yes');
			} //If not posting orders, do nothing;
		
		endif;
		
	endif;
}
add_action('woocommerce_order_status_pending', 'sod_quickbooks_pending_send_order', 12, 2);
function sod_quickbooks_pending_send_order($order_id, $posted){
	global $woocommerce, $post, $quickbooks;
	$order 				= new SOD_QuickbooksPOS_Data;
	$order->ID 			= $post->ID;
	$Queue 				= new QuickBooks_WebConnector_Queue($quickbooks->dsn);
	$qbpos_data 		= get_post_meta($post->ID, '_qbpos_data', true);
	$cust_list_id 		= get_post_meta($post->ID, '_customerPOSListID', true);
	$already_queued		= get_post_meta($order_id, '_qbpos_initial_queue', true);
	if($order->settings->order_status_trigger == "pending" && $already_queued != "yes" ):
		if(!$qbpos_data):
	//if($error = false):
			if($order->settings->post_orders =='on'){
				/*1. Check for customer ListID 
				 * if exists, send directly as receipt;
				 */
				if($cust_list_id ){
					$Queue->enqueue('QBPOS_ADD_RECEIPT',$order_id,8, NULL, $quickbooks->user);
				}else{
					/*2. If No CustomerListID
					 * send as add customer request, then add SO/SR request
					 */
					$Queue->enqueue('QBPOS_CUST_QUERY',$order_id,6, NULL, $quickbooks->user);
				}
				update_post_meta($order_id, '_qbpos_initial_queue', 'yes');
			} //If not posting orders, do nothing;
		
		endif;
		
	endif;
}
add_action('woocommerce_order_status_processing', 'sod_quickbooks_processing_send_order', 12);
function sod_quickbooks_processing_send_order($order_id){
	global $woocommerce, $post, $quickbooks;
	$order 				= new SOD_QuickbooksPOS_Data;
	$order->ID 			= $post->ID;
	$Queue 				= new QuickBooks_WebConnector_Queue($quickbooks->dsn);
	$already_queued		= get_post_meta($order_id, '_qbpos_initial_queue', true);
	$qbpos_data 		= get_post_meta($post->ID, '_qbpos_data', true);
	$cust_list_id 		= get_post_meta($post->ID, '_customerPOSListID', true);
	if($order->settings->order_status_trigger == "processing" && $already_queued != "yes" ):
		if(!$qbpos_data):
	//if($error = false):
			if($order->settings->post_orders =='on'){
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
				update_post_meta($order_id, '_qbpos_initial_queue', 'yes');
			} //If not posting orders, do nothing;
		
		endif;
		
	endif;
}
?>