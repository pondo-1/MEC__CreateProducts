<?php


use MEC__CreateProducts\Utils\Utils;

class admin_custom_button
{
  private $log;
  public function __construct()
  {
    // Log
    $this->log = Utils::getLogger(); // Access the logger using the utility class


    // self::$log = new PEDipa_Logger(MEC__CP_DIR . '/Log/create_products.txt');
    add_action('admin_menu', [$this, 'add_admin_menu']);

    // Import map marker categories, which is in /assets/markertax
    // add_action('admin_init', [$this, 'handle_import_button']);
    add_action('admin_init', [$this, 'handle_delete_button']);
    add_action('admin_init', [$this, 'handle_products_6_button']);
    add_action('admin_init', [$this, 'handle_all_variable_products']);

    if (defined('WP_CLI') && WP_CLI) {
      WP_CLI::add_command('convert', array($this, 'convert_text_file_to_json'));
      // WP_CLI::add_command('create_products', array($this, 'create_products_from_json'));

      WP_CLI::add_command('delete_all_products', array($this, 'delete_all_products_and_attachments'));
      WP_CLI::add_command('create_products_from_remote', array($this, 'create_products_from_remote_json'));
    }
  }



  public function add_admin_menu()
  {
    add_menu_page(
      'MEC_dev',         // Page title
      'MEC_dev',         // Menu title
      'manage_options',              // Capability
      'MEC_dev',       // Submenu slug
      [$this, 'custom_buttons'],  // Function to display the submenu page content
      '',
      65 // under the plugin 
    );
  }

  public function custom_buttons()
  {
?>
    <div class="wrap">
      <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

      <!-- <form method="post" action="">
        <input type="hidden" name="mp_custom_action" value="generates_main_products">
        <?php //submit_button('generates main products'); 
        ?>
      </form> -->
      <br>
      <form method="post" action="">
        <?php submit_button('Generate only first 6 Products', 'primary', 'generates_6_products'); ?>
        Better with WP CLI than button
        <pre>
        $wp create_products_from_remote --num=6
        </pre>
      </form>
      <br>
      <br>
      <form method="post" action="">
        <?php submit_button('Generate all variable Products with variants', 'primary', 'create_all_variable_products'); ?>
        Better with WP CLI than button
        <pre>
        $wp create_products_from_remote --num=-1
        </pre>
      </form>
      <br>
      <?php
      // Check for product generation progress
      $progress_message = get_transient('product_generation_progress');
      if ($progress_message) {
        echo '<p><strong>' . esc_html($progress_message) . '</strong></p>';
      }
      ?>
      <form method="post" action="">
        <?php submit_button('Delete All Products', 'secondary', 'delete_all_products'); ?>
        Better with WP CLI than button
        <pre>
        $wp delete_all_products
        </pre>
      </form>
      <br>
    </div>
<?php
  }

  public function handle_delete_button()
  {
    if (isset($_POST['delete_all_products'])) {

      $this->delete_all_products_and_attachments("wp_admin");
      add_action('admin_notices', function () {
        echo '<div class="notice notice-warning is-dismissible"><p>All Products and their metadata have been deleted.</p></div>';
      });
    }
  }

  public function handle_products_6_button()
  {
    if (isset($_POST['generates_6_products'])) {
      $assoc_args['num'] = 6;
      $assoc_args['where'] = "wp_admin";
      $this->create_products_from_remote_json(null, $assoc_args);
      add_action('admin_notices', function () {
        echo '<div class="notice notice-warning is-dismissible"><p> done </p></div>';
      });
    }
  }

  public function   handle_all_variable_products()
  {
    if (isset($_POST['create_all_variable_products'])) {
      $assoc_args['num'] = -1;
      $assoc_args['where'] = "wp_admin";
      $this->create_products_from_remote_json(null, $assoc_args);
      add_action('admin_notices', function () {
        echo '<div class="notice notice-warning is-dismissible"><p> done </p></div>';
      });
    }
  }



  function delete_all_products_and_attachments($where = "wp_CLI")
  {

    if ($where != "wp_admin") {
      WP_CLI::log("Delete all");
    }

    global $wpdb;

    // 1. Delete orphaned metadata from wp_postmeta
    $orphaned_meta_query = "
        DELETE pm 
        FROM {$wpdb->postmeta} pm
        LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID
        WHERE p.ID IS NULL
    ";
    $wpdb->query($orphaned_meta_query);

    // 2. Delete orphaned entries in wp_wc_product_meta_lookup
    $orphaned_product_meta_query = "
        DELETE pml
        FROM {$wpdb->prefix}wc_product_meta_lookup pml
        LEFT JOIN {$wpdb->posts} p ON pml.product_id = p.ID
        WHERE p.ID IS NULL
    ";
    $wpdb->query($orphaned_product_meta_query);

    // 1. Find and delete orphaned product variations
    $orphaned_variations_query = "
        DELETE v
        FROM {$wpdb->posts} v
        LEFT JOIN {$wpdb->posts} p ON v.post_parent = p.ID
        WHERE v.post_type = 'product_variation'
        AND p.ID IS NULL
    ";
    $wpdb->query($orphaned_variations_query);

    // 2. Clean up orphaned metadata from wp_postmeta
    $orphaned_meta_query = "
        DELETE pm
        FROM {$wpdb->postmeta} pm
        LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID
        WHERE p.ID IS NULL
    ";
    $wpdb->query($orphaned_meta_query);

    // 3. Clean up orphaned entries in wp_wc_product_meta_lookup
    $orphaned_product_meta_query = "
        DELETE pml
        FROM {$wpdb->prefix}wc_product_meta_lookup pml
        LEFT JOIN {$wpdb->posts} p ON pml.product_id = p.ID
        WHERE p.ID IS NULL
    ";
    $wpdb->query($orphaned_product_meta_query);
    // Fetch all products
    $args = array(
      'post_type' => 'product',
      'posts_per_page' => -1, // Get all products
      'post_status' => 'any'  // Include all product statuses
    );

    $products = get_posts($args);

    // Loop through each product
    foreach ($products as $product) {
      $product_id = $product->ID;

      // Get the featured image (thumbnail)
      $featured_image_id = get_post_thumbnail_id($product_id);
      if ($featured_image_id) {
        // Delete the featured image
        wp_delete_attachment($featured_image_id, true);
      }

      // Get the product gallery images
      $gallery_image_ids = get_post_meta($product_id, '_product_image_gallery', true);
      if ($gallery_image_ids) {
        $gallery_image_ids = explode(',', $gallery_image_ids); // Split gallery IDs into an array
        foreach ($gallery_image_ids as $gallery_image_id) {
          // Delete each gallery image
          wp_delete_attachment($gallery_image_id, true);
        }
      }

      // Delete the product itself
      wp_delete_post($product_id, true); // true to force delete the product
    }
  }


  // Step 1: Convert Text File to JSON

  function convert_text_file_to_json($args, $assoc_args)
  {
    $file_path = $assoc_args['file'];

    if (($handle = fopen($file_path, "r")) !== FALSE) {
      $header = fgetcsv($handle, 0, ";");
      $products = [];
      $count = 0;

      while (($data = fgetcsv($handle, 0, ";")) !== FALSE) {
        $product_data = array_combine($header, $data);

        if (strpos($product_data['Artikelnr.'], '-M') !== false && strpos($product_data['Freifeld 6'], 'master;1') === 0) {
          $sku = $product_data['Artikelnr.'];
          $products[$sku] = [
            "sku" => $sku,
            "products_name" => $product_data['Bezeichnung'],
            "products_description" => $product_data['Text 4'],
            "products_type" => "Variable",
            "attribute" => [
              "Kolbenmaß (mm)" => []
            ]
          ];
          $count++;
        } else {
          $parent_sku = $product_data['Freifeld 6'];
          if (isset($products[$parent_sku])) {
            $variant_number = count($products[$parent_sku]['attribute']["Kolbenmaß (mm)"]) + 1;
            $variant = str_replace($products[$parent_sku]['products_name'], '', $product_data['Bezeichnung']);
            $products[$parent_sku]['attribute']["Kolbenmaß (mm)"][$variant_number] = [
              "variant" => trim($variant),
              "sku" => $product_data['Artikelnr.'],
              "price" => (float) $product_data['Preis_VK2_Preis']
            ];
          }
        }
      }
      WP_CLI::log("Number of Variable Products: $count");
      fclose($handle);

      $file_info = pathinfo($file_path);
      $output_path = $file_info['dirname'] . '/' . $file_info['filename'] . '.json';
      // Save to JSON file
      file_put_contents($output_path, json_encode($products, JSON_PRETTY_PRINT));
    }
  }

  function create_products($wp_CLI_exist = 1, $products_data, $num = 0, $start = 0)
  {
    if (empty($products_data)) {
      return rest_ensure_response(array('error' => 'No products found.'));
    }
    $counts = 0;
    foreach ($products_data as $product) {
      $counts++;
      if ($start > $counts + 1) {
        continue;
      }
      // check if sku already used
      $productID = wc_get_product_id_by_sku($product['sku']);
      if (!$productID) {
        if ($product['products_type'] == 'Variable') {
          $parent_id = $this->create_wc_variable_product($product);
          if ($wp_CLI_exist) {
            WP_CLI::log("Processed product number: $counts");
          }
        }
        if ($counts + 1 > $num) {
          exit;
        }
      } else {
        $this->log->putLog("Product ID");
        $this->log->putLog($productID);
        $this->log->putLog("sku");
        $this->log->putLog($product['sku']);
      }
    }
  }

  function create_products_from_remote_json($arg, $assoc_args)
  {
    if (isset($assoc_args['num'])) {
      $number_to_generate =  $assoc_args['num'];
    } else $number_to_generate =  0;
    $wp_CLI_exist = null;
    if (!isset($assoc_args['where'])) {
      $wp_CLI_exist = 1;
    }

    // Set up external WooCommerce API credentials
    $external_wc_api_url = 'https://mec.pe-dev.de/wp-json/mec-api/v1/products-json/';

    // Use wp_remote_get to fetch data from the external WooCommerce API
    $response = wp_remote_get($external_wc_api_url, array(
      'timeout' => 45,
    ));

    if (is_wp_error($response)) {
      return rest_ensure_response(array('error' => 'Unable to fetch products from external site.'));
    }

    $products_json = json_decode(wp_remote_retrieve_body($response), true);
    $products_data = $products_json["product_data"];

    if ($wp_CLI_exist) {
      WP_CLI::log($wp_CLI_exist);
    }
    $start = 43;
    $this->create_products($wp_CLI_exist, $products_data, $number_to_generate, $start);

    // Return the processed product data as a JSON response
    // return rest_ensure_response($products_json);
  }



  function create_wc_variable_product($product_data)
  {
    // Create the parent variable product
    $product = new WC_Product_Variable();
    $product->set_name($product_data['products_name']);
    $product->set_sku($product_data['sku']);
    $product->set_description($product_data['products_description']);
    $product->set_status('publish');
    $product->set_catalog_visibility('visible');
    // Set the image using the URL
    $image_url = $product_data['products_image']; // Assuming you have the URL
    $this->set_product_image_from_url($product, $image_url);

    // Step 1: Set Product Attributes
    $attributes = [];

    foreach ($product_data['attribute'] as $attribute_name => $variants_products) {
      $attribute = new WC_Product_Attribute();
      $attribute->set_name($attribute_name);
      $attribute->set_options(array_column($variants_products, 'variant')); // Set options for the attribute
      $attribute->set_visible(true);
      $attribute->set_variation(true);

      $attributes[] = $attribute;
    }

    // Add the attributes to the product
    $product->set_attributes($attributes);

    // Save the variable product and get its ID
    $parent_id = $product->save();

    // Step 2: Create Product Variations
    foreach ($product_data['attribute'] as $attribute_name => $variants_products) {
      foreach ($variants_products as $variant_product) {
        $variation = new WC_Product_Variation();
        $variation->set_parent_id($parent_id);

        // Set variation attributes
        $variation->set_attributes([
          sanitize_title($attribute_name) => $variant_product['variant'] // Ensure attribute name is sanitized
        ]);

        // Check if the SKU already exists
        $existing_product_id = wc_get_product_id_by_sku($variant_product['sku']);
        if ($existing_product_id) {
          // Handle the case where the SKU already exists
          // You could log an error, throw an exception, or skip creating the variation
          self::$log->putLog("parent ID");
          self::$log->putLog($parent_id);
          self::$log->putLog("sku");
          self::$log->putLog($variant_product['sku']);
        } else {
          // Set other properties like price, SKU, etc.
          $variation->set_regular_price($variant_product['price']);
          $variation->set_sku($variant_product['sku']);
          $variation->set_status('publish');
          $variation->save();
        }
      }
    }
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
