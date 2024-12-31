<?php

namespace MEC__CreateProducts\Utils;

class VehiclesHandler
{
  private $args;
  private $placeholders;
  private $count;
  public $vehicles;
  public $filter_options;

  function __construct()
  {
    global $wpdb;
    $tablename = $wpdb->prefix . 'vehicles';

    // $this->placeholders = $this->createPlaceholders();
    $this->filter_options = $this->create_filter_options();
    $query = "SELECT * FROM $tablename ";
    $countQuery = "SELECT COUNT(*) FROM $tablename ";
    // $query .= $this->createWhereText();
    // $countQuery .= $this->createWhereText();
    $query .= " LIMIT 100";
    // $this->count = $wpdb->get_var($wpdb->prepare($countQuery, $this->placeholders));
    // $this->vehicles = $wpdb->get_results($wpdb->prepare($query, $this->placeholders));
    $this->vehicles = $wpdb->get_results($wpdb->prepare($query));
  }

  function createPlaceholders()
  {
    return array_map(function ($x) {
      return $x;
    }, $this->args);
  }

  function createWhereText()
  {
    $whereQuery = "";

    if (count($this->args)) {
      $whereQuery = "WHERE ";
    }

    $currentPosition = 0;
    foreach ($this->args as $index => $item) {
      $whereQuery .= $this->specificQuery($index);
      if ($currentPosition != count($this->args) - 1) {
        $whereQuery .= " AND ";
      }
      $currentPosition++;
    }

    return $whereQuery;
  }

  function specificQuery($index)
  {
    switch ($index) {
      case "minHubraum":
        return "engine_displacement >= %d";
      case "maxHubraum":
        return "engine_displacement <= %d";
      default:
        return $index . " = %s";
    }
  }

  public function create_filter_options()
  {
    global $wpdb;
    $tablename = $wpdb->prefix . 'vehicles';

    // Query to get distinct values for Typ, Marke, and Modell
    $types = $wpdb->get_col("SELECT DISTINCT vehicle_type FROM $tablename");
    $brands = $wpdb->get_col("SELECT DISTINCT brand FROM $tablename");
    $models = $wpdb->get_col("SELECT DISTINCT model FROM $tablename");

    // Structure the filter options
    return [
      'types' => $types,
      'brands' => $brands,
      'models' => $models,
    ];
  }
}
