/**
 * @version 2.0.14
 * @package Perfect Easy & Powerful Contact Form
 * @copyright © 2015 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
 * @license GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @author Piotr Moćko
 */

// TODO fix hiding child options

var pwebcontact_l10n = pwebcontact_l10n || {},
    pwebcontact_admin = pwebcontact_admin || {};

if (typeof jQuery !== "undefined") jQuery(document).ready(function($){
	
    if (typeof pwebcontact_admin.is_pro === "undefined") {
        pwebcontact_admin.is_pro = false;
    }
    
    pwebcontact_admin.confirm = true;
    pwebcontact_admin.confirmed = false;
    pwebcontact_admin.running_related = false;
    pwebcontact_admin.duration = 0;
    pwebcontact_admin.isLocalhost = (document.location.host === "localhost" || document.location.host === "127.0.0.1");
    pwebcontact_admin.domain = document.location.host.replace("www.", "");
    
    var $tabs = $("#pweb-tabs-content"),
        $adminBar = $("#pweb-adminbar");
    
    $(window).resize(function(){
        $tabs.css("padding-top", $(this).width() < 768 ? 0 : $adminBar.height());
    });
    
    // Initialize tooltips
    $(".pweb-has-tooltip").tooltip({
        track: true
    });
    
    // Tabs
    $("#pweb-tabs").find(".nav-tab").click(function(e){
        e.preventDefault();
        document.location.hash = $(this).attr("href");

        $("#pweb-tabs").find(".nav-tab-active").removeClass("nav-tab-active");
        $(this).addClass("nav-tab-active");

        $tabs.find(".nav-tab-content-active").removeClass("nav-tab-content-active");
        $($(this).attr("href")+"-content").addClass("nav-tab-content-active");
        
        $(window).trigger("resize");
    });
    
    $tabs.find(".pweb-next-tab-button").click(function(e){
        e.preventDefault();
        $( "#"+ $(this).closest(".nav-tab-content").attr("id").replace("-content", "") ).next().click();
    });
    
    // Location tabs
    $("#pweb-location-steps .pweb-location-step-tab").click(function(e){
        e.preventDefault();

        $("#pweb-location-steps .pweb-tab-active").removeClass("pweb-tab-active");
        $(this).addClass("pweb-tab-active");

        $("#pweb-location-options .pweb-location-options").removeClass("pweb-options-active");
        $("#"+$(this).attr("id")+"-options").addClass("pweb-options-active");

        var winWidth = $(window).width(),
            topOffset = $tabs.offset().top;
        
        if (winWidth > 600) {
            topOffset = topOffset - $("#wpadminbar").height();
            if (winWidth > 768) {
                topOffset = 0;
            }
        }
        
        if ($(window).scrollTop() > topOffset) {
            $("html,body").animate({ scrollTop: topOffset }, 500);
        }
    });
    
    // Show related options
    var $relatedFields = $tabs.find("fieldset.pweb-related input").not(".pweb-shortcode");
    $relatedFields.each(function(){
        $(this).data("relations", $(this).parent().attr("class").match(/pweb-related-[a-z\-]+/g) );
    }).change(function(e){
        
        if (pwebcontact_admin.running_related === true || !$(this).is(":checked")) return;
        
        pwebcontact_admin.running_related = true;
                
        var current_relations = $(this).data("relations"),
            relations = {
                name: [],
                strength: []
            };
        
        // find combinations of relations for checked options
        $relatedFields.filter(":checked").each(function(){
            var related = $.grep( $(this).data("relations"), function( n, i ){
                return $.inArray(n, current_relations) > -1;
            });
            
            if (related.length) {
                $.each(related, function( i, related_name ){
                    var index = $.inArray(related_name, relations.name);
                    if (index === -1) {
                        relations.name.push(related_name);
                        relations.strength.push(1);
                    } 
                    else {
                        relations.strength[index]++;
                    }
                });
            }
        });
        
        // find most relevant combination
        var strength = 0;
        $.each(relations.strength, function( i, s ){
            if (s > relations.strength[strength]) {
                strength = i;
            }
        });
        
        var selected = relations.name[strength];
        
        // change options which do not meet most relevan combination
        $relatedFields.filter(":checked").each(function(){
            if ($.inArray(selected, $(this).data("relations")) === -1) {
                $(this).closest("fieldset").find("."+selected+" input:not(:disabled)").first().click();
            }
        });
        
        // mark not related options
        $relatedFields.each(function(){
            var $option = $(this).parent();
            if ($option.hasClass(selected)) {
                // unmark related options
                $option.removeClass("pweb-not-related");
            }
            else {
                // mark not related options
                $option.addClass("pweb-not-related");
            }
        });
        
        pwebcontact_admin.running_related = false;
    }); 
    
    
    // display selected option in main settings of Location tab
    $("#pweb_params_handler input").change(function(e){
        if (this.checked) {
            var text = $("#"+$(this).attr("id")+"-lbl").text();
            $("#pweb-location-before .pweb-step-option").text(text);
        }
    });
    
    $("#pweb_params_effect input").change(function(e){
        if (this.checked) {
            $("#pweb-location-after .pweb-step-option").text( $("#"+$(this).attr("id")+"-lbl").text() );
        }
    }).filter(":checked").each(function(){
        $("#pweb-location-after .pweb-step-option").text( $("#"+$(this).attr("id")+"-lbl").text() );
    });
    
    $("#pweb_params_position input").change(function(e){
        if (this.checked) {
            $("#pweb-location-place .pweb-step-option").text( $("#"+$(this).attr("id")+"-lbl").text() );
        }
    }).filter(":checked").each(function(){
        $("#pweb-location-place .pweb-step-option").text( $("#"+$(this).attr("id")+"-lbl").text() );
    });
    
    
    
    
    function hideChildOptions(parent_id, current_id) {
        
        // Find child elements of given parent
        var $elements = $tabs.find( "."+ parent_id );
        if (typeof current_id !== "undefined") {
            // Exclude elements that will be shown by current option
            $elements = $elements.filter(":not(."+current_id+")");
        }
        $elements.each(function(){
            var show = false,
                // Get all parents IDs of child element
                ids = $(this).data("parents");
            for (var id in ids) {
                // Skip id if it has parent id from beginning of function
                if (parent_id !== id) { //TODO maybe not required statement
                    var $field = $("#"+id);
                    if ($field.length && $field.is(":checked") && $field.closest(".pweb-field").css("display") !== "none") {
                        // Do not hide field if parent is checked and visible
                        show = true;
                        break;
                    }
                }
            }
            if (show === false && $(this).css("display") !== "none") {
                // hide element if it is not already hidden
                $(this).hide(pwebcontact_admin.duration);
                // propagate hiding of options that were shown by this element
                hideChildOptions( $(this).find("input.pweb-parent:checked").attr("id") );
            }
        });
    }
    
    // store array of parents for each child
    $tabs.find(".pweb-child").each(function(){
        $(this).data("parents", $(this).attr("class").match(/pweb_params_[a-z_]+/g) );
    })
    // hide all childs on page load
    .filter(".pweb-field").hide();
    
    // Show options for checked parent
    $tabs.find("fieldset.pweb-parent input").change(function(e){
        var current_id = $(this).attr("id");
            $options = $(this).closest("fieldset").find("input.pweb-parent");
        
        // Hide child options of unchecked options
        $options.filter(":not(:checked)").each(function(){
            hideChildOptions( $(this).attr("id"), current_id );
        });
        
        // Show child options for checked option (current)
        $options.filter(":checked").each(function(){
            var $elements = $tabs.find( "."+ $(this).attr("id") );
            $elements.show(pwebcontact_admin.duration);
            // Propagate displaly of child options
            $elements.find("input.pweb-parent:checked").trigger("change");
        });
    });
    
    var $inputFields = $tabs.find(".pweb-field-text input.pweb-parent, .pweb-field-color input.pweb-parent").change(function(e){
        $tabs.find( "."+ $(this).attr("id") )[ $(this).val() ? "show" : "hide" ](pwebcontact_admin.duration);
    });
    
    
    // Init fields
    $relatedFields.filter(":checked").first().trigger("change");
	// Init parent options for fields not dependend on releated fields
    $tabs.find("fieldset.pweb-parent").find("input:first").trigger("change");
    $inputFields.trigger("change");
    
    
    
    // Advanced options toggler
    $(".pweb-advanced-options-toggler").click(function(e){
        e.preventDefault();
        
        var that = this,
            $box = $(this).parent();
        if ($box.hasClass("pweb-advanced-options-active")) {
            $box.removeClass("pweb-advanced-options-active");
            $(this).next().slideUp(400, function(){
                $(that).find("i.glyphicon-chevron-up").removeClass("glyphicon-chevron-up").addClass("glyphicon-chevron-down");
            });
        }
        else {
            $box.addClass("pweb-advanced-options-active");
            $(this).next().slideDown(400, function(){
                $(that).find("i.glyphicon-chevron-down").removeClass("glyphicon-chevron-down").addClass("glyphicon-chevron-up");
            });
        }
        
        $(this).blur();
    }).filter(".pweb-advanced-options-open").click();
    
    
    // Select current Administrator if Email to is empty
    if ($("#pweb_params_email_to").val() === "") {
        var $email_cms_user = $("#pweb_params_email_cms_user");
        if ($email_cms_user.get(0).options.length > 1) {
            $email_cms_user.val(userSettings.uid);
            if ($email_cms_user.get(0).selectedIndex < 1) {
                $email_cms_user.get(0).selectedIndex = 1;
            }
        }
    }
    
	
	// validate single email
	$(".pweb-filter-email").on("change", function() {
		if (this.value) {
			var regex=/^[a-zA-Z0-9.!#$%&‚Äô*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;
			if (regex.test(this.value)) {
				$(this).removeClass("pweb-invalid");
			} else {
				$(this).addClass("pweb-invalid");
			}
		}
	});
	
	// validate coma separated emails
	$(".pweb-filter-emails").on("change", function() {
		if (this.value) {
			var regex=/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*\.\w+(,[ ]*\w+([\.-]?\w+)*@\w+([\.-]?\w+)*\.\w+)*$/;
			if (regex.test(this.value)) {
				$(this).removeClass("pweb-invalid");
			} else {
				$(this).addClass("pweb-invalid");
			}
		}
	});
	
	// validate list of email recipients
	$(".pweb-filter-emailRecipients").on("change", function() {
		if (this.value) {
			var regex=/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*\.\w+[\|]{1}[^\r\n\|]+([\r]?\n\w+([\.-]?\w+)*@\w+([\.-]?\w+)*\.\w+[\|]{1}[^\r\n\|]+)*$/;
			if (regex.test(this.value)) {
				$(this).removeClass("pweb-invalid");
			} else {
				$(this).addClass("pweb-invalid");
			}
		}
	});
	
	// validate int
	$(".pweb-filter-int").on("change", function() {
		if (this.value && this.value !== "auto") {
			var value = parseInt(this.value);
			this.value = isNaN(value) ? "" : value;
		}
	});
	
	// validate float
	$(".pweb-filter-float").on("change", function() {
		if (this.value && this.value !== "auto") {
			var value = parseFloat(this.value);
			this.value = isNaN(value) ? "" : value;
		}
	});
	
	// validate unit
	$(".pweb-filter-unit").on("change", function() {
		var regex = /^\d+(px|em|ex|cm|mm|in|pt|pc|%){1}$/i;
		if (!this.value || this.value === "auto" || regex.test(this.value)) {
			$(this).removeClass("pweb-invalid");
		} else {
			var value = parseInt(this.value);
			if (!isNaN(value)) {
				this.value = value+"px";
				$(this).removeClass("pweb-invalid");
			} else {
				$(this).addClass("pweb-invalid");
			}
		}
	});
	
	// validate color
	$(".pweb-filter-color").on("change", function() {
		var regex = /^(\w|#[0-9a-f]{3}|#[0-9a-f]{6}|rgb\(\d{1,3},[ ]?\d{1,3},[ ]?\d{1,3}\)|rgba\(\d{1,3},[ ]?\d{1,3},[ ]?\d{1,3},[ ]?[0]?\.\d{1}\))$/i;
		if (!this.value || regex.test(this.value)) {
			$(this).removeClass("pweb-invalid");
		} else {
			$(this).addClass("pweb-invalid");
		}
	});
	
	// validate url
	$(".pweb-filter-url").on("change", function() {
		this.value = encodeURI(decodeURI(this.value));
        
        var regex = /^((http|https):){0,1}\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?$/i;
		if (!this.value || regex.test(this.value)) {
			$(this).removeClass("pweb-invalid");
		} else {
			$(this).addClass("pweb-invalid");
		}
	});
	
	// validate upload file size
	$(".pweb-filter-upload-max-size").on("change", function() {
		if (this.value) {
			var maxSize = pwebUploadMaxSize || 0,
				value = parseFloat(this.value);
			value = isNaN(value) ? 1 : value;
			if (value > maxSize) value = maxSize;
			this.value = value;
		}
	});
	
	// Validate upload files extensions
	$(".pweb-filter-ext").on("change", function(){
		this.value = this.value.toLowerCase().replace(/[^a-z0-9|?]+/g, "");
	});
    
    
    $("#pweb_params_email_from").change(function(e){
        $(this).removeClass("pweb-invalid");
        var email = $(this).val().toLowerCase();
        if (!pwebcontact_admin.isLocalhost && email.indexOf("@") !== -1 && email.indexOf(pwebcontact_admin.domain) === -1) {
            $(this).addClass("pweb-invalid");
        }
    }).trigger("change");
    
    $("#pweb_params_smtp_username").change(function(e){
        $(this).removeClass("pweb-invalid");
        if (!pwebcontact_admin.isLocalhost && $("#pweb_params_mailer input:checked").val() === "smtp") {
            // SMTP user from other domain than site
            var username = $(this).val().toLowerCase();
            if (username.indexOf("@") !== -1 && username.indexOf(pwebcontact_admin.domain) === -1) {
                $(this).addClass("pweb-invalid");
            }
        }
    }).trigger("change");
    
    $("#pweb_params_smtp_host").change(function(e){
        $(this).removeClass("pweb-invalid");
        if (!pwebcontact_admin.isLocalhost && $("#pweb_params_mailer input:checked").val() === "smtp") {
            // SMTP host from other domain than site
            var host = $(this).val().toLowerCase();
            if (host !== "localhost" && host.indexOf(pwebcontact_admin.domain) === -1) {
                $(this).addClass("pweb-invalid");
            }
        }
    }).trigger("change");
    
    // Set SMTP port depending on security encryption
    $("#pweb_params_smtp_secure input").change(function(e){
        var port = 25;
        switch ($(this).val()) {
            case "ssl":
                port = 465;
                break;
            case "tls":
                port = 587;
       }
       $("#pweb_params_smtp_port").val(port);
    });
    
    
    // AdWords paste button
	$("#pweb_params_adwords_url_btn").click(function(e){
		e.preventDefault();
		var s = prompt(pwebcontact_l10n.paste_adwords);
		if (s) {
			var u = s.match(/<img[^>]* src=["]([^"]+)"/i);
			if (u && typeof u[1] !== "undefined") {
                $("#pweb_params_adwords_url").val( u[1].replace(/&amp;/gi, "&") );
            }
		}
	});

    // AdCenter paste button
    $("#pweb_params_adcenter_url_btn").click(function(e){
		e.preventDefault();
		var s = prompt(pwebcontact_l10n.paste_adcenter);
		if (s) {
			var u = s.match(/<iframe[^>]* src=["]([^"]+)"/i);
			if (u && typeof u[1] !== "undefined") {
                $("#pweb_params_adcenter_url").val( u[1].replace(/&amp;/gi, "&") );
            }
		}
	});
    
    $("#pweb_params_bg_color").closest(".pweb-field-control").append( $("#pweb_params_bg_opacity") );
    
    
    $(".pweb-load-email-tmpl").change(function(e){
        
        if (this.selectedIndex) {
            
            var id = $(this).attr("id").replace("_list", "");
            
            // confirm loading of email tmpl
            if (!$("#"+id).val() || pwebcontact_admin.confirmed === true) {
                pwebcontact_admin.confirmed = false;
                
                var that = this,
                    data = {
                        "tmpl": $(this).val(),
                        "format": pwebcontact_admin.is_pro ? parseInt($("#"+id+"_format input:checked").val()) : 1
                    };
                
                $.ajax({
                    url: $(this).data("action"),
                    type: "POST", 
                    dataType: "text",
                    data: data,
                    beforeSend: function() {
                        $('<i class="glyphicon glyphicon-refresh"></i>').insertAfter(that);
                    }
                }).done(function(response, textStatus, jqXHR) {

                    // hide loading
                    $(that).val("").next("i.glyphicon-refresh").remove();

                    if (response) {
                        $("#"+id).val(response);
                    }
                    else {
                        alert(pwebcontact_l10n.missing_email_tmpl.replace("%s", data.tmpl + (data.format === 2 ? ".html" : ".txt")));
                    }
                }).fail(function(jqXHR, textStatus, errorThrown) {

                    alert(pwebcontact_l10n.request_error+". "+ jqXHR.status +" "+ errorThrown);
                });
            }
            else {
                // confirmation
                $("#pweb-dialog-email-load").data("element", $(this)).dialog("open");
            }
        }
    });
    
    $("#pweb-dialog-email-load").dialog({
        dialogClass: "wp-dialog",
        autoOpen: false,
        resizable: false,
        modal: true,
        buttons: [
            { 
                text: pwebcontact_l10n.ok,
                class : "button-primary",
                click: function(e) {
                    pwebcontact_admin.confirmed = true;
                    $(this).data("element").trigger("change");
                    $(this).dialog("close");
                }
            },
            {
                text: pwebcontact_l10n.cancel,
                class : "button",
                click: function() {
                    $(this).data("element").val("");
                    $(this).dialog("close");
                }
            }
        ]
    });
    
    
    $("#pweb_load_email_scheme").change(function(e){
        
        if (this.selectedIndex) {
            
            // confirm loading of email scheme
            if ((!$("#pweb_params_msg_success").val() && !$("#pweb_params_email_user_tmpl").val()) || pwebcontact_admin.confirmed === true) {
                pwebcontact_admin.confirmed = true;
                
                try {
                    var data = $.parseJSON( $(this).val() );
                } catch (e) {
                    var data = false;
                }
                
                if (data) {
                    $("#pweb_params_msg_success").val( data.msg );
                    $("#pweb_params_email_user_tmpl_list").val( data.tmpl ).trigger("change");
                }
                
                $(this).val("");
            }
            else {
                // confirmation
                $("#pweb-dialog-email-scheme-load").data("element", $(this)).dialog("open");
            }
        }
    });
    
    $("#pweb-dialog-email-scheme-load").dialog({
        dialogClass: "wp-dialog",
        autoOpen: false,
        resizable: false,
        modal: true,
        buttons: [
            { 
                text: pwebcontact_l10n.ok,
                class : "button-primary",
                click: function(e) {
                    pwebcontact_admin.confirmed = true;
                    $(this).data("element").trigger("change");
                    $(this).dialog("close");
                }
            },
            {
                text: pwebcontact_l10n.cancel,
                class : "button",
                click: function() {
                    $(this).data("element").val("");
                    $(this).dialog("close");
                }
            }
        ]
    });
    
    
    // Themes coverflow
    var $flipster = $("#pweb-themes-coverflow");
    var start = $flipster.find(".pweb-active-theme").index();
    $flipster.flipster({
        itemContainer: "ul",
        itemSelector: "li",
        style: "coverflow",
        start: start > -1 ? start : "center",
        enableKeyboard: true,
        enableMousewheel: false,
        enableTouch: true,
        enableNav: false,
        enableNavButtons: false
    });
    
    // Themes coverflow navigation
    $("#pweb-themes-coverflow-control-prev").click(function(e){
        e.preventDefault();
        $flipster.flipster("jump", "left");
    });
    $("#pweb-themes-coverflow-control-next").click(function(e){
        e.preventDefault();
        $flipster.flipster("jump", "right");
    });
    
    // Reset theme settings
    $("#pweb-themes-coverflow-control-reset").click(function(e){
        e.preventDefault();
        
        $("#pweb_params_theme").val("");
        
        $.each( $(this).data("settings"), function(option, value) {
            var $option = $("#pweb_params_"+option);
            if ($option.prop("tagName").toLowerCase() === "fieldset") {
                if (!value) {
                    value = "0";
                }
                $option.find("#pweb_params_"+option+"_"+value.toString()).prop("checked", true).trigger("change");
            }
            else {
                $option.val(value).trigger("change");
            }
        });
    });
    
    // Load theme settings
    $("#pweb-themes-coverflow-control-load").click(function(e){
        e.preventDefault();
        $(this).blur();
        $("#pweb-dialog-theme").dialog("open");
    });
    
    // Load theme settings dialog
    $("#pweb-dialog-theme").dialog({
        dialogClass: "wp-dialog",
        autoOpen: false,
        resizable: false,
        modal: true,
        buttons: [
            { 
                text: pwebcontact_l10n.ok,
                class : "button-primary",
                click: function(e) {
                    $(this).dialog("close");
                    
                    if (pwebcontact_admin.is_pro) {
                        // Find current theme
                        var $theme = $flipster.find(".flip-current .pweb-theme");
                        // Store theme name
                        $("#pweb_params_theme").val( $theme.data("name") );
                        // Load theme settings
                        $.each( $theme.data("settings"), function(option, value) {
                            var $option = $("#pweb_params_"+option);
                            if ($option.prop("tagName").toLowerCase() === "fieldset") {
                                if (!value) {
                                    value = "0";
                                }
                                $option.find("#pweb_params_"+option+"_"+value.toString()).prop("checked", true).trigger("change");
                            }
                            else {
                                $option.val(value).trigger("change");
                            }
                        });
                        
                        $("#pweb-save-button").click();
                    }
                }
            },
            {
                text: pwebcontact_l10n.cancel,
                class : "button",
                click: function() {
                    $(this).dialog("close");
                }
            }
        ]
    });
    
    // Disable predefined styles in Contact Form upgraded from verison 1.0
    $("#pweb-themes-disable-predefined").click(function(e){
        e.preventDefault();
        $("#pweb_params_style_toggler").val(-1);
        $("#pweb_params_style_form").val(-1);
        $("#pweb_params_style_bg").val(-1);
    });
    
    //TODO select background image
    
    
    $("input.pweb-shortcode").click(function(e){
        e.preventDefault();
        e.stopPropagation();
        $(this).select();
    }).on("keydown", function(e){
        e.preventDefault();
        e.stopPropagation();
        $(this).select();
    });
    
    
    $(".pweb-email-tmpl-vars").click(function(e){
        e.preventDefault();
        
        var width = $(window).width() - 100,
            height = $(window).height() - 150;
        
        if (width > 700) {
            width = 700;
        }
        
        tb_show(pwebcontact_l10n.email_vars, 
            "#TB_inline?width="+width+"&height="+height+"&inlineId=pweb-email-tmpl-vars", "");
    });
    
    
    $("#pweb-tab-check").click(function(){
        
        var is_empty_recipient = (!$("#pweb_params_email_to").val() && $("#pweb_params_email_cms_user").get(0).selectedIndex === 0);
        $("#pweb-email-to-warning")[is_empty_recipient ? "show" : "hide"]();
        
        if ($("#pweb-cog-check .pweb-alert-danger:visible").length) {
            $("#pweb-cog-check-success").hide();
            $("#pweb-cog-check-warning").hide();
            $("#pweb-cog-check-error").show();
            $("#pweb-cog-check-save").hide();
        }
        else if (!pwebcontact_admin.is_pro && ($("#pweb_fields_pro_warning").css("display") !== "none" || ($("#pweb_params_theme").val() !== "free" && $("#pweb_params_theme").val() !== ""))) {
            $("#pweb-cog-check-success").hide();
            $("#pweb-cog-check-warning").show();
            $("#pweb-cog-check-error").hide();
            $("#pweb-cog-check-save").show();
        }
        else {
            $("#pweb-cog-check-success").show();
            $("#pweb-cog-check-warning").hide();
            $("#pweb-cog-check-error").hide();
            $("#pweb-cog-check-save").show();
        }
    });
    
    $("#pweb-cog-check-save").click(function(){
        $("#pweb-save-button").click();
    });
    
    
    $("span.pweb-pro, .pweb-buy").click(function(e){
        e.preventDefault();
        e.stopPropagation();
        
        var width = $(window).width() - 50,
            height = $(window).height();
        
        if (width > 700) {
            width = 700;
        }
        if (height >= 750) {
            height = height - 120;
        }
        else {
            height = height - 20;
        }
        
        tb_show(pwebcontact_l10n.buy_subscription, 
            pwebcontact_admin.buy_url 
                    + (pwebcontact_admin.buy_url.indexOf("?") === -1 ? "?" : "&") 
                    + "TB_iframe=1&width="+width+"&height="+(height-30), "");
    });
    
    
    // save
    $("#pweb_form").on("submit", function(e){
        
        e.preventDefault();
        
        $("#pweb-save-button").get(0).disabled = true;
        
        // close options
        $("#pweb_fields_options_close").click();
        
        // change index in names of fields to match current order
        var counter = 0, last = 0;
        $("#pweb_fields_rows").find("input,textarea").not(":disabled").each(function(){
            //TODO instead of field index use unique class generated for each field
            //TODO store unique class in array as key and as value keep counter value, that each field with the same class would have the same index
            if (typeof $(this).data("index") !== "undefined" && $(this).data("index") !== last) {
                last = $(this).data("index");
                counter++;
            }
            //TODO always update data index with counter
            $(this).attr( "name", $(this).attr("name").replace("["+last+"]", "["+counter+"]") ).data("index", counter);
            
            // generate alias for email template
            if ($(this).hasClass("pweb-custom-field-alias") && !$(this).val()) {
                var $alias = $(this).closest(".pweb-custom-field-options").find("input.pweb-custom-field-label-input");
                if ($alias.length) {
                    var alias = $alias.val().replace(/[^a-z0-9\_]+/gi, "").toLowerCase();
                    $(this).val( alias ? alias : "field_"+counter );
                }
            }
        });
        //TODO get disabled inputs and update name and index with global counter, that when field will be removed from column, then column wuld have unique index and name
        
        // save with ajax
        $.ajax({
			url: $(this).attr("action")+"&ajax=1",
			type: "post", 
			dataType: "json",
            data: $(this).serialize(),
            beforeSend: function() {
                $("#pweb-save-status").html(pwebcontact_l10n.saving + ' <i class="glyphicon glyphicon-refresh"></i>');
            }
		}).always(function(){
            $("#pweb-save-button").get(0).disabled = false;
            
        }).done(function(response, textStatus, jqXHR) {
			if (response && typeof response.success === "boolean") 
			{
                $("#pweb-save-status").html(
                        response.success === true ? pwebcontact_l10n.saved_on+" "+(new Date()).toLocaleTimeString() : pwebcontact_l10n.error);
			}
		}).fail(function(jqXHR, textStatus, errorThrown) {
            $("#pweb-save-status").html("Request error");
            alert(pwebcontact_l10n.request_error+ ". "+ jqXHR.status +" "+ errorThrown);
		});
        
        return false;
    });
    
    
    // Open last active tab
    if (document.location.hash) {
        $(document.location.hash).click();
    }
    else {
        $(window).trigger("resize");
    }
    
    // Set duration of showing/hiding options
    setTimeout(function(){ pwebcontact_admin.duration = 400; }, 600);
    
    $("#wpbody").find(".error, .updated, .update-nag, .update-message, .update-php, .update-plugins").each(function(){
        var $close = $('<button class="button" style="position:absolute;top:5px;right:5px">&times;</button>')
        .click(function(e){
            e.preventDefault();
            $(this).parent().remove();
        });
        $(this).css({'position': 'relative', 'min-height': 36}).prepend($close);
    });
});