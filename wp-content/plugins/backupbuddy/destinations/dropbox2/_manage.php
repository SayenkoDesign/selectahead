<?php
// @author Dustin Bolton, July 2013.
// Incoming variables: $destination


pb_backupbuddy::$ui->title( 'Dropbox destination "' . htmlentities( $destination['title'] ) . '"' );



// Copy remote file down to local.
if ( '' != pb_backupbuddy::_GET( 'copy' ) ) {
	// Copy dropbox backups to the local backup files
	pb_backupbuddy::alert( sprintf( _x('The remote file is now being copied to your %1$slocal backups%2$s', '%1$s and %2$s are open and close <a> tags', 'it-l10n-backupbuddy' ), '<a href="' . pb_backupbuddy::page_url() . '">', '</a>. If the backup gets marked as bad during copying, please wait a bit then click the `Refresh` icon to rescan after the transfer is complete.' ) );
	pb_backupbuddy::status( 'details',  'Scheduling Cron for creating Dropbox copy.' );
	pb_backupbuddy::$classes['core']->schedule_single_event( time(), pb_backupbuddy::cron_tag( 'process_destination_copy' ), array( $destination, pb_backupbuddy::_GET( 'copy' ) ) );
	spawn_cron( time() + 150 ); // Adds > 60 seconds to get around once per minute cron running limit.
	update_option( '_transient_doing_cron', 0 ); // Prevent cron-blocking for next item.
}



// Delete selected dropbox backup(s) from form submission.
if ( 'delete_backup' == pb_backupbuddy::_POST( 'bulk_action' ) ) {
	pb_backupbuddy::verify_nonce();
	if ( is_array( pb_backupbuddy::_POST( 'items' ) ) ) {
		
		if ( true === ( $result = pb_backupbuddy_destinations::delete( $destination, pb_backupbuddy::_POST( 'items' ) ) ) ) {
			pb_backupbuddy::alert( __( 'Selected file(s) deleted.', 'it-l10n-backupbuddy' ) );
		} else {
			pb_backupbuddy::alert( __( 'Unable to delete one or more files. Details: ', 'it-l10n-backupbuddy' ) . $result );
		}
		
	}
}



$files_result = pb_backupbuddy_destinations::listFiles( $destination );

$backup_files = array();
foreach( (array)$files_result['contents'] as $file ) { // Loop through files looking for backups.
	
	if ( $file['is_dir'] == '1' ) { // Do NOT display subdirectories.
		continue;
	}
	
	$filename = str_ireplace( $files_result['path'] . '/', '', $file['path'] ); // Remove path from filename.
	if ( isset( $file['client_mtime'] ) ) {
		$last_modified = strtotime( $file['client_mtime'] );
		$last_modified = pb_backupbuddy::$format->date( pb_backupbuddy::$format->localize_time( $last_modified ) ) . '<br /><span class="description">(' . pb_backupbuddy::$format->time_ago( $last_modified ) . ' ago)</span>';
	} else {
		$last_modified = '<i>' . __( 'Unknown', 'it-l10n-backupbuddy' ) . '</i>';
	}
	
	$size = $file['bytes'];
	
	if ( false !== stristr( $filename, '-db-' ) ) {
		$backup_type ='Database';
	} elseif ( false !== stristr( $filename, '-full-' ) ) {
		$backup_type ='Full';
	} elseif ( false !== stristr( $filename, 'importbuddy.php' ) ) {
		$backup_type = 'ImportBuddy Tool';
	} else {
		$backup_type = 'Unknown';
	}
	
	// Generate array of table rows.
	$backup_files[$filename] = array(
		$filename,
		$last_modified,
		pb_backupbuddy::$format->file_size( $size ),
		$backup_type,
		'file_timestamp' => $last_modified,
	);
	
}


// For sorting by array item value.
function pb_backupbuddy_aasort (&$array, $key) {
	$sorter=array();
	$ret=array();
	reset($array);
	foreach ($array as $ii => $va) {
	    $sorter[$ii]=$va[$key];
	}
	asort($sorter);
	foreach ($sorter as $ii => $va) {
	    $ret[$ii]=$array[$ii];
	}
	$array=$ret;
}
pb_backupbuddy_aasort( $backup_files, 'file_timestamp' ); // Sort by multidimensional array with key start_timestamp.
$backup_files = array_reverse( $backup_files ); // Reverse array order to show newest first.


// Render table listing files.
if ( count( $backup_files ) == 0 ) {
	echo '<b>' . __( 'You have not completed sending any backups to this destination yet.', 'it-l10n-backupbuddy' ) . '</b>';
} else {
	$page_query = 'custom=remoteclient&destination_id=' . htmlentities( pb_backupbuddy::_GET( 'destination_id' ) );
	pb_backupbuddy::$ui->list_table(
		$backup_files,
		array(
			'action'					=>	pb_backupbuddy::page_url() . '&' . $page_query,
			'columns'					=>	array(
												'Backup File',
												'Uploaded <img src="' . pb_backupbuddy::plugin_url() . '/images/sort_down.png" style="vertical-align: 0px;" title="Sorted most recent first">',
												'File Size',
												'Type'
											),
			'hover_actions'				=>	array(
												$page_query . '&copy' => 'Copy to Local',
											),
			'hover_action_column_key'	=>	'0',
			'bulk_actions'				=>	array(
												'delete_backup' => 'Delete'
											),
			'css'						=>	'width: 100%;',
		)
	);
}






die();





















?>


<script type="text/javascript">
	jQuery(document).ready(function() {
		
		jQuery( '.pb_backupbuddy_hoveraction_copy' ).click( function() {
			var backup_file = jQuery(this).attr( 'rel' );
			var backup_url = '<?php echo pb_backupbuddy::page_url(); ?>&custom=remoteclient&destination_id=<?php echo pb_backupbuddy::_GET( 'destination_id' ); ?>&remote_path=<?php echo htmlentities( pb_backupbuddy::_GET( 'remote_path' ) ); ?>&copy_file=' + backup_file + '&stashhash=' + jQuery('#pb_backupbuddy_stashhash').attr( 'rel' );
			
			window.location.href = backup_url;
			
			return false;
		} );
		
		jQuery( '.pb_backupbuddy_hoveraction_download_link' ).click( function() {
			var backup_file = jQuery(this).attr( 'rel' );
			var backup_url = '<?php echo pb_backupbuddy::page_url(); ?>&custom=remoteclient&destination_id=<?php echo pb_backupbuddy::_GET( 'destination_id' ); ?>&remote_path=<?php echo htmlentities( pb_backupbuddy::_GET( 'remote_path' ) ); ?>&downloadlink_file=' + backup_file + '&stashhash=' + jQuery('#pb_backupbuddy_stashhash').attr( 'rel' );
			
			window.location.href = backup_url;
			
			return false;
		} );
		
	});
</script>


<?php

pb_backupbuddy::disalert( 'stash_offsite_welcome_link', 'Did you know you can download all of your BackupBuddy Stash backups offsite? <a href="http://ithemes.com/member/stash.php" target="_new"pluginb>Go check it out!</a>' );


// Load required files.
require_once( pb_backupbuddy::plugin_path() . '/destinations/stash/init.php' );
require_once( dirname( __FILE__ ) . '/lib/class.itx_helper.php' );
require_once( dirname( dirname( __FILE__ ) ) . '/_s3lib/aws-sdk/sdk.class.php' );


// Settings.
if ( isset( pb_backupbuddy::$options['remote_destinations'][pb_backupbuddy::_GET('destination_id')] ) ) {
	$settings = &pb_backupbuddy::$options['remote_destinations'][pb_backupbuddy::_GET('destination_id')];
}
$settings = array_merge( pb_backupbuddy_destination_stash::$default_settings, $settings );
$itxapi_username = $settings['itxapi_username'];
$itxapi_password = $settings['itxapi_password'];


// Set up paths.
if ( pb_backupbuddy::_GET( 'remote_path' ) == '' ) {
	$remote_path = pb_backupbuddy_destination_stash::get_remote_path(); // Has leading and trailng slashes.  $settings['directory']
} else {
	$remote_path = pb_backupbuddy::_GET( 'remote_path' );
	if ( ( $remote_path != '/' ) && ( $remote_path != '' ) ) { // Only allow an empty path or a single slash.
		$remote_path = '/';
	}
}

// Welcome text.
$up_path = '/';
if ( $settings['manage_all_files'] == '1' ) {
	$manage_all_link = ' <a href="' . pb_backupbuddy::page_url() . '&custom=remoteclient&destination_id=' . htmlentities( pb_backupbuddy::_GET( 'destination_id' ) ) . '&remote_path=' . $up_path . '" style="text-decoration: none; margin-left: 15px;" title="By default, Stash will display files in the Stash directory for this particular site. Clicking this will display files for all your sites in Stash.">List files for all sites</a>';
} else {
	$manage_all_link = '<!-- manage all disabled based on settings -->';
	if ( $remote_path == '/' ) {
		die( 'Access denied. Possible hacking attempt has been logged. Error #5549450.' );
	}
}
pb_backupbuddy::$ui->title( __( 'Stash Destination', 'it-l10n-backupbuddy' ) . ' "' . $destination['title'] . '"' . '<span style="font-size: 12px; margin-left: 15px;"><b>Directory</b>: ' . $remote_path . $manage_all_link . '</span>' );


// Lock out all file access without authentication.
function pb_backupbuddy_stash_pass_form() {
	echo 'Please enter your iThemes.com Member Password to access your full Stash listing including files stored from other sites:<br><br><br>';
	echo '<form method="post"><b>iThemes Member Password</b>: &nbsp;&nbsp;&nbsp; <input type="password" name="stash_password" size="20"> &nbsp;&nbsp;&nbsp; <input type="submit" name="submit" value="Authenticate" class="button button-primary"></form>';
	echo '<br><br><br><br>';
}

$stash_hash = '';
if ( pb_backupbuddy::_GET( 'stashhash' ) != '' ) {
	$stash_hash = pb_backupbuddy::_GET( 'stashhash' );
}
if ( ( $remote_path == '/' ) && ( $settings['manage_all_files'] == '1' ) ) {
	/*
	$stash_password = get_transient( 'pb_backupbuddy_stashallfiles_' . $current_user->user_login );
	echo 'pass: ' . $stash_password;
	*/
	$stash_password = '';
	if ( pb_backupbuddy::_POST( 'stash_password' ) != '' ) {
		$stash_password = pb_backupbuddy::_POST( 'stash_password' );
	}
	if ( ( $stash_password == '' ) && ( pb_backupbuddy::_GET( 'stashhash' ) == '' ) ) {
		pb_backupbuddy_stash_pass_form();
		return;
	} else {
		if ( pb_backupbuddy::_GET( 'stashhash' ) != '' ) {
			$itxapi_password =  pb_backupbuddy::_GET( 'stashhash' );
		} else {
			$itxapi_password = ITXAPI_Helper::get_password_hash( $itxapi_username, $stash_password ); // Generates hash for use as password for API.
		}
		$account_info = pb_backupbuddy_destination_stash::get_quota(
			array(
				'itxapi_username' => $itxapi_username,
				'itxapi_password' => $itxapi_password,
			),
			true // DO bypass caching so we can check authorization.
		);
		if ( $account_info === false ) {
			pb_backupbuddy_stash_pass_form();
			delete_transient( 'pb_backupbuddy_stashallfiles_' . $current_user->user_login );
			return;
		} else { // Valid login. Cache access for 1hr for this user.
			//echo 'settrans';
			//set_transient( 'pb_backupbuddy_stashallfiles_' . $current_user->user_login, $itxapi_password, $stash_allfiles_access_timelimit );
			$stash_hash = $itxapi_password;
		}
	}
}
echo '<span id="pb_backupbuddy_stashhash" rel="' . $itxapi_password . '" style="display: none;"></span>';


// Talk with the Stash API to get access to do things.
$stash = new ITXAPI_Helper( pb_backupbuddy_destination_stash::ITXAPI_KEY, pb_backupbuddy_destination_stash::ITXAPI_URL, $itxapi_username, $itxapi_password );

$manage_data = pb_backupbuddy_destination_stash::get_manage_data( $settings );


// Connect to S3.
$s3 = new AmazonS3( $manage_data['credentials'] );    // the key, secret, token
if ( $settings['ssl'] == '0' ) {
	@$s3->disable_ssl(true);
}


// Handle deletion.
if ( pb_backupbuddy::_POST( 'bulk_action' ) == 'delete_backup' ) {
	pb_backupbuddy::verify_nonce();
	$deleted_files = array();
	foreach( (array)pb_backupbuddy::_POST( 'items' ) as $item ) {
		
		$response = $s3->delete_object( $manage_data['bucket'], $manage_data['subkey'] . $remote_path . $item );
		if ( $response->isOK() ) {
			$deleted_files[] = $item;
		} else {
			pb_backupbuddy::alert( 'Error: Unable to delete `' . $item . '`. Verify permissions.' );
		}
		
		
	}
	
	if ( count( $deleted_files ) > 0 ) {
		pb_backupbuddy::alert( 'Deleted ' . implode( ', ', $deleted_files ) . '.' );
		delete_transient( 'pb_backupbuddy_stashquota_' . $itxapi_username ); // Delete quota transient since it probably has changed now.
	}
}


// Handle copying files to local
if ( pb_backupbuddy::_GET( 'copy_file' ) != '' ) {
	pb_backupbuddy::alert( sprintf( _x('The remote file is now being copied to your %1$slocal backups%2$s', '%1$s and %2$s are open and close <a> tags', 'it-l10n-backupbuddy' ), '<a href="' . pb_backupbuddy::page_url() . '">', '</a>.<br>If the backup gets marked as bad during copying, please wait a bit then click the `Refresh` icon to rescan after the transfer is complete.' ) );
	pb_backupbuddy::status( 'details',  'Scheduling Cron for creating Stash copy.' );
	pb_backupbuddy::$classes['core']->schedule_single_event( time(), pb_backupbuddy::cron_tag( 'process_remote_copy' ), array( 'stash', pb_backupbuddy::_GET( 'copy_file' ), $settings ) );
	spawn_cron( time() + 150 ); // Adds > 60 seconds to get around once per minute cron running limit.
	update_option( '_transient_doing_cron', 0 ); // Prevent cron-blocking for next item.
}


// Handle download link
if ( pb_backupbuddy::_GET( 'downloadlink_file' ) != '' ) {
	
	$link = $s3->get_object( $manage_data['bucket'], $manage_data['subkey'] . $remote_path . pb_backupbuddy::_GET( 'downloadlink_file' ), array('preauth'=>time()+3600));
	pb_backupbuddy::alert( 'You may download this backup (' . pb_backupbuddy::_GET( 'downloadlink_file' ) . ') with <a href="' . $link . '">this link</a>. The link is valid for one hour.' );
}






echo pb_backupbuddy_destination_stash::get_quota_bar( $account_info );

echo '<div style="text-align: center;">';
echo '<a href="http://ithemes.com/member/stash.php" target="_new">Manage Offsite</a> &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp; <a href="http://ithemes.com/member/stash.php" target="_new">Upgrade to More Storage</a><br><br>';
echo '</div>';


// Get file listing.
$response = $s3->list_objects(
	$manage_data['bucket'],
	array(
		'prefix' => $manage_data['subkey'] . $remote_path
	)
);     // list all the files in the subscriber account


// Display prefix somewhere to aid in troubleshooting/support.
$subscriber_prefix = substr( $response->body->Prefix, 0, strpos( $response->body->Prefix, '/' ) );


// Get list of files.
$backup_list_temp = array();
foreach( $response->body->Contents as $object ) {
	
	

}


krsort( $backup_list_temp );
$backup_list = array();
foreach( $backup_list_temp as $backup_item ) {
	$backup_list[ $backup_item[0] ] = $backup_item; //str_replace( 'db/', '', str_replace( 'full/', '', $backup_item ) );
}
unset( $backup_list_temp );




// Display troubleshooting subscriber key.
echo '<span class="description" style="position: absolute; bottom: 20px; right: 20px;">Subscriber key: ' . $subscriber_prefix . '</span>';
echo '<br style="clear: both;">';

return;
