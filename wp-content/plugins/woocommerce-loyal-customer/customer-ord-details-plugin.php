<?php
/**
 * Plugin Name: WooCommerce Loyal Customer
 * Plugin URI: http://www.codenterprise.com/corporate-profile/wordpress-plugin/
 * Description: Woo Commerce Loyal Customer provides wp-admin a way to view the total number of orders received per registered customer in a very user friendly manner with the help of color codes.
 * Version: 1.4.1
 * Author: codenterprise
 * Author URI: http://www.codenterprise.com/
 * License: GPL2
 */

/*
|--------------------------------------------------------------------------
| APPLY ACTIONS & FILTERS IS WOOCOMMERCE IS ACTIVE
|--------------------------------------------------------------------------
*/
/* woocommerce dependency check */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	/********** action to menu  *********/
	add_action('admin_menu', 'woo_shop_order_details_menu', 10);
}


/*
|--------------------------------------------------------------------------
| START PLUGIN FUNCTIONS
|--------------------------------------------------------------------------
*/
/* Adding Submenu page in woocommerce meun */
function woo_shop_order_details_menu() { 
	add_submenu_page( 'woocommerce', __('WLC', ''), __('WLC', ''), 'manage_options', 'cust-orders','list_ord_page');
}
/* including main plugin file */
function list_ord_page() {
	 include('admin/list_cus_ord_k.php');
}
?>
