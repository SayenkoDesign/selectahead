<?php if ( $_product ) : ?>
  <tr class="item <?php if ( ! empty( $class ) ) echo $class; ?>" data-order_item_id="<?php echo $item_id; ?>">
    <td class="thumb">
        <a href="<?php echo esc_url( admin_url( 'post.php?post=' . absint( $_product->id ) . '&action=edit' ) ); ?>" class="tips" data-tip="<?php

        echo '<strong>' . __( 'Product ID:', 'woocommerce' ) . '</strong> ' . absint( $item['product_id'] );

        if ( $item['variation_id'] )
          echo '<br/><strong>' . __( 'Variation ID:', 'woocommerce' ) . '</strong> ' . absint( $item['variation_id'] );

        if ( $_product && $_product->get_sku() )
          echo '<br/><strong>' . __( 'Product SKU:', 'woocommerce' ).'</strong> ' . esc_html( $_product->get_sku() );

        if ( $_product && isset( $_product->variation_data ) )
          echo '<br/>' . wc_get_formatted_variation( $_product->variation_data, true );

      ?>"><?php echo $_product->get_image( 'shop_thumbnail', array( 'title' => '' ) ); ?></a>
    </td>
    <td class="name">

    <?php if ( $_product && $_product->get_sku() ) echo esc_html( $_product->get_sku() ) . ' &ndash; '; ?>

    <?php if ( $_product ) : ?>
      <a target="_blank" href="<?php echo esc_url( admin_url( 'post.php?post='. absint( $_product->id ) .'&action=edit' ) ); ?>">
        <?php echo esc_html( $item['name'] ); ?>
      </a>
    <?php else : ?>
      <?php echo esc_html( $item['name'] ); ?>
    <?php endif; ?>

    <input type="hidden" class="product_item_id" name="product_item_id[]" value="<?php echo esc_attr( $_product_id_var ); ?>" />
    <input type="hidden" class="order_item_id" name="order_item_id[]" value="<?php echo esc_attr( $item_id ); ?>" />

    <div class="view">
      <?php
        if ( $metadata = $order->has_meta( $item_id ) ) {
          echo '<table cellspacing="0" class="display_meta">';
          foreach ( $metadata as $meta ) {

            // Skip hidden core fields
            if ( in_array( $meta['meta_key'], apply_filters( 'woocommerce_hidden_order_itemmeta', array(
              '_qty',
              '_tax_class',
              '_product_id',
              '_variation_id',
              '_line_subtotal',
              '_line_subtotal_tax',
              '_line_total',
              '_line_tax',
            ) ) ) ) {
              continue;
            }

            // Skip serialised meta
            if ( is_serialized( $meta['meta_value'] ) ) {
              continue;
            }

            echo '<tr><th>' . ucwords(substr(wp_kses_post( urldecode( $meta['meta_key'] ) ), 3)) . ':</th><td>' . wp_kses_post( wpautop( urldecode( ucwords($meta['meta_value']) ) ) ) . '</td></tr>';
          }
          echo '</table>';
        }
      ?>
    </div>
    </td>
    <td width="1%" class="quantity">
      <div class="edit">
      <input type="text" min="0" autocomplete="off" name="order_item_qty[<?php echo $_product_id_var; ?>]" placeholder="0" value="<?php echo esc_attr( $item['qty'] ); ?>" class="quantity" />
    </div>
    </td>
    <td width="1%" class="line_cost">
      <div class="view">
      <?php
        if ( isset( $item['line_total'] ) ) {
          if ( isset( $item['line_subtotal'] ) && $item['line_subtotal'] != $item['line_total'] ) echo '<del>' . wc_price( $item['line_subtotal'] ) . '</del> ';

          echo wc_price( $item['line_total'] );
        }
      ?>
      <input type="hidden" class="product_price" value="<?php echo $price ; ?>">
      <input type="hidden" class="product_line_tax" value="<?php echo $item['line_tax']; ?>">
    </div>
    </td>
    <td class="remove_item">
      <a href="#" class="remove_order_item tips" data-tip="<?php _e( 'Remove', 'wc_point_of_sale' ); ?>"></a>
    </td>
  </tr>
<?php endif; ?>