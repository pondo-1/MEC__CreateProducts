<?php

namespace MEC__CreateProducts\Admin;

use MEC__CreateProducts\API\SaveToLocal;
use MEC__CreateProducts\API\PrepareJsonLocal;
use MEC__CreateProducts\Utils\Utils;
use MEC__CreateProducts\Utils\AdminButton;

class AdminPage
{

  private $log;
  private $html;


  public function __construct()
  {
    $this->log = Utils::getLogger(); // Access the logger using the utility class

    // Action list
    // -> Button html 
    // -> CLI
    // Erstellen Funktion or class 
    // Then hier use 
    // Then prepare to use with button
    // Prepare use it for CLI? here? or Separate?

    // Hook into admin_init to register actions
    // add_action('admin_init', [$this, 'registerActions']);

    add_action('admin_init', [$this, 'generateHtml']);
    add_action('admin_menu', [$this, 'addAdminMenu']);
  }

  public function addAdminMenu()
  {

    add_menu_page(
      'MEC_dev_test',
      'MEC_dev_test',
      'manage_options',
      'MEC_dev_test',
      [$this, 'renderTableatAdminPage'],    // Callback to render the page content
      '',                           // Icon
      65                            // Position
    );
  }


  public function renderTableatAdminPage()
  {
    $return_html = null;
    // Start output buffering
    ob_start();
?>
    <div class="wrap">
      <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
      <table class="form-table">
        <tbody>
          <?php echo $this->generateHtml() ?>
        </tbody>
      </table>
    </div>
  <?php
    $return_html .= ob_get_clean();

    echo $return_html;
  }




  // Method that registers actions
  public function generateHtml()
  {
    $html = null;

    // Save Json to as Local file
    $from_pe_dev = new SaveToLocal;
    $from_pe_dev->setTarget('https://mec.pe-dev.de/wp-json/mec-api/v1/products/');
    if (isset($_POST['save_to_local'])) {
      $this->log->putLog("Button Clicked: 'save_to_local'");
      call_user_func([$from_pe_dev, 'saveJsonToFile']);
    }

    // Seperate data all -> all, single, variable, variant, variableWvariant?
    $LocalJsonProcess = new PrepareJsonLocal();
    if (isset($_POST['separate_products'])) {
      $this->log->putLog("Button Clicked: 'separate_products'");
      call_user_func([$LocalJsonProcess, 'separateProducts']);
    }

    // Save to local button. this generate local file products_all.json 
    $from_pe_dev_button = new AdminButton('save_to_local');
    $file_exist = $from_pe_dev->getFilePath();
    $description = 'Last modified: ' . $file_exist . '<br>' . 'Save the json(https://mec.pe-dev.de/wp-json/mec-api/v1/products/) to local directory';
    $html .= $from_pe_dev_button->returnTableButtonHtml('get Json', '', $description);

    // Seperates data and save it local products. products overview. variable products mit variant. single products, the rest
    $LocalJsonProcess_button = new AdminButton('separate_products');
    ob_start();
    // if ($LocalJsonProcess->fileExist()):
  ?>
    <div>
      if the all the processes from the upper buttons are succesfully finished, the endpoints automatically set
      <br>
      <a href="<?php //echo $LocalJsonProcess->EndpointUrl('single'); 
                ?>">Single Products(Single is not ready)</a>
      <br>
      <a href="<?php echo $LocalJsonProcess->EndpointUrl('variable'); ?>" target="_blank">Variable Products</a>
      <br>
      <a href="<?php echo $LocalJsonProcess->EndpointUrl('variant'); ?>" target="_blank">Variant Products</a>
      <br>
      <a href="<?php echo $LocalJsonProcess->EndpointUrl('extra'); ?>" target="_blank">Entra Products</a>
    </div>
<?php
    // endif;

    $description = ob_get_clean();
    $html .= $LocalJsonProcess_button->returnTableButtonHtml('prepare data', '', $description);


    return $html;
  }




  public function renderAdminPage()
  {
    echo $this->html; // Output the buffered HTML content
  }
}
