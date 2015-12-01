<?php
/*
Plugin Name: Woocommerce CSV Import Put on trash add-on
#Plugin URI: http://allaerd.org/
Description: Fields that where not imported are put on status trash
Version: 1.0.0
Author: Allaerd Mensonides
License: GPLv2 or later
Author URI: http://allaerd.org
*/

//init the class after woocommerce csv import is run
add_action('woocsvAfterInit', 'woocsvInitTrash');


//init
function woocsvInitTrash()
{
	$woocsvTrash = new woocsvTrash();
}

class woocsvTrash
{

	public function __construct()
	{
		$option = get_option('woocsvPutOnTrash');

		//add a short submenu
		add_action('admin_menu', array($this, 'adminMenu'));

		//if enabled set hooks to run
		if ($option == 1) {
			//add a hook when the product is saved to store the meta field
			add_action('woocsv_after_save', array($this, 'addTrashMeta'));

			//clean up meta field to start clean before the import start
			add_action('woocsv_after_csv_upload', array($this, 'cleanUpBefore'));

			//add hook after the import is finished
			add_action('woocsv_after_import_finished', array($this, 'cleanUpAfter'));
		}

	}

	public function cleanUpBefore()
	{
		global $wpdb;

		//clean up meta tag
		$wpdb->query("delete from $wpdb->postmeta where meta_key = '_woocsv_put_to_trash'");
	}

	public function cleanUpAfter()
	{
		global $wpdb;

		//update post_status to trash
		$wpdb->query("update $wpdb->posts set post_status='trash' where post_type in ('product','product_variation') and ID not in (select post_id from $wpdb->postmeta where meta_key = '_woocsv_put_to_trash')");

		//and clean up meta field
		$this->cleanUpBefore();

	}


	public static function addTrashMeta($product)
	{

		/*
		add custom field to produxts that ARE IN the CSV. Products that are not in the CSV will not have this custom field and will be put
		on status trash
		*/
		update_post_meta( $product->body['ID'], '_woocsv_put_to_trash', 'false');
	}

	public  function adminMenu()
	{

		//add smll submenu
		add_submenu_page( 'woocsv_import', 'Put on trash', 'Put on trash', 'manage_options', 'woocsvPutOnTrash', array($this, 'addToAdmin'));
	}


	function addToAdmin()
	{
		//if the FORM is posted, check nonce
		if ( !empty($_POST) && check_admin_referer('saveSettings', 'saveSettings') ) {
			//to be sure....check if the form is not tempered
			if (in_array($_POST['puttotrash'], array(0, 1))) {
				update_option('woocsvPutOnTrash', $_POST['puttotrash']);
			}
		}

		$option = get_option('woocsvPutOnTrash');
?>
		<div class="wrap">
		<div id="woocsv_warning" style="display:none" class="updated"></div>
		<h2>Put on trash</h2>
		<form id="settingsForm" method="POST">
			<label for="puttotrash">Put products that are NOT in the CSV on status trash? </label>
			<select id="puttotrash" name="puttotrash">
				<option <?php echo ( $option == 0 ) ? 'selected' : ''; ?>	value="0">No</option>
				<option <?php echo ( $option == 1 ) ? 'selected' : ''; ?>	value="1">Yes</option>
			</select>
			<?php wp_nonce_field('saveSettings', 'saveSettings'); ?>
			<br/>
			<button type="submit" class="button button-primary button-hero">Save</button>
		</form>
		<?php
	}
}
