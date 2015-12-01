/**
 * @version 2.0.14
 * @package Perfect Easy & Powerful Contact Form
 * @copyright © 2015 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
 * @license GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @author Piotr Moćko
 */

var pwebcontact_l10n = pwebcontact_l10n || {},
    pwebcontact_admin = pwebcontact_admin || {};

if (typeof jQuery !== "undefined") jQuery(document).ready(function($){
	
    pwebcontact_admin.duration = 0;
    pwebcontact_admin.isLocalhost = (document.location.host === "localhost" || document.location.host === "127.0.0.1");
    pwebcontact_admin.domain = document.location.host.replace("www.", "");
    
    // Initialize tooltips
    $(".pweb-has-tooltip").tooltip({
        track: true
    });
    
    var $settings = $("#pweb-settings-content");
    
    // store array of parents for each child
    $settings.find(".pweb-child").each(function(){
        $(this).data("parents", $(this).attr("class").match(/pweb_params_[a-z_]+/g) );
    })
    // hide all childs on page load
    .filter(".pweb-field").hide();
    
    // Show options for checked parent
    $settings.find("fieldset.pweb-parent input").change(function(e){
        var current_id = $(this).attr("id");
            $options = $(this).closest("fieldset").find("input.pweb-parent");
        
        // Hide child options of unchecked options
        $options.filter(":not(:checked)").each(function(){
            hideChildOptions( $(this).attr("id"), current_id );
        });
        
        // Show child options for checked option (current)
        $options.filter(":checked").each(function(){
            var $elements = $settings.find( "."+ $(this).attr("id") );
            $elements.show(pwebcontact_admin.duration);
            // Propagate displaly of child options
            $elements.find("input.pweb-parent:checked").trigger("change");
        });
    });
    
    // Init parent options
    $settings.find("fieldset.pweb-parent").find("input:first").trigger("change");
    
    function hideChildOptions(parent_id, current_id) {
        
        // Find child elements of given parent
        var $elements = $settings.find( "."+ parent_id );
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
                    if ($field.length && $field.is(":checked") && $field.closest(".pweb-field").css("dsiaply") !== "none") {
                        // Do not hide field if parent is checked and visible
                        show = true;
                        break;
                    }
                }
            }
            if (show === false && $(this).css("dsiaply") !== "none") {
                // hide element if it is not already hidden
                $(this).hide(pwebcontact_admin.duration);
                // propagate hiding of options that were shown by this element
                hideChildOptions( $(this).find("input.pweb-parent:checked").attr("id") );
            }
        });
    }
    
	// validate single email
	$('.pweb-filter-email').on('change', function() {
		if (this.value) {
			var regex=/^[a-zA-Z0-9.!#$%&‚Äô*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;
			if (regex.test(this.value)) {
				$(this).removeClass('pweb-invalid');
			} else {
				$(this).addClass('pweb-invalid');
			}
		}
	});
	
	// validate int
	$('.pweb-filter-int').on('change', function() {
		if (this.value && this.value !== 'auto') {
			var value = parseInt(this.value);
			this.value = isNaN(value) ? '' : value;
		}
	});
    
    
    $("#pweb_settings_email_from").change(function(e){
        $(this).removeClass("pweb-invalid");
        var email = $(this).val().toLowerCase();
        if (!pwebcontact_admin.isLocalhost && email.indexOf("@") !== -1 && email.indexOf(pwebcontact_admin.domain) === -1) {
            $(this).addClass("pweb-invalid");
        }
    }).trigger("change");
    
    $("#pweb_settings_smtp_username").change(function(e){
        $(this).removeClass("pweb-invalid");
        if (!pwebcontact_admin.isLocalhost && $("#pweb_settings_mailer input:checked").val() === "smtp") {
            // SMTP user from other domain than site
            var username = $(this).val().toLowerCase();
            if (username.indexOf("@") !== -1 && username.indexOf(pwebcontact_admin.domain) === -1) {
                $(this).addClass("pweb-invalid");
            }
        }
    }).trigger("change");
    
    $("#pweb_settings_smtp_host").change(function(e){
        $(this).removeClass('pweb-invalid');
        if (!pwebcontact_admin.isLocalhost && $("#pweb_settings_mailer input:checked").val() === "smtp") {
            // SMTP host from other domain than site
            var host = $(this).val().toLowerCase();
            if (host !== "localhost" && host.indexOf(pwebcontact_admin.domain) === -1) {
                $(this).addClass("pweb-invalid");
            }
        }
    }).trigger("change");
    
    // Set SMTP port depending on security encryption
    $("#pweb_settings_smtp_secure input").change(function(e){
        var port = 25;
        switch ($(this).val()) {
            case "ssl":
                port = 465;
                break;
            case "tls":
                port = 587;
       }
       $("#pweb_settings_smtp_port").val(port);
    });
    
    
    // save
    $("#pweb_form").on("submit", function(e){
        
        e.preventDefault();
        
        $("#pweb-save-button").get(0).disabled = true;
        
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
    
    if (document.location.hash.indexOf('#pweb_settings_dlid') !== -1) {
        var topOffset = $("#pweb_settings_dlid").offset().top - $("#pweb-adminbar").offset().top - $("#pweb-adminbar").outerHeight();
        $("html,body").animate({ scrollTop: topOffset }, 500, function(){
            $("#pweb_settings_dlid").closest(".pweb-field").addClass("pweb-alert pweb-alert-info");
        });
    }
});