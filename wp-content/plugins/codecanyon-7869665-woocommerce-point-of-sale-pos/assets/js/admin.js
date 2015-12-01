
var reloadKeypad, runKeypad, runTipTip, getTotalPriceNumber, calcRegisterTotal, calculateDiscount, getTotalTaxNumber, calculateDiscountKeyPad, calculateAmountTendered, calc_tax, runVariantion, find_matching_variations, variations_match;

jQuery('document').ready(function($) {

    if($('#btn_retrieve_from').length > 0){
        $('#btn_retrieve_from').click(function(){
            var id_register = $('#bulk-action-selector-top').val();
            var curent_id   = $('#id_register').val(); //retrieve_sales_popup_title
            var name        = $('#bulk-action-selector-top').find(":selected").data('name');
            if(id_register != ''){
                $('body, #retrieve_sales_popup').block({message: null, overlayCSS: {background: '#fff url(' + wc_pos_params.ajax_loader_url + ') no-repeat center', backgroundSize: '16px 16px', opacity: 0.6}});
                //$('#retrieve_sales_popup_content').html('');
                $('#retrieve_sales_popup_title i').html(name);
                retrieve_sales(id_register);
            }
            return false;
        });
    }

    $(".cb-switcher").click(function(){
        var parent = $(this).parents('.switch');
        $('.selected',parent).removeClass('selected');
        $(this).addClass('selected');
    });

    if($('input[name="products_or_cat"]').length > 0){
        $('input[name="products_or_cat"]').change(function(){
            var type = $('input[name="products_or_cat"]:checked').val();
            if(type == 'category'){
                $('#products_opt_wrap').hide();
                $('#category_opt_wrap').show();
            }else{
                $('#products_opt_wrap').show();
                $('#category_opt_wrap').hide();
            }
        }).trigger('change');
        $('.category_chosen').css('width', '400px').chosen();
    }


    $('select#woocommerce_pos_register_discount_presets').css('width', '400px').chosen({max_selected_options: 4});
    var z = '00';

    calc_tax = function(value){
        var tax = 0;
        if(wc_pos_params.wc.calc_taxes == 'yes'){
            tax = value.pr_inc_tax - value.pr_excl_tax;
        }
        return tax;
    }
    popupChooseAttributes = function(value, need_attributes){

        if(typeof value.parent_attr == 'string'){
            var product_attributes = jQuery.parseJSON(value.parent_attr);
        }else{
            var product_attributes = value.parent_attr;
        }
        var html = '';
        for(var attr in product_attributes){

            if (product_attributes.hasOwnProperty(attr) ){
                var attribute = product_attributes[attr];

                if(need_attributes[attribute.name]){

                    html += '<tr>\
                        <td>'+attribute.name+'</td>\
                    <td><select data-label="'+attribute.name+'" data-taxonomy="'+attribute.taxonomy+'">';

                    for(var opt in attribute.options){

                        if (attribute.options.hasOwnProperty(opt) ){

                            var options = attribute.options[opt];

                            html += '<option value="'+options.slug+'">'+options.name+'</option>'

                        }
                    }

                    html += '</select></td></tr>';

                }

            }

        }
        if(html){
            html = '<table>'+html+'</table>';
            $('#popup_choose_attributes_inner').html(html+'<input type="button" value="Add" class="button button-primary" id="add_pr_variantion_popup">');
            $('#popup_choose_attributes_inner').data('id', value.id);
            $('#popup_choose_attributes').show();
        }
        
    }
    runVariantion = function(el){
        var $ = jQuery;
        var id             = el.find('a').data( 'id');
        var all_variations = el.find('a').data( 'product_variations' );
        var html           = el.find('.hidden').html();
        $('#var_tip_over').remove();
        $('#var_tip').remove();
        var var_tip = '<div id="var_tip" data-id="'+id+'" ><div id="var_tip_arrow"><div id="var_tip_arrow_inner"></div></div><div id="var_tip_content">'+html+' <input type="button" id="add_pr_variantion" class="button button-primary" value="Add"/></div></div><a href="#" id="var_tip_over"></a>';
        $('body').append(var_tip);
        $('#var_tip').data('product_variations', all_variations );
        var t = el.offset().top;
        var l = el.offset().left+1;
        var h = el.height();
        var def = el.data('default_selection');
        var new_t = t+h+2;

        el.addClass('hover');
        if(def && def != ''){
            def = JSON.parse(def);      
            $.each(def, function(i, v){
                $('#var_tip_content select#'+v[1]).val(v[2]).change();
            });
        }

        $('#var_tip').css({
            top: new_t,
            left: l
        }).show();
        $('#var_tip_over').show();    
    }
    find_matching_variations = function( product_variations, settings ) {
        var matching = [];

        for ( var i = 0; i < product_variations.length; i++ ) {
            var variation = product_variations[i];
            var variation_id = variation.variation_id;

            if ( variations_match( variation.attributes, settings ) ) {
                matching.push( variation );
            }
        }

        return matching;
    };
    variations_match = function( attrs1, attrs2 ) {
        var match = true;

        for ( var attr_name in attrs1 ) {
            if ( attrs1.hasOwnProperty( attr_name ) ) {
                var val1 = attrs1[ attr_name ];
                var val2 = attrs2[ attr_name ];

                if ( val1 !== undefined && val2 !== undefined && val1.length !== 0 && val2.length !== 0 && val1 !== val2 ) {
                    match = false;
                }
            }
        }

        return match;
    };
    calculateQuantity = function(input, action){
        var val = $(input).val();
        if(val == '') val = 0;
        val = parseInt(val);
        if(action == 'plus'){
            val = val +1;
        }
        if(action == 'minus'){
            val = val-1;
            if(val < 0 ) val = 0;
        }
        $('.quantity_keypad .keypad-inpval').text(val);
        $(input).val(val);
    }
    calculateDiscountKeyPad = function(){
        var discount_prev = $('#order_discount_prev').val();
        var discount_val  = 0;
        var percent       = 0;
        var total         = getTotalPriceNumber();
        var symbol        = $('#order_discount_symbol').val();
        if (discount_prev && total) {
            discount_prev = parseFloat( discount_prev.replace("/\%/g", "") );

            if(symbol == 'percent_symbol'){
                percent      = discount_prev;
                discount_val = (total*percent/100).toFixed(2);
                $('.discount_keypad .keypad-discount_val1').text( discount_val );
                $('.discount_keypad .keypad-discount_val2').text( percent );
            }else{
                percent      = (discount_prev*100/total).toFixed(2);
                discount_val = discount_prev;
                $('.discount_keypad .keypad-discount_val1').text( percent +'% off ');
                $('.discount_keypad .keypad-discount_val2').text( discount_val );
            }
        }else{
            if(symbol == 'percent_symbol'){
                $('.discount_keypad .keypad-discount_val1').text( '0.00' );
                $('.discount_keypad .keypad-discount_val2').text( discount_prev );
            }else{
                $('.discount_keypad .keypad-discount_val1').text( '0% off ' );
                $('.discount_keypad .keypad-discount_val2').text( discount_prev );                    
            }
        }
    }

    calculateDiscount = function(){
        var discount_prev = $('#order_discount_prev').val();
        var discount_val  = 0;
        var percent       = 0;
        var total         = getTotalPriceNumber();
        var symbol        = $('#order_discount_symbol').val();
        if (discount_prev && total) {
            discount_prev = parseFloat( discount_prev.replace("/\%/g", "") );

            if(symbol == 'percent_symbol'){
                percent      = discount_prev;
                discount_val = (total*percent/100).toFixed(2);
                $('.discount_keypad .keypad-discount_val1').text( discount_val );
                $('.discount_keypad .keypad-discount_val2').text( percent );
            }else{
                percent      = (discount_prev*100/total).toFixed(2);
                discount_val = discount_prev;
                $('.discount_keypad .keypad-discount_val1').text( percent +'% off ');
                $('.discount_keypad .keypad-discount_val2').text( discount_val );
            }
            var formatted_discount = accounting.formatMoney(discount_val, {
                symbol: wc_pos_params.currency_format_symbol,
                decimal: wc_pos_params.currency_format_decimal_sep,
                thousand: wc_pos_params.currency_format_thousand_sep,
                precision: wc_pos_params.currency_format_num_decimals,
                format: wc_pos_params.currency_format
            });
            if(percent){
                formatted_discount = formatted_discount + ' ('+percent+'%)';
            }
            if ($('#tr_order_discount').length > 0) {
                $('#formatted_order_discount').text(formatted_discount);
                $('#order_discount').val(discount_val);
            } else {
                $('table.woocommerce_order_items #tr_order_total_label').before('\
                      <tr id="tr_order_discount">\
                        <th class="total_label">Order Discount</th>\
                        <td class="total_amount">\
                        <input type="hidden" value="'+discount_val+'" id="order_discount" name="order_discount">\
                        <strong id="formatted_order_discount" >' + formatted_discount + '</strong>\
                        <span id="span_clear_order_discount"></span>\
                        </td>\
                      </tr>');
            }
        }else{
            if(symbol == 'percent_symbol'){
                $('.discount_keypad .keypad-discount_val1').text( '0.00' );
                $('.discount_keypad .keypad-discount_val2').text( '0' );
            }else{
                $('.discount_keypad .keypad-discount_val1').text( '0% off ' );
                $('.discount_keypad .keypad-discount_val2').text( '0.00' );                    
            }
            $('#tr_order_discount').remove();
        }
    }

    var amount_tendered = {
        'tendered_1' : '0.00',
        'tendered_2' : '',
        'tendered_3' : '',
        'tendered_4' : '',
      };
    calculateAmountTendered = function(){
      var total_amount = $("#show_total_amt_inp").val();
          if(total_amount == '') total_amount = 0.00;
          else total_amount = parseFloat(total_amount).toFixed(2);

      amount_tendered = {
        'tendered_1' : '0.00',
        'tendered_2' : '',
        'tendered_3' : '',
        'tendered_4' : '',
      };     

      amount_tendered.tendered_1 = total_amount;
      var t_2 = 0;
      
      var decimal_p = parseFloat( ( total_amount - parseInt(total_amount) ).toFixed(2) );
      if(decimal_p > 0){
        t_2 = Math.ceil(total_amount);
      }else{
        t_2 = total_amount+1;
      }

      var t2_cel = Math.ceil(t_2);
      if( t2_cel == parseInt(t_2) )
        t2_cel = t2_cel+1;

      var t_3 = Math.ceil( t2_cel / 5 ) * 5;

      var t_4 = Math.ceil( t_3 / 10 ) * 10;
      if(t_4 == t_3){
        t_4 = Math.ceil( (t_3+1) / 10 ) * 10;
      }
      var i = 2;

      if( t_2 != total_amount && t_2 > total_amount ){
        amount_tendered['tendered_'+i] = parseFloat(t_2).toFixed(2);
        i++;
      }
      if( t_3 != t_2 && t_3 > t_2){
        amount_tendered['tendered_'+i] = parseFloat(t_3).toFixed(2);
        i++;
      }
      if( t_4 != t_3 && t_4 > t_3){
        amount_tendered['tendered_'+i] = parseFloat(t_4).toFixed(2);
        i++;
      }

      
      for(var key in amount_tendered) {

         if (amount_tendered.hasOwnProperty(key)) {

            var value = amount_tendered[key];

            if(value){
              $('.amount_pay_keypad .keypad-'+key).text(value).show();
            }else{
              $('.amount_pay_keypad .keypad-'+key).text('').hide();
            }
         }

      }

      if(!amount_tendered.tendered_1)
        $('.amount_pay_keypad .keypad-tendered_1').text('0.00').show();

    }

    if ($('#wc-pos-register-data').length > 0){

        $.keypad.setDefaults({
            showAnim : 'slideDown',
            duration : 'fast'
        });

        $.keypad.addKeyDef('PLUS', 'plus', function(inst) {
            calculateQuantity(this, 'plus');
            calculateDiscount();
            calcRegisterTotal();
            this.focus();
        }); 
        $.keypad.addKeyDef('MINUS', 'minus', function(inst) {
            calculateQuantity(this, 'minus');
            calculateDiscount();
            calcRegisterTotal();
            this.focus();
        });
        $.keypad.addKeyDef('INPVAL', 'inpval', function(inst) {this.focus();});

        $.keypad.addKeyDef('FIVEPERCENT', 'fivepercent', function(inst) {
            $(this).val(wc_pos_params.discount_presets[0]);
            $('#order_discount_symbol').val('percent_symbol');

            $('.keypad-currency_symbol').removeClass('active');
            $('.keypad-percent_symbol').addClass('active');
            calculateDiscountKeyPad();
            this.focus();
        }); 

        $.keypad.addKeyDef('TENPERCENT', 'tenpercent', function(inst) {
            $(this).val(wc_pos_params.discount_presets[1]);
            $('#order_discount_symbol').val('percent_symbol');

            $('.keypad-currency_symbol').removeClass('active');
            $('.keypad-percent_symbol').addClass('active');
            calculateDiscountKeyPad();
            this.focus();
        });

        $.keypad.addKeyDef('FIFTEENPERCENT', 'fifteenpercent', function(inst) {
            $(this).val(wc_pos_params.discount_presets[2]);
            $('#order_discount_symbol').val('percent_symbol');

            $('.keypad-currency_symbol').removeClass('active');
            $('.keypad-percent_symbol').addClass('active');
            calculateDiscountKeyPad();
            this.focus();
        });

        $.keypad.addKeyDef('TWENTYPERCENT', 'twentypercent', function(inst) {
            $(this).val(wc_pos_params.discount_presets[3]);
            $('#order_discount_symbol').val('percent_symbol');

            $('.keypad-currency_symbol').removeClass('active');
            $('.keypad-percent_symbol').addClass('active');
            calculateDiscountKeyPad();
            this.focus();
        });

        $.keypad.addKeyDef('CURRENCY_SYMBOL', 'currency_symbol', function(inst) { 
            $('#order_discount_symbol').val('currency_symbol');

            $('.keypad-percent_symbol').removeClass('active');
            $('.keypad-currency_symbol').addClass('active');
            calculateDiscountKeyPad();
            this.focus();
        });

        $.keypad.addKeyDef('PERCENT_SYMBOL', 'percent_symbol', function(inst) { 
            $('#order_discount_symbol').val('percent_symbol');

            $('.keypad-currency_symbol').removeClass('active');
            $('.keypad-percent_symbol').addClass('active');
            calculateDiscountKeyPad();
            this.focus();
        });
        $.keypad.addKeyDef('DISCOUNT_VAL1', 'discount_val1', function(inst) {this.focus();});

        $.keypad.addKeyDef('DISCOUNT_VAL2', 'discount_val2', function(inst) {this.focus();});

        $.keypad.addKeyDef('TENDERED_1', 'tendered_1', function(inst) {
            $(this).val(amount_tendered.tendered_1).change();
            this.focus();
        });
        $.keypad.addKeyDef('TENDERED_2', 'tendered_2', function(inst) {
            $(this).val(amount_tendered.tendered_2).change();
            this.focus();
        });
        $.keypad.addKeyDef('TENDERED_3', 'tendered_3', function(inst) {
            $(this).val(amount_tendered.tendered_3).change();
            this.focus();
        });
        $.keypad.addKeyDef('TENDERED_4', 'tendered_4', function(inst) {
            $(this).val(amount_tendered.tendered_4).change();
            this.focus();
        });
    }

    reloadKeypad = function() {
        if(isTouchDevice() == false){            

            $('#wc-pos-register-data tr.new_row input.quantity').keypad({
                keypadOnly: false,
                keypadClass : 'quantity_keypad',
                separator  : '|', 
                layout     : [ $.keypad.MINUS+'|'+$.keypad.INPVAL+'|'+$.keypad.PLUS, '1|2|3|' + $.keypad.CLEAR, '4|5|6|' + $.keypad.BACK, '7|8|9|' + $.keypad.CLOSE, '0|.|00'],
                closeText  : '',
                plusText   : '+',
                minusText  : '-',
                inpvalText  : '0',
                minusStatus: 'Minus', 
                plusStatus : 'Plus', 
                inpvalStatus : 'Quantity', 
                prompt     : 'Quantity',
                onKeypress : function(key, value, inst) { 
                    calculateDiscount();
                    calcRegisterTotal();
                    $('.quantity_keypad .keypad-inpval').text(value);
                },
                beforeShow : function(div, inst) { 
                    var val = $(inst.elem).val();
                    $('.quantity_keypad .keypad-inpval').text(val);
                }
            }).click(function(){
                this.select();
            }).keyup(function(ev){
                calculateDiscount();
                calcRegisterTotal();
            }).keydown(function(ev){
                $('.keypad-popup').hide();
                if ( ev.which == 13 ) {
                    ev.preventDefault();
                }
            });
        
        }else{
            $('#wc-pos-register-data tr.new_row input.quantity').attr('type', 'number');
        }
    }
    runKeypad = function() {
        if(isTouchDevice() == false){
            if ($('#amount_pay_cod').length >0 ){
                $('#amount_pay_cod').keypad({
                    keypadOnly: false,
                    keypadClass : 'amount_pay_keypad',
                    separator: '|',
                    layout: [
                      '1|2|3|' + $.keypad.CLEAR +'|' + $.keypad.TENDERED_1,
                      '4|5|6|' + $.keypad.BACK +'|' + $.keypad.TENDERED_2,
                      '7|8|9|' + $.keypad.CLOSE +'|' + $.keypad.TENDERED_3,
                      '0|.|00|' + $.keypad.TENDERED_4
                      ],
                    closeText      : '',
                    tendered_1Text : '',
                    tendered_2Text : '',
                    tendered_3Text : '',
                    tendered_4Text : '',

                    tendered_1Status : '',
                    tendered_2Status : '',
                    tendered_3Status : '',
                    tendered_4Status : '',
                    beforeShow : calculateAmountTendered,
                }).click(function(){
                    this.select();
                }).keyup(function(ev){
                    $(this).trigger('change');
                }).keydown(function(ev){
                    $('.keypad-popup').hide();
                });
            }
            if ($('#order_discount_prev').length >0 ){

                $('#order_discount_prev').keypad({
                    keypadOnly: false,
                    beforeShow : function(div, inst) { 
                        var order_discount_symbol  = $('#order_discount_symbol').val();
                        $('.discount_keypad .keypad-currency_symbol, .discount_keypad .keypad-percent_symbol').removeClass('active');
                        $('.discount_keypad .keypad-'+order_discount_symbol).addClass('active');
                        calculateDiscountKeyPad();
                    },
                    onKeypress : calculateDiscountKeyPad,

                    keypadClass : 'discount_keypad',
                    separator: '|',
                    layout: [
                        $.keypad.DISCOUNT_VAL1+'|'+ $.keypad.CURRENCY_SYMBOL+'|'+$.keypad.DISCOUNT_VAL2+'|'+$.keypad.PERCENT_SYMBOL,
                        '1|2|3|' + $.keypad.CLEAR +'|' + $.keypad.FIVEPERCENT,
                        '4|5|6|' + $.keypad.BACK+'|' + $.keypad.TENPERCENT,
                        '7|8|9|' + $.keypad.CLOSE + '|' + $.keypad.FIFTEENPERCENT,
                        '0|.|00|' + $.keypad.TWENTYPERCENT
                        ],
                    closeText  : '',

                    discount_val1Text    : '0% off',
                    currency_symbolText  : wc_pos_params.currency_format_symbol,
                    discount_val2Text    : '0',
                    percent_symbolText   : '%',

                    discount_val1Status   : '',
                    currency_symbolStatus : '',
                    discount_val2Status   : '',
                    percent_symbolStatus  : '',

                    fivepercentText    : wc_pos_params.discount_presets[0]+'%',
                    tenpercentText     : wc_pos_params.discount_presets[1]+'%',
                    fifteenpercentText : wc_pos_params.discount_presets[2]+'%',
                    twentypercentText  : wc_pos_params.discount_presets[3]+'%',

                    fivepercentStatus    : '',
                    tenpercentStatus     : '',
                    fifteenpercentStatus : '',
                    twentypercentStatus  : '',
                }).click(function(){
                    this.select();
                }).keydown(function(ev){
                    $('.keypad-popup').hide();
                    if ( ev.which == 13 ) {
                        $('#save_order_discount').trigger('click');
                        ev.preventDefault();
                    }
                });
            }

            if ($('#wc-pos-register-data tr input.quantity').length == 0)
                return;
            $('#wc-pos-register-data tr input.quantity').keypad({
                keypadOnly: false,
                keypadClass : 'quantity_keypad',
                separator  : '|', 
                layout     : [ $.keypad.MINUS+'|'+$.keypad.INPVAL+'|'+$.keypad.PLUS, '1|2|3|' + $.keypad.CLEAR, '4|5|6|' + $.keypad.BACK, '7|8|9|' + $.keypad.CLOSE, '0|.|00'],
                closeText  : '',
                plusText   : '+',
                minusText  : '-',
                inpvalText  : '0',
                minusStatus: 'Minus', 
                plusStatus : 'Plus', 
                inpvalStatus : 'Quantity', 
                prompt     : 'Quantity',
                onKeypress : function(key, value, inst) { 
                    calculateDiscount();
                    calcRegisterTotal();
                    $('.quantity_keypad .keypad-inpval').text(value);
                },
                beforeShow : function(div, inst) {
                    var val = $(inst.elem).val();
                    $('.quantity_keypad .keypad-inpval').text(val);
                }
            }).click(function(){
                this.select();
            }).keyup(function(ev){
                calculateDiscount();
                calcRegisterTotal();
            }).keydown(function(ev){
                $('.keypad-popup').hide();
                if ( ev.which == 13 ) {
                    ev.preventDefault();
                }
            });
        }else{
            if ($('#amount_pay_cod').length >0 ){
                $('#amount_pay_cod').attr('type', 'number');
                $('#order_discount_prev').attr('type', 'number');
            }
            if ($('#wc-pos-register-data tr input.quantity').length == 0)
                return;
            $('#wc-pos-register-data tr input.quantity').attr('type', 'number');
        }
    }
    runTipTip = function() {
        // remove any lingering tooltips
        $('#tiptip_holder').removeAttr('style');
        $('#tiptip_arrow').removeAttr('style');
        if(isTouchDevice() === false){
            // init tiptip
            $('.tips').tipTip({
                'attribute': 'data-tip',
                'fadeIn': 50,
                'fadeOut': 50,
                'delay': 200
            });
        }
    }
    getTotalPriceNumber = function() {
        var total = 0;
        $('#wc-pos-register-data .product_price').each(function() {
            var qt = $(this).parents('tr.item').find('input.quantity').val();
            var formatted_total_pr = accounting.formatMoney($(this).val() * qt, {
                symbol: wc_pos_params.currency_format_symbol,
                decimal: wc_pos_params.currency_format_decimal_sep,
                thousand: wc_pos_params.currency_format_thousand_sep,
                precision: wc_pos_params.currency_format_num_decimals,
                format: wc_pos_params.currency_format
            });
            total = total + accounting.unformat($(this).val() * qt, wc_pos_params.mon_decimal_point);
        });
        return total;
    }
    getTotalTaxNumber = function() {
        var number_tax = 0;
        $('#wc-pos-register-data .product_line_tax').each(function() {
            var qt = $(this).parents('tr.item').find('input.quantity').val();
            number_tax = number_tax + accounting.unformat($(this).val()* qt, wc_pos_params.mon_decimal_point);
        });
        number_tax = round(number_tax, wc_pos_params.currency_format_num_decimals, wc_pos_params.tax_rounding_mode);

        return number_tax;
    }
    calcRegisterTotal = function() {
        var number_total = getTotalPriceNumber();
        var number_tax = getTotalTaxNumber();
        var discount = 0;

        
        if ($('#order_discount').length > 0) {
            discount = $('#order_discount').val();
        }

        var formatted_sub_total = accounting.formatMoney(number_total, {
            symbol: wc_pos_params.currency_format_symbol,
            decimal: wc_pos_params.currency_format_decimal_sep,
            thousand: wc_pos_params.currency_format_thousand_sep,
            precision: wc_pos_params.currency_format_num_decimals,
            format: wc_pos_params.currency_format
        });
        var formatted_tax = accounting.formatMoney(number_tax, {
            symbol: wc_pos_params.currency_format_symbol,
            decimal: wc_pos_params.currency_format_decimal_sep,
            thousand: wc_pos_params.currency_format_thousand_sep,
            precision: wc_pos_params.currency_format_num_decimals,
            format: wc_pos_params.currency_format
        });

        if($('select#shipping_method_0').length > 0){
            number_total = number_total + parseFloat($('select#shipping_method_0 option:selected').attr('data-cost') );
        }else if($('input#shipping_method_0').length > 0){
            number_total = number_total + parseFloat( $('input#shipping_method_0').attr('data-cost') );
        }
        var formatted_total = accounting.formatMoney(number_total - discount, {
                symbol: wc_pos_params.currency_format_symbol,
                decimal: wc_pos_params.currency_format_decimal_sep,
                thousand: wc_pos_params.currency_format_thousand_sep,
                precision: wc_pos_params.currency_format_num_decimals,
                format: wc_pos_params.currency_format
            });

        $('#subtotal_amount').html(formatted_sub_total);
        $('#total_amount').html(formatted_total);
        $('#tax_amount').html(formatted_tax);
        var p_p = number_total - discount;

        if(wc_pos_params.pos_calc_taxes == 'enabled'){
            var formatted_total = accounting.formatMoney(number_tax + number_total - discount, {
                symbol: wc_pos_params.currency_format_symbol,
                decimal: wc_pos_params.currency_format_decimal_sep,
                thousand: wc_pos_params.currency_format_thousand_sep,
                precision: wc_pos_params.currency_format_num_decimals,
                format: wc_pos_params.currency_format
            });
            $('#total_amount').html(formatted_total);
            p_p = number_tax + number_total - discount;
        }

        var dd = accounting.formatMoney(p_p, {
                symbol: wc_pos_params.currency_format_symbol,
                decimal: wc_pos_params.currency_format_decimal_sep,
                thousand: wc_pos_params.currency_format_thousand_sep,
                precision: wc_pos_params.currency_format_num_decimals,
                format: wc_pos_params.currency_format
            });
        
        var find = wc_pos_params.currency_format_thousand_sep;
        if(find == '.') find = '\\.';
        var re = new RegExp(find, 'g');
        var total_amount = dd.replace(re, '');
        total_amount = total_amount.replace(wc_pos_params.currency_format_decimal_sep+'00', '');

        $('#show_total_amt').html(total_amount);

        total_amount = total_amount.replace(wc_pos_params.currency_format_symbol, '');
        total_amount = total_amount.replace(',', '.');

        $('#show_total_amt_inp').val(total_amount);
        
    }

    if( jQuery('.post-locked-message.not_close').length == 0 && $('#edit_wc_pos_registers').length > 0){
        if (typeof indexedDB != 'undefined' || window.openDatabase) {
            openDb();
        }else{ 
            addEventListeners_ajax();
            displayUnLocked();
        }
    }
    
    if($('.order_actions a.reprint_receipts').length > 0){
        $('.order_actions a.reprint_receipts').click(function(){
            var str         = $(this).attr('href');
            var res         = str.split("#");
            var order_id    = res[1];
            var receipt_ID  = res[2];
            var outlet_ID   = res[3];
            var register_ID = res[4];
            var data = {
                action: 'wc_pos_printing_receipt',
                security    : wc_pos_params.printing_receipt_nonce,
                order_id    : order_id,
                receipt_ID  : receipt_ID,
                outlet_ID   : outlet_ID,
                register_ID : register_ID,
            };
            var start_print    = false;
            var print_document ='';
            $.ajax({
                type: 'POST',
                async: false,
                url: wc_pos_params.ajax_url,
                data: data,
                success: function(response) {
                    if (response) {
                        start_print    = true;
                        print_document = response;
                    }
                }
            });
            if(start_print){
                if($('#printable').length > 0)
                    $('#printable').remove();
                var newHTML = $('<div id="printable">'+print_document+'</div>');
                $('body').append(newHTML);
                setTimeout(function(){
                    window.print();
                }, 500);
            }
            return false;
        });
    }

if (typeof Stripe == 'function') { 
  Stripe.setPublishableKey( wc_stripe_params.key );
}
    if($('#woocommerce_pos_tax_calculation').length > 0){
        $('.disabled_select').attr('disabled', 'disabled');
        $('#woocommerce_pos_tax_calculation').change(function(){
            if($(this).val() == 'disabled'){
                $('#woocommerce_pos_calculate_tax_based_on').parent().parent().hide();
            }else{
                $('#woocommerce_pos_calculate_tax_based_on').parent().parent().show();
            }   
        }).change();
    }

    if($('#woocommerce_pos_register_layout_text').length > 0){
        
        $('.pos_register_layout_opt').change(function(){
            var val = $('.pos_register_layout_opt:checked').val();
            if( val == 'text' || val == 'company_image_text'){
                $('#woocommerce_pos_register_layout_text').parents('tr').show();
            }else{
                $('#woocommerce_pos_register_layout_text').parents('tr').hide();
            }
            
        }).first().change();
        // Uploading files
        var file_frame;
        var current_shape_image;
        $('#woocommerce_pos_company_logo').click(function(){

            // If the media frame already exists, reopen it.
            if (file_frame) {
                file_frame.open();
                return;
            }

            // Create the media frame.
            file_frame = wp.media.frames.file_frame = wp.media({
                title: "Select a Company Logo", // $(this).data('uploader_title'),
                button: {
                    text: "Set Company Logo", //$(this).data('uploader_button_text'),
                },
                multiple: false,
            });

            // When an image is selected, run a callback.
            file_frame.on( 'select', function() {
                // We set multiple to false so only get one image from the uploader
                attachment = file_frame.state().get('selection').first().toJSON();
                console.log(file_frame);

                // Set the image id/display the image thumbnail
                $('#woocommerce_pos_company_logo_hidden').val(attachment.id);
                $('#woocommerce_pos_company_logo').val("Change");

                $('#woocommerce_pos_company_logo_img').attr('src', attachment.sizes.thumbnail.url);  // TODO: will the thumbnail always be available?
                $('#woocommerce_pos_company_logo_img').show();
            });

            // Finally, open the modal
            file_frame.open();
        });
    }

    /* Default Guest Customer Loading */
    var default_customer = '<tr class="item" data-customer_id="0"> <td class="name">Guest</td><td class="remove_customer"><a data-tip="Remove" class="remove_customer_row tips" href="#"></a> </td> </tr>';
    //$('#wc-pos-customer-data #customer_items_list').html(default_customer);

    $('#order_payment_popup input.select_payment_method').attr('disabled', 'disabled');


    runTipTip();
    runKeypad();
    calculateDiscount();
    calcRegisterTotal();

    if($('#add_wc_pos_registers').length > 0){

        $('#add_wc_pos_registers').submit(function(){
            var err = 0;
            $('.form-field').removeClass('form-invalid')
            if($('#_register_name').val() == ''){
                $('#_register_name').parents('.form-field').addClass('form-invalid');
                err++;
            }
            if($('#_register_grid_template').val() == '' || $('#_register_grid_template').val() == null){
                $('#_register_grid_template').parents('.form-field').addClass('form-invalid');                
                err++;
            }
            if($('#_register_receipt_template').val() == '' || $('#_register_receipt_template').val() == null){
                $('#_register_receipt_template').parents('.form-field').addClass('form-invalid');                
                err++;
            }
            if($('#_register_outlet').val() == '' || $('#_register_outlet').val() == null){
                $('#_register_outlet').parents('.form-field').addClass('form-invalid');                
                err++;
            }
            if(err){
                $(window).scrollTop(0);
                return false;
            }
            
        });
    }

    $('#add_wc_pos_outlets').submit(function() {
        $('.form-invalid').removeClass('form-invalid');
        var err = 0;
        if ($('#_outlet_name').val() == '') {
            $('#_outlet_name').parent().addClass('form-invalid');
            err++;
        }
        if ($('#_outlet_email').val() != '' && !checkEmail($('#_outlet_email').val())) {
            $('#_outlet_email').parent().addClass('form-invalid');
            err++;
        }
        if ($('#_outlet_phone').val() != '' && !checkPhone($('#_outlet_phone').val())) {
            $('#_outlet_phone').parent().addClass('form-invalid');
            err++;
        }
        if (err) {
            window.scrollTo(0, parseInt($('.form-invalid').first().offset().top) - 100);
            return false;
        }
    });
    $('form#add_wc_pos_outlets select#_outlet_country, form#edit_wc_pos_outlets select#_outlet_country').chosen();
    $('form#add_wc_pos_outlets select#_outlet_state, form#edit_wc_pos_outlets select#_outlet_state ').chosen();
    $('#add_wc_pos_outlets').on('change', '#_outlet_country', function() {
        if ($('form#add_wc_pos_outlets #_outlet_country').val() != '') {
            $('#add_wc_pos_outlets').block({message: null, overlayCSS: {background: '#fff url(' + wc_pos_params.ajax_loader_url + ') no-repeat center', backgroundSize: '16px 16px', opacity: 0.6}});
            var data = {
                action: 'wc_pos_new_update_outlets_address',
                security: wc_pos_params.new_update_pos_outlets_address_nonce,
                name: $('form#add_wc_pos_outlets #_outlet_name').val(),
                country: $('form#add_wc_pos_outlets #_outlet_country').val(),
                address_1: $('form#add_wc_pos_outlets #_outlet_address_1').val(),
                address_2: $('form#add_wc_pos_outlets #_outlet_address_2').val(),
                city: $('form#add_wc_pos_outlets #_outlet_city').val(),
                state: $('form#add_wc_pos_outlets #_outlet_state').val(),
                postcode: $('form#add_wc_pos_outlets #_outlet_postcode').val(),
                email: $('form#add_wc_pos_outlets #_outlet_email').val(),
                phone: $('form#add_wc_pos_outlets #_outlet_phone').val(),
                fax: $('form#add_wc_pos_outlets #_outlet_fax').val(),
                website: $('form#add_wc_pos_outlets #_outlet_website').val(),
                twitter: $('form#add_wc_pos_outlets #_outlet_twitter').val(),
                facebook: $('form#add_wc_pos_outlets #_outlet_facebook').val(),
            };
            if ($('#id_outlet').length > 0) {
                data.ID = $('#id_outlet').val();
            }

            xhr = $.ajax({
                type: 'POST',
                url: wc_pos_params.ajax_url,
                data: data,
                success: function(response) {

                    if (response) {
                        var html = $($.parseHTML($.trim(response)));

                        $('#add_wc_pos_outlets').html(html);
                        //$( 'body' ).trigger('updated_checkout' );
                        $('form#add_wc_pos_outlets select#_outlet_country').chosen();
                        $('form#add_wc_pos_outlets select#_outlet_state').chosen();
                        $('#add_wc_pos_outlets').unblock();
                    }
                }
            });
        }
    });


    $('#edit_wc_pos_outlets').on('change', '#_outlet_country', function() {
        if ($('form#edit_wc_pos_outlets #_outlet_country').val() != '') {
            $('#edit_wc_pos_outlets').block({message: null, overlayCSS: {background: '#fff url(' + wc_pos_params.ajax_loader_url + ') no-repeat center', backgroundSize: '16px 16px', opacity: 0.6}});

            var data = {
                action: 'wc_pos_edit_update_outlets_address',
                security: wc_pos_params.edit_update_pos_outlets_address_nonce,
                name: $('form#edit_wc_pos_outlets #_outlet_name').val(),
                country: $('form#edit_wc_pos_outlets #_outlet_country').val(),
                address_1: $('form#edit_wc_pos_outlets #_outlet_address_1').val(),
                address_2: $('form#edit_wc_pos_outlets #_outlet_address_2').val(),
                city: $('form#edit_wc_pos_outlets #_outlet_city').val(),
                state: $('form#edit_wc_pos_outlets #_outlet_state').val(),
                postcode: $('form#edit_wc_pos_outlets #_outlet_postcode').val(),
                email: $('form#edit_wc_pos_outlets #_outlet_email').val(),
                phone: $('form#edit_wc_pos_outlets #_outlet_phone').val(),
                fax: $('form#edit_wc_pos_outlets #_outlet_fax').val(),
                website: $('form#edit_wc_pos_outlets #_outlet_website').val(),
                twitter: $('form#edit_wc_pos_outlets #_outlet_twitter').val(),
                facebook: $('form#edit_wc_pos_outlets #_outlet_facebook').val(),
                ID: $('form#edit_wc_pos_outlets #id_outlet').val(),
            };

            xhr = $.ajax({
                type: 'POST',
                url: wc_pos_params.ajax_url,
                data: data,
                success: function(response) {

                    if (response) {
                        var html = $($.parseHTML($.trim(response)));
                        $('#edit_wc_pos_outlets').html(html);
                        $('form#edit_wc_pos_outlets select#_outlet_country').chosen();
                        $('form#edit_wc_pos_outlets select#_outlet_state').chosen();
                        $('#edit_wc_pos_outlets').unblock();
                    }
                }
            });
        }
    });
    if($('.wc_pos_register_void').length > 0 ){
        char0 = new Array("§", "32");
        char1 = new Array("˜", "732");
        $('#wc-pos-customer-data').on('click', '.remove_customer_row', function() {
            $(this).closest('tr').remove();
            $('#add_wc_pos_customer')[0].reset();
            return false;
        });
        /****/

        $('#clear_order_discount').on('click', function(){
            if($('#tr_order_discount').length > 0 ){
                $('#tr_order_discount').remove();
            }
            $('#order_discount_prev').val('');
            calculateDiscount();
            calcRegisterTotal();
            $('#overlay_order_discount').hide();
        });
        $('#poststuff').on('click', '#span_clear_order_discount', function(){
            if($('#tr_order_discount').length > 0 ){
                $('#tr_order_discount').remove();
            }
            $('#order_discount_prev').val('');
            calculateDiscount();
            calcRegisterTotal();
            $('#overlay_order_discount').hide();
        });
        $('#save_order_discount').on('click', function() {
            console.log('discount');
            calculateDiscount();
            calcRegisterTotal();
            $('#overlay_order_discount').hide();
        });
        $('#wc-pos-register-buttons').on('click', '.wc_pos_register_discount', function() {
            $('#overlay_order_discount').show();
        });
        $('#wc-pos-customer-data').on('change', '.ajax_chosen_select_customer', function() {
            ids_users = $('#customer_user').val();
            
                $('#wc-pos-customer-data').block({message: null, overlayCSS: {background: '#fff url(' + wc_pos_params.ajax_loader_url + ') no-repeat center', backgroundSize: '16px 16px', opacity: 0.6}});

                    var data = {
                        action: 'wc_pos_add_customers_to_register',
                        user_to_add: ids_users,
                        security: wc_pos_params.add_customers_to_register,
                        register_id: $('#id_register').val(),
                    };

                    $.post(wc_pos_params.ajax_url, data, function(response) {

                        $('#wc-pos-customer-data #customer_items_list').html('').append(response);
                            $('select#customer_user, #customer_user_chosen .chosen-choices').css('border-color', '').val('');
                            $('select#customer_user').trigger("chosen:updated");

                            reloadKeypad();
                            $('#wc-pos-customer-data .new_row').removeClass('new_row');
                            $('#wc-pos-customer-data').unblock();
                    });
                if (ids_users) {
                    if($( '.product_item_id' ).length > 0){
                        var products_ids = $( '.product_item_id' ).serialize();
                        var products_qt = $( '.quantity' ).serialize();
                        var data2 = {
                            action: 'wc_pos_check_shipping',
                            user_to_add: ids_users,
                            security: wc_pos_params.check_shipping,
                            products_ids: products_ids,
                            products_qt: products_qt,
                            register_id: $('#id_register').val(),
                        };

                        $.post(wc_pos_params.ajax_url, data2, function(response) {
                            $( "tr.shipping_methods_register" ).replaceWith( response );
                            $( "tr.shipping_methods_register" ).show();
                            calculateDiscount();
                            calcRegisterTotal();
                        });
                    }
                   

                }else{
                    $( "tr.shipping_methods_register" ).replaceWith( '<tr class="shipping_methods_register"><th></th><td></td></tr>' );
                }
            return false;
        });

        

        $('#wc-pos-register-data').on('change', '#shipping_method_0', function() {
            calculateDiscount();
            calcRegisterTotal();
        });

        $('#wc-pos-register-data').on('change', '.check-column input', function() {
            calculateDiscount();
            calcRegisterTotal();
        });
        $('.wc_pos_register_void').on('click', function() {
            if (confirm(wc_pos_params.void_register_notice)) {
                $('#post-body').block({message: null, overlayCSS: {background: '#fff url(' + wc_pos_params.ajax_loader_url + ') no-repeat center', backgroundSize: '16px 16px', opacity: 0.6}});
                var data = {
                    action      : 'wc_pos_void_products_register',
                    order_id    : $('#order_id').val(),
                    register_id : $('#id_register').val(),
                    security    : wc_pos_params.void_products_register
                };
                $.post(wc_pos_params.ajax_url, data, function(response) {
                    $('#wc-pos-register-data #order_items_list').html('');
                    $('#wc-pos-customer-data #customer_items_list tr').attr('data-customer_id', 0);
                    $('#wc-pos-customer-data #customer_items_list tr td.name').attr('colspan', 2).text('Guest');
                    var default_customer = '<tr class="item" data-customer_id="0"> <td class="name">Guest</td><td class="remove_customer"><a data-tip="Remove" class="remove_customer_row tips" href="#"></a> </td> </tr>';
                    $('#wc-pos-customer-data #customer_items_list').html(default_customer);
                    calculateDiscount();
                    calcRegisterTotal();
                    $('#post-body').unblock();
                });
            }
        });
        var show_p = false;
        if(note_request == 2){
            $('.wc_pos_register_pay').on('click', function() {
                $('.wc_pos_register_notes').click();
                show_p = true;
            });
        
        }else{
            $('.wc_pos_register_pay').on('click', function() {
                $('#order_payment_popup input.select_payment_method').removeAttr('disabled');            
                $('#overlay_order_payment').show();
            });
        }
        var submit_f = false;
        if(note_request == 1){
            $('#edit_wc_pos_registers').submit(function(){
                if(!submit_f){
                    $('#overlay_order_comments').show();
                    submit_f = true;
                    return false
                }
            });
            $('#save_order_comments').on('click', function() {
                $('#overlay_order_comments').hide();
                $('#order_comments_error').hide();
                if(submit_f) $('#edit_wc_pos_registers').submit();
            });
            $('#order_comments_popup .close_popup').click(function () {
                submit_f = false;
            })
        }else if(note_request == 2){
            $('#save_order_comments').on('click', function() {
                if(!show_p){
                    $('#overlay_order_comments').hide();
                }else if(show_p){
                    $('#overlay_order_comments').hide();
                    $('#order_payment_popup input.select_payment_method').removeAttr('disabled');            
                    $('#overlay_order_payment').show();
                }
            });
        }else{
            $('#save_order_comments').on('click', function() {
                $('#overlay_order_comments').hide();
                $('#order_comments_error').hide();
            });
        }
        
        $('.wc_pos_register_notes').on('click', function() {
            $('#overlay_order_comments').show();
            show_p = false;
            submit_f = false;
        });
        
        /* Add Customer Popup open */
        $('#add_customer_to_register').on('click', function() {
            $('#overlay_order_customer').show();
            $('#overlay_order_customer').css('visibility', 'visible');
        });

        $('#retrieve_sales').on('click', function() {
            $('body, #retrieve_sales_popup').block({message: null, overlayCSS: {background: '#fff url(' + wc_pos_params.ajax_loader_url + ') no-repeat center', backgroundSize: '16px 16px', opacity: 0.6}});
            $('#retrieve_sales_popup_content').html('');
            
            retrieve_sales('all');
            return false;
        });
        $('#overlay_retrieve_sales').on('click', '.load_order_data', function() {
            $('#retrieve_sales_popup').block({message: null, overlayCSS: {background: '#fff url(' + wc_pos_params.ajax_loader_url + ') no-repeat center', backgroundSize: '16px 16px', opacity: 0.6}});
            var load_order_id = $(this).attr('href');
                load_order_id = load_order_id.replace("#", "");
            var data = {
                action: 'wc_pos_load_order_data',
                security      : wc_pos_params.load_order_data,
                order_id      : $('#order_id').val(),
                load_order_id : load_order_id,
                register_id   : $('#id_register').val(),
            };
            $.ajax({
                type: 'POST',
                url: wc_pos_params.ajax_url,
                data: data,
                success: function(response) {
                    if (response) {
                        response = JSON.parse(response);
                        $('#wc-pos-register-data #order_items_list').html(response.products);
                        $('tbody#customer_items_list').html(response.customer);
                        if(response.guest_info != '' ){
                            var guest = response.guest_info;
                            $('#add_wc_pos_customer #billing_country').val(guest.billing_country);
                            $('#add_wc_pos_customer #billing_first_name').val(guest.billing_first_name);
                            $('#add_wc_pos_customer #billing_last_name').val(guest.billing_last_name);
                            $('#add_wc_pos_customer #billing_company').val(guest.billing_company);
                            $('#add_wc_pos_customer #billing_address_1').val(guest.billing_address_1);
                            $('#add_wc_pos_customer #billing_address_2').val(guest.billing_address_2);
                            $('#add_wc_pos_customer #billing_city').val(guest.billing_city);
                            $('#add_wc_pos_customer #billing_state').val(guest.billing_state);
                            $('#add_wc_pos_customer #billing_postcode').val(guest.billing_postcode);
                            $('#add_wc_pos_customer #billing_email').val(guest.billing_email);
                            $('#add_wc_pos_customer #billing_phone').val(guest.billing_phone);
                            $('#customer_items_list tr td').first().text(guest.billing_first_name+' '+guest.billing_last_name+'(Guest)');
                        }
                        $('input#order_id').val(load_order_id);
                        runTipTip();
                        reloadKeypad();
                        calculateDiscount();
                        calcRegisterTotal();
                        $('#wc-pos-register-data .new_row').removeClass('new_row');
                        $('.overlay_order_popup').hide();
                        $('#retrieve_sales_popup_content').html('');
                        $('#retrieve_sales_popup').removeAttr('style');
                        $('#retrieve_sales_popup').removeAttr('style');
                    }
                    $('#retrieve_sales_popup').unblock();
                }
            });

            return false;
        });
        if($('#ship-to-different-address-checkbox').length > 0){
            $('#ship-to-different-address-checkbox').change(function () {
                if($(this).is(':checked')){
                    $('.woocommerce-shipping-fields .shipping_address').show();
                }else{
                    $('.woocommerce-shipping-fields .shipping_address').hide();
                }
            }).change();
        }
    }
function retrieve_sales(id){
    var data = {
        action: 'wc_pos_load_pending_orders',
        security    : wc_pos_params.load_pending_orders,
        register_id : id,
        order_id    : $('#order_id').val()
    };
    $.ajax({
        type: 'POST',
        url: wc_pos_params.ajax_url,
        data: data,
        success: function(response) {
            if (response) {
                $('#retrieve_sales_popup_inner').html(response);
                $('#overlay_retrieve_sales').show();
                var tb_h = $('#retrieve_sales_popup_content_scroll table.orders').height();
                var ct_h = $('#retrieve_sales_popup_content_scroll').height();
                if(tb_h < ct_h){
                    $('#retrieve_sales_popup').css({
                        'bottom' : 'auto'
                    })
                    var pop_h = $('#retrieve_sales_popup').height()/2;
                    $('#retrieve_sales_popup').css({
                        'top'        : '20%'
                    });
                }
                $('body, #retrieve_sales_popup').unblock();
            }
        }
    });
}
//function addEventListeners_ajax(){ /******************************************************************************************/
function addEventListeners_ajax(){

    
    $('select.ajax_chosen_select_products_and_variations').ajaxChosen({
          method:   'GET',
          url:      wc_pos_params.ajax_url,
          dataType:   'json',
          afterTypeDelay: 30,
           minTermLength: 1,
           data:   {
            action:     'woocommerce_json_search_products_and_variations',
          security:   wc_pos_params.search_products_and_variations
          }
      }, function (data) {
        var terms = {};

          $.each(data, function (i, val) {
              terms[i] = val;
          });

          return terms;
      });
    $('#wc-pos-register-data').on('click', '.remove_order_item', function() {
        var $el = $(this).closest('tr');
        var id_product = $el.attr('data-order_item_id');
        if (id_product) {
            $('#post-body').block({message: null, overlayCSS: {background: '#fff url(' + wc_pos_params.ajax_loader_url + ') no-repeat center', backgroundSize: '16px 16px', opacity: 0.6}});
            var data = {
                action: 'wc_pos_remove_product_from_register',
                id_product: id_product,
                order_id: $('#order_id').val(),
                security: wc_pos_params.remove_product_from_register,
                register_id: $('#id_register').val(),
            };
            $.post(wc_pos_params.ajax_url, data, function(response) {
                $el.remove();
                calculateDiscount();
                calcRegisterTotal();
                $('#post-body').unblock();
            });
        }
        return false;
    });
    $('#wc-pos-register-grids').on('click', 'td.add_grid_tile', function() {
        var pid = $(this).find('a').attr('data-id');
            addProduct(pid, $(this));
            return false;
    });

    $('#wc-pos-register-data').on('change', 'input.quantity', function() {
        calculateDiscount();
        calcRegisterTotal();
    });
    
    $('#wc-pos-register-data').on('change', '#add_product_id', function() {
        var pid = $('#add_product_id').val()[0];
        addProduct(pid, 'no');
        $('select#add_product_id, #add_product_id_chosen .chosen-choices').css('border-color', '').val('-1');
        $('select#add_product_id').trigger("chosen:updated");
        return false;
    });

    if($('.wc_pos_register_void').length > 0 ){
        char0 = new Array("§", "32");
        char1 = new Array("˜", "732");
        characters = new Array(char0, char1);
        $(document).BarcodeListener(characters, function(code) {
            $('#post-body').block({message: null, overlayCSS: {background: '#fff url(' + wc_pos_params.ajax_loader_url + ') no-repeat center', backgroundSize: '16px 16px', opacity: 0.6}});
            $.get( wc_pos_params.ajax_url+"?action=woocommerce_json_search_products_and_variations&security="+wc_pos_params.search_products_and_variations+"&term="+code, 
                function( data ) {
                    if ( $.isEmptyObject(data) === false){
                        var id_product;
                        for (var i in data) {
                            if (data.hasOwnProperty(i) && typeof(i) !== 'function') {
                                id_product = i;
                                break;
                            }
                        }
                        if (id_product) {
                            var new_item = $('#wc-pos-register-data #order_items_list .product_id_' + id_product);

                                if (new_item.length > 0) {
                                    var qt = parseInt(new_item.find('input.quantity').val())+1;
                                    var item_order_id = new_item.attr('data-order_item_id');

                                    var data = {
                                        action: 'wc_pos_update_product_quantity',
                                        new_quantity: qt,
                                        item_order_id: item_order_id,
                                        order_id: $('#order_id').val(),
                                        security: wc_pos_params.add_product_to_register,
                                        register_id: $('#id_register').val(),
                                    };
                                    $.post(wc_pos_params.ajax_url, data, function(response) {
                                        new_item.find('input.quantity').val(qt);
                                        calculateDiscount();
                                        calcRegisterTotal();
                                        $('#post-body').unblock();
                                    });
                                }else{
                                    var data = {
                                        action: 'wc_pos_add_products_to_register',
                                        item_to_add: id_product,
                                        order_id: $('#order_id').val(),
                                        security: wc_pos_params.add_product_to_register,
                                        register_id: $('#id_register').val(),
                                    };
                                    $.post(wc_pos_params.ajax_url, data, function(response) {
                                        $('#wc-pos-register-data #order_items_list').append(response);
                                        $('#wc-pos-register-data #order_items_list tr.new_row').hide();
                                        var save_data = $('#edit_wc_pos_registers').serialize();
                                        $.post(document.location.href, save_data, function(response) {
                                            var container = $('<div/>').append(response);
                                            var error      = $(container).find('#message.error');
                                            if(error){
                                                    $('#message').remove();
                                                    $('#ajax-response').before(error);
                                                }
                                            var table_cont = $(container).find('#wc-pos-register-data #order_items_list').html();
                                            $('#wc-pos-register-data #order_items_list').html(table_cont);

                                            $('#wc-pos-register-data  #order_items_list tr').addClass('new_row');
                                            runTipTip();
                                            reloadKeypad();
                                            calculateDiscount();
                                            calcRegisterTotal();
                                            $('#wc-pos-register-data .new_row').removeClass('new_row');
                                            $('#post-body').unblock();
                                        });
                                    });
                                }
                        }
                    }else{
                        $('#post-body').unblock();
                    }
                });
        });
    }
    $('body').on('click', '#var_tip_over', function() {
        $('#var_tip_over').remove();
        $('#var_tip').remove();
        $('td.hover').removeClass('hover');
        return false;
    });
    $('body').on('click', '#add_pr_variantion', function() {
        var id = $('#var_tip').attr('data-id');
        var tagArray = new Array();
        var exit = false;
        $('#var_tip_content select').each(function(index, el){
            if($(this).val() == '') {
                exit = true;
                return false;
            }
            var attr = $(this).attr('data-taxonomy');
            var val  = $(this).val();
            
            tagArray[index] = {"name": attr, "option": val};
        });
        if(!exit){
            var data = {
                    action: 'wc_pos_find_variantion_by_attributes',
                    attributes: tagArray,
                    parent : id,
                    order_id: $('#order_id').val(),
                    security: wc_pos_params.search_products_and_variations,
                    register_id: $('#id_register').val(),
                };
                $('#var_tip_over').remove();
                $('#var_tip').remove();
                $('td.hover').removeClass('hover');
                $('#post-body').block({message: null, overlayCSS: {background: '#fff url(' + wc_pos_params.ajax_loader_url+') no-repeat center', backgroundSize: '16px 16px', opacity: 0.6}});
                $.post(wc_pos_params.ajax_url, data, function(response) {

                    if(typeof response.id != 'undefined'){
                        addProduct(response.id, 'no') ;
                    }else{
                        pub_msg.html('<p>'+wc_pos_params.cannot_be_purchased+'</p>').show();
                        return;
                    }
                });
        }        
        return false;
    });
    function addProduct(pid, element) {
        var new_item = jQuery('#wc-pos-register-data #order_items_list .product_id_' + pid);

        if(element != 'no' && element.find('.hidden').length > 0 ) {
            runVariantion(element);
            return;
        }
        if (pid) {
            $('#post-body').block({message: null, overlayCSS: {background: '#fff url(' + wc_pos_params.ajax_loader_url+') no-repeat center', backgroundSize: '16px 16px', opacity: 0.6}});
            if (new_item.length > 0) {
                var qt = parseInt(new_item.find('input.quantity').val())+1;
                var item_order_id = new_item.attr('data-order_item_id');

                var data = {
                    action: 'wc_pos_update_product_quantity',
                    new_quantity: qt,
                    item_order_id: item_order_id,
                    order_id: $('#order_id').val(),
                    security: wc_pos_params.add_product_to_register,
                    register_id: $('#id_register').val(),
                };
                $.post(wc_pos_params.ajax_url, data, function(response) {
                    new_item.find('input.quantity').val(qt);
                    calculateDiscount();
                    calcRegisterTotal();
                    $('#post-body').unblock();
                });
            }else{
                var data = {
                    action: 'wc_pos_add_products_to_register',
                    item_to_add: pid,
                    order_id: $('#order_id').val(),
                    security: wc_pos_params.add_product_to_register,
                    register_id: $('#id_register').val(),
                };
                $.post(wc_pos_params.ajax_url, data, function(response) {
                    $('#wc-pos-register-data #order_items_list').append(response);
                    $('#wc-pos-register-data #order_items_list tr.new_row').hide();
                    var save_data = $('#edit_wc_pos_registers').serialize();
                    $.post(document.location.href, save_data, function(response) {
                        var container = $('<div/>').append(response);
                        var error      = $(container).find('#message.error');
                       if(error){
                            $('#message').remove();
                            $('#ajax-response').before(error);
                        }
                        var table_cont = $(container).find('#wc-pos-register-data #order_items_list').html();
                        $('#wc-pos-register-data #order_items_list').html(table_cont);

                        $('#wc-pos-register-data  #order_items_list tr').addClass('new_row');
                        runTipTip();
                        reloadKeypad();
                        calculateDiscount();
                        calcRegisterTotal();
                        $('#wc-pos-register-data .new_row').removeClass('new_row');
                        $('#post-body').unblock();
                    });
                });
            }
        }
    }
} /************************************************************************************************/
    /* change the state according country */

    $(document).on('change', '#billing_country,#shipping_country', function() {

        var country = $(this).val();
        var id = $(this).attr('id').replace('_countries', '');

        var data = {
            action: 'wc_pos_loading_states',
            country: country,
            id: id
        };
        xhr = $.ajax({
            type: 'POST',
            url: wc_pos_params.ajax_url,
            data: data,
            beforeSend: function(xhr) {
                $('#order_customer_popup').block({message: null, overlayCSS: {background: '#fff url(' + wc_pos_params.ajax_loader_url + ') no-repeat center', backgroundSize: '16px 16px', opacity: 0.6}});
            },
            complete: function(xhr) {
                if($(id == 'billing_country' && 'select#billing_state').length > 0){
                    $('select#billing_state').chosen();
                }                

                if($(id == 'shipping_country' && 'select#shipping_state').length > 0){
                    $('select#shipping_state').chosen(); 
                }                

                $('#order_customer_popup').unblock()
            },
            success: function(response) {
                var j_data = JSON.parse(response);
                var html = $($.parseHTML($.trim(j_data.state_html)));
                if(id == 'billing_country'){
                    $('#billing_state').remove();
                    if($('#billing_state_chosen').length > 0){
                        $('#billing_state_chosen').remove();
                    }
                    $('label[for="billing_state"]').after($(html));
                    $('label[for="billing_state"]').html(j_data.state_label + ' <span class="required">*</span>');
                    $('label[for="billing_postcode"]').html(j_data.zip_label + ' <span class="required">*</span>');
                    $('label[for="billing_city"]').html(j_data.city_label + ' <span class="required">*</span>');
                }
                if(id == 'shipping_country'){
                    $('#shipping_state').remove();
                    if($('#shipping_state_chosen').length > 0){
                        $('#shipping_state_chosen').remove();
                    }
                    $('label[for="shipping_state"]').after($(html));
                    $('label[for="shipping_state"]').html(j_data.state_label + ' <span class="required">*</span>');
                    $('label[for="shipping_postcode"]').html(j_data.zip_label + ' <span class="required">*</span>');
                    $('label[for="shipping_city"]').html(j_data.city_label + ' <span class="required">*</span>');
                }
            }
        });
    });


    /* For saving Customer Data */
    $('#save_customer').on('click', function() {
        $('#error_in_customer p').html('');
        $('#error_in_customer').hide();
        $('.form-invalid').removeClass('form-invalid');
        var billing_firstname = $('#billing_first_name').val();
        var shipping_firstname = $('#shipping_first_name').val();
        var billing_lastname = $('#billing_last_name').val();
        var shipping_lastname = $('#shipping_last_name').val();
        var billing_address = $('#billing_address_1').val();
        var shipping_address = $('#shipping_address_1').val();
        var billing_city = $('#billing_city').val();
        var shipping_city = $('#shipping_city').val();
        var billing_postcode = $('#billing_postcode').val();
        var shipping_postcode = $('#shipping_postcode').val();
        var billing_state = $('#billing_state').val();
        var shipping_state = $('#shipping_state').val();
        var email = $('#billing_email').val();
        var phone = $('#billing_phone').val();
        var err = 0;
        if (billing_firstname == '') {
            $('#billing_first_name').addClass('form-invalid');
            err++;
        }
        
        if (billing_lastname == '') {
            $('#shipping_last_name').addClass('form-invalid');
            err++;
        }
        
        if (billing_address == '') {
            $('#billing_address_1').addClass('form-invalid');
            err++;
        }
        
        if (billing_city == '') {
            $('#billing_city').addClass('form-invalid');
            err++;
        }
        
        if (billing_postcode == '') {
            $('#billing_postcode').addClass('form-invalid');
            err++;
        }
        
        if (billing_state == '') {
            $('#billing_state').addClass('form-invalid');
            err++;
        }
        
        if (email == '') {
            $('#billing_email').addClass('form-invalid');
            err++;
        } else if (!checkEmail(email)) {
            $('#billing_email').addClass('form-invalid');
            err++;
        }
        if (phone == '') {
            $('#billing_phone').addClass('form-invalid');
            err++;
        } else if (!checkPhone(phone)) {
            $('#billing_phone').addClass('form-invalid');
            err++;
        }
        if($('#ship-to-different-address-checkbox').is('checked')){
            if (shipping_firstname == '') {
                $('#shipping_first_name').addClass('form-invalid');
                err++;
            }
            if (shipping_lastname == '') {
                $('#shipping_lastname').addClass('form-invalid');
                err++;
            }
            if (shipping_address == '') {
                $('#shipping_address_1').addClass('form-invalid');
                err++;
            }
            if (shipping_city == '') {
                $('#shipping_city').addClass('form-invalid');
                err++;
            }
            if (shipping_postcode == '') {
                $('#shipping_postcode').addClass('form-invalid');
                err++;
            }
            if (shipping_state == '') {
                $('#shipping_state').addClass('form-invalid');
                err++;
            }
        }

        if (err) {
            window.scrollTo(0, parseInt($('.form-invalid').first().offset().top) - 100);
            return false;
        }
        if($('#createaccount').is(':checked')){
            var data = {
                action: 'wc_pos_add_customer',
                form_data: $('#add_wc_pos_customer').serialize()
            };


            xhr = $.ajax({
                type: 'POST',
                url: wc_pos_params.ajax_url,
                data: data,
                beforeSend: function(xhr) {
                    $('#order_customer_popup').block({message: null, overlayCSS: {background: '#fff url(' + wc_pos_params.ajax_loader_url + ') no-repeat center', backgroundSize: '16px 16px', opacity: 0.6}});
                },
                complete: function(xhr) {
                    $('#order_customer_popup').unblock();
                },
                success: function(response) {
                    var j_data = JSON.parse(response);
                    if (j_data.success == false) {
                        $('#error_in_customer p').html(j_data.message);
                        $('#error_in_customer').show();
                        $('#customer_details').scrollTop(0);
                    } else {
                        $('#overlay_order_customer').hide('slow');
                        var customer = '<tr class="item" data-customer_id="' + j_data.id + '"><td class="name"><a target="_blank" href="user-edit.php?user_id=' + j_data.id + '">' + j_data.name + '</a> <input type="hidden" value="' + j_data.id + '" name="user_id"></td><td class="remove_customer"><a data-tip="Remove" class="remove_customer_row tips" href="#"></a></td></tr>';
                        $('#wc-pos-customer-data #customer_items_list').html(customer);
                        $('#add_wc_pos_customer')[0].reset();
                    }
                    
                }
            });
        }else{
            $('#overlay_order_customer').hide('slow');
            var name = $('#billing_first_name').val() + ' ' + $('#billing_last_name').val();
            var customer = '<tr class="item" data-customer_id="0"> <td class="name">'+name+' (Guest)</td><td class="remove_customer"><a data-tip="Remove" class="remove_customer_row tips" href="#"></a> <input type="hidden" name="customer_details" value="'+$('#add_wc_pos_customer').serialize()+'" /></td> </tr>';
            $('#wc-pos-customer-data #customer_items_list').html(customer);
        }
        

    });


    $('.close_popup, .back_to_sale').on('click', function() {
        $('.overlay_order_popup').hide();
        $('#order_payment_popup input.select_payment_method').attr('disabled', 'disabled');
        if($('#add_wc_pos_customer').length > 0)
            $('#add_wc_pos_customer')[0].reset();
        $('#error_in_customer').html('');
        $('#retrieve_sales_popup_content').html('');
    });
    if($('#sale_report_popup').length > 0){
        $('#sale_report_popup .close_popup').click(function(){
            history.pushState('', '', 'admin.php?page=wc_pos_registers');
        });
    }


    /* for calculate change amount*/
    $('#amount_pay_cod').on('change', function() {
        var total_amount = parseFloat($("#show_total_amt_inp").val());

        var amount_pay = $(this).val();
        var change = amount_pay - total_amount;
        var change = change.toFixed(2);
        if (amount_pay != '') {
            $('#amount_change_cod').val(0);
        }
        if (amount_pay > total_amount) {
            $('#amount_change_cod').val(change);
        } else {
            $('#amount_change_cod').val(0);
        }
    });

    if($('.previous-next-toggles').length > 0 ){
        if($('#grid_layout_cycle > div').length <= 1 ){
            $('.previous-next-toggles').hide();
        }
        $('#grid_layout_cycle').cycle({
            speed:  'fast',
            timeout : 0,
            pager   : '.previous-next-toggles #nav_layout_cycle',
            next    : '.previous-next-toggles .next-grid-layout',
            prev    : '.previous-next-toggles .previous-grid-layout',
            before  : function(currSlideElement, nextSlideElement, options, forwardFlag) {
                var table = $(nextSlideElement).find('table');
                if(typeof table.data('title') != undefined ){
                    var title = table.data('title');
                    $('#wc-pos-register-grids-title').html(title);
                }
            }
        });
    }

    $('#order_payment_popup').on('click', 'input.go_payment', function() {
        var selected_payment_method = $('.select_payment_method:checked').attr('id');
        $('#error_payment').text('');
        if(selected_payment_method == '' || selected_payment_method == undefined){
            $('#error_payment').text('Please select Payment method.');
            return false;
        }        err = '';
        $('#message').remove();
        if ($('#order_items_list tr').length == 0) {
            err += '<p>Please add products</p>';
        }
        if ($('#customer_items_list tr').length == 0) {
            err += '<p>Please add customer</p>';
        }

        if (err != '') {
            errors = '<div class="error" id="message">' + err + '</div>';
            $('#ajax-response').after(errors);
            return false;
        }
        /* check the amount pay and total paybale amount*/
        var total_amount = parseFloat($("#show_total_amt_inp").val());
        
        var amount_pay = $('#amount_pay_cod').val();
        if (amount_pay < total_amount && selected_payment_method == 'payment_method_cod') {
            $('.error_amount').html('Please enter correct amount.');
            return false;
        }else if (selected_payment_method == 'payment_method_stripe') {
            if ( jQuery( 'input.stripe_token' ).size() == 0 ) {

                    var card    = jQuery('#stripe-card-number').val();
                    var cvc     = jQuery('#stripe-card-cvc').val();
                    var expires = jQuery('#stripe-card-expiry').payment( 'cardExpiryVal' );
                    var $form   = jQuery("#order_payment_popup");

                    $form.block({message: null, overlayCSS: {background: '#fff url(' + wc_pos_params.ajax_loader_url + ') no-repeat center', backgroundSize: '16px 16px', opacity: 0.6}});

                    var data = {
                        number:    card,
                        cvc:       cvc,
                        exp_month: parseInt( expires['month'] ) || 0,
                        exp_year:  parseInt( expires['year'] ) || 0
                    };

                    if($('#customer_items_list input[name="user_id"]').length > 0 && $('#customer_items_list input[name="user_id"]').val() != ''){
                        var request = {
                            action : 'wc_pos_stripe_get_user',
                            user_id: $('#customer_items_list input[name="user_id"]').val()
                        };
                        $.post(wc_pos_params.ajax_url, request, function(response) {
                            var d = JSON.parse(response);
                            data.name = d.first_name + ' ' + d.last_name;
                            data.address_line1   = d.billing_address_1;
                            data.address_line2   = d.billing_address_2;
                            data.address_state   = d.billing_state;
                            data.address_city    = d.billing_city;
                            data.address_zip     = d.billing_postcode;
                            data.address_country = d.billing_country;
                        });                        
                    }else {
                        if($('#billing_first_name').val() != '' && $('#billing_last_name').val() != ''){
                            data.name = $('#billing_first_name').val() + ' ' + $('#billing_last_name').val();
                            data.address_line1   = $('#billing_address_1').val();
                            data.address_line2   = $('#billing_address_2').val();
                            data.address_state   = $('#billing_state').val();
                            data.address_city    = $('#billing_city').val();
                            data.address_zip     = $('#billing_postcode').val();
                            data.address_country = $('#billing_country').val();
                        }else{
                            var request = {
                                action : 'wc_pos_stripe_get_outlet_address',
                                outlet_id: $('#outlet_ID').val()
                            };
                            $.post(wc_pos_params.ajax_url, request, function(response) {
                                var d = JSON.parse(response);
                                var contact = d.contact;
                                
                                data.name = 'Outlet "' + d.name + '"';
                                data.address_line1   = contact.address_1;
                                data.address_line2   = contact.address_2;
                                data.address_state   = contact.state;
                                data.address_city    = contact.city;
                                data.address_zip     = contact.postcode;
                                data.address_country = contact.country;
                            });    
                        }
                    }
                    Stripe.createToken( data, stripeResponseHandler_ );

                    // Prevent form submitting
                    return false;
                }
        } else {
            $('.error_amount').html('');
        }
        $('#edit_wc_pos_registers').submit();
    });
    $('#order_payment_popup').on('click', 'button.payment_methods', function() {
        $('.payment_methods').removeClass('button-primary');
        $(this).addClass('button-primary');
        var selected_payment_method = $(this).attr('data-bind');
        $('#payment_method_' + selected_payment_method).attr('checked', 'checked');

        $('.payment_box').hide();
        $('.payment_method_'+selected_payment_method).show();

        return false;
    })



    


    if( $('#receipt_options').length > 0){
        $('#receipt_print_outlet_contact_details').change(function() {
            if($(this).is(':checked')){
                $('.show_receipt_print_outlet_contact_details').show();
            }else{
                $('.show_receipt_print_outlet_contact_details').hide();
            }
        }).trigger('change');

        $('#receipt_print_order_time').change(function() {
            if($(this).is(':checked')){
                $('#print_order_time').show();
            }else{
                $('#print_order_time').hide();
            }
        }).trigger('change');

        $('#receipt_print_server').change(function() {
            if($(this).is(':checked')){
                $('#print_server').show();
            }else{
                $('#print_server').hide();
            }
        }).trigger('change');

        $('#receipt_print_number_items').change(function() {
            if($(this).is(':checked')){
                $('#print_number_items').show();
            }else{
                $('#print_number_items').hide();
            }
        }).trigger('change');

        $('#receipt_print_barcode').change(function() {
            if($(this).is(':checked')){
                $('#print_barcode').show();
            }else{
                $('#print_barcode').hide();
            }
        }).trigger('change');

        $('#receipt_print_tax_number').change(function() {
            if($(this).is(':checked')){
                $('#print_tax_number').show();
            }else{
                $('#print_tax_number').hide();
            }
        }).trigger('change');

        /********/

        $('#receipt_telephone_label').on('change', function() {
            var val = $(this).val();
            $('#print-telephone_label').html(val);
            if(val == '')
                $('#print-telephone_label').next('.colon').hide();
            else
                $('#print-telephone_label').next('.colon').show();
        }).trigger('change');

        $('#receipt_fax_label').on('change', function() {
            var val = $(this).val();
            $('#print-fax_label').html(val);
            if(val == '')
                $('#print-fax_label').next('.colon').hide();
            else
                $('#print-fax_label').next('.colon').show();
        }).trigger('change');

        $('#receipt_email_label').on('change', function() {
            var val = $(this).val();
            $('#print-email_label').html(val);
            if(val == '')
                $('#print-email_label').next('.colon').hide();
            else
                $('#print-email_label').next('.colon').show();
        }).trigger('change');

        $('#receipt_website_label').on('change', function() {
            var val = $(this).val();
            $('#print-website_label').html(val);
            if(val == '')
                $('#print-website_label').next('.colon').hide();
            else
                $('#print-website_label').next('.colon').show();
        }).trigger('change');

        $('#receipt_receipt_title').on('change', function() {
            var val = $(this).val();
            $('#print-receipt_title').html(val);
            if(val == '')
                $('#print-receipt_title').parents('h4').hide();
            else
                $('#print-receipt_title').parents('h4').show();
        }).trigger('change');

        $('#receipt_order_number_label').on('change', function() {
            var val = $(this).val();
            $('#print-order_number_label').html(val);
            if(val == '')
                $('#print-order_number_label').next('.colon').hide();
            else
                $('#print-order_number_label').next('.colon').show();
        }).trigger('change');

        $('#receipt_order_date_label').on('change', function() {
            var val = $(this).val();
            $('#print-order_date_label').html(val);
            if(val == '')
                $('#print-order_date_label').next('.colon').hide();
            else
                $('#print-order_date_label').next('.colon').show();
        }).trigger('change');

        $('#receipt_served_by_label').on('change', function() {
            var val = $(this).val();
            $('#print-served_by_label').html(val);
            if(val == '')
                $('#print-served_by_label').next('.colon').hide();
            else
                $('#print-served_by_label').next('.colon').show();
        }).trigger('change');

        $('#receipt_tax_label').on('change', function() {
            var val = $(this).val();
            if(val == '')
                $('#print-tax_label').html(val);
            else
                $('#print-tax_label').html('('+val+')');
        }).trigger('change');

        $('#receipt_total_label').on('change', function() {
            var val = $(this).val();
            $('#print-total_label').html(val);
        }).trigger('change');

        $('#receipt_payment_label').on('change', function() {
            var val = $(this).val();
            $('#print-payment_label').html(val);
        }).trigger('change');

        $('#receipt_items_label').on('change', function() {
            var val = $(this).val();
            $('#print-items_label').html(val);
        }).trigger('change');

        $('#receipt_tax_number_label').on('change', function() {
            var val = $(this).val();
            $('#print-tax_number_label').html(val);
            if(val == '')
                $('#print-tax_number_label').next('.colon').hide();
            else
                $('#print-tax_number_label').next('.colon').show();
        }).trigger('change');

        /********/

        $('#receipt_header_text').on('change', function() {
            $('#print-header_text').html($(this).val());
        }).trigger('change');

        $('#receipt_footer_text').on('change', function() {
            $('#print-footer_text').html($(this).val());
        }).trigger('change');

        setTimeout(function(){
            $("#receipt_header_text_ifr").contents().find('body').keyup(function(){
                $('#print-header_text').html($(this).html());
            });
        }, 2000);

        setTimeout(function(){
            $("#receipt_footer_text_ifr").contents().find('body').keyup(function(){
                $('#print-footer_text').html($(this).html());
            });
        }, 2000);

        // Uploading files
        var file_frame;
        var current_shape_image;
        $('.set_receipt_logo').click(function(){

            // If the media frame already exists, reopen it.
            if (file_frame) {
                file_frame.open();
                return;
            }

            // Create the media frame.
            file_frame = wp.media.frames.file_frame = wp.media({
                title: "Select a Receipt Logo ",
                button: {
                    text: "Set Receipt Logo",
                },
                multiple: false,
            });

            // When an image is selected, run a callback.
            file_frame.on( 'select', function() {
                // We set multiple to false so only get one image from the uploader
                attachment = file_frame.state().get('selection').first().toJSON();
                console.log(file_frame);

                // Set the image id/display the image thumbnail
                $('#receipt_logo').val(attachment.id);
                $('#print_receipt_logo').attr('src', attachment.url);
                $('#set_receipt_logo_img img').attr('src', attachment.sizes.thumbnail.url);

                $('#set_receipt_logo_img, .remove_receipt_logo, #print_receipt_logo').show();
                $('#set_receipt_logo_text').hide();

            });

            // Finally, open the modal
            file_frame.open();
            return false;
        });

        $('.remove_receipt_logo').click(function(){

            $('#set_receipt_logo_img, .remove_receipt_logo, #print_receipt_logo').hide();
            $('#set_receipt_logo_text').show();

            $('#receipt_logo').val('');
            $('#print_receipt_logo').attr('src', '');
            $('#set_receipt_logo_img img').attr('src', '');
            return false;
        });


    }

    if($('#print_order_id').length > 0 && $('#print_order_id').val() != ''){
        var data = {
            action: 'wc_pos_printing_receipt',
            security  : wc_pos_params.printing_receipt_nonce,
            order_id  : $('#print_order_id').val(),
            receipt_ID: $('#print_receipt_ID').val(),
            outlet_ID : $('#outlet_ID').val(),
            register_ID : $('#id_register').val(),
        };
        var start_print    = false;
        var print_document ='';
        $.ajax({
            type: 'POST',
            async: false,
            url: wc_pos_params.ajax_url,
            data: data,
            success: function(response) {
                if (response) {
                    start_print    = true;
                    print_document = response;
                }
            }
        });
        if(start_print){
            var newHTML = $('<div id="printable">'+print_document+'</div>');
            delete_cookie('wc_point_of_sale_printing');
            $('body').append(newHTML);
            window.print();
            $('#printing_receipt').hide();
        }
    }
    var product_data = {};
    if($('.tile_style').length > 0){
        $('.tile_style').change(function(){
            var val = $('.tile_style:checked').val();
            if(val == 'colour'){
                $('.tile_style_bg_row').show();
            }else{
                $('.tile_style_bg_row').hide();
            }
            check_preview();
        }).trigger('change');

        

        
        $("#product_id").change(function(){
            var selected_produst = $(this).val();
            
            if(product_data[selected_produst] && product_data[selected_produst].image){
                var tiles_img =  product_data[selected_produst].image; 
                $('#custom-background-image1').data('shop_thumbnail', tiles_img);
            }else{
                $('#custom-background-image1').data('shop_thumbnail', '');
            }
            check_preview(); 

            if(selected_produst != ''){
                $('#serach_tile_product, #wc-pos-outlets-edit').block({message: null, overlayCSS: {background: '#fff url(' + wc_pos_params.ajax_loader_url + ') no-repeat center', backgroundSize: '16px 16px', opacity: 0.6}});
                var data = {
                    action: 'wc_pos_search_variations_for_product',
                    id_product: selected_produst,
                    security: wc_pos_params.search_variations_for_product,
                };
                $.post(wc_pos_params.ajax_url, data, function(response) {
                    option = '<option value="0" selected>'+wc_pos_params.no_default_selection+'</option>';
                    response = response.trim();
                    if(response != ''){
                        var obj = $.parseJSON( response );
                        $.each(obj, function (i, val) {
                            option += '<option value="'+i+'" data-img = "'+val.image+'">'+val.formatted_name+'</option>';
                        });
                        $('.dafault_selection').show();
                    }else{
                        $('.dafault_selection').hide();
                    }
                  $('#dafault_selection').html(option);
                  $('#serach_tile_product, #wc-pos-outlets-edit').unblock();
                });
            }
        });

        

        $('#dafault_selection').change(function(){
            var val = $(this).val();
            if(val != ''){
                tiles_img = $(this).find('option[value="'+val+'"]').attr('data-img');
                $('#custom-background-image1').data('shop_thumbnail', tiles_img);    
            }else{
                var selected_produst = $("#product_id").val();
                if(product_data[selected_produst] && product_data[selected_produst].image){
                    var tiles_img =  product_data[selected_produst].image; 
                    $('#custom-background-image1').data('shop_thumbnail', tiles_img);
                }else{
                    $('#custom-background-image1').data('shop_thumbnail', '');
                }
            }            
            check_preview();
        }).trigger('change');
        
        

        // Ajax Chosen Product Selectors
        jQuery("select.ajax_chosen_select_products").ajaxChosen({
            method:     'GET',
            url:        wc_pos_params.ajax_url,
            dataType:   'json',
            afterTypeDelay: 100,
            data:       {
                action:         'wc_pos_json_search_products',
                security:       wc_pos_params.search_products_and_variations
            }
        }, function (data) {
            product_data = {};
            product_data = data;
            var terms = {};

            $.each(data, function (i, val) {
                terms[i] = val.formatted_name;
            });

            return terms;
        });
        check_preview(); 
    }

    function check_preview(){
        if($('#tile_style_image').is(':checked')){
            var image = $('#custom-background-image1').data('shop_thumbnail');
            $('#custom-background-image1').removeAttr('style').css({
                'background'       : 'url("'+image+'") center no-repeat',
                'background-size'  : 'contain',
                'background-color' : '#ffffff'
            });
            $('#custom-background-tiles-color').hide();
        }else{
            $('#custom-background-tiles-color').show();
            var selected_produst = $("#product_id").val();
            var tiles_text = '';
            var background_color = $('#background_color').val();
            var text_color = $('#text-color').val();

            if(product_data[selected_produst] && product_data[selected_produst].name){
                tiles_text =  product_data[selected_produst].name; 
            }
            else{
                tiles_text = $("#product_id_chosen").find('span').text();
                var arr = tiles_text.split(' – ');
                if(arr[1]) tiles_text = arr[1];
            }

            if($('#product_id').val() != '')
                $("#custom-background-tiles-color").text(tiles_text);

            $("#custom-background-tiles-color").removeAttr('style').css({
                'color' : text_color
            });
            
            $('#custom-background-image1').removeAttr('style').css({
                'background-color' : background_color
            });
        }
    }

    if($('#product_grid-add-toggle').length > 0){
        $('#product_grid-add-toggle').click(function(){
            $(this).closest('#product_grid-adder').toggleClass('wp-hidden-children');
            return false;
        });
        $('#product_grid-add-submit').click(function(){
            add_product_grid();
            return false;
        });
        $('#newproduct_grid').keydown(function(e){
          var code = e.keyCode || e.which;
           if(code == 13) { //Enter keycode
            add_product_grid();
            return false;
           }
        });
    }

    function add_product_grid(){
      var val = $('#newproduct_grid').val();
      var term = val.trim();
      if( term == '') return;
      var data = {
          action      : 'wc_pos_add_product_grid',
          security    : wc_pos_params.add_product_grid,
          term        : term,
      };
      $('#product_grid-add-submit').attr('disabled', 'disabled');
      $.ajax({
          type: 'POST',
          async: false,
          url: wc_pos_params.ajax_url,
          data: data,
          success: function(response) {
            var id = parseInt(response);
              if (id > 0) {
                  $('div.gridcategorydiv ul').prepend('<li id="product_grid-'+id+'"><label class="selectit"><input type="checkbox" checked="checked" id="in-product_grid-'+id+'" name="pos_input[product_grid][]" value="'+id+'"> '+term+'</label></li>');
              }
              $('#product_grid-add-submit').removeAttr('disabled');
              $('#newproduct_grid').val('');
          },
          error: function(){
            $('#product_grid-add-submit').removeAttr('disabled');
          }
      });
    }
        
});

function checkEmail(e)
{
    ok = "1234567890qwertyuiop[]asdfghjklzxcvbnm.@-_QWERTYUIOPASDFGHJKLZXCVBNM";

    for (i = 0; i < e.length; i++)
        if (ok.indexOf(e.charAt(i)) < 0)
            return (false);

    if (document.images)
    {
        re = /(@.*@)|(\.\.)|(^\.)|(^@)|(@$)|(\.$)|(@\.)/;
        re_two = /^.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
        if (!e.match(re) && e.match(re_two))
            return true;
        else
            return false;

    }
    return true;

}


function checkPhone(e)
{
    var number_count = 0;
    for (i = 0; i < e.length; i++)
        if ((e.charAt(i) >= '0') && (e.charAt(i) <= 9))
            number_count++;

    if (number_count == 11 || number_count <= 12)
        return true;

    return false;
}
function delete_cookie (name) {
    document.cookie = name + '=;Path=/;expires=Thu, 01 Jan 1970 00:00:01 GMT;';
}

function stripeResponseHandler_( status, response ) {
    console.log(response);
    var $form = jQuery("#order_payment_popup");

    if ( response.error ) {

        // show the errors on the form
        jQuery('.woocommerce_error, .woocommerce-error, .woocommerce-message, .woocommerce_message, .stripe_token').remove();
        jQuery('#stripe-card-number').closest('p').before( '<ul class="woocommerce_error woocommerce-error"><li>' + response.error.message + '</li></ul>' );
        $form.unblock();

    } else {

        // token contains id, last4, and card type
        var token = response['id'];

        // insert the token into the form so it gets submitted to the server
        $form.append("<input type='hidden' class='stripe_token' name='stripe_token' value='" + token + "'/>");
        jQuery('#edit_wc_pos_registers').submit();
    }
}

function openLocalDb(){
    addEventListeners_ajax();
}
function isTouchDevice() {
   var el = document.createElement('div');
   el.setAttribute('ontouchstart', 'return;');

   //console.log(el.ontouchstart);

   if(typeof el.ontouchstart == "function"){
      return true;
   }else {
      return false;
   }
}