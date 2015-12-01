<?php

class FMViewProduct_option {
  ////////////////////////////////////////////////////////////////////////////////////////
  // Events                                                                             //
  ////////////////////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////////////////////
  // Constants                                                                          //
  ////////////////////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////////////////////
  // Variables                                                                          //
  ////////////////////////////////////////////////////////////////////////////////////////
  private $model;


  ////////////////////////////////////////////////////////////////////////////////////////
  // Constructor & Destructor                                                           //
  ////////////////////////////////////////////////////////////////////////////////////////
  public function __construct($model) {
    $this->model = $model;
  }
  ////////////////////////////////////////////////////////////////////////////////////////
  // Public Methods                                                                     //
  ////////////////////////////////////////////////////////////////////////////////////////
  public function display() {
    $id = ((isset($_GET['field_id'])) ? esc_html(stripslashes($_GET['field_id'])) : 0);
    $property_id = ((isset($_GET['property_id'])) ? esc_html(stripslashes($_GET['property_id'])) : '-1');
    $url_for_ajax = ((isset($_GET['url_for_ajax'])) ? esc_html(stripslashes($_GET['url_for_ajax'])) : '');
    ?>
    <span style="position: fixed; right: 10px;">
      <img title="Add" style="cursor: pointer; vertical-align: middle;" src="<?php echo WD_FM_URL . '/images/save.png'; ?>" onclick="save_options();" />
      <img title="Cancel" style=" cursor:pointer; vertical-align:middle;" src="<?php echo  WD_FM_URL . '/images/cancel_but.png'; ?>" onclick="window.parent.tb_remove();" />
    </span><br />
    <div>
      <br />
      <fieldset style="border: 1px solid #CCCCCC;">
        <legend><label style="color: rgb(0, 174, 239); font-weight: bold; font-size: 13px;">Properties</label></legend>
        <br />
        <div style="margin-left:10px">
          <label style="color: rgb(0, 174, 239); font-weight: bold; font-size: 13px; margin-right:20px">Type </label>
          <select id="option_type" style="width: 200px; border-width: 1px;" onchange="type_add_predefined(this.value)">
            <option value="Custom" selected="selected">Custom</option>
            <option value="Color">Color</option>
            <option value="T-Shirt Size">T-Shirt Size</option>
            <option value="Print Size">Print Size</option>
            <option value="Screen Resolution">Screen Resolution</option>
            <option value="Shoe Size">Shoe Size</option>
          </select>
          <br /><br />
          <label style="color: rgb(0, 174, 239); font-weight: bold; font-size: 13px; margin-right:20px">Title </label>
          <input type="text" style="width:200px" id="option_name"/>
          <br />
          <br />
          <label style="color: rgb(0, 174, 239); font-weight: bold; font-size: 13px;">Properties</label> &nbsp;
          <img id="el_choices_add" src="<?php echo WD_FM_URL . '/images/add.png'; ?>" style="cursor: pointer;" title="add" onclick="add_choise_option()" />
          <br />
          <div style="margin-left:0px" id="options"></div>
        </div>
      </fieldset>
    </div>
    <script>
    var j = 0;
    function save_options() {
      if (document.getElementById('option_name').value == '') {
        alert('The option must have a title.')
        return;
      }
      <?php
      if ($property_id == '-1') {
        ?>
        for (i = 30; i >= 0; i--) {
          if (window.parent.document.getElementById(<?php echo $id; ?>+"_propertyform_id_temp" + i)) {
            i = i + 1;
            select_ = document.createElement('select');
            select_.setAttribute("id", <?php echo $id; ?>+"_propertyform_id_temp" + i);
            select_.setAttribute("name", <?php echo $id; ?>+"_propertyform_id_temp" + i);
            select_.style.cssText = "width:auto; margin:2px 0px";
            break;
          }
        }
        if (i == -1) {
          i = 0;
          select_ = document.createElement('select');
          select_.setAttribute("id", <?php echo $id; ?>+"_propertyform_id_temp" + i);
          select_.setAttribute("name", <?php echo $id; ?>+"_propertyform_id_temp" + i);
          select_.style.cssText = "width:auto; margin:2px 0px";
        }
        for (k = 0; k <= 50; k++) {
          if (document.getElementById('el_option' + k)) {
            var option = document.createElement('option');
            option.setAttribute("id", "<?php echo $id; ?>_" + i + "_option" + k);
            option.setAttribute("value", document.getElementById('el_option' + k).value);
            option.innerHTML = document.getElementById('el_option' + k).value;
            select_.appendChild(option);
          }
        }
        var select_label = document.createElement('label');
        select_label.innerHTML = document.getElementById('option_name').value;
        select_label.style.cssText = "margin-right:5px";
        select_label.setAttribute("class", 'mini_label');
        select_label.setAttribute("id", '<?php echo $id; ?>_property_label_form_id_temp' + i);
        var span_ = document.createElement('span');
        span_.style.cssText = "margin-right:15px";
        span_.setAttribute("id", '<?php echo $id; ?>_property_' + i);
        div_ = window.parent.document.getElementById("<?php echo $id; ?>_divform_id_temp");
        span_.appendChild(select_label);
        span_.appendChild(select_);
        div_.appendChild(span_);
        var li_ = document.createElement('li');
        li_.setAttribute("id", 'property_li_' + i);
        var li_label = document.createElement('label');
        li_label.innerHTML = document.getElementById('option_name').value;
        li_label.setAttribute("id", 'label_property_' + i);
        li_label.style.cssText = "font-weight:bold; font-size: 13px";
        var li_edit = document.createElement('a');
        li_edit.setAttribute("href", "<?php echo $url_for_ajax; ?>?action=product_option&field_id=<?php echo $id; ?>&property_id=" + i + "&width=600&height=400&TB_iframe=1&url_for_ajax=<?php echo $url_for_ajax; ?>");
        li_edit.setAttribute("class", "thickbox-preview" + i);
        var li_edit_img = document.createElement('img');
        li_edit_img.setAttribute("src", "<?php echo WD_FM_URL . '/images/edit.png'; ?>");
        li_edit_img.style.cssText = "margin-left: 13px; width: 14px;";
        li_edit.appendChild(li_edit_img);
        var li_x = document.createElement('img');
        li_x.setAttribute("src", "<?php echo WD_FM_URL . '/images/delete.png'; ?>");
        li_x.setAttribute("onClick", 'remove_property(<?php echo $id; ?>,' + i + ')');
        li_x.style.cssText = "margin-left: 3px; cursor: pointer; width: 14px;";
        ul_ = window.parent.document.getElementById("option_ul");
        li_.appendChild(li_label);
        li_.appendChild(li_edit);
        li_.appendChild(li_x);
        ul_.appendChild(li_);
        // Edit paypal properties page open in popup.
        window.parent.spider_popup(i);
        <?php
      }
      else {
        ?>
        i = '<?php echo $property_id ?>';
        var select_ = window.parent.document.getElementById('<?php echo $id; ?>_propertyform_id_temp<?php echo $property_id; ?>');
        select_.innerHTML = "";
        for (k = 0; k <= j; k++) {
          if (document.getElementById('el_option' + k)) {
            var option = document.createElement('option');
            option.setAttribute("id", "<?php echo $id; ?>_" + i + "_option" + k);
            option.setAttribute("value", document.getElementById('el_option' + k).value);
            option.innerHTML = document.getElementById('el_option' + k).value;
            select_.appendChild(option);
          }
        }
        var select_label = document.createElement('label');
        select_label.innerHTML = document.getElementById('option_name').value;
        select_label.style.cssText = "margin-right:5px";
        select_label.setAttribute("class", 'mini_label');
        select_label.setAttribute("id", '<?php echo $id; ?>_property_label_form_id_temp' + i);
        var span_ = window.parent.document.getElementById('<?php echo $id; ?>_property_<?php echo $property_id; ?>');
        span_.innerHTML = '';
        span_.appendChild(select_label);
        span_.appendChild(select_);
        window.parent.document.getElementById('label_property_<?php echo $property_id; ?>').innerHTML = document.getElementById('option_name').value;
        <?php
      }
      ?>
      window.parent.tb_remove();
    }
    function type_add_predefined(type) {
      document.getElementById('options').innerHTML = '';
      switch (type) {
        case 'Custom': {
          w_choices = [];
          break;
        }

        case 'Color': {
          w_choices = ["Red", "Blue", "Green", "Yellow", "Black"];
          break;
        }

        case 'T-Shirt Size': {
          w_choices = ["XS", "S", "M", "L", "XL", "XXL", "XXXL"];
          break;
        }

        case 'Print Size': {
          w_choices = ["A4", "A3", "A2", "A1"];
          break;
        }

        case 'Screen Resolution': {
          w_choices = ["1024x768", "1152x864", "1280x768", "1280x800", "1280x960", "1280x1024", "1366x768", "1440x900", "1600x1200", "1680x1050", "1920x1080", "1920x1200"];
          break;
        }

        case 'Shoe Size': {
          w_choices = ["8", "8.5", "9", "9.5", "10", "10.5", "11", "11.5", "12", "13", "14"];
          break;
        }
      }
      type_add_options(w_choices);
    }
    function delete_options() {
      document.getElementById('options').innerHTML = '';
    }
    function type_add_options(w_choices) {
      i = 0;
      edit_main_td3 = document.getElementById('options');
      n = w_choices.length;
      for (j = 0; j < n; j++) {
        var br = document.createElement('br');
        br.setAttribute("id", "br" + j);
        var el_choices = document.createElement('input');
        el_choices.setAttribute("id", "el_option" + j);
        el_choices.setAttribute("type", "text");
        el_choices.setAttribute("value", w_choices[j]);
        el_choices.style.cssText = "width:100px; margin:0; padding:0; border-width: 1px; vertical-align: middle;";
        var el_choices_remove = document.createElement('img');
        el_choices_remove.setAttribute("id", "el_option" + j + "_remove");
        el_choices_remove.setAttribute("src", "<?php echo WD_FM_URL . '/images/delete.png'; ?>");
        el_choices_remove.style.cssText = 'cursor:pointer; vertical-align:middle; margin:3px';
        el_choices_remove.setAttribute("align", 'top');
        el_choices_remove.setAttribute("onClick", "remove_option(" + j + "," + i + ")");
        edit_main_td3.appendChild(br);
        edit_main_td3.appendChild(el_choices);
        edit_main_td3.appendChild(el_choices_remove);
      }
    }
    function add_choise_option() {
      num = 0;
      j++;
      var choices_td = document.getElementById('options');
      var br = document.createElement('br');
      br.setAttribute("id", "br" + j);
      var el_choices = document.createElement('input');
      el_choices.setAttribute("id", "el_option" + j);
      el_choices.setAttribute("type", "text");
      el_choices.setAttribute("value", "");
      el_choices.style.cssText = "width:100px; margin:0; padding:0; border-width: 1px; vertical-align: middle;";
      var el_choices_remove = document.createElement('img');
      el_choices_remove.setAttribute("id", "el_option" + j + "_remove");
      el_choices_remove.setAttribute("src", "<?php echo WD_FM_URL . '/images/delete.png'; ?>");
      el_choices_remove.style.cssText = 'cursor:pointer; vertical-align:middle; margin:3px';
      el_choices_remove.setAttribute("align", 'top');
      el_choices_remove.setAttribute("onClick", "remove_option('" + j + "','" + num + "')");
      choices_td.appendChild(br);
      choices_td.appendChild(el_choices);
      choices_td.appendChild(el_choices_remove);
    }
    function remove_option(id, num) {
      var choices_td = document.getElementById('options');
      var el_choices = document.getElementById('el_option' + id);
      var el_choices_remove = document.getElementById('el_option' + id + '_remove');
      var br = document.getElementById('br' + id);
      choices_td.removeChild(el_choices);
      choices_td.removeChild(el_choices_remove);
      choices_td.removeChild(br);
    }
    <?php
    if ($property_id != '-1') {
      ?>
      label_ = window.parent.document.getElementById('<?php echo $id; ?>_property_label_form_id_temp<?php echo $property_id; ?>').innerHTML;
      select_ = window.parent.document.getElementById('<?php echo $id; ?>_propertyform_id_temp<?php echo $property_id; ?>');
      n = select_.childNodes.length;
      delete_options();
      w_choices = [ ];
      document.getElementById('option_name').value = label_;
      for (k = 0; k < n; k++) {
        w_choices.push(select_.childNodes[k].value);
      }
      type_add_options(w_choices);
      <?php
    }
    ?>
    </script>
    <?php
    die();
  }

  ////////////////////////////////////////////////////////////////////////////////////////
  // Getters & Setters                                                                  //
  ////////////////////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////////////////////
  // Private Methods                                                                    //
  ////////////////////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////////////////////
  // Listeners                                                                          //
  ////////////////////////////////////////////////////////////////////////////////////////
}