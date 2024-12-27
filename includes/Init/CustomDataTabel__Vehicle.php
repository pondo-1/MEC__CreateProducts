<?php

namespace MEC__CreateProducts\Init;

class CustomDataTabel__Vehicle
{
  private $charset;
  private $tablename;

  public function __construct()
  {
    global $wpdb;
    $this->charset = $wpdb->get_charset_collate();
    $this->tablename = $wpdb->prefix . "vehicles";
    add_action('activate_MEC__CreateProducts/MEC__CreateProducts.php', array($this, 'onActivate'));
  }

  function onActivate()
  {
    global $wpdb;
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    // Check if the table already exists
    if ($wpdb->get_var("SHOW TABLES LIKE '{$this->tablename}'") !== $this->tablename) {
      dbDelta("CREATE TABLE $this->tablename (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        vehicle_type varchar(60) NOT NULL DEFAULT '',
        brand varchar(60) NOT NULL DEFAULT '',
        model varchar(60) NOT NULL DEFAULT '',
        engine_displacement int(15) NOT NULL DEFAULT 0,
        prod_year varchar(60) NOT NULL DEFAULT '',
        compatible_products text not NULL DEFAULT '',
        PRIMARY KEY  (id)
      ) $this->charset;");
    }
  }

  public function importVehiclesFromJson($file_path = MEC__CP_API_Data_DIR . 'vehicle_all.json')
  {
    global $wpdb;

    // Read and decode JSON file
    $json_content = file_get_contents($file_path);
    $vehicles_array = json_decode($json_content, true);

    if (!$vehicles_array) {
      return new WP_Error('json_error', 'Failed to parse JSON file');
    }

    $inserted = 0;
    $errors = [];

    foreach ($vehicles_array as $vehicle_string) {
      // Split the string by '|' delimiter
      $vehicle_data = explode('|', $vehicle_string);

      // Check if we have all required fields
      if (count($vehicle_data) >= 5) {
        $data = [
          'vehicle_type' => $vehicle_data[0],
          'brand' => $vehicle_data[1],
          'model' => $vehicle_data[2],
          'engine_displacement' => intval($vehicle_data[3]),
          'prod_year' => $vehicle_data[4]
        ];

        // Insert into database
        $result = $wpdb->insert(
          $this->tablename,
          $data,
          ['%s', '%s', '%s', '%d', '%s']
        );

        if ($result) {
          $inserted++;
        } else {
          $errors[] = "Failed to insert: " . implode('|', $vehicle_data);
        }
      } else {
        $errors[] = "Failed to insert: " . implode('|', $vehicle_data);
      }
    }

    return [
      'success' => true,
      'inserted' => $inserted,
      'errors' => $errors
    ];
  }

  public function clearVehiclesTable()
  {
    global $wpdb;

    // TRUNCATE is faster than DELETE but resets auto-increment counter
    $result = $wpdb->query("TRUNCATE TABLE {$this->tablename}");

    // Alternative using DELETE if TRUNCATE fails
    if ($result === false) {
      $result = $wpdb->query("DELETE FROM {$this->tablename}");
    }

    return [
      'success' => ($result !== false),
      'message' => ($result !== false)
        ? 'Vehicle table cleared successfully'
        : 'Failed to clear vehicle table: ' . $wpdb->last_error
    ];
  }

  public function updateCompatibleVehicles()
  {
    global $wpdb;

    // Read JSON files
    $products_simple = json_decode(file_get_contents(MEC__CP_API_Data_DIR . 'products_simple.json'), true);
    $products_variant = json_decode(file_get_contents(MEC__CP_API_Data_DIR . 'products_variant.json'), true);

    // Prepare vehicle list as an associative array for quick lookup
    $vehicle_map = [];

    // Iterate through simple products
    foreach ($products_simple as $product_sku => $product) {
      if (isset($product['compatible'])) {
        foreach ($product['compatible'] as $compatible_string) {
          $product_id = wc_get_product_id_by_sku($product_sku);
          if ($product_id) {
            $vehicle_map[$compatible_string][] = $product_id; // Use product_id instead of product_sku
          }
        }
      }
    }
    // Iterate through variant products
    foreach ($products_variant as $product_sku => $product) {
      if (isset($product['compatible'])) {
        foreach ($product['compatible'] as $compatible_string) {
          $product_id = wc_get_product_id_by_sku($product_sku);
          if ($product_id) {
            $vehicle_map[$compatible_string][] = $product_id; // Use product_id instead of product_sku
          }
        }
      }
    }


    // Optionally, you can save the updated vehicle_map back to the database or return it
    // For example, you could update the compatible_products field in the vehicles table
    $file_path = MEC__CP_API_Data_DIR . 'vehicle_all.json';
    file_put_contents($file_path, json_encode($vehicle_map, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    // return $vehicle_map; // Return the updated vehicle map for further processing if needed
  }
}
