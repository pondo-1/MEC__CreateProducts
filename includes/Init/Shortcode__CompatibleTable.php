<?php

namespace MEC__CreateProducts\Init;

class Shortcode__CompatibleTable
{
  // Constructor to initialize the shortcode
  public function __construct()
  {
    add_shortcode('compatible_page', [$this, 'render_shortcode']);
  }

  // Method to render the shortcode
  public function render_shortcode($atts = [], $content = null)
  {
    ob_start(); // Start output buffering
    global $wpdb;

    // Pass shortcode attributes to GetVehicles
    $vehicles = new GetVehicles($atts);
    $vehicleTypes = ['Car', 'Truck', 'Motorcycle']; // Example vehicle types

    // Checkbox form
    echo '<form id="filterForm">';
    foreach ($vehicleTypes as $type) {
      echo '<label><input type="checkbox" name="vehicle_type[]" value="' . esc_attr($type) . '">' . esc_html($type) . '</label><br>';
    }
    echo '<button type="button" id="filterButton">Filter</button>';
    echo '</form>';

    echo $this->generate_checkbox($vehicles->filter_options);
    // Table rendering
    echo '<div id="vehicleTable">';
    echo $this->generate_table($vehicles->vehicles);
    echo '</div>';

    return ob_get_clean(); // Return the buffered content
  }

  // Method to generate the HTML table
  private function generate_table($vehicles)
  {
    ob_start();
?>
    <table class="table">
      <tr>
        <th>Id</th>
        <th>Typ</th>
        <th>Marke</th>
        <th>Modell</th>
        <th>Hubraum</th>
        <th>Baujahr</th>
        <th>Product Ids</th>
      </tr>
      <?php foreach ($vehicles as $vehicle) { ?>
        <tr>
          <td><?php echo esc_html($vehicle->id); ?></td>
          <td><?php echo esc_html($vehicle->vehicle_type); ?></td>
          <td><?php echo esc_html($vehicle->brand); ?></td>
          <td><?php echo esc_html($vehicle->model); ?></td>
          <td><?php echo esc_html($vehicle->engine_displacement); ?></td>
          <td><?php echo esc_html($vehicle->prod_year); ?></td>
          <td><?php echo esc_html($vehicle->compatible_products); ?></td>
        </tr>
      <?php } ?>
    </table>
  <?php
    return ob_get_clean();
  }
}



class GetVehicles
{
  private $args;
  private $placeholders;
  private $count;
  public $vehicles;
  public $filter_options;

  function __construct($atts = [])
  {
    global $wpdb;
    $tablename = $wpdb->prefix . 'vehicles';

    $this->args = $this->getShortcodeArgs($atts);
    $this->placeholders = $this->createPlaceholders();
    $this->filter_options = $this->create_filter_options();
    $query = "SELECT * FROM $tablename ";
    $countQuery = "SELECT COUNT(*) FROM $tablename ";
    $query .= $this->createWhereText();
    $countQuery .= $this->createWhereText();
    $query .= " LIMIT 100";
    var_dump($this->args);
    var_dump($query);
    $this->count = $wpdb->get_var($wpdb->prepare($countQuery, $this->placeholders));
    $this->vehicles = $wpdb->get_results($wpdb->prepare($query, $this->placeholders));
  }
  function getShortcodeArgs($atts)
  {
    $temp = [];
    if (isset($atts['Typ'])) {
      $temp['vehicle_type'] = sanitize_text_field($atts['Typ']);
    }
    if (isset($atts['marke'])) {
      $temp['brand'] = sanitize_text_field($atts['marke']);
    }
    if (isset($atts['Modell'])) {
      $temp['model'] = sanitize_text_field($atts['Modell']);
    }
    if (isset($atts['minHubraum'])) {
      $temp['min_engine_displacement'] = sanitize_text_field($atts['minHubraum']);
    }
    if (isset($atts['maxHubraum'])) {
      $temp['max_engine_displacement'] = sanitize_text_field($atts['maxHubraum']);
    }

    return $temp;
  }
  function getArgs()
  {
    $temp = [];

    if (isset($_GET['Typ'])) {
      $temp['vehicle_type'] = sanitize_text_field($_GET['Typ']);
    }
    if (isset($_GET['Marke'])) {
      $temp['brand'] = sanitize_text_field($_GET['Marke']);
    }
    if (isset($_GET['Modell'])) {
      $temp['model'] = sanitize_text_field($_GET['Modell']);
    }
    if (isset($_GET['minHubraum'])) {
      $temp['min_engine_displacement'] = sanitize_text_field($_GET['minHubraum']);
    }
    if (isset($_GET['maxHubraum'])) {
      $temp['max_engine_displacement'] = sanitize_text_field($_GET['maxHubraum']);
    }

    return $temp; // Return only the populated array
  }

  function createPlaceholders()
  {
    return array_map(function ($x) {
      return $x;
    }, $this->args);
  }

  function createWhereText()
  {
    $whereQuery = "";

    if (count($this->args)) {
      $whereQuery = "WHERE ";
    }

    $currentPosition = 0;
    foreach ($this->args as $index => $item) {
      $whereQuery .= $this->specificQuery($index);
      if ($currentPosition != count($this->args) - 1) {
        $whereQuery .= " AND ";
      }
      $currentPosition++;
    }

    return $whereQuery;
  }

  function specificQuery($index)
  {
    switch ($index) {
      case "minHubraum":
        return "engine_displacement >= %d";
      case "maxHubraum":
        return "engine_displacement <= %d";
      default:
        return $index . " = %s";
    }
  }
}

add_action('wp_ajax_filter_vehicles', 'filter_vehicles');
add_action('wp_ajax_nopriv_filter_vehicles', 'filter_vehicles');

function filter_vehicles()
{
  global $wpdb;
  $vehicle_types = isset($_POST['vehicle_types']) ? $_POST['vehicle_types'] : [];

  // Prepare the query based on selected vehicle types
  $tablename = $wpdb->prefix . 'vehicles';
  $placeholders = implode(',', array_fill(0, count($vehicle_types), '%s'));
  $query = "SELECT * FROM $tablename WHERE vehicle_type IN ($placeholders)";
  $vehicles = $wpdb->get_results($wpdb->prepare($query, $vehicle_types));

  // Generate the table
  $table = generate_vehicle_table($vehicles);

  // Return the table as JSON
  wp_send_json_success(['table' => $table]);
}

function generate_vehicle_table($vehicles)
{
  ob_start();
  ?>
  <table class="table">
    <tr>
      <th>Id</th>
      <th>Typ</th>
      <th>Marke</th>
      <th>Modell</th>
      <th>Hubraum</th>
      <th>Baujahr</th>
      <th>Product Ids</th>
    </tr>
    <?php foreach ($vehicles as $vehicle) { ?>
      <tr>
        <td><?php echo esc_html($vehicle->id); ?></td>
        <td><?php echo esc_html($vehicle->vehicle_type); ?></td>
        <td><?php echo esc_html($vehicle->brand); ?></td>
        <td><?php echo esc_html($vehicle->model); ?></td>
        <td><?php echo esc_html($vehicle->engine_displacement); ?></td>
        <td><?php echo esc_html($vehicle->prod_year); ?></td>
        <td><?php echo esc_html($vehicle->compatible_products); ?></td>
      </tr>
    <?php } ?>
  </table>
<?php
  return ob_get_clean();
}
