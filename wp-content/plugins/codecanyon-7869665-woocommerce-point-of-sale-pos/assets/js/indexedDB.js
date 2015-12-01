window.indexedDB = window.indexedDB || window.mozIndexedDB || window.webkitIndexedDB || window.msIndexedDB;
window.IDBTransaction = window.IDBTransaction || window.webkitIDBTransaction || window.msIDBTransaction;
window.IDBKeyRange = window.IDBKeyRange || window.webkitIDBKeyRange || window.msIDBKeyRange;

    const DB_NAME = 'WoocommercePointOfSale';
    const DB_VERSION = 1; // Use a long long for this value (don't use a float)
    const DB_STORE_NAME = 'products';
    var db;
    var pub_msg;


function openDb() {
    console.log("openDb ...");
    wc_pos_params.errors_message = "Couldn't open database";
    pub_msg = jQuery('#message_pos');

    var del = indexedDB.deleteDatabase(DB_NAME);
    del.onsuccess = function () {
        console.log("Deleted database successfully");
    }
    del.onerror = function () {
        displayLocked("Couldn't delete database");
    }
    del.onblocked = function(evt) {
        displayLocked('');
    }
    
    var req = indexedDB.open(DB_NAME, DB_VERSION);
    req.onsuccess = function (evt) {
        db = this.result;
        console.log("openDb DONE");
        displayUnLocked();
        addEventListeners_indexedDB();
    }
    req.onerror = function (evt) {
        displayLocked(wc_pos_params.errors_message);
        console.error("openDb:", evt.target.errorCode);
    }

    req.onupgradeneeded = function (evt) {
        console.log("openDb.onupgradeneeded");
        db = this.result;

        var open = true;

        var data = {
            action      : 'wc_pos_json_search_products_all'
        };
        var productsData;
        jQuery.ajax({
          type: 'GET',
          async: false,
          url: wc_pos_params.wc_api_url+'products/',
          data: data,
          success: function(response) {
              if (response) {
                productsData = response.products;                
              }
          },
          error: function(response) {
            var responseText = JSON.parse(response.responseText);
                if(typeof responseText.errors != undefined){
                    wc_pos_params.errors_message = responseText.errors.message;
                }else{
                    wc_pos_params.errors_message = response.responseText;
                }
          }
        });

                
        var store = db.createObjectStore( DB_STORE_NAME, { keyPath: "id" });
        
        store.createIndex("title", "title", { unique: false });
        store.createIndex("barcode", "barcode", { unique: false });
        var html_products = '';
        jQuery.each(productsData, function(i, data){
            if(typeof data.id != 'undefined' && typeof data.f_title != 'undefined'){

                if(typeof data.variations != 'undefined' && data.type == 'variable'){
                    jQuery.each(data.variations, function(j, var_data){

                        var_data.attributes = JSON.stringify(var_data.attributes);                
                        store.add(var_data);
                        html_products += '<option value="' + var_data.id +'">'+var_data.f_title+'</option>';    
                    });
                }
                else
                {
                    data.attributes = JSON.stringify(data.attributes);                
                    store.add(data);
                    html_products += '<option value="' + data.id +'">'+data.f_title+'</option>';    
                }                

            }else{
                console.log(data);
            }
        });
       
        jQuery('#add_product_id').html(html_products);
        jQuery('select.ajax_chosen_select_products_and_variations').chosen();
    }    
  }
function getObjectStore(store_name, mode) {
    var tx = db.transaction(store_name, mode);
    return tx.objectStore(store_name);
}
function clearObjectStore(store_name) {
    var store = getObjectStore(DB_STORE_NAME, 'readwrite');
    var req = store.clear();
    req.onsuccess = function(evt) {
      displayActionSuccess("Store cleared");
      //displayPubList(store); ***************************************************************************
    };
    req.onerror = function (evt) {
      console.error("clearObjectStore:", evt.target.errorCode);
      displayActionFailure(this.error);
    };
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
function addEventListeners_indexedDB() {
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
            calculateDiscount();
            calcRegisterTotal();
            $('#tiptip_holder').hide().css( {margin: '-100px 0 0 -100px'});
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
            if (code.trim() != ''){
                var store = getObjectStore(DB_STORE_NAME, 'readonly');
                var index = store.index("barcode");
                index.get(code).onsuccess = function(evt) {
                    var value = evt.target.result;
                    if(typeof value != 'undefined'){
                        var id = value.id;
                        addProduct(id, 'no');
                    }else{
                        pub_msg.html('<p>'+wc_pos_params.cannot_be_purchased+'</p>').show();
                        return;
                    }
                };
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

    var store = getObjectStore(DB_STORE_NAME, 'readonly');

    pub_msg.empty().hide();

    req = store.get(parseInt(pid));
    req.onsuccess = function (evt) {
        var value = evt.target.result;
        if(typeof value == 'undefined' || parseFloat(value.price) <= 0.00){
            pub_msg.html('<p>'+wc_pos_params.cannot_be_purchased+'</p>').show();
            return;
        }

        var attributes = '';
        var row_class  = 'product_id_'+value.id;
        var exit = false;
        var need_attributes = {};
        if(value.attributes != ""){
            
            var v_data = JSON.parse(value.attributes);

            var attr = "";
            
            jQuery.each(v_data, function(index, val) {
                var hidden = '';
                if(val["option"] == ''){
                    if(typeof humaniz_settings != 'undefined' ){
                        if( humaniz_settings[val['name']] ){

                            val["option"] = humaniz_settings[ val['name'] ].value;
                            var taxonomy = humaniz_settings[ val['name'] ].taxonomy;
                            hidden += '<input type="hidden" name="variations['+value.id+']['+taxonomy+']" value="'+val["option"]+'"/>';   

                        }
                    }else{
                        need_attributes[ val['name'] ] = true;
                        exit = true;
                        return
                    }
                }else{
                    if(typeof value.parent_attr == 'string'){
                        var product_attributes = jQuery.parseJSON(value.parent_attr);
                    }else{
                        var product_attributes = value.parent_attr;
                    }
                    for(var att in product_attributes){

                        if (product_attributes.hasOwnProperty(att) ){
                            var attribute = product_attributes[att];
                            
                            if(attribute.name == val['name']){
                                var taxonomy = 'attribute_'+attribute.taxonomy;
                                hidden += '<input type="hidden" name="variations['+value.id+']['+taxonomy+']" value="'+val["option"]+'"/>';   
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
            popupChooseAttributes(value, need_attributes);
            return;
        }

        var new_item = jQuery('#wc-pos-register-data #order_items_list .' + row_class);


        var stock = value.stock_quantity;
        if (new_item.length > 0) {
            var el = new_item.find('input.quantity');
            var qt = parseInt(el.val());
            if(el.val() == '') qt = 0;
            qt = qt+1;

            if(!value.in_stock || ( stock < qt && stock != '') ){
                var txt = wc_pos_params.cannot_add_product.replace('%NAME%', value.title);
                    txt = txt.replace('%COUNT%', stock);
                 pub_msg.html('<p>'+txt+'</p>').show();

                 return;
            }
            el.val(qt);
        }else{
            if(!value.in_stock || ( stock < 1 && stock != '')){
                var txt = wc_pos_params.cannot_add_product.replace('%NAME%', value.title);
                    txt = txt.replace('%COUNT%', stock);
                 pub_msg.html('<p>'+txt+'</p>').show();
                 return;
            }
            var variation = '';

            
            var tax = calc_tax(value);
            var tip = '<strong>Product ID:</strong>'+value.id+'<br>';
            var title = '';
            if(value.barcode != ''){
                title += value.barcode + " &ndash; ";
                tip += '<strong>Product SKU:</strong>'+value.barcode+'<br>';
            }
            title += '<a href="'+wc_pos_params.admin_url+'/post.php?post='+value.id+'&amp;action=edit" >'+value.title+'</a>';



            var row = '\
<tr class="item '+row_class+' new_row" data-order_item_id="">\
    <td class="thumb">\
        <a href="'+wc_pos_params.admin_url+'/post.php?post='+value.id+'&amp;action=edit" class="tips" data-tip="'+tip+'"><img width="90" height="90" src="'+value.featured_src+'" class="attachment-shop_thumbnail wp-post-image"></a>\
    </td>\
<td class="name">\
'+title+'\
<input type="hidden" class="product_item_id" name="product_item_id[]" value="'+value.id+'">\
<input type="hidden" class="order_item_id" name="order_item_id[]" value="">\
'+attributes+'\
</td>\
<td width="1%" class="quantity">\
  <div class="edit">\
  <input type="text" min="0" autocomplete="off" name="order_item_qty['+value.id+']" placeholder="0" value="1" class="quantity">\
</div>\
</td>\
<td width="1%" class="line_cost">\
  <div class="view">\
  <span class="amount">'+value.price_html+'</span><input type="hidden" class="product_price" value="'+value.price+'">\
  <input type="hidden" class="product_line_tax" value="'+tax+'">\
</div>\
</td>\
<td class="remove_item">\
  <a href="#" class="remove_order_item tips" data-tip="'+wc_pos_params.remove_button+'"></a>\
</td>\
</tr>';
        jQuery('#wc-pos-register-data #order_items_list').append(row);
        }
        reloadKeypad();
        runTipTip();
        calculateDiscount();
        calcRegisterTotal();
    };
}