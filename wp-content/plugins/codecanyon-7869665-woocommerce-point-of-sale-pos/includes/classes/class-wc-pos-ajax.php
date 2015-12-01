<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

/**
 * WooCommerce WC_AJAX
 *
 * AJAX Event Handler
 *
 * @class     WC_POS_AJAX
 * @version   2.1.0
 * @package   WoocommercePointOfSale/Classes
 * @category  Class
 * @author    Actuality Extensions
 */

class WC_POS_AJAX {

    /**
     * Hook into ajax events
     */
    public function __construct() {

        // woocommerce_EVENT => nopriv
        $ajax_events = array(
            'new_update_outlets_address'   => false,
            'edit_update_outlets_address'  => false,
            'add_products_to_register'     => false,
            'update_product_quantity'      => false,
            'remove_product_from_register' => false,
            'add_customer'                 => false,
            'loading_states'               => false,
            'add_customers_to_register'    => false,
            'search_variations_for_product'=> false,
            'tile_ordering'                => false,
            'json_search_usernames'        => false,
            'printing_receipt'             => false,
            'search_variations_for_product_and_sku' => false,
            'check_shipping'               => false,
            'load_pending_orders'          => false,
            'load_order_data'              => false,
            'stripe_get_user'              => false,
            'stripe_get_outlet_address'    => false,
            'void_products_register'       => false,
            'json_search_products'         => false,
            'json_search_products_all'     => false,
            'find_variantion_by_attributes'=> false,
            'add_product_grid'             => false,
        ); 

        foreach ($ajax_events as $ajax_event => $nopriv) {
            add_action('wp_ajax_wc_pos_' . $ajax_event, array($this, $ajax_event));

            if ($nopriv)
                add_action('wp_ajax_nopriv_wc_pos_' . $ajax_event, array($this, $ajax_event));
        }
    }

    /**
     * Output headers for JSON requests
     */
    private function json_headers() {
        header('Content-Type: application/json; charset=utf-8');
    }

    public function new_update_outlets_address() {
        check_ajax_referer('new-update-pos-outlets-address', 'security');
        WC_Pos_Outlets::display_outlet_form();
        die();
    }

    public function edit_update_outlets_address() {
        check_ajax_referer('edit-update-pos-outlets-address', 'security');
        WC_Pos_Outlets::display_edit_form();
        die();
    }

    /* change the state according country */

    public function loading_states() {
        $country = $_REQUEST['country'];
        $id = $_REQUEST['id'];
        $countries = new WC_Countries();
        $filds = $countries->get_address_fields($country, '');

        unset($filds['first_name']);
        unset($filds['last_name']);
        unset($filds['company']);
        $filds['country']['options'] = $countries->get_allowed_countries();
        $filds['country']['type'] = 'select';

        if ($country != '') {
            $filds['country']['value'] = $country;
            $states = $countries->get_allowed_country_states();
            if (!empty($states[$country])) {
                $filds['state']['options'] = $states[$country];
                $filds['state']['type'] = 'select';
            }
        }

        $statelabel = $filds['state']['label'];
        $postcodelabel = $filds['postcode']['label'];
        $citylabel = $filds['city']['label'];
        $html = array();
        $state_html = '';
        if($id == 'shipping_country'){
            $dd = 'shipping_state';
        }else{
            $dd = 'billing_state';
        }
        if (isset($filds['state']['options']) &&  !empty($filds['state']['options'])) {
            $state_html .= '<select id="' . $dd . '" class="ajax_chosen_select_' . $dd . '" style="width: 220px;" name="' . $id . '_county">';
            foreach ($filds['state']['options'] as $key => $value) {
                $state_html .= '<option value = "' . $key . '"> ' . $value . '</option>';
            }
            $state_html .= '</select>';
        }else {
            $state_html .= '<input type="text" id="' . $dd . '" name="' . $dd . '" class="input" placeholder="'.$statelabel.'"/>';
        }
        $html['state_html'] = $state_html;
        $html['state_label'] = $statelabel;
        $html['zip_label'] = $postcodelabel;
        $html['city_label'] = $citylabel;
        echo(json_encode($html));
        die;
    }

    public function add_customer() {
        global $wpdb, $user;
        $userdata = array();
        parse_str($_REQUEST['form_data'], $userdata);
        $email = $userdata['billing_email'];
        $useremail = sanitize_user($email);
        $username = explode('@', $useremail);
        $username = $username[0];
        $id_user = username_exists( $username );
        $user_id = 0;
        // CREATES WP USER ACCOUNT
        if ( !isset($userdata['createaccount']) || empty($userdata['createaccount']) ) {
            if($id_user || email_exists($useremail) == true)
                die( json_encode( array('success' => false, 'message' => __('User already exists.', 'wc_point_of_sale') ) ) );
            else
                die( json_encode( array('success' => true ) ) );
        }
        if ( !$id_user and email_exists($useremail) == false ) {
            #$random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );

            $tmp_registration_generate_password = get_option( 'woocommerce_registration_generate_password' );
            update_option( 'woocommerce_registration_generate_password', 'yes' );
            $user_id = wc_create_new_customer( $useremail, $username, '' );
            update_option( 'woocommerce_registration_generate_password', $tmp_registration_generate_password );
        } else {
            die( json_encode( array('success' => false, 'message' => __('User already exists.', 'wc_point_of_sale') ) ) );
        }


        $phone = $userdata['billing_phone'];
        $billing_countries = $userdata['billing_country'];        
        $billing_firstname = $userdata['billing_first_name'];
        $billing_lastname = $userdata['billing_last_name'];        
        $billing_company = $userdata['billing_company'];       
        $billing_address = $userdata['billing_address_1'];        
        $billing_address1 = $userdata['billing_address_2'];        
        $billing_city = $userdata['billing_city'];       
        $billing_county = $userdata['billing_state'];
        $billing_postcode = $userdata['billing_postcode'];

        if(isset($userdata['ship_to_different_address'])){
            $shipping_countries = $userdata['shipping_country'];
            $shipping_firstname = $userdata['shipping_first_name'];
            $shipping_lastname = $userdata['shipping_last_name'];
            $shipping_company = $userdata['shipping_company'];
            $shipping_address = $userdata['shipping_address_1'];
            $shipping_address1 = $userdata['shipping_address_2'];
            $shipping_city = $userdata['shipping_city'];
            $shipping_county = $userdata['shipping_state'];
            $shipping_postcode = $userdata['shipping_postcode'];
        }else{
            $shipping_countries = $billing_countries;
            $shipping_firstname = $billing_firstname;
            $shipping_lastname = $billing_lastname;
            $shipping_company = $billing_company;
            $shipping_address = $billing_address;
            $shipping_address1 = $billing_address1;
            $shipping_city = $billing_city;
            $shipping_county = $billing_county;
            $shipping_postcode = $billing_postcode;
        }

        /* INSERT IN TO USER TABLE */
        $user_nicename = $billing_firstname . "-" . $billing_lastname;
        $user_registered = date('Y-m-d h:i:s');
        $display_name = $billing_firstname . " " . $billing_lastname;

        if ($user_id) {
            // SENDS EMAIL NOTIFICATION
            wp_new_user_notification($user_id, $random_password);
            // Use 'update_user_meta()' to add or update the user information fields.
            update_user_meta($user_id, 'user_nicename', $user_nicename);
            update_user_meta($user_id, 'user_registered', $user_registered);
            update_user_meta($user_id, 'display_name', $display_name);
            update_user_meta($user_id, 'first_name', $billing_firstname);
            update_user_meta($user_id, 'last_name', $billing_lastname);
            $user = new WP_User($user_id);
            $user->set_role('customer');
            update_user_meta($user_id, 'billing_first_name', $billing_firstname);
            update_user_meta($user_id, 'billing_last_name', $billing_lastname);
            update_user_meta($user_id, 'billing_company', $billing_company);
            update_user_meta($user_id, 'billing_address_1', $billing_address);
            update_user_meta($user_id, 'billing_address_2', $billing_address1);
            update_user_meta($user_id, 'billing_city', $billing_city);
            update_user_meta($user_id, 'billing_postcode', $billing_postcode);
            update_user_meta($user_id, 'billing_state', $billing_county);
            update_user_meta($user_id, 'billing_country', $billing_countries);
            update_user_meta($user_id, 'billing_phone', $phone);
            update_user_meta($user_id, 'billing_email', $email);
            update_user_meta($user_id, 'shipping_first_name', $shipping_firstname);
            update_user_meta($user_id, 'shipping_last_name', $shipping_lastname);
            update_user_meta($user_id, 'shipping_company', $shipping_company);
            update_user_meta($user_id, 'shipping_address_1', $shipping_address);
            update_user_meta($user_id, 'shipping_address_2', $shipping_address1);
            update_user_meta($user_id, 'shipping_city', $shipping_city);
            update_user_meta($user_id, 'shipping_postcode', $shipping_postcode);
            update_user_meta($user_id, 'shipping_state', $shipping_county);
            update_user_meta($user_id, 'shipping_country', $shipping_countries);
            update_user_meta($user_id, 'order_notes', $order_notes);
            $success = "success";
            echo(json_encode(array('success' => true, 'id'=> $user_id, 'name' => $billing_firstname . " " . $billing_lastname)));
        }

        die;
    }

    public function remove_product_from_register()
    {
        global $wpdb;
        check_ajax_referer('remove_product_from_register', 'security');
        $register_id = absint($_POST['register_id']);

        $id_product = absint($_POST['id_product']);
        $order_id = absint($_POST['order_id']);
        if (!is_numeric($id_product))
            die();
        wc_delete_order_item( $id_product );
        die('Deleted');
    }

    public function update_product_quantity()
    {
        check_ajax_referer('add_product_to_register', 'security');
        $register_id = absint($_POST['register_id']);

        $item_order_id = absint($_POST['item_order_id']);
        $new_quantity  = absint($_POST['new_quantity']);

        if (!is_numeric($item_order_id))
            die($item_order_id);
        if (!is_numeric($new_quantity))
            die();

        $order_id = absint($_POST['order_id']);
        $order = new WC_Order($order_id);

        $order_items = $order->get_items(apply_filters('woocommerce_admin_order_item_types', array('line_item', 'fee')));

        $_product = $order->get_product_from_item($order_items[$item_order_id]);

        $_tax     = new WC_Tax();
        $price    = $_product->get_price();
        $qty      = 1;
        $line_tax = 0;

        if ( ! defined( 'WOOCOMMERCE_CHECKOUT' ) )
            define( 'WOOCOMMERCE_CHECKOUT', true );


        if ( $_product->is_taxable() ) {
            if ( get_option('woocommerce_prices_include_tax') === 'no' ) {

                $tax_rates  = $_tax->get_rates( $_product->get_tax_class() );
                $taxes      = $_tax->calc_tax( $price * $qty, $tax_rates, false );
                $tax_amount   = $_tax->get_tax_total( $taxes );
                $line_tax      = round( $tax_amount, absint( get_option( 'woocommerce_price_num_decimals' ) ) );
            } else {
                $tax_rates      = $_tax->get_rates( $_product->get_tax_class() );
                $base_tax_rates = $_tax->get_shop_base_rate( $_product->tax_class );
                $is_vat_exempt = false;
                if ( $is_vat_exempt ) {
                    $base_taxes         = $_tax->calc_tax( $price * $qty, $base_tax_rates, true );
                    $base_tax_amount    = array_sum( $base_taxes );
                    $line_tax           = round( $base_tax_amount, absint( get_option( 'woocommerce_price_num_decimals' ) ) );

                } elseif ( $tax_rates !== $base_tax_rates ) {
                    $base_taxes         = $_tax->calc_tax( $price * $qty, $base_tax_rates, true );
                    $modded_taxes       = $_tax->calc_tax( ( $price * $qty ) - array_sum( $base_taxes ), $tax_rates, false );
                    #$line_tax              = round( array_sum( $base_taxes ) + array_sum( $modded_taxes ), absint( get_option( 'woocommerce_price_num_decimals' ) ) );
                    $line_tax              = round( array_sum( $base_taxes ), absint( get_option( 'woocommerce_price_num_decimals' ) ) );
                }

            }

        }
        wc_update_order_item_meta( $item_order_id, '_qty', apply_filters( 'woocommerce_stock_amount', $new_quantity) );
        wc_update_order_item_meta( $item_order_id, '_line_tax', $line_tax);
        die('updated');

    }

    public function add_products_to_register() {
        global $wpdb;
        check_ajax_referer('add_product_to_register', 'security');
        $register_id = absint($_POST['register_id']);

        $item_to_add = sanitize_text_field($_POST['item_to_add']);
        $order_id = absint($_POST['order_id']);

// Find the item
        if (!is_numeric($item_to_add))
            die();

        $post = get_post($item_to_add);

        if (!$post || ( $post->post_type !== 'product' && $post->post_type !== 'product_variation' ))
            die();

        $_product = get_product($post->ID);
        $_product_id_var = $post->ID;

        $order = new WC_Order($order_id);
        $class = 'new_row product_id_' . $_product_id_var;

// Set values
        $item = array();

        $item['product_id'] = $_product->id;
        $item['variation_id'] = isset($_product->variation_id) ? $_product->variation_id : '';
        $item['variation_data'] = isset($_product->variation_data) ? $_product->variation_data : '';
        $item['name'] = $_product->get_title();
        $item['tax_class'] = $_product->get_tax_class();
        $item['qty'] = 1;
        $item['line_subtotal'] = wc_format_decimal($_product->get_price_excluding_tax());
        $item['line_subtotal_tax'] = '';
        $item['line_total'] = wc_format_decimal($_product->get_price_excluding_tax()) * $item['qty'];
        $item['line_tax'] = '';

        $_tax     = new WC_Tax();
        $price    = $_product->get_price();
        $qty      = 1;
        $line_tax = 0;

        if ( ! defined( 'WOOCOMMERCE_CHECKOUT' ) )
            define( 'WOOCOMMERCE_CHECKOUT', true );


        if ( $_product->is_taxable() ) {
            if ( get_option('woocommerce_prices_include_tax') === 'no' ) {

                $tax_rates  = $_tax->get_rates( $_product->get_tax_class() );
                $taxes      = $_tax->calc_tax( $price * $qty, $tax_rates, false );
                $tax_amount   = $_tax->get_tax_total( $taxes );
                $line_tax      = round( $tax_amount, absint( get_option( 'woocommerce_price_num_decimals' ) ) );
            } else {
                $tax_rates      = $_tax->get_rates( $_product->get_tax_class() );
                $base_tax_rates = $_tax->get_shop_base_rate( $_product->tax_class );
                $is_vat_exempt = false;
                if ( $is_vat_exempt ) {
                    $base_taxes         = $_tax->calc_tax( $price * $qty, $base_tax_rates, true );
                    $base_tax_amount    = array_sum( $base_taxes );
                    $line_tax           = round( $base_tax_amount, absint( get_option( 'woocommerce_price_num_decimals' ) ) );

                } elseif ( $tax_rates !== $base_tax_rates ) {
                    $base_taxes         = $_tax->calc_tax( $price * $qty, $base_tax_rates, true );
                    $modded_taxes       = $_tax->calc_tax( ( $price * $qty ) - array_sum( $base_taxes ), $tax_rates, false );
                    #$line_tax              = round( array_sum( $base_taxes ) + array_sum( $modded_taxes ), absint( get_option( 'woocommerce_price_num_decimals' ) ) );
                    $line_tax              = round( array_sum( $base_taxes ), absint( get_option( 'woocommerce_price_num_decimals' ) ) );
                }

            }

        }
        if($line_tax)  $item['line_tax'] = $line_tax;


// Add line item
        $item_id = wc_add_order_item($order_id, array(
            'order_item_name' => $item['name'],
            'order_item_type' => 'line_item'
        ));

// Add line item meta
        if ($item_id) {
            wc_add_order_item_meta($item_id, '_qty', $item['qty']);
            wc_add_order_item_meta($item_id, '_tax_class', $item['tax_class']);
            wc_add_order_item_meta($item_id, '_product_id', $item['product_id']);
            wc_add_order_item_meta($item_id, '_variation_id', $item['variation_id']);
            wc_add_order_item_meta($item_id, '_line_subtotal', $item['line_subtotal']);
            wc_add_order_item_meta($item_id, '_line_subtotal_tax', $item['line_subtotal_tax']);
            wc_add_order_item_meta($item_id, '_line_total', $item['line_total']);
            wc_add_order_item_meta($item_id, '_line_tax', $item['line_tax']);

// Store variation data in meta
            if ($item['variation_data'] && is_array($item['variation_data'])) {
                foreach ($item['variation_data'] as $key => $value) {
                    wc_add_order_item_meta($item_id, str_replace('attribute_', '', $key), $value);
                }
            }

            do_action('woocommerce_ajax_add_order_item_meta', $item_id, $item);
        }



        $item = apply_filters('woocommerce_ajax_order_item', $item, $item_id);

        require_once( dirname(realpath(dirname(__FILE__))) . '/views/html-admin-registers-product-item.php' );

        die();
    }

    public function void_products_register() {
        global $wpdb;
        check_ajax_referer('void_products_register', 'security');

        $register_id = absint($_POST['register_id']);
        $order_id    = absint($_POST['order_id']);

        $order = new WC_Order($order_id);

        $order_items = $order->get_items();
        if ( sizeof( $order_items ) > 0 ) {
            foreach( $order_items as $id => $data ) {
                wc_delete_order_item( absint( $id ) );
            }
        }
        die;
    }

    public function add_customers_to_register() {
        global $wpdb;
        check_ajax_referer('add_customers_to_register', 'security');

        $register_id = absint($_POST['register_id']);

        $user_to_add = absint($_POST['user_to_add']);
        $class = 'new_row';
        
        require_once( dirname(realpath(dirname(__FILE__))) . '/views/html-admin-registers-customer.php' );
        die;
    }
    public function check_shipping() {
        global $wpdb;

        check_ajax_referer('check_shipping', 'security');

        $register_id = absint($_POST['register_id']);

        $user_id = isset( $_POST['user_to_add'] ) ? absint($_POST['user_to_add']) : 0;
        if(!$user_id) die();


        $products_ids = $_POST['products_ids'];
        parse_str($products_ids, $ids);        
        $ids = $ids['product_item_id'];

        $products_qt = $_POST['products_qt'];
        parse_str($products_qt, $qty);
        $qty = $qty['order_item_qty'];
        
        $session_class = apply_filters( 'woocommerce_session_handler', 'WC_Session_Handler' );
        WC()->cart     = new WC_Cart();
        WC()->customer = new WC_Customer();
        WC()->shipping = new WC_Shipping();
        WC()->session  = new $session_class();

        $user_info = get_user_meta($user_id);

        $country = isset($user_info['billing_country']) ? $user_info['billing_country'][0] : '';
        $state = isset($user_info['billing_state']) ? $user_info['billing_state'][0] : '';
        $postcode = isset($user_info['billing_postcode']) ? $user_info['billing_postcode'][0] : '';
        $city = isset($user_info['billing_city']) ? $user_info['billing_city'][0] : '';

        if( isset($user_info['shipping_country']) && $s_country = $user_info['shipping_country'][0] ){
            $s_state = isset($user_info['shipping_state']) ? $user_info['shipping_state'][0] : '';
            $s_postcode = isset($user_info['shipping_postcode']) ? $user_info['shipping_postcode'][0] : '';
            $s_city = isset($user_info['shipping_city']) ? $user_info['shipping_city'][0] : '';
        }else{
            $s_country = $country;
            $s_state = $state;
            $s_postcode = $postcode;
            $s_city = $city;
        }

        WC()->customer->set_location( $country, $state, $postcode, $city  );
        WC()->customer->set_shipping_location( $s_country, $s_state, $s_postcode, $s_city  );

        
        foreach ($ids as $key => $id) {
            $product_id        = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $id ) );
            $quantity          = empty( $qty[$key] ) ? 1 : apply_filters( 'woocommerce_stock_amount', $qty[$key] );
            $passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $id, $quantity );
            if ( $passed_validation ) {
              WC()->cart->add_to_cart( $id, $quantity );
            }
        }

        if ( ! defined( 'WOOCOMMERCE_CART' ) )
            define( 'WOOCOMMERCE_CART', true );
            WC()->cart->calculate_totals();
            WC()->cart->calculate_shipping();

        if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) :
            $packages = WC()->shipping->get_packages();

            foreach ( $packages as $i => $package ) {
                $chosen_method = isset( WC()->session->chosen_shipping_methods[ $i ] ) ? WC()->session->chosen_shipping_methods[ $i ] : '';
                $available_methods = $package['rates'];
                $show_package_details = ( sizeof( $packages ) > 1 );
                $index =  $i;
                require( dirname(realpath(dirname(__FILE__))) . '/views/html-admin-cart-shipping.php' );
            }

        endif; 
        // Remove cart
        WC()->cart->empty_cart();
        
        die();
    }

    public function search_variations_for_product()
    {
        global $wpdb;
        check_ajax_referer('search_variations_for_product', 'security');
        $id_product = absint($_POST['id_product']);
        $args = array(
            'post_type'      => array( 'product_variation' ),
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'order'          => 'ASC',
            'orderby'        => 'parent title',
            'post_parent'    => $id_product,
        );

        $posts = get_posts( $args );
        $found_products = array();

        if ( $posts ) {
            foreach ( $posts as $post ) {
                $product = get_product( $post->ID );
                $image = '';
                $size = 'shop_thumbnail';
                if ( has_post_thumbnail( $post->ID ) ) {
                  $thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), $size );
                  $image = $thumbnail[0];
                } elseif ( ( $parent_id = wp_get_post_parent_id( $post->ID ) ) && has_post_thumbnail( $parent_id ) ) {
                  $thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id($parent_id), $size );
                  $image = $thumbnail[0];
                } else {
                  $image = wc_placeholder_img_src();
                }
                if(!$image || $image == NULL) $image = wc_placeholder_img_src();
                
                $found_products[ $post->ID ]['formatted_name'] = $product->get_formatted_name();
                $found_products[ $post->ID ]['image']          = $image;
            }
        }
        if( !empty($found_products) )
            echo json_encode( $found_products );
        die();
    }
    public function search_variations_for_product_and_sku()
    {
        global $wpdb;
        check_ajax_referer('search_variations_for_product_and_sku', 'security');
        $id_product = absint($_POST['id_product']);
        $__product = get_product( $id_product );
        $sku   = $__product->get_sku();
        $price = woocommerce_price( $__product->get_price() );
        $args = array(
            'post_type'      => array( 'product_variation' ),
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'order'          => 'ASC',
            'orderby'        => 'parent title',
            'post_parent'    => $id_product,
        );

        $posts = get_posts( $args );
        $variation = array();

        if ( $posts ) {
            foreach ( $posts as $post ) {
                $product = get_product( $post->ID );
                $variation[ $post->ID ] = array(
                    'name' => $product->get_formatted_name(),
                    'sku' => $product->get_sku(),
                    );
            }
        }
        echo json_encode( array('sku' => $sku, 'price' => $price, 'variation' => $variation) );
        die();
    }
    /**
     * Ajax request handling for tiles ordering
     */
    public function tile_ordering() {
        global $wpdb;

        $id = (int) $_POST['id'];
        $grid_id = (int) $_POST['grid_id'];
        $next_id  = isset($_POST['nextid']) && (int) $_POST['nextid'] ? (int) $_POST['nextid'] : null;

        if ( !$id || !$grid_id) die(0);
        $index = 0;
        $table_name = $wpdb->prefix . 'wc_poin_of_sale_tiles';
        $all_tiles = $tiles = $wpdb->get_results( "SELECT * FROM  $table_name  WHERE grid_id = $grid_id ORDER BY order_position ASC" );

        if( empty( $all_tiles ) ) die($index);

        foreach ($all_tiles as $tile) {

            if( $tile->ID == $id ) { // our tile to order, we skip
                continue; // our tile to order, we skip
            }
            // the nextid of our tile to order, lets move our tile here
            if(null !== $next_id && $tile->ID == $next_id) {
                $index++;
                $wpdb->update( $table_name, array('order_position' => $index), array( 'ID' => $id ) );
            }

            // set order
            $index++;
            $wpdb->update( $table_name, array('order_position' => $index), array( 'ID' => $tile->ID ) );
        }
        if( null === $next_id ){
            $index++;
            $wpdb->update( $table_name, array('order_position' => $index), array( 'ID' => $id ) );
        }
        die($index);
    }
    public function json_search_usernames()
    {
        global $wpdb;

        check_ajax_referer( 'search-usernames', 'security' );

        header( 'Content-Type: application/json; charset=utf-8' );

        $term = urldecode( stripslashes( strip_tags( $_GET['term'] ) ) );

        if ( empty( $term ) )
        die();

        $found_users = array();

        $data = WC_POS()->user()->get_data($term);

        if ( $data ) {
            foreach ( $data as $userid => $user ) {
                $found_users[$userid] = $user['username'];
            }
        }

        echo json_encode( $found_users );
        die();
    }

    function printing_receipt()
    {

        check_ajax_referer( 'printing_receipt', 'security' );
        if(isset($_POST['order_id']) && $_POST['order_id']){
            $order_id    = $_POST['order_id'];
            $receipt_ID  = $_POST['receipt_ID'];
            $outlet_ID   = $_POST['outlet_ID'];
            $register_ID = $_POST['register_ID'];
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
            require_once( dirname(realpath(dirname(__FILE__) ) ).'/views/html-print-receipt.php' );
        }
        die();
    }
    function load_pending_orders()
    {
        check_ajax_referer( 'load_pending_orders', 'security' );
        ?>        
        <table class="wp-list-table widefat fixed retrieve_sales_nav">
            <thead>
                <tr>
                    <th style="" class="manage-column column-order_status" scope="col"><span data-tip="Status" class="status_head tips">Status</span></th>
                    <th style="" class="manage-column column-order_title" scope="col">Order</th>
                    <th style="" class="manage-column column-order_items" scope="col">Purchased</th>
                    <th style="" class="manage-column column-shipping_address" scope="col">Ship to</th>
                    <th style="" class="manage-column column-customer_message" scope="col"><span class="notes_head tips">Customer Message</span></th>
                    <th style="" class="manage-column column-order_notes" scope="col"><span class="order-notes_head tips">Order Notes</span></th>
                    <th style="" class="manage-column column-order_date" scope="col">Date</th>
                    <th style="" class="manage-column column-order_total" scope="col">Total</th>
                    <th style="" class="manage-column column-crm_actions" scope="col">Actions</th>
                </tr>
            </thead>
        </table>
        <div id="retrieve_sales_popup_content">
            <div id="retrieve_sales_popup_content_scroll">
            <?php
            require_once( 'class-wc-pos-order-list.php');
            $id   = (isset($_POST['register_id']) && !empty($_POST['register_id'])) ? $_POST['register_id'] : 0;
            $wc_pos_order_list = new WC_Crm_Order_List();
            $wc_pos_order_list->prepare_items($id);
            $wc_pos_order_list->display();
            ?>
            </div>
        </div>
        <table class="wp-list-table widefat fixed retrieve_sales_nav">
            <tfoot>
                <tr>
                    <th style="" class="manage-column column-order_status" scope="col"><span data-tip="Status" class="status_head tips">Status</span></th>
                    <th style="" class="manage-column column-order_title" scope="col">Order</th>
                    <th style="" class="manage-column column-order_items" scope="col">Purchased</th>
                    <th style="" class="manage-column column-shipping_address" scope="col">Ship to</th>
                    <th style="" class="manage-column column-customer_message" scope="col"><span class="notes_head tips">Customer Message</span></th>
                    <th style="" class="manage-column column-order_notes" scope="col"><span class="order-notes_head tips">Order Notes</span></th>
                    <th style="" class="manage-column column-order_date" scope="col">Date</th>
                    <th style="" class="manage-column column-order_total" scope="col">Total</th>
                    <th style="" class="manage-column column-crm_actions" scope="col">Actions</th>
                </tr>
            </tfoot>
        </table>
        <?php
        die();
    }
    function load_order_data()
    {
        check_ajax_referer( 'load_order_data', 'security' );
            $order_id      = absint($_POST['order_id']);
            $load_order_id = absint($_POST['load_order_id']);
            $register_id   = absint($_POST['register_id']);

            if( get_post_type( $order_id )  == 'pos_temp_register_or'){
                wp_delete_post( $order_id, true );;
            }

            $order = new WC_Order($load_order_id);
            $products = '';
            $order_items = $order->get_items(apply_filters('woocommerce_admin_order_item_types', array('line_item', 'fee')));
                if(!empty($order_items) && is_array($order_items)){
                ob_start();
                foreach ($order_items as $item_id => $item) {

                    $_product = $order->get_product_from_item($item);
                    $price    = $_product->get_price();


                    $_product_id_var = $_product->id;
                    if(!empty($_product->variation_id)){
                        $_product_id_var = $_product->variation_id;
                    }
                    $item_meta = $order->get_item_meta($item_id);
                    $class = 'new_row product_id_' . $_product_id_var;
                    require( dirname(realpath(dirname(__FILE__))) . '/views/html-admin-registers-product-item.php' );
                }
                $products = ob_get_contents();
                ob_end_clean();
            }

            $customer_email = $order->billing_email;
            $customer = '';
            $user_to_add = '';
            $guest_info = '';
            if(!empty($customer_email)){
                $user = get_user_by( 'email', $customer_email );
                if($user && !empty($user) && $user->ID){
                    $user_to_add = $user->ID;
                }else{
                    $guest_info = array(
                        'billing_country'    => $order->billing_country,
                        'billing_first_name' => $order->billing_first_name,
                        'billing_last_name'  => $order->billing_last_name,
                        'billing_company'    => $order->billing_company,
                        'billing_address_1'  => $order->billing_address_1,
                        'billing_address_2'  => $order->billing_address_2,
                        'billing_city'       => $order->billing_city,
                        'billing_state'      => $order->billing_state,
                        'billing_postcode'   => $order->billing_postcode,
                        'billing_email'      => $order->billing_email,
                        'billing_phone'      => $order->billing_phone
                    );
                }
            }
            $class = 'new_row';
            ob_start();          
            require_once( dirname(realpath(dirname(__FILE__))) . '/views/html-admin-registers-customer.php' );
            $customer = ob_get_contents();
            ob_end_clean();
            
            $loaded_info = array('products' => $products, 'customer' => $customer, 'guest_info' => $guest_info);
        die(json_encode($loaded_info));
    }
    function stripe_get_user()
    {
        $user_id   = $_POST['user_id'];
        $user_meta = array();
        $user_data = get_user_meta( $user_id );
        if($user_data && is_array($user_data) && !empty($user_data)){
            foreach ($user_data as $key => $value) {
                if(isset($value[0])){
                    $user_meta[$key] = $value[0];                
                }
            }            
        }
        die(json_encode($user_meta));
    }

    function stripe_get_outlet_address()
    {
        global $wpdb;
        $outlet_id   = $_POST['outlet_id'];
        $table_name = $wpdb->prefix . "wc_poin_of_sale_outlets";
        $db_data = $wpdb->get_results("SELECT * FROM $table_name WHERE ID = $outlet_id");
        $data;

        foreach ($db_data as $value) {
          $value->contact = (array)json_decode($value->contact);
          $data = get_object_vars($value);
        }
        die(json_encode($data));
    }

    public function json_search_variation_pr($parent_id, $v_id)
    {
        if(!$parent_id || empty($parent_id)) return false;
        if(!$v_id || empty($v_id)) return false;

        $found_products = array();

        $product = get_product( $v_id );
        $id = $v_id;

        $title   = "";
        $f_title = "";

        $tips = '<strong>' . __( 'Product ID:', 'woocommerce' ) . '</strong> ' . absint( $parent_id );
        $tips .= '<br/><strong>' . __( 'Variation ID:', 'woocommerce' ) . '</strong> ' . absint( $id );

        $sku = '';
        if ( $product && $product->get_sku() ){
          $tips    .= '<br/><strong>' . __( 'Product SKU:', 'woocommerce' ).'</strong> ' . esc_html( $product->get_sku() );
          $title   .= esc_html( $product->get_sku() ) . ' &ndash; ';
          $f_title .= esc_html( $product->get_sku() ) . ' &ndash; ';
          $sku      = esc_html( $product->get_sku() );
        }
        $title    .= '<a target="_blank" href="' . esc_url( admin_url( 'post.php?post='. absint( $id ) .'&action=edit' ) ) . '">' . esc_html( $product->post->post_title ) . '</a>';
        $f_title  .= esc_html( $product->post->post_title );


        $variation_data = array();
        if ( $product && isset( $product->variation_data ) ){
            $f_title .= ' &ndash; ';
            $f_var = '';
            $tips    .= '<br/>' . wc_get_formatted_variation( $product->variation_data, true );
            $i=0;
            
            $attributes = (array) maybe_unserialize( get_post_meta( $parent_id, '_product_attributes', true ) );


            foreach ( $product->variation_data as $names => $value ) {
                if ( ! $value ) {
                    continue;
                }
                $name = str_replace('attribute_', '', $names);
                if( isset($attributes[$name]) ){
                        
                    if($attributes[$name]['is_taxonomy']){

                        $rental_features = get_taxonomy($name);
                        $variation_data[$i][1] = $rental_features->label;

                        $post_terms = wp_get_post_terms( $parent_id, $attributes[$name]['name'] );

                        foreach ( $post_terms as $term ){
                            if( $term->slug == $value){
                                $variation_data[$i][2] = $term->name;
                                break;
                            }
                        }
                    }else{
                        $variation_data[$i][1] = $attributes[$name]['name'];
                        $variation_data[$i][2] = '';
                        $options = array_map( 'trim', explode( WC_DELIMITER, $attributes[$name]['value'] ) );
                        foreach ( $options as $option ){
                            if( sanitize_title($option) == $value){
                                $variation_data[$i][2] = $option;
                                break;
                            }
                        }
                    }                    
                }
                
                if(!empty($f_var)) $f_var .= ', ';
                $f_var .= $variation_data[$i][2];
                $i++;
            }
            $f_title .= $f_var . ' &ndash; ';
            $f_title .= wc_price( $product->get_price() );
        }
        $image = '';
        $size = 'shop_thumbnail';
        if ( has_post_thumbnail( $id ) ) {
          $thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id($id), $size );
          $image = $thumbnail[0];
        } elseif ( ( $parent_id = wp_get_post_parent_id( $id ) ) && has_post_thumbnail( $parent_id ) ) {
          $thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id($parent_id), $size );
          $image = $thumbnail[0];
        } else {
          $image = wc_placeholder_img_src();
        }
        if(!$image || $image == NULL) $image = wc_placeholder_img_src();

        $found_products['pid']         = $id;
        $found_products['title']       = $title;
        $found_products['f_title']     = $f_title;
        $found_products['stock']       = $product->get_stock_quantity();
        $found_products['sku']         = $sku;
        $found_products['price']       = $product->get_price(); 
        $found_products['f_price']     = wc_price( $product->get_price() );
        $found_products['tax']         = 0;
        $found_products['pr_inc_tax']  = $product->get_price_including_tax();
        $found_products['pr_excl_tax'] = $product->get_price_excluding_tax();
        $found_products['tip']         = $tips;
        $found_products['variation']   = json_encode($variation_data);
        $found_products['image']       = $image;
        return $found_products;
    }

    public function json_search_products_all()
    {
        check_ajax_referer( 'search-products', 'security' );
        $this->json_headers();
        $args = array(
            'post_type'      => array('product' ),
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'order'          => 'ASC',
            'orderby'        => 'ID'
        );
        
        $found_products = array();
        $posts = get_posts( $args );
         if ( $posts ) {
            foreach ( $posts as $post ) {
                $product = get_product( $post->ID );

                $id = $product->id;

                if($product->product_type == 'variable'){
                    $variations = $product->get_available_variations();

                    foreach ($variations as $key => $variation_value) {
                        if($variation_pr = $this->json_search_variation_pr($id, $variation_value['variation_id'])){
                            $found_products[$id]['children'][] = $variation_value['variation_id'];                            
                            $found_products[$variation_value['variation_id']] = $variation_pr;
                        }
                    }
                }

                $title   = "";
                $f_title = "";
                $tips = '<strong>' . __( 'Product ID:', 'woocommerce' ) . '</strong> ' . absint( $id );
                
                $sku = '';
                if ( $product && $product->get_sku() ){
                  $tips    .= '<br/><strong>' . __( 'Product SKU:', 'woocommerce' ).'</strong> ' . esc_html( $product->get_sku() );
                  $title   .= esc_html( $product->get_sku() ) . ' &ndash; ';
                  $f_title .= esc_html( $product->get_sku() ) . ' &ndash; ';
                  $sku      = esc_html( $product->get_sku() );
                }

                
                $title    .= '<a target="_blank" href="' . esc_url( admin_url( 'post.php?post='. absint( $id ) .'&action=edit' ) ) . '">' . esc_html( $product->post->post_title ) . '</a>';
                $f_title  .= esc_html( $product->post->post_title );

                $variation_data = array();
                

                $image = '';
                $size = 'shop_thumbnail';
                if ( has_post_thumbnail( $id ) ) {
                  $thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id($id), $size );
                  $image = $thumbnail[0];
                } else {
                  $image = wc_placeholder_img_src();
                }
                if(!$image || $image == NULL) $image = wc_placeholder_img_src();

                $found_products[$id]['pid']         = $id;
                $found_products[$id]['title']       = $title;
                $found_products[$id]['f_title']     = $f_title;
                $found_products[$id]['stock']       = $product->get_stock_quantity();
                $found_products[$id]['sku']         = $sku;
                $found_products[$id]['price']       = $product->get_price(); 
                $found_products[$id]['f_price']     = wc_price( $product->get_price() );
                $found_products[$id]['tax']         = 0;
                $found_products[$id]['pr_inc_tax']  = $product->get_price_including_tax();
                $found_products[$id]['pr_excl_tax'] = $product->get_price_excluding_tax();
                $found_products[$id]['tip']         = $tips;
                $found_products[$id]['variation']   = json_encode($variation_data);
                $found_products[$id]['image']       = $image;

                $attributes = (array) maybe_unserialize( get_post_meta( $id, '_product_attributes', true ) );
                $default_attributes = maybe_unserialize( get_post_meta( $id, '_default_attributes', true ) );
                
                if(!empty($attributes)){
                    $found_products[$id]['all_var'] = '';
                    foreach ( $attributes as $attribute ) {

                        if(empty($attribute))
                            continue;

                        // Only deal with attributes that are variations
                        if ( ! $attribute['is_variation'] )
                            continue;
                        

                        // Get terms for attribute taxonomy or value if its a custom attribute
                        if ( $attribute['is_taxonomy'] ) {

                            $rental_features = get_taxonomy($attribute['name']);

                            $found_products[$id]['all_var'] .= '<select data-label="'.$rental_features->label.'" ><option value="">' . __( 'No default', 'woocommerce' ) . ' ' . esc_html( wc_attribute_label( $attribute['name'] ) ) . '&hellip;</option>';

                            $post_terms = wp_get_post_terms( $post->ID, $attribute['name'] );

                            foreach ( $post_terms as $term )
                                $found_products[$id]['all_var'] .= '<option  value="' . esc_attr( $term->name ) . '">' . esc_attr( $term->name ). '</option>';

                        } else {

                            $found_products[$id]['all_var'] .= '<select data-label="'.$attribute['name'].'" ><option value="">' . __( 'No default', 'woocommerce' ) . ' ' . esc_html( wc_attribute_label( $attribute['name'] ) ) . '&hellip;</option>';

                            $options = array_map( 'trim', explode( WC_DELIMITER, $attribute['value'] ) );

                            foreach ( $options as $option )
                                $found_products[$id]['all_var'] .= '<option  value="' . esc_attr( $option ) . '">' . esc_attr( $option )  . '</option>';

                        }

                        $found_products[$id]['all_var'] .= '</select>';
                    }
                    
                }
                

                //{"pid" : 83, "title" : "Some text", "stock" : 15, "price": 3.5, "tax": 3.5, "image": "", "variation": "", "tip" : "" },
            }
        }

        $found_products = apply_filters( 'wc_pos_json_search_found_products', $found_products );

        echo json_encode( $found_products );

        die();
    }

    /**
     * Search for products and echo json
     *
     * @param string $x (default: '')
     * @param string $post_types (default: array('product'))
     */
    public function json_search_products( $x = '', $post_types = array('product') ) {

        check_ajax_referer( 'search-products', 'security' );

        $this->json_headers();

        $term = (string) wc_clean( stripslashes( $_GET['term'] ) );

        if ( empty( $term ) ) {
            die();
        }

        if ( is_numeric( $term ) ) {

            $args = array(
                'post_type'      => $post_types,
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'post__in'       => array(0, $term),
                'fields'         => 'ids'
            );

            $args2 = array(
                'post_type'      => $post_types,
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'post_parent'    => $term,
                'fields'         => 'ids'
            );

            $args3 = array(
                'post_type'      => $post_types,
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'meta_query'     => array(
                    array(
                        'key'     => '_sku',
                        'value'   => $term,
                        'compare' => 'LIKE'
                    )
                ),
                'fields'         => 'ids'
            );

            $posts = array_unique( array_merge( get_posts( $args ), get_posts( $args2 ), get_posts( $args3 ) ) );

        } else {

            $args = array(
                'post_type'      => $post_types,
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                's'              => $term,
                'fields'         => 'ids'
            );

            $args2 = array(
                'post_type'      => $post_types,
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'meta_query'     => array(
                    array(
                    'key'     => '_sku',
                    'value'   => $term,
                    'compare' => 'LIKE'
                    )
                ),
                'fields'         => 'ids'
            );

            $posts = array_unique( array_merge( get_posts( $args ), get_posts( $args2 ) ) );

        }

        $found_products = array();

        if ( $posts ) {
            foreach ( $posts as $post ) {
                $product = get_product( $post );

                $image = '';
                $size = 'shop_thumbnail';
                if ( has_post_thumbnail( $post ) ) {
                  $thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id($post), $size );
                  $image = $thumbnail[0];
                } elseif ( ( $parent_id = wp_get_post_parent_id( $post ) ) && has_post_thumbnail( $parent_id ) ) {
                  $thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id($parent_id), $size );
                  $image = $thumbnail[0];
                } else {
                  $image = wc_placeholder_img_src();
                }
                if(!$image || $image == NULL) $image = wc_placeholder_img_src();

                $found_products[ $post ]['formatted_name'] = $product->get_formatted_name();
                $found_products[ $post ]['name']           = $product->post->post_title;
                $found_products[ $post ]['image']          = $image;
            }
        }

        $found_products = apply_filters( 'wc_pos_json_search_found_products', $found_products );

        echo json_encode( $found_products );

        die();
    }
    public function find_variantion_by_attributes() {

        check_ajax_referer( 'search-products', 'security' );

        $this->json_headers();

        $attributes  = $_POST['attributes'];
        $register_id = absint($_POST['register_id']);
        $parent      = absint($_POST['parent']);

        if ( empty( $attributes ) ) {
            die();
        }
        $new_attr = array();
        foreach ($attributes as $value) {
            $new_attr['attribute_'.sanitize_title($value['name'])] = $value['option'];
        }

            $args = array(
                'post_type'      => array( 'product_variation' ),
                'posts_per_page' => -1,
                'post_status'    => 'publish',
                'order'          => 'ASC',
                'orderby'        => 'parent title',
                'post_parent'    => $parent,
            );
          
            $posts = get_posts( $args );

        $found_products = array();

        if ( $posts ) {
            foreach ( $posts as $post ) {
                $product = get_product( $post );
                
                if($new_attr == $product->variation_data){                    
                    $found_products['id'] = $post->ID;
                }

            }
        }


        echo json_encode( $found_products );
        die();
    }

    function add_product_grid(){
        check_ajax_referer( 'add-product_grid', 'security' );
        global $wpdb;
        $grid_name  = $_POST['term'];
        $grid_label = sanitize_title($grid_name);
        $sql = "SELECT COUNT(ID) FROM {$wpdb->prefix}wc_poin_of_sale_grids WHERE label = '$grid_label' ";
        $count = $wpdb->get_var($sql);
        if($count > 0)
            $grid_label = $grid_label . '-'.($count+1);
        $grid = array(
            'label'   => $grid_label,
            'name'    => $grid_name
        );
        // insert gird layout data  its table "wp_wc_poin_of_sale_grids"
        if($wpdb->insert( $wpdb->prefix . 'wc_poin_of_sale_grids', $grid )){
            do_action( 'woocommerce_grid_added', $wpdb->insert_id, $grid );
            echo $wpdb->insert_id;
            die();
        }
        
    }

}

new WC_POS_AJAX();
