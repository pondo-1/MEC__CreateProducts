<?php
/*
Plugin Name: MEC__CreateProducts plugin 
Description: Generates products for MEC Shop, based on the external data(mec.pe-dev.de)
Version: 2.2
Author: Page-effect

ssh u131-bxrrihxoclkj@ssh.final-mec.pe-dev.de -p18765


/wp-content/plugins/MEC__CreateProducts
    /assets
        /js
            process-display.js
    /includes
        /Admin
              // This is for Admin option page
            AdminPage.php
        /API
              // Get Products data from mec.pe-dev.de(WC instance 1) and save the file  /includes/API/products_all.json
            SaveToLocal.php
            
              // seperate products from products_all.json by products type(single, variable, variant and extra) and save it prodcut_type.json
              // variable   -> freifeld6 has '-M'
              // variant    -> freifeld6 has variable product's SKU
              // single     -> freifeld6 has neither '-M' nor 'variable product's SKU'
              // extra      -> the products that does not satisfy any condition for products types  
            PrepareJsonLocal.php
            
              //Register Endpoints
              // /wp-json/mec-api/v1/products/product_all
              // /wp-json/mec-api/v1/products/product_variable
              // /wp-json/mec-api/v1/products/product_variant
              // /wp-json/mec-api/v1/products/product_single
              // /wp-json/mec-api/v1/products/product_extra
            LocalJsonToAPI.php

        /Log
              // Logger Class, that used in Utils/Utils.php
            Logger.php

        /Utils
              //The purpose of the Utils class in this code is to provide a convenient, 
              //centralized way to access a shared Logger instance 
              //across different parts of the codebase without needing to create multiple Logger instances.
            Utils.php
              // This is helper class to register and generate Buttons in Admin Option page
            AdminButton.php
            
              // SQL script for the data processing
            SQLscript.php
        /WPquery // Data verarbeitung 
            

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
define('MEC__CP_API_Data_DIR', dirname(__FILE__) . '/includes/API/');  // ../public/wp-content/plugins/MEC__CreateProducts/API/
global $MEC__CP_log;
global $MEC__CP_json_products_all;

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


$MEC__CP_log = MEC__CreateProducts\Utils\Utils::getLogger();

// Initialize plugin components
function mec__CP_plugin_init()
{
  MEC__CreateProducts\API\LocalJsonToAPI::prepareAPI();
}
add_action('plugins_loaded', 'mec__CP_plugin_init');

new MEC__CreateProducts\Admin\AdminPage();
// Instantiate the Taxonomy class
new MEC__CreateProducts\Init\Taxonomy();

new MEC__CreateProducts\Init\CLIcommand();


// disable woocommerce cashe for dev purpose 
add_filter('woocommerce_cache_enabled', '__return_false');
