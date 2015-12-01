<?php
/**
 * Add to Cart Restriction Interface.
 *
 * @version 1.0.0
 * @author 	SomewhereWarm
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface WC_CSP_Add_To_Cart_Restriction {

	/**
	 * Validation running on the 'woocommerce_add_to_cart_validation' hook.
	 *
	 * @param  bool   $add
	 * @param  mixed  $product_id
	 * @param  mixed  $product_quantity
	 * @param  string $variation_id
	 * @param  array  $variations
	 * @param  array  $cart_item_data
	 * @return void
	 */
	public function validate_add_to_cart( $add, $product_id, $product_quantity, $variation_id = '', $variations = array(), $cart_item_data = array() );

}
