jQuery(document).ready(function() {
	if(site.wc_generated !=1){
		jQuery('form.quickbooks').find(':input:not(:disabled)').prop('disabled',true);	
	};
	jQuery('#faqs h4').each(function() {
    	var tis = jQuery(this), state = false, answer = tis.next('div').hide().css('height','auto').slideUp();
    	tis.click(function() {
      		state = !state;
      		answer.slideToggle(state);
      		tis.toggleClass('active',state);
    	});
  	});
	jQuery(".chzn-select").chosen(
		{
			no_results_text: "No results matched",
		}
		
	);
	jQuery('a.resync_product_data').live('click', function(){
		var Othis = jQuery(this);
		var confirm_delete = confirm("This will only delete the QuickBooks POS data stored in WooCommerce for this product. This does not affect your data in QuickBooks POS or the QuickBooks POS data for any other proudct. Are you sure you want to continue?"); 
		if(confirm_delete ==true){
			jQuery.post(
				site.siteurl+"/wp-admin/admin-ajax.php", 
				//Data
				{
					action:"prod_refresh", 
					'cookie': encodeURIComponent(document.cookie),
					'id':Othis.attr('rel'),
					'refresh_nonce':site.refresh_nonce,
				},
				//success
	 			function(id)
					{
						
						Othis.parent().find('p.form-field').fadeOut().replaceWith('<p><mark class="refreshing">Queueing Data from Quickbooks</mark></p>').fadeIn();	
						
							      		
					}
		)
		};
		return false;
	}) 
	jQuery('.push-quickbooks input').click(function(){
		jQuery.post(
			site.siteurl+"/wp-admin/admin-ajax.php", 
			//Data
			{
				action:"sod_qb_product_enqueue", 
				'cookie': encodeURIComponent(document.cookie),
				'ID':this.id,
				'parentID':jQuery(this).parent().attr('id')
			},
			//success
 			function(id)
				{
					jQuery('#'+id).fadeOut();
					jQuery('#'+id).prev('mark').replaceWith('<mark>Enqueued</mark>');	      		
				}
		);
		return false;
	});
	jQuery('#create_qwc').click(function(){
		jQuery.ajaxSetup(
				{
					async:false
				}
		);
		jQuery.post(
			site.siteurl+"/wp-admin/admin-ajax.php", 
			//Data
			{
				action:"create_qwc", 
				'cookie': encodeURIComponent(document.cookie),
				'create_qwc_nonce':site.create_qwc_nonce,
				'username':jQuery('input[name=sod_qbpos_webconnector\\[username\\]]').val(),
				'password':jQuery('input[name=sod_qbpos_webconnector\\[password\\]]').val(),
				'ssl':jQuery('input[name=sod_qbpos_webconnector\\[ssl\\]]').val(),
				'key':jQuery('input[name=sod_qbpos_webconnector\\[key\\]]').val(),
				'computer_name':jQuery('input[name=sod_qbpos_webconnector\\[computer_name\\]]').val(),
				'company_data':jQuery('input[name=sod_qbpos_webconnector\\[company_data\\]]').val(),
				'version':jQuery('select[name=sod_qbpos_webconnector\\[version\\]]').val(),
			},
			//success
 			function(data)
				{
					jQuery('form.quickbooks').find(':input(:disabled)').removeProp('disabled');
				}
		);
		return true;
	});
	jQuery('#sod_quickbooks_defaults\\[create_customer_account\\]').click(function(){
		if (jQuery(this).is(':checked')) { 
			jQuery("#create_names").show();
			jQuery("#default_customer_account").hide();
		} else { 
			jQuery("#create_names").hide();
			jQuery("#default_customer_account").show(); 
		}
	});
	jQuery('#sod_quickbooks_defaults\\[post_orders\\]').click(function(){
			jQuery("#prefixes").toggle();
			jQuery("#so").toggle();
			jQuery("#order_type").toggle();
	})
	jQuery('.requeue a').click(function(){
		var $this = jQuery(this); 
			
		  	
		jQuery.post(
			site.siteurl+"/wp-admin/admin-ajax.php", 
			//Data
			{
				action:"requeue_order", 
				'cookie': encodeURIComponent(document.cookie),
				'requeue_nonce':site.requeue_nonce,
				'order_id':$this.attr("id"),
				
			},
			//success
 			function(id)
				{
					$this.parent().parent().find('ul#quickbooks mark').replaceWith('<mark class="requeued">REQUEUED</mark>');	      		
				}
		);
		return false;    
	})
	jQuery('#qbpos_use_stored_xml').click(function(){
		var $this = jQuery(this); 
		jQuery.post(
			site.siteurl+"/wp-admin/admin-ajax.php", 
			//Data
			{
				action:"use_stored_xml", 
				'cookie': encodeURIComponent(document.cookie),
				'requeue_nonce':site.requeue_nonce,
				'order_id':$this.attr("data-id"),
				'sync_status':$this.attr('checked')
			},
		function(id)
				{
						      		
				}
		)
	});
	
	
	jQuery('a.refresh').click(function(){
		var $this = jQuery(this); 
		var $td = $this.parent().parent();
		var $chosen_dropdown = $this.parent().prev();
		if(site.wc_generated ==1){
			$chosen_dropdown.remove();
			$this.fadeOut();
			jQuery.post(
				site.siteurl+"/wp-admin/admin-ajax.php",
				{
					action:"refresh_accounts",
					'cookie': encodeURIComponent(document.cookie),
					'refresh_account_nonce':site.refresh_account_nonce,
					'option':$this.attr('data-option')
				},function(data)
				{
					//jQuery('<mark class="refreshing">Retrieving Accounts from Quickbooks POS</mark>').appendTo($td);
				}
			)
		}
		return false;
	})
	jQuery('a.recheck_defaults').click(function(){
		var $this = jQuery(this); 
		//if(site.wc_generated ==1){
			$this.fadeOut();
			jQuery.post(
				site.siteurl+"/wp-admin/admin-ajax.php",
				{
					action:"recheck_defaults",
					'cookie': encodeURIComponent(document.cookie),
					'refresh_account_nonce':site.refresh_account_nonce,
					
				},function(data)
				{
					jQuery('<mark class="refreshing">Re-Queueing and Checking ALL Accounts from Quickbooks. Start the Web Connector to initiate the transfer</mark>').insertBefore($this.parent());
				}
			)
		//}
		return false;
	})
	jQuery('a.revalidate').click(function(){
		var $this = jQuery(this); 
		//if(site.wc_generated ==1){
			$this.fadeOut();
			jQuery.post(
				site.siteurl+"/wp-admin/admin-ajax.php",
				{
					action:"validate_products",
					'cookie': encodeURIComponent(document.cookie),
					'refresh_account_nonce':site.refresh_account_nonce,
					
				},function(data)
				{
					//console.log(data);
					confirm("All of your products are queued for re-validation. Please start the web connector manually so that the revalidaton can proceed.");
					//jQuery('<mark class="refreshing">Re-Queueing and Checking ALL Accounts from Quickbooks. Start the Web Connector to initiate the transfer</mark>').insertBefore($this.parent());
				}
			)
		//}
		return false;
	})
	jQuery('a.recover_orders').click(function(){
		var $this = jQuery(this); 
		//if(site.wc_generated ==1){
		var confirm_recover = confirm("You're about to recover ALL previous orders that have not posted to QBPOS yet? Depending on the number of orders you're recovering, this could take a long time. Do you want to continue? If not press cancel, and requeue individual orders from the orders page."); 
		if(confirm_recover ==true){
			$this.fadeOut();
			jQuery.post(
				site.siteurl+"/wp-admin/admin-ajax.php",
				{
					action:"recover_orders",
					'cookie': encodeURIComponent(document.cookie),
					'refresh_account_nonce':site.refresh_account_nonce,
					
				},function(data)
				{
					confirm("All of the uposted orders are queued for recovery. Please start the web connector manually so that the orders can post.");
					//jQuery('<mark class="refreshing">Re-Queueing and Checking ALL Accounts from Quickbooks. Start the Web Connector to initiate the transfer</mark>').insertBefore($this.parent());
				}
			)
		}
		//}
		return false;
	})
	jQuery('a.prod_refresh').click(function(){
		var $this = jQuery(this);
		var $chosen_dropdown = $this.parent().prev('.meta_select > div');
		$chosen_dropdown.remove();
		$this.fadeOut();
		jQuery.post(
			site.siteurl+"/wp-admin/admin-ajax.php",
			{
				action:"refresh_qb_data",
				'cookie': encodeURIComponent(document.cookie),
				'refresh_nonce':site.refresh_accounts,
				'id':$this.attr('data-id')
			},function(data)
			{
				jQuery('<mark class="refreshing">Retrieving QB Data</mark>').appendTo($this.parent().prev('.meta_select'));
			}
		)
		return false;
	})
	jQuery('.switch').click(function(){
		var $this = jQuery(this);
		jQuery.post(
			site.siteurl+"/wp-admin/admin-ajax.php",
			{
				action:"sync_item",
				'cookie': encodeURIComponent(document.cookie),
				'sync_item_nonce':site.sync_item_nonce,
				'id':$this.attr('data-id'),
				'sync_status':$this.attr('checked')
			},function(data)
			{
				
				
			}
		)
		//return false;
	})
	jQuery('.meta-select').click(function(){
		jQuery.post(
			site.siteurl+"/wp-admin/admin-ajax.php",
			{
				action:"update_account",
				'cookie': encodeURIComponent(document.cookie),
				'update_account_nonce':site.sync_item_nonce,
				'account':$this.attr('data-accounttype'),
				'id':$this.attr('data-id'),
				'list_id':$this.attr('checked'),
				
			},function(data)
			{
				
				
			}
		)
	})
});