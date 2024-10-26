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
        /Utils
            Utils.php
        /Admin
            AdminPage.php
        /API
            APIHandler.php 
        /Product
            ProductGenerator.php
        /Log
            Logger.php
    MEC__CreateProducts.php


    Eingabe
    Vorarbeitung
    Ausgabe

    user-interface (view), 
    data (model),
    application logic (controller)
    
    */


if (!defined('ABSPATH')) die('No direct access allowed');

define('MEC__CP_DIR', dirname(__FILE__)); // ..../public/wp-content/plugins/MEC__CreateProducts
define('MEC__CP_URL', plugins_url('', __FILE__));
define('MEC__CP_PLUGIN_SLUG', plugin_basename(__FILE__));
define('MEC__CP_APIURL', '/wp-json/mec-api/v1/products/');
define('MEC__CP_API_Data_DIR', __DIR__ . DIRECTORY_SEPARATOR . 'API');  // ../public/wp-content/plugins/MEC__CreateProducts/API

// Autoload classes
spl_autoload_register(function ($class_name) {
  $namespace = 'MEC__CreateProducts\\';
  if (strpos($class_name, $namespace) !== false) {
    $class_name = str_replace($namespace, '', $class_name);
    $file = plugin_dir_path(__FILE__) . 'includes/' . str_replace('\\', '/', $class_name) . '.php';
    if (file_exists($file)) {
      require_once $file;
    } else {
      error_log("Failed to load file: " . $file);
    }
  }
});
$log = MEC__CreateProducts\Utils\Utils::getLogger();
$log->putLog('MEC__CP_DIR: ' . MEC__CP_DIR);
$log->putLog('MEC__CP_API_Data_DIR: ' . MEC__CP_API_Data_DIR);

// Initialize plugin components
function mec_create_products_plugin_init()
{

  // Initialize Admin Page (Register menu and handle admin actions)
  new  MEC__CreateProducts\Admin\AdminPage();

  //API Verbereitung: Save the Info in Json in Plugin directory. Total, Single, Variable, Variant, Variable with variant, Extra  
}
add_action('plugins_loaded', 'mec_create_products_plugin_init');
