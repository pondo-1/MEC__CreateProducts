<?php

namespace MEC__CreateProducts\Utils;

use WC_Product_Simple;
// use WP_CLI;

class WCHandler
{
  public function __construct() {}


  function create_products($wp_CLI_exist = 1, $products_type = 'simple', $num = -1, $start = 0)
  {

    if ($products_type == 'simple') {
      $this->create_simple_product($num, $start);
    } else if ($products_type == 'simple') {
      $this->create_variable_product($num, $start);
    }
  }

  function create_variable_product($num, $start)
  {
    $counts = 0;
    $filePath_variable = MEC__CP_API_Data_DIR . 'products_variable_and_variant.json';
    if (file_exists($filePath_variable)) {
    }
  }

  function create_simple_product($num, $start)
  {
    $counts = 0;
    $filePath = MEC__CP_API_Data_DIR . 'products_single.json';
    if (file_exists($filePath)) {
      $products_data = json_decode(file_get_contents($filePath), true);
      foreach ($products_data as $sku => $product_data) {
        $counts++;
        if ($start > $counts + 1) {
          continue;
        }
        $productID = wc_get_product_id_by_sku($sku);
        if (!$productID) {
          // that's CRUD object
          $product = new WC_Product_Simple();
          $this->set_product_data($sku, $product, $product_data);
          // if ($wp_CLI_exist) {
          //   WP_CLI::log("Processed product number: $counts");
          // }
          if (($num != -1) && ($counts + 1 > $num)) {
            exit;
          }
        } else {
          Utils::putLog("sku already exist: " . $sku);
        }
      }
    } else {
      // Log an error or handle the missing file case
      Utils::putLog("Error: 'products_single.json' file not found at $filePath");
    }
  }

  function set_product_data($sku, $product, $product_data)
  {
    $product->set_name($product_data['name']);
    $product->set_sku($sku);
    $product->set_description($product_data['info']['description']);
    $product->set_status('publish');
    $product->set_catalog_visibility('visible');
    // Set the image using the URL
    $image_url = $product_data['info']['image']; // Assuming you have the URL
    $this->set_product_image_from_url($product, $image_url);

    // Save the product to get its ID
    $product_id = $product->save();

    // Set custom taxonomy terms
    wp_set_object_terms($product_id, $product_data['compatible']['Typ'], 'typ');
    wp_set_object_terms($product_id, $product_data['compatible']['Marke'], 'marke');
    wp_set_object_terms($product_id, $product_data['compatible']['Modell'], 'modell');
    wp_set_object_terms($product_id, $product_data['compatible']['Hubraum'], 'hubraum');
    wp_set_object_terms($product_id, $product_data['compatible']['Baujahr'], 'baujahr');


    Utils::putLog('simple Product created: ' . $sku);
  }
  // Function to download the image from a URL and attach it to the product
  function set_product_image_from_url($product, $image_url)
  {
    // Check if the URL is valid and download the image
    $image_id = media_sideload_image($image_url, 0, null, 'id');

    if (is_wp_error($image_id)) {
      // Handle the error, maybe log it for debugging
      error_log('Failed to download image: ' . $image_url);
      return false;
    }

    // Set the downloaded image as the product's featured image
    $product->set_image_id($image_id);
    return true;
  }
}
