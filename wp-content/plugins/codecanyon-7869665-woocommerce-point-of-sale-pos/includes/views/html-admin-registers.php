<?php
/**
 * HTML for a view registers page in admin.
 *
 * @author   Actuality Extensions
 * @package  WoocommercePointOfSale/views
 * @since    0.1
 */
$data = array();

WC_POS()->register()->init_form_fields();

if ($id) {
    $reg_slug = $id;
    $data = WC_POS()->register()->get_data_by_slug($id);
    $data = $data[0];
    foreach ($data['detail'] as $i => $val) {
        $data[$i] = $val;
    }
    foreach ($data['settings'] as $i => $val) {
        $data[$i] = $val;
    }
}

if (!pos_check_register_is_open($data['ID'])) {
    pos_set_register_lock($data['ID']);
}
$error_string = '';

$detail_fields    = WC_POS()->register()->get_register_detail_fields();
$detail_data      = $data['detail'];

$grid_template    = $detail_fields['grid_template']['options'][$detail_data['grid_template']];
$receipt_template = $detail_fields['receipt_template']['options'][$detail_data['receipt_template']];


if(!$grid_template)
  $error_string   .= '<p>No product grid assigned.</p>';
if(!$receipt_template)
  $error_string   .= '<b>Receipt Template </b> is required<br>';

$outlets_name = WC_POS()->outlet()->get_data_names();

if(!$outlets_name[$data['outlet']])
  $error_string   .= '<b>Outlet </b> is required<br>';

if(!empty($error_string)){ ?>
<div id="post-lock-dialog" class="notification-dialog-wrap<?php echo $hidden; ?>">
    <div class="notification-dialog-background"></div>
    <div class="notification-dialog">
    <div class="post-locked-message not_close">
        <p class="currently-editing wp-tab-first" tabindex="0"><?php echo $error_string; ?></p>
        <p>
        <a class="button" href="admin.php?page=wc_pos_registers&amp;action=edit&amp;id=<?php echo $data['ID']; ?> "><?php  _e( 'Edit Register' ); ?></a>
                </p>
    </div>
    </div>
</div>
<?php
}else{
    _admin_notice_register_locked($data['ID']);
}
?>

<style>
    #adminmenuback{
        display: none;
    }
    #adminmenuwrap{
        display: none;
    }
    #wpcontent{
        margin-left: 0px;
    }
    #wpbody-content {
	    padding: 0px;
    }
</style>
<div class="wrap" id="wc-pos-registers-edit">
    <h2>
        <?php _e('Register', 'wc_point_of_sale'); ?> - <?php echo $data['name']; ?>
        <a class="add-new-h2" href="#" id="retrieve_sales"> <?php _e('Retrieve Sales', 'wc_point_of_sale'); ?> </a>
        <a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), '/');?>admin.php?page=wc_pos_registers&amp;close=<?php echo $data['ID']; ?>" id="close_register"> <?php _e('Close Register', 'wc_point_of_sale'); ?> </a>
    </h2>
    <?php
    if (wc_notice_count('error') > 0) {
        $all_notices = WC()->session->get('wc_notices', array());
        $messages = $all_notices['error'];
        $e = '';
        foreach ($messages as $message) : if (empty($message))
                continue;
            $e .= '<p>' . wp_kses_post($message) . '</p>';
        endforeach;
        if (!empty($e))
            echo '<div class="error" id="message" style="display: block;">' . $e . '</div>';
    }

    if (wc_notice_count('success') > 0) {
        $all_notices = WC()->session->get('wc_notices', array());
        $messages = $all_notices['success'];
        $s = '';
        foreach ($messages as $message) : if (empty($message))
                continue;
            $s .= '<p>' . wp_kses_post($message) . '</p>';
        endforeach;
        if (!empty($s))
            echo '<div class="updated below-h2" id="message" style="display: block;">' . $s . '</div>';
    }

    if($data['print_receipt']){
        if (wc_notice_count('printing') > 0) {
            $all_notices = WC()->session->get('wc_notices', array());
            $messages = $all_notices['printing'];
            $s = '';
            foreach ($messages as $message) : if (empty($message))
                    continue;
                $s .= '<p>' . $message . '</p>';
            endforeach;
            if (!empty($s)){
                ?>
                <div class="notification-dialog-wrap" id="printing_receipt">
                    <div class="notification-dialog-background"></div>
                    <div class="notification-dialog">
                        <div class="post-locked-message">
                                <?php echo $s; ?>
                        </div>
                    </div>
                </div>
                <?php
            }
        }elseif( isset($_COOKIE['wc_point_of_sale_printing']) ){
            $printing_order_id = $_COOKIE['wc_point_of_sale_printing'];
            $order__ = new WC_Order( $printing_order_id );
            $order__->update_status( 'completed', __( 'Point of Sale transaction completed. ', 'woocommerce' ) );
                ?>
                <div class="notification-dialog-wrap" id="printing_receipt">
                    <div class="notification-dialog-background"></div>
                    <div class="notification-dialog">
                        <div class="post-locked-message">
                                <?php echo __( 'Printing&hellip;', 'wc_point_of_sale' ).'<input type="hidden" id="print_order_id" value="'.$printing_order_id.'" />'; ?>
                        </div>
                    </div>
                </div>
                <?php
        }
    }
    wc_clear_notices();
    if($data['change_user']){
        ?>
        <script>
            jQuery('document').ready(function($) {                
                window.onafterprint =  afterPrint;
                if ('matchMedia' in window) {
                    window.matchMedia('print').addListener(function(media) {
                        if (!media.matches) {
                            $(document).one('mouseover', afterPrint );
                        }
                    });
                }
            });
            function afterPrint() {
                window.location.href= "<?php echo admin_url( 'admin.php?page=wc_pos_registers&logout='.$data['ID'] ); ?>";
            }
        </script>
        <?php
    }

    if ( get_post_status ( $data['order_id'] ) != 'publish' || get_post_type( $data['order_id'] ) != 'pos_temp_register_or') {
      $data['order_id'] = 0;
    }
    $data['order_id'] = $data['order_id'] != 0 ? $data['order_id'] : WC_POS()->register()->crate_order_id($data['ID']);
    ?>
    <div class="error below-h2" id="message_pos" style=""></div>
    <div id="ajax-response"></div>
    <?php
    $action_url = admin_url( 'admin.php?page=wc_pos_registers&action=view&outlet=' . $_GET['outlet'] . '&reg='. $_GET['reg']);
    ?>
    <form id="edit_wc_pos_registers" class="validate" action="<?php echo $action_url; ?>" method="post" autocomplete="off">
        <input type="hidden" value="save-wc-pos-registers-as-order" name="action">
        <input type="hidden" value="<?php echo $data['ID']; ?>" name="id_register" id="id_register">
        <input type="hidden" value="<?php echo $data['order_id']; ?>" name="id" id="order_id">
        <input type="hidden" value="<?php echo $data['receipt_template']; ?>" id="print_receipt_ID">
        <input type="hidden" value="<?php echo $data['outlet']; ?>" id="outlet_ID">
        <?php wp_nonce_field('nonce-save-wc-pos-registers-as-order', '_wpnonce_save-wc-pos-registers-as-order'); ?>
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <div id="postbox-container-1" class="postbox-container">
                    <div id="wc-pos-register-grids" class="postbox ">
                    <?php
                    $pos_layout = get_option('woocommerce_pos_register_layout', 'product_grids');
                    if($pos_layout == 'product_grids') :
                    $grid_id = $data['grid_template'];
                    $grids_single_record = wc_point_of_sale_tile_record($grid_id);
                    $grids_all_record    = wc_point_of_sale_get_all_grids($grid_id);
                    ?>
                        <h3 class="hndle">
                            <span id="wc-pos-register-grids-title"><?php _e( ucfirst($grids_single_record[0]->name).' Layout', 'wc_point_of_sale' ) ?></span>
                            <div class="clear"></div>
                        </h3>
                        <div class="inside" id="grid_layout_cycle">
                            <?php 
                            the_grid_layout_cycle($grids_single_record[0]);
                            if (!empty($grids_all_record) ) 
                                foreach ($grids_all_record as $grid)
                                    the_grid_layout_cycle($grid);
                            ?>
                        </div>
                        <div class="previous-next-toggles">
                            <span class="previous-grid-layout tips" data-tip="<?php _e('Previous', 'wc_point_of_sale'); ?>"></span>
                            <div id="nav_layout_cycle"></div>
                            <span class="next-grid-layout tips" data-tip="<?php _e('Next', 'wc_point_of_sale'); ?>"></span>
                        </div>
                    <?php else: ?>
                        <div class="inside" id="grid_layout_cycle">
                        <?php if($pos_layout == 'company_image'){ 
                            $woocommerce_pos_company_logo = get_option('woocommerce_pos_company_logo', '');
                            $src = '';
                            if(!empty($woocommerce_pos_company_logo) ){
                                $src = wp_get_attachment_image_src( $woocommerce_pos_company_logo, 'full' );
                                $src = $src[0];
                            }
                            ?>
                            <div class="grid_logo" style="height: 100%; ">
                                <img src="<?php echo $src; ?>" alt="">
                            </div>
                        <?php } elseif($pos_layout == 'text'){ ?>
                            <div class="grid_text" style="height: 100%; ">
                                <?php echo get_option('woocommerce_pos_register_layout_text', ''); ?>                                
                            </div>
                        <?php } elseif ($pos_layout == 'company_image_text'){ 
                            $woocommerce_pos_company_logo = get_option('woocommerce_pos_company_logo', '');
                            $src = '';
                            if(!empty($woocommerce_pos_company_logo) ){
                                $src = wp_get_attachment_image_src( $woocommerce_pos_company_logo, 'full' );
                                $src = $src[0];
                            }
                            ?>
                            <div class="grid_logo" style="height: 33%; ">
                                <img src="<?php echo $src; ?>" alt="">
                            </div>
                            <div class="grid_text" style="height: 67%; ">
                                <?php echo get_option('woocommerce_pos_register_layout_text', ''); ?>                                
                            </div>
                        <?php } ?>
                        </div>                            
                    <?php
                    endif; ?>
                    </div>
                    <div id="wc-pos-register-buttons" class="postbox ">
                        <div class="register_buttons">
                            <p>
                                <button class="button tips wc_pos_register_void button-primary" type="button" data-tip="<?php _e('Void Order', 'wc_point_of_sale'); ?>"><?php _e('Void', 'wc_point_of_sale'); ?></button>
                                <button class="button tips wc_pos_register_save" type="submit" data-tip="<?php _e('Save Order', 'wc_point_of_sale'); ?>"><?php _e('Save', 'wc_point_of_sale'); ?></button>
                                <button class="button tips wc_pos_register_notes" type="button" data-tip="<?php _e('Add A Note', 'wc_point_of_sale'); ?>"><?php _e('Note', 'wc_point_of_sale'); ?></button>
                                <button class="button tips wc_pos_register_discount" type="button" data-tip="<?php _e('Apply Discount', 'wc_point_of_sale'); ?>"><?php _e('Discount', 'wc_point_of_sale'); ?></button>
                                <button class="button tips wc_pos_register_pay button-primary" type="button" data-tip="<?php _e('Accept Payment', 'wc_point_of_sale'); ?>"><?php _e('Pay', 'wc_point_of_sale'); ?></button>
                            </p>
                        </div>
                    </div>
                </div>
                <div id="postbox-container-2" class="postbox-container">
                    <div id="wc-pos-register-data" class="postbox ">
                        <div class="hndle">
                            <p class="add_items">
                                <select id="add_product_id" class="ajax_chosen_select_products_and_variations" data-placeholder="<?php _e('Search or Scan Products', 'wc_point_of_sale'); ?>" multiple="multiple">
                                </select>
                            </p>
                            <span class="clearfix"></span>
                        </div>
                        <div class="inside">
                            <div class="woocommerce_order_items_wrapper">
                                <table class="woocommerce_order_items" cellspacing="0" cellpadding="0">
                                    <thead>
                                        <tr>
                                            <th colspan="2" class="item">Item</th>
                                            <th class="quantity">Qty</th>
                                            <th class="line_cost">Total</th>
                                            <th class="line_remove">&nbsp;</th>
                                        </tr>
                                    </thead>
                                    <tbody id="order_items_list">
                                        <?php
                                        $order = new WC_Order($data['order_id']);

                                        $order_items = $order->get_items(apply_filters('woocommerce_admin_order_item_types', array('line_item', 'fee')));
                                        foreach ($order_items as $item_id => $item) {

                                            $_product = $order->get_product_from_item($item);
                                            $price    = $_product->get_price();


                                            $_product_id_var = $_product->id;
                                            if(!empty($_product->variation_id)){
                                                $_product_id_var = $_product->variation_id;
                                            }
                                            $item_meta = $order->get_item_meta($item_id);
                                            $class = 'product_id_' . $_product_id_var;
                                            include( 'html-admin-registers-product-item.php' );
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="wc_pos_register_subtotals">
                                <table class="woocommerce_order_items" cellspacing="0" cellpadding="0">
                                    <tr id="tr_order_subtotal_label">
                                        <th class="subtotal_label"><?php _e('Subtotal', 'wc_point_of_sale'); ?></th>
                                        <td class="subtotal_amount"><strong id="subtotal_amount"></strong></td>
                                    </tr>
                                    <?php /********************************/ ?>
                                    <tr class="shipping_methods_register">
                                        <th></th>
                                        <td></td>
                                    </tr>
                                    <?php /********************************/ ?>
                                    <?php
                                    $wc_calc_taxes   = get_option('woocommerce_calc_taxes', 'no');
                                    $wc_pos_tax_calculation = get_option('woocommerce_pos_tax_calculation', 'disabled');
                                    if($wc_calc_taxes == 'yes' && $wc_pos_tax_calculation == 'enabled'){
                                    ?>
                                    <tr class="tax_row">
                                        <th class="tax_label"><?php _e('Tax', 'wc_point_of_sale'); ?></th>
                                        <td class="tax_amount"><strong id="tax_amount"></strong></td>
                                    </tr>
                                    <?php
                                    }

                                     if ($d = $order->get_order_discount()) { ?>
                                        <tr id="tr_order_discount">
                                            <th class="total_label">Order Discount</th>
                                            <td class="total_amount">
                                                <input type="hidden" value="<?php echo $d; ?>" id="order_discount" name="order_discount">
                                                <strong id="formatted_order_discount"><?php echo wc_price($d, array('currency' => $order->get_order_currency())); ?></strong>
                                                <span id="span_clear_order_discount"></span>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    <tr id="tr_order_total_label">
                                        <th class="total_label"><?php _e('Total', 'wc_point_of_sale'); ?></th>
                                        <td class="total_amount"><strong id="total_amount"></strong></td>
                                    </tr>
                                    
                                </table>
                            </div>
                        </div>
                    </div>
                    <div id="wc-pos-customer-data" class="postbox ">
                        <div class="hndle">
                            <p class="add_items">
                                <select id="customer_user" class="ajax_chosen_select_customer">
                                    <option value=""><?php _e('Search customer…', 'wc_point_of_sale'); ?></option>
                                </select>
                                <a title="<?php _e('Add Customer', 'wc_point_of_sale'); ?>" class="tips" id="add_customer_to_register" type="button" data-tip="<?php _e('Add Customer', 'wc_point_of_sale'); ?>"><span><?php _e('', 'wc_point_of_sale'); ?></span></a>

                            </p>
                            <span class="clearfix"></span>
                        </div>
                        <div class="inside">
                            <div class="woocommerce_order_items_wrapper">
                                <table class="woocommerce_order_items" cellspacing="0" cellpadding="0">
                                    <thead>
                                        <tr>
                                            <th class="customer"><?php _e('Customer Name', 'wc_point_of_sale'); ?></th>
                                            <th width="1%">&nbsp;</th>
                                        </tr>
                                    </thead>
                                    <tbody id="customer_items_list">
                                        <?php
                                        $user_to_add = ( $value = get_post_meta($data['order_id'], '_customer_user', true) ) ? $value : '';
                                        require_once( 'html-admin-registers-customer.php' );
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
        <div class="overlay_order_popup" id="overlay_order_comments">
            <div id="order_comments_popup">
            <div class="media-frame-title">
				<h1><?php _e('Notes', 'woocommerce'); ?></h1>
			</div>
                <textarea name="order_comments" id="order_comments"><?php echo $order->customer_note; ?></textarea>
                <button class="button button-primary" type="button" id="save_order_comments">Add Note</button>
                <span class="close_popup"></span>
            </div>
        </div>
        <div class="overlay_order_popup" id="overlay_order_discount">
            <div id="order_discount_popup">
                <div class="media-frame-title" style="position: initial;">
                    <h1><?php _e('Discount', 'wc_point_of_sale'); ?></h1>
                </div>
                <div class="order-discount-field">
                <p><?php _e('This is the total discount (% or '.get_woocommerce_currency_symbol().') applied after tax.', 'wc_point_of_sale'); ?></p>
                <input type="text" id="order_discount_prev" value="<?php echo ($order->get_order_discount() > 0 ) ? $order->get_order_discount() : ''; ?>" placeholder="<?php _e('20% or 2.50', 'wc_point_of_sale'); ?>">
                <input type="hidden" id="order_discount_symbol" value="currency_symbol">
                </div>
                <div class="button_group">
                    <button class="button" type="button" id="clear_order_discount"><?php _e('Clear', 'wc_point_of_sale'); ?></button>
                    <button class="button button-primary" type="button" id="save_order_discount"><?php _e('Add Discount', 'wc_point_of_sale'); ?></button>
                </div>
                <span class="close_popup"></span>
            </div>
        </div>
        <!-- Payments Popup Box -->

        <div class="overlay_order_popup" id="overlay_order_payment">
            <div id="order_payment_popup" class="payment_popup_background_none">

                 <div id="payment">

                   <div class="pop5wrp">
                                    <div class="pop5wrpin">
                                    	<div class="media-frame-title" style="position: inherit;">
                                        	<h1><?php _e('Payment', 'woocommerce'); ?></h1>
                                    	</div>
                                        <div class="accept-payment-options">
                                            <div class="topaytop">
                                                <div class="topaytopin">
                                                    <label>To Pay</label>
                                                    <span class="to-pay-total"><span id="show_total_amt"></span></span></div>
                                                    <input type="hidden" id="show_total_amt_inp">
                                            </div>
                                            <center><span id="error_payment"></span></center>

                                            <div class="midbtnrowpop">
                                                <?php
                                                WC()->customer = new WC_Customer();
                                                WC()->cart = new WC_Cart();

                                                delete_user_meta( get_current_user_id(), '_stripe_customer_id' );

                                                $available_gateways = WC()->payment_gateways->payment_gateways();
                                                if (!empty($available_gateways)) {

                                                    // Chosen Method
                                                    if (isset(WC()->session->chosen_payment_method) && isset($available_gateways[WC()->session->chosen_payment_method])) {
                                                        $available_gateways[WC()->session->chosen_payment_method]->set_current();
                                                    } elseif (isset($available_gateways[get_option('woocommerce_default_gateway')])) {
                                                        $available_gateways[get_option('woocommerce_default_gateway')]->set_current();
                                                    } else {
                                                        current($available_gateways)->set_current();
                                                    }
                                                    $gateway_fields = '';
                                                    $enabled_gateways = get_option( 'pos_enabled_gateways', array() );
                                                    foreach ($available_gateways as $gateway) {
                                                        if(!in_array($gateway->id, $enabled_gateways) ) continue;
                                                        ?>
                                                        <input id="payment_method_<?php echo $gateway->id; ?>" type="radio" class="select_payment_method" name="payment_method" value="<?php echo esc_attr($gateway->id); ?>"  style="display: none;"/>

                                                        <button type="button" class="button payment_methods payment_method_<?php echo $gateway->id; ?>" id="payment_method_button_<?php echo $gateway->id; ?>"  data-bind="<?php echo $gateway->id; ?>"/><?php echo $gateway->get_title(); ?></button>
                                                        <?php
                                                    if ($gateway->has_fields() || $gateway->get_description()) :
                                                        ob_start();
                                                            echo '<div class="payment_box payment_method_' . $gateway->id . '" style="display:none; ">';
                                                            $gateway->payment_fields();
                                                            if($gateway->id == 'cod'):
                                                            ?>
                                                            <ul class="amfrmpop">
                                                                <li>
                                                                    <label>Amount Tendered</label>
                                                                    <input name="amount_pay"  id="amount_pay_cod" type="text" class="txtpopamtfild" />
                                                                    <span class="error_amount" style="color: #CC0000;"></span>
                                                                </li>
                                                                <li class="floatright">
                                                                    <label>Change</label>
                                                                    <input name="amount_change" id="amount_change_cod" type="text" class="txtpopamtfild" readonly="readonly"/>
                                                                </li>
                                                            </ul>
                                                            <?php
                                                            endif;
                                                            echo '</div>';
                                                        $gateway_fields .= ob_get_contents();
                                                        ob_end_clean();
                                                        endif;
                                                    }
                                                } else {

                                                    if (!WC()->customer->get_country())
                                                        $no_gateways_message = __('Please fill in your details above to see available payment methods.', 'woocommerce');
                                                    else
                                                        $no_gateways_message = __('Sorry, it seems that there are no available payment methods for your state. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce');

                                                    echo '<p>' . apply_filters('woocommerce_no_available_payment_methods_message', $no_gateways_message) . '</p>';
                                                }
                                                ?>
                                                <div class="form-row place-order">

                                                    <noscript><?php _e('Since your browser does not support JavaScript, or it is disabled, please ensure you click the <em>Update Totals</em> button before placing your order. You may be charged more than the amount stated above if you fail to do so.', 'woocommerce'); ?><br/><input type="submit" class="button alt" name="woocommerce_checkout_update_totals" value="<?php _e('Update totals', 'woocommerce'); ?>" /></noscript>

                                                    <?php wp_nonce_field('woocommerce-process_checkout'); ?>

                                                    <?php do_action('woocommerce_review_order_before_submit'); ?>



                                                    <?php
                                                    if (wc_get_page_id('terms') > 0 && apply_filters('woocommerce_checkout_show_terms', true)) {
                                                        $terms_is_checked = apply_filters('woocommerce_terms_is_checked_default', isset($_POST['terms']));
                                                        ?>
                                                        <p class="form-row terms">
                                                            <label for="terms" class="checkbox"><?php printf(__('I&rsquo;ve read and accept the <a href="%s" target="_blank">terms &amp; conditions</a>', 'woocommerce'), esc_url(get_permalink(wc_get_page_id('terms')))); ?></label>
                                                            <input type="checkbox" class="input-checkbox" name="terms" <?php checked($terms_is_checked, true); ?> id="terms" />
                                                        </p>
                                                    <?php } ?>

                                                    <?php do_action('woocommerce_review_order_after_submit'); ?>

                                                </div>
                                            </div>
                                            <?php echo $gateway_fields; ?>

                                            <div class="midbtnrowpop alignleft">
                                                  <input name="" type="button" class="button back_to_sale" value="Back" />
                                                  <input name="" type="button" class="button-primary go_payment " value="Pay" />
                                            </div>
                                        </div>
                                        <div class="clear"></div>
                                    </div>
                                    <div class="Clear"></div>
                                      <span class="close_popup"></span>
                                </div>

                </div>



            </div>
        </div>
    </form>

     <div class="overlay_order_popup" id="overlay_retrieve_sales">
        <div id="retrieve_sales_popup">
            <div class="media-frame-title" style="position: initial;">
                <h1 id="retrieve_sales_popup_title"><?php _e('Retrieve Sales', 'wc_point_of_sale'); ?> - <i><?php echo $data['name']; ?></i></h1>
            </div>
            <span class="close_popup"></span>
            <div id="retrieve_sales_popup_wrap">
                <div class="tablenav top">
                    <div class="alignleft actions bulkactions">
                        <?php $reg_ = WC_POS()->register()->get_data_names(); ?>
                        <select name="retrieve_from" id="bulk-action-selector-top">
                            <option value="all" data-name="<?php _e('All', 'wc_point_of_sale'); ?>"><?php _e('All', 'wc_point_of_sale'); ?></option>
                            <option value="<?php echo $data['ID']; ?>" data-name="<?php echo $data['name']; ?>"><?php _e('This Register', 'wc_point_of_sale'); ?></option>
                            <?php if(!empty($reg_)){
                                foreach ($reg_ as $reg_id => $reg_name) {
                                    if ($reg_id == $data['ID']) continue;
                                    echo '<option value="'.$reg_id.'" data-name="'.$reg_name.'">'.$reg_name.'</option>';
                                }
                            } ?>
                        </select>
                        <input type="button" value="<?php _e('Load', 'wc_point_of_sale'); ?>" class="button action" id="btn_retrieve_from">
                    </div>
                </div>
                <div id="retrieve_sales_popup_inner">
                </div>
            </div>
        </div>
    </div>
    <div class="overlay_order_popup" id="popup_choose_attributes">
        <div id="popup_choose_attributes_content">
            <div class="media-frame-title">
                <h1><?php _e('Select Options', 'wc_point_of_sale'); ?></h1>
            </div>
            <span class="close_popup"></span>
            <div id="popup_choose_attributes_inner">
            </div>
        </div>
    </div>
    <!-- Add New Customer Popup box -->
    <div class="overlay_order_popup" id="overlay_order_customer" style="display: block; visibility: hidden;">
        <div id="order_customer_popup" class="woocommerce">
                <?php
                WC()->customer = new WC_Customer();
                WC()->cart     = new WC_Cart();
                WC()->checkout = new WC_Checkout();
                $countries     = new WC_Countries();
                $countries_arr = $countries->get_allowed_countries();
                $state_arr = $countries->get_allowed_country_states();
                ?>
                <form id="add_wc_pos_customer" class="validate" action="" method="post">
                <div class="media-frame-title">
					<h1>Customer Details</h1>
                </div>
                <div id="customer_details" class="col2-set">
                    <div id="error_in_customer">
                        <p></p>
                    </div>
                    <div class="col-1">
                        <div class="woocommerce-billing-fields">
                                <h3><?php _e( 'Billing Details', 'woocommerce' ); ?></h3>
                            

                            <?php 
                            $checkout = new WC_Checkout();
                            do_action( 'woocommerce_before_checkout_billing_form', $checkout ); ?>

                            <?php foreach ( $checkout->checkout_fields['billing'] as $key => $field ) : ?>

                                <?php woocommerce_form_field( $key, $field, '' ); ?>

                            <?php endforeach; ?>

                            <?php do_action('woocommerce_after_checkout_billing_form', $checkout ); ?>
                            <p class="form-row form-row-wide create-account">
                                <input class="input-checkbox" id="createaccount" <?php checked( ( true === $checkout->get_value( 'createaccount' ) || ( true === apply_filters( 'woocommerce_create_account_default_checked', false ) ) ), true) ?> type="checkbox" name="createaccount" value="1" /> <label for="createaccount" class="checkbox"><?php _e( 'Create an account?', 'woocommerce' ); ?></label>
                            </p>
                        </div>
                        
                    </div>
                    <div class="col-2">
                        <div class="woocommerce-shipping-fields">

                                <?php
                                    if ( empty( $_POST ) ) {

                                        $ship_to_different_address = get_option( 'woocommerce_ship_to_billing' ) === 'no' ? 1 : 0;
                                        $ship_to_different_address = apply_filters( 'woocommerce_ship_to_different_address_checked', $ship_to_different_address );

                                    } else {

                                        $ship_to_different_address = $checkout->get_value( 'ship_to_different_address' );

                                    }
                                ?>

                                <h3 style="float: left; width: 160px;"id="ship-to-different-address">
                                    <label for="ship-to-different-address-checkbox" class="checkbox"><?php _e( 'Shipping Address', 'woocommerce' ); ?></label>
                                </h3>
                                    <input style="margin-top: 20px;" id="ship-to-different-address-checkbox" class="input-checkbox" <?php checked( $ship_to_different_address, 1 ); ?> type="checkbox" name="ship_to_different_address" value="1" />

                                <div class="shipping_address">

                                    <?php do_action( 'woocommerce_before_checkout_shipping_form', $checkout ); ?>

                                    <?php foreach ( $checkout->checkout_fields['shipping'] as $key => $field ) : ?>

                                        <?php woocommerce_form_field( $key, $field, '' ); ?>

                                    <?php endforeach; ?>

                                    <?php do_action( 'woocommerce_after_checkout_shipping_form', $checkout ); ?>

                                </div>
                        </div>
                    </div>
                    <div class="clear"></div>
                    
                </div>
                <div class="customer-save">
                	<input type="button" name="submit" id="save_customer" class="button button-primary" value="Save Customer">
                </div>
                </form>

                <div class="clear"></div>

            <span class="close_popup"></span>
        </div>
    </div>

</div>
<script>
    var note_request = <?php echo isNoteRequest( $data['ID'] ); ?>;
    jQuery('document').ready(function($) {
        jQuery('select#billing_country').chosen();
        jQuery('select#shipping_country').chosen();
        if(jQuery('select#billing_state').length > 0){
            jQuery('select#billing_state').chosen(); 
        }
        if(jQuery('select#shipping_state').length > 0){
            jQuery('select#shipping_state').chosen(); 
        }

          jQuery('select.ajax_chosen_select_customer').ajaxChosen({
                    method:         'GET',
                    url:            '<?php echo admin_url( 'admin-ajax.php' ); ?>',
                    dataType:       'json',
                    afterTypeDelay: 100,
                    minTermLength:  1,
                    data:       {
                        action:     'woocommerce_json_search_customers',
                        security:   '<?php echo wp_create_nonce( 'search-customers' ); ?>'
                    }
                }, function (data) {

                    var terms = {};

                    $.each(data, function (i, val) {
                        terms[i] = val;
                    });

                    return terms;
                });
          
    });
</script>
<style>
    .keypad-popup{
        z-index: 999;
        width: 240px !important
    }
</style>