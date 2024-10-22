<?php

namespace MEC__CreateProducts\API;

class SaveToLocal
{
  private static $target_url = null;

  // Set Target External Json URL
  public static function setTarget($url)
  {
    if (self::$target_url === null) {
      self::$target_url = $url;
    }
    return self::$target_url;
  }

  // Function: Save JSON from the given URL as a text file in the same directory where this class exists
  public static function saveJsonToFile()
  {
    if (self::$target_url === null) {
      return 'Error: Target URL is not set.';
    }

    // Fetch JSON data from the target URL
    $response = wp_remote_get(self::$target_url);

    // Handle errors
    if (is_wp_error($response)) {
      return 'Error: ' . $response->get_error_message();
    }

    $body = wp_remote_retrieve_body($response);

    // Check if it's valid JSON
    if (!self::isValidJson($body)) {
      return 'Error: Invalid JSON data retrieved.';
    }

    // Determine the directory where the current class file is located
    $dir = __DIR__;
    $filename = $dir . DIRECTORY_SEPARATOR . 'saved_products.json';

    // Save JSON data to a file
    file_put_contents($filename, $body);

    return 'Success: JSON saved to ' . $filename;
  }

  // Helper function to check if a string is valid JSON
  private static function isValidJson($string)
  {
    json_decode($string);
    return (json_last_error() === JSON_ERROR_NONE);
  }
}
