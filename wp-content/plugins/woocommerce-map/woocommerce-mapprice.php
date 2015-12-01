<?php
/*
Plugin Name: Woocommerce M.A.P. (Minimum Advertised Price)
Plugin URI: http://www.advancedstyle.com/
Description: Hide prices that are lower than the M.A.P.
Author: David Barnes
Version: 1.0
Author URI: http://www.advancedstyle.com/
*/

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	function woocommerce_map_pricing_input(){
			// Price
			woocommerce_wp_text_input( array( 'id' => '_map_price', 'class' => 'wc_input_price short', 'label' => __( 'M.A.P. Price', 'woocommerce' ) . ' ('.get_woocommerce_currency_symbol().')', 'type' => 'number', 'custom_attributes' => array(
				'step' 	=> 'any',
				'min'	=> '0'
			) ) );
	}
	add_action('woocommerce_product_options_pricing','woocommerce_map_pricing_input');
	
	function woocommerce_process_map_pricing_variable($post_id){
		if((int)$post_id > 0 && isset($_POST['_map_price'])){
			update_post_meta( $post_id, '_map_price', $_POST['_map_price']);
		}
	}
	
	add_action( 'save_post', 'woocommerce_process_map_pricing_variable' );
	
	function woocommerce_get_map_price_html($price, $obj){
		$p = preg_replace('#[^0-9.]*#','',html_entity_decode(strip_tags($price)));
		$map = get_post_meta($obj->id,'_map_price',true);
		if($map != '' && $map > 0 && $p < $map){
			$map_price = 'M.A.P. '.woocommerce_price($map);
			if(is_single()){
				$map_price .= '<br><a href="#ourprice" class="inline show_review_form">Click to View Our Price</a><div style="display:none;"><div id="ourprice"><h3 id="reply-title">'.$obj->post->post_title.'</h3><p><strong>Our Price:</strong> '.$price.'</p></div></div>';
			}
			return $map_price;
		}
		return $price;
	}
	
	add_filter('woocommerce_get_price_html','woocommerce_get_map_price_html',10,2);
}
?>