<?php 
/*
 * Default Accounts
 */
function sod_qbpos_account_settings(){
	global $quickbooks, $woocommerce;
	$types = array("Income","COGS","Deposits","Receivables","Expense","Assets");
	foreach($types as $type){
		$quickbooks_accounts[$type] = $quickbooks->get_accounts($type);	
	}
	$meta_box = array(
		    'id' => 'sod_qbpos_products',
		    'title' => __('Quickbooks Options', 'woocommerce'),
		    'page' => 'product',
		    'context' => 'normal',
		    'priority' => 'default',
		    'section'=>'settings',
		    'meta_key'=> 'sod_qbpos_defaults',
		    'fields' => array(
	        array(
	            'name' => 'Income Account',
	            'desc' => 'Select the default income account to use',
	            'id' => 'income',
	            'type' => 'qbdata-select',
	            'options'=> $quickbooks_accounts['Income'],
	            'class' =>'chzn-select wide',
	            'refresh_link'=>array(
					'option'=>'_sod_qbpos_income_accounts',
					'title' =>'Refresh Income Accounts from QB'
				)
	        ),
	         array(
	            'name' => 'COGs Account',
	            'desc' => 'Select the default COGs account to use.',
	            'id' => 'cogs',
	            'type' => 'qbdata-select',
	             'options'=> $quickbooks_accounts['COGS'],
	              'class' =>'chzn-select wide',
	            'refresh_link'=>array(
					'option'=>'_sod_qbpos_cogs_accounts',
					'title' =>'Refresh COGS Accounts from QB'
				)
	        ),
	        array(
	            'name' => 'Deposit Account',
	            'desc' => 'Select the default deposit account to use.',
	            'id' => 'deposits',
	            'type' => 'qbdata-select',
	             'options'=> $quickbooks_accounts['Deposits'],
	              'class' =>'chzn-select wide',
	            'refresh_link'=>array(
					'option'=>'_sod_qbpos_deposit_accounts',
					'title' =>'Refresh Deposit Accounts from QB'
				)
	        ),
	       
	   	)
	);
		sod_form_builder($meta_box);
}
/*
 * Posting Options
 */
function sod_qbpos_posting_settings(){
	global $quickbooks;
	
	$prefix_labels = array(
		'sr_prefix'=>'Sales Receipt Prefix',
		'so_prefix'=>'Sales Order Prefix',
		'invoice_prefix'=>'Invoice Prefix',
		'payment_prefix'=>'Payment Prefix'
	);
	$prefix_text_boxes = array(
		'sr_prefix',
		'so_prefix',
		'invoice_prefix',
		'payment_prefix'
		);
	$meta_box = array(
		    'id' => 'sod_qbpos_products',
		    'title' => __('Quickbooks Options', 'woocommerce'),
		    'page' => 'product',
		    'context' => 'normal',
		    'priority' => 'default',
		    'section'=>'settings',
		    'meta_key'=> 'sod_qbpos_defaults',
		    'fields' => array(
		     array(
	            'name' => 'Post Orders to QuickBooks',
	            'desc' => 'Choose whether to post orders to QuickBooks or not.',
	            'id' => 'post_orders',
	            'type' => 'checkbox',
	            'class' =>''
	         ),
	          array(
	            'name' => 'Store Order XML?',
	            'desc' => 'Choose whether to store order XML',
	            'id' => 'store_xml',
	            'type' => 'checkbox',
	            'class' =>''
	         ),
	   		array(
	            'name' => 'How Should Orders be Posted To Quickbooks?',
	            'desc' => 'Choose whether to use Sales Receipts or Sales Orders.',
	            'id' => 'post_order_type',
	            'type' => 'select',
	            'options'=> array(
							"sales_receipt"=>"Sales Receipt",
							
				),
	            'class' =>'chzn-select wide'
	            
	        ),
			 array(
	            'name' 	=> 'Order Status Trigger to send order to QuickBooks?',
	            'desc' 	=> 'Choose whether to use Sales Receipts or Sales Orders.',
	            'id' 	=> 'order_status_trigger',
	            'type' 	=> 'select',
	            'std'	 =>'completed',
	            'options'=> array(
							//"pending"	=>"Pending",
							"processing"=>"Processing",
							"completed"	=>"Completed"
							
				),
	            'class' =>'chzn-select wide'
	            
	        ),
	   		)
	   	);
		$meta_box = apply_filters('sod_qbpos_order_posting_fields', $meta_box);
	   	sod_form_builder($meta_box);
	
	}
/*
 * Payment Mappings
 */
function sod_qbpos_payments_settings(){
	global $quickbooks,$woocommerce;
	$payments = array(
		"Cash"=>array(
			'ListID'=>"Cash"
			),
		"Cash"=>array(
			'ListID'=>"Cash"
			), 
		"Check"=>array(
			'ListID'=>"Check"
			),
		"Visa"=>array(
			'ListID'=>"Visa"
			), 
		"Mastercard"=>array(
			'ListID'=>"Mastercard"
			),
		"Discover Card"=>array(
			'ListID'=>"Discover Card"
			),
		"American Express"=>array(
			'ListID'=>"American Express"
			),
		"Diners Club"=>array(
			'ListID'=>"Diners Club"
			),
		"JCB"=>array(
			'ListID'=>"JCB"
			) 
	);
	$payments	= apply_filters('sod_qbpos_payment_methods', $payments);
	
	$w_payments = $woocommerce->payment_gateways->payment_gateways();
	$w_payments = apply_filters('sod_qbpos_wc_payment_gateways', $w_payments);
	if(empty($w_payments)){
		echo  'No Payment Methods available';
	}else{
	$meta_box = array(
		    'id' => 'sod_qbpos_products',
		    'title' => __('Quickbooks Options', 'woocommerce'),
		    'page' => 'product',
		    'context' => 'normal',
		    'priority' => 'default',
		    'section'=>'settings',
		    'meta_key'=> 'sod_qbpos_defaults',
		    'fields' => array(
		    	array(
		            'name' => 'Map Your WooCommerce Payment Methods to Your QuickBooks Payment Methods',
		            'desc' => 'Choose whether to automatically create individual customer accounts in QuickBooks. If you choose no, you\'ll need to specify an default account to post to.',
		            'id' => 'payment_mappings',
		            'type' => 'mapping',
		            'options'=>array(
		            	'labels'=>array(
		            		'heading'=>'Installed Payment Method',
		            		'data'=>$w_payments
		            		),
		            	'selects'=>array(
		            		'heading'=>'Quickbooks Payment Methods',
		            		'class' =>'chzn-select medium',
		            		'data'=>$payments
			            )
		           ),
		           'refresh_link'=>array(
						'option'=>'_sod_qbpos_payment_methods',
						'title' =>'Refresh Payment Methods'
					)
				)
			)
		);
		$meta_box = apply_filters('sod_qbpos_payment_method_fields', $meta_box);
	   	sod_form_builder($meta_box);
	}
}
/*
 * Payment Mappings
 */
function sod_qbpos_pricelevels_settings(){
	global $quickbooks,$woocommerce;
	$price_levels = array();
	
	$price_levels = $quickbooks->get_accounts('PriceLevels');
	if($price_levels):
		foreach($price_levels as $key => $price_level){
			$qbpos_prices[$key]=$price_level['Name'];
		}
	endif;
	$prices = array(
		'_regular_price'=>'Regular Price',
		'_sale_price'=>'Sale Price'
	);
	if(empty($prices)){
		echo  'No Price Levels available';
	}else{
	$meta_box = array(
		    'id' => 'sod_qbpos_products',
		    'title' => __('Quickbooks Options', 'woocommerce'),
		    'page' => 'product',
		    'context' => 'normal',
		    'priority' => 'default',
		    'section'=>'settings',
		    'meta_key'=> 'sod_qbpos_defaults',
		    'fields' => array(
		    	array(
		            'name' => 'Map Your WooCommerce Payment Methods to Your QuickBooks Payment Methods',
		            'desc' => 'Choose whether to automatically create individual customer accounts in QuickBooks. If you choose no, you\'ll need to specify an default account to post to.',
		            'id' => 'pricelevel_mappings',
		            'type' => 'mapping',
		            'options'=>array(
		            	'labels'=>array(
		            		'heading'=>'QuickBooks POS Price Levels',
		            		'data'=>$prices
		            		),
		            	'selects'=>array(
		            		'heading'=>'Price / Sale Price',
		            		'class' =>'chzn-select medium',
		            		'data'=>$qbpos_prices
			            )
		           ),
		           'refresh_link'=>array(
						'option'=>'_sod_qbpos_price_levels',
						'title' =>'Refresh Payment Methods'
					)
				)
			)
		);
		$meta_box = apply_filters('sod_qbpos_price_level_fields', $meta_box);
	   	sod_form_builder($meta_box);
	}
}
function sod_qbpos_departments_settings(){
	global $quickbooks;
	
	$departments 	= $quickbooks->get_accounts("Departments");
	$w_categories	= get_terms('product_cat', array( 'hide_empty' => 0 ));
	$cats 			= array();
	foreach ($w_categories  as $key => $value) {
		$cats[] = $value->slug;
	}
	// if(empty($w_categories)){
		// echo  'No Categories available';
	// }else{
	$meta_box = array(
		    'id' => 'sod_qbpos_products',
		    'title' => __('Quickbooks Options', 'woocommerce'),
		    'page' => 'product',
		    'context' => 'normal',
		    'priority' => 'default',
		    'section'=>'settings',
		    'meta_key'=> 'sod_qbpos_defaults',
		    'fields' => array(
		     array(
	            'name' => 'Default Department',
	            'desc' => 'Select the default department to use.',
	            'id' => 'default_department',
	            'type' => 'qbdata-select',
	            'options'=> $departments,
	            'class' =>'chzn-select wide',
	            'refresh_link'=>array(
					'option'=>'_sod_qbpos_departments',
					'title' =>'Refresh Departments from QBPOS'
				)
	        ),
		    	array(
		            'name' => 'Map Your WooCommerce Categories to Your QuickBooks POS Departments',
		            'desc' => 'Choose whether to automatically create individual customer accounts in QuickBooks. If you choose no, you\'ll need to specify an default account to post to.',
		            'id' => 'department_mappings',
		            'type' => 'mapping',
		            'options'=>array(
		            	'labels'=>array(
		            		'heading'=>'Category Name',
		            		'data'=>$cats
		            		),
		            	'selects'=>array(
		            		'heading'=>'Quickbooks POS Departments',
		            		'class' =>'chzn-select medium',
		            		'data'=>$departments
			            )
		           ),
		           'refresh_link'=>array(
						'option'=>'_sod_qbpos_departments',
						'title' =>'Refresh Depts'
					)
				)
			)
		);
		$meta_box = apply_filters('sod_qbpos_department_fields', $meta_box);
	   	sod_form_builder($meta_box);
	//}
}
/*
 * Salestax Settings
 */
function sod_qbpos_salestax_settings(){
	global $quickbooks, $woocommerce;
	
	$taxes = $quickbooks->get_sales_tax();
	$w_taxes_codes = $quickbooks->get_cart_tax_classes();
	
	
	$meta_box = array(
		    'id' => 'sod_qbpos_products',
		    'title' => __('Quickbooks Options', 'woocommerce'),
		    'page' => 'product',
		    'context' => 'normal',
		    'priority' => 'default',
		    'section'=>'settings',
		    'meta_key'=> 'sod_qbpos_defaults',
		    'fields' => array(
		    	array(
		            'name' => 'Map Your WooCommerce Sales Tax Codes to Your QuickBooks Sales Tax Codes',
		            'desc' => 'Choose whether to automatically create individual customer accounts in QuickBooks. If you choose no, you\'ll need to specify an default account to post to.',
		            'id' => 'taxcodes_mappings',
		            'type' => 'mapping',
		            'options'=>array(
		            	'labels'=>array(
		            		'heading'=>'Website Sales Tax Codes',
		            		'data'=>$w_taxes_codes
		            		),
		            	'selects'=>array(
		            		'heading'=>'Quickbooks Sales Tax Codes',
		            		'class' =>'chzn-select medium',
		            		'data'=>$taxes['codes']
			            )
			           ),
					 'refresh_link'=>array(
						'option'=>'_sod_qbpos_salestax_records',
						'title' =>'Refresh Tax Codes'
					
		           )
				),
				
			)
		);
		$meta_box = apply_filters('sod_qbpos_salestax_fields', $meta_box);
	   	sod_form_builder($meta_box);
}
/*
 * Order Status Mappings
 */



/*
 * Inventory Options
 */
function sod_qbpos_inventory_settings(){
	global $quickbooks;
	$accounts = $quickbooks->get_accounts("Income");
	$cogs = $quickbooks->get_accounts("COGS");
	$identifiers = array(
		//'ID'=>'Product ID',
		'_sku'=>'SKU'
		
	);
	//$identifiers = apply_filters('sod_qbpos_product_identifiers', $identifiers);
	$qbpos_identifiers = array(
		'UPC'=>'UPC',
		'ALU'=>'ALU',
		'ItemNumber'=>'Item Number',
	); 
	$qbpos_identifiers = apply_filters('sod_qbpos_identifiers', $qbpos_identifiers);
	$cases = array(
		'_manage_stock'=>'Inventory Items',
		'_no_manage_stock'=>'Non-Inventory Items',
		'downloadable_products'=>'Downloadable Goods',
		'virtual_product'=>'Virtual Products'
	);
	$cases = apply_filters('sod_qbpos_inventory_cases', $cases);
	$item_types = array(
		'non_inventory'=>'Non-Inventory',
		'inventory'=>'Inventory',
		'service'=>'Service'
	);
	$item_types = apply_filters('sod_qbpos_inventory_types', $item_types);
	$fields = array(
		    'id' => 'sod_qbpos_products',
		    'title' => __('Quickbooks Options', 'woocommerce'),
		    'page' => 'product',
		    'context' => 'normal',
		    'section' => 'inventory_settings',
		    'meta_key'=> 'sod_qbpos_inv_defaults',
		    'fields' => array(
		    	array(
	            	'name' => 'Sync Inventory On-Hand from QuickBooks',
	            	'desc' => 'Choose whether to automatically create individual customer accounts in QuickBooks. If you choose no, you\'ll need to specify an default account to post to.',
	            	'id' => 'sync_inv',
	            	'type' => 'checkbox',
	            	'class' =>'switch'
	           ),
	           array(
	            	'name' => 'Sync Price from QuickBooks',
	            	'desc' => 'Choose whether to automatically create individual customer accounts in QuickBooks. If you choose no, you\'ll need to specify an default account to post to.',
	            	'id' => 'sync_price',
	            	'type' => 'checkbox',
	            	'class' =>'switch'
	           ),
	           
	         array(
	            'name' => 'QBPOS field to use during syncing',
	            'desc' => 'Please select the field in QBPOS that you want to use to sync products',
	            'id' => 'qbpos_identifier',
	            'type' => 'select',
	            'options'=> $qbpos_identifiers,
	            'class' =>'chzn-select wide',
	            
	        ),
	         array(
	            'name' => 'WooCommerce field to use during syncing',
	            'desc' => 'Please select the product field in WooCommerce that you want to use to sync products',
	            'id' => 'product_identifier',
	            'type' => 'select',
	            'options'=> $identifiers,
	            'class' =>'chzn-select wide',
	            
	        ),
	        
	        array(
	            'name' => 'How Often Should the Inventory Sync run?',
	            'desc' => 'If not creating individual accounts, please select the default QuickBooks account to post orders to.',
	            'id' => 'inv_sync_frequency',
	            'type' => 'text',
	            'std'=>'5',
	            'suffix'=>'minutes',
	            //'options'=> $identifiers
	            'class' =>'wide',
	       	),  
	       	array(
	            'name' => 'Change WooCommerce stock status when QBPOS quantity goes above or below zero?',
	            'desc' => 'If not creating individual accounts, please select the default QuickBooks account to post orders to.',
	            'id' => 'change_stock_status',
	            'type' => 'checkbox',
	        ), 
	       	array(
	            'name' => 'Auto-create <strong>website</strong> products in QBPOS if they can\'t be found?',
	            'desc' => 'If not creating individual accounts, please select the default QuickBooks account to post orders to.',
	            'id' => 'auto_create',
	            'type' => 'checkbox',
	        ), 
	        array(
	            'name' => 'Default QBPOS Item Type for auto-created products?',
	            'desc' => 'If not creating individual accounts, please select the default QuickBooks account to post orders to.',
	            'id' => 'default_itemtype',
	             'type' => 'select',
	            'options'=> $item_types,
	            'class' =>'chzn-select wide',
	        ),  
			)
		);
		
		$fields = apply_filters('sod_quickbookspos_inventory_settings', $fields);
	   	sod_form_builder($fields);
	}

function sod_qbpos_qwc_settings(){
	global $quickbooks;
	$options 		= get_option('sod_qbpos_webconnector');
	$key 			= get_option('_qbpos_connector_key');
	$dir 			= plugin_dir_path( __FILE__ );
	$webconnector 	= new SOD_QuickBooksPOS_Data;
	$qbpos_versions = array(
		'11'=>'Version 11 - 2013',
		
	); 
	$qbpos_versions = apply_filters('sod_qbpos_supported_versions', $qbpos_versions);
	?>

	<form method="post" action="<?php echo plugins_url('qwc.php', __FILE__);?>">
		<table id="qwc">
						<tr>
							<td class="label">
								<label for="sod_qbpos_webconnector[username]">Webconnector Username?</label>
							</td>
							<td>
								<input name="sod_qbpos_webconnector[username]" id="sod_qbpos_webconnector[username]" value="<?php echo $options['username'];?>" type="input"/> 
							</td>
						</tr>
							<tr>
							<td class="label">
								<label for="sod_qbpos_webconnector[password]">Webconnector Password?</label>
							</td>
							<td>
								<input name="sod_qbpos_webconnector[password]" id="sod_qbpos_webconnector[password]" value="<?php echo $webconnector->decode($options['password']);?>" type="password"/> 
							</td>
						</tr>
						<tr>
							<td class="label">
								<label for="sod_qbpos_webconnector[ssl]">SSL Path</label>
							</td>
							<td>
								<input name="sod_qbpos_webconnector[ssl]" id="sod_qbpos_webconnector[ssl]" value="<?php echo $options['ssl'];?>" type="input"/> 
							</td>
						</tr>
						<tr>
							<td class="label">
								<label for="sod_qbpos_webconnector[computer_name]">Computer Name</label>
							</td>
							<td>
								<input name="sod_qbpos_webconnector[computer_name]" id="sod_qbpos_webconnector[computer_name]" value="<?php echo $options['computer_name'];?>" type="input"/> 
							</td>
						</tr>
						<tr>
							<td class="label">
								<label for="sod_qbpos_webconnector[company_data]">QBPOS Company Data</label>
							</td>
							<td>
								<input name="sod_qbpos_webconnector[company_data]" id="sod_qbpos_webconnector[company_data]" value="<?php echo $options['company_data'];?>" type="input"/> 
							</td>
						</tr>
						<tr>
							<td class="label">
								<label for="sod_qbpos_webconnector[version]">QBPOS Version</label>
							</td>
							<td>
								<select name="sod_qbpos_webconnector[version]" id="sod_qbpos_webconnector[version]">
								<?php foreach($qbpos_versions as $version_number=>$version_label){
									$checked = $options['version']== $version_number ? $checked='selected="selected"': $checked='';
									echo '<option '. $checked .' value="'.$version_number.'">'.$version_label.'</option>';
								};?>																
								</select>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<input type="hidden" name="sod_qbpos_webconnector[key]" value="<?php echo $key;?>"/>
								<input type="hidden" name="sod_qbpos_webconnector[plugin_dir]" value="<?php echo $dir;?>"/>
							</td>
						</tr>
					</table>
			<input type="submit" class="button-primary" id="create_qwc" value="<?php _e('Create QWC File') ?>" />
	</form>
<?php 
}