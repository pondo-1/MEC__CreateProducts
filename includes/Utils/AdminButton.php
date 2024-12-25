<?php

namespace MEC__CreateProducts\Utils;


class AdminButton
{
  private $log;
  private $action_name;

  public function __construct($action_name)
  {
    $this->action_name = $action_name;
  }

  public function returnButtonHtml($button_text = '', $button_type = '', $description_html = '')
  {
    $html = null;
    if ($button_text == '') {
      $button_text = $this->action_name;
    }
    if ($button_type == '') {
      $button_type = 'primary';
    }
    if ($description_html == '') {
      $description_html = 'This button for the follwoing action: ' . $this->action_name;
    }
    ob_start();
?>
    <form method="post" action="">
      <?php submit_button($button_text, $button_type, $this->action_name); ?>
      <?php echo $description_html; ?>
    </form>
  <?php

    $html .= ob_get_clean();
    return $html;
  }



  public function returnTableButtonHtml($button_text = '', $button_type = '', $description_html = '')
  {
    $html = null;
    if ($button_text == '') {
      $button_text = $this->action_name;
    }
    if ($button_type == '') {
      $button_type = 'primary';
    }
    if ($description_html == '') {
      $description_html = 'This button for the follwoing action: ' . $this->action_name;
    }
    ob_start();
  ?>
    <div class="admin-button-row" style="display: flex; padding: 8px 0; border-top: gray dotted; align-items: center;">
      <div class="admin-button-label" style="flex: 0 0 200px; font-weight: bold;">
        <?php echo $this->action_name; ?>
      </div>
      <div class="admin-button-content" style="flex: 1;">
        <form method="post" action="">
          <?php submit_button($button_text, $button_type, $this->action_name); ?>
        </form>
        <?php echo $description_html; ?>
      </div>
    </div>
<?php
    $html .= ob_get_clean();
    return $html;
  }
}
