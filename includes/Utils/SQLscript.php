<?php

namespace MEC__CreateProducts\Utils;

global $MEC__CP_log;

class SQLscript
{
  // Delete all products and related lookup datas
  public function delete_all_products()
  {
    global $wpdb;

    // Step 1: Get product IDs and variation IDs
    $product_ids = $wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE post_type IN ('product', 'product_variation')");

    if (!empty($product_ids)) {
      $product_ids_str = implode(',', array_map('intval', $product_ids));

      // Step 2: Delete from wp_posts (products and variations)
      $wpdb->query("DELETE FROM {$wpdb->posts} WHERE ID IN ($product_ids_str)");

      // Step 3: Delete related post meta
      $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE post_id IN ($product_ids_str)");

      // Step 4: Delete from WooCommerce specific tables

      // Delete from WooCommerce product lookup table
      $wpdb->query("DELETE FROM {$wpdb->prefix}wc_product_meta_lookup WHERE product_id IN ($product_ids_str)");

      // Delete term relationships (categories, tags, attributes, etc.)
      $wpdb->query("DELETE FROM {$wpdb->term_relationships} WHERE object_id IN ($product_ids_str)");

      // Delete from custom tables related to WooCommerce (replace with actual table names if different)
      // Example: delete from product-related tables you may have added

      // Step 5: Delete associated media attachments (thumbnails, featured images, etc.)
      // Get IDs of media attachments related to products
      $attachment_ids = $wpdb->get_col("
        SELECT ID FROM {$wpdb->posts} 
        WHERE post_type = 'attachment' AND post_parent IN ($product_ids_str)
    ");

      if (!empty($attachment_ids)) {
        $attachment_ids_str = implode(',', array_map('intval', $attachment_ids));

        // Delete attachment posts
        $wpdb->query("DELETE FROM {$wpdb->posts} WHERE ID IN ($attachment_ids_str)");

        // Delete attachment post meta
        $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE post_id IN ($attachment_ids_str)");
      }
    }
  }
}
