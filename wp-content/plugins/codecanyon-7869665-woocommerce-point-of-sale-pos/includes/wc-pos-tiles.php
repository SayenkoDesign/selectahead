<?php
/**
 * Logic related to displaying Tiles page.
 *
 * @author   Actuality Extensions
 * @package  WoocommercePointOfSale
 * @since    0.1
 */


if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
function wc_point_of_sale_add_options_tiles() {
  $option = 'per_page';
  $args = array(
    'label' => __( 'Tiles', 'wc_point_of_sale' ),
    'default' => 10,
    'option' => 'tiles_per_page'
  );
  add_screen_option( $option, $args );

  WC_POS()->tiles_table();

}
add_filter('set-screen-option', 'wc_point_of_sale_set_tiles_options', 10, 3);
function wc_point_of_sale_set_tiles_options($status, $option, $value) {
    if ( 'tiles_per_page' == $option ) return $value;
    return $status;
}
add_action( 'admin_init', 'wc_point_of_sale_actions_tiles' );

function wc_point_of_sale_actions_tiles() {
    if(isset($_GET['page']) && $_GET['page'] != WC_POS()->id_tiles) return;

    if( isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id']) && !empty($_GET['id']) ) {
        WC_POS()->tile()->delete_tiles($_GET['id']);
    }
    else if(  isset($_POST['action']) && $_POST['action'] == 'delete' && isset($_POST['id']) && !empty($_POST['id']) ) {
        WC_POS()->tile()->delete_tiles($_POST['id']);
    }
    else if ( isset($_POST['action2']) &&  $_POST['action2'] == 'delete' && isset($_POST['id']) && !empty($_POST['id']) )  {
        WC_POS()->tile()->delete_tiles($_POST['id']);
    }
    else if(isset($_POST['action']) && $_POST['action'] == 'wc_pos_edit_update_tiles' && isset($_POST['id']) && !empty($_POST['id']) ){
        WC_POS()->tile()->update_tile();
    }
}
  /**
   * Init the tiles page
   */
function wc_point_of_sale_render_tiles() {

      if(isset($_GET['action']) && isset($_GET['id']) && $_GET['action'] == 'edit' && $_GET['id'] != '')
        WC_POS()->tile()->display_edit_form($_GET['id']);
      else
        WC_POS()->tile()->output();


}
 /**
   * wc_point_of_sale_tile_record : this function are used fetch data single record table wc_poin_of_sale_grids
   * @param : int id pass gird id
   * return :array single record
   */
function wc_point_of_sale_tile_record($grid_id = null){

  global $wpdb;

  $grid_record_set = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "wc_poin_of_sale_grids WHERE ID = " .$grid_id );
  return $grid_record_set;
}
function wc_point_of_sale_get_all_grids($grid_id = null){
	global $wpdb;
	$grid_record_set = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "wc_poin_of_sale_grids WHERE ID != " .$grid_id );
	return $grid_record_set;
}

 /**
   * wc_point_of_sale_tiles_product_exists : checking data record exits
   * @param : int grid_id , int product_id
   * return :array single record
   */
function wc_point_of_sale_tiles_product_exists( $grid_id = null , $product_id = null, $default_selection = null, $tile_id = 0){
	global $wpdb;
  $filter = '';
  if($default_selection){
    $filter = "AND default_selection = $default_selection";
  }
  if(!$tile_id)
    $grid_record_set = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "wc_poin_of_sale_tiles WHERE grid_id = " .$grid_id." $filter AND product_id =".$product_id );
  else
    $grid_record_set = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "wc_poin_of_sale_tiles WHERE grid_id = " .$grid_id." $filter AND product_id =".$product_id." AND ID <> ".$tile_id );
	return $grid_record_set;

}

function the_grid_layout_cycle($grid){
  $i = 0;
  $t = 0;
  $js = array();
  $titles = wc_point_of_sale_get_tiles($grid->ID);
      if ( $titles ) :
          foreach ($titles as $title) :

              $product              = get_product( $title->product_id );
              $available_variations = array();
              if($product->is_type( 'variable' )){
                  $available_variations = $product->get_available_variations();                                                
              }
              if($title->default_selection ){                                                

                  $product_variation = get_product( $title->default_selection );

                  $variation_data = array();

                  if( !empty( $available_variations ) && $product_variation){
                      $product_id = $title->default_selection;

                      if ( $product_variation && isset( $product_variation->variation_data ) ){
                        $k=0;
                        foreach ( $product_variation->variation_data as $name => $value ) {
                              if ( ! $value ) {
                                  continue;
                              }
                              $name = str_replace('attribute_', '', $name);
                              $variation_data[$name] = $value;
                              $k++;
                          }

                      }
                  }else{
                      continue;
                  }
              }else{
                  $product_id = $title->product_id;
              }

              $i++;
              $t++;
              if($t == 1) {
                  echo '<div><table data-title="' . ucfirst($grid->name) . ' ' . __( 'Layout', 'wc_point_of_sale') . '"><tbody>';
              }
              if($i == 1) echo '<tr>';
              if($title->style == 'image'){
                  $image = '';
                  $size = 'shop_thumbnail';
                  if ( has_post_thumbnail( $product_id ) ) {
                    $thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id($product_id), $size );
                    $image = $thumbnail[0];
                  } elseif ( ( $parent_id = wp_get_post_parent_id( $product_id ) ) && has_post_thumbnail( $parent_id ) ) {
                    $thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id($parent_id), $size );
                    $image = $thumbnail[0];
                  } else {
                    $image = wc_placeholder_img_src();
                  }
                  if(!$image || $image == NULL) $image = wc_placeholder_img_src();
                  
                  ?>
                  <td id="title_<?php echo $title->ID ?>" style="background: url('<?php echo $image; ?>') center no-repeat; background-size: contain; background-color: #fff;" class="title_product add_grid_tile">
                    <a class="add_grid_tile" style="color: #<?php echo $title->colour;?>;" data-id="<?php echo $title->product_id; ?>" data-product_variations="<?php echo esc_attr( json_encode( $available_variations ) ) ?>"></a>
                  <?php
                }else{ ?>
                  <td id="title_<?php echo $title->ID ?>" style="background: #<?php echo $title->background;?>; " class="title_product add_grid_tile">
                   <a class="add_grid_tile" style="color: #<?php echo $title->colour;?>;" data-id="<?php echo $title->product_id; ?>" data-product_variations="<?php echo esc_attr( json_encode( $available_variations ) ) ?>">
                      <?php echo get_the_title($title->product_id) ;?>
                  </a>
                  <?php 
                } 
                        $id = $title->product_id;

                        $attributes = (array) maybe_unserialize( get_post_meta( $id, '_product_attributes', true ) );

                        if(!empty($attributes) && $product->is_type( 'variable' ) ){

                          echo '<div class="hidden">';
                          foreach ( $attributes as $attribute ) {

                              if(empty($attribute))
                                  continue;

                              // Only deal with attributes that are variations

                              if ( ! $attribute['is_variation'] )
                                  continue;
                              

                              // Get terms for attribute taxonomy or value if its a custom attribute
                              if ( $attribute['is_taxonomy'] ) {

                                  echo '<select data-taxonomy="'.$attribute['name'].'" data-label="'.esc_html( wc_attribute_label( $attribute['name'] ) ).'" ><option value="">' . __( 'No default', 'woocommerce' ) . ' ' . esc_html( wc_attribute_label( $attribute['name'] ) ) . '&hellip;</option>';

                                  $post_terms = wp_get_post_terms( $id, $attribute['name'] );

                                  foreach ( $post_terms as $term ){
                                      $selected = '';
                                      if( isset($variation_data[$attribute['name']]) &&  $variation_data[$attribute['name']] == esc_attr( $term->slug ) ) $selected = 'selected="selected"';

                                      echo '<option '.$selected.' value="' . esc_attr( $term->slug ) . '">' . esc_attr( $term->name ). '</option>';
                                  }

                              } else {

                                  echo  '<select data-taxonomy="'.$attribute['name'].'" data-label="'.esc_html( wc_attribute_label( $attribute['name'] ) ).'" ><option value="">' . __( 'No default', 'woocommerce' ) . ' ' . esc_html( wc_attribute_label( $attribute['name'] ) ) . '&hellip;</option>';

                                  $options = array_map( 'trim', explode( WC_DELIMITER, $attribute['value'] ) );

                                  foreach ( $options as $option ){
                                      $selected = '';
                                      if( isset($variation_data[$attribute['name']]) &&  $variation_data[$attribute['name']] == esc_attr( sanitize_title($option) ) ) $selected = 'selected="selected"';

                                     echo  '<option  value="' . esc_attr( sanitize_title($option) ) . '">' . esc_attr( $option )  . '</option>';
                                  }

                              }

                              echo  '</select>';
                          }
                          echo '</div>';
                          
                      }
                        ?>
                  </td>
                <?php
              if($i == 5) {
                  echo '</tr>';
                  $i = 0;

                  if($t == 25) {
                      $t = 0;
                      echo '</tbody></table></div>';
                  }
              };

          endforeach;
          if($i != 0){
              $j = $i+1;
              for ($j; $j<=5; $j++) :
                  ?>
                  <td></td>
                  <?php
                  if($j == 5) echo '</tr>';
              endfor;
              echo '</tbody></table></div>';
          }else{
              if($t != 0) {
                        $t = 0;
                        echo '</tbody></table></div>';
                    }
            }
          
      endif;
}

function get_last_position_of_tile($grid_id = 0){
  global $wpdb;
  $table_name = $wpdb->prefix . 'wc_poin_of_sale_tiles';
  return $wpdb->get_row("SELECT MAX(order_position) AS `max` FROM $table_name WHERE grid_id=$grid_id");
}