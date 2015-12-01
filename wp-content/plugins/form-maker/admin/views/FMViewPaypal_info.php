<?php

class FMViewPaypal_info {
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
    $id = ((isset($_GET['id'])) ? esc_html(stripslashes($_GET['id'])) : 0);
    $row = $this->model->get_form_session($id);
    if (!isset($row->ipn)) {
      ?>
      <div style="width:100%; text-align: center; height: 70%; vertical-align: middle;">
        <h1 style="vertical-align: middle; margin: auto; color: #000"><p>No information yet</p></h1>
      </div>
      <?php
    }
    else {
      ?>
      <style>
        table.admintable td.key, table.admintable td.paramlist_key {
          background-color: #F6F6F6;
          border-bottom: 1px solid #E9E9E9;
          border-right: 1px solid #E9E9E9;
          color: #666666;
          font-weight: bold;
          margin-right: 10px;
          text-align: right;
          width: 140px;
        }
      </style>
      <h2>Payment Info</h2>
      <table class="admintable">
        <tr>
          <td class="key">Currency</td>
          <td><?php echo $row->currency; ?></td>
        </tr>
        <tr>
          <td class="key">Last modified</td>
          <td><?php echo $row->ord_last_modified; ?></td>
        </tr>
        <tr>
          <td class="key">Status</td>
          <td><?php echo $row->status; ?></td>
        </tr>
        <tr>
          <td class="key">Full name</td>
          <td><?php echo $row->full_name; ?></td>
        </tr>
        <tr>
          <td class="key">Email</td>
          <td><?php echo $row->email; ?></td>
        </tr>
        <tr>
          <td class="key">Phone</td>
          <td><?php echo $row->phone; ?></td>
        </tr>
        <tr>
          <td class="key">Mobile phone</td>
          <td><?php echo $row->mobile_phone; ?></td>
        </tr>
        <tr>
          <td class="key">Fax</td>
          <td><?php echo $row->fax; ?></td>
        </tr>
        <tr>
          <td class="key">Address</td>
          <td><?php echo $row->address; ?></td>
        </tr>
        <tr>
          <td class="key">Paypal info</td>
          <td><?php echo $row->paypal_info; ?></td>
        </tr>
        <tr>
          <td class="key">IPN</td>
          <td><?php echo $row->ipn; ?></td>
        </tr>
        <tr>
          <td class="key">Tax</td>
          <td><?php echo $row->tax; ?>%</td>
        </tr>
        <tr>
          <td class="key">Shipping</td>
          <td><?php echo $row->shipping; ?></td>
        </tr>
        <tr>
          <td class="key">Read</td>
          <td><?php echo $row->read; ?></td>
        </tr>
        <tr>
          <td class="key">Total</td>
          <td><b><?php echo $row->total; ?></b></td>
        </tr>
      </table>
      <?php
    }
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