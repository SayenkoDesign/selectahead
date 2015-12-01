    const DB_NAME         = 'wc_pointofsale';
    const DB_DISPLAY_NAME = 'Woocommerce Point Of Sale';
    
    const DB_STORE_NAME   = 'pos_products';
    const MAX_SIZE        = 10485760;
    var db;
    var pub_msg;


function openDb() {
    console.log('safari');
    console.log("load products ...");

    pub_msg = jQuery('#message_pos');

    try {
        if (!window.openDatabase) {
            alert('not supported');
        } else {
            var data = {
                action      : 'wc_pos_json_search_products_all'
            };
            var productsData;
            jQuery.ajax({
              type: 'GET',
              async: true,
              url: wc_pos_params.wc_api_url+'products/',
              data: data,
              timeout: 120000,
              success: function(response) {
                  if (response) {                    
                    console.log("openDb ...");
                    db = window.openDatabase(DB_NAME, '', DB_DISPLAY_NAME, MAX_SIZE);
                    /*if(db.version == ''){
                        db.changeVersion('', '1', function(t){
                            t.executeSql("ALTER TABLE "+DB_STORE_NAME+" ADD COLUMN parent_attr");
                        });
                    }*/
                    createTables(db, response.products);                      
                  }
              },
              error: function(response) {
                var responseText = JSON.parse(response.responseText);
                    if(typeof responseText.errors != undefined){
                        errors_message = responseText.errors.message;
                    }else{
                        errors_message = response.responseText;
                    }
                    displayLocked(errors_message);
              }
            });
            
            // You should have a database instance in db.
        }
    } catch(e) {
        // Error handling code goes here.
        if (e == 2) {
            // Version number mismatch.
            alert("Invalid database version.");
        } else {
            alert("Unknown error "+e+".");
        }
        return;
    }
    

     
}

function createTables(db, productsData)
{   
    console.log("createTables START");
    db.transaction(
        function (transaction) {

        
            /* The first query causes the transaction to (intentionally) fail if the table exists. */
            transaction.executeSql('CREATE TABLE IF NOT EXISTS '+DB_STORE_NAME+' (_id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT, id INTEGER NOT NULL DEFAULT 0, parent_id INTEGER NOT NULL DEFAULT 0, stock_quantity INTEGER NOT NULL  DEFAULT 0, pr_excl_tax FLOAT NOT NULL DEFAULT 0.00, pr_inc_tax FLOAT NOT NULL DEFAULT 0.00, attributes TEXT NOT NULL DEFAULT "", parent_attr TEXT NOT NULL DEFAULT "", title TEXT NOT NULL DEFAULT "", barcode TEXT NOT NULL DEFAULT "", f_title TEXT NOT NULL DEFAULT "", featured_src TEXT NOT NULL DEFAULT "", price TEXT NOT NULL DEFAULT "", price_html TEXT NOT NULL DEFAULT "", in_stock BOOLEAN NULL DEFAULT false);', [], nullDataHandler, errorHandler);
            /* These insertions will be skipped if the table already exists. */
            
            transaction.executeSql('DELETE FROM '+DB_STORE_NAME+';');

            var html_products = '';
            jQuery.each(productsData, function(i, data){
                if(typeof data.id != 'undefined' && typeof data.f_title != 'undefined'){

                    if(typeof data.variations != 'undefined' && data.type == 'variable'){
                        jQuery.each(data.variations, function(j, var_data){
                            
                            var_data.attributes  = JSON.stringify(var_data.attributes);
                            var_data.parent_attr = JSON.stringify(var_data.parent_attr);
                            if(typeof var_data.parent_id == 'undefined')
                                var_data.parent_id = 0;

                            var ar_data = new Array( var_data.id, var_data.parent_id, var_data.stock_quantity, var_data.pr_excl_tax, var_data.pr_inc_tax, var_data.attributes, var_data.parent_attr, var_data.title, var_data.barcode, var_data.f_title, var_data.featured_src, var_data.price, var_data.price_html, var_data.in_stock);

                            transaction.executeSql('INSERT INTO '+DB_STORE_NAME+' (id, parent_id, stock_quantity, pr_excl_tax, pr_inc_tax, attributes, parent_attr, title, barcode, f_title, featured_src, price, price_html, in_stock ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? );', ar_data, nullDataHandler, errorHandler);


                            html_products += '<option value="' + var_data.id +'">'+var_data.f_title+'</option>';
                        });
                    }
                    else
                    {
                        data.attributes  = JSON.stringify(data.attributes);
                        data.parent_attr = JSON.stringify(data.parent_attr);
                        if(typeof data.parent_id == 'undefined')
                            data.parent_id = 0;

                        var ar_data = new Array( data.id, data.parent_id, data.stock_quantity, data.pr_excl_tax, data.pr_inc_tax, data.attributes, data.parent_attr, data.title, data.barcode, data.f_title, data.featured_src, data.price, data.price_html, data.in_stock);

                        transaction.executeSql('INSERT INTO '+DB_STORE_NAME+' (id, parent_id, stock_quantity, pr_excl_tax, pr_inc_tax, attributes, parent_attr, title, barcode, f_title, featured_src, price, price_html, in_stock ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? );', ar_data, nullDataHandler, errorHandler);


                        html_products += '<option value="' + data.id +'">'+data.f_title+'</option>';
                    }
                }else{
                    console.log(data);
                }
            });
           
            jQuery('#add_product_id').html(html_products);
            jQuery('select.ajax_chosen_select_products_and_variations').chosen();
            console.log("createTables END");
            displayUnLocked();
            addEventListeners_webSql();
        }
    ); 
}
function displayActionSuccess(msg) {
    msg = typeof msg != 'undefined' ? "Success: " + msg : "Success";
    $('#msg').html('<span class="action-success">' + msg + '</span>');
}


function displayLocked(msg) {
    // If some other tab is loaded with the database, then it needs to be closed
    // before we can proceed.
    var msg = msg;
    if(msg == '') msg = wc_pos_params.open_another_tab;
    jQuery('document').ready(function($) {
        $('#post-lock-dialog .post-taken-over').html("<p>"+msg+"</p>");
        $('#post-lock-dialog').show();
    });
    console.log("Please close all other tabs with this site open!");
}
function displayUnLocked() {
    console.log("displayUnLocked");
    jQuery('document').ready(function($) {
        if( jQuery('.post-locked-message.not_close').length == 0 ){
            jQuery('#post-lock-dialog').hide();            
        }
    });
}
function displayActionFailure(msg) {
    msg = typeof msg != 'undefined' ? "Failure: " + msg : "Failure";
    $('#msg').html('<span class="action-failure">' + msg + '</span>');
}
function resetActionStatus() {
    console.log("resetActionStatus ...");
    $('#msg').empty();
    console.log("resetActionStatus DONE");
}
function addEventListeners_webSql() {
    console.log("addEventListeners_indexedDB");
    jQuery('document').ready(function($) {
        $('#wc-pos-register-grids').on('click', 'td.add_grid_tile', function() {
            var pid = $(this).find('a').attr('data-id');
            addProduct(pid, $(this));
            return false;
        });
        $('body').on('click', '#var_tip_over', function() {
            $('#var_tip_over').remove();
            $('#var_tip').remove();
            $('td.hover').removeClass('hover');
            return false;
        });
        $('body').on('click', '#add_pr_variantion', function() {
            var all_variations = $('#var_tip').data( 'product_variations' ),
                exit    = false,
                showing_variation = false,
                current_settings = {},
                humaniz_settings = {},
                exclude = '';

            $('#var_tip select' ).each( function() {

                if ( $( this ).val().length === 0 ) {
                    exit = true;
                }
                // Encode entities
                value = $( this ).val();
                // Add to settings array
                current_settings[ 'attribute_'+$( this ).data( 'taxonomy' ) ] = value;
                var label = $( this ).data( 'label' );
                humaniz_settings[ label ] = {};
                humaniz_settings[ label ]['value'] = value;
                humaniz_settings[ label ]['taxonomy'] = 'attribute_'+$( this ).data( 'taxonomy' );
            });
            if(exit) return false;
            var matching_variations = find_matching_variations( all_variations, current_settings );
            var variation = matching_variations.shift();
            if ( variation ) {

                addProduct(variation.variation_id, 'no', humaniz_settings);

            }else{
                pub_msg.html('<p>'+wc_pos_params.cannot_be_purchased+'</p>').show();
            }
            return false;
        });
        $('body').on('click', '#add_pr_variantion_popup', function() {

            var product_id     = parseInt( $('#popup_choose_attributes_inner').data('id') )
                humaniz_settings = {};

            $('#popup_choose_attributes_inner select' ).each( function() {

                // Encode entities
                value = $( this ).val();
                // Add to settings array
                var label = $( this ).data( 'label' );
                humaniz_settings[ label ] = {};
                humaniz_settings[ label ]['value'] = value;
                humaniz_settings[ label ]['taxonomy'] = 'attribute_'+$( this ).data( 'taxonomy' );
            });

            addProduct(product_id, 'no', humaniz_settings);
            $('#popup_choose_attributes').hide();
            return false;
        });
        $('#wc-pos-register-data').on('click', '.remove_order_item', function() {
            var $el = $(this).closest('tr');
            $el.remove();
            $('#tiptip_holder').hide().css( {margin: '-100px 0 0 -100px'});
            calculateDiscount();
            calcRegisterTotal();
            return false;
        });
        $('#wc-pos-register-data').on('change', '#add_product_id', function() {
            var ids_products = $('#add_product_id').val();
            $('select#add_product_id, #add_product_id_chosen .chosen-choices').css('border-color', '').val('-1');
            $('select#add_product_id').trigger("chosen:updated");
            if (ids_products) {
                $.each(ids_products, function(index, value) {
                    addProduct(value, 'no');
                });
            }
        });
        char0 = new Array("§", "32");
        char1 = new Array("˜", "732");
        characters = new Array(char0, char1);
        $(document).BarcodeListener(characters, function(code) {
            code = code.trim();
            if (code != ''){
                db.transaction(function(tx) {
                    tx.executeSql("SELECT * FROM "+DB_STORE_NAME +" WHERE barcode = '"+code+"'", [], function(tx, result){
                        if(typeof result == 'undefined'){
                                pub_msg.html('<p>'+wc_pos_params.cannot_be_purchased+'</p>').show();
                                return;
                            }
                            var item = result.rows.item(0);
                            if(typeof item == 'undefined' || parseFloat(item.price) <= 0.00){
                                pub_msg.html('<p>'+wc_pos_params.cannot_be_purchased+'</p>').show();
                                return;
                            }
                            addProduct(item.id, 'no');
                    }, function(tx, error){});
                });
            }else{
                pub_msg.html('<p>'+wc_pos_params.cannot_be_purchased+'</p>').show();
                return;
            }
        });

        $('#wc-pos-register-data').on('change', 'input.quantity', function() {
            var pid = $(this).parents('tr.item').find('input.product_item_id').val();
            calculateDiscount();
            calcRegisterTotal();
        });

    });

}
function addProduct(pid, element, humaniz_settings) {
    
    if(element != 'no' && element.find('.hidden').length > 0 ) {
        runVariantion(element);
        return;
    }
    pub_msg.empty().hide();

    db.transaction(function(tx) {
        tx.executeSql("SELECT * FROM "+DB_STORE_NAME +" WHERE id = "+pid, [], function(tx, result) {            
            for(var i = 0; i < result.rows.length; i++) {
                var item = result.rows.item(i);
                if(typeof result.rows == 'undefined'){
                    pub_msg.html('<p>'+wc_pos_params.cannot_be_purchased+'</p>').show();
                    return;
                }

                var attributes = '';
                var row_class  = 'product_id_'+item.id;
                var exit = false;
                var need_attributes = {};
                if(item.attributes != ""){
                    var v_data = JSON.parse(item.attributes);

                    var attr = "";
                    
                    jQuery.each(v_data, function(index, val) {
                        var hidden = '';
                        if(val["option"] == ''){
                            if(typeof humaniz_settings != 'undefined' ){
                                if( humaniz_settings[val['name']] ){

                                    val["option"] = humaniz_settings[ val['name'] ].value;
                                    var taxonomy = humaniz_settings[ val['name'] ].taxonomy;
                                    hidden += '<input type="hidden" name="variations['+item.id+']['+taxonomy+']" value="'+val["option"]+'"/>';   

                                }
                            }else{
                                need_attributes[ val['name'] ] = true;
                                exit = true;
                                return
                            }
                        }else{
                            if(typeof item.parent_attr == 'string'){
                                var product_attributes = jQuery.parseJSON(item.parent_attr);
                            }else{
                                var product_attributes = item.parent_attr;
                            }
                            for(var att in product_attributes){

                                if (product_attributes.hasOwnProperty(att) ){
                                    var attribute = product_attributes[att];
                                    
                                    if(attribute.name == val['name']){
                                        var taxonomy = 'attribute_'+attribute.taxonomy;
                                        hidden += '<input type="hidden" name="variations['+item.id+']['+taxonomy+']" value="'+val["option"]+'"/>';   
                                    }

                                }

                            }
                        }

                        row_class += '_'+val["option"];


                        attr += "<tr><th>"+val['name']+":</th><td><p>"+val["option"]+"</p>"+hidden+"</td></tr>";
                        tip += val['name']+': '+val["option"]+', ';
                    }); 
                    attributes = '<div class="view"><table cellspacing="0" class="display_meta"><tbody>'+attr+'</tbody></table></div>';
                }
                if(exit){
                    popupChooseAttributes(item, need_attributes);
                    return;
                }

                var new_item = jQuery('#wc-pos-register-data #order_items_list .' + row_class);

                var stock = item.stock_quantity;

            if (new_item.length > 0) {
                var el = new_item.find('input.quantity');
                var qt = parseInt(el.val());
                if(el.val() == '') qt = 0;
                qt = qt+1;

                if(!item.in_stock || ( stock < qt && stock != '') ){
                    var txt = wc_pos_params.cannot_add_product.replace('%NAME%', item.title);
                        txt = txt.replace('%COUNT%', stock);
                     pub_msg.html('<p>'+txt+'</p>').show();

                     return;
                }
                el.val(qt);
            }else{
                if(!item.in_stock || ( stock < 1 && stock != '')){
                    var txt = wc_pos_params.cannot_add_product.replace('%NAME%', item.title);
                        txt = txt.replace('%COUNT%', stock);
                     pub_msg.html('<p>'+txt+'</p>').show();
                     return;
                }
                var variation = '';

                
                var tax = calc_tax(item);
                var tip = '<strong>Product ID:</strong>'+item.id+'<br>';
                var title = '';
                if(item.barcode != ''){
                    title += item.barcode + " &ndash; ";
                    tip += '<strong>Product SKU:</strong>'+item.barcode+'<br>';
                }
                title += '<a href="'+wc_pos_params.admin_url+'/post.php?post='+item.id+'&amp;action=edit" >'+item.title+'</a>';

                var row = '\
<tr class="item '+row_class+' new_row" data-order_item_id="">\
    <td class="thumb">\
        <a href="'+wc_pos_params.admin_url+'/post.php?post='+item.id+'&amp;action=edit" class="tips" data-tip="'+tip+'"><img width="90" height="90" src="'+item.featured_src+'" class="attachment-shop_thumbnail wp-post-image"></a>\
    </td>\
<td class="name">\
'+title+'\
<input type="hidden" class="product_item_id" name="product_item_id[]" value="'+item.id+'">\
<input type="hidden" class="order_item_id" name="order_item_id[]" value="">\
'+attributes+'\
</td>\
<td width="1%" class="quantity">\
  <div class="edit">\
  <input type="text" min="0" autocomplete="off" name="order_item_qty['+item.id+']" placeholder="0" value="1" class="quantity">\
</div>\
</td>\
<td width="1%" class="line_cost">\
  <div class="view">\
  <span class="amount">'+item.price_html+'</span><input type="hidden" class="product_price" value="'+item.price+'">\
  <input type="hidden" class="product_line_tax" value="'+tax+'">\
</div>\
</td>\
<td class="remove_item">\
  <a href="#" class="remove_order_item tips" data-tip="'+wc_pos_params.remove_button+'"></a>\
</td>\
</tr>';
                jQuery('#wc-pos-register-data #order_items_list').append(row);
                }
            }
            reloadKeypad();
            runTipTip();
            calculateDiscount();
            calcRegisterTotal();
        }, null)
    });    
}

function errorHandler(transaction, error)
{
    // error.message is a human-readable string.
    // error.code is a numeric error code
    console.log(transaction);
    alert('Oops.  Error was '+error.message+' (Code '+error.code+')');
 
    // Handle errors here
    var we_think_this_error_is_fatal = true;
    if (we_think_this_error_is_fatal) return true;
    return false;
}
 
function dataHandler(transaction, results)
{
    // Handle the results
    var string = "Green shirt list contains the following people:\n\n";
    for (var i=0; i<results.rows.length; i++) {
        // Each row is a standard JavaScript array indexed by
        // column names.
        var row = results.rows.item(i);
        string = string + row['title'] + " (ID "+row['id']+")\n";
    }
    alert(string);
}
function nullDataHandler(transaction, results) { }