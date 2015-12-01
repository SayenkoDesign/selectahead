<?php
/**
 * Admin init logic
 *
 * @author   Actuality Extensions
 * @package  WoocommercePointOfSale
 * @since    0.1
 * @version  1.9
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WC_Pos_Init' ) ) :


/**
 * WC_Pos_Init Class
 */
class WC_Pos_Init {

    /**
     * Hook in tabs.
     */
    public function __construct() {
        // Hooks
        add_action('admin_menu', array($this, 'wc_point_of_sale_add_menu'));
        add_action('admin_head', array($this, 'wc_point_of_sale_menu_highlight'));
        add_action('admin_print_footer_scripts', array($this, 'wc_point_of_sale_highlight_menu_item'));
    }   

    /**
     * Add the menu item
     */
    function wc_point_of_sale_add_menu() {

        $hook = add_menu_page(
                __('Point of Sale', 'wc_point_of_sale'), // page title
                __('Point of Sale', 'wc_point_of_sale'), // menu title
                'manage_woocommerce', // capability
                WC_POS()->id, // unique menu slug
                'wc_point_of_sale_render_registers', null, '55.8'
        );
        $registers_hook = add_submenu_page(WC_POS()->id, __("Registers", 'wc_point_of_sale'), __("Registers", 'wc_point_of_sale'), 'manage_woocommerce', WC_POS()->id_registers, 'wc_point_of_sale_render_registers');

        $outlets_hook = add_submenu_page(WC_POS()->id, __("Outlets", 'wc_point_of_sale'), __("Outlets", 'wc_point_of_sale'), 'manage_woocommerce', WC_POS()->id_outlets, 'wc_point_of_sale_render_outlets');
        $grids_hook = add_submenu_page(WC_POS()->id, __("Product Grids", 'wc_point_of_sale'), '<span id="wc_pos_grids">'.__("Product Grids", 'wc_point_of_sale').'</span>', 'manage_woocommerce', WC_POS()->id_grids, 'wc_point_of_sale_render_grids');
        // add submenu page or permission allow this page action
        $tiles_page_title = '';
        if(isset($_GET['page']) && $_GET['page'] == WC_POS()->id_tiles && isset($_GET['grid_id']) && !empty($_GET['grid_id']) ){
            $grid_id = $_GET['grid_id'];
            $grids_single_record = wc_point_of_sale_tile_record($grid_id);
            $tiles_page_title = $grids_single_record[0]->name . ' Layout';
        }
        $tiles_hook = add_submenu_page(WC_POS()->id_grids, "Tiles - ".$tiles_page_title, "Tiles - ".$tiles_page_title, 'manage_woocommerce', WC_POS()->id_tiles, 'wc_point_of_sale_render_tiles');

        $receipt_hook = add_submenu_page(WC_POS()->id, __("Receipts", 'wc_point_of_sale'), __("Receipts", 'wc_point_of_sale'), 'manage_woocommerce', WC_POS()->id_receipts, 'wc_point_of_sale_render_receipts');

        $users_hook = add_submenu_page(WC_POS()->id, __("Users", 'wc_point_of_sale'), __("Users", 'wc_point_of_sale'), 'manage_woocommerce', WC_POS()->id_users, 'wc_point_of_sale_render_users');

        $barcodes_hook = add_submenu_page(WC_POS()->id, __("Barcode", 'wc_point_of_sale'), __("Barcode", 'wc_point_of_sale'), 'manage_woocommerce', WC_POS()->id_barcodes, 'wc_point_of_sale_render_barcodes');

        $barcodes_hook = add_submenu_page(WC_POS()->id, __("Settings", 'wc_point_of_sale'), __("Settings", 'wc_point_of_sale'), 'manage_woocommerce', WC_POS()->id_settings, 'wc_point_of_sale_render_settings');


        add_action("load-$hook", 'wc_point_of_sale_add_options_registers');
        add_action("load-$registers_hook", 'wc_point_of_sale_add_options_registers');
        add_action("load-$outlets_hook", 'wc_point_of_sale_add_options_outlets');
        add_action("load-$tiles_hook", 'wc_point_of_sale_add_options_tiles');
        add_action("load-$grids_hook", 'wc_point_of_sale_add_options_grids');
        add_action("load-$receipt_hook", 'wc_point_of_sale_add_options_receipts');
        add_action("load-$users_hook", 'wc_point_of_sale_add_options_users');

    }

    function wc_point_of_sale_highlight_menu_item()
    {
       if( isset($_GET['page']) && $_GET['page'] == WC_POS()->id_tiles ){

        ?>
            <script type="text/javascript">
                jQuery(document).ready( function($) {
                    jQuery('#wc_pos_grids').parent().addClass('current').parent().addClass('current');
                    jQuery('#toplevel_page_wc_point_of_sale').addClass('wp-has-current-submenu wp-menu-open').removeClass('wp-not-current-submenu');
                    jQuery('#toplevel_page_wc_point_of_sale > a').addClass('wp-has-current-submenu wp-menu-open').removeClass('wp-not-current-submenu');
                });
            </script>
        <?php
        }
    }


    function wc_point_of_sale_menu_highlight() {
        global $submenu;
        if (isset($submenu[WC_POS()->id]) && isset($submenu[WC_POS()->id][1])) {
            $submenu[WC_POS()->id][0] = $submenu[WC_POS()->id][1];
            unset($submenu[WC_POS()->id][1]);
        }
    }

}

endif;

return new WC_Pos_Init();
