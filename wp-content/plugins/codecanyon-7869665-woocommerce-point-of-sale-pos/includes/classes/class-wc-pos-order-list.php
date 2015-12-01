<?php
/**
 * Table with list of customers.
 *
 * @author   Actuality Extensions
 * @package  WooCommerce_Customer_Relationship_Manager
 * @since    1.0
 */

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WP_List_Table' ) ) {
  require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WC_Crm_Order_List extends WP_List_Table {

  var $data = array();

  function __construct() {
    global $status, $page;

    parent::__construct( array(
      'singular' => __( 'order', 'wc_point_of_sale' ), //singular name of the listed records
      'plural' => __( 'orders', 'wc_point_of_sale' ), //plural name of the listed records
      'ajax' => false //does this table support ajax?

    ) );
  }
  function admin_header__() {
     die;
  }


  function no_items() {
    _e( 'No orders data found.', 'wc_point_of_sale' );
  }

  function column_default( $item, $column_name ) {
    switch ( $column_name ) {
      case 'order_status':
      case 'order_title':
      case 'order_items':
      case 'shipping_address':
      case 'customer_message':
      case 'order_notes':
      case 'order_date':
      case 'order_total':
      case 'crm_actions':
        return $item[$column_name];
      default:
        return print_r( $item, true ); //Show the whole array for troubleshooting purposes
    }
  }


  function get_columns() {
    $columns = array(
      'order_status' => '<span class="status_head tips" data-tip="' . esc_attr__( 'Status', 'woocommerce' ) . '">' . esc_attr__( 'Status', 'woocommerce' ) . '</span>',
      'order_title' => __( 'Order', 'wc_point_of_sale' ),
      'order_items' => __( 'Purchased', 'wc_point_of_sale' ),
      'shipping_address' => __( 'Ship to', 'wc_point_of_sale' ),
      'customer_message' => '<span class="notes_head tips">'.__( 'Customer Message', 'wc_point_of_sale' ).'</span>',
      'order_notes' => '<span class="order-notes_head tips">'.__( 'Order Notes', 'wc_point_of_sale' ).'</span>',
      'order_date' => __( 'Date', 'wc_point_of_sale' ),
      'order_total' => __( 'Total', 'wc_point_of_sale' ),
      'crm_actions' => __( 'Actions', 'wc_point_of_sale' ),
    );
    return $columns;
  }

  function usort_reorder( $a, $b ) {
    // If no sort, default to last purchase
    $orderby = ( !empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'order_date';
    // If no order, default to desc
    $order = ( !empty( $_GET['order'] ) ) ? $_GET['order'] : 'desc';
    // Determine sort order
    if ( $orderby == 'order_value' ) {
      $result = $a[$orderby] - $b[$orderby];
    } else {
      $result = strcmp( $a[$orderby], $b[$orderby] );
    }
    // Send final sort direction to usort
    return ( $order === 'asc' ) ? $result : -$result;
  }

 /* function column_customer_name( $item ) {
    return sprintf( '<strong>%1$s</strong>', $item['customer_name'] );
  }*/

  function column_crm_actions( $item ) {
    global $woocommerce;
      $actions = array(
        'load_order_data' => array(
          'classes' => 'load_order_data',
          'url' => '#'.$item['ID'],
          'name' => __( 'Load Order', 'wc_point_of_sale' ),
        )
      );
      echo '<p>';
      foreach ( $actions as $action ) {
        printf( '<a class="button tips %s" href="%s" data-tip="%s">%s</a>', esc_attr($action['classes']), esc_url( $action['url'] ), esc_attr( $action['name'] ), esc_attr( $action['name'] ) );
      }
      echo '</p>';
  }

  /*function column_order_value( $item ) {
    return woocommerce_price( $item['order_value'] );
  }*/

  function prepare_items($id = 0) {

    $this->data = $this->get_wooc_orders_data($id);


    $columns = $this->get_columns();
    $hidden = array();
    $sortable = $this->get_sortable_columns();
    $this->_column_headers = array($columns, $hidden, $sortable);
    usort( $this->data, array(&$this, 'usort_reorder') );

      $this->items = $this->data;

  }
  function get_wooc_orders_data($id = 0){
    global $post, $woocommerce, $the_order;

    if(   defined( 'WC_VERSION') && floatval(WC_VERSION) < 2.2 ){
      $orders_data = array();

      $CPT_Shop_Order = new WC_Admin_CPT_Shop_Order();
      
      $args = array(
        'numberposts' => -1,
        'post_type' => 'shop_order',
        'post_status' => 'publish',
        'tax_query'=>array(
          array(
            'taxonomy' => 'shop_order_status',
            'field'    => 'slug',
            'terms'    => 'pending',
          ),
        )
      );
      if($id == 0) return $orders_data;
      if($id == 'all'){

        $args['meta_key']     = 'wc_pos_id_register';
        $args['meta_value']   = '';
        $args['meta_compare'] = '!=';
      }else{
        $args['meta_key']   = 'wc_pos_id_register';
        $args['meta_value'] = $id;        
      }

      if( isset($_POST['order_id']) ) $args['post__not_in'] = array((int)$_POST['order_id']);     

      $orders = get_posts( $args );
      foreach ( $orders as $order ) {
          $id = $order->ID;


         $post = $order;

          $o['ID'] = $post->ID;
          ob_start();
            $CPT_Shop_Order->custom_columns( 'order_status' );
            $o['order_status']  = ob_get_contents();
          ob_end_clean();
          ob_start();
            $CPT_Shop_Order->custom_columns( 'order_title' );
          $o['order_title']  = ob_get_contents();
          ob_end_clean();
          ob_start();
            $CPT_Shop_Order->custom_columns( 'order_items' );
          $o['order_items']  = ob_get_contents();
          ob_end_clean();
          ob_start();
            $CPT_Shop_Order->custom_columns( 'shipping_address' );
          $o['shipping_address']  = ob_get_contents();
          ob_end_clean();
          ob_start();
            $CPT_Shop_Order->custom_columns( 'customer_message' );
          $o['customer_message']  = ob_get_contents();
          ob_end_clean();
          ob_start();
            $CPT_Shop_Order->custom_columns( 'order_notes' );
          $o['order_notes']  = ob_get_contents();
          ob_end_clean();
          ob_start();
            $CPT_Shop_Order->custom_columns( 'order_date' );
          $o['order_date']  = ob_get_contents();
          ob_end_clean();
          ob_start();
            $CPT_Shop_Order->custom_columns( 'order_total' );
          $o['order_total']  = ob_get_contents();
          ob_end_clean();
        $orders_data[] = $o;

      }
    }else{
      $orders_data = array();
      include_once( dirname(WC_PLUGIN_FILE).'/includes/admin/class-wc-admin-post-types.php' );
      $CPT_Shop_Order = new WC_Admin_Post_Types();
      $args = array(
        'numberposts' => -1,
        'post_type' => 'shop_order',
        'post_status' => 'pending',
      );

      if($id === 0){
        return $orders_data;
      } 
      if($id === 'all'){

        $args['meta_key']     = 'wc_pos_id_register';
        $args['meta_value']   = '';
        $args['meta_compare'] = '!=';
      }else{
        $args['meta_key']   = 'wc_pos_id_register';
        $args['meta_value'] = $id;        
      }

      if( isset($_POST['order_id']) ) $args['post__not_in'] = array((int)$_POST['order_id']);     

      $orders = get_posts( $args );
      foreach ( $orders as $order ) {
          $id = $order->ID;


         $post = $order;

          $o['ID'] = $post->ID;
          ob_start();
            $CPT_Shop_Order->render_shop_order_columns( 'order_status' );
            $o['order_status']  = ob_get_contents();
          ob_end_clean();
          ob_start();
            $CPT_Shop_Order->render_shop_order_columns( 'order_title' );
          $o['order_title']  = ob_get_contents();
          ob_end_clean();
          ob_start();
            $CPT_Shop_Order->render_shop_order_columns( 'order_items' );
          $o['order_items']  = ob_get_contents();
          ob_end_clean();
          ob_start();
            $CPT_Shop_Order->render_shop_order_columns( 'shipping_address' );
          $o['shipping_address']  = ob_get_contents();
          ob_end_clean();
          ob_start();
            $CPT_Shop_Order->render_shop_order_columns( 'customer_message' );
          $o['customer_message']  = ob_get_contents();
          ob_end_clean();
          ob_start();
            $CPT_Shop_Order->render_shop_order_columns( 'order_notes' );
          $o['order_notes']  = ob_get_contents();
          ob_end_clean();
          ob_start();
            $CPT_Shop_Order->render_shop_order_columns( 'order_date' );
          $o['order_date']  = ob_get_contents();
          ob_end_clean();
          ob_start();
            $CPT_Shop_Order->render_shop_order_columns( 'order_total' );
          $o['order_total']  = ob_get_contents();
          ob_end_clean();
        $orders_data[] = $o;

      }
    }


    
  return $orders_data;
  }
  function extra_tablenav( $which ) {
    if ( $which == 'top' ) {
     // do_action( 'wc_crm_restrict_list_customers' );
    }
  }

}