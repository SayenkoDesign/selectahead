/**
 * @version 2.1.2
 * @package Perfect Easy & Powerful Contact Form
 * @copyright © 2015 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
 * @license GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @author Piotr Moćko
 */

// TODO check if aliases of fields are unique

var pwebcontact_l10n = pwebcontact_l10n || {},
    pwebcontact_admin = pwebcontact_admin || {};

if (typeof jQuery !== "undefined") jQuery(document).ready(function($){

    if (typeof pwebcontact_admin.is_pro === "undefined") {
        pwebcontact_admin.is_pro = false;
    }

    pwebcontact_admin.item_index = 0;
    pwebcontact_admin.counter = 0;
    pwebcontact_admin.pro_fields = 0;
    pwebcontact_admin.create_column = true;
    pwebcontact_admin.stop_sorting = false;

    // allow rows sorting
    var $rows = $("#pweb_fields_rows");
    $rows.sortable({
        axis: "y",
        opacity: 0.7,
        handle: ".pweb-fields-sort-row",
        cancel: ".pweb-fields-cols",
        start: function( event, ui ) {
            ui.item.addClass("pweb-dragged");
        },
        stop: function( event, ui ) {
            ui.item.removeClass("pweb-dragged");
            // change order of sortable items in DOM
            $(this).sortable("refresh");
        }
    });

    //TODO arg create column
    // add new row
    function addRow(index, after) {

        if (typeof index === "undefined") index = 0;
        if (typeof after === "undefined") after = true;

        pwebcontact_admin.counter++;

        //TODO add unique class to row input element - will be used for saving, keep index
        var $row = $('<div class="pweb-fields-row pweb-clearfix">'
                        +'<input type="hidden" name="fields['+pwebcontact_admin.counter+'][type]" value="row" data-index="'+pwebcontact_admin.counter+'">'
                        +'<div class="pweb-fields-sort-row pweb-has-tooltip" title="'+pwebcontact_l10n.drag_row+'">&varr;</div>'
                        +'<div class="pweb-fields-cols"></div>'
                        +'<div class="pweb-fields-add-col pweb-has-tooltip" title="'+pwebcontact_l10n.add_column+'"><i class="glyphicon glyphicon-plus"></i></div>'
                    +'</div>');

        $row.data("cols", 0).find(".pweb-fields-cols").sortable({
            connectWith: ".pweb-fields-cols",
            opacity: 0.7,
            cursor: "move",
            placeholder: "pweb-sortable-placeholder",
            tolerance: "pointer",
            receive: function( event, ui ) {
                ui.sender.removeClass("pweb-placeholder");
                $rows.find(".pweb-placeholder-top").removeClass("pweb-placeholder-top");

                if (ui.sender !== ui.item.parent()) {

                    //TODO function

                    if (pwebcontact_admin.stop_sorting !== false) {
                        pwebcontact_admin.stop_sorting.append(ui.item);
                        pwebcontact_admin.stop_sorting = false;
                    }
                    else {
						var $row = ui.item.closest(".pweb-fields-row");
                        //TODO arg do not create column
                        $row = addRow( $row.index(), ui.item.index()+1 > $row.data("cols") );
                        $row.find(".pweb-fields-col").remove();
                        $row.find(".pweb-fields-cols").append(ui.item);
                    }

                    // previous row
                    var $row = ui.sender.closest(".pweb-fields-row");
                    var cols = $row.data("cols");
                    if (cols > 1) {
                        // decrease columns count in previous row
                        $row.removeClass("pweb-fields-cols-" + cols.toString());
                        cols--;
                        $row.addClass("pweb-fields-cols-" + cols.toString()).data("cols", cols);

                        if (cols < 3) {
                            // enable droping of field types on add column button
                            $row.find(".pweb-fields-add-col").droppable("enable");
                        }
                    }
                    else {
                        // remove row
                        $row.remove();
                    }
                }
            },
            change: function( event, ui ) {

                $rows.find(".pweb-placeholder-top").removeClass("pweb-placeholder-top");

				var helper_row_index = ui.helper.closest(".pweb-fields-row").index(),
					helper_row_columns = ui.helper.closest(".pweb-fields-row").data("cols"),
					placeholder_row_index = ui.placeholder.closest(".pweb-fields-row").index(),
					placeholder_index = ui.placeholder.index(),
                    placeholder_row_columns = ui.placeholder.closest(".pweb-fields-row").data("cols");

				ui.placeholder.show();

				if (helper_row_columns === 1) {

					if (helper_row_index - placeholder_row_index === 1) {
						// before
						if (placeholder_index === placeholder_row_columns) {
							// skip before
							ui.placeholder.hide();
							return;
						}
					}
					else if (helper_row_index - placeholder_row_index === -1) {
						// after
						if (placeholder_index === 0) {
							// skip after
							ui.placeholder.hide();
							return;
						}
					}
				}

				if (placeholder_index !== 0 && placeholder_index !== placeholder_row_columns) {
                    ui.placeholder.parent().addClass("pweb-placeholder-top");
                }
            },
            start: function( event, ui ) {
                event.stopPropagation();
                // remember position of current column
                pwebcontact_admin.item_index = ui.item.index();
                ui.item.addClass("pweb-dragged");
                ui.item.parent().addClass("pweb-placeholder");
            },
            stop: function( event, ui ) {
                ui.item.removeClass("pweb-dragged");
                ui.item.parent().removeClass("pweb-placeholder");
                // change order of sortable items in DOM
                //$rows.find(".pweb-fields-cols").sortable("refresh");
                $rows.find(".pweb-placeholder-top").removeClass("pweb-placeholder-top");

                //TODO function
                if (pwebcontact_admin.stop_sorting !== false) {


                    // previous row
                    var $row = ui.item.parent().closest(".pweb-fields-row");

                    pwebcontact_admin.stop_sorting.append(ui.item);
                    pwebcontact_admin.stop_sorting = false;

                    var cols = $row.data("cols");
                    if (cols > 1) {
                        // decrease columns count in previous row
                        $row.removeClass("pweb-fields-cols-" + cols.toString());
                        cols--;
                        $row.addClass("pweb-fields-cols-" + cols.toString()).data("cols", cols);

                        if (cols < 3) {
                            // enable droping of field types on add column button
                            $row.find(".pweb-fields-add-col").droppable("enable");
                        }
                    }
                    else {
                        // remove row
                        $row.remove();
                    }
                }
            }
        });

        //TODO function addColumn
        // add new column button
        $row.find(".pweb-fields-add-col").click(function(){
            var cols = $row.data("cols");
            // add column only if not exited limit
            if (cols < 3) {

                // increase columns count
                $row.removeClass("pweb-fields-cols-" + cols.toString());
                cols++;
                $row.addClass("pweb-fields-cols-" + cols.toString()).data("cols", cols);

                if (cols === 3) {
                    // disable droping of field types on add column button
                    $(this).droppable("disable");
                }

                pwebcontact_admin.counter++;

                //TODO function createColumn
                if (pwebcontact_admin.create_column) {

                    //TODO add unique class to column input element - will be used for saving, keep index
                    // create new column
                    var $col = $('<div class="pweb-fields-col">'
                                    +'<input type="hidden" name="fields['+pwebcontact_admin.counter+'][type]" value="column" data-index="'+pwebcontact_admin.counter+'">'
                                    +'<div class="pweb-fields-remove-col pweb-has-tooltip" title="'+pwebcontact_l10n.delete+'"><i class="glyphicon glyphicon-remove"></i></div>'
                                +'</div>');

                    // insert field by droping field type on column
                    $col.droppable({
                        accept: function(item) {
                            return (item.hasClass("pweb-custom-fields-type") && !item.hasClass("pweb-custom-fields-disabled"));
                        },
                        activeClass: "pweb-droppable",
                        hoverClass: "pweb-droppable-hover",
                        drop: function(event, ui) {
                            // drop field type on column slot
                            dropField( ui.draggable, $(this) );
                        }
                    });

                    // remove button
                    $col.find(".pweb-fields-remove-col").click(function(){
                        if ($col.hasClass("pweb-has-field")) {
                            // remove field
                            if (pwebcontact_admin.confirm === false || pwebcontact_admin.confirmed === true) {
                                pwebcontact_admin.confirmed = false;

                                // check if field options are opened
                                var $field = $col.find(".pweb-custom-field-container");
                                if ($("#pweb_fields_options").data("parent") === $field.attr("id")) {
                                    // close field options if opened
                                    $("#pweb_fields_options_close").click();
                                }
                                if (!pwebcontact_admin.is_pro && $field.hasClass("pweb-pro")) {
                                    pwebcontact_admin.pro_fields--;
                                    if (pwebcontact_admin.pro_fields <= 0) {
                                        $("#pweb_fields_pro_warning").fadeOut("slow");
                                    }
                                }
                                // hide upload path warning
                                if ($field.data("type") === "upload") {
                                    $("#pweb-upload-path-warning").hide();
                                }
                                // show field type if only one instance is allowed
                                if ($field.hasClass("pweb-custom-fields-single")) {
                                    $("#pweb_field_type_" + $field.data("type")).removeClass("pweb-custom-fields-disabled");
                                }
                                // enable droping of field types on add column button
                                $col.removeClass("pweb-has-field pweb-custom-field-active pweb-custom-field-type-"+$field.data("type"))
                                        .droppable("enable");
                                //TODO remove index update
                                // get field index which might change after form saving and update it for column
                                var index = $field.find("input:first").data("index");
                                // destroy DOM element
                                $field.remove();
                                // update index of column
                                var $col_type = $col.find("input");
                                $col_type.attr( "name", $col_type.attr("name").replace("["+$col_type.data("index")+"]", "["+index+"]") ).data("index", index);
                                // enable field type of column
                                $col_type.get(0).disabled = false;
                            }
                            else $("#pweb-dialog-field-delete").data("element", $(this)).dialog("open");
                        }
                        else {

                            var $row = $col.closest(".pweb-fields-row"),
                                cols = $row.data("cols");
                            if (cols > 1) {
                                // decrease columns count in current row
                                $row.removeClass("pweb-fields-cols-" + cols.toString());
                                cols--;
                                $row.addClass("pweb-fields-cols-" + cols.toString()).data("cols", cols);
                                // destroy DOM element
                                $col.droppable("destroy").remove();

                                if (cols < 3) {
                                    // enable droping of field types on add column button
                                    $row.find(".pweb-fields-add-col").droppable("enable");
                                }
                            }
                            else {
                                // remove whole row
                                $row.remove();
                            }
                        }
                    }).tooltip();

                    // Insert new column into row
                    $col.appendTo( $row.find(".pweb-fields-cols") );
                }
                pwebcontact_admin.create_column = true;

                // Refresh DOM elements
                $row.find(".pweb-fields-cols").sortable("refresh");
            }
        }).droppable({
            accept: function(item) {
                return (item.hasClass("pweb-has-field") || (item.hasClass("pweb-custom-fields-type") && !item.hasClass("pweb-custom-fields-disabled")));
            },
            tolerance: "pointer",
            activeClass: "pweb-droppable",
            hoverClass: "pweb-droppable-hover",
            drop: function(event, ui) {
                if (ui.draggable.hasClass("pweb-has-field")) {
                    pwebcontact_admin.stop_sorting = $(this).prev();
                    pwebcontact_admin.create_column = false;
                    $(this).click();
                }
                else {
                    $(this).click();
                    // drop field type on add column button
                    dropField( ui.draggable, $(this).prev().children().last() );
                }
            }
        }).trigger("click").tooltip();

        // Insert new row and refresh DOM elements
        var $target = $rows.children().eq(index);
        if ($target.length) {
            $target[after ? "after" : "before"]($row);
        }
        else {
            $rows.append($row);
        }
        $rows.sortable("refresh");

        return $row;
    }

    $("#pweb_fields_add_row_before").click(function(){
        //TODO arg create column
        addRow( 0, false );
    }).droppable({
        accept: function(item) {
            return (item.hasClass("pweb-custom-fields-type") && !item.hasClass("pweb-custom-fields-disabled"));
        },
        activeClass: "pweb-droppable",
        hoverClass: "pweb-droppable-hover",
        drop: function(event, ui) {
            //TODO arg create column
            addRow( 0, false );
            // drop field type on add row button
            //TODO pass column return
            dropField( ui.draggable, $(this).next().children().first().find(".pweb-fields-cols").children().first() );
        }
    });

    $("#pweb_fields_add_row_after").click(function(){
        //TODO arg create column
        addRow( $(this).prev().children().length-1, true );
    }).droppable({
        accept: function(item) {
            return (item.hasClass("pweb-custom-fields-type") && !item.hasClass("pweb-custom-fields-disabled"));
        },
        activeClass: "pweb-droppable",
        hoverClass: "pweb-droppable-hover",
        drop: function(event, ui) {
            //TODO arg create column
            addRow( $(this).prev().children().length-1, true );
            // drop field type on add row button
            //TODO pass column return
            dropField( ui.draggable, $(this).prev().children().last().find(".pweb-fields-cols").children().first() );
        }
    });


    // Drag field types to insert field into column
    $("#pweb_fields_types .pweb-custom-fields-type").draggable({
        revert: true
    });


    // Display field label on sort list
    $("#pweb_fields_types .pweb-custom-field-label-input").change(function(){
        $( "#"+ $(this).attr("id").replace("_label", "_container") )
            .find(".pweb-custom-field-label span")
            .text( $(this).val() );
    });


    // Display option of single field
    $("#pweb_fields_types .pweb-custom-field-show-options").click(function(e){
        e.preventDefault();
        // Close previous field options
        $("#pweb_fields_options_close").click();
        // Hide field types
        $("#pweb_fields_types").hide();
        // Move fields option to modal
        var $parent = $(this).closest(".pweb-custom-field-container");
        $("#pweb_fields_options_content").append( $parent.find(".pweb-custom-field-options") );
        // Activate field slot
        $parent.closest(".pweb-fields-col").addClass("pweb-custom-field-active");
        // Hide all system fields options
        $("#pweb_fields_options .pweb-fields-options-content").hide();
        // Show constant options for single system fields
        $("#pweb_fields_options_content_" + $parent.data("type")).show();
        // Remeber feild options parent
        $("#pweb_fields_options").data("parent", $parent.attr("id") ).show();
    });

    // Hide options of single field
    $("#pweb_fields_options_close").click(function(e){
        e.preventDefault();
        var parent = $("#pweb_fields_options").data("parent");
        if (parent) {
            // Move fields options back to parent and deactivate field slot
            $("#"+parent)
                    .append( $("#pweb_fields_options_content").children() )
                    .closest(".pweb-fields-col").removeClass("pweb-custom-field-active");;
            // Forget parent and hide options
            $("#pweb_fields_options").data("parent", "").hide();
            // Display field types
            $("#pweb_fields_types").show();
        }
        $(this).blur();
    });

    function dropField(source, target, show_options) {

        //TODO remove index
        // get index of current column
        var inputIndex = target.find("input, button"),
            index = inputIndex.data("index");

        // disable field type of column
        inputIndex.get(0).disabled = true;

        //TODO add unique class to all options of this field - will be used for saving
        // Change options IDs and names
        var $field = source.find(".pweb-custom-field-container").clone(true, true);
        $field.find("input,textarea,select").each(function(){
            this.disabled = false;
            $(this)
                .data("index", index) //TODO remove index
                .attr("id", $(this).attr("id").replace(/_X_/g, "_"+index+"_") ) //TODO remove index, use fields global counter
                .attr("name", $(this).attr("name").replace(/\[X\]/g, "["+index+"]") ); //TODO remove index, use fields global counter
        });
        $field.find("button").each(function(){
            this.disabled = false;
            $(this)
                .data("index", index) //TODO remove index
                .attr('id', "pweb_fields_"+index+"_container");
        });
        $field.find("fieldset").each(function(){
            this.disabled = false;
            $(this).attr("id", $(this).attr("id").replace(/_X_/g, "_"+index+"_") ); //TODO remove index, use fields global counter
        });
        $field.find("label").each(function(){
            $(this)
                .attr("id", $(this).attr("id").replace(/_X_/g, "_"+index+"_") ) //TODO remove index, use fields global counter
                .attr("for", $(this).attr("for").replace(/_X_/g, "_"+index+"_") ); //TODO remove index, use fields global counter
        });
        $field.attr("id", "pweb_fields_"+index+"_container").removeClass("pweb-custom-field-type-"+$field.data("type")); //TODO remove index, use fields global counter

        // Disable adding new fields into this column and insert field details
        target.droppable("disable").addClass("pweb-has-field pweb-custom-field-type-"+$field.data("type")).prepend($field);

        // Hide remove action for Send button
        if ($field.data("type") === "button_send") {
            target.find(".pweb-fields-remove-col").remove();
        }
        else if ($field.data("type") === "upload") {
            $("#pweb-upload-path-warning").show();
        }


        // Display field options
        /*if (typeof show_options === "undefined" || show_options !== false) {
         $field.find(".pweb-custom-field-show-options").click();
         }*/

        // Hide field on fields types list if only one instance is allowed
        if (source.hasClass("pweb-custom-fields-single")) {
            source.addClass("pweb-custom-fields-disabled");
        }

        if (!pwebcontact_admin.is_pro && $field.hasClass("pweb-pro")) {
            pwebcontact_admin.pro_fields++;
            if (pwebcontact_admin.pro_fields > 0) {
                $("#pweb_fields_pro_warning").fadeIn("slow");
            }
        }

        return $field;
    }

    function loadFields(fields, parse) {

        var $row = null, $cols = null, $addCol = null, rowCreated = false;

        if (typeof parse === "undefined" || parse !== false) {
            fields = $.parseJSON( fields );
        }

        // reset number of loaded fields
        pwebcontact_admin.pro_fields = 0;
        if (!pwebcontact_admin.is_pro) {
            $("#pweb_fields_pro_warning").fadeOut("fast");
        }

        $.each(fields, function(i, field) {

            if (field.type === "row") {
                // create new row
                $("#pweb_fields_add_row_after").trigger("click");
                $row = $rows.children().last();
                $cols = $row.find(".pweb-fields-cols");
                $addCol = $row.find(".pweb-fields-add-col");
                rowCreated = true;
            }
            else {
                if (rowCreated === false) {
                    // add new column if not already created with new row
                    //TODO use function
                    $addCol.trigger("click");
                }
                if (field.type !== "column" && $("#pweb_field_type_"+field.type).length) {
                    var $target = $cols.children().last();
                    // add field
                    //TODO pass column return
                    dropField( $("#pweb_field_type_"+field.type), $target, false );

                    // load field options
                    var index = $target.find(".pweb-custom-field-options input:first").data("index");
                    $.each(field, function(key, value) {
                        if (key !== "type") {
                            var $option = $target.find("#pweb_fields_"+index+"_"+key);
                            if ($option.length) {
                                if ($option.prop("tagName").toLowerCase() === "fieldset") {
                                    if (!value) {
                                        value = "0";
                                    }
                                    $option.find("#pweb_fields_"+index+"_"+key+"_"+value.toString()).prop("checked", true);
                                }
                                else {
                                    if( 'object' === typeof value && key == 'newsletter_lists' )
                                    {
                                        document.newsletter_lists = value;
                                    }
                                    else
                                    {
                                        $option.val( value.replace(/\\+/g, "\\") );
                                    }
                                }

                                if (key === "label") {
                                    $target.find(".pweb-custom-field-label span").text(value);
                                }
                            }
                        }
                    });
                }
                rowCreated = false;
            }
        });
    }


    // load sample fields
    $("#pweb_load_fields").change(function(){
        var that = this;
        if (this.selectedIndex) {
            var $rowsChildren = $rows.children();
            // confirm old fields removal
            if ($rowsChildren.length <= 1 || pwebcontact_admin.confirmed === true) {
                pwebcontact_admin.confirmed = false;

                $.ajax({
                    url: $(this).data("action"),
                    type: "POST",
                    dataType: "json",
                    data: {
                        "fields": $(this).val()
                    },
                    beforeSend: function() {
                        $('<i class="glyphicon glyphicon-refresh"></i>').insertAfter( that );
                    }
                }).done(function(response, textStatus, jqXHR) {

                    // hide loading
                    $(that).next("i.glyphicon-refresh").remove();

                    if (response) {
                        // hide options to remove them
                        $("#pweb_fields_options_close").click();
                        // remove fields
                        pwebcontact_admin.confirm = false;
                        $rowsChildren.find(".pweb-fields-remove-col").click();
                        // remove rows with columns and fields without confirmation
                        $rowsChildren.remove();
                        // load sample fields
                        loadFields( response, false );
                        // set default option
                        that.selectedIndex = 0;
                    }
                    else {
                        alert(pwebcontact_l10n.error_loading_fields_settings);
                    }
                }).fail(function(jqXHR, textStatus, errorThrown) {

                    alert(pwebcontact_l10n.request_error+'. '+ jqXHR.status +' '+ errorThrown);
                });
            }
            else {
                $("#pweb-dialog-fields-load").data("element", $(this)).dialog("open");
            }
        }
    });


    // load fields dialog
    $("#pweb-dialog-fields-load").dialog({
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


    // delete field dialog
    $("#pweb-dialog-field-delete").dialog({
        dialogClass: "wp-dialog",
        autoOpen: false,
        resizable: false,
        modal: true,
        buttons: [
            {
                text: pwebcontact_l10n.delete,
                class : "button-primary",
                click: function(e) {
                    pwebcontact_admin.confirmed = true;
                    $(this).data("element").click();
                    $(this).dialog("close");
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



    // load fields
    loadFields( $("#pweb_params_fields").val() || '{}' );
    $("#pweb_params_fields").get(0).disabled = true;

    // load Send button if missing
    if ($rows.children().length === 0) {

        $("#pweb_load_fields").val("Contact form (FREE)").trigger("change");

        //$("#pweb_fields_add_row_after").click();
        //dropField( $("#pweb_field_type_button_send"), $("#pweb_fields_add_row_after").prev().children().last().find(".pweb-fields-cols").children().first(), false );
    }

    $("body").css("overflow-y", "scroll");

    //newsletter select change
    $('.newsletter_provider').change(function(){

        var that        = $(this);
        var value       = that.val();
        var parent      = that.parents('.pweb-custom-field-options');
        var actual      = $('.newsletter_depends_on_' + value, parent);
        var canWeDoAjax = true;

        $('[class*="newsletter_depends_on_"]', parent).parents('.pweb-field').slideUp();
        actual.parents('.pweb-field').slideDown();

        actual.each(function(i,e){

            if($(e).val().length === 0 )
                canWeDoAjax = false;
        });

        if( !canWeDoAjax )
            return false;

        $.ajax({
            url: $('.newsletter_ajax', parent).data('action'),
            type: "POST",
            dataType: "json",
            data: actual.serialize() + '&newsletter_type=' + value,
            beforeSend: function () {
                $('<i class="glyphicon glyphicon-refresh"></i>').insertAfter(that);
            },
            success: function(response){

                $(that).next("i.glyphicon-refresh").remove();

                if( response.error !== undefined )
                {
                    alert( response );
                }
                else
                {

                    $('select.newsletter_lists option', parent).remove();

                    $.each(response, function(value,title){

                        $('<option></option>')
                            .attr('value', '{"'+value+'":"'+title+'"}')
                            .text(title)
                            .attr('selected', $.inArray('{"'+value+'":"'+title+'"}', document.newsletter_lists) > -1)
                            .appendTo($('select.newsletter_lists', parent));
                    });
                }
            },
            error: function(error, xhr, message){
                console.log( error, xhr, message );
            }
        });

    }).trigger('change');

    $('.newsletter_ajax').off().on('click', function(e){

        e.preventDefault();

        var that        = $(this);
        var parent      = that.parents('.pweb-custom-field-options');
        var selected    = $('select.newsletter_provider option:selected', parent).val();
        var actual      = $('.newsletter_depends_on_' + selected, parent);

        var canWeDoAjax = true;

        actual.each(function(i,e){

            if($(e).val().length === 0 )
                canWeDoAjax = false;
        });

        if( !canWeDoAjax )
            return false;

        $.ajax({
            url: that.data('action'),
            type: "POST",
            dataType: "json",
            data: actual.serialize() + '&newsletter_type=' + selected,
            beforeSend: function () {
                $('<i class="glyphicon glyphicon-refresh"></i>').insertAfter(that);
            },
            success: function(response){

                $(that).next("i.glyphicon-refresh").remove();

                if( response.error !== undefined )
                {
                    alert( response );
                }
                else
                {

                    $('select.newsletter_lists option', parent).remove();

                    $.each(response, function(value,title){

                        $('<option></option>')
                            .attr('value', '{"'+value+'":"'+title+'"}')
                            .text(title)
                            .appendTo($('select.newsletter_lists', parent));
                    });
                }
            },
            error: function(error, xhr, message){
                console.log( error, xhr, message );
            }
        });
    });

});