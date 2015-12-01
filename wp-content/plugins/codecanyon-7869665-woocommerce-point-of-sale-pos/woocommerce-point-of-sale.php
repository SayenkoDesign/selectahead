<?php
/**
 * Plugin Name: WooCommerce Point of Sale
 * Plugin URI: http://actualityextensions.com/
 * Description: WooCommerce Point of Sale is an extension which allows you to enter a customer order using the point of sale interface. This extension is suitable for retailers who have both on online and offline store.
 * Version: 2.0
 * Author: Actuality Extensions
 * Author URI: http://actualityextensions.com/
 * Tested up to: 3.7.1
 *
 * Copyright: (c) 2012-2013 Actuality Extensions
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package     WC-Point-Of-Sale
 * @author      Actuality Extensions
 * @category    Plugin
 * @copyright   Copyright (c) 2012-2013, Actuality Extensions
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly


if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))))
    return; // Check if WooCommerce is active

if (!class_exists('WoocommercePointOfSale')) {

/**
 * Main WoocommercePointOfSale Class
 *
 * @class WoocommercePointOfSale
 * @version 1.9
 */
class WoocommercePointOfSale {

    /**
     * @var string
     */
    public $version = '1.9';

    /**
     * @var string
     */
    public $db_version = '2.4';

    /**
     * @var bool
     */
    public $is_pos = false;

    /**
     * @var WoocommercePointOfSale The single instance of the class
     * @since 1.9
     */
    protected static $_instance = null;

    /**
     * The plugin's ids
     * @var string
     */
    public $id           = 'wc_point_of_sale';
    public $id_outlets   = 'wc_pos_outlets';
    public $id_registers = 'wc_pos_registers';
    public $id_grids     = 'wc_pos_grids';
    public $id_tiles     = 'wc_pos_tiles';
    public $id_users     = 'wc_pos_users';
    public $id_receipts  = 'wc_pos_receipts';
    public $id_barcodes  = 'wc_pos_barcodes';
    public $id_settings  = 'wc_pos_settings';

    /**
     * Main WoocommercePointOfSale Instance
     *
     * Ensures only one instance of WoocommercePointOfSale is loaded or can be loaded.
     *
     * @since 1.9
     * @static
     * @see WC_POS()
     * @return WoocommercePointOfSale - Main instance
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Cloning is forbidden.
     *
     * @since 1.9
     */
    public function __clone() {
        _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'woocommerce' ), '1.9' );
    }

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 1.9
     */
    public function __wakeup() {
        _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'woocommerce' ), '1.9' );
    }

    /**
     * WoocommercePointOfSale Constructor.
     * @access public
     * @return WoocommercePointOfSale
     */

    public function __construct() {

        // installation after woocommerce is available and initialized
        /*if (is_admin() && !defined('DOING_AJAX'))
            add_action('woocommerce_init', array($this, 'wc_pos_install'));
            */

        add_action('woocommerce_init', array($this, 'init'), 1);

        $this->includes();

        add_action('admin_enqueue_scripts', array($this, 'enqueue_dependencies_admin'));

        /* Change the Guest in to Walk in Customer */
        add_filter('manage_shop_order_posts_custom_column', array($this, 'pos_custom_columns'), 2);
        add_action( 'wp_trash_post', array($this, 'delete_tile'), 10 );

        if( (isset($_POST['register_id']) && !empty($_POST['register_id'])) || (isset($_GET['page']) && $_GET['page'] == 'wc_pos_registers' && isset($_GET['action']) && $_GET['action'] == 'view' && isset($_GET['id']) && !empty($_GET['action']) ) ) {
            add_filter('woocommerce_customer_taxable_address', 'set_outlet_taxable_address' );
        }

        add_action('wp_login', array( $this, 'set_last_login') );
        add_action( 'wc_pos_restrict_list_users', array( $this, 'restrict_list_users'));
        add_filter('woocommerce_attribute_label', array( $this, 'tile_attribute_label') );
        add_filter('woocommerce_get_checkout_order_received_url', array( $this, 'order_received_url') );

        add_filter( 'manage_edit-shop_order_columns', array( $this, 'add_order_type_column'), 9999);            
        add_action( 'manage_shop_order_posts_custom_column', array( $this, 'display_order_type_column'), 2 );

        /******* product_grid *********/
        add_filter( 'manage_edit-product_columns', array( $this, 'add_product_grid_column'), 9999);            
        add_action( 'manage_product_posts_custom_column', array( $this, 'display_product_grid_column'), 2 );
        add_action( 'admin_footer', array( $this, 'product_grid_bulk_actions'), 11 );
        add_action( 'load-edit.php', array( $this, 'product_grid_bulk_actions_handler') );
        /******* end product_grid *********/

        add_action( 'restrict_manage_posts', array( $this, 'restrict_manage_orders' ), 5 );
        add_filter( 'request', array( $this, 'orders_by_order_type' ));


        add_action( 'wp_head', array( $this, 'checkk' ));

        add_filter( 'woocommerce_admin_order_actions', array( $this, 'order_actions_reprint_receipts' ), 2, 20);

        // allow access to the WC REST API, init product class before serving response
        add_filter( 'woocommerce_api_check_authentication', array( $this, 'wc_api_authentication' ), 10, 1 );
        add_action( 'woocommerce_api_server_before_serve', array( $this, 'wc_api_init') );

        add_filter('woocommerce_add_cart_item', array($this, 'add_cart_item_linen_data'), 10, 2);
        add_filter('woocommerce_order_number', array($this, 'add_prefix_suffix_order_number'), 10, 2);

        add_action( 'template_redirect', array( $this, 'template_redirect' ) );

    }

    /**
     * Init POS after WooCommerce Initialises.
     */
    public function init() {
    }


    /**
    * Check if page is POS Register 
    * @since 1.9
    * @return bool
    */
    function is_pos(){
        if( isset($this->is_pos) && $this->is_pos === true ) 
            return true;
        else
            return false;
    }
    public function parse_request( $wp ) {
        if( isset( $wp->query_vars['page'] ) && $wp->query_vars['page'] == 'wc_pos_registers' && isset( $wp->query_vars['action'] ) && $wp->query_vars['action'] == 'view' ) {
            $this->is_pos = true;
        }
    }

        /**
 * Display POS page or login screen
 */
public function template_redirect() {
    // bail if not pos
    if( !$this->is_pos() ) 
        return;

    // set up $current_user for use in includes
    global $current_user;
    get_currentuserinfo();

    // check page and credentials
    if ( is_user_logged_in() ) {
        include_once( 'includes/views/html-admin-pos.php' );
        exit;
    } else {
        auth_redirect();
    }
}

    function add_query_vars( $public_query_vars ) {
        $public_query_vars[] = 'page';
        $public_query_vars[] = 'action';
        $public_query_vars[] = 'outlet';
        $public_query_vars[] = 'reg';
        return $public_query_vars;
    }
    function create_rewrite_rules($rules) {
        global $wp_rewrite;
        $newRule = array('^point-of-sale/([^/]+)/([^/]+)/?$' => 'index.php?page=wc_pos_registers&action=view&outlet=$matches[1]&reg=$matches[2]');
        $newRules = $newRule + $rules;
        return $newRules;
    }
    function on_rewrite_rule(){
        add_rewrite_rule('^point-of-sale/([^/]+)/([^/]+)/?$','index.php?page=wc_pos_registers&action=view&outlet=$matches[1]&reg=$matches[2]','top');        
        flush_rewrite_rules( false );

    }
    function set_last_login($login) {
       $user = get_userdatabylogin($login);
       update_usermeta( $user->ID, 'last_login', current_time('mysql') );
    }

    /**
     * Bypass authenication for WC REST API
     * @return WP_User object
     */
    public function wc_api_authentication( $user) {

        // get user_id from the wp logged in cookie
        $user_id = apply_filters( 'determine_current_user', false );

        // if user can manage_woocommerce_pos, open the api
        if( is_numeric($user_id) && user_can( $user_id, 'manage_woocommerce' ) ) {
            // error_log( print_R( $user, TRUE ) ); //debug
            return new WP_User( $user_id );
        } else {
            return $user;
        }
    }

    /**
     * Instantiate the Product Class when making api requests
     * @param  object $api_server  WC_API_Server Object      
     */
    public function wc_api_init( $api_server ) {

        // check both GET & POST requests
        $params = array_merge($api_server->params['GET'], $api_server->params['POST']);
        if( isset($params['action']) && $params['action'] ==  'wc_pos_json_search_products_all' ) {
            include_once( 'includes/classes/class-wc-pos-product.php' );
            $this->product = new WC_Pos_Product();
        }
    }


    

    /**
     * Enqueue admin CSS and JS dependencies
     */
    public function enqueue_dependencies_admin() {
        front_enqueue_dependencies();
    }

    function flush_rewrite_rules() {
        global $wp_rewrite;
        $wp_rewrite->flush_rules();
    }

    public function activate() {
        global $wp_rewrite;
        $this->flush_rewrite_rules();            
        $this->wc_pos_install();
    }

    public function wc_pos_install() {
        global $wpdb;

        $wpdb->hide_errors();
        $installed_ver = get_option("wc_pos_db_version");

        if ($installed_ver != $this->version) {

            $collate = '';
            if ($wpdb->has_cap('collation')) {
                if (!empty($wpdb->charset))
                    $collate .= "DEFAULT CHARACTER SET $wpdb->charset";
                if (!empty($wpdb->collate))
                    $collate .= " COLLATE $wpdb->collate";
            }

            // initial install
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            $table_name = $wpdb->prefix . "wc_poin_of_sale_outlets";
            $sql = "CREATE TABLE $table_name (
            ID        bigint(20) NOT NULL AUTO_INCREMENT,
            name      text NOT NULL,
            contact   text DEFAULT '' NOT NULL,
            social    text DEFAULT '' NOT NULL,
            PRIMARY KEY  (ID)
    )" . $collate;
            dbDelta($sql);

            $table_name = $wpdb->prefix . "wc_poin_of_sale_registers";
            $sql = "CREATE TABLE $table_name (
            ID        bigint(20) NOT NULL AUTO_INCREMENT,
            name      varchar(255) NOT NULL,
            slug      varchar(255) NOT NULL,
            detail    text DEFAULT '' NOT NULL,
            outlet    int(20) DEFAULT 0 NOT NULL,
            order_id  int(20) DEFAULT 0 NOT NULL,
            settings   text DEFAULT '' NOT NULL,
            _edit_last    int(20) DEFAULT 0 NOT NULL,
            opened timestamp NOT NULL DEFAULT current_timestamp,
            closed timestamp NOT NULL,
            PRIMARY KEY  (ID)
    )" . $collate;
            dbDelta($sql);


            $table_name = $wpdb->prefix . "wc_poin_of_sale_tiles";
            $sql = "CREATE TABLE $table_name (
            ID          bigint(20) NOT NULL AUTO_INCREMENT,
            grid_id     bigint(20) NOT NULL,
            product_id  bigint(20) NOT NULL,
            style       varchar(100) DEFAULT 'image' NOT NULL,
            colour      varchar(6) DEFAULT '000000' NOT NULL,
            background  varchar(6) DEFAULT 'ffffff' NOT NULL,
            default_selection  bigint(20) NOT NULL,
            order_position     bigint(20) NOT NULL,
            PRIMARY KEY  (ID)
    )" . $collate;
            dbDelta($sql);

            $table_name = $wpdb->prefix . "wc_poin_of_sale_grids";
            $sql = "CREATE TABLE $table_name (
            ID        bigint(20) NOT NULL AUTO_INCREMENT,
            name      varchar(255) NOT NULL,
            label     varchar(255) NOT NULL,
            PRIMARY KEY  (ID)
    )" . $collate;
            dbDelta($sql);



            $table_name = $wpdb->prefix . "wc_poin_of_sale_receipts";
            $sql = "CREATE TABLE $table_name (
            ID          bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) DEFAULT '' NOT NULL,
            print_outlet_address varchar(255) DEFAULT '' NOT NULL,
            print_outlet_contact_details varchar(255) DEFAULT '' NOT NULL,
            telephone_label text DEFAULT '' NOT NULL,
            fax_label text DEFAULT '' NOT NULL,
            email_label text DEFAULT '' NOT NULL,
            website_label text DEFAULT '' NOT NULL,
            receipt_title text DEFAULT '' NOT NULL,
            order_number_label text DEFAULT '' NOT NULL,
            order_date_label text DEFAULT '' NOT NULL,
            print_order_time varchar(255) DEFAULT '' NOT NULL,
            print_server varchar(255) DEFAULT '' NOT NULL,
            served_by_label text DEFAULT '' NOT NULL,
            tax_label text DEFAULT '' NOT NULL,
            total_label text DEFAULT '' NOT NULL,
            payment_label text DEFAULT '' NOT NULL,
            print_number_items text DEFAULT '' NOT NULL,
            items_label text DEFAULT '' NOT NULL,
            print_barcode varchar(255) DEFAULT '' NOT NULL,
            print_tax_number varchar(255) DEFAULT '' NOT NULL,
            tax_number_label text DEFAULT '' NOT NULL,
            header_text text DEFAULT '' NOT NULL,
            footer_text text DEFAULT '' NOT NULL,
            logo text DEFAULT '' NOT NULL,
            PRIMARY KEY  (ID)
    )" . $collate;
            dbDelta($sql);



            if (get_option("wc_pos_db_version")) {
                update_option("wc_pos_db_version", $this->version);
            } else {
                add_option("wc_pos_db_version", $this->version);
            }
        }
    }

    /**
     * Include required files
     */
    public function includes() {
        if (is_admin() ){
            require_once( 'includes/classes/class-wc-pos-outlets-table.php');
            require_once( 'includes/classes/class-wc-pos-registers-table.php');
            require_once( 'includes/classes/class-wc-pos-grids-table.php');
            require_once( 'includes/classes/class-wc-pos-tiles-table.php');
            require_once( 'includes/classes/class-wc-pos-users-table.php');
            require_once( 'includes/classes/class-wc-pos-receipts-table.php');
        }
        #if (is_admin() || $this->is_pos() ) {

            require_once( 'includes/functions.php' );
            require_once( 'includes/classes/class-wc-pos-outlets.php');
            require_once( 'includes/classes/class-wc-pos-registers.php');
            require_once( 'includes/classes/class-wc-pos-registers-order.php');
            require_once( 'includes/classes/class-wc-pos-grids.php');
            require_once( 'includes/classes/class-wc-pos-tiles.php');
            require_once( 'includes/classes/class-wc-pos-users.php');
            require_once( 'includes/classes/class-wc-pos-receipts.php');
            require_once( 'includes/classes/class-wc-pos-barcodes.php');
            //include_once( dirname(WC_PLUGIN_FILE).'/includes/wc-notice-functions.php' );

            require_once( 'includes/wc-pos-outlets.php' );
            require_once( 'includes/wc-pos-grids.php' );
            require_once( 'includes/wc-pos-tiles.php' );
            require_once( 'includes/wc-pos-receipt.php' );
            require_once( 'includes/wc-pos-users.php' );
            require_once( 'includes/wc-pos-barcodes.php');
            require_once( 'includes/wc-pos-settings.php');
            require_once( 'includes/wc-pos-register.php' );

            require_once( 'includes/admin-init.php' ); // Admin section


            if (defined('DOING_AJAX')) {
                $this->ajax_includes();
            }
       # }
    }

    /**
     * Include required ajax files.
     */
    public function ajax_includes() {
        include_once( 'includes/classes/class-wc-pos-ajax.php' );         // Ajax functions for admin and the front-end
    }
     /**
     * Change the Guest in to Walk in Customer
     */
    function pos_custom_columns() {
        global $post, $woocommerce, $the_order;
        if (empty($the_order) || $the_order->id != $post->ID) {
            $the_order = new WC_Order($post->ID);
        }

        if (!$the_order->billing_first_name) {

            $the_order->billing_first_name = 'Walk-in Customer';
        }
    }
    function delete_tile($pid){
        global $wpdb;
        $table_name = $wpdb->prefix . "wc_poin_of_sale_tiles";
        $query = "DELETE FROM $table_name WHERE product_id = $pid";
        $wpdb->query( $query );
    }
    function restrict_list_users()
    {
        $wc_pos_filters = array('outlets', 'usernames');
        ?>
        <div class="alignleft actions">
            <?php
                foreach ($wc_pos_filters as $value) {
                        add_action( 'wc_pos_add_filters_users', array($this, 'wc_pos_'.$value.'_filter') );
                }
                do_action( 'wc_pos_add_filters_users');
            ?>
        <input type="submit" id="post-query-submit" class="button action" value="Filter"/>
        </div>
        <?php
         $js = "
            jQuery('select#dropdown_outlets').css('width', '150px').chosen();

            jQuery('select#dropdown_usernames').css('width', '200px').ajaxChosen({
                method:         'GET',
                url:            '" . admin_url( 'admin-ajax.php' ) . "',
                dataType:       'json',
                afterTypeDelay: 100,
                minTermLength:  2,
                data:       {
                    action:     'wc_pos_json_search_usernames',
                    security:   '" . wp_create_nonce( "search-usernames" ) . "',
                    default:    '" . __( 'Show all users ', 'wc_point_of_sale' ) . "',
                }
            }, function (data) {

                var terms = {};

                $.each(data, function (i, val) {
                    terms[i] = val;
                });

                return terms;
            });
        ";
         if ( class_exists( 'WC_Inline_Javascript_Helper' ) ) {
            $woocommerce->get_helper( 'inline-javascript' )->add_inline_js( $js );
          } elseif( function_exists('wc_enqueue_js') ){
            wc_enqueue_js($js);
          }  else {
            $woocommerce->add_inline_js( $js );
          }
    }
    function wc_pos_outlets_filter() {        
        $outlet_arr = $this->outlet()->get_data_names();
        if ( isset($_POST['_outlets_filter']) && !empty( $_POST['_outlets_filter'] ) ) {
            $outlet_id = $_POST['_outlets_filter'];
        }else{
            $outlet_id = 0;
        }
        ?>
        <select id="dropdown_outlets" name="_outlets_filter">
          <option value=""><?php _e( 'Show all outlets', 'wc_point_of_sale' ) ?></option>
          <?php
          foreach ($outlet_arr as $key => $value) {
              if ( $outlet_id ) {
                echo '<option value="' . $key . '" ';
                selected( 1, 1 );
                echo '>' . $value . '</option>';
              }else{
                echo '<option value="' . $key . '" >' . $value . '</option>';
              }
          }
          ?>
        </select>
        <?php
    }
    function wc_pos_usernames_filter() {
        ?>
        <select id="dropdown_usernames" name="_usernames_filter">
          <option value=""><?php _e( 'Show all users', 'wc_point_of_sale' ) ?></option>
          <?php
          if ( !empty( $_POST['_usernames_filter'] ) ) {
            $user_id  = $_POST['_usernames_filter'];
            $userdata = get_userdata( $user_id );

            echo '<option value="' . $user_id . '" ';
            selected( 1, 1 );
            echo '>' . $userdata->user_nicename . '</option>';
          }
          ?>
        </select>
        <?php
    }
    function tile_attribute_label($label)
    {
        if(isset($_GET['page']) && $_GET['page'] == $this->id_tiles && isset($_GET['grid_id']))
            return '<strong>' . $label . '</strong>';
        else return  $label;
    }
    function order_received_url($order_received_url)
    {   
        if(  isset($_COOKIE['wc_point_of_sale_register']) ){
            require_once( 'includes/classes/class-wc-pos-outlets-table.php');

            $slug = $_COOKIE['wc_point_of_sale_register'];
            $data = $this->register()->get_data_by_slug($slug);            
            $data = $data[0];

            $slug      = $data['slug'];
            $register  = $slug;
            $outlet_id = $data['outlet'];

            $outlets_name = $this->outlet()->get_data_names();
            $outlet = sanitize_title($outlets_name[$outlet_id]);

            setcookie ("wc_point_of_sale_register", $register ,time()-3600*24*120, '/');
              $register_url = get_home_url()."/point-of-sale/$outlet/$register";

            return $register_url;
        }
        else{
            return $order_received_url;
        }
    }
    function add_order_type_column($columns)
    {
        $new_columns = array();
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            if($key == 'order_status')
                $new_columns['wc_pos_order_type'] = __( '<span class="order-type tips" data-tip="Order Type">Order Type</span>', 'wc_point_of_sale' );
        }
        return $new_columns;
    }
    
    function display_order_type_column($column)
    {
        global $post, $woocommerce, $the_order;

            if ( empty( $the_order ) || $the_order->id != $post->ID )
                $the_order = new WC_Order( $post->ID );

            if ( $column == 'wc_pos_order_type' ) {
                $order_type = __( '<span class="order-type-web tips" data-tip="Website Order">web<span>', 'wc_point_of_sale' );
                $amount_change = get_post_meta( $the_order->id, 'wc_pos_order_type', true );
                if($amount_change) $order_type = __( '<span class="order-type-pos tips" data-tip="Point of Sale Order">pos<span>', 'wc_point_of_sale' );
                echo $order_type;
            }
    }
    /******* product_grid *********/
    function add_product_grid_column($columns)
    {
        $new_columns = array();
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            if($key == 'product_tag')
                $new_columns['wc_pos_product_grid'] = __( 'Product Grid', 'wc_point_of_sale' );
        }
        return $new_columns;
    }
    
    function display_product_grid_column($column)
    {
        global $post, $woocommerce;
        if ( $column == 'wc_pos_product_grid' ) {
            $product_id = $post->ID;
            $grids      = wc_point_of_sale_get_grids_names_for_product($product_id);
            $links      = array();
            if(!empty($grids)){
              foreach ($grids as $id => $name) {
                $url = admin_url( 'admin.php?page=wc_pos_tiles&grid_id=').$id;
                $links[] = '<a href="'.$url.'">'.$name.'</a>';
              }
              echo implode(', ', $links);
            }else{
              echo '<span class="na">–</span>';
            }
        }
    }

    function product_grid_bulk_actions(){
      global $post_type;
      if ( 'product' == $post_type ) {
      ?>
         <script type="text/javascript">
             jQuery(document).ready(function() {
                  <?php
                  $grids = wc_point_of_sale_get_grids();
                  if(!empty($grids)){
                   foreach($grids as $grid){ ?>
                       jQuery('<option>').val('wc_pos_add_to_grid_<?php echo $grid->ID; ?>')
                            .text('<?php printf( __( "Add to %s", "wc_point_of_sale" ), $grid->name ); ?>').appendTo('select[name=action]');
                       jQuery('<option>').val('wc_pos_add_to_grid_<?php echo $grid->ID; ?>')
                            .text('<?php printf( __( "Add to %s", "wc_point_of_sale" ), $grid->name ); ?>').appendTo('select[name=action2]');
                    <?php
                    }
                  }
                 ?>
              });
        </script>
      <?php
      }
    }

    function product_grid_bulk_actions_handler(){
      if(!isset($_REQUEST['post'])){
        return;
      }
      $wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
      $action = $wp_list_table->current_action();
      
      global $wpdb;
      $changed = 0;
      $post_ids = array_map( 'absint', (array) $_REQUEST['post'] );
      if(strstr($action,'wc_pos_add_to_grid_')) {
        $grid_id = (int)substr($action,strlen('wc_pos_add_to_grid_'));
        $report_action = "products_added_to_grid";
        foreach( $post_ids as $post_id ) {
            if(!product_in_grid($post_id, $grid_id)){
              $order_position = 1;
              $position = get_last_position_of_tile($grid_id);
              if(!empty($position->max)) $order_position = $position->max + 1;
              $data = array(
                'grid_id'           => $grid_id,
                'product_id'        => $post_id,
                'colour'            => 'ffffff',
                'background'        => '8E8E8E',
                'default_selection' => 0,
                'order_position'    => $order_position,
                'style'             => 'image'
              );
              $wpdb->insert( $wpdb->prefix.'wc_poin_of_sale_tiles', $data );
              $changed++;
            }
        }
      } else{
          return;
      }
      $sendback = add_query_arg( array( 'post_type' => 'product', $report_action => $changed, 'ids' => join( ',', $post_ids ) ), '' );
      wp_redirect( $sendback );
      exit();
    }
    /******* end product_grid *********/


    function restrict_manage_orders($value='')
    {
        global $woocommerce, $typenow, $wp_query;
        if ( 'shop_order' != $typenow ) {
            return;
        }
        // Status
        ?>
        <select name='shop_order_wc_pos_order_type' id='dropdown_shop_order_wc_pos_order_type'>
            <option value=""><?php _e( 'Show all types', 'wc_point_of_sale' ); ?></option>
            <?php if(isset($wp_query->query['meta_key']) && $wp_query->query['meta_key'] == 'wc_pos_order_type' && isset($wp_query->query['meta_value']) && $wp_query->query['meta_value'] == 'POS' ) { ?>
                <option value="online"><?php _e( 'Online', 'wc_point_of_sale' ); ?></option>
                <option value="POS" selected="selected"><?php _e( 'POS', 'wc_point_of_sale' ); ?></option>
            <?php } elseif( isset($wp_query->query['meta_key']) && $wp_query->query['meta_key'] == 'wc_pos_order_type' && isset($wp_query->query['meta_compare']) && $wp_query->query['meta_compare'] == 'NOT EXISTS') {?>
                <option value="online" selected="selected" ><?php _e( 'Online', 'wc_point_of_sale' ); ?></option>
                <option value="POS"><?php _e( 'POS', 'wc_point_of_sale' ); ?></option>
            <?php
            }else{ ?>
                <option value="online"><?php _e( 'Online', 'wc_point_of_sale' ); ?></option>
                <option value="POS"><?php _e( 'POS', 'wc_point_of_sale' ); ?></option>
            <?php } ?>
        </select>
        <?php
        wc_enqueue_js( "
            jQuery('select#dropdown_shop_order_wc_pos_order_type').css('width', '150px').chosen();
        ");

    }
    public function orders_by_order_type( $vars ) {
        global $typenow, $wp_query;
        if ( $typenow == 'shop_order' && isset( $_GET['shop_order_wc_pos_order_type'] ) && $_GET['shop_order_wc_pos_order_type'] != '' ) {
            if($_GET['shop_order_wc_pos_order_type'] == 'POS'){
                $vars['meta_key'] = 'wc_pos_order_type';
                $vars['meta_value'] = 'POS';
            }elseif($_GET['shop_order_wc_pos_order_type'] == 'online'){
                $i = count($vars['meta_query']);
                $vars['meta_key'] = 'wc_pos_order_type';
                $vars['meta_compare'] = 'NOT EXISTS';
            }
            
        }

        return $vars;
    }
    function order_actions_reprint_receipts($actions, $the_order){
        $amount_change = get_post_meta( $the_order->id, 'wc_pos_order_type', true );
        $id_register = get_post_meta( $the_order->id, 'wc_pos_id_register', true );
        if($amount_change && $id_register){
            $data = $this->register()->get_data($id_register);
            if(!empty($data) && !empty($data[0])){
                $data = $data[0];
                $actions['reprint_receipts'] = array(
                    'url'       => '#'.$the_order->id.'#'.$data['detail']['receipt_template'].'#'.$data['outlet'].'#'.$data['ID'],
                    'name'      => __( 'Reprint receipts', 'wc_point_of_sale' ),
                    'action'    => "reprint_receipts"
                );    
            }
            
        }
        
        return $actions;
    }

    function add_cart_item_linen_data($cart_item_data, $cart_item_key) {
        
        if (isset($_POST['action']) && $_POST['action'] == 'save-wc-pos-registers-as-order') {
            if( isset($cart_item_data['variation_id']) ){
                $v_id = $cart_item_data['variation_id'];
                if (isset($_POST['variations']) && !empty( $_POST['variations'] ) && $_POST['variations'][$v_id] ) {
                    foreach ($_POST['variations'][$v_id] as $attr => $value) {

                        if(!isset($cart_item_data['data']->variation))
                            $cart_item_data['data']->variation = array();

                        if(!isset($cart_item_data['data']->variation_data))
                            $cart_item_data['data']->variation_data = array();

                        if(!isset($cart_item_data['data']->product_custom_fields))
                            $cart_item_data['data']->product_custom_fields = array();

                        $cart_item_data['data']->variation[$attr] = $value;
                        $cart_item_data['data']->variation_data[$attr] = $value;
                        $cart_item_data['data']->product_custom_fields[$attr] = array($value); 
                    }                    
                } 
            }               
        }
        return $cart_item_data;
    }

    function checkk()
    {

        if (get_the_ID () == get_option ( 'woocommerce_checkout_page_id' , 0 ) ) {
           setcookie ("wc_point_of_sale_register", '' ,time()-3600*24*120, '/');
        }
    }
    function add_prefix_suffix_order_number($order_id, $order)
    {
        $redister_id = get_post_meta($order->id, 'wc_pos_id_register', true);
        
        if($redister_id){
            $reg = wc_pos_get_register($redister_id);
            if($reg){
                $detail = (array)json_decode($reg->detail);
                if(is_array($detail)){
                    $order_id = '#' . $detail['prefix'] . $order->id . $detail['suffix'];
                }
            }
        }
        return $order_id;
    }

    /** Helper functions ******************************************************/

    /**
     * Get the plugin url.
     *
     * @return string
     */
    public function plugin_url() {
        return untrailingslashit( plugins_url( '/', __FILE__ ) );
    }

    /**
     * Get the plugin path.
     *
     * @return string
     */
    public function plugin_path() {
        return untrailingslashit( plugin_dir_path( __FILE__ ) );
    }

    /**
     * Get the plugin path.
     *
     * @return string
     */
    public function plugin_views_path() {
        return untrailingslashit( plugin_dir_path( __FILE__ ).'/includes/views' );
    }

    /**
     * Get Outlets class
     *
     * @since 1.9
     * @return WC_Pos_Outlets
     */
    public function outlet() {
        return WC_Pos_Outlets::instance();
    }

    /**
     * Get Outlets table class
     *
     * @since 1.9
     * @return WC_Pos_Outlets_Table
     */
    public function outlet_table() {
        return new WC_Pos_Outlets_Table;
    }

    /**
     * Get Registers class
     *
     * @since 1.9
     * @return WC_Pos_Registers
     */
    public function register() {
        return WC_Pos_Registers::instance();
    }

    /**
     * Get Registers Table class
     *
     * @since 1.9
     * @return WC_Pos_Registers_Table
     */
    public function registers_table() {
        return new WC_Pos_Registers_Table;
    }


    /**
     * Get Grids class
     *
     * @since 1.9
     * @return WC_Pos_Grids
     */
    public function grid() {
        return WC_Pos_Grids::instance();
    }

    /**
     * Get Grids Table class
     *
     * @since 1.9
     * @return WC_Pos_Grids_Table
     */
    public function grids_table() {
        return new WC_Pos_Grids_Table;
    }

    /**
     * Get Tiles class
     *
     * @since 1.9
     * @return WC_Pos_Tiles
     */
    public function tile() {
        return WC_Pos_Tiles::instance();
    }
    /**
     * Get Tiles Table class
     *
     * @since 1.9
     * @return WC_Pos_Tiles_Table
     */
    public function tiles_table() {
        return new WC_Pos_Tiles_Table;
    }

    /**
     * Get Users class
     *
     * @since 1.9
     * @return WC_Pos_Users
     */
    public function user() {
        return WC_Pos_Users::instance();
    }

    /**
     * Get Users Table class
     *
     * @since 1.9
     * @return WC_Pos_users_Table
     */
    public function users_table() {
        return new WC_Pos_users_Table;
    }

    /**
     * Get Receipts class
     *
     * @since 1.9
     * @return WC_Pos_Receipts
     */
    public function receipt() {
        return WC_Pos_Receipts::instance();
    }

    /**
     * Get Receipts Table class
     *
     * @since 1.9
     * @return WC_Pos_Receipts_Table
     */
    public function receipts_table() {
        return new WC_Pos_Receipts_Table();
    }

    /**
     * Get Barcodes class
     *
     * @since 1.9
     * @return WC_Pos_Barcodes
     */
    public function barcode() {
        return WC_Pos_Barcodes::instance();
    }

}
/**
 * Returns the main instance of WoocommercePointOfSale to prevent the need to use globals.
 *
 * @since  1.9
 * @return WoocommercePointOfSale
 */
function WC_POS() {
    return WoocommercePointOfSale::instance();
}

// Global for backwards compatibility.
global $wc_point_of_sale, $wc_pos_db_version;

$wc_pos_db_version      = WC_POS()->db_version;
$wc_point_of_sale       = WC_POS();
$GLOBALS['woocommerce'] = WC_POS();




    register_activation_hook( __file__, array($wc_point_of_sale, 'activate') );


    add_filter('rewrite_rules_array', array($wc_point_of_sale, 'create_rewrite_rules'));
    add_filter('query_vars',array($wc_point_of_sale, 'add_query_vars'));
    add_action( 'parse_request', array( $wc_point_of_sale, 'parse_request' ) );

    add_filter('admin_init', array($wc_point_of_sale, 'flush_rewrite_rules'));
}