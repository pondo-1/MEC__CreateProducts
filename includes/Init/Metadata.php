<?php

namespace MEC__CreateProducts\Init;

// disable the defulat taxonomie 
// add metadata "compatible"

class Metadata
{

  // Constructor to set up actions
  public function __construct()
  {
    add_action('admin_menu', [$this, 'remove_product_taxonomies_from_sidebar'], 99);
    add_action('init', [$this, 'disable_default_taxonomies'], 20);
    // Hook to add the meta box
    add_action('add_meta_boxes', [$this, 'add_compatible_meta_box']);
    // Hook to save the meta box data
    // add_action('save_post', [$this, 'save_compatible_meta_box']);
  }

  // Removes categories and tags from the sidebar
  public function remove_product_taxonomies_from_sidebar()
  {
    remove_submenu_page('edit.php?post_type=product', 'edit-tags.php?taxonomy=product_cat&post_type=product'); // Remove Categories
    remove_submenu_page('edit.php?post_type=product', 'edit-tags.php?taxonomy=product_tag&post_type=product'); // Remove Tags
  }

  // Unregister default WooCommerce categories and tags for products
  public function disable_default_taxonomies()
  {
    if (post_type_exists('product')) { // Ensures WooCommerce is loaded
      unregister_taxonomy_for_object_type('product_cat', 'product'); // Remove WooCommerce product categories
      unregister_taxonomy_for_object_type('product_tag', 'product'); // Remove WooCommerce product tags
    }
  }

  // Add a custom meta box to the product edit page
  public static function add_compatible_meta_box()
  {
    add_meta_box(
      'compatible_meta_box', // ID
      'Compatible', // Title
      [__CLASS__, 'render_compatible_meta_box'], // Callback
      'product', // Post type
      'side', // Context
      'default' // Priority
    );
  }

  // Render the custom meta box
  public static function render_compatible_meta_box($post)
  {
    $compatible = get_post_meta($post->ID, 'compatible', true);

    // Check if $compatible is an array
    if (is_array($compatible)) {
      foreach ($compatible as $value) {
        echo esc_html($value) . '<br>'; // Use <br> for line breaks
      }
    } else {
      echo esc_html($compatible); // Handle case where it's not an array
    }
  } 
}
