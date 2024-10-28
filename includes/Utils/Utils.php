<?php

namespace MEC__CreateProducts\Utils;

use MEC__CreateProducts\Log\Logger;
//The purpose of the Utils class in this code is to provide a convenient, 
//centralized way to access a shared Logger instance 
//across different parts of the codebase without needing to create multiple Logger instances.

class Utils
{
  // Static property to hold a single instance of Logger
  private static $logger = null;

  // Static method to get or create the logger instance
  public static function getLogger()
  {
    // Check if the logger instance is already created
    if (self::$logger === null) {
      // Create a new Logger instance if it does not exist
      self::$logger = new Logger('log.txt');
    }
    // Return the logger instance
    return self::$logger;
  }

  // Static method to log messages directly
  public static function putLog($message)
  {
    // Ensure the logger instance exists and log the message
    self::getLogger()->putLog($message);
  }
}
