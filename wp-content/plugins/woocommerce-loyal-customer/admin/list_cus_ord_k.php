<?php
/* sorting by post */
ini_set('memory_limit', '512M');

if(isset($_POST['code_sort_by'])){
	$code_sort_by = $_POST['code_sort_by'];
	
	if(is_wp_error($code_sort_by)) 
	{
		$error_string1 = $code_sort_by->get_error_message();
		mail_error_generation($error_string1);
		}
}

if(isset($_POST['id_sort_by'])){
	$id_sort_by = $_POST['id_sort_by'];
	
	if(is_wp_error($id_sort_by)) 
	{
		$error_string2 = $id_sort_by->get_error_message();
		mail_error_generation($error_string2);
		}
	
}

function mail_error_generation($error)
{
	$mainname = "Woocommerce Search By SKU Plugin bug";
			$header_subject	=	"Woocommerce Search By SKU facing a Bug";
			$header_message	=	'<b>"Woocommerce Search By SKU" failed to initialize properly</b><br><br>';
			
			$header_message	.=	$error.'<br><br>';
			$header_message	.=	'<br><br>
			<div style="font-size:8pt;font-family:Calibri,sans-serif;color:rgb(64,64,64);"><b>CONFIDENTIALITY NOTICE:</b> <span>This message and any attachments are solely for the intended recipients.  They may contain privileged and/or confidential information or other information protected from disclosure and distribution. If you are not an intended recipient, please (1) let me know, and (2) delete the message and any attachments from your system.</span></div>';
			
			$mainemail = "amer.mushtaq@codenterprise.com";
			$headers = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			$headers .= 'From:' . $mainname . '  <' . $mainemail . '>' . "\r\n";
			wp_mail( $mainemail, $header_subject, $header_message, $headers );
	}
?>

<div class="wrap">
  <h2> WLC </h2>
  <?php /* admin notic ... color code information */ ?>
  <table class="widefat">
    <thead>
      <tr>
        <th><div style='background:grey;width:20px;height:20px; border-radius: 1em; float:left;'></div>
          &nbsp;&nbsp;1 Order</th>
        <th><div style='background:orange;width:20px;height:20px; border-radius: 1em; float:left;'></div>
          &nbsp;&nbsp;2 - 4 Order</th>
        <th><div style='background:yellow;width:20px;height:20px; border-radius: 1em; float:left;'></div>
          &nbsp;&nbsp;5 - 8 Order</th>
        <th><div style='background:green;width:20px;height:20px; border-radius: 1em; float:left;'></div>
          &nbsp;&nbsp;9 - 12 Order</th>
        <th><div style='background:blue;width:20px;height:20px; border-radius: 1em; float:left;'></div>
          &nbsp;&nbsp;13+ Order</th>
        <th style="background:#615555; padding-right: 0px;"> <form action="" method="post">
            <input type="search" name="search_cus_ord" style="height: 2em;">
            <input type="submit" name="search_cus_ord_b" value="Search" class="button">
          </form>
        </th>
      </tr>
    </thead>
  </table>
  <table class="widefat">
    <thead>
      <tr>
        <th> <div style="float:left;">Customer ID</div>
          <div onClick="document.forms['id_sort'].submit();"  style="cursor: pointer;">
            <?php if($id_sort_by == 'acs'){echo '&#9650;';}else{echo '&#9660;';}?>
          </div>
          <form action="" method="post" name="id_sort" id="id_sort">
            <input type="hidden" name="id_sort_by" value="<?php if($id_sort_by == 'acs'){echo 'desc';}else{echo 'acs';}?>">
          </form>
        </th>
        <th>Customer Name</th>
        <th>Customer E-mail</th>
        <th> <div style="float:left;">Order Count </div>
          <div onClick="document.forms['code_sort'].submit();"  style="cursor: pointer;">
            <?php if($code_sort_by == 'acs'){echo '&#9650;';}else{echo '&#9660;';}?>
          </div>
          <form action="" method="post" name="code_sort" id="code_sort">
            <input type="hidden" name="code_sort_by" value="<?php if($code_sort_by == 'acs'){echo 'desc';}else{echo 'acs';}?>">
          </form>
        </th>
        <th>Order Color</th>
      </tr>
    </thead>
    <?php        
	function woocommerce_version_check() 
	{
		// If get_plugins() isn't available, require it
		if ( ! function_exists( 'get_plugins' ) )
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	
        // Create the plugins folder and file variables
	$plugin_folder = get_plugins( '/' . 'woocommerce' );
	$plugin_file = 'woocommerce.php';
	
	// If the plugin version number is set, return it 
	if ( isset( $plugin_folder[$plugin_file]['Version'] ) ) {
		
		if(is_wp_error($plugin_folder[$plugin_file]['Version'])) 
		{
			$error_string3 = $plugin_folder[$plugin_file]['Version']->get_error_message();
			mail_error_generation($error_string3);
			}
		return (float) $plugin_folder[$plugin_file]['Version'];

	} else {
	// Otherwise return null
		return NULL;
	}
}
		//echo woocommerce_version_check();
		function fused_get_all_user_orders($user_id)
		{
			if(!$user_id)return false;
			
			if( woocommerce_version_check() < 2.2 ) 
			{
				$user_order = query_posts(
					array(
						'post_type'   => 'shop_order', 
						'meta_key'    => '_customer_user', 
						'meta_value'  => $user_id,
						'posts_per_page' => -1
						)
					);
			
			if(is_wp_error($user_order)) 
			{
				$error_string4 = $user_order->get_error_message();
				mail_error_generation($error_string4);
				}
			
			 //getting each order of single user..... where order status = completed 
			$c = 0;
			foreach ($user_order as $customer_order) 
			{
				$order = new WC_Order();
				$order->populate($customer_order);
				echo $orderdata = (array) $order;
				if(is_wp_error($orderdata)) 
				{
					$error_string5 = $orderdata->get_error_message();
					mail_error_generation($error_string5);
					}
				if( $orderdata['status'] == 'completed' )
				{
					$c++;
					}
				}
			 //return counted array 
			return $c;
			}
			else			
			{				
				$user_order = query_posts(
				array(
					'post_type'   => 'shop_order', 
					'meta_key'    => '_customer_user', 
					'meta_value'  => $user_id,
					'posts_per_page' => -1,
					'post_status' => 'wc-completed'
					)
				);

				if(is_wp_error($user_order))
				{
					$error_string6 = $user_order->get_error_message();
					mail_error_generation($error_string6);
					}

				$c = 0;
				
				//echo "<pre>".print_r($user_order,true)."</pre>";
	
				foreach ($user_order as $customer_order) 
				{
					$order = new WC_Order();
					$order->populate($customer_order);
					$orderdata = (array) $order;
					
					if(is_wp_error($orderdata))
					{
						$error_string7 = $orderdata->get_error_message();
						mail_error_generation($error_string7);
						}					
						
					if( $orderdata['post_status'] == 'wc-completed' )
					{
						$c++;
						}
					}
				return $c;
				}
			}
		
		if( woocommerce_version_check() < 2.2 ) 
		{
			$arguments1 = array('post_type' => 'shop_order','meta_key' => '_customer_user','posts_per_page' => -1);
			$orders = new WP_Query($arguments1);
			
			if(is_wp_error($orders)) 
			{
				$error_string8 = $orders->get_error_message();
				mail_error_generation($error_string8);
				}	
		
		if($orders->have_posts())
		{
			error_reporting(0);
			$ids = array(); 
			
			while($orders->have_posts())
			{ 
				$orders->the_post();
				$order_id = get_the_ID();
				$order = new WC_Order($order_id);
				
				$users_id=$order->user_id; 
				$ids[] = $users_id ;
				
				if(is_wp_error($ids)) 
				{
					$error_string9 = $ids->get_error_message();
					mail_error_generation($error_string9);
					}
				}
				$ids = array_unique($ids) ;
				$ids = array_values(array_filter($ids));
				
				if(is_wp_error($ids)) 
				{
					$error_string10 = $ids->get_error_message();
					mail_error_generation($error_string10);
					}
				
				if($id_sort_by=="acs")
				{
				sort($ids);
				}
				if($id_sort_by=="desc")
				{
				rsort($ids);	
				}
				
				$users_count = array();
				for($i=0 ; $i<count($ids) ; $i++ )
				{
					$total = fused_get_all_user_orders($ids[$i]);
					
					if(is_wp_error($total)) 
					{
						$error_string11 = $total->get_error_message();
						mail_error_generation($error_string11);
						}
				
						if($total > 0){
							$user_info = get_userdata( $ids[$i] );
							$users_count[$ids[$i]]['id'] = $ids[$i];
							$users_count[$ids[$i]]['count'] = $total;
							$users_count[$ids[$i]]['username'] = $user_info->first_name." ".$user_info->last_name;
							$users_count[$ids[$i]]['useremail'] = $user_info->billing_email;
						}
					}
			}
		}
		else
    	{
			/*$arguments = array('post_type' => 'shop_order','meta_key' => '_customer_user','posts_per_page' => -1);
			$orders = new WP_Query($arguments);*/
			
			$arguments = array('post_type' => 'shop_order','meta_key' => '_customer_user','posts_per_page' => -1,'post_status' => 'wc-completed' );
			
			$orders = new WP_Query($arguments);
			
			

			
			
			
			if(is_wp_error($orders)) 
			{
				$error_string8 = $orders->get_error_message();
				mail_error_generation($error_string8);
				}
			
			if($orders->have_posts())
			{
				
				error_reporting(0);
				$ids = array();
				
				while($orders->have_posts())
				{
					$orders->the_post();
					$order_id = get_the_ID();
					$order = new WC_Order($order_id);
					
					
					$users_id=$order->user_id;
					$ids[] = $users_id ;
					
					
					if(is_wp_error($ids))
					{
						$error_string9 = $ids->get_error_message();
						mail_error_generation($error_string9);
						}
					}

			$ids = array_unique($ids) ;
			
			$ids = array_values(array_filter($ids));	

			if(is_wp_error($ids)) 
			{
				$error_string10 = $ids->get_error_message();
				mail_error_generation($error_string10);
				}		

			if($id_sort_by=="acs")
			{
				sort($ids);
				}

			if($id_sort_by=="desc")
			{
				rsort($ids);
				}		

			$users_count = array();
			
				

			for($i=0 ; $i<count($ids) ; $i++ )
			{
				$total = fused_get_all_user_orders($ids[$i]);
				
				
				if(is_wp_error($total))
				{
					$error_string11 = $total->get_error_message();
					mail_error_generation($error_string11);
					}
					
				if($total > 0)
				{
					$user_info = get_userdata( $ids[$i] );
					
					$users_count[$ids[$i]]['id'] = $ids[$i];
					$users_count[$ids[$i]]['count'] = $total;
					$users_count[$ids[$i]]['username'] = $user_info->first_name." ".$user_info->last_name;
					$users_count[$ids[$i]]['useremail'] = $user_info->billing_email;
					}
					
				}
			}
			}
	
	
////// sort by order count ////////
			/////////////// sorting function ///////////////////////////////////////////
		function array_orderby(){
				$args = func_get_args();
				$data = array_shift($args);
				foreach ($args as $n => $field) {
					if (is_string($field)) {
						$tmp = array();
						foreach ($data as $key => $row)
							$tmp[$key] = $row[$field];
						$args[$n] = $tmp;
						}
				}
				$args[] = &$data;
				call_user_func_array('array_multisort', $args);
				return array_pop($args);
			}
			
	/*		/////////////// calling sorting function ///////////////////////////////////////////
		if($code_sort_by == 'acs'){
				$users_count = array_orderby($users_count, 'count', SORT_ASC, 'id', SORT_ASC);
			}else{
				$users_count = array_orderby($users_count, 'count', SORT_DESC, 'id', SORT_ASC);
			}
	*/
//print_r($users_count);

if($code_sort_by == 'acs'){
				$users_count = array_orderby($users_count, 'count', SORT_ASC, 'id', SORT_ASC);
				
				if(is_wp_error($users_count)) 
				{
					$error_string13 = $users_count->get_error_message();
					mail_error_generation($error_string13);
					}
			}
		if( woocommerce_version_check() < 2.2  )
		{			
			if($code_sort_by == 'desc')
			{
				$users_count = array_orderby($users_count, 'count', SORT_DESC, 'id', SORT_ASC);
				
				if(is_wp_error($users_count)) 
				{
					$error_string13 = $users_count->get_error_message();
					mail_error_generation($error_string13);
					}
				}
			}
	
		foreach( $users_count as $user_count)
		{
			if(isset($_POST['search_cus_ord_b']))
			{
				$pos_code = strpos(trim($user_count['username']), trim($_POST['search_cus_ord']));
				$pos1_code = strpos(trim($user_count['useremail']), trim($_POST['search_cus_ord']));
				
				if(is_wp_error($pos_code)) 
				{
					$error_string14 = $pos_code->get_error_message();
					mail_error_generation($error_string14);
					}
					
				if(is_wp_error($pos1_code)) 
				{
					$error_string15 = $pos1_code->get_error_message();
					mail_error_generation($error_string15);
					}
				}
				
					
				
			if(($pos_code === false) && ($pos1_code === false))continue;
				?>
    <tr>
      <td><?php echo $user_count['id']; ?></td>
      <td><?php echo $user_count['username'];?></td>
      <td><?php echo $user_count['useremail']; ?></td>
      <td><?php echo $user_count['count']; ?></td>
      <td>
	  <?php 
	  
	  if($user_count['count']==1)
	  {
		  echo "<div style='background:grey;width:20px;height:20px; border-radius: 1em;'></div>";
		  }
		  else if($user_count['count']>=2 && $user_count['count']<=4)
		  {
			  echo "<div style='background:orange;width:20px;height:20px; border-radius: 1em;'></div>";
			  }
		  else if($user_count['count']>=5 && $user_count['count']<=8)
		  {
			  echo "<div style='background:yellow;width:20px;height:20px; border-radius: 1em;'></div>";
			  }
		  else if($user_count['count']>=9 && $user_count['count']<=12)
		  {
			  echo "<div style='background:green;width:20px;height:20px; border-radius: 1em;'></div>";
			  }
		  else if($user_count['count']>=13)
		  {
			  echo "<div style='background:blue;width:20px;height:20px; border-radius: 1em;'></div>";
			  }
			  ?></td>
    </tr>
    <?php }?>
  </table>
</div>
<?php /******************************************************************/?>