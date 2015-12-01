<?php

class FMModelUninstall_fm {
  ////////////////////////////////////////////////////////////////////////////////////////
  // Events                                                                             //
  ////////////////////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////////////////////
  // Constants                                                                          //
  ////////////////////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////////////////////
  // Variables                                                                          //
  ////////////////////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////////////////////
  // Constructor & Destructor                                                           //
  ////////////////////////////////////////////////////////////////////////////////////////
  public function __construct() {
  }
  ////////////////////////////////////////////////////////////////////////////////////////
  // Public Methods                                                                     //
  ////////////////////////////////////////////////////////////////////////////////////////
  public function delete_db_tables() {
    delete_option("wd_form_maker_version");
    delete_option('formmaker_cureent_version');
    delete_option('contact_form_themes');
    delete_option('contact_form_forms');
    delete_option('form_maker_pro_active');
    delete_option('fm_emailverification');
    global $wpdb;
    $wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "formmaker");
    $wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "formmaker_submits");
    $wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "formmaker_views");
    $wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "formmaker_themes");
    $wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "formmaker_sessions");
    $wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "formmaker_blocked");
    $wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "formmaker_query");
    $wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "formmaker_backup");
    $wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "formmaker_mailchimp");
    $wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "formmaker_reg");
    $wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "formmaker_post_gen_options");
    $wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "formmaker_email_conditions");
    $wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "formmaker_dbox_int");
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