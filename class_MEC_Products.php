<?php
class MEC_Products
{

  public function __construct()
  {
    // Create admin page
    require_once(MECPRODUCTS_DIR . '/' . 'class_admin_custom_button.php');
    new admin_custom_button();
  }
};
