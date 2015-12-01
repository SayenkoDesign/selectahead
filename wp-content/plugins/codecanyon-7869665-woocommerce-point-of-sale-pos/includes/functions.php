<?php
/**
* WoocommercePointOfSale Functions
*
* @author   Actuality Extensions
* @package  WoocommercePointOfSale/Admin/Functions
* @since    0.1
*/

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Output a text input box.
 *
 * @access public
 * @param array $field
 * @return void
 */
function wc_pos_text_input( $field ) {
	global $thepostid, $post, $woocommerce;

	$thepostid              = empty( $thepostid ) ? '' : $thepostid;
	$field['placeholder']   = isset( $field['placeholder'] ) ? $field['placeholder'] : '';
	$field['class']         = isset( $field['class'] ) ? $field['class'] : 'short';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['value']         = isset( $field['value'] ) ? $field['value'] : (!empty( $thepostid ) ? get_post_meta( $thepostid, $field['id'], true ) : '' );
	$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
	$field['type']          = isset( $field['type'] ) ? $field['type'] : 'text';
	$data_type              = empty( $field['data_type'] ) ? '' : $field['data_type'];

	$field['wrapper_tag'] 		= isset( $field['wrapper_tag'] ) ? $field['wrapper_tag'] : 'div';
	$field['wrapper_label_tag'] 		= isset( $field['wrapper_label_tag'] ) ? $field['wrapper_label_tag'] : '%s';
	$field['wrapper_field_tag'] 		= isset( $field['wrapper_field_tag'] ) ? $field['wrapper_field_tag'] : '%s';

	switch ( $data_type ) {
		case 'price' :
			$field['class'] .= ' wc_input_price';
			$field['value']  = wc_format_localized_price( $field['value'] );
		break;
		case 'decimal' :
			$field['class'] .= ' wc_input_decimal';
			$field['value']  = wc_format_localized_decimal( $field['value'] );
		break;
	}

	// Custom attribute handling
	$custom_attributes = array();

	if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) )
		foreach ( $field['custom_attributes'] as $attribute => $value )
			$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';

	$input = '<input type="' . esc_attr( $field['type'] ) . '" class="' . esc_attr( $field['class'] ) . '" name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $field['value'] ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" ' . implode( ' ', $custom_attributes ) . ' /> ';

	if ( ! empty( $field['description'] ) ) {

		if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {
			$input .= '<img class="help_tip" data-tip="' . esc_attr( $field['description'] ) . '" src="' . esc_url( WC()->plugin_url() ) . '/assets/images/help.png" height="16" width="16" />';
		} else {
			$input .= '<p class="description">' . $field['description'] . '<p>';
		}

	}

	$label = '<label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label>';
	echo '<' . $field['wrapper_tag'] . ' class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '">'. sprintf($field['wrapper_label_tag'], $label) . sprintf($field['wrapper_field_tag'], $input);


	echo '</' . $field['wrapper_tag'] . '>';
}


/**
 * Output a select input box.
 *
 * @access public
 * @param array $field
 * @return void
 */
function wc_pos_select( $field ) {
	global $thepostid, $post, $woocommerce;

	$thepostid 				= empty( $thepostid ) ? '' : $thepostid;
	$field['class'] 		= isset( $field['class'] ) ? $field['class'] : 'select short';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['value']           = isset( $field['value'] ) ? $field['value'] : (!empty( $thepostid ) ? get_post_meta( $thepostid, $field['id'], true ) : '' );
	$field['wrapper_tag'] 		= isset( $field['wrapper_tag'] ) ? $field['wrapper_tag'] : 'div';
	$field['wrapper_label_tag'] 		= isset( $field['wrapper_label_tag'] ) ? $field['wrapper_label_tag'] : '%s';
	$field['wrapper_field_tag'] 		= isset( $field['wrapper_field_tag'] ) ? $field['wrapper_field_tag'] : '%s';

	$select = '<select id="' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $field['id'] ) . '" class="' . esc_attr( $field['class'] ) . '">';
	foreach ( $field['options'] as $key => $value ) {

		$select .= '<option value="' . esc_attr( $key ) . '" ' . selected( esc_attr( $field['value'] ), esc_attr( $key ), false ) . '>' . esc_html( $value ) . '</option>';

	}
	$select .= '</select> ';

	if ( ! empty( $field['description'] ) ) {

		if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {
			$select .= '<img class="help_tip" data-tip="' . esc_attr( $field['description'] ) . '" src="' . esc_url( WC()->plugin_url() ) . '/assets/images/help.png" height="16" width="16" />';
		} else {
			$select .= '<p class="description">' .  $field['description']  . '<p>';
		}

	}

	$label = '<label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label>';

	echo '<' . $field['wrapper_tag'] . ' class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '">' . sprintf($field['wrapper_label_tag'], $label) . sprintf($field['wrapper_field_tag'], $select);



	echo '</' . $field['wrapper_tag'] . '>';
}

/**
 * Output a radio input box.
 *
 * @access public
 * @param array $field
 * @return void
 */
function wc_pos_radio( $field ) {
	global $thepostid, $post, $woocommerce;

	$thepostid 				= empty( $thepostid ) ? '' : $thepostid;
	$field['class'] 		= isset( $field['class'] ) ? $field['class'] : 'select short';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['value']           = isset( $field['value'] ) ? $field['value'] : (!empty( $thepostid ) ? get_post_meta( $thepostid, $field['id'], true ) : '' );
	$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
	$field['wrapper_tag'] 		= isset( $field['wrapper_tag'] ) ? $field['wrapper_tag'] : 'div';
	$field['wrapper_label_tag'] 		= isset( $field['wrapper_label_tag'] ) ? $field['wrapper_label_tag'] : '%s';
	$field['wrapper_field_tag'] 		= isset( $field['wrapper_field_tag'] ) ? $field['wrapper_field_tag'] : '%s';

	$label = '<label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label>';
	$inputs = '<ul class="wc-radios">';
	  foreach ( $field['options'] as $key => $value ) {

		$inputs .= '<li><label><input
			        		name="' . esc_attr( $field['name'] ) . '"
			        		value="' . esc_attr( $key ) . '"
			        		type="radio"
			        		class="' . esc_attr( $field['class'] ) . '"
			        		' . checked( esc_attr( $field['value'] ), esc_attr( $key ), false ) . '
			        		/> ' . esc_html( $value ) . '</label>
    						</li>';
		}
		$inputs .= '</ul>';
		if ( ! empty( $field['description'] ) ) {

			if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {
				$inputs .= '<img class="help_tip" data-tip="' . esc_attr( $field['description'] ) . '" src="' . esc_url( WC()->plugin_url() ) . '/assets/images/help.png" height="16" width="16" />';
			} else {
				$inputs .= '<p class="description">' . $field['description'] . '</p>';
			}

		}

	echo '<' . $field['wrapper_tag'] . ' class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '">' . sprintf($field['wrapper_label_tag'], $label) .  sprintf($field['wrapper_field_tag'], $inputs);


    echo '</' . $field['wrapper_tag'] . '>';
}

function pos_set_register_lock( $register_id ) {
	global $wpdb;

	$table_name = $wpdb->prefix . "wc_poin_of_sale_registers";

	$db_data = $wpdb->get_results("SELECT * FROM $table_name WHERE ID = $register_id");
	
	if ( !$db_data)
		return false;

	if ( 0 == ($user_id = get_current_user_id()) )
		return false;

	$now = current_time( 'mysql' );
	
	$data['opened']     = $now;
	$data['_edit_last'] = $user_id;
	$rows_affected = $wpdb->update( $table_name, $data, array( 'ID' => $register_id ) );
	return array( $now, $user_id );
}

function pos_check_register_lock( $register_id ) {
	global $wpdb;

	$table_name = $wpdb->prefix . "wc_poin_of_sale_registers";

	$db_data = $wpdb->get_results("SELECT * FROM $table_name WHERE ID = $register_id");

	if ( !$db_data)
		return false;

	$row = $db_data[0];

	$user = $row->_edit_last;

	if ( strtotime($row->opened) >= strtotime($row->closed) && $user != get_current_user_id() ){
		return $user;
	}
	return false;
}
function pos_check_register_is_open( $register_id ) {
	global $wpdb;

	$table_name = $wpdb->prefix . "wc_poin_of_sale_registers";

	$db_data = $wpdb->get_results("SELECT * FROM $table_name WHERE ID = $register_id");

	if ( !$db_data)
		return false;

	$row = $db_data[0];

	if ($row->_edit_last > 0 && strtotime($row->opened) > strtotime($row->closed))
		return true;
	else
		return false;
}
function pos_check_user_can_open_register( $register_id ) {
	global $wpdb;

	$table_name = $wpdb->prefix . "wc_poin_of_sale_registers";

	$db_data = $wpdb->get_results("SELECT * FROM $table_name WHERE ID = $register_id");

	if ( !$db_data)
		return false;

	$row = $db_data[0];

	if ( !$outlet = $row->outlet )
		return false;

	$value_user_meta = esc_attr( get_user_meta( get_current_user_id(), 'outlet', true ) );
	if($value_user_meta == $outlet) return true;
	
	return false;
}

function _admin_notice_register_locked($register_id) {
	global $wpdb;

	$table_name = $wpdb->prefix . "wc_poin_of_sale_registers";

	$db_data = $wpdb->get_results("SELECT * FROM $table_name WHERE ID = $register_id");

	if ( !$db_data)
		return;

	$user = null;
	if (  $user_id = pos_check_register_lock( $register_id ) ){

		$user = get_userdata( $user_id );
	}

	if ( $user ) {
		$locked = true;
	} else {
		$locked = false;
	}

		$sendback = admin_url( 'admin.php?page=wc_pos_registers' );

		$sendback_text = __( 'All Registers', 'wc_point_of_sale' );

	?>
	<div id="post-lock-dialog" class="notification-dialog-wrap">
	<div class="notification-dialog-background"></div>
	<div class="notification-dialog">
	<?php
	if(!pos_check_user_can_open_register( $register_id )){
		?>
		<div class="post-locked-message not_close">
		<p class="currently-editing wp-tab-first" tabindex="0">
		<?php
			_e( 'You do not have permission to access this register.', 'wc_point_of_sale' );
		?>
		</p>
		<p>
		<a class="button" href="<?php echo esc_url( $sendback ); ?>"><?php echo $sendback_text; ?></a>
		</p>
		</div>
		<?php
	}else	if ( $locked ) {

		/**
		 * Filter whether to allow the post lock to be overridden.
		 *
		 * Returning a falsey value to the filter will disable the ability
		 * to override the post lock.
		 *
		 * @since 3.6.0
		 *
		 * @param bool    $override Whether to allow overriding post locks. Default true.
		 * @param WP_Post $post     Post object.
		 * @param WP_User $user     User object.
		 */
		$override = apply_filters( 'override_register_lock', false, $register_id, $user );
		$tab_last = $override ? '' : ' wp-tab-last';

		?>
		<div class="post-locked-message not_close">
		<div class="post-locked-avatar"><?php echo get_avatar( $user->ID, 64 ); ?></div>
		<p class="currently-editing wp-tab-first" tabindex="0">
		<?php
			_e( 'This register currently has a user (' . $user->display_name . ') logged on.' );
			if ( $override )
				printf( ' ' . __( 'If you take over, %s will be blocked from continuing to edit.' ), esc_html( $user->display_name ) );
		?>
		</p>
		<p>
		<a class="button" href="<?php echo esc_url( $sendback ); ?>"><?php echo $sendback_text; ?></a>
		<?php

		// Allow plugins to prevent some users overriding the post lock
		if ( $override ) {
			?>
			<a class="button button-primary wp-tab-last" href="admin.php?page=wc_pos_registers&amp;ation=get-post-lock&amp;id=<?php echo $register_id; ?>"><?php _e('Take over'); ?></a>
			<?php
		}

		?>
		</p>
		</div>
		<?php
	} else {
		?>
		<div class="post-taken-over">
			<div class="post-locked-avatar"></div>
			<p class="wp-tab-first" tabindex="0">
			<span class="currently-editing"></span><br>
			<span class="locked-saving"><img src="<?php echo admin_url(); ?>/images/wpspin_light-2x.gif" width="16" height="16" /> <?php _e('Loading...'); ?></span>
			</p>
		</div>
		<?php
	}

	?>
	</div>
	</div>
	<?php
}

function set_outlet_taxable_address($address){
  $register_id = 0;
  if(isset($_POST['register_id']) && !empty($_POST['register_id']) ) {
  	$register_id = absint($_POST['register_id']);
  }elseif(isset($_GET['page']) && $_GET['page'] == 'wc_pos_registers' && isset($_GET['action']) && $_GET['action'] == 'view' && isset($_GET['id']) && !empty($_GET['action']) ){
  	$register_id = absint($_GET['id']);
  }
  if($register_id) {
      $id_outlet = getOutletID($register_id);

      $outlet = WC_POS()->outlet()->get_data($id_outlet);
      $address_data = $outlet[0]['contact'];
      return array( $address_data['country'], $address_data['state'], $address_data['postcode'], $address_data['city'] );
  }
  else{
      return $address;
  }
}
function isPrintReceipt($register_id = 0)
{	
	if($register_id){
		$register_data = WC_POS()->register()->get_data($register_id);
	    return $register_data[0]['settings']['print_receipt'];
	}
	return false;
}
function isNoteRequest($register_id = 0)
{	
	if($register_id){
		$register_data = WC_POS()->register()->get_data($register_id);
	    return $register_data[0]['settings']['note_request'];
	}
	return false;
}
function isEmailReceipt($register_id = 0)
{	
	if($register_id){
		$register_data = WC_POS()->register()->get_data($register_id);
		if( $register_data[0]['settings']['email_receipt'] ){
		    return array(
		    	'receipt_template' => $register_data[0]['detail']['receipt_template'],
		    	'outlet' => $register_data[0]['outlet']
		    	);
    	}
    	return false;
	}
	return false;
}

function isChangeUser($register_id = 0)
{	
	if($register_id){
		$register_data = WC_POS()->register()->get_data($register_id);
	    return $register_data[0]['settings']['email_receipt'];
	}
	return false;
}
function front_enqueue_dependencies(){
	$wc_pos_version = WC_POS()->version;
	$id             = 'wc_point_of_sale';
  $id_outlets     = 'wc_pos_outlets';
  $id_registers   = 'wc_pos_registers';
  $id_grids       = 'wc_pos_grids';
  $id_tiles       = 'wc_pos_tiles';
  $id_users       = 'wc_pos_users';
  $id_receipts    = 'wc_pos_receipts';
  $id_barcodes    = 'wc_pos_barcodes';
  $id_settings    = 'wc_pos_settings';

            wp_enqueue_script(array('jquery', 'editor', 'thickbox'));
            wp_enqueue_style('thickbox');

            wp_register_style('woocommerce_frontend_styles', plugins_url() . '/woocommerce/assets/css/admin.css');
            wp_enqueue_style('woocommerce_frontend_styles');

            wp_register_script('woocommerce_admin_crm', plugins_url() . '/woocommerce/assets/js/admin/woocommerce_admin.min.js', array('jquery', 'jquery-blockui', 'jquery-placeholder', 'jquery-ui-sortable', 'jquery-ui-widget', 'jquery-ui-core', 'jquery-tiptip'));
            wp_enqueue_script('woocommerce_admin_crm');

            wp_register_script('woocommerce_tiptip_js', plugins_url() . '/woocommerce/assets/js/jquery-tiptip/jquery.tipTip.min.js');
            wp_enqueue_script('woocommerce_tiptip_js');


            wp_register_script('postbox_', admin_url() . '/js/postbox.min.js', array(), '2.66');
            wp_enqueue_script('postbox_');

            wp_register_script('chosen_js', WC()->plugin_url() . '/assets/js/chosen/chosen.jquery.min.js', array('jquery'), '2.66');
            wp_register_script('ajax-chosen_js', WC()->plugin_url() . '/assets/js/chosen/ajax-chosen.jquery.min.js', array('jquery'), '2.66');
            wp_register_script('jquery-blockui', WC()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI.min.js', array('jquery'), '2.66');
            
            wp_register_script('accounting', WC()->plugin_url() . '/assets/js/admin/accounting.min.js', array('jquery'), '0.3.2');
            wp_register_script('round', WC()->plugin_url() . '/assets/js/admin/round.min.js', array('jquery'), WC_VERSION);
            wp_register_script('woocommerce_admin_meta_boxes', WC()->plugin_url() . '/assets/js/admin/meta-boxes.js', array('jquery'), WC_VERSION);

            wp_enqueue_script('chosen_js');
            wp_enqueue_script('ajax-chosen_js');
            
            wp_enqueue_script('jquery-blockui');
            wp_enqueue_script('accounting');
            wp_enqueue_script('round');

            wp_register_script('jquery_cycle', plugins_url('assets/plugins/jquery.cycle.all.js', realpath(dirname(__FILE__) ) ), array('jquery'), $wc_pos_version);
            wp_enqueue_script('jquery_cycle');

            wp_register_script('jquery_barcodelistener', plugins_url('assets/plugins/jquery.barcodelistener.js', realpath(dirname(__FILE__) )), array('jquery'), $wc_pos_version);
            wp_enqueue_script('jquery_barcodelistener');

            if (in_array('woocommerce-gateway-stripe/woocommerce-gateway-stripe.php', apply_filters('active_plugins', get_option('active_plugins')))){
                wp_register_script('jquery-payment', WC()->plugin_url() . '/assets/js/jquery-payment/jquery.payment.min.js', array( 'jquery' ), '1.0.2' );
                wp_enqueue_script('jquery-payment');
                wp_enqueue_script( 'stripe', 'https://js.stripe.com/v1/', '', '1.0', true );
                wp_enqueue_script( 'woocommerce_stripe', plugins_url( 'woocommerce-gateway-stripe/assets/js/stripe.js' ), array( 'stripe' ), WC_STRIPE_VERSION, true );    

                $stripe = new WC_Gateway_Stripe();

                
                $testmode              = $stripe->get_option( 'testmode' ) === "yes" ? true : false;
                $secret_key            = $stripe->testmode ? $stripe->get_option( 'test_secret_key' ) : $stripe->get_option( 'secret_key' );
                $publishable_key       = $stripe->testmode ? $stripe->get_option( 'test_publishable_key' ) : $stripe->get_option( 'publishable_key' );



                $stripe_params = array(
                    'key'        => $publishable_key,
                    'i18n_terms' => __( 'Please accept the terms and conditions first', 'woocommerce-gateway-stripe' )
                );

                wp_localize_script( 'woocommerce_stripe', 'wc_stripe_params', $stripe_params );
            }

           
            wp_register_style('woocommerce-point-of-sale-style', plugins_url('assets/css/admin.css', realpath(dirname(__FILE__) )), array(), $wc_pos_version);
            wp_enqueue_style('woocommerce-point-of-sale-style');

            wp_register_script('woocommerce-point-of-sale-script-admin', plugins_url('assets/js/admin.js', realpath(dirname(__FILE__) )), array('jquery'), $wc_pos_version);
            wp_enqueue_script('woocommerce-point-of-sale-script-admin');
            /******************/
            wp_register_script('wc_pos_cardswipe', plugins_url('assets/plugins/jquery.cardswipe.js', realpath(dirname(__FILE__) )), array('jquery', 'woocommerce-point-of-sale-script-admin'), $wc_pos_version);
            wp_enqueue_script('wc_pos_cardswipe');
            
            wp_register_script('wc_pos_payment_gateways', plugins_url('assets/js/payment_gateways.js', realpath(dirname(__FILE__) )), array('jquery', 'wc_pos_cardswipe'), $wc_pos_version);
            wp_enqueue_script('wc_pos_payment_gateways');
            /******************/

           


			wp_register_script('woocommerce-point-of-sale-script-admin1', plugins_url('assets/js/colormin.js', realpath(dirname(__FILE__) )), array('jquery'), $wc_pos_version);
      wp_enqueue_script('woocommerce-point-of-sale-script-admin1');

                //Detect special conditions devices
                $iPod    = stripos($_SERVER['HTTP_USER_AGENT'],"iPod");
                $iPhone  = stripos($_SERVER['HTTP_USER_AGENT'],"iPhone");
                $iPad    = stripos($_SERVER['HTTP_USER_AGENT'],"iPad");
                $Android = stripos($_SERVER['HTTP_USER_AGENT'],"Android");
                $webOS   = stripos($_SERVER['HTTP_USER_AGENT'],"webOS");
                $Safari   = stripos($_SERVER['HTTP_USER_AGENT'],"Safari");
                $Chrome   = stripos($_SERVER['HTTP_USER_AGENT'],"Chrome");
                
                if($Chrome === false && ($iPod !== false || $iPhone !== false  || $iPad !== false  || $webOS !== false  || $Safari !== false) ){
                    wp_register_script('woocommerce-point-of-sale-websql', plugins_url('assets/js/websql.js', realpath(dirname(__FILE__) )), array('jquery'), '45');
                    wp_enqueue_script('woocommerce-point-of-sale-websql');    
                }else{
                    wp_register_script('woocommerce-point-of-sale-indexedDB', plugins_url('assets/js/indexedDB.js', realpath(dirname(__FILE__) )), array('jquery'), '45');
                    wp_enqueue_script('woocommerce-point-of-sale-indexedDB');                    
                }

                wp_register_style('woocommerce-style', WC()->plugin_url() . '/assets/css/woocommerce-layout.css', array(), $wc_pos_version);
                wp_enqueue_style('woocommerce-style');

                wp_register_style('woocommerce-point-of-sale-jquery_plugin', plugins_url('assets/plugins/jquery_keypad/jquery.keypad.css', realpath(dirname(__FILE__) )), array(), $wc_pos_version);
                wp_enqueue_style('woocommerce-point-of-sale-jquery_plugin');

                wp_register_script('woocommerce-point-of-sale-script-jquery_keypad_plugin', plugins_url('assets/plugins/jquery_keypad/jquery.plugin.min.js', realpath(dirname(__FILE__) )), array('jquery'), $wc_pos_version);
                wp_enqueue_script('woocommerce-point-of-sale-script-jquery_keypad_plugin');

                wp_register_script('woocommerce-point-of-sale-script-jquery_keypad', plugins_url('assets/plugins/jquery_keypad/jquery.keypad.min.js', realpath(dirname(__FILE__) )), array('jquery'), $wc_pos_version);
                wp_enqueue_script('woocommerce-point-of-sale-script-jquery_keypad');
            
            if (isset($_GET['page']) && $_GET['page'] == $id_tiles && isset($_GET['grid_id']) && !empty($_GET['grid_id'])) {
                wp_register_script('woocommerce-point-of-sale-script-tile-ordering', plugins_url('assets/js/tile-ordering.js', realpath(dirname(__FILE__) )), array('jquery'), $wc_pos_version);
                wp_enqueue_script('woocommerce-point-of-sale-script-tile-ordering');
                wp_register_script('jquery_cycle', plugins_url('assets/plugins/jquery.cycle.all.js', realpath(dirname(__FILE__) )), array('jquery'), $wc_pos_version);
                wp_enqueue_script('jquery_cycle');
            }

            wp_localize_script('woocommerce-point-of-sale-script-admin', 'wc_pos_params', apply_filters('wc_pos_params', array(
                'ajax_url'        => WC()->ajax_url(),
                'admin_url'       => admin_url(),
                'ajax_loader_url' => apply_filters('woocommerce_ajax_loader_url', WC()->plugin_url() . '/assets/images/ajax-loader@2x.gif'),
                'post_id'         => isset($post->ID) ? $post->ID : '',

                'new_update_pos_outlets_address_nonce'  => wp_create_nonce("new-update-pos-outlets-address"),
                'edit_update_pos_outlets_address_nonce' => wp_create_nonce("edit-update-pos-outlets-address"),
                'search_variations_for_product'         => wp_create_nonce("search_variations_for_product"),
                'printing_receipt_nonce'                => wp_create_nonce("printing_receipt"),
                'add_product_to_register'               => wp_create_nonce("add_product_to_register"),
                'void_products_register'                => wp_create_nonce("void_products_register"),
                'remove_product_from_register'          => wp_create_nonce("remove_product_from_register"),
                'add_customers_to_register'             => wp_create_nonce("add_customers_to_register"),
                'check_shipping'                        => wp_create_nonce("check_shipping"),
                'load_order_data'                       => wp_create_nonce("load_order_data"),
                'load_pending_orders'                   => wp_create_nonce("load_pending_orders"),
                'search_products_and_variations'        => wp_create_nonce("search-products"),

                'remove_item_notice'      => __("Are you sure you want to remove the selected items?", 'wc_point_of_sale'),
                'void_register_notice'    => __("Are you sure you want to clear all fields and start from scratch?", 'wc_point_of_sale'),
                'register_discount_text'  => __("Order Discount. This is the total discount applied after tax.", 'wc_point_of_sale'),
                'product_no_sku'          => __('No SKU, for this product, please define an SKU to print barcodes.', 'wc_point_of_sale'),
                'variation_no_sku'        => __('No SKU, for this variation, please define an SKU to print barcodes.', 'wc_point_of_sale'),
                'no_default_selection'    => __('No Default Selection', 'wc_point_of_sale'),
                'open_another_tab'        => __('This register is already open in another tab.', 'wc_point_of_sale'),
                'remove_button'           => __('Remove', 'wc_point_of_sale'),
                'cannot_add_product'      => __('You cannot add that amount of "%NAME%" to the cart because there is not enough stock (%COUNT% remaining).', 'wc_point_of_sale'),
                'cannot_be_purchased'     => __('Sorry, this product cannot be purchased.', 'wc_point_of_sale'),
                
                'mon_decimal_point'            => get_option('woocommerce_price_decimal_sep'),
                'currency_format_num_decimals' => absint(get_option('woocommerce_price_num_decimals')),
                'currency_format_symbol'       => get_woocommerce_currency_symbol(),
                'currency_format_decimal_sep'  => esc_attr(stripslashes(get_option('woocommerce_price_decimal_sep'))),
                'currency_format_thousand_sep' => esc_attr(stripslashes(get_option('woocommerce_price_thousand_sep'))),

                'pos_calc_taxes'     => get_option( 'woocommerce_pos_tax_calculation'),
                'currency_format'    => esc_attr(str_replace(array('%1$s', '%2$s'), array('%s', '%v'), get_woocommerce_price_format())), // For accounting JS

                'prices_include_tax' => get_option('woocommerce_prices_include_tax'),
                'ready_to_scan'      => get_option('woocommerce_pos_register_ready_to_scan'),
                'cc_scanning'        => get_option('woocommerce_pos_register_cc_scanning'),

                'barcode_url'        => plugins_url( 'includes/classes/barcode/image.php?filetype=PNG&dpi=72&scale=2&rotation=0&font_family=Arial.ttf&&thickness=30&start=NULL&code=BCGcode128' , realpath(dirname(__FILE__) ) ), 

                'wc_api_url'  => home_url('/wc-api/v1/', 'relative'),
                
                'discount_presets' => WC_Admin_Settings::get_option( 'woocommerce_pos_register_discount_presets', array(5,10,15,20) ),
                'wc'          =>  array(
                                    'tax_label'             => WC()->countries->tax_or_vat(), 
                                    'calc_taxes'            => get_option( 'woocommerce_calc_taxes' ),
                                    'prices_include_tax'    => get_option( 'woocommerce_prices_include_tax' ),
                                    'tax_round_at_subtotal' => get_option( 'woocommerce_tax_round_at_subtotal' ),
                                    'tax_display_cart'      => get_option( 'woocommerce_tax_display_cart' ),
                                    'tax_total_display'     => get_option( 'woocommerce_tax_total_display' ),
                                ),


                
                ))
            );	
}
function wc_pos_get_register($id)
{
	global $wpdb;
	$table_name = $wpdb->prefix . "wc_poin_of_sale_registers";
	$reg = $wpdb->get_results("SELECT * FROM $table_name WHERE ID = $id");
	if(isset($reg[0]))
		return $reg[0];
	else
		return false;
}
function getOutletID($reg_id = 0)
{
		global $wpdb;
		if(!$reg_id) return 0;
		
	  $db_data = wc_pos_get_register($reg_id);
	  $data = array();
		foreach ($db_data as $value) {
		  $data[] = get_object_vars($value);
		}
		return $data[0]['outlet'];
}
?>