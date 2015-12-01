<?php
//require_once('../../../wp-load.php');
/**
 * Require the utilities class
 */

$options = $_POST['sod_qbpos_webconnector'];
if(isset($options) && !empty($options)){
	require_once($options['plugin_dir'].'../QuickBooks.php');
	$characters 			= 'ABCD0123456789';
	$string 				= '';
	$random_string_length 	= 8;
 	for ($i = 0; $i < $random_string_length; $i++) {
      	$string .= $characters[rand(0, strlen($characters) - 1)];
 	}
	$ssl_path 				= $options['ssl'];
	$name 					= 'WooCommerce QuickBooksPOS Connector';				// A name for your server (make it whatever you want)
	$descrip				= "QuickBooksPOS Connector for Woocommerce."; 		// A description of your server
	$api_key 				= $options['key'];
	$appid 					= '57F3B9B6';
	$appurl 				= $ssl_path."/?qbposconnector=".$api_key;//plugins_url('sod-qb-qwc.php', __FILE__);	// This *must* be httpS:// (path to your QuickBooks SOAP server)
	$appsupport 			= $ssl_path."/?qbposconnector=support"; 		// This *must* be httpS:// and the domain name must match the domain name above
	$username 				= $options['username'];		// This is the username you stored in the 'quickbooks_user' table by using QuickBooks_Utilities::createUser()
	$fileid 				= $string . '-86F1-4FCC-B1FF-966DE1813A33';		// Just make this up, but make sure it keeps that format
	$ownerid 				= $string . '-86F1-4FCC-B1FF-166DE1813A33';		// Just make this up, but make sure it keeps that format
	$qbtype 				= QUICKBOOKS_TYPE_QBPOS;	// You can leave this as-is unless you're using QuickBooks POS
	$readonly 				= false; // No, we want to write data to QuickBooks
	$run_every_n_seconds 	= null;
	// Generate the XML file
	$QWC = new QuickBooks_WebConnector_QWC(
		$name, 
		$descrip, 
		$appurl, 
		$appsupport, 
		$username, 
		$fileid, 
		$ownerid, 
		$qbtype,
		$readonly = false, 
		$run_every_n_seconds = null, 
		$personaldata = QuickBooks_WebConnector_QWC::PERSONALDATA_DEFAULT, 
		$unattendedmode = QuickBooks_WebConnector_QWC::UNATTENDEDMODE_DEFAULT, 
		$authflags = QuickBooks_WebConnector_QWC::SUPPORTED_DEFAULT, 
		$notify = false, 
		$appdisplayname = '', 
		$appuniquename = '', 
		$appid 
	);
	$xml = $QWC->generate();
	// Send as a file download
	header('Content-type: text/xml');
	header('Content-Disposition: attachment; filename="quickbookspos-connector.qwc"');
	print($xml);
	exit;
}