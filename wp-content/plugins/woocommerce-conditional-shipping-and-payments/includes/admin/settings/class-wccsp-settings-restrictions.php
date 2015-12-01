<?php
/**
 * WooCommerce Restriction Settings
 *
 * @author      SomewhereWarm
 * @version     1.1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Settings_Restrictions' ) ) :

/**
 * WC_Settings_Restrictions
 */
class WC_Settings_Restrictions extends WC_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->id    = 'restrictions';
		$this->label = __( 'Restrictions', WC_Conditional_Shipping_Payments::TEXT_DOMAIN );

		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'woocommerce_sections_' . $this->id, array( $this, 'output_sections' ) );
		add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
	}

	/**
	 * Get sections.
	 *
	 * @return array
	 */
	public function get_sections() {

		$restrictions = WC_CSP()->restrictions->get_restrictions();

		$sections = array(
			'' => __( 'Restrictions', 'woocommerce' )
		);

		foreach ( $restrictions as $restriction_id => $restriction ) {
			if ( $restriction->has_admin_global_fields() ) {
				$sections[ $restriction_id ] = esc_html( $restriction->get_title() );
			}
		}

		return apply_filters( 'woocommerce_csp_get_sections_' . $this->id, $sections );
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {

		return apply_filters( 'woocommerce_csp_settings', array(

			array(
				'title' => __( 'Restrictions', WC_Conditional_Shipping_Payments::TEXT_DOMAIN ),
				'type'  => 'title',
				'desc'  => __( 'To view, create and modify restriction rules, click on any section above. Use the following options to temporarily disable all defined Restrictions, for example if you need to troubleshoot your payment and shipping settings.', WC_Conditional_Shipping_Payments::TEXT_DOMAIN ),
				'id'    => 'restriction_options'
			),

			array(
				'title'         => __( 'Disable Global Restrictions', WC_Conditional_Shipping_Payments::TEXT_DOMAIN ),
				'desc'          => __( 'Disable all global restrictions', WC_Conditional_Shipping_Payments::TEXT_DOMAIN ),
				'id'            => 'wccsp_restrictions_disable_global',
				'default'       => 'no',
				'type'          => 'checkbox',
				'checkboxgroup' => 'start',
				'desc_tip'      => __( 'Disable all restrictions created in the tab sections above.', WC_Conditional_Shipping_Payments::TEXT_DOMAIN ),
			),

			array(
				'title'         => __( 'Disable Product Restrictions', WC_Conditional_Shipping_Payments::TEXT_DOMAIN ),
				'desc'          => __( 'Disable all restrictions defined at product level', WC_Conditional_Shipping_Payments::TEXT_DOMAIN ),
				'id'            => 'wccsp_restrictions_disable_product',
				'default'       => 'no',
				'type'          => 'checkbox',
				'checkboxgroup' => 'start',
				'desc_tip'      => __( 'Disable all restrictions created from the <strong>Product Data > Restrictions</strong> tab in product pages. Product level restrictions are associated with specific products.', WC_Conditional_Shipping_Payments::TEXT_DOMAIN ),
			),

			array( 'type' => 'sectionend', 'id' => 'global_restriction_options' ),

		) );
	}

	/**
	 * Output the settings.
	 */
	public function output() {

		global $current_section;

		// Define restrictions that can be customised here

		if ( $current_section ) {

			$restriction = WC_CSP()->restrictions->get_restriction( $current_section );

			if ( $restriction ) {
				$restriction->admin_options();
			}

		} else {

			$settings = $this->get_settings();

			WC_Admin_Settings::output_fields( $settings );
		}
	}

	/**
	 * Save settings.
	 */
	public function save() {

		global $current_section, $wpdb;

		if ( ! $current_section ) {

			$settings = $this->get_settings();
			WC_Admin_Settings::save_fields( $settings );

		} else {

			do_action( 'woocommerce_update_options_' . $this->id . '_' . $current_section );
		}

		// WC 2.2 bug?
		$wpdb->query( "DELETE FROM `$wpdb->options` WHERE `option_name` LIKE ('\_transient\_wc\_ship\_%') OR `option_name` LIKE ('\_transient\_timeout\_wc\_ship\_%')" );
	}
}

endif;

return new WC_Settings_Restrictions();
