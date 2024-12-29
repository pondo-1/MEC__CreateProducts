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
    }
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
    WCHandler::create_products_from_json(1, $type, $number_to_generate, $start);
  }
}
