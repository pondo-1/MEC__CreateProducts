<?php

namespace MEC__CreateProducts\API;

use MEC__CreateProducts\Utils\Utils;

class SaveToLocal
{
  private $target_url;
  private $filePath;
  private $log = null;

  public function __construct($url = '', $filePath = '')
  {
    $this->log = Utils::getLogger();
    if ($url != '') {
      $this->setTarget($url);
    }
    if ($filePath != '') {
      $this->filePath = $filePath . '_all.json';
    }
  }
  // Set Target External Json URL
  public function setTarget($url)
  {
    if ($this->target_url === null) {
      $this->target_url = $url;
    }
    return $this->target_url;
  }

  // Function: Save JSON from the given URL as a text file in the same directory where this class exists
  public function saveJsonToFile()
  {
    if ($this->target_url === null) {
      return 'Error: Target URL is not set.';
    }

    // Fetch JSON data from the target URL
    $response = wp_remote_get($this->target_url, array(
      'timeout' => 45,
    ));

    // Handle errors
    if (is_wp_error($response)) {
      Utils::putLog('Error: ' . $response->get_error_message());
      return 'Error: ' . $response->get_error_message();
    }

    $body = wp_remote_retrieve_body($response);

    // Check if it's valid JSON
    if (!$this->isinValidJson($body)) {
      Utils::putLog('Error: Invalid JSON data retrieved.');
      return 'Error: Invalid JSON data retrieved.';
    }
    // Save JSON data to a file
    file_put_contents($this->filePath, $body);
    Utils::putLog('Success: JSON saved to ' . $this->filePath);
    return $this->filePath;
  }

  public function getFilePath()
  {
    // Check if the file exists
    if (file_exists($this->filePath)) {
      // Get the file modification time as a Unix timestamp
      date_default_timezone_set('Europe/Berlin');
      $fileModificationTime = filemtime($this->filePath);

      // Format the timestamp into a readable date and time format
      $formattedTime = date("Y-m-d H:i:s", $fileModificationTime);

      return $formattedTime;
    } else {
      return "File does not exist.";
    }

    return $filename;
  }

  // Helper function to check if a string is valid JSON
  private function isinValidJson($string)
  {
    json_decode($string);
    return (json_last_error() === JSON_ERROR_NONE);
  }
}
