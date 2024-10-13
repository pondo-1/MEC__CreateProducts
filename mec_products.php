<?php
/*
Plugin Name: MEC_PRODUCTS
Description: Import Products from csv file
Version: 0.1
Author: Page-effect
*/


if (!defined('ABSPATH')) die('No direct access allowed');

define('MECPRODUCTS_DIR', dirname(__FILE__));
define('MECPRODUCTS_URL', plugins_url('', __FILE__));
define('MECPRODUCTS_PLUGIN_SLUG', plugin_basename(__FILE__));


require_once(MECPRODUCTS_DIR . '/' . 'class_MEC_Products.php');
$instance_MEC_Products = new MEC_Products();
