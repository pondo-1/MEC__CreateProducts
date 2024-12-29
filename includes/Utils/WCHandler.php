<?php

namespace MEC__CreateProducts\Utils;

use WC_Product_Simple;
use WC_Product_Variable;
use WC_Product_Attribute;
use WC_Product_Variation;


class WCHandler
{
  function wc_create_or_update_product($product_data)
  {
    // Check if SKU is provided
    if (empty($product_data['sku'])) {
      return new WP_Error('missing_sku', 'SKU is required to create or update a product.');
    }

    // Check if the product with this SKU exists
    $product_id = wc_get_product_id_by_sku($product_data['sku']);

    if ($product_id) {
      // If product exists, update it
      $product = new WC_Product_Simple($product_id);
    } else {
      // If product does not exist, create a new one
      $product = new WC_Product_Simple();
    }

    // Set product data
    if (!empty($product_data['name'])) {
      $product->set_name($product_data['name']);
    }

    if (!empty($product_data['regular_price'])) {
      $product->set_regular_price($product_data['regular_price']);
    }

    if (!empty($product_data['description'])) {
      $product->set_description($product_data['description']);
    }

    if (!empty($product_data['short_description'])) {
      $product->set_short_description($product_data['short_description']);
    }

    if (!empty($product_data['stock_quantity'])) {
      $product->set_stock_quantity($product_data['stock_quantity']);
    }

    if (isset($product_data['manage_stock'])) {
      $product->set_manage_stock($product_data['manage_stock']);
    }

    if (!empty($product_data['sku'])) {
      $product->set_sku($product_data['sku']);
    }

    if (!empty($product_data['categories'])) {
      $product->set_category_ids($product_data['categories']);
    }

    if (!empty($product_data['images'])) {
      $product->set_image_id($product_data['images'][0]); // Set the main image
      $product->set_gallery_image_ids(array_slice($product_data['images'], 1)); // Set gallery images
    }

    // Save the product
    $product_id = $product->save();

    return $product_id;
  }

  public static function create_products($wp_CLI_exist = 1, $products_type = 'simple', $num = -1, $start = 0)
  {

    if ($products_type == 'simple') {
      self::create_simple_product($wp_CLI_exist, $num, $start);
    } else if ($products_type == 'variable') {
      self::create_variable_product($num, $start);
    }
  }

  public static function create_variable_product($num, $start)
  {

    $filePath = MEC__CP_API_Data_DIR . 'products_variable_variant.json';
    if (file_exists($filePath)) {
      $products_data = json_decode(file_get_contents($filePath), true);
      Utils::cli_log("$num of variable products will be created:");
      $startpoint = 0;
      $counts = 0;
      foreach ($products_data as $variable_sku => $product_data) {
        $startpoint++;

        // start only 
        if ($start > $startpoint + 1) {
          continue;
        }
        // check if sku already oppcupied
        $productID = wc_get_product_id_by_sku($variable_sku);
        if (!$productID) {

          // Step 1: Create the variable product
          $product = new WC_Product_Variable();
          $product_id = self::set_product_data($variable_sku, $product, $product_data);

          if (isset($product_data['relation']['options'])) {
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

            foreach ($product_data['relation']['options'] as $variant_data) {
              $variation = new WC_Product_Variation();
              $variation->set_parent_id($product_id);

              // Use the attribute slug to link the variation option correctly
              $variation->set_attributes([
                $attribute_slug => $variant_data['option']
              ]);

              $variation->set_sku($variant_data['sku']);
              $variation->set_regular_price($variant_data['price']);
              $variation->set_price($variant_data['price']);
              $variation->set_status('publish');

              $variation->save(); // Save each variation
            }
          } else {
            Utils::cli_log("this variable product has no variant. sku:$variable_sku");
          }
          $counts++;
          Utils::cli_log($counts . "th product created, sku:" . $variable_sku);
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

  public static function create_simple_product($num, $start)
  {
    $counts = 0;
    $filePath = MEC__CP_API_Data_DIR . 'products_simple.json';
    if (file_exists($filePath)) {
      $products_data = json_decode(file_get_contents($filePath), true);
      Utils::cli_log("$num of simple products will be created:");
      foreach ($products_data as $sku => $product_data) {
        $counts++;

        // manage start point
        if ($start > $counts) {
          continue;
        }
        // check if the sku already exist 
        $productID = wc_get_product_id_by_sku($sku);
        if (!$productID) {
          $product = new WC_Product_Simple();
          self::set_product_data($sku, $product, $product_data);
          Utils::cli_log($counts . "th product created, sku:" . $sku);
          if (($num != -1) && ($counts + 1 > $num)) {
            exit;
          }
        } else {
          Utils::cli_log("sku already exist: " . $sku);
          self::update_product_data($productID, $product_data);
        }
      }
    } else {
      // Log an error or handle the missing file case
      Utils::cli_log("Error: 'products_simple.json' file not found at $filePath");
    }
  }

  public static function update_product_data($productID, $product_data)
  {
    $product = wc_get_product($productID);
    if (!$product) {
      Utils::cli_log("Product not found for ID: $productID");
      return;
    }

    // // Update product fields
    // $product->set_name($product_data['name']);
    // $product->set_description($product_data['info']['description']);
    $product->set_regular_price($product_data['price']);
    $product->set_price($product_data['price']);
    // // Update the image if a new one is provided
    // if (isset($product_data['info']['image'])) {
    //   self::set_product_image_from_url($product, $product_data['info']['image']);
    // }

    // Save the updated product
    $product->save();
  }

  public static function set_product_data($sku, $product, $product_data)
  {
    $product->set_name($product_data['name']);
    $product->set_sku($sku);
    $product->set_regular_price($product_data['price']);
    $product->set_price($product_data['price']);
    $product->set_description($product_data['info']['description']);
    $product->set_status('publish');
    $product->set_catalog_visibility('visible');
    // Set the image using the URL
    $image_url = $product_data['info']['image']; // Assuming you have the URL
    self::set_product_image_from_url($product, $image_url);

    // Save the product to get its ID
    $product_id = $product->save();

    return $product_id;
  }
  // Function to download the image from a URL and attach it to the product
  public static function set_product_image_from_url($product, $image_url)
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
