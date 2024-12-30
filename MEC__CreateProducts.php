<?php
/*
Plugin Name: MEC__CreateProducts plugin 
Description: Generates products for MEC Shop, based on the external data(mec.pe-dev.de)
Version: 2.2
Author: Page-effect


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

new MEC__CreateProducts\Init\AdminOptionPage();
new MEC__CreateProducts\Init\CLIcommand();
new MEC__CreateProducts\Init\CustomDataTabel__Vehicle();
new MEC__CreateProducts\Init\Metadata__Compatible();

// Initialize plugin components
function mec__CP_plugin_init()
{
  MEC__CreateProducts\API\LocalJsonToAPI::prepareAPI();
}
add_action('plugins_loaded', 'mec__CP_plugin_init');



// Shortcode
new MEC__CreateProducts\Utils\Compatible();

// disable woocommerce cashe for dev purpose 
add_filter('woocommerce_cache_enabled', '__return_false');
