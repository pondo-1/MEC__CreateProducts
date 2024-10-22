<?php

namespace MEC__CreateProducts\Admin;

use MEC__CreateProducts\API\SaveToLocal;
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
    $from_pe_dev::setTarget('https://mec.pe-dev.de/wp-json/wp/v2/posts');
    if (isset($_POST['save_to_local'])) {
      $this->log->putLog("Button Clicked: 'save_to_local'");
      call_user_func([$from_pe_dev, 'saveJsonToFile']);
    }
    // Start output buffering
    ob_start();
  ?>
    <?php
    $from_pe_dev_button = new AdminButton('save_to_local', [$from_pe_dev, 'saveJsonToFile']);
    echo $from_pe_dev_button->returnTableButtonHtml();
    ?>
<?php
    $html = ob_get_clean();

    return $html;
  }




  public function renderAdminPage()
  {
    echo $this->html; // Output the buffered HTML content
  }
}
