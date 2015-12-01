<?php

class FMViewCheckpaypal {
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
    $id = ((isset($_GET['form_id'])) ? esc_html(stripslashes($_GET['form_id'])) : 0);
    $group_id = ((isset($_GET['group_id'])) ? esc_html(stripslashes($_GET['group_id'])) : 0);
    $form = $this->model->get_form_data($id);
    $row = $this->model->get_form_session_data($group_id);
    // $File = "request.php";
    // $Handle = fopen($File, 'c+');
    global $wpdb;
    $req = date( 'Y-m-d H:i:s' ) . "----" . $_SERVER['HTTP_REFERER'] . "----" . $_SERVER['REMOTE_ADDR'] . "
      ";
    foreach ($_SERVER as $key => $value) {
      $req .= $key . "----" . $value . "
    ";
    }
    foreach ($_REQUEST as $key => $value) {
      $req .= $key . "----" . $value . "
    ";
    }
    if ($form->checkout_mode == "production") {
      $paypal_action = "https://www.paypal.com/cgi-bin/webscr";
    }
    else {
      $paypal_action = "https://www.sandbox.paypal.com/cgi-bin/webscr";
    }
    $rep = ""; 
    $postdata = "";
    foreach ($_POST as $key => $value) {
      $postdata .= $key . "=" . urlencode($value) . "&";
    }
    $postdata .= "cmd=_notify-validate";
    $curl = curl_init($paypal_action);
    curl_setopt ($curl, CURLOPT_HEADER, 0); 
    curl_setopt ($curl, CURLOPT_POST, 1); 
    curl_setopt ($curl, CURLOPT_POSTFIELDS, $postdata); 
    curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, 0); 
    curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt ($curl, CURLOPT_SSL_VERIFYHOST, 1); 
    $response = curl_exec($curl); 
    curl_close($curl);
    $payment_status = esc_html($_POST['payment_status']);
    $option = $_POST['option'];
    $total = $_POST['mc_gross'];
    $tax_total = $_POST['tax'];
    $shipping_total	= $_POST['mc_shipping'];
    $refresh = 0;
    $tax = 0;
    $shipping = 0;
    $total_cost = 0;
    $total_count = 0;
    $form_currency = '$';
    $currency_code = array('USD', 'EUR', 'GBP', 'JPY', 'CAD', 'MXN', 'HKD', 'HUF', 'NOK', 'NZD', 'SGD', 'SEK', 'PLN', 'AUD', 'DKK', 'CHF', 'CZK', 'ILS', 'BRL', 'TWD', 'MYR', 'PHP', 'THB');
    $currency_sign = array('$', '&#8364;', '&#163;', '&#165;', 'C$', 'Mex$', 'HK$', 'Ft', 'kr', 'NZ$', 'S$', 'kr', 'zl', 'A$', 'kr', 'CHF', 'Kc', '&#8362;', 'R$', 'NT$', 'RM', '&#8369;', '&#xe3f;');
    if ($form->payment_currency) {
      $form_currency = $currency_sign[array_search($form->payment_currency, $currency_code)];
    }
    $tax = $form->tax;
    $shipping = $_POST['mc_shipping'];

    $this->model->insert_submission($payment_status, $group_id);

    $address = "Country: " . $_POST['address_country'] . "<br>";
    $address .= ((isset($_POST['address_state'])) ? "State: " . $_POST['address_state'] . "<br>" : '');
    $address .= ((isset($_POST['address_city'])) ? "City: " . $_POST['address_city'] . "<br>" : '');
    $address .= ((isset($_POST['address_street'])) ? "Street: " . $_POST['address_street'] . "<br>" : '');
    $address .= ((isset($_POST['address_zip'])) ? "Zip Code: " . $_POST['address_zip'] . "<br>" : '');
    $address .= ((isset($_POST['address_status'])) ? "Address Status: " . $_POST['address_status'] . "<br>" : '');
    $address .= ((isset($_POST['address_name'])) ? "Name: " . $_POST['address_name'] . "<br>" : '');
    $paypal_info = "";
    $paypal_info .= ((isset($_POST['payer_status'])) ? "Payer Status - " . $_POST['payer_status'] . "<br>" : '');
    $paypal_info .= ((isset($_POST['payer_email'])) ? "Payer Email - " . $_POST['payer_email'] . "<br>" : '');
    $paypal_info .= ((isset($_POST['txn_id'])) ? "Transaction - " . $_POST['txn_id'] . "<br>" : '');
    $paypal_info .= ((isset($_POST['payment_type'])) ? "Payment Type - " . $_POST['payment_type'] . "<br>" : '');
    $paypal_info .= ((isset($_POST['residence_country'])) ? "Residence Country - " . $_POST['residence_country'] . "<br>" : '');
    if (!$row) {
      $wpdb->insert($wpdb->prefix . "formmaker_sessions", array(
        'form_id' => $id,
        'group_id' => $group_id,
        'full_name' => $_POST['first_name'] . " " . $_POST['last_name'],
        'email' => $_POST['payer_email'],
        'phone' => $_POST['night_ phone_a']." - ".$_POST['night_ phone_b']." - ".$_POST['night_ phone_c'],
        'address' => $address,
        'status' => $_POST['payment_status'],
        'ipn' => $response,
        'currency' => $form->payment_currency . ' - ' . $form_currency,
        'paypal_info' => $paypal_info,
        'ord_last_modified' => date('Y-m-d H:i:s'),
        'tax' => $tax,
        'shipping' => $shipping,
        'total' => $total
      ), array(
        '%d',
        '%d',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s'
      ));
    }
    else {
      $wpdb->update($wpdb->prefix . "formmaker_sessions", array(
        'form_id' => $id,
        'full_name' => $_POST['first_name'] . " " . $_POST['last_name'],
        'email' => $_POST['payer_email'],
        'phone' => $_POST['night_ phone_a'] . " - " . $_POST['night_ phone_b'] . " - " . $_POST['night_ phone_c'],
        'address' => $address,
        'status' => $_POST['payment_status'],
        'ipn' => $response,
        'currency' => $form->payment_currency . ' - ' . $form_currency,
        'paypal_info' => $paypal_info,
        'ord_last_modified' => date('Y-m-d H:i:s'),
        'tax' => $tax,
        'shipping' => $shipping,
        'total' => $total
      ), array(
        'group_id' => $group_id
      ), array(
        '%d',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s'
      ), array(
        '%d'
      ));
    }
    $row = $this->model->get_form_session_data($group_id);
    if (!$row) {
      ?>
      <script>
        alert('Error');
        window.history.go(-1);
      </script>
      <?php
      exit();
    }
    $list = '
    <table class="admintable" border="1" >
      <tr>
        <td class="key">Currency</td>
        <td> ' . $row->currency . '</td>
      </tr>
      <tr>
        <td class="key">Date</td>
        <td> ' . $row->ord_last_modified . '</td>
      </tr>
      <tr>
        <td class="key">Status</td>
        <td> ' . $row->status . '</td>
      </tr>
      <tr>
        <td class="key">Full name</td>
        <td> ' . $row->full_name . '</td>
      </tr>
      <tr>
        <td class="key">Email</td>
        <td> ' . $row->email . '</td>
      </tr>
      <tr>
        <td class="key">Phone</td>
        <td> ' . $row->phone . '</td>
      </tr>
      <tr>
        <td class="key">Mobile phone</td>
        <td> ' . $row->mobile_phone . '</td>
      </tr>
      <tr>
        <td class="key">Fax</td>
        <td> ' . $row->fax . '</td>
      </tr>
      <tr>
        <td class="key">Address</td>
        <td> ' . $row->address . '</td>
      </tr>
      <tr>
        <td class="key">PayPal info</td>
        <td> ' . $row->paypal_info . '</td>
      </tr>	
      <tr>
        <td class="key">IPN</td>
        <td> ' . $row->ipn . '</td>
      </tr>
      <tr>
        <td class="key">tax</td>
        <td> ' . $row->tax . '%</td>
      </tr>
      <tr>
        <td class="key">shipping</td>
        <td> ' . $row->shipping . '</td>
      </tr>
      <tr>
        <td class="key">read</td>
        <td> ' . $row->read . '</td>
      </tr>
      <tr>
        <td class="key"><b>Item total</b></td>
        <td> ' . ($total - $tax_total - $shipping_total) . $form_currency . '</td>
      </tr>
      <tr>
        <td class="key"><b>Tax</b></td>
        <td> ' . $tax_total . $form_currency . '</td>
      </tr>
      <tr>
        <td class="key"><b>Shipping and handling</b></td>
        <td> ' . $shipping_total . $form_currency . '</td>
      </tr>
      <tr>
        <td class="key"><b>Total</b></td>
        <td> ' . $total . $form_currency . '</td>
      </tr>
    </table>';
    if ($form->mail) {
      $recipient = $form->mail;
      $subject = "Payment information";
      $body = wordwrap($list, 70, "\n", TRUE);
      add_filter('wp_mail_content_type', create_function('', 'return "text/html";'));
      $send = wp_mail($recipient, $subject, stripslashes($body), '', '');
    }
    return 0;
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