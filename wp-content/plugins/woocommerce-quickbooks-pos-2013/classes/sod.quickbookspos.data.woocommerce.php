<?php 
class SOD_QuickbooksPOS_Data {
			
	public function __construct()
	{
		$this->dsn = 'mysql://'.DB_USER.':'.DB_PASSWORD.'@'.DB_HOST.'/'.DB_NAME;
		
		$this->order_types = array(
			"sales_receipt"=>"Sales Receipt",
			"sales_order"=>"Sales Order"
		);
		
		$this->customer_mapping = array(
			''=>'None',
			'_billing_first_name'=>'First Name',
			'_billing_last_name'=>'Last Name',
			'_customer_user'=>'Customer ID'
		);
		
		$inventory = 
				array(   
			      'sync_inv' => 'on',
			      'product_identifier' => 'ID',
			      'inv_sync_frequency' => '5',
			      'create_new_items' => 'on',
			      'sync_price'=>'on',
			   );
		
		$settings = 
			    array(
			       'income' => null,
			       'cogs' => null,
			       'deposits' => null,
			       'receivables' => null,
			       'post_orders' => 'on',
			       'post_order_type' => 'sales_order',
			       'create_so_invoices' => 'on',
			       'prefixes' => 
			        	array(
			           		'sr_prefix' => 'SR',
			           		'so_prefix' => 'SO',
			           		'invoice_prefix' => 'INV',
			           		'payment_prefix' => 'PYMNT'
							),
			       'create_customer_account' => 'on',
			       'customer_identifier' => 
			        	array(
			           		'first' =>'_billing_first_name',
			           		'second' =>'_billing_last_name',
			           		'third' => '_customer_user'
						),
				   'customer' => null,
			       'payment_mappings' => 
			        	array(
			           		'bacs' => null,
			           		'cheque' => null,
			           		'paypal' => null
							),
			       'taxes_mappings' => 
			        	array(
			           		'reduced-rate' => null, 
			           		'zero-rate' => null,
			           		'standard' => null
							),
			       'status_mappings' => 
						array(				        
			           		'pending' =>'true',
			           		'on-hold' =>'true',
			           		'processing' =>'true',
			           		'completed' =>'false'
			           	),
			       'shipping_item' => null
			       );
			$iventory_obj 				= get_option('sod_qbpos_inv_defaults')!=false ? get_option('sod_qbpos_inv_defaults') : $inventory;
			$settings_obj 				= get_option('sod_qbpos_defaults')!=false ? get_option('sod_qbpos_defaults') : $settings;
			$this->settings 			= (object)$settings_obj;
			$this->inventory_settings 	= (object)$iventory_obj;
			$webconnector_data 			= get_option('sod_qbpos_webconnector');
			$this->user 				= $webconnector_data['username']; 
	
	}
	function get_sales_tax(){
	
		$tax_info=array(
			'codes'=>array(
				'Tax'=>array(
					'name'=>'Tax',
					'ListID'=>'Tax'
					),
				'Non'=>array(
					'name'=>'Non',
					'ListID'=>'Non'
					)
				)
			);
	
		return $tax_info; 
	
	}
	function update_qbpos_response_info($arr,$key=null){
		$quickbooks_data = get_post_meta($this->ID,'_qbpos_data',array());
	
		if(!empty($key)){
	
			foreach($arr as $arr_key=>$arr_value){
	
				$quickbooks_data[$key][$arr_key]=$arr_value;
	
			}	
	
		}else{
	
			foreach($arr as $arr_key=>$arr_value){
	
				$quickbooks_data[$arr_key]=$arr_value;
	
			}
	
		}
	
		update_post_meta($this->ID , '_qbpos_data',$quickbooks_data);
		if(isset($arr['ItemNumber'])){
			update_post_meta($this->ID , '_qbpos_item_number',$arr['ItemNumber']);
		}
	
	}
	function get_required_accounts(){
	
		$options = array(
			'_sod_qbpos_shipping_methods'=>'QBPOS_SHIPPING',
			'_sod_qbpos_departments'=>'QBPOS_DEPARTMENTS',
			'_sod_qbpos_vendors'=>'QBPOS_VENDORS',
			'_sod_qbpos_customer_accounts'=>'QBPOS_CUST_ACCT_SETUP',
			'_sod_qbpos_price_levels'=>'QBPOS_PRICELEVELS',
			'_sod_qbpos_salestax_records'=>'QBPOS_TAX_RECORDDS',
		);
	
		return $options;
		
	}
	function check_defaults(){
		$id = uniqid();
	
		$Queue = new  QuickBooks_WebConnector_Queue($this->dsn);
	
		$started = get_option('qbpos_started_setup');
	
		$options = array(
			'_sod_qbpos_shipping_methods'=>'QBPOS_SHIPPING',
			'_sod_qbpos_departments'=>'QBPOS_DEPARTMENTS',
			'_sod_qbpos_customer_accounts'=>'QBPOS_CUST_ACCT_SETUP',
			'_sod_qbpos_price_levels'=>'QBPOS_PRICELEVELS',
			'_sod_qbpos_salestax_records'=>'QBPOS_TAX_RECORDDS',
		);
	
		if(empty($started) && get_option('quickbookspos_connected')):
	
			foreach($options as $name=>$queue_value){
	
				if(!get_option($name)){
	
					$Queue->enqueue($queue_value,$id,99, NULL, $quickbooks->user);	
	
				}	
	
			}
	
			update_option('qbpos_started_setup','started');
	
		endif;
	
		do_action('sod_qbpos_check_defaults', $Queue);
	
	}
	function recheck_defaults(){
		$id = uniqid();
	
		$Queue = new  QuickBooks_WebConnector_Queue($this->dsn);
	
		$started = get_option('qbpos_started_setup');
	
		$options = array(
			'_sod_qbpos_shipping_methods'=>'QBPOS_SHIPPING',
			'_sod_qbpos_departments'=>'QBPOS_DEPARTMENTS',
			'_sod_qbpos_customer_accounts'=>'QBPOS_CUST_ACCT_SETUP',
			'_sod_qbpos_price_levels'=>'QBPOS_PRICELEVELS',
			'_sod_qbpos_salestax_records'=>'QBPOS_TAX_RECORDDS',
		);
	
		foreach($options as $name=>$queue_value){
	
			$Queue->enqueue($queue_value,$id,99, NULL, $quickbooks->user);	
	
		}
	
		update_option('qbpos_started_setup','started');
	
		do_action('sod_qbpos_recheck_defaults', $Queue);
	
	}
	function get_accounts($type){
	
		$option = null;
	
		switch($type){
	
			case "Departments":
	
				$option = "_sod_qbpos_departments";
	
				break;
	
			case "Vendors":
	
				$option = "_sod_qbpos_vendors";
	
				break;
	
			case "PriceLevels":
	
				$option = "_sod_qbpos_price_levels";
	
				break;
	
			case "Customers":
	
				$option = "_sod_qbpos_customer_accounts";
	
				break;
	
	
			case "PaymentMethods":
	
				$option = "_sod_qbpos_payment_methods";
	
				break;
	
			case "Shipping":
	
				$option = "_sod_qbpos_shipping_options";
	
				break;
	
			case "SalesTaxCodes":
	
				$option = "_sod_qbpos_salestax_codes";
	
				break;
		
			case "SalesTaxRecords":
	
				$option = "_sod_qbpos_salestax_records";
	
				break;
	
		}
	
		return get_option($option);
	
	}
	
	function set_accounts($type, $arr){
	
		$option = null;
	
		switch($type){
	
			case "Departments":
	
				$option = "_sod_qbpos_departments";
	
				break;
	
			case "Vendors":
	
				$option = "_sod_qbpos_vendors";
	
				break;
	
			case "PriceLevels":
	
				$option = "_sod_qbpos_price_levels";
	
				break;
	
			case "Customers":
	
				$option = "_sod_qbpos_customer_accounts";
	
				break;
	
			case "PaymentMethods":
	
				$option = "_sod_qbpos_payment_methods";
	
				break;
	
			case "Shipping":
	
				$option = "_sod_qbpos_shipping_options";
	
				break;
	
	
			case "SalesTaxCodes":
	
				$option = "_sod_qbpos_salestax_codes";
	
				break;
	
			case "SalesTaxRecords":
	
				$option = "_sod_qbpos_salestax_records";
	
				break;
		}
	
		update_option($option,$arr);
	
		return true;
	}
	function set_preferences($arr){
		
		update_option('quickbookspos_connected',$arr);
		
	}
	function update_customer_listid($ID, $value){
		
		update_post_meta($ID,'_customerPOSListID',$value);
		
	}
	function update_price($item, $arr){
		$array  		= maybe_unserialize($arr);
		$quickbooks 	= new SOD_QuickbooksPOS_Data();
		$sale_price 	= false;
		$price 			= false;
		$price_mappings = $quickbooks->settings->pricelevel_mappings;
		switch($price_mappings['regular_price']){
			case 'PriceLevel1':
				$reg_price = $array['Price1'];
				$price = $array['Price1'];
			break;
			case 'PriceLevel2':
				$reg_price = $array['Price2'];
				$price = $array['Price2'];
			break;
			case 'PriceLevel3':
				$reg_price = $array['Price3'];
				$price = $array['Price3'];
			break;
			case 'PriceLevel4':
				$reg_price = $array['Price4'];
				$price = $array['Price4'];
			break;
			case 'PriceLevel5':
				$reg_price = $array['Price5'];
				$price = $array['Price5'];
			break;
		}
		if(isset($reg_price)):
			update_post_meta($item->ID,'_regular_price',$reg_price);
		endif;
		if(isset($price)):
			update_post_meta($item->ID,'_price',$price);
		endif;
		switch($price_mappings['sale_price']){
			case 'PriceLevel1':
				$sale_price = $arr['Price1'];
			break;
			case 'PriceLevel2':
				$sale_price = $arr['Price2'];
			break;
			case 'PriceLevel3':
				$sale_price = $arr['Price3'];
			break;
			case 'PriceLevel4':
				$sale_price = $arr['Price4'];
			break;
			case 'PriceLevel5':
				$sale_price = $arr['Price5'];
			break;
		}
		if(isset($sale_price)):
			if((int)$sale_price >0 && $sale_price < $price):
				update_post_meta( $item->ID, '_sale_price_dates_to', '' );	
				update_post_meta( $item->ID,'_sale_price',$sale_price);
				update_post_meta($item->ID,'_price',$sale_price);
			else:
				delete_post_meta( $item->ID,'_sale_price');
			endif; 
		endif;
		do_action('sod_qbpos_update_price', $item, $arr);
	}
	
	function update_stock($ID,$qty){
		$quickbooks = new SOD_QuickbooksPOS_Data;
		$change_status = isset($quickbooks->inventory_settings->change_stock_status)? $quickbooks->inventory_settings->change_stock_status : "off";
		$item_type = get_post_meta($ID, '_qbpos_item_type', true);
		if($item_type == "Inventory"):
			update_post_meta($ID,'_stock',$qty);
			if($change_status == "on"):
				if((int)$qty>0){
					update_post_meta($ID,'_stock_status','instock');
				}else{
					update_post_meta($ID,'_stock_status','outofstock');
				}
			endif;
			$this->kill_transients($this->ID);
		endif;
		do_action('sod_qbpos_update_stock', $ID, $qty);
	
	}
	
	function get_cart_tax_classes(){
		$temp = array_filter(array_map('trim', explode("\n", get_option('woocommerce_tax_classes'))));
		$tax_classes = array();
		$tax_classes['standard']="Standard"; 
		foreach($temp as $item){
			$tax_classes[str_replace(" ","_",strtolower($item))]=$item;
		}
		$tax_classes = apply_filters('sod_qbpos_get_cart_tax_classes', $tax_classes);
		return $tax_classes;
	}
	
	function update_item_by_sku($ID, $arr){
		global $wpdb;
		$gsm 		= get_option('woocommerce_manage_stock');
		$quickbooks = new SOD_QuickbooksPOS_Data;
		$key 		= $quickbooks->inventory_settings->product_identifier;
		$value 		= $quickbooks->inventory_settings->qbpos_identifier;
		if($ID):
			$product = get_post($ID);
			if(function_exists('get_product')):
				$wc_product = get_product($ID);
			else:
				$wc_product = new WC_Product($ID);
			endif;
			if($product->post_parent > 0 ):
				$parent_id 		= $product->post_parent;
				$sync_status 	= get_post_meta($parent_id, '_sync_status', true) ? get_post_meta($parent_id, '_sync_status', true) : false;
				$manage_stock 	= get_post_meta($parent_id, '_manage_stock', true) ? get_post_meta($parent_id, '_manage_stock', true) : false;
			else:
				$sync_status 	= get_post_meta($ID, '_sync_status', true) ? get_post_meta($ID, '_sync_status', true) : false;;
				$manage_stock 	= get_post_meta($ID, '_manage_stock', true) ? get_post_meta($ID, '_manage_stock', true) : false;;
			endif;
			if($sync_status == 'on'):
				update_post_meta($ID,'_qbpos_data',$arr);
				update_post_meta($ID,'_qbpos_item_number',$arr['ItemNumber']);
				update_post_meta($ID,'_qbpos_item_type',$arr['ItemType']);
				if($quickbooks->inventory_settings->sync_inv=="on" && $arr['ItemType'] !="Service"):
					/*
					 * Transients - qty
					 */
					$qty = $arr['QuantityOnHand'];
					$transient_id = 'wc_product_total_stock_'.$ID;
					delete_transient($transient_id);
					set_transient($transient_id,$qty);
						/*
						 * Check Stock Managements
						/*If manage_stock returns true, we don't have a variant*/
					if($gsm == "yes" && $arr['ItemType'] != "Service"):
						$change_status = isset($quickbooks->inventory_settings->change_stock_status)? $quickbooks->inventory_settings->change_stock_status : "off";
						if($change_status == "on"):
							/*
							 * 1. Qty > 0, manage stock and not a variation
							 */
							if((int)$qty >0 && $manage_stock=="yes" && !$wc_product->post->post_parent && $arr['ItemType'] != "Service"):
								update_post_meta($ID,'_stock_status','instock');
							/*
							 * 2. Qty = 0, manage stock and not a variation
							 */	
							elseif((int)$qty==0 && $manage_stock=="yes" && $wc_product->get_total_stock() > 0 && $arr['ItemType'] != "Service" ):
								update_post_meta($ID,'_stock_status','instock');
							/*
							 * 2. Qty = 0, manage stock and not a variation
							 */	
							elseif($manage_stock == false && $arr['ItemType'] != "Service"):
								update_post_meta($ID,'_stock_status','instock');
							elseif((int)$qty<=0 && $wc_product->get_total_stock()<=0 && $manage_stock == "yes" && $arr['ItemType'] != "Service"):
								update_post_meta($ID,'_stock_status','outofstock');
							else:
								update_post_meta($ID,'_stock_status','instock');
							endif;
						endif;
					else:
						$change_status = isset($quickbooks->inventory_settings->change_stock_status)? $quickbooks->inventory_settings->change_stock_status : "off";
						if($change_status == "on" && $arr['ItemType'] != "Service"):
							if( (int)$qty <=0 && $arr['ItemType'] != "Service" ):
								update_post_meta($ID,'_stock_status','outofstock');
							elseif((int)$qty >0 &&$arr['ItemType'] != "Service" ):
								update_post_meta($ID,'_stock_status','instock');
							endif;
						elseif($change_status == "on" && $arr['ItemType'] == "Service"):
							update_post_meta($ID,'_stock_status','instock');
						endif;
					endif;
				endif;
				if($quickbooks->inventory_settings->sync_price=="on"):
					$quickbooks->update_price($product,$arr);
					if($wc_product->is_type('variable')):
						$wc_product->variable_product_sync();
					endif;
				endif;
				delete_transient('wc_products_onsale');
				delete_transient('wc_hidden_product_ids');
				delete_transient('wc_hidden_from_search_product_ids');
				$wpdb->query("DELETE FROM `$wpdb->options` WHERE `option_name` LIKE ('_transient_woocommerce_unfiltered_product_ids_%')");
				$wpdb->query("DELETE FROM `$wpdb->options` WHERE `option_name` LIKE ('_transient_woocommerce_layered_nav_count_%')");
				delete_transient('wc_product_total_stock_'.$ID);
				delete_transient('wc_product_children_ids_'.$ID);
				if($arr['ItemType'] !="Service"):
					update_post_meta($ID,'_stock',$qty);
				endif;
			endif;
			do_action('sod_qbpos_inv_sync_after', $product);	
		else:
			do_action('sod_qbpos_product_not_found', $product);
 			
		endif;
		
	}
	function inventory_sync_response($ID, $arr){
		$quickbooks = new SOD_QuickbooksPOS_Data;
		$global_stock_management = get_option('woocommerce_manage_stock');
		$quickbooks->update_item_by_sku($ID, $arr);
		return true;
	}
	
	function inventory_check_for_new(){
		wp_reset_query();
		$products = $this->posts_without_meta('_qbpos_data','product','','','');
		$products = apply_filters('sod_qbpos_products_missing_qbpos_data', $products);
		$quickbooks = new SOD_QuickbooksPOS_Data;
		if($products){
			foreach($products as $product){
				$sync_status = get_post_meta($product->ID, '_sync_status', true);
				if(($sync_status =="on")):
					$Queue = new QuickBooks_WebConnector_Queue($quickbooks->dsn);
					$Queue->enqueue('QBPOS_ITEM_INVENTORY_QUERY',$product->ID,1, NULL, $quickbooks->user);
				endif;
			}	
		}
		$variations = $this->posts_without_meta('_qbpos_data','product_variation','','','');
		$variations = apply_filters('sod_qbpos_variations_missing_qbpos_data', $variations);
		if($variations){
			foreach($variations as $variation){
				$post 			= get_post($variation->ID);
				$sync_status 	= get_post_meta($post->post_parent, '_sync_status', true);
				if($sync_status =="on"):
					$Queue = new QuickBooks_WebConnector_Queue($quickbooks->dsn);
						$Queue->enqueue('QBPOS_ITEM_INVENTORY_QUERY',$post->ID,1, NULL, $quickbooks->user);
					endif;	
				}	
			}
	
	}
	function recheck_stock_status(){
		$args = array( 
			'post_type'=> array(
				'product',
			),
			'posts_per_page'=>-1
		);
		$query = new WP_Query();
		$query->query($args);
		
		$products = $query->posts;
		
		if($products){
			
			foreach($products as $product){
				if(function_exists('get_product')):
					
					$woo  = get_product( $product->ID );
					
				else:
					
					$woo  = new WC_Product($product->ID);
					
				endif;
				
					
				$sync_status 	= get_post_meta($product->ID, '_sync_status', true) ? get_post_meta($product->ID, '_sync_status', true) : false;
					
				if($sync_status == "on") :
					if (
						$woo->managing_stock() && 
						!$woo->backorders_allowed() && 
						$woo->get_total_stock()<=0
					) :
						update_post_meta($woo->id, '_stock_status', 'outofstock');
					// Instock
					elseif(
						$woo->managing_stock() && 
						(
							$woo->backorders_allowed() || 
							$woo->get_total_stock()>0
						)
					) :
						update_post_meta($woo->id, '_stock_status', 'instock');
					endif;
				endif;
			};
		}		
		do_action('sod_qb_recheck_stock_status_after');		
	}
	function recheck_one_stock_status( $ID ){
		if(function_exists('get_product')):
			$woo  = get_product( $ID );
		else:
			$woo  = new WC_Product( $ID );
		endif;
		$sync_status 	= get_post_meta( $ID, '_sync_status', true ) ? get_post_meta( $ID, '_sync_status', true ) : false;
		if($sync_status == "on") :
			if (
				$woo->managing_stock() && 
				!$woo->backorders_allowed() && 
				$woo->get_total_stock()<=0
			) :
				update_post_meta($woo->id, '_stock_status', 'outofstock');
			// Instock
			elseif(
				$woo->managing_stock() && 
				(
					$woo->backorders_allowed() || 
					$woo->get_total_stock()>0
				)
			) :
				update_post_meta($woo->id, '_stock_status', 'instock');
			endif;
		endif;
	}
	
	
	
	/*
	$meta_key    - What meta key to check against (required)
	$post_type   - What post type to check (default - post)
	$post_status - What post status to check (default - publish)
	$fields      - Whether to query all the post table columns, or just a select one ... all, titles, ids, or guids (all returns an array of objects, others return an array of values)
	*/
	function posts_without_meta( $meta_key = '', $post_type = 'post', $post_status = 'publish', $fields = 'all' ) {
		global $wpdb;
	
		if( !isset( $meta_key ) || !isset( $post_type ) || !isset( $post_status ) || !isset( $fields ) )
	
			return false;
			// Meta key is required
	
		if( empty( $meta_key ) )
	
			return false;
		// All parameters are expected to be strings
		if( !is_string( $meta_key ) || !is_string( $post_type ) || !is_string( $post_status ) || !is_string( $fields ) )
	
			return false;
	
		if( empty( $post_type ) )
	
			$post_type = 'post';
	
		if( empty( $post_status ) )
	
			$post_status = 'publish';
	
		if( empty( $fields ) )
	
			$fields = 'all';
		
		// Since all parameters are strings, bind them into one for a cheaper preg match (rather then doing one for each)
		$possibly_unsafe_text = $meta_key . $post_type . $post_status . $fields;
		// Simply die if anything not a letter, number, underscore or hyphen is present
		if( preg_match( '/([^a-zA-Z0-9_-]+)/', $possibly_unsafe_text ) ) {
	
			wp_die( 'Invalid characters present in call to function (valid chars are a-z, 0-9, A-Z, underscores and hyphens).' );
	
			exit;
		}
		
		switch( $fields ) :
	
			case 'ids':
	
				$cols = 'p.ID';
	
				break;
	
			case 'titles':
	
				$cols = 'p.post_title';
	
				break;
	
			case 'guids':
	
				$cols = 'p.guid';
	
				break;
	
			case 'all':
	
			default:
	
				$cols = 'p.*';
	
				break;
		endswitch;
		
		if( 'all' == $fields ){
	
			$result = $wpdb->get_results( $wpdb->prepare( "
	
				SELECT $cols FROM {$wpdb->posts} p
	
				WHERE NOT EXISTS
	
				(
	
					SELECT pm.* FROM {$wpdb->postmeta} pm
	
					WHERE p.ID = pm.post_id
	
					AND pm.meta_key = '%s'
				)
	
				AND p.post_type = '%s'
	
				AND p.post_status = '%s'
	
				", 
	
				$meta_key, 
	
				$post_type, 
	
				$post_status 
	
			) );
		// get_col is nicer for single column selection (less data to traverse)
		}else{
			 
			$result = $wpdb->get_col( $wpdb->prepare( "
	
				SELECT $cols FROM {$wpdb->posts} p
	
				WHERE NOT EXISTS
				(
	
					SELECT pm.* FROM {$wpdb->postmeta} pm
	
					WHERE p.ID = pm.post_id
	
					AND pm.meta_key = '%s'
				)
	
				AND p.post_type = '%s'
	
				AND p.post_status = '%s'
	
				", 
	
				$meta_key, 
	
				$post_type, 
	
				$post_status 
	
			) );
	
		}	
	
		return $result;
	}
	function kill_transients($post_id){
		global $wpdb;
		
		delete_transient('woocommerce_products_onsale');
		
		if ($post_id>0) :
		
			$post_id = (int) $post_id;
		
			delete_transient('woocommerce_product_total_stock_'.$post_id);
		
		else :
		
			$wpdb->query("DELETE FROM `$wpdb->options` WHERE `option_name` LIKE ('_transient_woocommerce_product_total_stock_%')");
		
		endif;
	}
	public function safe_b64encode($string) {
        	
      	$data = base64_encode($string);
        
        $data = str_replace(array('+','/','='),array('-','_',''),$data);
        
        return $data;
    }
 	public function safe_b64decode($string) {
        	
        $data = str_replace(array('-','_'),array('+','/'),$string);
        
        $mod4 = strlen($data) % 4;
        
        if ($mod4) {
        	
            $data .= substr('====', $mod4);
        
        }
        
        return base64_decode($data);
    }
    public function encode($value){
 	    	 
 	    if(!$value){return false;}
        
        $text = $value;
        
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        
        $crypttext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $this->skey=null, $text, MCRYPT_MODE_ECB, $iv);
        
        return trim($this->safe_b64encode($crypttext)); 
    }
    public function decode($value){
        	
        if(!$value){return false;}
        
        $crypttext = $this->safe_b64decode($value); 
        
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        
        $decrypttext = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $this->skey=null, $crypttext, MCRYPT_MODE_ECB, $iv);
        
        return trim($decrypttext);
    }
	public static function get_tax_class($tax_class, $tax_status){
		$quickbooks = new SOD_QuickbooksPOS_Data;
		//$tax_class = $item['_tax_class'];
		//$tax_status = $item['_tax_status'];
		if($tax_status=="taxable"){
		
			if(empty($tax_class)||$tax_class==""){
		
				$tax_class="standard";
		
			}
		
			$new_tax_class = str_replace("-","_",$tax_class);
		
			$taxid = $quickbooks->settings->taxcodes_mappings->$new_tax_class;
		
		}else{
		
			$taxid="";
		
		}
		
		return $taxid;
	}
	function get_name(){
		$first 	= substr($this->settings->customer_identifier->first,1);
		
		$second = substr($this->settings->customer_identifier->second,1);
		
		$third 	= substr($this->settings->customer_identifier->third,1);
		
		$customer = ($first?$this->$first:false) . " " . ($second?$this->$second:false) . " " .($third?$this->$third:false );
		
		return $customer;
	}
	function get_customer_info($quickbooks,$order){
		if(!$quickbooks->settings->create_customer_account){
		
			$cust_ident = '<ListID>'.$quickbooks->settings->customer.'</ListID>';
		
		}else{
		
			if($quickbooks->settings->create_customer_account=="on"){
		
				if(!$order->_customerListID){
				
					$cust_ident = '<FullName>'.trim(webconnector::map_cust_identifier($order->ID)).'</FullName>';
		
				}else{
		
					$cust_ident = '<ListID>'.$order->_customerListID.'</ListID>';
		
				}
		
			}elseif($quickbooks->settings->create_customer_account=="off" || $quickbooks->settings->create_customer_account==""){
		
				$cust_ident = '<ListID>'.$quickbooks->settings->customer.'</ListID>';
		
			}
		
		}
		
		$cust_ident = apply_filters('sod_qbpos_get_customer_info', $cust_ident, $quickbooks, $item);
		
		return $cust_ident;		
	}
	function get_item_info($item, $use_tax){
		$quickbooks = new SOD_QuickbooksPOS_Data;
		$attributes = false;
		$id = isset($item['product_id']) ? $item['product_id'] : $item['id'];
		/*
		 * This is for variations
		 */
		if(isset($item['variation_id']) && $item['variation_id']!=="" && $item['variation_id']!==0){
		
			$qb_data = get_post_meta($item['variation_id'],'_qbpos_data',true);
		
			$meta = get_post_custom($item['variation_id']);
		
			$ident ='<ListID>'. $qb_data['ListID']. '</ListID>';
			if(isset($meta['_tax_class']) && isset($meta['_tax_status'])):
				$tax = $this->get_tax_class($meta['_tax_class'][0],$meta['_tax_status'][0]);
			endif;
		
		}else{
			/*
			 * This is for simple products
			 */
			$qb_data = get_post_meta($id,'_qbpos_data',true);
		
			$meta = get_post_custom($id);
		
			$ident ='<ListID>'. $qb_data['ListID']. '</ListID>';
		
			//$tax = $this->get_tax_class($meta['_tax_class'][0],$meta['_tax_status'][0]);
		}
		
		$tax_line="";
		
		if($use_tax && $use_tax > 0):
		
			if(!empty($meta['_tax_class'][0]) && $tax !==""){
		
				$tax_line ='<TaxCode>'.$meta['_tax_class'][0].'</TaxCode>';
		
			}else{
		
				$tax_line ='';
		
			}
		
		elseif ((int)$use_tax == 0):
		
			$tax_line="<TaxCode>Non</TaxCode>";
		
		endif;
		
		$line_item = $ident.'<ExtendedPrice>'.number_format($item['line_subtotal'],2,'.','').'</ExtendedPrice><Qty>'.$item['qty'].'</Qty>'.$tax_line;
		
		$line_item = apply_filters('sod_qbpos_get_item_info',$line_item, $item, $use_tax, $quickbooks);
		
		return $line_item;
	}
	function get_sales_tax_code($ListID){
		$items = get_option('_sod_qbpos_all_items');
		
		$tax_code ='';
		
		foreach($items as $key=>$value){
		
			if(is_array($value)){
		
				if($value['ListID'] ==$ListID){
		
					$tax_code = $value['SalesTaxCodeRef'];
		
				}
		
			}
		
		}
		
		$tax_code = apply_filters('sod_qbpos_get_sales_tax_code', $tax_code, $ListID);
		
		return $tax_code;
	}
	function get_sales_tax_listid($order_id){
		$tax_rates = array(
			'global'=>array(),
			'local'=>array()
		);
		
		if(!empty($this->taxes)){
		
			foreach($this->taxes as $tax_rate){
				
				$label = $this->format_tax_rate($tax_rate['label']);
				
				if($this->settings->taxrates->$label):
		
					$tax_rates['global'][] = $this->settings->taxrates->$label;
		
				elseif($this->settings->taxrates_local->$label):
		
					$tax_rates['local'][] = $this->settings->taxrates_local->$label;
		
				endif;
		
			}
		
		}else{
		
			$tax_rates['global'][] = $this->settings->no_tax;
		
		}
		
		$tax_rates = apply_filters('sod_qbpos_get_sales_tax_listid', $tax_rates, $order_id);
		
		return $tax_rates; 	
	}
	function get_local_sales_tax_listid($order_id){
		$quickbooks = new SOD_QuickbooksPOS_Data;
		
		$order_sales_tax = get_post_meta($order_id,'_order_taxes',true);
		
		$tax_rates = array();
		
		if($order_sales_tax){
		
			foreach($order_sales_tax as $tax_rate){
		
				$label = $quickbooks->format_tax_rate($tax_rate['label']);
		
				$tax_rates[] = $quickbooks->settings->taxrates_local->$label;
		
			}
		
			$tax_rates = apply_filters('sod_qbpos_get_local_sales_tax_listid', $tax_rates, $order_id);
		
			return $tax_rates; 	
		
		}else{
		
			return false;
		
		}
	
	}
	
	function sod_tax_row_label( $selected ) {
		global $woocommerce;
	
		$return = '';
		// Get counts/countries
		$counties_array = array();
	
		$states_count = 0;
	
		if ($selected) foreach ($selected as $country => $value) :
	
			$country = woocommerce_clean($country);
	
			if (sizeof($value)>0 && $value[0]!=='*') :
	
				$states_count+=sizeof($value);
	
			endif;
	
			if (!in_array($country, $counties_array)) $counties_array[] = $woocommerce->countries->countries[$country];
	
		endforeach;
		
		$states_text = '';
	
		$countries_text = implode(', ', $counties_array);
		// Show label
		if (sizeof($counties_array)==0) :
	
			$return .= __('No countries selected', 'woocommerce');
	
		elseif ( sizeof($counties_array) < 6 ) :
	
			if ($states_count>0) $states_text = sprintf(_n('(1 state)', '(%s states)', $states_count, 'woocommerce'), $states_count);
	
			$return .= $countries_text . ' ' . $states_text;
	
		else :
	
			if ($states_count>0) $states_text = sprintf(_n('and 1 state', 'and %s states', $states_count, 'woocommerce'), $states_count);
	
			$return .= sprintf(_n('1 country', '%1$s countries', sizeof($counties_array), 'woocommerce'), sizeof($counties_array)) . ' ' . $states_text;
	
		endif;
	
		$return = apply_filters('sod_qbpos_tax_row_label', $return, $selected);
	
		return $return;
	
	}
	function format_tax_rate($label){
		global $woocommerce;
		
		if (!$label) :
		
			$label = $woocommerce->countries->tax_or_vat();
		
		endif;
		// Add % to label
		
		$temp = str_replace(array('(',')','%','.'),'', $label);
		
		$formatted = str_replace(" ","_",strtolower($temp));
		
		$formatted = apply_filters('sod_qbpos_format_tax_rate', $formatted, $label);
		
		return $formatted;
	}
}

add_action( 'admin_init', 'sod_init_qbpos' );
function sod_init_qbpos(){
	global $quickbooks;

	$quickbooks = new SOD_QuickbooksPOS_Data();	

	$quickbooks->dsn = 'mysql://'.DB_USER.':'.DB_PASSWORD.'@'.DB_HOST.'/'.DB_NAME;
}