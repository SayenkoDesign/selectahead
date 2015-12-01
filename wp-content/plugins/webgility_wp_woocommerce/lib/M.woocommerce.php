<?php ini_set("display_errors","On");
/*
===================================
© Copyright Webgility LLC 2007-2011
----------------------------------------
This file and the source code contained herein are the property of Webgility LLC
and are protected by United States copyright law. All usage is restricted as per 
the terms & conditions of Webgility License Agreement. You may not alter or remove 
any trademark, copyright or other notice from copies of the content.

The code contained herein may not be reproduced, copied, modified or redistributed in any form
without the express written consent by an officer of Webgility LLC.

File last updated		: 	04/01/2012

*/

# Added for removing memory exhausted  problem of service file
require_once('M.WgCommon.php'); 



if(((int)str_replace("M","",ini_get("memory_limit")))<128)
    ini_set("memory_limit","128M");
$path=dirname(dirname(dirname(__FILE__)));
define( 'WPSC_FILE_PATH', $path.'/wp-e-commerce' );
if ( defined('ABSPATH') )
{  
	require_once(ABSPATH . 'wp-load.php');
	require_once(ABSPATH . 'wp-settings');
	if(file_exists($path.'/wp-e-commerce/wpsc-admin/includes/image.php'))
	require_once($path.'/wp-e-commerce/wpsc-admin/includes/image.php');
}	
else
{	

	require_once('../../../wp-load.php');

	require_once('../../../wp-settings.php');

		if(file_exists('../../../wp-admin/includes/image.php'))
	require_once('../../../wp-admin/includes/image.php');
		
	if(file_exists($path.'/wp-e-commerce/wpsc-core/wpsc-functions.php'))
	{
		require_once($path.'/wp-e-commerce/wpsc-core/wpsc-functions.php');
	}
	if(file_exists($path.'/wp-e-commerce/wpsc-theme/functions/wpsc-transaction_results_functions.php'))
	{
		require_once($path.'/wp-e-commerce/wpsc-theme/functions/wpsc-transaction_results_functions.php');
	}
	
	require_once($path.'/wp-e-commerce/wpsc-admin/admin.php');

	require_once($path.'/wp-e-commerce/wpsc-includes/purchaselogs.class.php');	
	require_once($path.'/wp-e-commerce/wpsc-includes/misc.functions.php');	
	require_once($path.'/wp-e-commerce/wpsc-admin/ajax-and-init.php');
}

$ecc_base_path		=	ABSPATH.'wp-content/plugins/wp-e-commerce/webgility';#required for apns



class Webgility_Ecc_WP extends WgCommon
{	

	
	 public function auth_user($username,$password)
	{
		global $sql_tbl;
		add_filter('authenticate', 'wp_authenticate_username_password', 20, 3);
		$user = wp_authenticate_username_password('WP_User', $username, $password);	
		$WgBaseResponse = new WgBaseResponse();		
		try
		{
		   // return true;
			if (isset($user->errors[invalid_username]))
			{
				if(is_array($user->errors[invalid_username]))
				{
					$WgBaseResponse->setStatusCode('1');
					$WgBaseResponse->setStatusMessage('Invalid login. Authorization failed');
					return $this->response($WgBaseResponse->getBaseResponse());		   
					exit;
			   }
			}
		   	elseif (isset($user->errors['incorrect_password']))
			{
				if(is_array($user->errors['incorrect_password']))
				{
					$WgBaseResponse->setStatusCode('2');
					$WgBaseResponse->setStatusMessage('Invalid password. Authorization failed');
					return $this->response($WgBaseResponse->getBaseResponse());		   
					exit;
				}
				
			}
			else
			{
				return 0;
			}   	
			   
		
		}catch (Exception $e)
		{
			$WgBaseResponse->setStatusCode('1');
			$WgBaseResponse->setStatusMessage('Invalid login. Authorization failed');
			return $this->response($WgBaseResponse->getBaseresponse());		   
			exit;
		}
	}
	
	
	# Returns the version of the shopping cart to check the compatibility

	function getVersion()
	{
		# Earlier way fo fectching version
		/*
			$allplugin = get_option( '_transient_update_plugins' );
			$version = $allplugin->checked['wp-e-commerce/wp-shopping-cart.php'];
		*/
		$version = WPSC_PRESENTABLE_VERSION;
		if($version)
		{
			return $version;
		}
		else
		{
			return "0";
		}
	}
	#
	# Returns the Company Info of the Store
	#
	function getStores($username, $password, $devicetokenid)
	{
		global $wpdb;
		
		#check for authorisation
		$status=$this->auth_user($username,$password);
		if($status!='0') {return $status;}
		
		#required for apns
		#@mail('ziyar@webgility.com','Device token id from getStores',$devicetokenid);
		##############		Code to create and modify apns-config.txt	###################
		$devicetokenid = str_replace(" ","", $devicetokenid);
		$devicetokenid = substr($devicetokenid,1,strlen($devicetokenid)-2);
		
		$device_token_array	=	array();
		$alert_flag_array	=	array();
		global $ecc_base_path;
		#echo substr(decoct(fileperms($ecc_base_path)),2);die('reached');
		$apns_config_file	=	$ecc_base_path.'/apns-config.txt';
		$config_str			=	'';
		$config_array		=	array();
		$config_array_count	=	0;
		$is_device_not_exist	=	true;
		if(file_exists($apns_config_file)) {
		
			//Code to read DeviceToken
			$file = fopen($apns_config_file, 'r') or exit('Unable to open file for read!');
			//Output a line of the file until the end is reached
			while(!feof($file)) {
			  
			  $line	=	fgets($file);
			  $line_array	=	explode('::', $line); //print_r($line_array);
			  $config_str	=	$line_array[0];
			  $config_str	=	json_decode($config_str);
			  #print_r($config_str);die('reached');
			  
			  foreach($config_str as $k=>$v) {
				$config_array[$k] = $this->object_2_array($v);
				#print_r($config_array_new);
				foreach($config_array['devices'] as $k1 => $v1) {
					#print_r($v1);
					if($devicetokenid == $v1['id']) {
						#$config_array[$k][] = $v1;
						$is_device_not_exist	=	false;
						$config_array['devices'][$config_array_count]['newOrder']	=	'';
					}
					$config_array_count++;
				}
			  }
			}
			fclose($file);
		}
		
		if($is_device_not_exist) {
			$config_array_count	=	0;
			$config_array['devices'][$config_array_count] = array('id' => $devicetokenid, 'newOrder' =>"0", 'newUser' =>"0");
			#print_r($config_array['devices']);die();
		}
		#echo $apns_config_file;
		$fh = fopen($apns_config_file, 'w') or die("can't open file");

		$stringData = json_encode($config_array);
		//die($stringData);
		fwrite($fh, $stringData);
		fclose($fh);
		
		chmod($apns_config_file, 0777);
		########################################################################
		
		
		$CompanyInfo = new WG_CompanyInfo();	
			
		$region_list = $wpdb->get_results("SELECT `".WPSC_TABLE_REGION_TAX."`.* FROM `".WPSC_TABLE_REGION_TAX."`, `".WPSC_TABLE_CURRENCY_LIST."`  WHERE `".WPSC_TABLE_CURRENCY_LIST."`.`isocode` IN('".get_option('base_country')."') AND `".WPSC_TABLE_CURRENCY_LIST."`.`id` = `".WPSC_TABLE_REGION_TAX."`.`country_id` AND ".WPSC_TABLE_REGION_TAX.".id='".get_option('base_region')."'",ARRAY_A) ;
		
		if($region_list[0]['name'])
		{
			$region = $region_list[0]['name'];
		}
		
		if($region == "")
		{
			$region_data = $wpdb->get_results("SELECT  option_value  FROM ".wp_options." WHERE option_name = 'wpec_taxes_rates' ") ;
			//print_r($region_data);
			$region_detail = unserialize($region_data[0]->option_value);
			$country_code = $region_detail[0]['country_code'];
			$region_code = $region_detail[0]['region_code'];
			
			$region_arr = $wpdb->get_results("SELECT name FROM ".WPSC_TABLE_REGION_TAX." WHERE code = '".$region_code."' ",ARRAY_A) ;
			$region = $region_arr[0]['name'];
		}
		
		$Stores = new WG_StoresInfo();		
		
		$Stores->setStatusCode('0');
		$Stores->setStatusMessage('All Ok');	
		
		$Store = new WG_Store();
		$Store->setStoreID('1');
		$Store->setStoreName(esc_attr(get_option('blogname')));
		$Store->setStoreWebsiteId('1');
		$Store->setStoreWebsiteName('Wordpress ecommerce store website');
		$Store->setStoreRootCategoryId('1');
		$Store->setStoreDefaultStoreId('1');
		$Store->setStoreType('wordpress');									
		$Stores->setstores($Store->getStore());										
		
		return $this->response($Stores->getStoresInfo());	
		
			
	} // GetCompanyInfo
	
	
	#apns alert code start from here #required for apns
	function object_2_array($data) 
	{
		if(is_array($data) || is_object($data))
		{
			$result = array(); 
			foreach ($data as $key => $value)
			{ 
				$result[$key] = $this->object_2_array($value); 
			}
			return $result;
		}
		return $data;
	}
	
	
	
	function runApns($username,$password,$storeId=1) {
		
		global $wpdb, $ecc_base_path;
		
		#check for authorisation
		$responseArray = array();
		$status=$this->auth_user($username,$password);
		
		$WgBaseResponse = new WgBaseResponse();	
		if($status!="0"){ //login name invalid
		
			if($status=="1"){
				$WgBaseResponse->setStatusCode('1');
				$WgBaseResponse->setStatusMessage('Invalid login. Authorization failed');
				return $this->response($WgBaseResponse->getBaseresponse());
			} elseif($status=="2"){ //password invalid
				$WgBaseResponse->setStatusCode('1');
				$WgBaseResponse->setStatusMessage('Invalid login. Authorization failed');
				return $this->response($WgBaseResponse->getBaseresponse());
			}
			
		} else {
		
			$WgBaseResponse->setStatusCode('0');
			$WgBaseResponse->setStatusMessage('All Ok');
			
			$version = $this->getVersion();
			#modifyApnsConfigFile('100000026', 'get_order', '51370ecb8c3f78a3ee14fa77617deb45ea2584841b9b6c99dec35bc0d8fb7402');die();
			
			$device_token_array	=	array();
			$alert_flag_array	=	array();
			$apns_config_file	=	$ecc_base_path.'/apns-config.txt';
			
			$config_str			=	'';
			$config_array		=	array();
			$config_array_count	=	0;
			if(file_exists($apns_config_file)) {
			
				//Code to read DeviceToken
				$file = fopen($apns_config_file, 'r') or exit('Unable to open file for read!');
				
				//Output a line of the file until the end is reached
				while(!feof($file)) {
				  
				  $line	=	fgets($file);
				  $line_array	=	explode('::', $line); //print_r($line_array);
				  $config_str	=	$line_array[0];
				  $config_str	=	json_decode($config_str);
				  #print_r($config_str);die('reached');
				  foreach($config_str as $k=>$v) {
					$config_array[$k] = $this->object_2_array($v);
					$config_array_count++;
				  }
				}
				fclose($file);
			}
			
			#print_r($config_array['devices']);die('reached');
			
			foreach($config_array['devices'] as $device_array) {
			
				if(isset($device_array['newOrder'])) {
					$last_order_id_in_app	=	isset($device_array['newOrder']) ? $device_array['newOrder'] : 0;
					//Code to check new order
					$orders = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `id`>".$last_order_id_in_app,ARRAY_A);
					$count = count($orders);
					$store_name	=	esc_attr(get_option('blogname'));
					
					if($count > 0) {
						//echo $device_token;
						if($count > 1) {
							$message	=	$count.' new orders received on '.$store_name.'.';
						} else {
							$message	=	$count.' new order received on '.$store_name.'.';
						}
						$this->sendApnsAlert($device_array['id'], $message);
						#sendApnsAlert('51370ecb8c3f78a3ee14fa77617deb45ea2584841b9b6c99dec35bc0d8fb7402', $message);die();
						
					}
					
				}
			
			}
			
		}
		
	}
	
	function sendApnsAlert($device_token, $message) {
		
		#!/usr/bin/env php
		 //$device_token = '1f52588dad33b4a62a027ff490121dc9dbbeb51799b8804d3dea7eeba0412951'; // masked for security reason
		  // Passphrase for the private key (ck.pem file)
		  // $pass = '';
		
		  // Get the parameters from http get or from command line
		  //$message = $_GET['message'] or $message = $argv[1] or $message = 'Message received from javacom';
//		  $badge = (int)$_GET['badge'] or $badge = (int)$argv[2];
//		  $sound = $_GET['sound'] or $sound = $argv[3];
		  
		  global $ecc_base_path;
		  
		  $message = isset($message) ? $message : 'Message received from webgility mobile apns test';
		  $badge = (int)1;
		  $sound = 'received5.caf';
		  
		  
		
		  // Construct the notification payload
		  $body = array();
		  $body['aps'] = array('alert' => $message);
		  if ($badge)
		  $body['aps']['badge'] = $badge;
		  if ($sound)
		  $body['aps']['sound'] = $sound;
		
		
		  // End of Configurable Items 
		  $ctx = stream_context_create();
		  stream_context_set_option($ctx, 'ssl', 'local_cert', $ecc_base_path.'/apns.pem');
		  //stream_context_set_option($ctx, 'ssl', 'local_cert', 'apns.pem');
		  // assume the private key passphase was removed.
		  // stream_context_set_option($ctx, 'ssl', 'passphrase', $pass);
		
		  #$fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT, $ctx);
		  $fp = stream_socket_client('ssl://gateway.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT, $ctx);
		  // for production change the server to ssl://gateway.push.apple.com:2195
		  if (!$fp) {
			  print "Failed to connect $err $errstr\n";
			  return;
		  } else {
		  		print "Connection OK\n";
		  }
		
		  $payload = json_encode($body);
		  $msg = chr(0) . pack("n",32) . pack('H*', str_replace(' ', '', $device_token)) . pack("n",strlen($payload)) . $payload;
		  #print "Device Token: ".$device_token.", sending message :" . $payload . "\n";
		  echo 'Push notifications enabled successfully. You are ready to send notifications.'; 
		  fwrite($fp, $msg);
		  fclose($fp);
		
	}	
	
	
	function modifyApnsConfigFile($action_entity_id, $action_type, $devicetokenid) {
		
		global $ecc_base_path;
		
		##############		Code to create and modify apns-config.txt	###################
		$devicetokenid = str_replace(" ","", $devicetokenid);
		$devicetokenid = substr($devicetokenid,1,strlen($devicetokenid)-2);
		
		$device_token_array	=	array();
		$alert_flag_array	=	array();
		$apns_config_file	=	$ecc_base_path.'/apns-config.txt';
		#$apns_config_file	=	$_SERVER['DOCUMENT_ROOT'].'/magento/ecc/apns-config.txt';
		$config_str			=	'';
		$config_array		=	array();
		$config_array_count	=	0;
		if(file_exists($apns_config_file)) {
		
			//Code to read DeviceToken
			$file = fopen($apns_config_file, 'r') or exit('Unable to open file for read!');
			
			//Output a line of the file until the end is reached
			while(!feof($file)) {
			  
			  $line	=	fgets($file);
			  $line_array	=	explode('::', $line); //print_r($line_array);
			  $config_str	=	$line_array[0];
			  $config_str	=	json_decode($config_str);
			  
			  foreach($config_str as $k=>$v) {
				$config_array[$k] = $this->object_2_array($v);
				#print_r($config_array_new);
				foreach($config_array['devices'] as $k1 => $v1) {
					#print_r($v1);
					if($devicetokenid == $v1['id']) {
						#$config_array[$k][] = $v1;
						$config_array['devices'][$config_array_count]['newOrder']	=	$action_entity_id;
					}
					$config_array_count++;
				}
			  }
			  
			}
			fclose($file);
			
			
		}

		$fh = fopen($apns_config_file, 'w') or die("can't open file");

		$stringData = json_encode($config_array);
		//die($stringData);
		fwrite($fh, $stringData);
		fclose($fh);
		chmod($apns_config_file, 0777);
		########################################################################
	}
	
	
	function deleteStore($username,$password,$devicetokenid) {
		#@mail('ziyar@webgility.com','Device token id from deleteStore',$devicetokenid);
		global $wpdb, $ecc_base_path;
		
		#check for authorisation
		$responseArray = array();
		$status=$this->auth_user($username,$password);
		
		$WgBaseResponse = new WgBaseResponse();	
		if($status!="0"){ //login name invalid
		
			if($status=="1"){
				$WgBaseResponse->setStatusCode('1');
				$WgBaseResponse->setStatusMessage('Invalid login. Authorization failed');
				return $this->response($WgBaseResponse->getBaseresponse());
			} elseif($status=="2"){ //password invalid
				$WgBaseResponse->setStatusCode('1');
				$WgBaseResponse->setStatusMessage('Invalid login. Authorization failed');
				return $this->response($WgBaseResponse->getBaseresponse());
			}
			
		} else {
		
			$WgBaseResponse->setStatusCode('0');
			$WgBaseResponse->setStatusMessage('All Ok');
			
			##############		Code to create and modify apns-config.txt	###################
			$devicetokenid = str_replace(" ","", $devicetokenid);
			$devicetokenid = substr($devicetokenid,1,strlen($devicetokenid)-2);
			
			$device_token_array	=	array();
			$alert_flag_array	=	array();
			$apns_config_file	=	$ecc_base_path.'/apns-config.txt';
			#$apns_config_file	=	$_SERVER['DOCUMENT_ROOT'].'/magento/ecc/apns-config.txt';
			$config_str			=	'';
			$config_array		=	array();
			$config_array_new	=	array();
			$config_array_count	=	0;
			if(file_exists($apns_config_file)) {
			
				//Code to read DeviceToken
				$file = fopen($apns_config_file, 'r') or exit('Unable to open file for read!');
				
				//Output a line of the file until the end is reached
				while(!feof($file)) {
				  
				  $line	=	fgets($file);
				  $line_array	=	explode('::', $line); //print_r($line_array);
				  $config_str	=	$line_array[0];
				  $config_str	=	json_decode($config_str);
				  #print_r($config_str);echo '<br/>';
				  foreach($config_str as $k=>$v) {
					$config_array_new[$k] = $this->object_2_array($v);
					#print_r($config_array_new);
					foreach($config_array_new['devices'] as $k1 => $v1) {
						#print_r($v1);
						if($devicetokenid != $v1['id']) {
							$config_array[$k][] = $v1;
						}
						$config_array_count++;
					}
				  }
				}
				fclose($file);
			}
			#print_r($config_array);die('reached');
			if(count($config_array) > 0) {
	
				$fh = fopen($apns_config_file, 'w') or die("can't open file");
		
				$stringData = json_encode($config_array);
				//die($stringData);
				fwrite($fh, $stringData);
				fclose($fh);
				chmod($apns_config_file, 0777);
			}else {@unlink($apns_config_file);}
			########################################################################
		}
		return $this->response($WgBaseResponse->getBaseresponse());
	}
	
	#apns alert code end here
	
	
	function getCustomers($username,$password,$datefrom,$customerid=0,$limit,$storeid=1,$others){
		
		global $sql_tbl;
		
		#check for authorisation
		$status=$this->auth_user($username,$password);
		if($status!='0')
		{
			return $status;
		}
		
		//if($customerid > 0) {
				
			//$user_array = get_users(array('ID'=>2));	
		
		//} else {
		
			$user_array = get_users();	
		//}
		
		//print_r($user_array);
		
		$Customers = new WG_Customers();
		$Customers->setStatusCode('0');		 
		$Customers->setStatusMessage('All Ok');
		
		$count_customer = 0;
		
		foreach($user_array as $user) {
			
			$Customer = new Wg_Customer();
			$Customer->setCustomerId($user->ID);
			
			$first_name	=	get_user_meta($user->ID, 'first_name', true);
			$Customer->setFirstName(!empty($first_name) ? $first_name : $user->user_login);
			
			$Customer->setMiddleName('');
			
			$last_name	=	get_user_meta($user->ID, 'last_name', true);
			$Customer->setLastName(!empty($last_name) ? $last_name : $user->user_login);
			
			$Customer->setCustomerGroup('1');
			$Customer->setemail($user->user_email);
			$Customer->setAddress1('');
			$Customer->setAddress2('');
			$Customer->setCity('');
			$Customer->setState('');
			$Customer->setZip('');
			$Customer->setCountry('');
			$Customer->setPhone('');
			$Customer->setCreatedAt(date("Y-m-d H:i:s", time()));
			$Customer->setUpdatedAt(date("Y-m-d H:i:s", time()));
			$Customer->setLifeTimeSale("");
			$Customer->setAverageSale("");
			
			//print_r($Customer->getCustomer());
			
			
			$Customers->setCustomer($Customer->getCustomer());
			$count_customer++;
			
		}
		
		$Customers->setTotalRecordFound((int)$count_customer);	
		$Customers->setTotalRecordSent((int)$count_customer);	

		return $this->response($Customers->getCustomers());
		
	}
	
	function getStoreCustomerByIdForEcc($username,$password,$datefrom,$customerid,$limit,$storeid=1,$others) {
		
		global $sql_tbl;
		
		#check for authorisation
		$status=$this->auth_user($username,$password);
		if($status!='0')
		{
			return $status;
		}
				
		$user_array = get_users();
		
		//print_r($user_array);
		
		$Customers = new WG_Customers();
		$Customers->setStatusCode('0');		 
		$Customers->setStatusMessage('All Ok');
		
		$count_customer = 0;
		
		foreach($user_array as $user) {
			
			if($customerid == $user->ID) {
			
				$Customer = new WG_Customer();
				$Customer->setCustomerId($user->ID);
				
				$first_name	=	get_user_meta($user->ID, 'first_name', true);
				$Customer->setFirstName(!empty($first_name) ? $first_name : $user->user_login);
				
				$Customer->setMiddleName('');
				
				$last_name	=	get_user_meta($user->ID, 'last_name', true);
				$Customer->setLastName(!empty($last_name) ? $last_name : $user->user_login);
				
				$Customer->setCustomerGroup('1');
				$Customer->setemail($user->user_email);
				$Customer->setAddress1('');
				$Customer->setAddress2('');
				$Customer->setCity('');
				$Customer->setState('');
				$Customer->setZip('');
				$Customer->setCountry('');
				$Customer->setPhone('');
				$Customer->setCreatedAt(date("Y-m-d H:i:s", time()));
				$Customer->setUpdatedAt(date("Y-m-d H:i:s", time()));
				$Customer->setLifeTimeSale("");
				$Customer->setAverageSale("");
				
				//print_r($Customer->getCustomer());
				
				
				$Customers->setCustomer($Customer->getCustomer());
				$count_customer++;
			}
			
		}
		
		$Customers->setTotalRecordFound((int)$count_customer);	
		$Customers->setTotalRecordSent((int)$count_customer);	

		return $this->response($Customers->getCustomers());
		
	}
	
	
	# retrive all order status
	function getOrderStatus($username,$password)
	{
		global $wpdb;
		$status=$this->auth_user($username,$password);
		if($status!='0')
		{
			return $status;
		}
		
		$version = $this->getVersion();
		$logs = new wpsc_purchaselogs();
		$orderStatus = $logs->the_purch_item_statuses();	
		
		//$pMethodNodes = $xmlResponse->createTag("OrderStatus", array(), '', $root);
		//$xmlResponse->createTag("StatusCode", array(), "0", $pMethodNodes, __ENCODE_RESPONSE);
		//$xmlResponse->createTag("StatusMessage", array(), "All Ok", $pMethodNodes, __ENCODE_RESPONSE);
		$OrderStatuses = new WG_OrderStatuses();
		$OrderStatuses->setStatusCode('0');
		$OrderStatuses->setStatusMessage('All Ok');	
		
		foreach($orderStatus as $statusdata)
		{	
				
			//$pMethodNode = $xmlResponse->createTag("Status",    array(), '', $pMethodNodes);
			//$xmlResponse->createTag("StatusId", array(), ($version>'3.7.8')?$statusdata['order']:$statusdata->id, $pMethodNode, __ENCODE_RESPONSE);
			//$xmlResponse->createTag("StatusName", array(), ($version>'3.7.8')?$statusdata['label']:$statusdata->name, $pMethodNode, __ENCODE_RESPONSE);
			
			$OrderStatus =new WG_OrderStatus();
			$OrderStatus->setOrderStatusID(($version>'3.7.8')?$statusdata['order']:$statusdata->id);
			$OrderStatus->setOrderStatusName(($version>'3.7.8')?$statusdata['label']:$statusdata->name);
			$OrderStatuses->setOrderStatuses($OrderStatus->getOrderStatus());
		}	
	
		return $this->response($OrderStatuses->getOrderStatuses());			
	
	} //getOrderStatus
	
	function addItemImage($username,$password,$itemid,$image,$storeid=1) {

		global $wpdb; 	
		global $totaltax;
		global $totaldiscount;	
		
		$uploads	=	wp_upload_dir();
		//print_r($uploads);die();
		
		define('DIR_IMAGE_FOR_UPLOAD', $uploads['path'].'/');
		//echo DIR_IMAGE_FOR_UPLOAD;
		chmod(DIR_IMAGE_FOR_UPLOAD, 0777);
		$url = 'http://'.$_SERVER['HTTP_HOST'].'/'.substr($_SERVER['SCRIPT_NAME'], 1, strpos(substr($_SERVER['SCRIPT_NAME'],1), '/')).'/?wpsc-product=';
		
		//echo $itemid;die();
		
		#check for authorisation
		/*$status = $this->auth_user($username,$password);
		if($status!==0)
		{
		  return $status;
		}*/
		$Items = new WG_Items();
		$version = $this->getVersion();
		
		$timestamp_valu	=	time();
		
		$image_name = $timestamp_valu.'.jpg';
		
		//Base 64 encoded string $image
		$str	=	base64_decode($image);
		if(substr(decoct(fileperms(DIR_IMAGE_FOR_UPLOAD)),2) == '777') {
		
			$fp = fopen(DIR_IMAGE_FOR_UPLOAD.$image_name, 'w+');
		
			fwrite($fp, $str);
			fclose($fp);
			
			#create the item's image
			// Construct the attachment array
			$attachment = array(
				'post_mime_type' => 'image/jpeg',
				'guid' => $url.$timestamp_valu,
				'post_parent' => $itemid,
				'post_title' => $timestamp_valu,
				'post_content' => 'Product image post !!!',
				'post_type' => "attachment",
				'post_status' => 'inherit'		
			);
		
			// Save the data
			$postID = wp_insert_post($attachment, $file, $product_id);
			
			$time = current_time( 'mysql' );
			$y = substr( $time, 0, 4 );
			$m = substr( $time, 5, 2 );
			$subdir = "/$y/$m";
			
			add_post_meta($postID,'_wp_attached_file',$subdir.'/'.$image_name);	
			
			$Items->setStatusCode('0');
			$Items->setStatusMessage('All Ok');
			$Items->setItemImageFlag('1');
			
			$Item = new WG_Item();
			$image_node_array = array();
			
			
			
			$ImagePath	=	'http://'.$_SERVER['HTTP_HOST'].'/'.substr($_SERVER['SCRIPT_NAME'], 1, strpos(substr($_SERVER['SCRIPT_NAME'],1), '/')).'/wp-content/uploads'.$subdir.'/'.$image_name;
			
			$image_node_array['ItemImages']=array('ItemID'=>$itemid, 'ItemImageID'=>$itemid, 'ItemImageFileName'=>$image_name, 'ItemImageUrl'=>$ImagePath);
			
			$Item->setItemImages($image_node_array['ItemImages']);
			$Items->setItems($Item->getItem()); 
			
		} else {
		
			$Items->setStatusCode('1');
			$Items->setStatusMessage('Images directory is not writeable.');
			$Items->setItemImageFlag('0');
		
		}
		
		return $this->response($Items->getItems());
	}

	/*function current_time( $type, $gmt = 0 ) {
		switch ( $type ) {
			case 'mysql':
				return ( $gmt ) ? gmdate( 'Y-m-d H:i:s' ) : gmdate( 'Y-m-d H:i:s', ( time() + ( get_option( 'gmt_offset' ) * 3600 ) ) );
				break;
			case 'timestamp':
				return ( $gmt ) ? time() : time() + ( get_option( 'gmt_offset' ) * 3600 );
				break;
		}
	}*/

	#
	# function to return the store item list so synch with QB inventory
	#
	function getItems($username,$password,$datefrom,$start,$limit,$storeid)
	{
		global $wpdb,$wp_query,$table_prefix;
		
		
		/*$postion_char		=	strpos(substr($_SERVER['PHP_SELF'],1), '/');
		$store_site_path	=	 'http://'.$_SERVER['HTTP_HOST'].'/'.substr($_SERVER['PHP_SELF'], 1, $postion_char);
		$product_image_path	=	$store_site_path.'/components/com_virtuemart/shop_image/product/';*/
		
		//parse_url();
		
		
		#check for authorisation
		$status = $this->auth_user($username,$password);
		if($status!==0)
		{
		  return $status;
		}
		$Items = new WG_Items();
		$version = $this->getVersion();
		
		
		if($version > '3.7.8')
		{
			$sql = "SELECT SQL_CALC_FOUND_ROWS  posts.* FROM ".$table_prefix."posts as posts  WHERE posts.post_type = 'wpsc-product' AND (posts.post_status = 'publish' OR post_status = 'future' OR posts.post_status = 'draft' OR posts.post_status = 'pending' OR posts.post_status = 'private') AND posts.id > $start ORDER BY posts.id ASC limit 0,$limit";
			
			$sql_total = "SELECT SQL_CALC_FOUND_ROWS  posts.* FROM ".$table_prefix."posts as posts  WHERE posts.post_type = 'wpsc-product' AND (posts.post_status = 'publish' OR posts.post_status = 'future' OR posts.post_status = 'draft' OR posts.post_status = 'pending' OR posts.post_status = 'private') AND posts.id > $start ORDER BY posts.id ASC ";
		}
		else
		{
			$sql = "SELECT DISTINCT * FROM `".WPSC_TABLE_PRODUCT_LIST."` AS `products` WHERE products.ID > $start AND `products`.`active`='1' $search_sql ORDER BY `products`.`ID` ASC limit 0,$limit";
			$sql_total = "SELECT DISTINCT * FROM `".WPSC_TABLE_PRODUCT_LIST."` AS `products` WHERE products.ID > $start AND `products`.`active`='1' $search_sql ORDER BY `products`.`ID` ASC";
		}
		$total_record = count($wpdb->get_results($sql_total,ARRAY_A));
		$product_list = $wpdb->get_results($sql,ARRAY_A);
		if($product_list)
		{
	
			$Items->setStatusCode('0');
			$Items->setStatusMessage('All Ok');
			$Items->setTotalRecordFound($total_record?$total_record:'0');
	
			$itemI = 0;
	
			foreach ($product_list as $iInfo) 
			{			
				$iInfo = $this->parseSpecCharsA($iInfo);
						
				$Item = new WG_Item();	
				
				$cat_array	=	array();
						
				if($version <= '3.7.8')
				{
					$sql = "SELECT * FROM `".WPSC_TABLE_PRODUCTMETA."` AS `meta` WHERE `meta`.`product_id` = ".$iInfo[id]."";
					$product_meta = $wpdb->get_results($sql,ARRAY_A);			
					$product_meta_data = array();
					foreach ($product_meta as $metadata)
					{
						$product_meta_data[$metadata['meta_key']] = $metadata['meta_value'];			
					}
					
					
					
					$sku = $product_meta_data['sku'];
					$name = $iInfo['name'];
					$desc=htmlentities(substr($iInfo['post_excerpt'],0,4000),ENT_QUOTES);	
					$stock = $iInfo['quantity'];
					$Unitprice = $iInfo['price'];
					$price = $iInfo['list_price'];
					$weight = $iInfo['weight'];
					$lowQty = $iInfo['quantity_limited'];
					$freeshipping = $iInfo['free_shipping'];
					$discount =	$iInfo['no_shipping'];
					$shippingFreight = $iInfo['shipping_freight'];
					$unit_s = $config['General'][weight_symbol];
					$unit = $config['General'][weight_symbol_grams];
					if($iInfo['notax']=='0') 
					{
						$taxexempt = 'N';
					}
					elseif($iInfo['notax']=='1')
					{
						$taxexempt = 'N';
					}
					elseif($iInfo['notax'] == 'n')
					{  
					
						$taxexempt = 'Y';
					}
					elseif($iInfo['notax'] == 'N')
					{ 
						$taxexempt = 'Y';
					}
					
				}
				else
				{ 
					$sku = get_post_meta($iInfo['ID'], '_wpsc_sku', true);
					$name = $iInfo['post_title'];
					$desc = $iInfo['post_excerpt'];
					$stock = get_post_meta($iInfo['ID'], '_wpsc_stock', true);
					$Unitprice = get_post_meta($iInfo['ID'], '_wpsc_special_price', true);
					$price = get_post_meta($iInfo['ID'], '_wpsc_price', true);
					$lowQty = "";
					$freeShipping = $product_data['meta']['_wpsc_product_metadata']['no_shipping'];
					if($noShipping == 1)
					{
						$freeshipping = 'Y';
					}
					else
					{
						$freeshipping = 'N';
					}
					$discount = '';
					
					
					
					
					
					$product_data['meta'] = get_post_meta($iInfo['ID'], '');
					
					foreach($product_data['meta'] as $meta_name => $meta_value) 
					{
							$product_data['meta'][$meta_name] = maybe_unserialize(array_pop($meta_value));
					}
					$product_data['transformed'] = array();
					
					if(!isset($product_data['meta']['_wpsc_product_metadata']['weight'])) $product_data['meta']['_wpsc_product_metadata']['weight'] = "";
					if(!isset($product_data['meta']['_wpsc_product_metadata']['weight_unit'])) $product_data['meta']['_wpsc_product_metadata']['weight_unit'] = "";
					
					//$product_data['transformed']['weight'] = wpsc_convert_weight($product_data['meta']['_wpsc_product_metadata']['weight'], "gram", $product_data['meta']['_wpsc_product_metadata']['weight_unit']);
					//$weight = $product_data['transformed']['weight'];
					$weight = $product_data['meta']['_wpsc_product_metadata']['weight'];
					if($weight == '')
					{
						$weight = '0';
					}
					//print_r($product_data['meta']['_wpsc_product_metadata']);
					//continue;
					$unit = $product_data['meta']['_wpsc_product_metadata']['weight_unit'];
					//echo "<li>".$weight."===".$unit;
					switch($unit) {
						case "pound":
							$unit_s = " lbs.";
							break;
						case "ounce":
							$unit_s = " oz.";
							break;
						case "gram":
							$unit_s = " g";
							break;
						case "kilograms":
							$unit_s = " kgs.";
							break;
					}
					
					$tax_band = $product_data['meta']['_wpsc_product_metadata']['wpec_taxes_band'];
					if($tax_band != 'Disabled')
					{
						$taxexempt = 'Y';
					}
					else
					{
						$taxexempt = 'N';
					}
				
					$cat_array = get_the_product_category($iInfo['ID']);
					
					
				}
				
				$Item->setItemID(($version>'3.7.8')?$iInfo['ID']:$iInfo['id']);
				
				$Item->setItemCode($sku);
				$Item->setItemDescription($name);
				$Item->setItemShortDescr($name);
				
				
				//Code to get image
					
				$post_id	=	0;
				$post_mime_type	=	'';
				$image_sql = "SELECT ID FROM ".$table_prefix."posts WHERE post_type = 'attachment' AND post_parent = ".$iInfo['ID']." AND post_mime_type LIKE '%image%'";
				$product_image_result = $wpdb->get_results($image_sql,ARRAY_A);
				if(is_array($product_image_result) && count($product_image_result) > 0) {		
					foreach($product_image_result as $product_image){
						$post_id	=	$product_image['ID'];
						
						
						$wp_attached_file = get_post_meta( $post_id, '_wp_attached_file', true );
					
						//print_r($wp_attachment_metadata_array);die('reached');
						
						if(strlen($wp_attached_file) > 0) {
							
							//$image_file =	$wp_attachment_metadata_array['file'];
							$image_file =	$wp_attached_file;
							$ImagePath	=	'http://'.$_SERVER['HTTP_HOST'].'/'.substr($_SERVER['SCRIPT_NAME'], 1, strpos(substr($_SERVER['SCRIPT_NAME'],1), '/')).'/wp-content/uploads/'.$image_file;
							$image_file_array	=	explode('/', $image_file);
							
							$image_arr=array('ItemID'=>$iInfo['ID'], 'ItemImageID'=>$post_id, 'ItemImageFileName'=>$image_file_array[count($image_file_array) - 1],'ItemImageUrl'=>$ImagePath);
							$Item->setItemImages($image_arr);
							
						}
						
						
					}
				}				
				//End code to get image
				
				
				if($version <= '3.7.8') {
				
					$categoriesI = 0;
					foreach($cat_array as $catid)
					{
						$cat_name_raw = "SELECT name,category_parent FROM ".WPSC_TABLE_PRODUCT_CATEGORIES." WHERE id = ".$catid['category_id']."";
						$cat_name = $wpdb->get_results($cat_name_raw,ARRAY_A);
						foreach($cat_name as $categoty)
						{ 
								unset($catArray);
								$catArray['Category'] =  $categoty['name'];
								$catArray['CategoryId'] = $catid['category_id'];
								
								//$catArray['ParentId'] = $categoty['category_parent'];
								$Item->setCategories($catArray);
								$categoriesI++;
						}
					} 
					
				} else {
				
					//$categoriesNode = $xmlResponse->createTag("Categories",    array(), '',    $itemNode);
					$categoriesI = 0;
					foreach($cat_array as $categoty)
					{ 
							unset($catArray);
							$catArray['Category'] =  $categoty->cat_name;
							$catArray['CategoryId'] = $categoty->term_id;
							//$catArray['ParentId'] = $categoty->category_parent;
							$Item->setCategories($catArray);
							$categoriesI++;
					}
					
				}
				
				$Item->setManufacturer("");
				$Item->setQuantity($stock);
				$Item->setUnitPrice($Unitprice);
				$Item->setListPrice($price);
				$Item->setWeight($weight);
				$Item->setLowQtyLimit($lowQty);
				$Item->setFreeShipping($freeshipping);
				$Item->setDiscounted($discount);
				$Item->setShippingFreight($iInfo['shipping_freight']);
				$Item->setWeight_Symbol($unit_s);
				$Item->setWeight_Symbol_Grams($unit);
				$Item->setTaxExempt($taxexempt);
				
				$Item->setUpdatedAt($iInfo['post_modified']?$iInfo['post_modified']:$iInfo['post_date']);
				
				//$iVariants = $xmlResponse->createTag("ItemVariants", array(), '',$itemNode);
				$Variants = new WG_Variants();
				
				$product_variations = $this->variations_grid_view($iInfo['id']); 						
							
				//$iOptions = $xmlResponse->createTag("ItemOptions", array(), '', $itemNode, __ENCODE_RESPONSE);
				if($product_variations)
				{  
					$op=0;
					$Options = new WG_Options();
					foreach ($product_variations as $ioInfo)
					{
						$ioInfo = parseSpecCharsA($ioInfo);					
						//$xmlResponse->createTag("ItemOption", array("ID"=>$ioInfo['optionid'],"Value"=>$ioInfo['value'],"Name"=>$ioInfo['name']), "",        $iOptions, __ENCODE_RESPONSE);
						$optionArray['ItemOption']['ID'] = $ioInfo['optionid'];
						$optionArray['ItemOption']['Value'] = $ioInfo['value'];
						$optionArray['ItemOption']['Name'] = $ioInfo['name'];
						$Item->setItemOptions($optionArray);
						$op++;
					}
				}
				$Items->setItems($Item->getItem()); 
				$itemI++;
			} // end items
			$Items->setTotalRecordSent($itemI);
		}
		
		return $this->response($Items->getItems());
	} // getItems
	
	
	function getStoreItemByIdForEcc($username,$password,$datefrom,$start,$limit,$storeid,$others="") {

		global $wpdb,$wp_query,$table_prefix;
		
		
		/*$postion_char		=	strpos(substr($_SERVER['PHP_SELF'],1), '/');
		$store_site_path	=	 'http://'.$_SERVER['HTTP_HOST'].'/'.substr($_SERVER['PHP_SELF'], 1, $postion_char);
		$product_image_path	=	$store_site_path.'/components/com_virtuemart/shop_image/product/';*/
		
		//parse_url();
		
		
		#check for authorisation
		$status = $this->auth_user($username,$password);
		if($status!==0)
		{
		  return $status;
		}
		$Items = new WG_Items();
		$version = $this->getVersion();
		
		
		if($version > '3.7.8')
		{
			$sql = "SELECT SQL_CALC_FOUND_ROWS  posts.* FROM ".$table_prefix."posts as posts  WHERE posts.post_type = 'wpsc-product' AND (posts.post_status = 'publish' OR post_status = 'future' OR posts.post_status = 'draft' OR posts.post_status = 'pending' OR posts.post_status = 'private') AND posts.id = $start ORDER BY posts.id ASC limit 0,$limit";
			
			$sql_total = "SELECT SQL_CALC_FOUND_ROWS  posts.* FROM ".$table_prefix."posts as posts  WHERE posts.post_type = 'wpsc-product' AND (posts.post_status = 'publish' OR posts.post_status = 'future' OR posts.post_status = 'draft' OR posts.post_status = 'pending' OR posts.post_status = 'private') AND posts.id = $start ORDER BY posts.id ASC ";
		}
		else
		{
			$sql = "SELECT DISTINCT * FROM `".WPSC_TABLE_PRODUCT_LIST."` AS `products` WHERE products.ID = $start AND `products`.`active`='1' $search_sql ORDER BY `products`.`ID` ASC limit 0,$limit";
			$sql_total = "SELECT DISTINCT * FROM `".WPSC_TABLE_PRODUCT_LIST."` AS `products` WHERE products.ID = $start AND `products`.`active`='1' $search_sql ORDER BY `products`.`ID` ASC";
		}
		$total_record = count($wpdb->get_results($sql_total,ARRAY_A));
		$product_list = $wpdb->get_results($sql,ARRAY_A);
		if($product_list)
		{
	
			$Items->setStatusCode('0');
			$Items->setStatusMessage('All Ok');
			$Items->setTotalRecordFound($total_record?$total_record:'0');
	
			$itemI = 0;
	
			foreach ($product_list as $iInfo) 
			{			
				$iInfo = $this->parseSpecCharsA($iInfo);
						
				$Item = new WG_Item();	
				
				$cat_array	=	array();
						
				if($version <= '3.7.8')
				{
					$sql = "SELECT * FROM `".WPSC_TABLE_PRODUCTMETA."` AS `meta` WHERE `meta`.`product_id` = ".$iInfo[id]."";
					$product_meta = $wpdb->get_results($sql,ARRAY_A);			
					$product_meta_data = array();
					foreach ($product_meta as $metadata)
					{
						$product_meta_data[$metadata['meta_key']] = $metadata['meta_value'];			
					}
					
					
					
					$sku = $product_meta_data['sku'];
					$name = $iInfo['name'];
					$desc=htmlentities(substr($iInfo['post_excerpt'],0,4000),ENT_QUOTES);	
					$stock = $iInfo['quantity'];
					$Unitprice = $iInfo['price'];
					$price = $iInfo['list_price'];
					$weight = $iInfo['weight'];
					$lowQty = $iInfo['quantity_limited'];
					$freeshipping = $iInfo['free_shipping'];
					$discount =	$iInfo['no_shipping'];
					$shippingFreight = $iInfo['shipping_freight'];
					$unit_s = $config['General'][weight_symbol];
					$unit = $config['General'][weight_symbol_grams];
					if($iInfo['notax']=='0') 
					{
						$taxexempt = 'N';
					}
					elseif($iInfo['notax']=='1')
					{
						$taxexempt = 'N';
					}
					elseif($iInfo['notax'] == 'n')
					{  
					
						$taxexempt = 'Y';
					}
					elseif($iInfo['notax'] == 'N')
					{ 
						$taxexempt = 'Y';
					}
					
				}
				else
				{ 
					$sku = get_post_meta($iInfo['ID'], '_wpsc_sku', true);
					$name = $iInfo['post_title'];
					$desc = $iInfo['post_excerpt'];
					$stock = get_post_meta($iInfo['ID'], '_wpsc_stock', true);
					$Unitprice = get_post_meta($iInfo['ID'], '_wpsc_special_price', true);
					$price = get_post_meta($iInfo['ID'], '_wpsc_price', true);
					$lowQty = "";
					$freeShipping = $product_data['meta']['_wpsc_product_metadata']['no_shipping'];
					if($noShipping == 1)
					{
						$freeshipping = 'Y';
					}
					else
					{
						$freeshipping = 'N';
					}
					$discount = '';
					
					
					
					
					
					$product_data['meta'] = get_post_meta($iInfo['ID'], '');
					
					foreach($product_data['meta'] as $meta_name => $meta_value) 
					{
							$product_data['meta'][$meta_name] = maybe_unserialize(array_pop($meta_value));
					}
					$product_data['transformed'] = array();
					
					if(!isset($product_data['meta']['_wpsc_product_metadata']['weight'])) $product_data['meta']['_wpsc_product_metadata']['weight'] = "";
					if(!isset($product_data['meta']['_wpsc_product_metadata']['weight_unit'])) $product_data['meta']['_wpsc_product_metadata']['weight_unit'] = "";
					
					//$product_data['transformed']['weight'] = wpsc_convert_weight($product_data['meta']['_wpsc_product_metadata']['weight'], "gram", $product_data['meta']['_wpsc_product_metadata']['weight_unit']);
					//$weight = $product_data['transformed']['weight'];
					$weight = $product_data['meta']['_wpsc_product_metadata']['weight'];
					if($weight == '')
					{
						$weight = '0';
					}
					//print_r($product_data['meta']['_wpsc_product_metadata']);
					//continue;
					$unit = $product_data['meta']['_wpsc_product_metadata']['weight_unit'];
					//echo "<li>".$weight."===".$unit;
					switch($unit) {
						case "pound":
							$unit_s = " lbs.";
							break;
						case "ounce":
							$unit_s = " oz.";
							break;
						case "gram":
							$unit_s = " g";
							break;
						case "kilograms":
							$unit_s = " kgs.";
							break;
					}
					
					$tax_band = $product_data['meta']['_wpsc_product_metadata']['wpec_taxes_band'];
					if($tax_band != 'Disabled')
					{
						$taxexempt = 'Y';
					}
					else
					{
						$taxexempt = 'N';
					}
				
					$cat_array = get_the_product_category($iInfo['ID']);
					
					
				}
				
				$Item->setItemID(($version>'3.7.8')?$iInfo['ID']:$iInfo['id']);
				
				$Item->setItemCode($sku);
				$Item->setItemDescription($name);
				$Item->setItemShortDescr($name);
				
				
				//Code to get image
					
				$post_id	=	0;
				$post_mime_type	=	'';
				$image_sql = "SELECT ID FROM ".$table_prefix."posts WHERE post_type = 'attachment' AND post_parent = ".$iInfo['ID']." AND post_mime_type LIKE '%image%'";
				$product_image_result = $wpdb->get_results($image_sql,ARRAY_A);
				if(is_array($product_image_result) && count($product_image_result) > 0) {		
					foreach($product_image_result as $product_image){
						$post_id	=	$product_image['ID'];
						
						
						$wp_attached_file = get_post_meta( $post_id, '_wp_attached_file', true );
					
						//print_r($wp_attachment_metadata_array);die('reached');
						
						if(strlen($wp_attached_file) > 0) {
							
							//$image_file =	$wp_attachment_metadata_array['file'];
							$image_file =	$wp_attached_file;
							$ImagePath	=	'http://'.$_SERVER['HTTP_HOST'].'/'.substr($_SERVER['SCRIPT_NAME'], 1, strpos(substr($_SERVER['SCRIPT_NAME'],1), '/')).'/wp-content/uploads/'.$image_file;
							$image_file_array	=	explode('/', $image_file);
							
							$image_arr=array('ItemID'=>$iInfo['ID'], 'ItemImageID'=>$post_id, 'ItemImageFileName'=>$image_file_array[count($image_file_array) - 1],'ItemImageUrl'=>$ImagePath);
							$Item->setItemImages($image_arr);
							
						}
						
						
					}
				}				
				//End code to get image
				
				
				if($version <= '3.7.8') {
				
					$categoriesI = 0;
					foreach($cat_array as $catid)
					{
						$cat_name_raw = "SELECT name,category_parent FROM ".WPSC_TABLE_PRODUCT_CATEGORIES." WHERE id = ".$catid['category_id']."";
						$cat_name = $wpdb->get_results($cat_name_raw,ARRAY_A);
						foreach($cat_name as $categoty)
						{ 
								unset($catArray);
								$catArray['Category'] =  $categoty['name'];
								$catArray['CategoryId'] = $catid['category_id'];
								
								//$catArray['ParentId'] = $categoty['category_parent'];
								$Item->setCategories($catArray);
								$categoriesI++;
						}
					} 
					
				} else {
				
					//$categoriesNode = $xmlResponse->createTag("Categories",    array(), '',    $itemNode);
					$categoriesI = 0;
					foreach($cat_array as $categoty)
					{ 
							unset($catArray);
							$catArray['Category'] =  $categoty->cat_name;
							$catArray['CategoryId'] = $categoty->term_id;
							//$catArray['ParentId'] = $categoty->category_parent;
							$Item->setCategories($catArray);
							$categoriesI++;
					}
					
				}
				
				$Item->setManufacturer("");
				$Item->setQuantity($stock);
				$Item->setUnitPrice($Unitprice);
				$Item->setListPrice($price);
				$Item->setWeight($weight);
				$Item->setLowQtyLimit($lowQty);
				$Item->setFreeShipping($freeshipping);
				$Item->setDiscounted($discount);
				$Item->setShippingFreight($iInfo['shipping_freight']);
				$Item->setWeight_Symbol($unit_s);
				$Item->setWeight_Symbol_Grams($unit);
				$Item->setTaxExempt($taxexempt);
				
				$Item->setUpdatedAt($iInfo['post_modified']?$iInfo['post_modified']:$iInfo['post_date']);
				
				//$iVariants = $xmlResponse->createTag("ItemVariants", array(), '',$itemNode);
				$Variants = new WG_Variants();
				
				$product_variations = $this->variations_grid_view($iInfo['id']); 						
							
				//$iOptions = $xmlResponse->createTag("ItemOptions", array(), '', $itemNode, __ENCODE_RESPONSE);
				if($product_variations)
				{  
					$op=0;
					$Options = new WG_Options();
					foreach ($product_variations as $ioInfo)
					{
						$ioInfo = parseSpecCharsA($ioInfo);					
						//$xmlResponse->createTag("ItemOption", array("ID"=>$ioInfo['optionid'],"Value"=>$ioInfo['value'],"Name"=>$ioInfo['name']), "",        $iOptions, __ENCODE_RESPONSE);
						$optionArray['ItemOption']['ID'] = $ioInfo['optionid'];
						$optionArray['ItemOption']['Value'] = $ioInfo['value'];
						$optionArray['ItemOption']['Name'] = $ioInfo['name'];
						$Item->setItemOptions($optionArray);
						$op++;
					}
				}
				$Items->setItems($Item->getItem()); 
				$itemI++;
			} // end items
			$Items->setTotalRecordSent($itemI);
		}
		
		return $this->response($Items->getItems());
	}
	
	  
	function getItemsQuantity($username,$password){
	
		
		
		global $wpdb,$wp_query,$table_prefix;
		
		#check for authorisation
		$status = $this->auth_user($username,$password);
		if($status!==0)
		{
		  return $status;
		}
		$Items = new WG_Items();
		$version = $this->getVersion();
		
		
		if($version > '3.7.8')
		{
			$sql = "SELECT SQL_CALC_FOUND_ROWS  posts.* FROM ".$table_prefix."posts as posts  WHERE posts.post_type = 'wpsc-product' AND (posts.post_status = 'publish' OR post_status = 'future' OR posts.post_status = 'draft' OR posts.post_status = 'pending' OR posts.post_status = 'private') ORDER BY post_title ASC";
			
			$sql_total = "SELECT SQL_CALC_FOUND_ROWS  posts.* FROM ".$table_prefix."posts as posts  WHERE posts.post_type = 'wpsc-product' AND (posts.post_status = 'publish' OR posts.post_status = 'future' OR posts.post_status = 'draft' OR posts.post_status = 'pending' OR posts.post_status = 'private') ORDER BY post_title ASC ";
		}
		else
		{
			$sql = "SELECT DISTINCT * FROM `".WPSC_TABLE_PRODUCT_LIST."` AS `products` WHERE `products`.`active`='1' $search_sql ORDER BY `products`.`date_added` ASC";
			$sql_total = "SELECT DISTINCT * FROM `".WPSC_TABLE_PRODUCT_LIST."` AS `products` WHERE `products`.`active`='1' $search_sql ORDER BY `products`.`date_added`";
		}
		$total_record = count($wpdb->get_results($sql_total,ARRAY_A));
		$product_list = $wpdb->get_results($sql,ARRAY_A);
		if($product_list)
		{
	
			$Items->setStatusCode('0');
			$Items->setStatusMessage('All Ok');
			$Items->setTotalRecordFound($total_record?$total_record:'0');
	
			$itemI = 0;
	
			foreach ($product_list as $iInfo) 
			{			//print_r($iInfo);
				$iInfo = $this->parseSpecCharsA($iInfo);
						
				$Item = new WG_Item();	
				
				$cat_array	=	array();
						
				if($version <= '3.7.8')
				{
					$sql = "SELECT * FROM `".WPSC_TABLE_PRODUCTMETA."` AS `meta` WHERE `meta`.`product_id` = ".$iInfo[id]."";
					$product_meta = $wpdb->get_results($sql,ARRAY_A);			
					$product_meta_data = array();
					foreach ($product_meta as $metadata)
					{
						$product_meta_data[$metadata['meta_key']] = $metadata['meta_value'];			
					}
					$sku = $product_meta_data['sku'];
					$name = $iInfo['name'];
					$desc=htmlentities(substr($iInfo['post_excerpt'],0,4000),ENT_QUOTES);	
					$stock = $iInfo['quantity'];
					$Unitprice = $iInfo['price'];
					$price = $iInfo['list_price'];
					$weight = $iInfo['weight'];
					$lowQty = $iInfo['quantity_limited'];
					$freeshipping = $iInfo['free_shipping'];
					$discount =	$iInfo['no_shipping'];
					$shippingFreight = $iInfo['shipping_freight'];
					$unit_s = $config['General'][weight_symbol];
					$unit = $config['General'][weight_symbol_grams];
					if($iInfo['notax']=='0') 
					{
						$taxexempt = 'N';
					}
					elseif($iInfo['notax']=='1')
					{
						$taxexempt = 'N';
					}
					elseif($iInfo['notax'] == 'n')
					{  
					
						$taxexempt = 'Y';
					}
					elseif($iInfo['notax'] == 'N')
					{ 
						$taxexempt = 'Y';
					}
					
				}
				else
				{ 
					$sku = get_post_meta($iInfo['ID'], '_wpsc_sku', true);
					$name = $iInfo['post_title'];
					$desc = $iInfo['post_excerpt'];
					$stock = get_post_meta($iInfo['ID'], '_wpsc_stock', true);
					$Unitprice = get_post_meta($iInfo['ID'], '_wpsc_special_price', true);
					$price = get_post_meta($iInfo['ID'], '_wpsc_price', true);
					$lowQty = "";
					$freeShipping = $product_data['meta']['_wpsc_product_metadata']['no_shipping'];
					if($noShipping == 1)
					{
						$freeshipping = 'Y';
					}
					else
					{
						$freeshipping = 'N';
					}
					$discount = '';
					
					
					$product_data['meta'] = get_post_meta($iInfo['ID'], '');
					
					foreach($product_data['meta'] as $meta_name => $meta_value) 
					{
							$product_data['meta'][$meta_name] = maybe_unserialize(array_pop($meta_value));
					}
					$product_data['transformed'] = array();
					
					if(!isset($product_data['meta']['_wpsc_product_metadata']['weight'])) $product_data['meta']['_wpsc_product_metadata']['weight'] = "";
					if(!isset($product_data['meta']['_wpsc_product_metadata']['weight_unit'])) $product_data['meta']['_wpsc_product_metadata']['weight_unit'] = "";
					
					//$product_data['transformed']['weight'] = wpsc_convert_weight($product_data['meta']['_wpsc_product_metadata']['weight'], "gram", $product_data['meta']['_wpsc_product_metadata']['weight_unit']);
					//$weight = $product_data['transformed']['weight'];
					$weight = $product_data['meta']['_wpsc_product_metadata']['weight']; 
					if($weight == '')
					{
						$weight = '0';
					}
					//print_r($product_data['meta']['_wpsc_product_metadata']);
					//continue;
					$unit = $product_data['meta']['_wpsc_product_metadata']['weight_unit'];
					//echo "<li>".$weight."===".$unit;
					switch($unit) {
						case "pound":
							$unit_s = " lbs.";
							break;
						case "ounce":
							$unit_s = " oz.";
							break;
						case "gram":
							$unit_s = " g";
							break;
						case "kilograms":
							$unit_s = " kgs.";
							break;
					}
					
					$tax_band = $product_data['meta']['_wpsc_product_metadata']['wpec_taxes_band'];
					if($tax_band != 'Disabled')
					{
						$taxexempt = 'Y';
					}
					else
					{
						$taxexempt = 'N';
					}
				
					$cat_array = get_the_product_category($iInfo['ID']);
					
					
				}
				
				$Item->setItemID(($version>'3.7.8')?$iInfo['ID']:$iInfo['id']);
				$Item->setItemCode($sku);
				$Item->setQuantity($stock);
				$Item->setUnitPrice($Unitprice);
				$Item->setListPrice($price);
				$Item->setWeight($weight);

				$Items->setItems($Item->getItem()); 
				$itemI++;
			} // end items
			$Items->setTotalRecordSent($itemI);
		}
		
		return $this->response($Items->getItems());
	
	
	
	}  
	  
	function getPriceQtyBySku($username,$password,$limit,$storeid=1,$items) {
	
		
		
	
		
		
		global $wpdb,$wp_query,$table_prefix;
		
		#check for authorisation
		$status = $this->auth_user($username,$password);
		if($status!==0)
		{
		  return $status;
		}
		$Items = new WG_Items();
		$version = $this->getVersion();
		
		$items	=	"'".implode("','", explode(',', $items))."'";
		
		if($version > '3.7.8')
		{
			$sql = "SELECT SQL_CALC_FOUND_ROWS  posts.* FROM ".$table_prefix."posts as posts  WHERE posts.post_type = 'wpsc-product' AND (posts.post_status = 'publish' OR post_status = 'future' OR posts.post_status = 'draft' OR posts.post_status = 'pending' OR posts.post_status = 'private')  AND post_title IN ($items) ORDER BY post_title ASC";
			
			$sql_total = "SELECT SQL_CALC_FOUND_ROWS  posts.* FROM ".$table_prefix."posts as posts  WHERE posts.post_type = 'wpsc-product' AND (posts.post_status = 'publish' OR posts.post_status = 'future' OR posts.post_status = 'draft' OR posts.post_status = 'pending' OR posts.post_status = 'private')  AND post_title IN ($items) ORDER BY post_title ASC ";
		}
		else
		{
			$sql = "SELECT DISTINCT * FROM `".WPSC_TABLE_PRODUCT_LIST."` AS `products` WHERE _wpsc_sku IN ($items) AND  `products`.`active`='1' $search_sql ORDER BY `products`.`date_added` ASC";
			$sql_total = "SELECT DISTINCT * FROM `".WPSC_TABLE_PRODUCT_LIST."` AS `products` WHERE _wpsc_sku IN ($items) AND  `products`.`active`='1' $search_sql ORDER BY `products`.`date_added`";
		}
		$total_record = count($wpdb->get_results($sql_total,ARRAY_A));
		$product_list = $wpdb->get_results($sql,ARRAY_A);
		if($product_list)
		{
	
			$Items->setStatusCode('0');
			$Items->setStatusMessage('All Ok');
			$Items->setTotalRecordFound($total_record?$total_record:'0');
	
			$itemI = 0;
	
			foreach ($product_list as $iInfo) 
			{			//print_r($iInfo);
				$iInfo = $this->parseSpecCharsA($iInfo);
						
				$Item = new WG_Item();	
				
				$cat_array	=	array();
						
				if($version <= '3.7.8')
				{
					$sql = "SELECT * FROM `".WPSC_TABLE_PRODUCTMETA."` AS `meta` WHERE `meta`.`product_id` = ".$iInfo[id]."";
					$product_meta = $wpdb->get_results($sql,ARRAY_A);			
					$product_meta_data = array();
					foreach ($product_meta as $metadata)
					{
						$product_meta_data[$metadata['meta_key']] = $metadata['meta_value'];			
					}
					$sku = $product_meta_data['sku'];
					$name = $iInfo['name'];
					$desc=htmlentities(substr($iInfo['post_excerpt'],0,4000),ENT_QUOTES);	
					$stock = $iInfo['quantity'];
					$Unitprice = $iInfo['price'];
					$price = $iInfo['list_price']; 
					$weight = $iInfo['weight'];
					$lowQty = $iInfo['quantity_limited'];
					$freeshipping = $iInfo['free_shipping'];
					$discount =	$iInfo['no_shipping'];
					$shippingFreight = $iInfo['shipping_freight'];
					$unit_s = $config['General'][weight_symbol];
					$unit = $config['General'][weight_symbol_grams];
					if($iInfo['notax']=='0') 
					{
						$taxexempt = 'N';
					}
					elseif($iInfo['notax']=='1')
					{
						$taxexempt = 'N';
					}
					elseif($iInfo['notax'] == 'n')
					{  
					
						$taxexempt = 'Y';
					}
					elseif($iInfo['notax'] == 'N')
					{ 
						$taxexempt = 'Y';
					}
					
				}
				else
				{ 
					$sku = get_post_meta($iInfo['ID'], '_wpsc_sku', true);
					$name = $iInfo['post_title'];
					$desc = $iInfo['post_excerpt'];
					$stock = get_post_meta($iInfo['ID'], '_wpsc_stock', true);
					$Unitprice = get_post_meta($iInfo['ID'], '_wpsc_special_price', true); 
					$price = get_post_meta($iInfo['ID'], '_wpsc_price', true);
					$lowQty = "";
					$freeShipping = $product_data['meta']['_wpsc_product_metadata']['no_shipping'];
					if($noShipping == 1)
					{
						$freeshipping = 'Y';
					}
					else
					{
						$freeshipping = 'N';
					}
					$discount = '';
					
					
					$product_data['meta'] = get_post_meta($iInfo['ID'], '');
					
					foreach($product_data['meta'] as $meta_name => $meta_value) 
					{
							$product_data['meta'][$meta_name] = maybe_unserialize(array_pop($meta_value));
					}
					$product_data['transformed'] = array();
					
					if(!isset($product_data['meta']['_wpsc_product_metadata']['weight'])) $product_data['meta']['_wpsc_product_metadata']['weight'] = "";
					if(!isset($product_data['meta']['_wpsc_product_metadata']['weight_unit'])) $product_data['meta']['_wpsc_product_metadata']['weight_unit'] = "";
					
					//$product_data['transformed']['weight'] = wpsc_convert_weight($product_data['meta']['_wpsc_product_metadata']['weight'], "gram", $product_data['meta']['_wpsc_product_metadata']['weight_unit']);
					//$weight = $product_data['transformed']['weight'];
					$weight = $product_data['meta']['_wpsc_product_metadata']['weight'];
					if($weight == '')
					{
						$weight = '0';
					}
					//print_r($product_data['meta']['_wpsc_product_metadata']);
					//continue;
					$unit = $product_data['meta']['_wpsc_product_metadata']['weight_unit'];
					//echo "<li>".$weight."===".$unit;
					switch($unit) {
						case "pound":
							$unit_s = " lbs.";
							break;
						case "ounce":
							$unit_s = " oz.";
							break;
						case "gram":
							$unit_s = " g";
							break;
						case "kilograms":
							$unit_s = " kgs.";
							break;
					}
					
					$tax_band = $product_data['meta']['_wpsc_product_metadata']['wpec_taxes_band'];
					if($tax_band != 'Disabled')
					{
						$taxexempt = 'Y';
					}
					else
					{
						$taxexempt = 'N';
					}
				
					$cat_array = get_the_product_category($iInfo['ID']);
					
					
				}
				
				$Item->setItemID(($version>'3.7.8')?$iInfo['ID']:$iInfo['id']);
				$Item->setItemCode($sku);
				$Item->setQuantity($stock);
				$Item->setUnitPrice($Unitprice);
				$Item->setListPrice($price);
				$Item->setWeight($weight);

				$Items->setItems($Item->getItem()); 
				$itemI++;
			} // end items
			$Items->setTotalRecordSent($itemI);
		}
		
		return $this->response($Items->getItems());
	
	
	
	
	
	}  
	  /**
	   * This is one part of the code that displays the variation combination forms in the add and edit product pages.
	   * If this fails to find any data about the variation combinations, it runs "variations_add_grid_view" instead
	   */
	  function variations_grid_view($product_id, $variation_values = null) 
	  {  
			global $wpdb;
			$product_id = (int)$product_id;
			$product_data = $wpdb->get_row("SELECT `price`, `quantity_limited` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id` IN ('{$product_id}') LIMIT 1", ARRAY_A);
			$product_price = $product_data['price'];    
			
			$associated_variations = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_VARIATION_ASSOC."` WHERE `type` IN ('product') AND `associated_id` = '{$product_id}' ORDER BY `id` ASC",ARRAY_A);
			$variation_count = count($associated_variations);
		   
			
			if($variation_count > 0) {
			  
			  foreach((array)$associated_variations as $key => $associated_variation) {
				$variation_id = (int)$associated_variation['variation_id'];
				$excluded_values = $wpdb->get_col("SELECT `value_id` FROM `".WPSC_TABLE_VARIATION_VALUES_ASSOC."` WHERE `product_id` IN('{$associated_variation['associated_id']}') AND `variation_id` IN ('{$variation_id}') AND `visible` IN ('1')");
				
				$included_value_sql = "AND `b{$variation_id}`.`value_id`  IN('".implode("','", $excluded_values)."')";
			  
				// generate all the various bits of SQL to bind the tables together
				$join_selected_cols[] = "`b{$variation_id}`.`value_id` AS `value_id{$variation_id}`";
				$join_tables[] = "`".WPSC_TABLE_VARIATION_COMBINATIONS."` AS `b{$variation_id}`";
				$join_on[] = "`a`.`id` = `b{$variation_id}`.`priceandstock_id`";
				$join_conditions[] = "`b{$variation_id}`.`variation_id` = '{$variation_id}' AND `b{$variation_id}`.`all_variation_ids` IN (':all_variation_ids:') $included_value_sql";
				$join_order[] = "`value_id{$variation_id}` ASC";
				
				// also store the columns in which the value ID's are, because we need them later
				$table_columns[] = "value_id{$variation_id}";
				
				$selected_variations[] = $variation_id;
				
				$get_variation_names = $wpdb->get_results("SELECT `id`, `name` FROM `".WPSC_TABLE_VARIATION_VALUES."` WHERE `variation_id` = '{$variation_id}'", ARRAY_A);
				
				foreach((array)$get_variation_names as $get_variation_name) {
				  $variation_names[$get_variation_name['id']] = $get_variation_name['name'];
				}
			  }
			  
			  // implode the SQL statment segments into bigger segments
			  $join_selected_cols = implode(", ", $join_selected_cols);
			  $join_tables = implode(" JOIN ", $join_tables);
			  $join_on = implode(" AND ", $join_on);
			  $join_conditions = implode(" AND ", $join_conditions);
			  $join_order = implode(", ", $join_order);
			  
			  
			  asort($selected_variations);      
			  $all_variation_ids = implode(",", $selected_variations);
			  $join_conditions = str_replace(":all_variation_ids:",$all_variation_ids, $join_conditions );
			  
			  // Assemble and execute the SQL query
			   $associated_variation_values = $wpdb->get_results("SELECT `a`.*, {$join_selected_cols} FROM  `".WPSC_TABLE_VARIATION_PROPERTIES."` AS `a` JOIN {$join_tables} ON {$join_on} WHERE `a`.`product_id` = '$product_id' AND {$join_conditions} ORDER BY {$join_order}", ARRAY_A);
			   
				   
				   
					// if there are no associated variations, run this function instead
			if(count($associated_variation_values) < 1) {
				$price = $wpdb->get_var("SELECT `price` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id` ='{$product_id}' LIMIT 1");
				return variations_add_grid_view((array)$selected_variations, $variation_values, $price, $limited_stock, $product_id);
			 }
			 
			  $br=0;
			  foreach((array)$associated_variation_values as $key => $associated_variation_row) {
			  
				// generate the variation name and ID arrays
				$associated_variation_names = array();
				$associated_variation_ids = array();
				foreach((array)$table_columns as $table_column) {
				  $associated_variation_ids[] =  $associated_variation_row[$table_column];
				  $associated_variation_names[] =  $variation_names[$associated_variation_row[$table_column]];
				}
				$group_defining_class = '';
				
				if($associated_variation_ids[0] != $associated_variation_values[$key+1]["value_id{$selected_variations[0]}"]) {
				  $group_defining_class = "group_boundary";
				}
				$previous_row_id = $associated_variation_ids[0];
				
				// Implode them into a comma seperated string
				$associated_variation_names =  stripslashes(implode(", ",(array)$associated_variation_names));
				
				$associated_variation_ids = implode(",",(array)$associated_variation_ids);
				
				$variation_settings_uniqueid = $product_id."_".str_replace(",","_",$associated_variation_ids);
				
			  
				// Format the price nicely
				if(is_numeric($associated_variation_row['price'])) {
				  $product_price = number_format($associated_variation_row['price'],2,'.', '');
				}
				$file_checked = '';
				if((int)$associated_variation_row['file'] == 1) {
				  $file_checked = "checked='checked'";
				}
						
				$var[$br]['name']  = $associated_variation_names;
				$var[$br]['stock'] = $associated_variation_row['stock'];
				$var[$br]['price'] = $product_price;  
				$var[$br]['optionid'] = $associated_variation_ids;  
				
						   
				$br++;
			  }
			
			}
			return $var;
		}
	
	function variations_add_grid_view($variations, $variation_values = null, $default_price = null, $limited_stock = true, $product_id = 0) 
	{	
		global $wpdb;
		$variation_count = count($variations);
		if($variation_count < 1) 
		{
			return "";
			exit();
		}
		$stock_column_state = '';
		if($limited_stock == false) {
		  $stock_column_state = " style='display: none;'";
		}
			if((float)$default_price == 0) {
			  $default_price = 0;
			}
		$default_price = number_format($default_price,2,'.', '');
	
		// Need to join the wp_variation_values variation_values`table to itself multiple times with no condition for joining, resulting in every combination of values being extracted
			foreach((array)$variations as $variation) {
		  $variation = (int)$variation;
		  
				$excluded_value_sql = '';
				if($product_id > 0 ) {
				  $included_values = $wpdb->get_col("SELECT `value_id` FROM `".WPSC_TABLE_VARIATION_VALUES_ASSOC."` WHERE `product_id` IN('{$product_id}') AND `variation_id` IN ('{$variation}') AND `visible` IN ('1')");
					$included_values_sql = "AND `a{$variation}`.`id` IN('".implode("','", $included_values)."')";
				} else if(count($variation_values) > 0) {
					$included_values_sql = "AND `a{$variation}`.`id` IN('".implode("','", $variation_values)."')";
				
				}
				
		  
		  // generate all the various bits of SQL to bind the tables together
		  $join_selected_cols[] = "`a{$variation}`.`id` AS `id_{$variation}`, `a{$variation}`.`name` AS `name_{$variation}`";
		  $join_tables[] = "`".WPSC_TABLE_VARIATION_VALUES."` AS `a{$variation}`";
		  $join_conditions[] = "`a{$variation}`.`variation_id` = '{$variation}' $included_values_sql";
		}
		
		// implode the SQL statment segments into bigger segments
		$join_selected_cols = implode(", ", $join_selected_cols);
		$join_tables = implode(" JOIN ", $join_tables);
		$join_conditions = implode(" AND ", $join_conditions);
		//echo "/*\nSELECT {$join_selected_cols} FROM {$join_tables} WHERE {$join_conditions} \n*/ \n";
		// Assemble and execute the SQL query
		$associated_variation_values = $wpdb->get_results("SELECT {$join_selected_cols} FROM {$join_tables} WHERE {$join_conditions}", ARRAY_A);
		
		//echo "/*\n\r"."SELECT {$join_selected_cols} FROM {$join_tables} WHERE {$join_conditions}"."\n\r*/";
			
			$variation_sets = array();
			$i = 0;
			foreach((array)$associated_variation_values as $associated_variation_value_set) {
			  foreach($variations as $variation) {
				$value_id = $associated_variation_value_set["id_$variation"];
				$name_id = $associated_variation_value_set["name_$variation"];
				$variation_sets[$i][$value_id] = $name_id;
			  }
		  $i++;
			}
			$br=0;
		foreach((array)$variation_sets as $key => $variation_set) {
		  //echo "<pre>".print_r($asssociated_variation_set,true)."</pre>";
		  $variation_names = implode(", ", $variation_set);
		  $variation_id_array = array_keys((array)$variation_set);
		  $variation_ids = implode(",", $variation_id_array);
		  $variation_settings_uniqueid = "0_".str_replace(",","_",$variation_ids);
		  
		  $group_defining_class = '';
		  
		  $next_id_set = array_keys((array)$variation_sets[$key+1]);
		  //echo "<pre>".print_r($variation_set,true)."</pre>";
		  if($variation_id_array[0] != $next_id_set[0]) {
			$group_defining_class = "group_boundary";
		  } 
		  
			$var[$br]['name']  = $variation_names;		
			//$var[$br]['stock'] = $variation_ids['stock'];
			//$var[$br]['price'] = $default_price;		  
			$var[$br]['optionid'] = $variation_ids;  		           
			$br++;
		}	
		return $var;
		}
	
	function ItemUpdatePriceQty($username,$password,$itemId,$qty,$price,$cost,$weight,$storeid=1) {
	
		
		//echo base64_decode('cGFzc3dvcmQ=');
		
		global $wpdb; 	
		global $totaltax;
		global $totaldiscount;	
		//$requestArray = json_decode($data,true);	
		$version = $this->getVersion();
		$Items = new WG_Items();
		
		if (!isset($itemId)) {
			$Items->setStatusCode('9997');
			$Items->setStatusMessage('Unknown request or request not in proper format');				
			return $this->response($Items->getItemsNode());				
		}
		$Items->setStatusCode('0');
		$Items->setStatusMessage('Item successfully updated');
				
		$Item = new WG_Item();
		$productID = $itemId;
			
		if ($qty!="")
		{		
			
			//$wpdb->query($wpdb->prepare("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `quantity` = '".$qty."'  WHERE `id` = '".$productID."' "));
			if($version > '3.7.8')
			{
				 if(update_post_meta($productID, '_wpsc_stock', $qty)) 
				 {
				 	
					//Update weight 
				 	update_post_meta($productID, '_wpsc_product_metadata', $weight);
				 
					$status = "Success";
					
				 }
				 else 
				 {
					 $status = "failed";
				  }
			}
			else
			{	
				//echo "UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `quantity` = '".$qty."', weight='".$weight."'  WHERE `id` = '".$productID."' ";
				$wpdb->query($wpdb->prepare("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `quantity` = '".$qty."', weight='".$weight."'  WHERE `id` = '".$productID."' "));
				//$wpdb->query($wpdb->prepare("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `quantity` = '".$qty."' WHERE `id` = '".$productID."' "));
				$status = "Success";
			}
			  
		}
		
		if ($price!="")
		{
			if($version > '3.7.8')
			{
				if(update_post_meta($productID, '_wpsc_special_price', $price)) 
				//if(update_post_meta($productID, '_wpsc_price', $price)) 
				{
					update_post_meta($productID, '_wpsc_price', $cost);   
					 $status = "Success";
					 
				} 
				else 
				{
					$status = "failed";
				}
			}
			else
			{
				//echo "UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `price` = '".$price."', list_price='".$cost."'  WHERE `id` = '".$productID."' ";
				$wpdb->query($wpdb->prepare("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `price` = '".$price."', list_price='".$cost."'  WHERE `id` = '".$productID."' "));
				//$wpdb->query($wpdb->prepare("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `price` = '".$price."' WHERE `id` = '".$productID."' "));
				$status = "Success";			
			}
		}
		if ($status =="") $status ="Success";	

		$Item->setStatus($status);
		$Items->setItems($Item->getItem());			
		return $this->response($Items->getItems());
	}
	
	
	#
	# Return the Count of the orders remained with specific dates and status
	#
	function getOrdersRemained($start_date,$start_order_no=0, $type=0)
	{
	   global $wpdb;
	   
	   $previous_orders = 0;  
	   
	  
	  // $previous_orders = $wpdb->get_var("SELECT COUNT(*) FROM `".WPSC_TABLE_PURCHASE_LOGS."` a,`".WPSC_TABLE_PURCHASE_STATUSES."` b WHERE a.`date`>=".$start_date." AND a.`id`>".$start_order_no." AND a.`processed`=b.`id` and b.name in (".QB_ORDERS_DOWNLOAD_EXCL_LIST.") order by a.`id` asc" );
	  
	  if($type > 0) {
	  	$previous_orders = $wpdb->get_var("SELECT COUNT(*) FROM `".WPSC_TABLE_PURCHASE_LOGS."` a WHERE a.`date`>=".$start_date." AND a.`id`=".$start_order_no." order by a.`id` ASC ");
	  } else {
		$previous_orders = $wpdb->get_var("SELECT COUNT(*) FROM `".WPSC_TABLE_PURCHASE_LOGS."` a WHERE a.`date`>=".$start_date." AND a.`id`>".$start_order_no." order by a.`id` ASC ");
	}	
	
	
	  // $previous_orders = $wpdb->get_var("SELECT COUNT(*) FROM `".WPSC_TABLE_PURCHASE_LOGS."` a,`".WPSC_TABLE_PURCHASE_STATUSES."` b WHERE a.`date`>=".$start_date." AND a.`id`>".$start_order_no." AND  b.name in (".QB_ORDERS_DOWNLOAD_EXCL_LIST.") order by a.`id` asc" );

	   return $previous_orders;
	}

	#
	# Return the Orders to sync with the QB according to the date and the staus and order id.
	#
	function getOrders($username,$password,$datefrom,$start_order_no,$ecc_excl_list,$order_per_response,$storeid,$devicetokenid) {
		
		global $wpdb;
		global $purchlogitem; 
		global $purchlogs;
		$version = $this->getVersion();
		
		
		if(!isset($datefrom) or empty($datefrom)) $datefrom = time();
		if(!isset($dateto) or empty($dateto)) $dateto = time();
		
		list($mm,$dd,$yy)=explode("-",$datefrom);
		$query="SELECT unix_timestamp('".$yy."-".$mm."-".$dd."');";	
		$datefrom = $var = $wpdb->get_var($query); 
	
		#check for authorisation
		$status = $this->auth_user($username,$password);
		if($status!='0')
		{
		  return $status;
		}
	
					
		define("QB_ORDERS_DOWNLOAD_EXCL_LIST", $ecc_excl_list);
		define("QB_ORDERS_PER_RESPONSE",$order_per_response);  
	   
		#
		# Get total no of orders available for the said filter criteria excluding start order no.
		#
		$all_gateway = array();
		if($GLOBALS['nzshpcrt_gateways']) 
		{
			foreach($GLOBALS['nzshpcrt_gateways'] as $gateway) 
			{
				if($gateway['internalname'] == 'testmode')
				{
					$all_gateway[$gateway['name']] = $gateway['admin_name'];
				}
				else
				{	
					$all_gateway[$gateway['internalname']] = $gateway['name'];
				}		
			}	
		}
	
		$orders_remained = $this->getOrdersRemained($datefrom,$start_order_no);	
		$orders_remained=$orders_remained>0?$orders_remained:0;	
		
		
		//$orders = $wpdb->get_results("SELECT a.*,b.name as order_status FROM `".WPSC_TABLE_PURCHASE_LOGS."` a,`".WPSC_TABLE_PURCHASE_STATUSES."` b WHERE a.`date`>=".$datefrom." AND a.`id`>".$start_order_no." AND a.`processed`=b.`id` and b.name in (".QB_ORDERS_DOWNLOAD_EXCL_LIST.") order by a.`id` ASC ".(QB_ORDERS_PER_RESPONSE>echo "SELECT a.*,b.name as order_status FROM `".WPSC_TABLE_PURCHASE_LOGS."` a ,`".WPSC_TABLE_PURCHASE_STATUSES."` b  WHERE a.`date`>=".$datefrom." AND a.`id`>".$start_order_no." AND b.name in (".QB_ORDERS_DOWNLOAD_EXCL_LIST.") order by a.`id` ASC ".(QB_ORDERS_PER_RESPONSE>0?"LIMIT 0, ".QB_ORDERS_PER_RESPONSE:''),ARRAY_A);
		
		$orders = $wpdb->get_results("SELECT a.* FROM `".WPSC_TABLE_PURCHASE_LOGS."` a WHERE a.`date`>=".$datefrom." AND a.`id`>".$start_order_no." order by a.`id` ASC ".(QB_ORDERS_PER_RESPONSE>0?"LIMIT 0, ".QB_ORDERS_PER_RESPONSE:''),ARRAY_A);
		//$orders = $wpdb->get_results("SELECT a.*,b.name as order_status FROM `".WPSC_TABLE_PURCHASE_LOGS."` a  ,`".WPSC_TABLE_PURCHASE_STATUSES."` b WHERE a.`date`>=".$datefrom." AND a.`id`>".$start_order_no." AND b.name in (".QB_ORDERS_DOWNLOAD_EXCL_LIST.") order by a.`id` ASC ".(QB_ORDERS_PER_RESPONSE>0?"LIMIT 0, ".QB_ORDERS_PER_RESPONSE:''),ARRAY_A);	
		//echo 	$no_orders = count($orders);
		//die("hihihi");
		
		$no_orders = count($orders);
		$Orders = new WG_Orders();
		if ($no_orders<=0) {
			$no_orders = true;
			//$xmlResponse->createTag("StatusCode", array(), "9999", $root, __ENCODE_RESPONSE);
//			$xmlResponse->createTag("StatusMessage", array(), "No Orders returned", $root, __ENCODE_RESPONSE);

			$Orders->setStatusCode($no_orders?"9999":"0");
			$Orders->setStatusMessage($no_orders?"No Orders returned":"Total Orders:".$orders_remained);
			return $this->response($Orders->getOrders());
		  }
		# Fetch All state
		$state = array();
		$state_sql =  "SELECT id, name  FROM `".WPSC_TABLE_REGION_TAX."`";						
		$states = $wpdb->get_results($state_sql,ARRAY_A);
		
		$logs = new wpsc_purchaselogs();
		$orderStatus = $logs->the_purch_item_statuses();
		
		//$status = array();	
//		foreach($orderStatus as $k=>$v)
//		{ 
//			$status[$k+1]['order'] = $v['order'];
//			$status[$k+1]['internalname'] = $v['internalname'];
//			$status[$k+1]['label'] = $v['label'];
//		}
		foreach($states  as $states2)
		{
			$state[$states2['id']] = $states2['name'];
		}
	
		# Fetch all countries 
		$country_sql =  "SELECT isocode,country  FROM `".WPSC_TABLE_CURRENCY_LIST."`";		
		$countries = $wpdb->get_results($country_sql,ARRAY_A);
		foreach($countries as $countries )
		{
			$country[$countries['isocode']] = $countries['country'];
		}
		//print_r($orders);
		//die("hi");
		//$ordersNode = $xmlResponse->createTag("Orders", array(), '', $root);
		if($orders){
		
			//$xmlResponse->createTag("StatusCode", array(), "0", $ordersNode, __ENCODE_RESPONSE);
//			$xmlResponse->createTag("StatusMessage", array(), "Total Orders:".$orders_remained, $ordersNode, __ENCODE_RESPONSE);
			$Orders->setStatusCode(0);
			$Orders->setStatusMessage("Total Orders:".$orders_remained);
			$Orders->setTotalRecordFound($orders_remained?(int)$orders_remained:"0");
			
			$ordCountForRecordSentWg = 0;
			$last_order_downloaded_by_app = 0;#required for apns
			foreach($orders as $order) {
			
					$_REQUEST['purchaselog_id'] = $order['id'];
					
					//print_r($order);
					
					if(isset($_REQUEST['purchaselog_id'])){
			
						$purchlogitem = new wpsc_purchaselogs_items((int)$_REQUEST['purchaselog_id']);
						$purchlogs = new wpsc_purchaselogs((int)$_REQUEST['purchaselog_id']);
					}
					
					# Billing Info
					$gateway_name=wpsc_display_purchlog_paymentmethod();
					$billingfirstname = $purchlogitem->userinfo['billingfirstname']['value'];
					$billinglastname = $purchlogitem->userinfo['billinglastname']['value'];
					$billingaddress = wpsc_display_purchlog_buyers_phone();
					$billingemail = wpsc_display_purchlog_buyers_email();
					$billingphone = wpsc_display_purchlog_buyers_phone();
				
					# Shipping Info
					//$shippingmethod = wpsc_display_purchlog_shipping_method();
					$shippingfirstname = $purchlogitem->shippinginfo['shippingfirstname']['value'];
					$shippinglastname = $purchlogitem->shippinginfo['shippinglastname']['value'];
					$shippingaddress = wpsc_display_purchlog_shipping_address();
					$shippingcity = wpsc_display_purchlog_shipping_city();
						
					$shippingcountry = wpsc_display_purchlog_shipping_country();
					$shippingpostcode = $purchlogitem->shippinginfo['shippingpostcode']['value'];
					
					
					# Discount and Shipping
					$discountval = wpsc_purchaselog_details_discount(true);		
					$shippingval[0] = wpsc_display_purchlog_shipping(true);		
			
					$weightsymbol = 'lbs';
					$weight_symbol_grams ='453.6';
								
					$cartsql = "SELECT * FROM `".WPSC_TABLE_CART_CONTENTS."` WHERE `purchaseid`=".$order['id']."";			
					$cart_log = $wpdb->get_results($cartsql,ARRAY_A) ; 
					
					$form_sql = "SELECT * FROM `".WPSC_TABLE_SUBMITED_FORM_DATA."` WHERE  `log_id` = '".(int)$order['id']."'";
					$input_data = $wpdb->get_results($form_sql,ARRAY_A);
			
					if (is_array($input_data) and count($input_data) > 0)
					{
						foreach($input_data as $input_row) 
						{
							 $rekeyed_input[$input_row['form_id']] = $input_row;
						}
					}		
					
					$form_data = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `active` = '1'",ARRAY_A);
					
			
					foreach($form_data as $form_field) {			
						if(strstr($form_field['unique_name'],'billing'))
						{
							$order['billing'][$form_field['name']] = $rekeyed_input[$form_field['id']]['value'];
							
						}else if (strstr($form_field['unique_name'],'shipping'))
						{
							$order['shipping'][$form_field['name']] = $rekeyed_input[$form_field['id']]['value'];
										
						}			
					}		
					
					if($version < '3.7.6.2')
					{		
						$billingcountry =  "SELECT country  FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `isocode` LIKE  '".$order['billing']['Country']."'";			
						$billingcount = $wpdb->get_results($billingcountry,ARRAY_A);
						$billingcountryname = $billingcount[0]['country'];
					}
					else
					{
						$test = $order['billing']['Country'];
						$billingcount = unserialize($test);
						$billingcountryname = $billingcount[0];
						$billingstate = $billingcount[1];
						
					}
		
					$extra = unserialize($order['extra']);
			
					$sql = "SELECT option_value FROM $wpdb->options Where option_name ='blogname' ";
					$storename = $wpdb->get_results($sql,ARRAY_A);
					
					$Order = new WG_Order();
					
					$objOrderInfo	=	new WG_OrderInfo();
					
					$objOrderInfo->setOrderId($order['id']);
					$objOrderInfo->setTitle($order['title']?$order['title']:"");
					$objOrderInfo->setFirstName($billingfirstname);
					$objOrderInfo->setLastName($billinglastname);
					$objOrderInfo->setDate(date("Y-m-d",$order['date']));
					$objOrderInfo->setTime(date("H:i:s A",$order['date']));
					$objOrderInfo->setStoreID('');
					$objOrderInfo->setStoreName($storename[0]['option_value']);
					$objOrderInfo->setCurrency("USD");
					$objOrderInfo->setWeight_Symbol($weightsymbol);
					$objOrderInfo->setWeight_Symbol_Grams($weight_symbol_grams);
					$objOrderInfo->setCustomerId($order['user_ID'] ? $order['user_ID'] : "");
					
					
					//$xmlResponse->createTag("Status", array(),$order['order_status'], $orderNode, __ENCODE_RESPONSE);
					$status = $orderStatus[$order['processed']-1];
					
					$objOrderInfo->setStatus($status['label']?$status['label']:"");
					
					if (!empty($order['details']))
					{
						$order['details'] = text_decrypt($order['details']);
					}
					
					$Note1 = $purchlogitem->customcheckoutfields;
					foreach($Note1 as $note)
					{
			
						$note_val = $note['value'];
					}
			
					if(isset($order['notes']) && $order['notes']!='')
					{
						$Notes = (trim($note_val))." ".(trim($order['notes']));
					}else 
					{
						$Notes = (trim($note_val))." ".(trim($order['details']));
					}
					
					
					//$xmlResponse->createTag("Notes", array(), $Notes  , $orderNode, __ENCODE_RESPONSE);
					//$xmlResponse->createTag("Fax",array(), $order['fax'],$orderNode, __ENCODE_RESPONSE, __ENCODE_RESPONSE);
					$objOrderInfo->setNotes($Notes);
					$objOrderInfo->setFax($order['fax']?$order['fax']:"");
					
					$Order->setOrderInfo($objOrderInfo->getOrderInfo());
					$Order->setComment($customer_comment);
					
					# Orders/Bill/CreditCard info
					$shipto = '';
					$Bill = new WG_Bill();
					//$CreditCard = new WG_CreditCard();
					//$creditCard = $xmlResponse->createTag("CreditCard",  array(), '',   $billNode, __ENCODE_RESPONSE);
					if(isset($order['transactid']))
					{
						 $transactid = $order['transactid'];
					}
					else
					{
						$transactid ="";
					}
					//$xmlResponse->createTag("TransactionId",   array(), $transactid,     $creditCard, __ENCODE_RESPONSE);
					//$CreditCard->setTransactionId($transactid);
					//$CreditCard->getCreditCard();
					//$Bill->setCreditCardInfo($CreditCard->getCreditCard());
					/*if (isset($order_details->payment_details))
					{
						$card_type = $order_details->payment_details['cc_type'];
						$card_no =  $order_details->payment_details['cc_number'];
						$expiration_date = ($order_details->payment_details['cc_exp_month']."/".$order_details->payment_details['cc_exp_year']);
						
						if (!empty($card_type) || (!empty($card_no)))
						{
							$creditCard = $xmlResponse->createTag("CreditCard",  array(), '',   $billNode);
							$xmlResponse->createTag("CreditCardType",     array(), $card_type,       $creditCard, __ENCODE_RESPONSE);
							$xmlResponse->createTag("CreditCardCharge",   array(), '',  $creditCard, __ENCODE_RESPONSE);
							$xmlResponse->createTag("ExpirationDate",     array(), $expiration_date,    $creditCard, __ENCODE_RESPONSE);
							$xmlResponse->createTag("CreditCardName",     array(), '',       $creditCard, __ENCODE_RESPONSE);
							$xmlResponse->createTag("CreditCardNumber",   array(), $card_no,     $creditCard, __ENCODE_RESPONSE);
							$xmlResponse->createTag("CVV2",   array(), '',     $creditCard, __ENCODE_RESPONSE);
							$xmlResponse->createTag("AdvanceInfo",   array(), '',     $creditCard, __ENCODE_RESPONSE);
							$xmlResponse->createTag("TransactionId",   array(), '',     $creditCard, __ENCODE_RESPONSE);
							
						}
					}
					unset($card_type,$expiration_date,$card_no);*/
					
					//$xmlResponse->createTag("Comment",  array(), $Notes, $orderNode, __ENCODE_RESPONSE);
					//$Order->setComment($Notes);
					$Order->setComment("");
					//$Bill->setPayMethod($gateway_name);
					$Bill->setPayMethod($gateway_name);
					$Bill->setPayStatus(isset($all_gateway[$gateway_name]) ? $all_gateway[$gateway_name] : "");
					$Bill->setTitle($order['b_title']?$order['b_title']:"");
					$Bill->setFirstName($billingfirstname);
					$Bill->setLastName($billinglastname);
					$Bill->setCompanyName($order['billing']['company']?$order['billing']['company']:"");
					$addr_arr =  split("\n",$order['billing']['Address']);
					$addr_line1 = $addr_arr[0];
					$addr_line2 = $addr_arr[1];
					
			
					#Billing details
					$Bill->setAddress1($addr_line1);				
					$Bill->setAddress2($addr_line2?$addr_line2:"");				
					$Bill->setCity($order['billing']['City']);				
					$Bill->setState($state[$billingcount[1]]?$state[$billingcount[1]]:"");				
					$Bill->setZip($order['billing']['Postal Code']);				
					$Bill->setCountry($country[$billingcount[0]]?$country[$billingcount[0]]:"");				
					$Bill->setEmail($billingemail);				
					$Bill->setPhone($billingphone);				
					$Bill->setPONumber($order['billing']['PONumber']?$order['billing']['PONumber']:"");								
					$Order->setOrderBillInfo($Bill->getBill());	
					
					# Orders/Ship info
					$Ship =new WG_Ship();
					$shippingmethod = $order['shipping_option'];
					$carrier =  $order['shipping_method'];
		
					$Ship->setShipMethod($shippingmethod);
					$Ship->setCarrier($carrier);
					$Ship->setTrackingNumber($order['track_id'] != 'NA' ? $order['track_id'] : "");
					$Ship->setTitle($order['s_title']?$order['s_title']:"");
					$Ship->setFirstName($shippingfirstname);
					$Ship->setLastName($shippinglastname);
					$Ship->setCompanyName($order['billing']['CompanyName']?$order['billing']['CompanyName']:"");
			
					$addr_line1 ='';
					$addr_line2 ='';
					$addr_arr =  split("\n",$order['shipping']['Address']);
					$addr_line1 = $addr_arr[0];
					$addr_line2 = $addr_arr[1];
					
					$Ship->setAddress1($shippingaddress);
					$Ship->setAddress2('');
					$Ship->setCity($shippingcity);
					$Ship->setState($state[$order['shipping_region']]?$state[$order['shipping_region']]:"");
					$Ship->setZip($shippingpostcode);
					$Ship->setCountry($country[$shippingcountry]);
					$Ship->setEmail('');
					$Ship->setPhone($order['shipping']['Phone']?$order['shipping']['Phone']:"");
					$Order->setOrderShipInfo($Ship->getShip());
			
					foreach($cart_log as $cart_row) {
						
						if($version > '3.7.8')
						{
							$productsql= "SELECT SQL_CALC_FOUND_ROWS  wp_posts.* FROM wp_posts   WHERE wp_posts.ID = ".$cart_row['prodid']." ORDER BY post_title ASC ";
							$product_data = $wpdb->get_results($productsql,ARRAY_A);
							//print_r($product_data);
							
						}
						else
						{
							$productsql= "SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id`=".$cart_row['prodid']."";
							$product_data = $wpdb->get_results($productsql,ARRAY_A); 
						}
					
						
			
						if($cart_row['donation'] != 1) {
							$all_donations = false;
						}
						/*if($cart_row['no_shipping'] != 1) {
							$shipping = $cart_row['pnp'] * $cart_row['quantity'];
							$total_shipping += $shipping;            
							$all_no_shipping = false;
						} else {
							$shipping = 0;
						}*/
						$price = $cart_row['price'] * $cart_row['quantity'];
						$gst = $price - ($price  / (1+($cart_row['gst'] / 100)));
						
						if($gst > 0) {
						  $tax_per_item = $gst / $cart_row['quantity'];
						}
						
						if($version<='3.7.8')
						{
						
							$sql = "SELECT meta_value FROM `".WPSC_TABLE_PRODUCTMETA."` AS `meta` WHERE `meta`.`product_id` = ".$cart_row['prodid']." and `meta_key`='custom_tax'";
							$product_meta = $wpdb->get_results($sql,ARRAY_A);
							$custom_tax= count($product_meta);
							if($custom_tax>=1)
							{
								$customtax_val = $product_meta[0]['meta_value'];
							}
							else
							{
								$customtax_val = 0;
							}
							$product_meta_data = array();
							
							
							
							$sql = "SELECT meta_value FROM `".WPSC_TABLE_PRODUCTMETA."` AS `meta` WHERE `meta`.`product_id` = ".$cart_row['prodid']." and `meta_key`='sku'";
							$product_meta_sku = $wpdb->get_results($sql,ARRAY_A);			
							$product_meta_data_sku = array();
						}
			
						$Item = new WG_Item();	
					
						if($version>'3.7.8')
						{
							$sku = get_post_meta($cart_row['prodid'], '_wpsc_sku', true);
							$product_name = $product_data[0]['post_title'];
							$description = $product_data[0]['post_excerpt'];
							//$weight = $product_data_1['meta']['_wpsc_product_metadata']['weight'];
							$product_data_1['meta'] = get_post_meta($cart_row['prodid'], '');
						foreach($product_data_1['meta'] as $meta_name => $meta_value) 
						{
								$product_data_1['meta'][$meta_name] = maybe_unserialize(array_pop($meta_value));
						}
						
						$product_data_1['transformed'] = array();
						if(!isset($product_data_1['meta']['_wpsc_product_metadata']['weight'])) $product_data_1['meta']['_wpsc_product_metadata']['weight'] = "";
						if(!isset($product_data_1['meta']['_wpsc_product_metadata']['weight_unit'])) $product_data_1['meta']['_wpsc_product_metadata']['weight_unit'] = "";
						
						//$product_data_1['transformed']['weight'] = wpsc_convert_weight($product_data_1['meta']['_wpsc_product_metadata']['weight'], "lbs", $product_data_1['meta']['_wpsc_product_metadata']['weight_unit']);
						//$weight = $product_data_1['transformed']['weight'];
						$weight = $product_data_1['meta']['_wpsc_product_metadata']['weight'];
						if($weight == ''){
							$weight = '0';
						}
						//print_r($product_data['meta']['_wpsc_product_metadata']);
						//continue;
						$unit = $product_data_1['meta']['_wpsc_product_metadata']['weight_unit'];
						switch($unit) {
							case "pound":
								$unit_s = " lbs.";
								break;
							case "ounce":
								$unit_s = " oz.";
								break;
							case "gram":
								$unit_s = " g";
								break;
							case "kilograms":
								$unit_s = " kgs.";
								break;
						}
							
						}
						else
						{
							$sku = $product_meta_sku[0]['meta_value'];
							$product_name = $product_data[0]['name'];
							$description = $product_data[0]['description'];
							$weight = $product_data[0]['weight'];
							
						}
						
						//$xmlResponse->createTag("ItemCode",       array(), $sku, $itemNode, __ENCODE_RESPONSE);
						$Item->setItemCode($sku);		
						//$xmlResponse->createTag("ItemDescription",array(), htmlentities($product_name, ENT_QUOTES), $itemNode, __ENCODE_RESPONSE);
						$desc=$product_name;
						if($product_name!='')		
						{
							//$xmlResponse->createTag("ItemShortDescr",array(), htmlentities($description), $itemNode, __ENCODE_RESPONSE);
							$Item->setItemDescription(stripslashes($desc));
							$Item->setItemShortDescr(stripslashes($desc));
						}
						else
						{
							//$xmlResponse->createTag("ItemShortDescr",array(), "", $itemNode, __ENCODE_RESPONSE);
							$Item->setItemDescription('');
							$Item->setItemShortDescr('');
						}
						
						$sql = "SELECT meta_value FROM `".WPSC_TABLE_PRODUCTMETA."` AS `meta` WHERE `meta`.`product_id` = ".$cart_row['prodid']." and `meta_key`='sku'";
						$product_meta_sku = $wpdb->get_results($sql,ARRAY_A);	
						if($cart_row['tax_charged'] > 0)
						{
							$item_price = $cart_row['price']+$cart_row['pnp']-$cart_row['tax_charged'];
						}
						else
						{
							
							$item_price = $cart_row['price']+$cart_row['pnp'];
						}
						$Item->setQuantity($cart_row['quantity']);
						$Item->setUnitPrice($item_price?$item_price:"0.00");
						$Item->setWeight((float)$weight);
						$Item->setFreeShipping($product_data[0]['no_shipping']);
						$Item->setDiscounted($discountval);
						$Item->setshippingFreight($cart_row['pnp']);
						$Item->setWeight_Symbol($weightsymbol);
						$Item->setWeight_Symbol_Grams($weight_symbol_grams);
						
						if($product_data[0]['notax']=='0') 
						{
							$taxexempt = 'N';
							$taxval = $cart_row['tax_charged'];
						}
						elseif($product_data[0]['notax']=='1')
						{
							$taxexempt = 'N';
							$taxval = $cart_row['tax_charged'];
						}
						elseif($product_data[0]['notax'] == 'n')
						{  
						
							$taxexempt = 'Y';
							//$taxval = "";
							$taxval = $cart_row['tax_charged'];
						}
						elseif($product_data[0]['notax'] == 'N')
						{ 
							$taxexempt = 'Y';
							//$taxval = "";
							$taxval = $cart_row['tax_charged'];
						}
						//$xmlResponse->createTag("TaxExempt",      array(), $taxexempt,        $itemNode, __ENCODE_RESPONSE);
						$Item->setTaxExempt($taxexempt);
						
						//$xmlResponse->createTag("OneTimeCharge",      array(), "",        $itemNode, __ENCODE_RESPONSE);
						$Item->setOneTimeCharge('');
						//$xmlResponse->createTag("ItemTaxAmount",      array(), $taxval, $itemNode, __ENCODE_RESPONSE); 
						$Item->setItemTaxAmount($taxval);
						
						//$iOptions = $xmlResponse->createTag("ItemOptions",      array(),  $variation_list,        $itemNode);		
						$responseArray['ItemOptions'] = array();
						
						$variation_sql = "SELECT * FROM `".WPSC_TABLE_CART_ITEM_VARIATIONS."` WHERE `cart_id`='".$cart_row['id']."'";
						
						$variation_data = $wpdb->get_results($variation_sql,ARRAY_A); 
						$variation_count = count($variation_data);
						
						if($variation_count >=1)
						{
							foreach($variation_data as $variation) 
							{
								$name = "SELECT * FROM `".WPSC_TABLE_PRODUCT_VARIATIONS."` WHERE `id`='".$variation['variation_id']."' LIMIT 1";
								$variation_name = $wpdb->get_results($name ,ARRAY_A);
								
								$option = "SELECT * FROM `".WPSC_TABLE_VARIATION_VALUES."` WHERE `id`='".$variation['value_id']."' LIMIT 1";
								$variation_options = $wpdb->get_results($option ,ARRAY_A);
									
								//$xmlResponse->createTag("ItemOption", array("Value"=> htmlentities($variation_options[0]['name']),"Name"=>htmlentities($variation_name[0]['name'])), "", $iOptions, __ENCODE_RESPONSE);
								$responseArray['ItemOptions'][$optionI]['Name'] = ($variation_name[0]['name']);
								$responseArray['ItemOptions'][$optionI]['Value'] = ($variation_options[0]['name']);
							  }
						  }
						
						 $custom_tax_val = 0.0;
						 ## code for custom tax
						 ##$custom_tax_val = (($cart_row['price']*$customtax_val)/100)."\n";
						 $custom_tax_val_tot +=  $custom_tax_val; 
						 unset($tax_calc);
						//$tax_calc = $cart_row['tax_charged']*$cart_row['quantity'];
						$tax_calc = $cart_row['tax_charged'];
						 $basetax += $tax_calc;
						//echo $order['id']."==".$tot_shipping = $total_shipping."\n";
						$shipping = $shipping+$cart_row['pnp'];
						
						$Order->setOrderItems($Item->getItem());		
					} // end items 
					$totaltax = $custom_tax_val_tot +  $basetax + $order['wpec_taxes_total'];
					
					//echo "<li>".$order['id']."===".$custom_tax_val_tot."*****".$basetax;
					$tot_shipping = $shippingval[0]+ $shipping;
					$charges =new WG_Charges();
					if($order['discount_value']!= '' && $order['discount_value'] != '0.00')
					{
					
						$Item = new WG_Item();
						if(isset($order['discount_data']))
						{
							$coupon_title = "Discount Coupon (".$order['discount_data'].")";
							$coupon_sku = $order['discount_data'];
						}
						else
						{
							$coupon_title = "Discount Coupon";
							$coupon_sku = "Discount Coupon";
						}
							$coupon_amt = abs($disc_type['amount']);
						
						$Item->setItemCode(htmlentities($coupon_sku, ENT_QUOTES));		
						$Item->setItemDescription(htmlentities($coupon_title, ENT_QUOTES));
						$Item->setQuantity('1');
						$Item->setUnitPrice('-'.$order['discount_value']);
						$Item->setWeight('0');
						$Item->setFreeShipping('N');
						$Order->setOrderItems($Item->getItem());
						//$charges->setDiscount($order['discount_value']?$order['discount_value']:"0.00");
					}
					else
					{
						$charges->setDiscount('0.00');
					}
					
					$charges->setStoreCredit('0.00');
					//$xmlResponse->createTag("Tax",      array(), $totaltax,				$chargesNode, __ENCODE_RESPONSE);
					$charges->setTax($totaltax?$totaltax:'0.00');
					unset($totaltax,$custom_tax_val, $custom_tax_val_tot,$tax_calc,$basetax);
					//$xmlResponse->createTag("Shipping", array(), $tot_shipping,   $chargesNode, __ENCODE_RESPONSE);
					$charges->setShipping($tot_shipping?$tot_shipping:'0.00');
					unset($tot_shipping, $prod_shipping,$shippingval[0],$shipping);
					//$xmlResponse->createTag("Total",    array(), $order['totalprice'],			$chargesNode, __ENCODE_RESPONSE);
					
					$charges->setTotal($order['totalprice']?$order['totalprice']:"0.00");
					
					$Order->setOrderChargeInfo($charges->getCharges());
							
					$Order->setShippedOn(date("m-d-Y",$order['date']));
					$Order->setShippedVia($carrier);
					$Orders->setOrders($Order->getOrder());
					$ordCountForRecordSentWg++;
					#Set last order id for apns alert
					$last_order_downloaded_by_app = $order['id'];#required for apns
				
			}
			
			#Update apns-config.txt for apns alert
			$this->modifyApnsConfigFile($last_order_downloaded_by_app, 'get_order', $devicetokenid);#required for apns
			  
		}
		$Orders->setTotalRecordSent($ordCountForRecordSentWg);
		return $this->response($Orders->getOrders());
	}  // getOrders
	
	
	
	function getStoreOrderByIdForEcc($username,$password,$datefrom,$start_order_no,$ecc_excl_list,$order_per_response="25") {
		global $wpdb;
		global $purchlogitem; 
		global $purchlogs;
		$version = $this->getVersion();
		
		
		if(!isset($datefrom) or empty($datefrom)) $datefrom = time();
		if(!isset($dateto) or empty($dateto)) $dateto = time();
		
		list($mm,$dd,$yy)=explode("-",$datefrom);
		$query="SELECT unix_timestamp('".$yy."-".$mm."-".$dd."');";	
		$datefrom = $var = $wpdb->get_var($query); 
	
		#check for authorisation
		$status = $this->auth_user($username,$password);
		if($status!='0')
		{
		  return $status;
		}
	
					
		define("QB_ORDERS_DOWNLOAD_EXCL_LIST", $ecc_excl_list);
		define("QB_ORDERS_PER_RESPONSE",$order_per_response);  
	   
		#
		# Get total no of orders available for the said filter criteria excluding start order no.
		#
		$all_gateway = array();
		if($GLOBALS['nzshpcrt_gateways']) 
		{
			foreach($GLOBALS['nzshpcrt_gateways'] as $gateway) 
			{
				if($gateway['internalname'] == 'testmode')
				{
					$all_gateway[$gateway['name']] = $gateway['admin_name'];
				}
				else
				{	
					$all_gateway[$gateway['internalname']] = $gateway['name'];
				}		
			}	
		}
	
		$orders_remained = $this->getOrdersRemained($datefrom,$start_order_no,1);	
		$orders_remained=$orders_remained>0?$orders_remained:0;	
		
		
		//$orders = $wpdb->get_results("SELECT a.*,b.name as order_status FROM `".WPSC_TABLE_PURCHASE_LOGS."` a,`".WPSC_TABLE_PURCHASE_STATUSES."` b WHERE a.`date`>=".$datefrom." AND a.`id`>".$start_order_no." AND a.`processed`=b.`id` and b.name in (".QB_ORDERS_DOWNLOAD_EXCL_LIST.") order by a.`id` ASC ".(QB_ORDERS_PER_RESPONSE>echo "SELECT a.*,b.name as order_status FROM `".WPSC_TABLE_PURCHASE_LOGS."` a ,`".WPSC_TABLE_PURCHASE_STATUSES."` b  WHERE a.`date`>=".$datefrom." AND a.`id`>".$start_order_no." AND b.name in (".QB_ORDERS_DOWNLOAD_EXCL_LIST.") order by a.`id` ASC ".(QB_ORDERS_PER_RESPONSE>0?"LIMIT 0, ".QB_ORDERS_PER_RESPONSE:''),ARRAY_A);
		
		$orders = $wpdb->get_results("SELECT a.* FROM `".WPSC_TABLE_PURCHASE_LOGS."` a WHERE a.`date`>=".$datefrom." AND a.`id`=".$start_order_no." order by a.`id` ASC ".(QB_ORDERS_PER_RESPONSE>0?"LIMIT 0, ".QB_ORDERS_PER_RESPONSE:''),ARRAY_A);
		//$orders = $wpdb->get_results("SELECT a.*,b.name as order_status FROM `".WPSC_TABLE_PURCHASE_LOGS."` a  ,`".WPSC_TABLE_PURCHASE_STATUSES."` b WHERE a.`date`>=".$datefrom." AND a.`id`>".$start_order_no." AND b.name in (".QB_ORDERS_DOWNLOAD_EXCL_LIST.") order by a.`id` ASC ".(QB_ORDERS_PER_RESPONSE>0?"LIMIT 0, ".QB_ORDERS_PER_RESPONSE:''),ARRAY_A);	
		//echo 	$no_orders = count($orders);
		//die("hihihi");
		
		$no_orders = count($orders);
		$Orders = new WG_Orders();
		if ($no_orders<=0) {
			$no_orders = true;
			//$xmlResponse->createTag("StatusCode", array(), "9999", $root, __ENCODE_RESPONSE);
//			$xmlResponse->createTag("StatusMessage", array(), "No Orders returned", $root, __ENCODE_RESPONSE);

			$Orders->setStatusCode($no_orders?"9999":"0");
			$Orders->setStatusMessage($no_orders?"No Orders returned":"Total Orders:".$orders_remained);
			return $this->response($Orders->getOrders());
		  }
		# Fetch All state
		$state = array();
		$state_sql =  "SELECT id, name  FROM `".WPSC_TABLE_REGION_TAX."`";						
		$states = $wpdb->get_results($state_sql,ARRAY_A);
		
		$logs = new wpsc_purchaselogs();
		$orderStatus = $logs->the_purch_item_statuses();
		
		//$status = array();	
//		foreach($orderStatus as $k=>$v)
//		{ 
//			$status[$k+1]['order'] = $v['order'];
//			$status[$k+1]['internalname'] = $v['internalname'];
//			$status[$k+1]['label'] = $v['label'];
//		}
		foreach($states  as $states2)
		{
			$state[$states2['id']] = $states2['name'];
		}
	
		# Fetch all countries 
		$country_sql =  "SELECT isocode,country  FROM `".WPSC_TABLE_CURRENCY_LIST."`";		
		$countries = $wpdb->get_results($country_sql,ARRAY_A);
		foreach($countries as $countries )
		{
			$country[$countries['isocode']] = $countries['country'];
		}
		//print_r($orders);
		//die("hi");
		//$ordersNode = $xmlResponse->createTag("Orders", array(), '', $root);
		if($orders){
		
			//$xmlResponse->createTag("StatusCode", array(), "0", $ordersNode, __ENCODE_RESPONSE);
//			$xmlResponse->createTag("StatusMessage", array(), "Total Orders:".$orders_remained, $ordersNode, __ENCODE_RESPONSE);
			$Orders->setStatusCode(0);
			$Orders->setStatusMessage("Total Orders:".$orders_remained);
			$Orders->setTotalRecordFound($orders_remained?(int)$orders_remained:"0");
			
			$ordCountForRecordSentWg = 0;
			
			foreach($orders as $order) {
			
			$_REQUEST['purchaselog_id'] = $order['id'];
			
			//print_r($order);
			
			if(isset($_REQUEST['purchaselog_id'])){
	
				$purchlogitem = new wpsc_purchaselogs_items((int)$_REQUEST['purchaselog_id']);
				$purchlogs = new wpsc_purchaselogs((int)$_REQUEST['purchaselog_id']);
			}
			
			# Billing Info
			$gateway_name=wpsc_display_purchlog_paymentmethod();
			$billingfirstname = $purchlogitem->userinfo['billingfirstname']['value'];
			$billinglastname = $purchlogitem->userinfo['billinglastname']['value'];
			$billingaddress = wpsc_display_purchlog_buyers_phone();
			$billingemail = wpsc_display_purchlog_buyers_email();
			$billingphone = wpsc_display_purchlog_buyers_phone();
		
			# Shipping Info
			//$shippingmethod = wpsc_display_purchlog_shipping_method();
			$shippingfirstname = $purchlogitem->shippinginfo['shippingfirstname']['value'];
			$shippinglastname = $purchlogitem->shippinginfo['shippinglastname']['value'];
			$shippingaddress = wpsc_display_purchlog_shipping_address();
			$shippingcity = wpsc_display_purchlog_shipping_city();
				
			$shippingcountry = wpsc_display_purchlog_shipping_country();
			$shippingpostcode = $purchlogitem->shippinginfo['shippingpostcode']['value'];
			
			
			# Discount and Shipping
			$discountval = wpsc_purchaselog_details_discount(true);		
			$shippingval[0] = wpsc_display_purchlog_shipping(true);		
	
			$weightsymbol = 'lbs';
			$weight_symbol_grams ='453.6';
						
			$cartsql = "SELECT * FROM `".WPSC_TABLE_CART_CONTENTS."` WHERE `purchaseid`=".$order['id']."";			
			$cart_log = $wpdb->get_results($cartsql,ARRAY_A) ; 
			
			$form_sql = "SELECT * FROM `".WPSC_TABLE_SUBMITED_FORM_DATA."` WHERE  `log_id` = '".(int)$order['id']."'";
			$input_data = $wpdb->get_results($form_sql,ARRAY_A);
	
			if (is_array($input_data) and count($input_data) > 0)
			{
				foreach($input_data as $input_row) 
				{
					 $rekeyed_input[$input_row['form_id']] = $input_row;
				}
			}		
			
			$form_data = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `active` = '1'",ARRAY_A);
			
	
			foreach($form_data as $form_field) {			
				if(strstr($form_field['unique_name'],'billing'))
				{
					$order['billing'][$form_field['name']] = $rekeyed_input[$form_field['id']]['value'];
					
				}else if (strstr($form_field['unique_name'],'shipping'))
				{
					$order['shipping'][$form_field['name']] = $rekeyed_input[$form_field['id']]['value'];
								
				}			
			}		
			
			if($version < '3.7.6.2')
			{		
				$billingcountry =  "SELECT country  FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `isocode` LIKE  '".$order['billing']['Country']."'";			
				$billingcount = $wpdb->get_results($billingcountry,ARRAY_A);
				$billingcountryname = $billingcount[0]['country'];
			}
			else
			{
				$test = $order['billing']['Country'];
				$billingcount = unserialize($test);
				$billingcountryname = $billingcount[0];
				$billingstate = $billingcount[1];
				
			}

			$extra = unserialize($order['extra']);
	
			$sql = "SELECT option_value FROM $wpdb->options Where option_name ='blogname' ";
			$storename = $wpdb->get_results($sql,ARRAY_A);
			
			$Order = new WG_Order();
			
			$objOrderInfo	=	new WG_OrderInfo();
			
			$objOrderInfo->setOrderId($order['id']);
			$objOrderInfo->setTitle($order['title']?$order['title']:"");
			$objOrderInfo->setFirstName($billingfirstname);
			$objOrderInfo->setLastName($billinglastname);
			$objOrderInfo->setDate(date("Y-m-d",$order['date']));
			$objOrderInfo->setTime(date("H:i:s A",$order['date']));
			$objOrderInfo->setStoreID('');
			$objOrderInfo->setStoreName($storename[0]['option_value']);
			$objOrderInfo->setCurrency("USD");
			$objOrderInfo->setWeight_Symbol($weightsymbol);
			$objOrderInfo->setWeight_Symbol_Grams($weight_symbol_grams);
			$objOrderInfo->setCustomerId($order['user_ID'] ? $order['user_ID'] : "");
			
			
			//$xmlResponse->createTag("Status", array(),$order['order_status'], $orderNode, __ENCODE_RESPONSE);
			$status = $orderStatus[$order['processed']-1];
			
			$objOrderInfo->setStatus($status['label']?$status['label']:"");
			
			if (!empty($order['details']))
			{
				$order['details'] = text_decrypt($order['details']);
			}
			
			$Note1 = $purchlogitem->customcheckoutfields;
			foreach($Note1 as $note)
			{
	
				$note_val = $note['value'];
			}
	
			if(isset($order['notes']) && $order['notes']!='')
			{
				$Notes = (trim($note_val))." ".(trim($order['notes']));
			}else 
			{
				$Notes = (trim($note_val))." ".(trim($order['details']));
			}
			
			
			//$xmlResponse->createTag("Notes", array(), $Notes  , $orderNode, __ENCODE_RESPONSE);
			//$xmlResponse->createTag("Fax",array(), $order['fax'],$orderNode, __ENCODE_RESPONSE, __ENCODE_RESPONSE);
			$objOrderInfo->setNotes($Notes);
			$objOrderInfo->setFax($order['fax']?$order['fax']:"");
			
			$Order->setOrderInfo($objOrderInfo->getOrderInfo());
			$Order->setComment($customer_comment);
			
			# Orders/Bill/CreditCard info
			$shipto = '';
			$Bill = new WG_Bill();
			//$CreditCard = new WG_CreditCard();
			//$creditCard = $xmlResponse->createTag("CreditCard",  array(), '',   $billNode, __ENCODE_RESPONSE);
			if(isset($order['transactid']))
			{
				 $transactid = $order['transactid'];
			}
			else
			{
				$transactid ="";
			}
			//$xmlResponse->createTag("TransactionId",   array(), $transactid,     $creditCard, __ENCODE_RESPONSE);
			//$CreditCard->setTransactionId($transactid);
			//$CreditCard->getCreditCard();
			//$Bill->setCreditCardInfo($CreditCard->getCreditCard());
			/*if (isset($order_details->payment_details))
			{
				$card_type = $order_details->payment_details['cc_type'];
				$card_no =  $order_details->payment_details['cc_number'];
				$expiration_date = ($order_details->payment_details['cc_exp_month']."/".$order_details->payment_details['cc_exp_year']);
				
				if (!empty($card_type) || (!empty($card_no)))
				{
					$creditCard = $xmlResponse->createTag("CreditCard",  array(), '',   $billNode);
					$xmlResponse->createTag("CreditCardType",     array(), $card_type,       $creditCard, __ENCODE_RESPONSE);
					$xmlResponse->createTag("CreditCardCharge",   array(), '',  $creditCard, __ENCODE_RESPONSE);
					$xmlResponse->createTag("ExpirationDate",     array(), $expiration_date,    $creditCard, __ENCODE_RESPONSE);
					$xmlResponse->createTag("CreditCardName",     array(), '',       $creditCard, __ENCODE_RESPONSE);
					$xmlResponse->createTag("CreditCardNumber",   array(), $card_no,     $creditCard, __ENCODE_RESPONSE);
					$xmlResponse->createTag("CVV2",   array(), '',     $creditCard, __ENCODE_RESPONSE);
					$xmlResponse->createTag("AdvanceInfo",   array(), '',     $creditCard, __ENCODE_RESPONSE);
					$xmlResponse->createTag("TransactionId",   array(), '',     $creditCard, __ENCODE_RESPONSE);
					
				}
			}
			unset($card_type,$expiration_date,$card_no);*/
			
			//$xmlResponse->createTag("Comment",  array(), $Notes, $orderNode, __ENCODE_RESPONSE);
			//$Order->setComment($Notes);
			$Order->setComment("");
			//$Bill->setPayMethod($gateway_name);
			$Bill->setPayMethod($gateway_name);
			$Bill->setPayStatus(isset($all_gateway[$gateway_name]) ? $all_gateway[$gateway_name] : "");
			$Bill->setTitle($order['b_title']?$order['b_title']:"");
			$Bill->setFirstName($billingfirstname);
			$Bill->setLastName($billinglastname);
			$Bill->setCompanyName($order['billing']['company']?$order['billing']['company']:"");
			$addr_arr =  split("\n",$order['billing']['Address']);
			$addr_line1 = $addr_arr[0];
			$addr_line2 = $addr_arr[1];
			
	
			#Billing details
			$Bill->setAddress1($addr_line1);				
			$Bill->setAddress2($addr_line2?$addr_line2:"");				
			$Bill->setCity($order['billing']['City']);				
			$Bill->setState($state[$billingcount[1]]?$state[$billingcount[1]]:"");				
			$Bill->setZip($order['billing']['Postal Code']);				
			$Bill->setCountry($country[$billingcount[0]]?$country[$billingcount[0]]:"");				
			$Bill->setEmail($billingemail);				
			$Bill->setPhone($billingphone);				
			$Bill->setPONumber($order['billing']['PONumber']?$order['billing']['PONumber']:"");								
			$Order->setOrderBillInfo($Bill->getBill());	
			
			# Orders/Ship info
			$Ship =new WG_Ship();
			$shippingmethod = $order['shipping_option'];
			$carrier =  $order['shipping_method'];

			$Ship->setShipMethod($shippingmethod);
			$Ship->setCarrier($carrier);
			$Ship->setTrackingNumber($order['track_id'] != 'NA' ? $order['track_id'] : "");
			$Ship->setTitle($order['s_title']?$order['s_title']:"");
			$Ship->setFirstName($shippingfirstname);
			$Ship->setLastName($shippinglastname);
			$Ship->setCompanyName($order['billing']['CompanyName']?$order['billing']['CompanyName']:"");
	
			$addr_line1 ='';
			$addr_line2 ='';
			$addr_arr =  split("\n",$order['shipping']['Address']);
			$addr_line1 = $addr_arr[0];
			$addr_line2 = $addr_arr[1];
			
			$Ship->setAddress1($shippingaddress);
			$Ship->setAddress2('');
			$Ship->setCity($shippingcity);
			$Ship->setState($state[$order['shipping_region']]?$state[$order['shipping_region']]:"");
			$Ship->setZip($shippingpostcode);
			$Ship->setCountry($country[$shippingcountry]);
			$Ship->setEmail('');
			$Ship->setPhone($order['shipping']['Phone']?$order['shipping']['Phone']:"");
			$Order->setOrderShipInfo($Ship->getShip());
	
			foreach($cart_log as $cart_row) {
				
				if($version > '3.7.8')
				{
					$productsql= "SELECT SQL_CALC_FOUND_ROWS  wp_posts.* FROM wp_posts   WHERE wp_posts.ID = ".$cart_row['prodid']." ORDER BY post_title ASC ";
					$product_data = $wpdb->get_results($productsql,ARRAY_A);
					//print_r($product_data);
					
				}
				else
				{
					$productsql= "SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id`=".$cart_row['prodid']."";
					$product_data = $wpdb->get_results($productsql,ARRAY_A); 
				}
			
				
	
				if($cart_row['donation'] != 1) {
					$all_donations = false;
				}
				/*if($cart_row['no_shipping'] != 1) {
					$shipping = $cart_row['pnp'] * $cart_row['quantity'];
					$total_shipping += $shipping;            
					$all_no_shipping = false;
				} else {
					$shipping = 0;
				}*/
				$price = $cart_row['price'] * $cart_row['quantity'];
				$gst = $price - ($price  / (1+($cart_row['gst'] / 100)));
				
				if($gst > 0) {
				  $tax_per_item = $gst / $cart_row['quantity'];
				}
				
				if($version<='3.7.8')
				{
				
					$sql = "SELECT meta_value FROM `".WPSC_TABLE_PRODUCTMETA."` AS `meta` WHERE `meta`.`product_id` = ".$cart_row['prodid']." and `meta_key`='custom_tax'";
					$product_meta = $wpdb->get_results($sql,ARRAY_A);
					$custom_tax= count($product_meta);
					if($custom_tax>=1)
					{
						$customtax_val = $product_meta[0]['meta_value'];
					}
					else
					{
						$customtax_val = 0;
					}
					$product_meta_data = array();
					
					
					
					$sql = "SELECT meta_value FROM `".WPSC_TABLE_PRODUCTMETA."` AS `meta` WHERE `meta`.`product_id` = ".$cart_row['prodid']." and `meta_key`='sku'";
					$product_meta_sku = $wpdb->get_results($sql,ARRAY_A);			
					$product_meta_data_sku = array();
				}
	
				$Item = new WG_Item();	
			
				if($version>'3.7.8')
				{
					$sku = get_post_meta($cart_row['prodid'], '_wpsc_sku', true);
					$product_name = $product_data[0]['post_title'];
					$description = $product_data[0]['post_excerpt'];
					//$weight = $product_data_1['meta']['_wpsc_product_metadata']['weight'];
					$product_data_1['meta'] = get_post_meta($cart_row['prodid'], '');
				foreach($product_data_1['meta'] as $meta_name => $meta_value) 
				{
						$product_data_1['meta'][$meta_name] = maybe_unserialize(array_pop($meta_value));
				}
				
				$product_data_1['transformed'] = array();
				if(!isset($product_data_1['meta']['_wpsc_product_metadata']['weight'])) $product_data_1['meta']['_wpsc_product_metadata']['weight'] = "";
				if(!isset($product_data_1['meta']['_wpsc_product_metadata']['weight_unit'])) $product_data_1['meta']['_wpsc_product_metadata']['weight_unit'] = "";
				
				//$product_data_1['transformed']['weight'] = wpsc_convert_weight($product_data_1['meta']['_wpsc_product_metadata']['weight'], "lbs", $product_data_1['meta']['_wpsc_product_metadata']['weight_unit']);
				//$weight = $product_data_1['transformed']['weight'];
				$weight = $product_data_1['meta']['_wpsc_product_metadata']['weight'];
				if($weight == ''){
					$weight = '0';
				}
				//print_r($product_data['meta']['_wpsc_product_metadata']);
				//continue;
				$unit = $product_data_1['meta']['_wpsc_product_metadata']['weight_unit'];
				switch($unit) {
					case "pound":
						$unit_s = " lbs.";
						break;
					case "ounce":
						$unit_s = " oz.";
						break;
					case "gram":
						$unit_s = " g";
						break;
					case "kilograms":
						$unit_s = " kgs.";
						break;
				}
					
				}
				else
				{
					$sku = $product_meta_sku[0]['meta_value'];
					$product_name = $product_data[0]['name'];
					$description = $product_data[0]['description'];
					$weight = $product_data[0]['weight'];
					
				}
				
				//$xmlResponse->createTag("ItemCode",       array(), $sku, $itemNode, __ENCODE_RESPONSE);
				$Item->setItemCode($sku);		
				//$xmlResponse->createTag("ItemDescription",array(), htmlentities($product_name, ENT_QUOTES), $itemNode, __ENCODE_RESPONSE);
				$desc=$product_name;
				if($product_name!='')		
				{
					//$xmlResponse->createTag("ItemShortDescr",array(), htmlentities($description), $itemNode, __ENCODE_RESPONSE);
					$Item->setItemDescription(stripslashes($desc));
					$Item->setItemShortDescr(stripslashes($desc));
				}
				else
				{
					//$xmlResponse->createTag("ItemShortDescr",array(), "", $itemNode, __ENCODE_RESPONSE);
					$Item->setItemDescription('');
					$Item->setItemShortDescr('');
				}
				
				$Item->setQuantity($cart_row['quantity']);
				$Item->setUnitPrice($cart_row['price']?$cart_row['price']:"0.00");
				$Item->setWeight((float)$weight);
				$Item->setFreeShipping($product_data[0]['no_shipping']);
				$Item->setDiscounted($discountval);
				$Item->setshippingFreight($cart_row['pnp']);
				$Item->setWeight_Symbol($weightsymbol);
				$Item->setWeight_Symbol_Grams($weight_symbol_grams);
				
				if($product_data[0]['notax']=='0') 
				{
					$taxexempt = 'N';
					$taxval = $cart_row['tax_charged'];
				}
				elseif($product_data[0]['notax']=='1')
				{
					$taxexempt = 'N';
					$taxval = $cart_row['tax_charged'];
				}
				elseif($product_data[0]['notax'] == 'n')
				{  
				
					$taxexempt = 'Y';
					//$taxval = "";
					$taxval = $cart_row['tax_charged'];
				}
				elseif($product_data[0]['notax'] == 'N')
				{ 
					$taxexempt = 'Y';
					//$taxval = "";
					$taxval = $cart_row['tax_charged'];
				}
				//$xmlResponse->createTag("TaxExempt",      array(), $taxexempt,        $itemNode, __ENCODE_RESPONSE);
				$Item->setTaxExempt($taxexempt);
				
				//$xmlResponse->createTag("OneTimeCharge",      array(), "",        $itemNode, __ENCODE_RESPONSE);
				$Item->setOneTimeCharge('');
				//$xmlResponse->createTag("ItemTaxAmount",      array(), $taxval, $itemNode, __ENCODE_RESPONSE); 
				$Item->setItemTaxAmount($taxval);
				
				//$iOptions = $xmlResponse->createTag("ItemOptions",      array(),  $variation_list,        $itemNode);		
				$responseArray['ItemOptions'] = array();
				
				$variation_sql = "SELECT * FROM `".WPSC_TABLE_CART_ITEM_VARIATIONS."` WHERE `cart_id`='".$cart_row['id']."'";
				
				$variation_data = $wpdb->get_results($variation_sql,ARRAY_A); 
				$variation_count = count($variation_data);
				
				if($variation_count >=1)
				{
					foreach($variation_data as $variation) 
					{
						$name = "SELECT * FROM `".WPSC_TABLE_PRODUCT_VARIATIONS."` WHERE `id`='".$variation['variation_id']."' LIMIT 1";
						$variation_name = $wpdb->get_results($name ,ARRAY_A);
						
						$option = "SELECT * FROM `".WPSC_TABLE_VARIATION_VALUES."` WHERE `id`='".$variation['value_id']."' LIMIT 1";
						$variation_options = $wpdb->get_results($option ,ARRAY_A);
							
						//$xmlResponse->createTag("ItemOption", array("Value"=> htmlentities($variation_options[0]['name']),"Name"=>htmlentities($variation_name[0]['name'])), "", $iOptions, __ENCODE_RESPONSE);
						$responseArray['ItemOptions'][$optionI]['Name'] = ($variation_name[0]['name']);
						$responseArray['ItemOptions'][$optionI]['Value'] = ($variation_options[0]['name']);
					  }
				  }
				
				 $custom_tax_val = 0.0;
				 ## code for custom tax
				 ##$custom_tax_val = (($cart_row['price']*$customtax_val)/100)."\n";
				 $custom_tax_val_tot +=  $custom_tax_val; 
				 unset($tax_calc);
				//$tax_calc = $cart_row['tax_charged']*$cart_row['quantity'];
				$tax_calc = $cart_row['tax_charged'];
				 $basetax += $tax_calc;
				//echo $order['id']."==".$tot_shipping = $total_shipping."\n";
				$shipping = $shipping+$cart_row['pnp'];
				
				$Order->setOrderItems($Item->getItem());		
			} // end items 
			$totaltax = $custom_tax_val_tot +  $basetax + $order['wpec_taxes_total'];
			
			//echo "<li>".$order['id']."===".$custom_tax_val_tot."*****".$basetax;
			$tot_shipping = $shippingval[0]+ $shipping;
			$charges =new WG_Charges();
			if($order['discount_value']!= '' && $order['discount_value'] != '0.00')
			{
				$charges->setDiscount($order['discount_value']?$order['discount_value']:"0.00");
			}
			else
			{
				$charges->setDiscount('0.00');
			}
			
			$charges->setStoreCredit('0.00');
			//$xmlResponse->createTag("Tax",      array(), $totaltax,				$chargesNode, __ENCODE_RESPONSE);
			$charges->setTax($totaltax?$totaltax:'0.00');
			unset($totaltax,$custom_tax_val, $custom_tax_val_tot,$tax_calc,$basetax);
			//$xmlResponse->createTag("Shipping", array(), $tot_shipping,   $chargesNode, __ENCODE_RESPONSE);
			$charges->setShipping($tot_shipping?$tot_shipping:'0.00');
			unset($tot_shipping, $prod_shipping,$shippingval[0],$shipping);
			//$xmlResponse->createTag("Total",    array(), $order['totalprice'],			$chargesNode, __ENCODE_RESPONSE);
			
			$charges->setTotal($order['totalprice']?$order['totalprice']:"0.00");
			
			$Order->setOrderChargeInfo($charges->getCharges());
					
			$Order->setShippedOn(date("m-d-Y",$order['date']));
			$Order->setShippedVia($carrier);
			$Orders->setOrders($Order->getOrder());
			$ordCountForRecordSentWg++;
			}
			  
		}
		$Orders->setTotalRecordSent($ordCountForRecordSentWg);
		return $this->response($Orders->getOrders());
	}
	
	
	
	function OrderUpdateStatus($username,$password,$orderid,$current_order_status,$order_status,$order_notes,$storeid=1,$emailAlert='N') {
		
		$OrderId			=	trim($orderid); 
		$CurrentOrderStatus	=	trim($current_order_status);
		$NewOrderStatus		=	trim($order_status); 
		$OrderNotes			=	trim($order_notes);
	
		
		
		global $sql_tbl, $config, $mail_smarty, $wpdb;
		
		#check for authorisation
		$status = $this->auth_user($username,$password);
		if($status!='0')
		{ 
		  return $status;
		}
		$version = $this->getVersion();
	
		$Orders = new WG_Orders();		
		if(!isset($OrderId)) {
			$Orders->setStatusCode("9997");
			$Orders->setStatusMessage("Unknown request or request not in proper format");	
			return $this->response($Orders->getOrders());
		}
		
		$update_notes_flag	=	false;
		$update_status_flag	=	false;
		$check_status_change	=	true;
		
		
		$Orders->setStatusCode("0");
		
		if($NewOrderStatus == '') {
			$NewOrderStatus		=	$CurrentOrderStatus;
			$check_status_change	=	false;
		} /*else if($OrderNotes == '') {
			$OrderNotes		=	'Change status only';
			$check_status_change	=	false;
		}*/

			$_REQUEST['purchaselog_id'] = $OrderId;
			
			if(isset($_REQUEST['purchaselog_id'])){
	
				$purchlogitem = new wpsc_purchaselogs_items((int)$_REQUEST['purchaselog_id']);
				$purchlogs = new wpsc_purchaselogs((int)$_REQUEST['purchaselog_id']);
			}
			
			$Note1 = $purchlogitem->customcheckoutfields;
			foreach($Note1 as $note)
			{
				$note_val = $note['value'];
			}
	
		# Check for record Existence for the Order ID
			$query = "SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` where id = '".$OrderId."'";
			$orderdata = $wpdb->get_results($query,ARRAY_A);
			
			if($orderdata)
			{ 
				
				if((isset($note_val) && $note_val!=""))
				{
					//$info .=" \n".$note_vat." ".$order['ORDERNOTES'];
					$info =$note_val;
					$info .= "\nOrder shipped ";
				}
				else
				{
					$info = "\nOrder shipped ";
				}

				if((!isset($note_val) && $note_val==""))
				{
					if ($OrderNotes!="")
					{	
						$info .=" \n".$OrderNotes;
						
					}
				}
				

				$logs = new wpsc_purchaselogs();
				$orderStatus = $logs->the_purch_item_statuses();
				foreach($orderStatus as $status_name)
				{ 
					
					if($status_name['label'] == $NewOrderStatus)
					{
						$status = $status_name['order'];
					}
					else
					{
						continue;
					}
				}
				# check if notes field exist in database and update accordingly
		
				$result_tables = mysql_query("SELECT * FROM ".WPSC_TABLE_PURCHASE_LOGS);
				$fields = mysql_num_fields($result_tables);
				$rows   = mysql_num_rows($result_tables);	
				unset($field_array);
				for ($j=0; $j < $fields; $j++) 
				{
					$field_array[] = mysql_field_name($result_tables, $j);
				}
			
				$sessionid = $orderdata[0]['sessionid'];
				if($NewOrderStatus != '' && $check_status_change) {
				
					$updateQuery1 = "UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET processed  = $status WHERE id = '".$OrderId."'";
					$wpdb->query($updateQuery1);
					$update_status_flag	=	true;
					
				}
				
				//transaction_results_new($sessionid,$status);
				//transaction_results($sessionid, $echo_to_screen = true, $transaction_id = null) 
				
				if (in_array('notes', $field_array)) { 
					
					if($OrderNotes != '') {
						$updateQuery = "UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET processed  = '".htmlentities($status)."', notes = '".htmlentities($info)."' WHERE id = '".$OrderId."'";	
						$wpdb->query($updateQuery);
						$update_notes_flag	=	true;
					}
		
					
				} else { 
					 echo $updateQuery = "UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET processed  = '".htmlentities($status)."' WHERE id = '".$OrderId."'";
					 $wpdb->query($updateQuery);
					 $update_status_flag	=	true;
				}
				$result = 'Success';
				if($order['IsNotifyCustomer']=='Y' && $result == 'Success') {
					transaction_results($sessionid, $echo_to_screen = true, $transaction_id = null) ;
				}
				
			} else {
				$result = 'Order not found';
			}
	

		if($update_notes_flag && $update_status_flag) {
			$StatusMessage	=	"Order updated successfully";	
		} elseif($update_notes_flag) {
			$StatusMessage	=	"Order notes updated successfully";	
		} elseif($update_status_flag) {
			$StatusMessage	=	"Order status updated successfully";	
		} else {
			$StatusMessage	=	"Error in update order";
		}
		$Orders->setStatusMessage($StatusMessage);	
	
		return $this->response($Orders->getOrderResponse());
	
	
	}
	
	###########################################################################
	#
	# General utility functions
	#
	
	# function to escape html entity characters
	function parseSpecCharsA($arr){
	   foreach($arr as $k=>$v){
		 //$arr[$k] = htmlspecialchars($v, ENT_NOQUOTES);
			 $arr[$k] = addslashes(htmlentities($v, ENT_QUOTES));
	   }
	   return $arr;
	}
	
	
} // Class end




if(isset($_REQUEST['request'])) {
	$wpObject = new Webgility_Ecc_WP();
	$wpObject->parseRequest();
}	
?>