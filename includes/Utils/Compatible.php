<?php

namespace MEC__CreateProducts\Utils;

class Compatible
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
    $Getvehicles = new GetVehicles($atts);
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
      <?php
      foreach ($Getvehicles->vehicles as $vehicle) { ?>
        <tr>
          <td><?php echo $vehicle->id; ?></td>
          <td><?php echo $vehicle->vehicle_type; ?></td>
          <td><?php echo $vehicle->brand; ?></td>
          <td><?php echo $vehicle->model; ?></td>
          <td><?php echo $vehicle->engine_displacement; ?></td>
          <td><?php echo $vehicle->prod_year; ?></td>
          <td><?php echo $vehicle->compatible_products; ?></td>
        </tr>
      <?php }
      ?>
    </table>
<?php
    return ob_get_clean(); // Return the buffered content
  }
}



class GetVehicles
{
  private $args;
  private $placeholders;
  private $count;
  public $vehicles;

  function __construct($atts = [])
  {
    global $wpdb;
    $tablename = $wpdb->prefix . 'vehicles';
    var_dump($atts);
    $this->args = $this->getShortcodeArgs($atts);
    $this->placeholders = $this->createPlaceholders();

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
