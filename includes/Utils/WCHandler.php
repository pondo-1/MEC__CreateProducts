<?php

namespace MEC__CreateProducts\Utils;

use WC_Product_Simple;
use WC_Product_Variable;
use WC_Product_Attribute;
use WC_Product_Variation;


class WCHandler
{
  public static function wc_create_or_update_product($product_type = 'simple', $sku, $product_data)
  {
    // Check if SKU is provided
    if ($sku == "") {
      return new WP_Error('missing_sku', 'SKU is required to create or update a product.');
    }

    // Check if the product with this SKU exists
    $product_id = wc_get_product_id_by_sku($sku);
    switch ($product_type) {
      case 'variable':
        if ($product_id) {
          // If product exists, update it
          $product = new WC_Product_Variable($product_id);
        } else {
          // If product does not exist, create a new one
          $product = new WC_Product_Variable();
        }
        // set attribution
        self::wc_create_or_update_attribute_variant($product, $product_data['relation']);
        break;
      case 'variant':
        if ($product_id) {
          // If product exists, update it
          $product = new WC_Product_Variation($product_id);
        } else {
          // If product does not exist, create a new one
          $product = new  WC_Product_Variation();
        }
        break;
      case 'simple':
        if ($product_id) {
          // If product exists, update it
          $product = new WC_Product_Simple($product_id);
        } else {
          // If product does not exist, create a new one
          $product = new WC_Product_Simple();
        }
        break;

      default:
        new WP_Error('undefined_product_type', 'Product type undefined');
        break;
    }

    // Set Product basic data: which is apply all types of products


    // Set product data
    $product->set_sku($sku);

    if (!empty($product_data['name'])) {
      $product->set_name($product_data['name']);
    }

    if (!empty($product_data['price'])) {
      $product->set_regular_price($product_data['price']);
      $product->set_price($product_data['price']);
    }

    if (!empty($product_data['info']['description'])) {
      $product->set_description($product_data['info']['description']);
    }

    // if (!empty($product_data['stock_quantity'])) {
    //   $product->set_stock_quantity($product_data['stock_quantity']);
    // }

    // if (isset($product_data['manage_stock'])) {
    //   $product->set_manage_stock($product_data['manage_stock']);
    // }

    if (!empty($product_data['info']['image'])) {
      self::set_product_image_from_url($product, $product_data['info']['image']);
    }

    // Save the product
    $product_id = $product->save();

    return $product_id;
  }


  public static function wc_create_or_update_attribute_variant($product, $attribute_data)
  {
    $product_id = get_the_ID($product);
    if (isset($attribute_data['options'])) {
      // Step 2: Define and set attribute for the variable product
      $attribute = new WC_Product_Attribute();
      $attribute_name = $attribute_data[2]; // Attribute name, e.g., "Kolbenmaß (mm)"
      $attribute_options = array_column($attribute_data['options'], 'option');

      // WooCommerce expects attribute names to be lowercase, no spaces
      $attribute_slug = sanitize_title($attribute_name);
      $existing_attribute = wc_get_attribute($attribute_slug);

      if (!$existing_attribute) {
        // If the attribute does not exist, create it
        $attribute = new WC_Product_Attribute();
        $attribute->set_name($attribute_name);
        $attribute->set_options($attribute_options);
        $attribute->set_position(0);
        $attribute->set_visible(true);
        $attribute->set_variation(true);
        $product->set_attributes([$attribute]);
      } else {
        // Handle the case where the attribute already exists
        Utils::cli_log("Attribute '$attribute_name' already exists.");
      }

      foreach ($attribute_data['options'] as $variant_data) {
        // Check if the variation already exists
        $existing_variation_id = wc_get_product_id_by_sku($variant_data['sku']);

        if (!$existing_variation_id) {
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

        } else {
          // Retrieve the existing variation object
          $variation = wc_get_product($existing_variation_id);
          // Update the price
          $variation->set_regular_price($variant_data['price']);
          $variation->set_price($variant_data['price']);
          $variation->save(); // Save the updated variation
          Utils::cli_log("Variation with SKU '{$variant_data['sku']}' already exists. Price updated.");
          continue; // Check the next variation in the loop        
        }
      }
    } else {
      Utils::cli_log("this variable product has no variant. sku:" . $product_id);
    }
  }

  public static function create_products_from_json($wp_CLI_exist = 1, $products_type = 'simple', $num = -1, $start = 0)
  {
    $counts = 0;
    switch ($products_type) {
      case 'simple':
        $filePath = MEC__CP_API_Data_DIR . 'products_simple.json';
        break;

      case 'variable':
        $filePath = MEC__CP_API_Data_DIR . 'products_variable_variant.json';
        break;

      default:
        new WP_Error('undefined_product_type', 'Product type undefined');
        break;
    }

    if (file_exists($filePath)) {
      $products_data = json_decode(file_get_contents($filePath), true);
      Utils::cli_log("$num of $products_type products will be created:");
      $startpoint = 0;
      $counts = 0;
      foreach ($products_data as $sku => $product_data) {
        $startpoint++;
        // start only 
        if ($start > $startpoint + 1) {
          continue;
        }
        // create or update product
        $productID = self::wc_create_or_update_product($products_type, $sku, $product_data);
        $counts++;
        Utils::cli_log($counts . "th product created/updated, sku:" . $sku);
        // Final Save for the variable product to update WooCommerce with variations

        if (($num != -1) && ($counts + 1 > $num)) {
          exit;
        }
      }
    }
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
