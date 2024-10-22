<?php

namespace MEC__CreateProducts\Utils;

use MEC__CreateProducts\Log\Logger;

class Utils
{
  private static $logger = null;

  // Static method to get the logger instance
  public static function getLogger()
  {
    if (self::$logger === null) {
      self::$logger = new Logger('product-import-log.txt');
    }
    return self::$logger;
  }
}
