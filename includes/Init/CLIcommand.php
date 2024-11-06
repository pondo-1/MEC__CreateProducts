<?php

namespace MEC__CreateProducts\Init;

use MEC__CreateProducts\Utils\WCHandler;
use MEC__CreateProducts\Utils\SQLscript;
use MEC__CreateProducts\Utils\Utils;
use WP_CLI;

class CLIcommand
{

  public function __construct()
  {
    if (defined('WP_CLI') && WP_CLI) {
      WP_CLI::add_command('create_products', [$this, 'create_products_CLI']);
      WP_CLI::add_command('delete_all_products', [SQLscript::class, 'delete_all_products']);
      WP_CLI::add_command('compare', [$this, 'compare']);
    }
  }
  function compare()
  {
    $filePath = MEC__CP_API_Data_DIR . 'products_raw.json';
    if (file_exists($filePath)) {
      $raw = json_decode(file_get_contents($filePath), true);
      $raw = $raw['products_data'];
      $raw_keys = array_keys($raw);
    }
    $filePath = MEC__CP_API_Data_DIR . '14774.csv';
    if (file_exists($filePath)) {
      $csv = array_map('str_getcsv', file($filePath));
    }

    $i = 0;
    do {
      if (isset($raw[$csv[$i][0]])) {
        // Utils::cli_log($i);
      } else {
        Utils::cli_log($csv[$i][0]);
      }
      $i++;
    } while (isset($csv[$i][0]));
  }
  function create_products_CLI($arg, $assoc_args)
  {
    $wp_CLI_exist = null;
    if (!isset($assoc_args['where'])) {
      $wp_CLI_exist = 1;
    }

    if (isset($assoc_args['num'])) {
      $number_to_generate =  $assoc_args['num'];
    } else $number_to_generate =  0;

    if (isset($assoc_args['type'])) {
      $type = $assoc_args['type'];
    } else {
      $type = 'simple';
    }

    if (isset($assoc_args['start'])) {
      $start = $assoc_args['start'];
    } else {
      $start = 0;
    }
    WCHandler::create_products(1, $type, $number_to_generate, $start);
  }
}
