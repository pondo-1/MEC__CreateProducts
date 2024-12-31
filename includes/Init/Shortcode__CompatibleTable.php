<?php

namespace MEC__CreateProducts\Init;

use MEC__CreateProducts\Utils\VehiclesHandler;
use MEC__CreateProducts\Utils\Utils;

class Shortcode__CompatibleTable
{
  // Constructor to initialize the shortcode
  public function __construct()
  {
    add_shortcode('compatible_page', [$this, 'render_shortcode']);; // Enqueue scripts

    add_action('wp_ajax_get_models_by_brand',  [$this, 'get_models_by_brand']);
    add_action('wp_ajax_nopriv_get_models_by_brand',  [$this, 'get_models_by_brand']);

    add_action('wp_ajax_filter_vehicles', [$this, 'filter_vehicles']);
    add_action('wp_ajax_nopriv_filter_vehicles', [$this, 'filter_vehicles']);
  }


  function filter_vehicles()
  {
    global $wpdb;

    $data = json_decode(file_get_contents('php://input'), true);
    $vehicle_type = isset($data['vehicle_type']) ? sanitize_text_field($data['vehicle_type']) : '';
    $brand = isset($data['brand']) ? sanitize_text_field($data['brand']) : '';
    $model = isset($data['model']) ? sanitize_text_field($data['model']) : '';
    Utils::putLog($data);
    // Prepare the base query
    $tablename = $wpdb->prefix . 'vehicles';
    $query = "SELECT * FROM $tablename WHERE 1=1"; // Start with a base query

    // Add conditions based on the filter criteria
    if ('' != $vehicle_type) {
      Utils::putLog($vehicle_type);
      $query .= $wpdb->prepare(" AND vehicle_type = %s", $vehicle_type);
    }
    if ('' != $brand) {
      $query .= $wpdb->prepare(" AND brand = %s", $brand);
    }
    if ('' != $model) {
      $query .= $wpdb->prepare(" AND model = %s", $model);
    }
    $query .= " LIMIT 100";
    // Execute the query
    $vehicles = $wpdb->get_results($query);

    // Generate the HTML table for the filtered results
    $table = $this->generate_table($vehicles);

    // Return the table as JSON
    wp_send_json_success(['table' => $table]);
  }


  function get_models_by_brand()
  {
    // Get the raw POST data
    $data = json_decode(file_get_contents('php://input'), true);
    $brand = isset($data['brand']) ? sanitize_text_field($data['brand']) : '';

    // Check if the brand is received correctly
    Utils::putLog("Received brand: " . $brand); // Log the received brand for debugging

    // Query to get models based on the selected brand
    global $wpdb;
    $tablename = $wpdb->prefix . 'vehicles';
    $query = "SELECT DISTINCT model FROM $tablename";
    $query .= $wpdb->prepare("WHERE brand = %s", $brand);
    $query .= " LIMIT 100";
    $models = $wpdb->get_col($wpdb->get_results($query));
    // Return the models as JSON
    wp_send_json_success(['models' => $models]);
  }
  // Method to render the shortcode
  public function render_shortcode($atts = [], $content = null)
  {
    ob_start(); // Start output buffering
    global $wpdb;

    // // Pass shortcode attributes to GetVehicles
    $vehicle_object = new VehiclesHandler();
    // $vehicleTypes = ['Car', 'Truck', 'Motorcycle']; // Example vehicle types

    // // Checkbox form
    // echo '<form id="filterForm">';
    // foreach ($vehicleTypes as $type) {
    //   echo '<label><input type="checkbox" name="vehicle_type[]" value="' . esc_attr($type) . '">' . esc_html($type) . '</label><br>';
    // }
    // echo '<button type="button" id="filterButton">Filter</button>';
    // echo '</form>';

    // echo $this->generate_checkbox($vehicles->filter_options);
    // Table rendering
    $filter_options = $vehicle_object->create_filter_options();
    echo $this->generate_options($filter_options);
    echo '<div id="vehicleTable">';
    echo $this->generate_table($vehicle_object->vehicles);
    echo '</div>';
    echo $this->javascript();
    // Enqueue the JavaScript file
    wp_enqueue_script('compatible-table', 'MEC__CP_URL' . '/includes/Init/compatible-filter.js', [], null, true);

    return ob_get_clean(); // Return the buffered content
  }
  private function javascript()
  {
?>
    <script>
      // Only loaded if the page has id vehicleTable element exist
      const vehicleTableElement = document.getElementById("vehicleTable");
      // update options 
      // if one of then are change -> not selected options modified 
      // reset button 
      if (vehicleTableElement) {
        document.getElementById("brand").addEventListener("change", function() {
          const selectedBrand = this.value;
          // Make an AJAX request to fetch models based on the selected brand
          fetch(
              "<?php echo admin_url('admin-ajax.php'); ?>?action=get_models_by_brand", {
                method: "POST",
                headers: {
                  "Content-Type": "application/json",
                },
                body: JSON.stringify({
                  brand: selectedBrand,
                }),
              }
            )
            .then((response) => response.json())
            .then((data) => {
              const modelSelect = document.getElementById("model");
              modelSelect.innerHTML = '<option value="">Select Modell</option>'; // Reset the model dropdown

              // Populate the model dropdown with the fetched models
              data.data.models.forEach((model) => {
                const option = document.createElement("option");
                option.value = model;
                option.textContent = model;
                modelSelect.appendChild(option);
              });
            })
            .catch((error) => console.error("Error:", error));
        });
      }


      if (vehicleTableElement) {
        // Function to handle the change event
        const handleChange = function() {
          const selectedType = document.getElementById("vehicle_type").value;
          const selectedBrand = document.getElementById("brand").value;
          const selectedModel = document.getElementById("model").value;

          // Make an AJAX request to fetch filtered data
          fetch("<?php echo admin_url('admin-ajax.php'); ?>?action=filter_vehicles", {
              method: "POST",
              headers: {
                "Content-Type": "application/json",
              },
              body: JSON.stringify({
                vehicle_type: selectedType,
                brand: selectedBrand,
                model: selectedModel,
              }),
            })
            .then((response) => response.json())
            .then((data) => {
              console.log(data);
              document.getElementById("vehicleTable").innerHTML = data.data.table; // Update the table with filtered data
            })
            .catch((error) => console.error("Error:", error));
        };

        // Add event listeners for change events on the relevant elements
        document.getElementById("brand").addEventListener("change", handleChange);
        document.getElementById("model").addEventListener("change", handleChange);
        document.getElementById("vehicle_type").addEventListener("change", handleChange);

        const resetFilters = function() {
          document.getElementById("vehicle_type").value = ""; // Reset vehicle type
          document.getElementById("brand").value = ""; // Reset brand
          document.getElementById("model").value = ""; // Reset model
          document.getElementById("vehicleTable").innerHTML = ""; // Clear the vehicle table
          handleChange();
        };

        // Add event listener for the reset button
        document.getElementById("filterResetButton").addEventListener("click", resetFilters);
      }
    </script>
  <?php
  }
  private function generate_options($filter_options)
  {

    // Display filter options
    echo '<form id="filterForm">';
    echo '<h3>Filter Options</h3>';

    echo '<label for="vehicle_type">Typ:</label>';
    echo '<select name="vehicle_type" id="vehicle_type">';
    echo '<option value="">Select Typ</option>'; // Default option
    foreach ($filter_options['types'] as $type) {
      echo '<option value="' . esc_attr($type) . '">' . esc_html($type) . '</option>';
    }
    echo '</select><br>';

    echo '<label for="brand">Marke:</label>';
    echo '<select name="brand" id="brand">';
    echo '<option value="">Select Marke</option>'; // Default option
    foreach ($filter_options['brands'] as $brand) {
      echo '<option value="' . esc_attr($brand) . '">' . esc_html($brand) . '</option>';
    }
    echo '</select><br>';

    echo '<label for="model">Modell:</label>';
    echo '<select name="model" id="model">';
    echo '<option value="">Select Modell</option>'; // Default option
    foreach ($filter_options['models'] as $model) {
      echo '<option value="' . esc_attr($model) . '">' . esc_html($model) . '</option>';
    }
    echo '</select><br>';

    echo '<button type="button" id="filterResetButton">Reset</button>';
    echo '</form>';
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
      <?php if (!empty($vehicles)) : ?>
        <?php foreach ($vehicles as $vehicle) : ?>
          <tr>
            <td><?php echo esc_html($vehicle->id); ?></td>
            <td><?php echo esc_html($vehicle->vehicle_type); ?></td>
            <td><?php echo esc_html($vehicle->brand); ?></td>
            <td><?php echo esc_html($vehicle->model); ?></td>
            <td><?php echo esc_html($vehicle->engine_displacement); ?></td>
            <td><?php echo esc_html($vehicle->prod_year); ?></td>
            <td><?php echo esc_html($vehicle->compatible_products); ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else : ?>
        <tr>
          <td colspan="7">No vehicles found.</td>
        </tr>
      <?php endif; ?>
    </table>
<?php
    return ob_get_clean();
  }
}
