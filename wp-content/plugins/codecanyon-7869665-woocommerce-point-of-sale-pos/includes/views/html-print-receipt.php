<div id="print_receipt">
    <center>
        <img src="<?php echo $attachment_image_logo[0]; ?>" id="print_receipt_logo" <?php echo (!$receipt_options['logo']) ? 'style="display: none;"' : ''; ?>>
        <p><?php if($preview) _e('<span class="receipt-outlet_label">Outlet Name</h4>', 'wc_point_of_sale'); else echo $outlet['name']; ?></p>
        <p><?php if($preview) _e('<span>Outlet Address</span><br><span>Address Line 2</span><br><span>Town/City</span><br><span>County, </span><span>Postcode/Zip</span><br><span>Country</span>', 'wc_point_of_sale'); else echo $outlet_address; ?></p>
        <?php if( $preview || $receipt_options['print_outlet_contact_details'] == 'yes') { ?>
            <div class="show_receipt_print_outlet_contact_details">
                <?php if($preview){ ?>
                    <p><span id="print-telephone_label"><?php echo $receipt_options['telephone_label']; ?></span><span class="colon">:</span> 1234567890</p>
                <?php }elseif($outlet['social']['phone']){
                        echo '<p>';
                        if($receipt_options['telephone_label']) echo $receipt_options['telephone_label']. ': ';
                        echo  $outlet['social']['phone'];
                        echo '</p>';
                    }  ?>

                <?php if($preview){ ?>
                    <p><span id="print-fax_label"><?php echo $receipt_options['fax_label']; ?></span><span class="colon">:</span> 1234567890</p>
                <?php }elseif($outlet['social']['fax']){
                        echo '<p>';
                        if($receipt_options['fax_label']) echo $receipt_options['fax_label']. ': ';
                        echo  $outlet['social']['fax'];
                        echo '</p>';
                    }  ?>

                <?php if($preview){ ?>
                    <p><span id="print-email_label"><?php echo $receipt_options['email_label']; ?></span><span class="colon">:</span> email@domain.com</p>
                <?php }elseif($outlet['social']['email']){
                        echo '<p>';
                        if($receipt_options['email_label']) echo $receipt_options['email_label']. ': ';
                        echo  $outlet['social']['email'];
                        echo '</p>';
                    }  ?>

                <?php if($preview){ ?>
                    <p><span id="print-website_label"><?php echo $receipt_options['website_label']; ?></span><span class="colon">:</span> www.shop.com</p>
                <?php }elseif($outlet['social']['website']){
                        echo '<p>';
                        if($receipt_options['website_label']) echo $receipt_options['website_label']. ': ';
                        echo  $outlet['social']['website'];
                        echo '</p>';
                    }  ?>
            </div>
        <?php  } ?>

        <div id="print-header_text"><?php
                echo $receipt_options['header_text'];
        ?></div>
        <?php if($preview || $receipt_options['receipt_title']) { ?>
                <h1><span id="print-receipt_title"><?php echo $receipt_options['receipt_title']; ?></span></h1>
        <?php } ?>
    </center>
    <?php if($preview) { ?>
        <p><span id="print-order_number_label"><?php echo $receipt_options['order_number_label']; ?></span><span class="colon">:</span> WC-123</p>
    <?php }elseif($receipt_options['order_number_label']){ ?>
        <p><span id="print-order_number_label"><?php echo $receipt_options['order_number_label']; ?></span><span class="colon">:</span> <?php echo $order->get_order_number(); ?></p>
    <?php }else{ echo $order->get_order_number(); } ?>

    <?php if( $preview || $receipt_options['print_order_time'] == 'yes') { ?>
        <p id="print_order_time"><span id="print-order_date_label"><?php echo $receipt_options['order_date_label']; ?></span>
        <?php if($preview || $receipt_options['order_date_label']){?>
        <span class="colon">:</span>
        <?php } ?>
        <?php
         $order_date = explode(' ', $order->order_date);

         echo $order_date[0]; ?> at <?php echo $order_date[1];  ?></p>
    <?php  } ?>
    <?php if( $preview || $receipt_options['print_server'] == 'yes') {
        $current_user = wp_get_current_user();
    ?>
        <p id="print_server"><span id="print-served_by_label"><?php echo $receipt_options['served_by_label']; ?></span>
        <?php if($preview || $receipt_options['served_by_label']){?>
        <span class="colon">:</span>
        <?php } ?>
         <?php echo $current_user->display_name; ?> on
         <?php if($preview ){?>
            [register-name]
        <?php } else{
            echo $register_name;
            }?>
        </p>
    <?php  } ?>
    <?php if($preview && $shop_order_query->found_posts == 0){ ?>
    <table id="receipt_products" class="wp-list-table widefat" style="width: 100%; ">
        <thead>
            <tr>
                <td><?php _e( 'Qty', 'wc_point_of_sale' ); ?></td>
                <td><?php _e( 'Description', 'wc_point_of_sale' ); ?></td>
                <td><?php _e( 'Price', 'wc_point_of_sale' ); ?></td>
                <td><?php _e( 'Amount', 'wc_point_of_sale' ); ?></td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>2</td>
                <td>Keyboard</td>
                <td>£59.00</td>
                <td>£118.00</td>
            </tr>
        </tbody>
         <tfoot>
            <tr>
                <th scope="row" colspan="3" style="border-top-width: 4px;">
                    Subtotal
                </th>
                <td style="border-top-width: 4px;">£118.00</td>
            </tr>
            <tr>
                <th scope="row" colspan="3" style="border-top-width: 4px;">
                    <span id="print-total_label">Total</span>
                </th>
                <td style="border-top-width: 4px;">£141.60</td>
            </tr>
            <tr>
                <th scope="row" colspan="3">
                    VAT <span id="print-tax_label">(Tax)</span>
                </th>
                <td>£23.60</td>
            </tr>
            <tr>
                <th scope="row" colspan="3">
                    Payment Type <span id="print-payment_label">Sales</span>
                </th>
                <td>£141.60</td>
            </tr>
             <tr>
                <th scope="row" colspan="3">
                    Change
                </th>
                <td>£0.00</td>
            </tr>
             <tr>
                <th scope="row" colspan="3">
                   <span id="print-items_label">Number of Items</span>
                </th>
                <td>2</td>
            </tr>
         </tfoot>
    </table>
    <?
        }else{
    ?>
    <table id="receipt_products" class="wp-list-table widefat" style="width: 100%; " border="0" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <td style="border: 1px solid #eee; padding: 8px 10px;"><?php _e( 'Qty', 'wc_point_of_sale' ); ?></td>
                <td style="border: 1px solid #eee; padding: 8px 10px;"><?php _e( 'Description', 'wc_point_of_sale' ); ?></td>
                <td style="border: 1px solid #eee; padding: 8px 10px;"><?php _e( 'Price', 'wc_point_of_sale' ); ?></td>
                <td style="border: 1px solid #eee; padding: 8px 10px;"><?php _e( 'Amount', 'wc_point_of_sale' ); ?></td>
            </tr>
        </thead>
        <tbody>
            <?php $ii = 0;
                    $items = $order->get_items();
                    foreach ($items as $item) {
                        $ii++;
                        $_product     = apply_filters( 'woocommerce_order_item_product', $order->get_product_from_item( $item ), $item );
                        $item_meta    = new WC_Order_Item_Meta( $item['item_meta'], $_product );
                        ?>
                        <tr>
                            <td style="text-align:left; vertical-align:middle; border: 1px solid #eee; padding: 8px 10px;"><?php echo $item['qty'] ;?></td>
                            <td style="text-align:left; vertical-align:middle; word-wrap:break-word; border: 1px solid #eee; padding: 8px 10px;"><?php
                                // Product name
                                echo apply_filters( 'woocommerce_order_item_name', $item['name'], $item );

                                // Variation
                                if ( $item_meta->meta ) {
                                    echo '<br/><small>' . nl2br( $item_meta->display( true, true ) ) . '</small>';
                                }

                            ?></td>
                            <td style="text-align:left; vertical-align:middle; border: 1px solid #eee; padding: 8px 10px;"><?php echo $order->get_formatted_line_subtotal( $item ); ?></td>
                            <td style="text-align:left; vertical-align:middle; border: 1px solid #eee; padding: 8px 10px;"><?php echo $order->get_formatted_line_subtotal( $item ); ?></td>
                        </tr>
                    <?php
                    }
            ?>
        </tbody>
        <tfoot>
            <?php
                if ( $totals = $order->get_order_item_totals() ) {
                    $i = 0;
                    $total_order = 0;
                    foreach ( $totals as $total_key => $total ) {
                        if( $total_key == 'cart_subtotal' ){
                            $total_label = __( 'Subtotal', 'wc_point_of_sale' );
                        }
                        elseif( $total_key == 'order_total' ){
                            $total_label = '<span id="print-total_label">'.__( 'Total', 'wc_point_of_sale' ).'</span>';
                            $total_order = $total['value'];
                        }
                        elseif( $total_key == 'order_discount' ){
                            $total_label = __( 'Discount', 'wc_point_of_sale' );
                        }
                        else{
                            continue;
                        }
                        $i++;
                        ?>
                        <tr>
                            <th scope="row" colspan="3" style="text-align:right ; border: 1px solid #eee; padding: 8px 10px; <?php if ( $i == 1 || $total_key == 'order_total' ) echo 'border-top-width: 4px;'; ?>">
                                <?php echo $total_label; ?></th>
                            <td style="text-align:left; border: 1px solid #eee; padding: 8px 10px; <?php if ( $i == 1 || $total_key == 'order_total' ) echo 'border-top-width: 4px;'; ?>"><?php echo $total['value']; ?></td>
                        </tr>
                        <?php
                        if( $total_key == 'order_total' ){
                               // Tax for tax exclusive prices
                            $tax_display = $order->tax_display_cart;
                            if ( 'excl' == $tax_display ) {
                                if ( get_option( 'woocommerce_tax_total_display' ) == 'itemized' ) {
                                    foreach ( $order->get_tax_totals() as $code => $tax ) {
                                        $total_rows[] = array(
                                            'label' => $tax->label,
                                            'value' => $tax->formatted_amount
                                        );
                                    }
                                } else {
                                    $total_rows[] = array(
                                        'label' => WC()->countries->tax_or_vat(),
                                        'value' => wc_price( $order->get_total_tax(), array('currency' => $order->get_order_currency()) )
                                    );
                                }
                            }
                            if(!empty($total_rows)){
                                foreach ($total_rows as $row) {
                                ?>
                                <tr>
                                    <th scope="row" colspan="3" style="text-align:right ; border: 1px solid #eee; padding: 8px 10px;">
                                        <?php echo $row['label']; ?>  <span id="print-tax_label">
                                        <?php if($preview) {
                                                echo '('.$receipt_options['tax_label'].')';
                                        } elseif($receipt_options['tax_label']){
                                             echo '('.$receipt_options['tax_label'].')';
                                         }?>
                                        </span></th>
                                    <td style="text-align:left; border: 1px solid #eee; padding: 8px 10px;"><?php echo $row['value']; ?></td>
                                </tr>
                                <?php
                                }
                            }
                        }
                    }
                    ?>
                    <tr>
                        <th scope="row" colspan="3" style="text-align:right ; border: 1px solid #eee; padding: 8px 10px; ">
                            <?php echo $order->payment_method_title ;?> <span id="print-payment_label"><?php echo $receipt_options['payment_label']; ?></span>
                        </th>
                        <td style="text-align:left; border: 1px solid #eee; padding: 8px 10px;">
                            <?php
                                $amount_pay = get_post_meta( $order->id, 'wc_pos_amount_pay', true );
                                if($amount_pay){
                                    echo wc_price( $amount_pay, array('currency' => $order->get_order_currency()) );
                                }
                                else{
                                    echo $total_order;
                                }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" colspan="3" style="text-align:right ; border: 1px solid #eee; padding: 8px 10px;">
                            <?php _e( 'Change', 'wc_point_of_sale' ); ?>
                        </th>
                        <td style="text-align:left; border: 1px solid #eee; padding: 8px 10px;">
                            <?php
                                $amount_change = get_post_meta( $order->id, 'wc_pos_amount_change', true );
                                if($amount_change){
                                     echo wc_price( $amount_change, array('currency' => $order->get_order_currency()) );
                                }
                                else{
                                    echo wc_price( 0, array('currency' => $order->get_order_currency()) );
                                }
                            ?>
                        </td>
                    </tr>
                     <?php if( $preview || $receipt_options['print_number_items'] == 'yes') { ?>
                     <tr id="print_number_items">
                        <th scope="row" colspan="3" style="text-align:right ; border: 1px solid #eee; padding: 8px 10px;">
                            <span id="print-items_label"><?php echo $receipt_options['items_label']; ?></span>
                        </th>
                        <td style="text-align:left; border: 1px solid #eee; padding: 8px 10px;">
                            <?php  echo $order->get_item_count(); ?>
                        </td>
                    </tr>
                    <?php  } ?>
                    <?php
                }
            ?>
        </tfoot>
    </table>
    <?php } ?>

    <center>
        <div id="print-footer_text"><?php
            echo $receipt_options['footer_text'];
        ?></div>
        <p id="print_barcode">[barcode]</p>
        <?php if( $preview || $receipt_options['print_tax_number'] == 'yes') { ?>
            <p id="print_tax_number"><span id="print-tax_number_label"><?php echo $receipt_options['tax_number_label']; ?></span><span class="colon">:</span> [tax-number]</p>
        <?php  } ?>
    </center>

</div>