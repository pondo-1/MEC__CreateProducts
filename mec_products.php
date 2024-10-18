<?php
/*
Plugin Name: MEC__CreateProducts plugin 
Description: Generates products for MEC Shop, based on the external data(mec.pe-dev.de)
Version: 1.1
Author: Page-effect

/wp-content/plugins/MEC__CreateProducts
    /assets
        /js
            process-display.js
    /includes
        /Admin
            AdminPage.php
        /API
            APIHandler.php
        /Product
            ProductGenerator.php
        /Log
            Logger.php
    MEC__CreateProducts.php

*/


if (!defined('ABSPATH')) die('No direct access allowed');

define('MEC__CP_DIR', dirname(__FILE__));
define('MEC__CP_URL', plugins_url('', __FILE__));
define('MEC__CP_PLUGIN_SLUG', plugin_basename(__FILE__));

// Autoload classes
spl_autoload_register(function ($class_name) {
  $namespace = 'MEC__CreateProducts\\';
  if (strpos($class_name, $namespace) !== false) {
    $class_name = str_replace($namespace, '', $class_name);
    $file = plugin_dir_path(__FILE__) . 'includes/' . str_replace('\\', '/', $class_name) . '.php';
    if (file_exists($file)) {
      require_once $file;
    } else {
      error_log("Failed to load file??: " . $file);
    }
  }
});

// Initialize Logger as a global variable
global $my_plugin_logger;
$my_plugin_logger = new \MEC__CreateProducts\Log\Logger('product-import-log.txt');



// log: Set global log instance for this plugin
//require_once(MEC__CP_DIR . '/' . 'class_MEC_Products.php');
//$instance_MEC_Products = new MEC_Products();

//Log 
//log class
require_once MEC__CP_DIR . '/Log/logger.class.php';
require_once(MEC__CP_DIR . '/' . 'class_MEC_Products.php');
$instance_MEC_Products = new MEC_Products();
