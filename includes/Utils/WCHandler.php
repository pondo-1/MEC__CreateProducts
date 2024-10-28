<?php

namespace MEC__CreateProducts\Utils;

use WC_Product_Simple;
// use WP_CLI;

class WCHandler
{
  public function __construct() {}


  function create_products($wp_CLI_exist = 1, $products_data, $num = 0, $start = 0)
  {
    global $MEC__CP_log;
    if (empty($products_data)) {
      return rest_ensure_response(array('error' => 'No products found.'));
    }
    $counts = 0;
    foreach ($products_data as $sku => $product) {
      $counts++;
      if ($start > $counts + 1) {
        continue;
      }
      $productID = wc_get_product_id_by_sku($sku);
      if (!$productID) {

        $this->create_wc_simple_product($sku, $product);
        if ($wp_CLI_exist) {
          // WP_CLI::log("Processed product number: $counts");
        }

        if ($counts + 1 > $num) {
          exit;
        }
      } else {
        Utils::putLog("following sku already exist: " . $sku);
      }
      // variable product
      // check if sku already used
      // $productID = wc_get_product_id_by_sku($product['sku']);
      // if (!$productID) {
      //   if ($product['products_type'] == 'Variable') {
      //     $parent_id = $this->create_wc_variable_product($product);
      //     if ($wp_CLI_exist) {
      //       WP_CLI::log("Processed product number: $counts");
      //     }
      //   }
      //   if ($counts + 1 > $num) {
      //     exit;
      //   }
      // } else {
      //   Utils::putLog("following sku already exist: " . $product['sku']);
      // }
    }
  }
  function create_wc_simple_product($sku, $product_data)
  {
    // that's CRUD object
    $product = new WC_Product_Simple();
    $product->set_name($product_data['name']);
    $product->set_sku($sku);
    $product->set_description($product_data['info']['description']);
    $product->set_status('publish');
    $product->set_catalog_visibility('visible');
    // Set the image using the URL
    $image_url = $product_data['info']['image']; // Assuming you have the URL
    $this->set_product_image_from_url($product, $image_url);
    Utils::putLog('Product' . $sku . 'generiert');

    // Save the variable product and get its ID
    $parent_id = $product->save();
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
