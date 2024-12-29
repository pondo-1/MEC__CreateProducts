<?php

namespace MEC__CreateProducts\Init;

class Taxonomy
{

  // Constructor to set up actions
  public function __construct()
  {
    add_action('admin_menu', [$this, 'remove_product_taxonomies_from_sidebar'], 99);
    add_action('init', [$this, 'disable_default_taxonomies'], 20);
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
}
