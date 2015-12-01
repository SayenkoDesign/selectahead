/**
 * @version 2.0.14
 * @package Perfect Easy & Powerful Contact Form
 * @copyright © 2015 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
 * @license GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @author Piotr Moćko
 */

var pwebcontact_l10n = pwebcontact_l10n || {},
    pwebcontact_admin = {};

if (typeof jQuery !== "undefined") jQuery(document).ready(function($){
    
    // Initialize tooltips
    $(".pweb-has-tooltip").tooltip({
        track: true
    });
    
    // Toogle state action
    $(".pweb-action-toggle-state").click(function(e){
        
        var that = this;
        e.preventDefault();
        $(this).blur();
        
        $.ajax({
			url: $(this).data("action") + ($(this).data("state") ? 0 : 1),
			type: "get", 
			dataType: "json",
            beforeSend: function() {
                $(that).removeClass("pweb-text-success pweb-text-danger").find("i").get(0).className = "glyphicon glyphicon-refresh";
            }
		}).done(function(response, textStatus, jqXHR) {
			
			if (response && typeof response.success === "boolean") 
			{
				if (response.success === true) 
				{
                    // change state icon and color
                    $(that).data("state", response.state)
                            .addClass(response.state ? "pweb-text-success" : "pweb-text-danger")
                            .find("i").get(0).className = "glyphicon " + (response.state ? "glyphicon-ok-sign" : "glyphicon-remove-sign");
				}
				else 
                {
                    // restore state icon and color and alert response message
                    $(that).addClass(!response.state ? "pweb-text-success" : "pweb-text-danger")
                            .find("i").get(0).className = "glyphicon " + (!response.state ? "glyphicon-ok-sign" : "glyphicon-remove-sign");
                    alert(response.message);
                }
			}
		}).fail(function(jqXHR, textStatus, errorThrown) {
			
            // restore state icon and color
            $(that).addClass($(that).data("state") ? "pweb-text-success" : "pweb-text-danger")
                    .find("i").get(0).className = "glyphicon " + ( $(that).data("state") ? "glyphicon-ok-sign" : "glyphicon-remove-sign" );
			alert(pwebcontact_l10n.request_error+'. '+ jqXHR.status +' '+ errorThrown);
		});
    });
    
    // Delete action
    $(".pweb-action-delete").click(function(e){
        
        e.preventDefault();
        $(this).blur();
        
        $("#pweb-dialog-delete")
                .data("element", $(this)) // pass delete button handler to dialog
                .dialog("open")
                .find(".pweb-dialog-form-title").text( $(this).data("form-title") ) // set form title in dialog
                ;
    });
    
    // Delete dialog box
    $("#pweb-dialog-delete").dialog({
        dialogClass: "wp-dialog",
        autoOpen: false,
        resizable: false,
        modal: true,
        buttons: [
            { 
                text: pwebcontact_l10n.delete,
                class : "button-primary",
                click: function() {
                    
                    $(this).dialog("close");
                    var $element = $(this).data("element");
                    
                    $.ajax({
                        url: $element.data("action"),
                        type: "get", 
                        dataType: "json",
                        beforeSend: function() {
                            $element.find("i").get(0).className = "glyphicon glyphicon-refresh";
                        }
                    }).done(function(response, textStatus, jqXHR) {

                        if (response && typeof response.success === "boolean") 
                        {
                            if (response.success === true) 
                            {
                                // fade out form and remove it
                                $element.closest(".pweb-panel-box").fadeOut("slow", function() {
                                    $(this).remove();
                                });
                            }
                            else 
                            {
                                // restore delete button icon and alert response message
                                $element.find("i").get(0).className = "glyphicon glyphicon-trash";
                                alert(response.message);
                            }
                        }
                    }).fail(function(jqXHR, textStatus, errorThrown) {

                        // restore delete button icon
                        $element.find("i").get(0).className = "glyphicon glyphicon-trash";
                        alert(pwebcontact_l10n.request_error+'. '+ jqXHR.status +' '+ errorThrown);
                    });
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
    
    
    
    $("input.pweb-shortcode").click(function(e){
        e.preventDefault();
        e.stopPropagation();
        $(this).select();
    }).on("keydown", function(e){
        e.preventDefault();
        e.stopPropagation();
        $(this).select();
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
            height = height - 40;
        }
        
        tb_show(pwebcontact_l10n.buy_subscription, 
            pwebcontact_admin.buy_url 
                    + (pwebcontact_admin.buy_url.indexOf("?") === -1 ? "?" : "&") 
                    + "TB_iframe=1&width="+width+"&height="+(height-30), "");
    });

    $("#wpbody").find(".error, .updated, .update-nag, .update-message, .update-php, .update-plugins").each(function(){
        var $close = $('<button class="button" style="position:absolute;top:5px;right:5px">&times;</button>')
        .click(function(e){
            e.preventDefault();
            $(this).parent().remove();
        });
        $(this).css({'position': 'relative', 'min-height': 36}).prepend($close);
    });
});