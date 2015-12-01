<?php
/**
 * WoocommercePointOfSale Registers Order Class
 *
 * @author    Actuality Extensions
 * @package   WoocommercePointOfSale/Classes/Registers
 * @category	Class
 * @since     0.1
 */


if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WC_Pos_Registers_Orders {
  /** @var array Array of posted form data. */
  public $posted;

  /** @var array Array of fields to display on the checkout. */
  public $checkout_fields;

  /** @var bool Whether or not the user must create an account to checkout. */
  public $must_create_account;

  /** @var object The shipping method being used. */
  private $shipping_method;

  /** @var WC_Payment_Gateway The payment gateway being used. */
  private $payment_method;

  /** @var int ID of customer. */
  private $customer_id;

  /** @var int ID of order. */
  private $order_id;


  private $old_calc_shipping;


  private $cart;
  private $customer;
  private $chosen_shipping_methods;
  private $wc_tax_based_on;
  private $wc_default_country;


  /**
   * Constructor for the checkout class. Hooks in methods and defines checkout fields.
   *
   * @access public
   * @return void
   */
  public function __construct ($order_id, $customer_id) {
    if (!$order_id ) {
      $order_id = WC_POS()->register()->crate_order_id($_POST['id_register']);
    }
    include_once( dirname(WC_PLUGIN_FILE).'/includes/wc-notice-functions.php' );
    $this->order_id                  = $order_id;
    $this->customer_id               = $customer_id;
    $this->enable_signup             = false;
    $this->enable_guest_checkout     = true;
    $this->must_create_account       = false;
    $this->payment_method            = false;
    $this->needs_payment             = isset( $_POST['payment_method'] ) ? true : false;
    $this->order_awaiting_payment    = 0;
    $this->old_calc_shipping         = get_option('woocommerce_calc_shipping');

    $this->wc_calc_taxes             = get_option('woocommerce_calc_taxes', 'no');
    $this->wc_pos_tax_calculation    = get_option('woocommerce_pos_tax_calculation', 'disabled');

    if($this->wc_pos_tax_calculation != 'enabled'){
      update_option('woocommerce_calc_taxes', 'no');
    }

    include_once( dirname(WC_PLUGIN_FILE).'/includes/abstracts/abstract-wc-session.php' );
    $session_class                   = apply_filters( 'woocommerce_session_handler', 'WC_Session_Handler' );
    WC()->session                    = new $session_class();
    WC()->cart                       = new WC_Cart();
    WC()->customer                   = new WC_Customer();


    // Define all Checkout fields
    $this->checkout_fields['billing']   = WC()->countries->get_address_fields( $this->get_value('billing_country'), 'billing_' );
    $this->checkout_fields['shipping']  = WC()->countries->get_address_fields( $this->get_value('shipping_country'), 'shipping_' );

    $this->checkout_fields['order'] = array(
      'order_comments' => array(
        'type' => 'textarea',
        'class' => array('notes'),
        'label' => __( 'Order Notes', 'woocommerce' ),
        'placeholder' => _x('Notes about your order, e.g. special notes for delivery.', 'placeholder', 'woocommerce')
        )
      );

    $this->checkout_fields = apply_filters( 'woocommerce_checkout_fields', $this->checkout_fields );

    // Init save
    $this->save_new_order();

    $this->process_create_order();

    $outlet   = $_GET['outlet'];
    $register = $_GET['reg'];
    $register_url = get_home_url()."/point-of-sale/$outlet/$register";
        
    if($this->wc_pos_tax_calculation != 'enabled'){
      update_option('woocommerce_calc_taxes', $this->wc_calc_taxes);
    }
     wp_safe_redirect($register_url);
  }

  public function check_cart_items() {
    // When we process the checkout, lets ensure cart items are rechecked to prevent checkout
    WC()->cart->check_cart_items();
  }

  /**
   * create_order function.
   * @access public
   * @throws Exception
   * @return int
   */
  public function create_order() {
    global $wpdb;

    // Give plugins the opportunity to create an order themselves
    $order_id = $this->order_id;

    if ( !is_numeric( $order_id ) )
      return 'failed';

    // Create Order (send cart variable so we can record items and reduce inventory). Only create if this is a new order, not if the payment was rejected.
    $order_data = apply_filters( 'woocommerce_new_order_data', array(
      'ID'   => $order_id,
      'post_type'   => 'shop_order',
      'post_date'   => current_time( 'mysql' ),
      'post_title'  => sprintf( __( 'Order &ndash; %s', 'woocommerce' ), strftime( _x( '%b %d, %Y @ %I:%M %p', 'Order date parsed by strftime', 'woocommerce' ) ) ),
      'ping_status'   => 'closed',
      'post_excerpt'  => isset( $this->posted['order_comments'] ) ? $this->posted['order_comments'] : '',
      'post_author'   => get_current_user_id(),
      'post_password' => uniqid( 'order_' ) // Protects the post just in case
    ) );



    $create_new_order = false;
    /* Check order is unpaid by getting its status */
    if(   defined( 'WC_VERSION') && floatval(WC_VERSION) < 2.2 ){
      $terms        = wp_get_object_terms( $order_id, 'shop_order_status', array( 'fields' => 'slugs' ) );
      $order_status = isset( $terms[0] ) ? $terms[0] : 'pending';
      
      // Resume the unpaid order if its pending
      if ( get_post( $order_id ) && ( $order_status == 'pending' || $order_status == 'failed' ) ) {

        // Update the existing order as we are resuming it

        wp_update_post( $order_data );

        // Clear the old line items - we'll add these again in case they changed
        $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}woocommerce_order_itemmeta WHERE order_item_id IN ( SELECT order_item_id FROM {$wpdb->prefix}woocommerce_order_items WHERE order_id = %d )", $order_id ) );

        $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}woocommerce_order_items WHERE order_id = %d", $order_id ) );

        // Trigger an action for the resumed order
        do_action( 'woocommerce_resume_order', $order_id );
      }
      if ( $order_status == 'pending' ) {
        wp_set_object_terms( $order_id, 'pending', 'shop_order_status' );
      }
    }else{
        // Update the existing order as we are resuming it
        $order_data['post_status'] = 'pending';
        wp_update_post( $order_data );

        // Clear the old line items - we'll add these again in case they changed
        $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}woocommerce_order_itemmeta WHERE order_item_id IN ( SELECT order_item_id FROM {$wpdb->prefix}woocommerce_order_items WHERE order_id = %d )", $order_id ) );

        $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}woocommerce_order_items WHERE order_id = %d", $order_id ) );

        // Trigger an action for the resumed order
        do_action( 'woocommerce_resume_order', $order_id );
    }

    add_post_meta($order_id, 'wc_pos_order_type', 'POS', true);
    add_post_meta($order_id, 'wc_pos_id_register', $_POST['id_register'], true);
      
      

    // Store user data
    if ( $this->checkout_fields['billing'] )
      foreach ( $this->checkout_fields['billing'] as $key => $field ) {
        update_post_meta( $order_id, '_' . $key, $this->posted[ $key ] );
      }

    if ( $this->checkout_fields['shipping'] &&  true ) {
      foreach ( $this->checkout_fields['shipping'] as $key => $field ) {
        $postvalue = false;

        if ( $this->posted['ship_to_different_address'] == false ) {
          if ( isset( $this->posted[ str_replace( 'shipping_', 'billing_', $key ) ] ) ) {
            $postvalue = $this->posted[ str_replace( 'shipping_', 'billing_', $key ) ];
            update_post_meta( $order_id, '_' . $key, $postvalue );
          }
        } else {
          if ( isset( $this->posted[ $key ] ) ) {
            $postvalue = $this->posted[ $key ];
            update_post_meta( $order_id, '_' . $key, $postvalue );
          }
          
        }
      }
    }
    if ( $this->checkout_fields['shipping'] && WC()->cart->needs_shipping() ) {
      foreach ( $this->checkout_fields['shipping'] as $key => $field ) {
        $postvalue = false;

        if ( $this->posted['ship_to_different_address'] == false ) {
          if ( isset( $this->posted[ str_replace( 'shipping_', 'billing_', $key ) ] ) ) {
            $postvalue = $this->posted[ str_replace( 'shipping_', 'billing_', $key ) ];
            update_post_meta( $order_id, '_' . $key, $postvalue );
          }
        } else {
          $postvalue = $this->posted[ $key ];
          update_post_meta( $order_id, '_' . $key, $postvalue );
        }
      }
    }


    // Store the line items to the new/resumed order
    foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
      $_product = $values['data'];

      $variation_data = isset($_product->variation_data) ? $_product->variation_data : '';
            // Add line item
            $item_id = wc_add_order_item( $order_id, array(
        'order_item_name'     => $_product->get_title(),
        'order_item_type'     => 'line_item'
      ) );

      // Add line item meta
      if ( $item_id ) {
        wc_add_order_item_meta( $item_id, '_qty', apply_filters( 'woocommerce_stock_amount', $values['quantity'] ) );
        wc_add_order_item_meta( $item_id, '_tax_class', $_product->get_tax_class() );
        wc_add_order_item_meta( $item_id, '_product_id', $values['product_id'] );
        wc_add_order_item_meta( $item_id, '_variation_id', $values['variation_id'] );
        wc_add_order_item_meta( $item_id, '_line_subtotal', wc_format_decimal( $values['line_subtotal'] ) );
        wc_add_order_item_meta( $item_id, '_line_total', wc_format_decimal( $values['line_total'] ) );
        wc_add_order_item_meta( $item_id, '_line_tax', wc_format_decimal( $values['line_tax'] ) );
        wc_add_order_item_meta( $item_id, '_line_subtotal_tax', wc_format_decimal( $values['line_subtotal_tax'] ) );

        // Store variation data in meta so admin can view it
        if ( $variation_data && is_array( $variation_data ) ) {
          foreach ( $variation_data as $key => $value ) {
            $key = str_replace( 'attribute_', '', $key );
            wc_add_order_item_meta( $item_id, $key, $value );
          }
        }

        // Add line item meta for backorder status
        if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $values['quantity'] ) ) {
          wc_add_order_item_meta( $item_id, apply_filters( 'woocommerce_backordered_item_meta_name', __( 'Backordered', 'woocommerce' ), $cart_item_key, $order_id ), $values['quantity'] - max( 0, $_product->get_total_stock() ) );
        }

        // Allow plugins to add order item meta
        do_action( 'woocommerce_add_order_item_meta', $item_id, $values, $cart_item_key );
      }
    }

    // Store fees
    foreach ( WC()->cart->get_fees() as $fee_key => $fee ) {
      $item_id = wc_add_order_item( $order_id, array(
        'order_item_name'     => $fee->name,
        'order_item_type'     => 'fee'
      ) );

      if ( $fee->taxable )
        wc_add_order_item_meta( $item_id, '_tax_class', $fee->tax_class );
      else
        wc_add_order_item_meta( $item_id, '_tax_class', '0' );

      wc_add_order_item_meta( $item_id, '_line_total', wc_format_decimal( $fee->amount ) );
      wc_add_order_item_meta( $item_id, '_line_tax', wc_format_decimal( $fee->tax ) );

      // Allow plugins to add order item meta to fees
      do_action( 'woocommerce_add_order_fee_meta', $order_id, $item_id, $fee, $fee_key );
    }
    // Store shipping for all packages
    $packages = WC()->shipping->get_packages();

    foreach ( $packages as $i => $package ) {
      if ( isset( $package['rates'][ $this->shipping_methods[ $i ] ] ) ) {

        $method = $package['rates'][ $this->shipping_methods[ $i ] ];

        $item_id = wc_add_order_item( $order_id, array(
          'order_item_name'     => $method->label,
          'order_item_type'     => 'shipping'
        ) );

        if ( $item_id ) {
          wc_add_order_item_meta( $item_id, 'method_id', $method->id );
          wc_add_order_item_meta( $item_id, 'cost', wc_format_decimal( $method->cost ) );
          do_action( 'woocommerce_add_shipping_order_item', $order_id, $item_id, $i );
        }
      }
    }
    // Store tax rows
    foreach ( array_keys( WC()->cart->taxes + WC()->cart->shipping_taxes ) as $key ) {
      $code = WC()->cart->tax->get_rate_code( $key );

      if ( $code ) {
        $item_id = wc_add_order_item( $order_id, array(
          'order_item_name'     => $code,
          'order_item_type'     => 'tax'
        ) );

        // Add line item meta
        if ( $item_id ) {
          wc_add_order_item_meta( $item_id, 'rate_id', $key );
          wc_add_order_item_meta( $item_id, 'label', WC()->cart->tax->get_rate_label( $key ) );
          wc_add_order_item_meta( $item_id, 'compound', absint( WC()->cart->tax->is_compound( $key ) ? 1 : 0 ) );
          wc_add_order_item_meta( $item_id, 'tax_amount', wc_format_decimal( isset( WC()->cart->taxes[ $key ] ) ? WC()->cart->taxes[ $key ] : 0 ) );
          wc_add_order_item_meta( $item_id, 'shipping_tax_amount', wc_format_decimal( isset( WC()->cart->shipping_taxes[ $key ] ) ? WC()->cart->shipping_taxes[ $key ] : 0 ) );
        }
      }
    }

    // Store coupons
    if ( $applied_coupons = WC()->cart->get_coupons() ) {
      foreach ( $applied_coupons as $code => $coupon ) {

        $item_id = wc_add_order_item( $order_id, array(
          'order_item_name'     => $code,
          'order_item_type'     => 'coupon'
        ) );

        // Add line item meta
        if ( $item_id ) {
          wc_add_order_item_meta( $item_id, 'discount_amount', isset( WC()->cart->coupon_discount_amounts[ $code ] ) ? WC()->cart->coupon_discount_amounts[ $code ] : 0 );
        }
      }
    }


    if ( $this->payment_method ) {
      update_post_meta( $order_id, '_payment_method',     $this->payment_method->id );
      update_post_meta( $order_id, '_payment_method_title',   $this->payment_method->get_title() );
    }

    $user_info = get_userdata($this->customer_id);
    update_post_meta( $order_id, '_billing_email', $this->posted['billing_email'] );

    $discount         = isset( $_POST['order_discount'] ) ? $_POST['order_discount'] : 0;

    update_post_meta( $order_id, '_order_shipping',     wc_format_decimal( WC()->cart->shipping_total ) );
    update_post_meta( $order_id, '_order_discount',     wc_format_decimal( WC()->cart->get_order_discount_total()+$discount ) );
    update_post_meta( $order_id, '_cart_discount',      wc_format_decimal( WC()->cart->get_cart_discount_total() ) );
    update_post_meta( $order_id, '_order_tax',        wc_format_decimal( WC()->cart->tax_total ) );
    update_post_meta( $order_id, '_order_shipping_tax',   wc_format_decimal( WC()->cart->shipping_tax_total ) );
    update_post_meta( $order_id, '_order_total',      wc_format_decimal( WC()->cart->total-$discount, get_option( 'woocommerce_price_num_decimals' ) ) );


    //die( wc_format_decimal( WC()->cart->total, get_option( 'woocommerce_price_num_decimals' ) )   );

    update_post_meta( $order_id, '_order_key',        'wc_' . apply_filters('woocommerce_generate_order_key', uniqid('order_') ) );
    update_post_meta( $order_id, '_customer_user',      absint( $this->customer_id ) );
    update_post_meta( $order_id, '_order_currency',     get_woocommerce_currency() );
    update_post_meta( $order_id, '_prices_include_tax',   get_option( 'woocommerce_prices_include_tax' ) );
    update_post_meta( $order_id, '_customer_ip_address',  isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'] );
    update_post_meta( $order_id, '_customer_user_agent',  isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '' );


    /// Let plugins add meta
    do_action( 'woocommerce_checkout_update_order_meta', $order_id, $this->posted );

    // Order status
    //wp_set_object_terms( $order_id, 'pending', 'shop_order_status' );

    return $order_id;
  }

  /**
     * Process the payment and return the result
     *
     * @param int $order_id
     * @return array
     */
    public function process_payment( $order_id ) {

      $order = new WC_Order( $order_id );

      // Mark as on-hold (we're awaiting the payment)
      $order->update_status( 'processing');

      // Reduce stock levels
      $order->reduce_order_stock();

      // Remove cart
      WC()->cart->empty_cart();

      // Return thankyou redirect
      return array(
        'result'  => 'success'
      );
    }

  /**
   *
   * @access public
   * @return void
   */
  public function process_create_order() {
    global $wpdb, $current_user;

    if ( ! defined( 'WOOCOMMERCE_CHECKOUT' ) )
      define( 'WOOCOMMERCE_CHECKOUT', true );

    // Prevent timeout
    @set_time_limit(0);

    do_action( 'woocommerce_before_checkout_process' );

    if ( sizeof( WC()->cart->get_cart() ) == 0 )
      wc_add_notice( sprintf( __( 'Please add products.', 'woocommerce' ), home_url() ), 'error' );

    // Note if we skip shipping
    $skipped_shipping = false;


    // Checkout fields (not defined in checkout_fields)
    $this->posted['terms']                     = 1 ;
    $this->posted['createaccount']             = 0;
    $this->posted['payment_method']            = isset( $_POST['payment_method'] ) ? stripslashes( $_POST['payment_method'] ) : '';
    if (isset( $_POST['shipping_method'] ) && is_array( $_POST['shipping_method'] ) ){
      foreach ( $_POST['shipping_method']  as $key => $value) {
        if($value == 'no_shipping'){
          unset($_POST['shipping_method'][$key]);
          $skipped_shipping = true;
        }
      }
      if(count($_POST['shipping_method'][$key]) == 0) unset($_POST['shipping_method']);
    }
  
    $this->posted['shipping_method']           = isset( $_POST['shipping_method'] ) ? $_POST['shipping_method'] : '';


    $this->posted['ship_to_different_address'] = isset( $_POST['ship_to_different_address'] ) ? true : false;
    $this->posted['order_comments']            = isset( $_POST['order_comments'] ) ? $_POST['order_comments'] : '';

    if(! empty( $_POST[ 'customer_details' ] ) ){
      parse_str($_POST['customer_details'], $userdata);
      if(isset($userdata['shipping_country']) && !empty($userdata['shipping_country']))
        $this->posted['ship_to_different_address']  = true;
    }else if($this->customer_id){
      $this->posted['ship_to_different_address']  = true;
    }


    // Ship to billing only option
    if ( WC()->cart->ship_to_billing_address_only() )
      $this->posted['ship_to_different_address']  = false;

    if ( isset( $this->posted['shipping_method'] ) && is_array( $this->posted['shipping_method'] ) )
      foreach ( $this->posted['shipping_method'] as $i => $value )
        $this->chosen_shipping_methods[ $i ] = wc_clean( $value );

    
    
    // Get posted checkout_fields
    foreach ( $this->checkout_fields as $fieldset_key => $fieldset ) {

      // Skip shipping if not needed
      if ( $fieldset_key == 'shipping' && ( $this->posted['ship_to_different_address'] == false || ! WC()->cart->needs_shipping() ) ) {
        $skipped_shipping = true;
        continue;
      }

      foreach ( $fieldset as $key => $field ) {
        $this->posted[ $key ] = $this->get_value($key);
      }
    }

    if ( isset( $this->posted['billing_country'] ) )
      WC()->customer->set_country( $this->posted['billing_country'] );
    if ( isset( $this->posted['billing_state'] ) )
      WC()->customer->set_state( $this->posted['billing_state'] );
    if ( isset( $this->posted['billing_postcode'] ) )
      WC()->customer->set_postcode( $this->posted['billing_postcode'] );

    // Shipping Information
    
    if ( ! $skipped_shipping ) {

      // Update customer location to posted location so we can correctly check available shipping methods
      if ( isset( $this->posted['shipping_country'] ) )
        WC()->customer->set_shipping_country( $this->posted['shipping_country'] );
      if ( isset( $this->posted['shipping_state'] ) )
        WC()->customer->set_shipping_state( $this->posted['shipping_state'] );
      if ( isset( $this->posted['shipping_postcode'] ) )
        WC()->customer->set_shipping_postcode( $this->posted['shipping_postcode'] );

    } else {

      // Update customer location to posted location so we can correctly check available shipping methods
      if ( isset( $this->posted['billing_country'] ) )
        WC()->customer->set_shipping_country( $this->posted['billing_country'] );
      if ( isset( $this->posted['billing_state'] ) )
        WC()->customer->set_shipping_state( $this->posted['billing_state'] );
      if ( isset( $this->posted['billing_postcode'] ) )
        WC()->customer->set_shipping_postcode( $this->posted['billing_postcode'] );

    }

    

    // Update cart totals now we have customer address
    WC()->cart->calculate_totals();

    if ( WC()->cart->needs_shipping() && !$skipped_shipping) {

      if ( ! in_array( $this->get_value( 'shipping_country' ), array_keys( WC()->countries->get_shipping_countries() ) ) )
        wc_add_notice( sprintf( __( 'Unfortunately <strong>we do not ship to %s</strong>. Please enter an alternative shipping address.', 'woocommerce' ), WC()->countries->shipping_to_prefix() . ' ' . $this->get_value( 'shipping_country' ) ), 'error' );

      // Validate Shipping Methods
      $packages               = WC()->shipping->get_packages();
 
      $this->shipping_methods = $this->chosen_shipping_methods;

      foreach ( $packages as $i => $package ) {
        if ( ! isset( $package['rates'][ $this->shipping_methods[ $i ] ] ) ) {
          wc_add_notice( __( 'Invalid shipping method.', 'woocommerce' ), 'error' );
          $this->shipping_methods[ $i ] = '';
        }
      }
    }

    if ( WC()->cart->needs_payment() &&  $this->needs_payment) {

      // Payment Method
      $available_gateways = WC()->payment_gateways->payment_gateways();

      if ( ! isset( $available_gateways[ $this->posted['payment_method'] ] ) ) {
        $this->payment_method = '';
        wc_add_notice( __( 'Invalid payment method.', 'woocommerce' ), 'error' );
      } else {
        $this->payment_method = $available_gateways[ $this->posted['payment_method'] ];
        $this->payment_method->validate_fields();
      }

    }

      try {
        
        // Do a final stock check at this point
        $this->check_cart_items();

        include_once( dirname(WC_PLUGIN_FILE).'/includes/wc-notice-functions.php' );

        // Abort if errors are present
        if ( wc_notice_count( 'error' ) > 0 )
          throw new Exception();


        $order_id = $this->create_order();

        update_option('woocommerce_tax_based_on', $this->wc_tax_based_on);
        update_option('woocommerce_default_country', $this->wc_default_country);
        // Process payment
        if ( $this->needs_payment && WC()->cart->needs_payment() ) {
          setcookie ("wc_point_of_sale_register", $_GET['reg'] ,time()+3600*24*120, '/');
          // Store Order ID in session so it can be re-used after payment failure
          WC()->session->order_awaiting_payment = $order_id;

          // Process Payment
         # $result = $this->process_payment($order_id);
          $result = $available_gateways[ $this->posted['payment_method'] ]->process_payment( $order_id );

          // Redirect to success/confirmation/payment page
          if ( $result['result'] == 'success' ) {

            $result = apply_filters( 'woocommerce_payment_successful_result', $result, $order_id );

            $table_name = $wpdb->prefix . "wc_poin_of_sale_registers";

            $rows_affected = $wpdb->update( $table_name, array('order_id' => 0), array('ID' => $_POST['id_register'] ) );

              $order__ = new WC_Order( $order_id );

                //payment_label
              add_post_meta($order_id, 'wc_pos_amount_pay', $_POST['amount_pay'], true);
              add_post_meta($order_id, 'wc_pos_amount_change', $_POST['amount_change'], true);
              

              $count_orders = esc_attr( get_user_meta( get_current_user_id(), 'wc_pos_count_orders', true ) );
              update_user_meta( get_current_user_id(), 'wc_pos_count_orders', $count_orders+1  );
              update_option('woocommerce_calc_shipping', $this->old_calc_shipping );

              $end_of_sale_order_status = WC_Admin_Settings::get_option( 'woocommerce_pos_end_of_sale_order_status', 'processing' );
              $order__->update_status( $end_of_sale_order_status, __( 'Point of Sale transaction completed.', 'woocommerce' ) );
              wc_add_notice( __( 'Order saved.', 'wc_point_of_sale' ), 'success' );



              if($EmailReceiptDetail = isEmailReceipt( $_POST['id_register'] ) && $this->posted['billing_email']){
                if(isset($order_id) ){
                    $receipt_ID = $EmailReceiptDetail['receipt_template'];
                    $outlet_ID  = $EmailReceiptDetail['outlet'];
                    $register_ID = $_POST['id_register'];
                    $preview     = false;

                    $order = new WC_Order($order_id);
                    $receipt_options = WC_POS()->receipt()->get_data($receipt_ID);
                    $receipt_options = $receipt_options[0];
                    $attachment_image_logo = wp_get_attachment_image_src( $receipt_options['logo'], 'full' );

                    $register = WC_POS()->register()->get_data($register_ID);
                    $register = $register[0];
                    $register_name = $register['name'];

                    $outlet = WC_POS()->outlet()->get_data($outlet_ID);
                    $outlet = $outlet[0];
                    $address = $outlet['contact'];
                    $address['first_name'] = '';
                    $address['last_name'] = '';
                    $address['company'] = '';
                    $outlet_address = WC()->countries->get_formatted_address( $address );
                    ob_start();
                    ?>
                    <html>
                      <head>
                        <title><?php echo get_bloginfo('name'); ?></title>
                        <meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
                      </head>
                      <body bgcolor="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
                        <center>
                          <table width="600"  border="0" cellpadding="0" cellspacing="0" style="font-family:Arial; font-size:14px; color: #494848">
                            <tr>
                            <td>
                              <?php
                              require_once( dirname(realpath(dirname(__FILE__) ) ).'/views/html-print-receipt.php' );
                              ?>
                            </td>
                            </tr>
                          </table>
                        </center>
                      </body>
                    </html>
                    <?php
                    $message_html = ob_get_contents();
                    ob_end_clean();

                    $multiple_to_recipients = array(
                        $this->posted['billing_email']
                    );

                    add_filter( 'wp_mail_content_type', array($this, 'set_html_content_type') );

                    wp_mail( $multiple_to_recipients, 'Receipt', $message_html );

                    // Reset content-type to avoid conflicts -- http://core.trac.wordpress.org/ticket/23578
                    remove_filter( 'wp_mail_content_type', array($this, 'set_html_content_type') );
                }
              }
              if( isPrintReceipt( $_POST['id_register'] ) ){
                wc_add_notice( __( 'Printing&hellip;', 'wc_point_of_sale' ).'<input type="hidden" id="print_order_id" value="'.$order_id.'" />', 'printing' );
                setcookie ("wc_point_of_sale_printing", $order_id,time()+3600*24*120, '/');
              }
              wp_redirect( $result['redirect'] );
              exit;
          }

        }  else {

          if ( empty( $order ) )
            $order = new WC_Order( $order_id );

          // No payment was required for order
          //$order->payment_complete();

          // Empty the Cart
          WC()->cart->empty_cart();
          update_option('woocommerce_calc_shipping', $this->old_calc_shipping );
          wc_add_notice( __( 'Order saved.', 'wc_point_of_sale' ), 'success' );
          throw new Exception();
          // Get redirect
          //$return_url = $order->get_checkout_order_received_url();

          // Redirect to success/confirmation/payment page
          

        }

      } catch ( Exception $e ) {
        update_option('woocommerce_tax_based_on', $this->wc_tax_based_on);
        update_option('woocommerce_default_country', $this->wc_default_country);
        if ( ! empty( $e ) )
          wc_add_notice( $e->getMessage(), 'error' );

      }


  }
  /**
   * Gets the value either from the posted data, or from the users meta data
   *
   * @access public
   * @param string $input
   * @return string|null
   */
  public function get_value( $input ) {
    if ( ! empty( $_POST[ $input ] ) ) {

      return wc_clean( $_POST[ $input ] );

    } else {

      $value = apply_filters( 'woocommerce_checkout_get_value', null, $input );

      if ( $value !== null )
        return $value;

      if ( $this->customer_id ) {

        if ( $meta = get_user_meta( $this->customer_id, $input, true ) )
          return $meta;

        $user_info = get_userdata($this->customer_id);
        if ( $input == "billing_email" )
          return $user_info->user_email;

        $user_info = get_user_meta($this->customer_id);
        switch ( $input ) {
          case "billing_country" :
            return apply_filters( 'default_checkout_country', isset($user_info['billing_country']) ? $user_info['billing_country'][0] : WC()->countries->get_base_country(), 'billing' );
          case "billing_state" :
            return apply_filters( 'default_checkout_state', isset($user_info['billing_state']) ? $user_info['billing_state'][0] : '', 'billing' );
          case "billing_postcode" :
            return apply_filters( 'default_checkout_postcode', isset($user_info['billing_postcode']) ? $user_info['billing_postcode'][0] : '', 'billing' );
          case "shipping_country" :
            return apply_filters( 'default_checkout_country', isset($user_info['shipping_country']) ? $user_info['shipping_country'][0] : WC()->countries->get_base_country(), 'shipping' );
          case "shipping_state" :
            return apply_filters( 'default_checkout_state', isset($user_info['shipping_state']) ? $user_info['shipping_state'][0] : '', 'shipping' );
          case "shipping_postcode" :
            return apply_filters( 'default_checkout_postcode', isset($user_info['shipping_postcode']) ? $user_info['shipping_postcode'][0] : '', 'shipping' );
          default :
            return apply_filters( 'default_checkout_' . $input, null, $input );
        }
      }elseif(! empty( $_POST[ 'customer_details' ] ) ){
        parse_str($_POST['customer_details'], $userdata);

        switch ( $input ) {
          case "billing_country" :
            return apply_filters( 'default_checkout_country', isset($userdata['billing_country']) ? $userdata['billing_country'] : WC()->countries->get_base_country(), 'billing' );
          case "billing_state" :
            return apply_filters( 'default_checkout_state', isset($userdata['billing_state']) ? $userdata['billing_state'] : '', 'billing' );
          case "billing_postcode" :
            return apply_filters( 'default_checkout_postcode', isset($userdata['billing_postcode']) ? $userdata['billing_postcode'] : '', 'billing' );
          case "shipping_country" :
            return apply_filters( 'default_checkout_country', isset($userdata['shipping_country']) ? $userdata['shipping_country'] : WC()->countries->get_base_country(), 'shipping' );
          case "shipping_state" :
            return apply_filters( 'default_checkout_state', isset($userdata['shipping_state']) ? $userdata['shipping_state'] : '', 'shipping' );
          case "shipping_postcode" :
            return apply_filters( 'default_checkout_postcode', isset($userdata['shipping_postcode']) ? $userdata['shipping_postcode'] : '', 'shipping' );
          default :
            return apply_filters( 'default_checkout_' . $input, isset($userdata[$input]) ? $userdata[$input] : '', $input );
        }
      }
    }
  }
  /**
   * Add to cart
   */
  public function add_to_cart($id, $qty) {
    $product_id        = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $id ) );
    $quantity          = empty( $qty ) ? 1 : apply_filters( 'woocommerce_stock_amount', $qty );
    $passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );

    if ( $passed_validation ) {
      WC()->cart->add_to_cart( $product_id, $quantity );
    }
  }

  public function set_order_discount($discount) {
    $coupon_name = 'POS Coupon '.$_POST['id_register'];
    $new_coupon = array(
         'post_title' => $coupon_name,
         'post_status' => 'publish',
         'post_author' => get_current_user_id(),
         'post_type' => 'shop_coupon'
    );
    // Insert the post into the database
    $coupon_id = wp_insert_post( $new_coupon );
    update_post_meta($coupon_id, 'coupon_amount', $discount);
  }

  public function save_new_order() {
    // add to cart
    $ids      = isset( $_POST['product_item_id'] ) ? $_POST['product_item_id'] : array();
    $qty      = isset( $_POST['order_item_qty'] ) ? $_POST['order_item_qty'] : array();

    if(!isset( $_POST['shipping_method'] ) ){
      update_option('woocommerce_calc_shipping', 'no');
    }

    $this->wc_tax_based_on = get_option('woocommerce_tax_based_on', 'shipping');

    $this->wc_default_country = get_option('woocommerce_default_country');

    $wc_pos_tax_based_on    = get_option('woocommerce_pos_calculate_tax_based_on', 'default');

    if($this->wc_calc_taxes == 'yes' && $this->wc_pos_tax_calculation == 'enabled'){
      if($wc_pos_tax_based_on != 'default' && $this->wc_tax_based_on != $wc_pos_tax_based_on){
        switch ($wc_pos_tax_based_on) {
          case 'shipping':
            update_option('woocommerce_tax_based_on', 'shipping');
            break;
          case 'billing':
            update_option('woocommerce_tax_based_on', 'billing');
            break;
          case 'base':
            update_option('woocommerce_tax_based_on', 'base');
            break;
          case 'outlet':
            $id_register = (isset($_POST['id_register']) && !empty($_POST['id_register']) ) ? $_POST['id_register'] : '';
            if(!empty($id_register)){ 
              $outlet_ID      = $_POST['outlet_ID'];
              $outlet_data    = WC_POS()->outlet()->get_data($outlet_ID);
              $outlet_country = $outlet_data[0]['contact']['country'];
              update_option('woocommerce_default_country', $outlet_country);
              update_option('woocommerce_tax_based_on', 'base');
            }
            break;
        }
      }
      
    }

    foreach ($ids as $id) {
      $this->add_to_cart($id, $qty[$id]);
    }

    
    $user_id          = isset( $_POST['user_id'] ) ? $_POST['user_id'] : 0;
    $discount         = isset( $_POST['order_discount'] ) ? $_POST['order_discount'] : 0;

    #$this->set_order_discount($discount);

  }
  function set_html_content_type() {
    return 'text/html';
  }

} //class