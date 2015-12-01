<?php
/**
 * WooCommerce POS General Settings
 *
 * @author    Actuality Extensions
 * @package   WoocommercePointOfSale/Classes/settings
 * @category	Class
 * @since     0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_POS_Settings_General' ) ) :

/**
 * WC_POS_Settings_General
 */
class WC_POS_Settings_General extends WC_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'general_pos';
		$this->label = __( 'General', 'woocommerce' );

		add_filter( 'wc_pos_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'wc_pos_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'wc_pos_settings_save_' . $this->id, array( $this, 'save' ) );

	}

	/**
	 * Get settings array
	 *
	 * @return array
	 */
	public function get_settings() {
		global $woocommerce;

		$order_statuses = wc_get_order_statuses();
		$statuses['processing'] = $order_statuses['wc-processing'];
		$statuses['completed'] = $order_statuses['wc-completed'];
		return apply_filters( 'woocommerce_point_of_sale_general_settings_fields', array(

			array( 'title' => __( 'General Options', 'wc_point_of_sale' ), 'type' => 'title', 'desc' => '', 'id' => 'general_pos_options' ),

			array(
					'name' => __( 'Ready To Scan', 'wc_point_of_sale' ),
					'id'   => 'woocommerce_pos_register_ready_to_scan',
					'std'  => '',
					'type' => 'checkbox',
					'desc' => __( 'Check this box if you\'d like the register to always be ready for barcode scanning.', 'wc_point_of_sale' ),
					'checkboxgroup'	=> 'start',
					'default'	=> 'no',
					'autoload'  => false					
				),

			array(
					'name' => __( 'Credit Card Scanning', 'wc_point_of_sale' ),
					'id'   => 'woocommerce_pos_register_cc_scanning',
					'std'  => '',
					'type' => 'checkbox',
					'desc' => __( 'Check this box if you would like to enable credit card scanning. Currently supported on RealEx, Stripe and Braintree.', 'wc_point_of_sale' ),
					'checkboxgroup'	=> 'start',
					'default'	=> 'no',
					'autoload'  => false					
				),

			array(
					'name'    => __( 'Discount Presets', 'wc_point_of_sale' ),
					'desc'    => '',
					'id'      => 'woocommerce_pos_register_discount_presets',
					'css'     => '',
					'std'     => '',
					'type'    => 'multiselect',
					'options' => apply_filters('woocommerce_pos_register_discount_presets', array(
						5 => __( '5%', 'wc_point_of_sale' ),
						10 => __( '10%', 'wc_point_of_sale' ),
						15 => __( '15%', 'wc_point_of_sale' ),
						20 => __( '20%', 'wc_point_of_sale' ),
						25 => __( '25%', 'wc_point_of_sale' ),
						30 => __( '30%', 'wc_point_of_sale' ),
						35 => __( '35%', 'wc_point_of_sale' ),
						40 => __( '40%', 'wc_point_of_sale' ),
						45 => __( '45%', 'wc_point_of_sale' ),
						50 => __( '50%', 'wc_point_of_sale' ),
						55 => __( '55%', 'wc_point_of_sale' ),
						60 => __( '60%', 'wc_point_of_sale' ),
						65 => __( '65%', 'wc_point_of_sale' ),
						70 => __( '70%', 'wc_point_of_sale' ),
						75 => __( '75%', 'wc_point_of_sale' ),
						80 => __( '80%', 'wc_point_of_sale' ),
						85 => __( '85%', 'wc_point_of_sale' ),
						90 => __( '90%', 'wc_point_of_sale' ),
						95 => __( '95%', 'wc_point_of_sale' ),
						100 => __( '100%', 'wc_point_of_sale' )
						)),
					'default' => array(5, 10, 15, 20),
				),

			array(
					'name'    => __( 'Order Status', 'woocommerce' ),
					'desc'    => 'Choose what the order status is when the order is completed using the Point of Sale.',
					'id'      => 'woocommerce_pos_end_of_sale_order_status',
					'css'     => '',
					'std'     => '',
					'type'    => 'select',
					'options' => apply_filters('woocommerce_pos_end_of_sale_order_status', $statuses),
					'default' => 'processing'
				),

			array( 'type' => 'sectionend', 'id' => 'general_pos_options'),

		) ); // End general settings

	}

	/**
	 * Save settings
	 */
	public function save() {
		$settings = $this->get_settings();

		WC_Pos_Settings::save_fields( $settings );
	}

}

endif;

return new WC_POS_Settings_General();
