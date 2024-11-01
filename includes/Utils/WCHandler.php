<?php

namespace MEC__CreateProducts\Utils;

use WC_Product_Simple;
use WC_Product_Variable;
use WC_Product_Attribute;
use WC_Product_Variation;

// use WP_CLI;

class WCHandler
{
  public function __construct() {}


  function create_products($wp_CLI_exist = 1, $products_type = 'simple', $num = -1, $start = 0)
  {

    if ($products_type == 'simple') {
      $this->create_simple_product($num, $start);
    } else if ($products_type == 'variable') {
      $this->create_variable_product($num, $start);
    }
  }

  function create_variable_product($num, $start)
  {
    $counts = 0;
    $filePath = MEC__CP_API_Data_DIR . 'products_variable_variant.json';
    if (file_exists($filePath)) {
      $products_data = json_decode(file_get_contents($filePath), true);
      foreach ($products_data as $variable_sku => $product_data) {
        $counts++;
        if ($start > $counts + 1) {
          continue;
        }
        // check if sku already oppcupied
        $productID = wc_get_product_id_by_sku($variable_sku);
        if (!$productID) {

          // Step 1: Create the variable product
          $product = new WC_Product_Variable();
          $product_id = $this->set_product_data($variable_sku, $product, $product_data);

          // Step 2: Define and set attribute for the variable product
          $attribute = new WC_Product_Attribute();
          $attribute_name = $product_data['relation'][2]; // Attribute name, e.g., "Kolbenmaß (mm)"
          $attribute_options = array_column($product_data['relation']['options'], 'option');

          // WooCommerce expects attribute names to be lowercase, no spaces
          $attribute_slug = sanitize_title($attribute_name);
          $attribute->set_name($attribute_name);
          $attribute->set_options($attribute_options);
          $attribute->set_position(0);
          $attribute->set_visible(true);
          $attribute->set_variation(true);
          $product->set_attributes([$attribute]);

          // Step 3: Set default attribute
          // $default_attributes = [];
          // foreach ($product_data['relation']['options'] as $variant_data) {
          //   if (strpos($variant_data['option'], '(Standard)') !== false) {
          //     $default_attributes[$product_data['relation'][2]] = $variant_data['option'];
          //   }
          // }
          // $product->set_default_attributes($default_attributes);

          // Step 4: Add variations to the variable product
          foreach ($product_data['relation']['options'] as $variant_data) {
            $variation = new WC_Product_Variation();
            $variation->set_parent_id($product_id);

            // Use the attribute slug to link the variation option correctly
            $variation->set_attributes([
              $attribute_slug => $variant_data['option']
            ]);

            $variation->set_sku($variant_data['sku']);
            $variation->set_price($variant_data['price']);
            $variation->set_regular_price($variant_data['price']);
            $variation->set_status('publish');

            $variation->save(); // Save each variation
          }

          // Final Save for the variable product to update WooCommerce with variations
          $product->save();

          if (($num != -1) && ($counts + 1 > $num)) {
            exit;
          }
        } else {
          Utils::putLog("sku already exist: " . $variable_sku);
        }
      }
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
    // Convert Baujahr terms to strings
    $baujahr_terms = array_map('strval', $product_data['compatible']['Baujahr']);
    // Set the terms for 'baujahr' taxonomy
    wp_set_object_terms($product_id, $baujahr_terms, 'baujahr');

    Utils::putLog('set the product: ' . $sku);
    return $product_id;
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
