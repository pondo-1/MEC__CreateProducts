<?php

namespace MEC__CreateProducts\API;

use MEC__CreateProducts\Utils\Utils;

//Show products. products overview. variable products mit variant. single products, the rest

class LocalJsonToAPI
{
  private $mec_api_url = '/wp-json/mec-api/v1/products/';
  private $filePath = __DIR__ . DIRECTORY_SEPARATOR . 'products_all.json';
  private $log = null;

  public function __construct($filePath = '')
  {
    //set Default
    if (!$filePath == '') {
      $this->filePath = $filePath;
    }

    // Check if the file exists
    // 1. Single
    // 2. Variable Product with variant 
    //    2.1. only  Variable Products 
    //    2.2 only    variants Prducts
    // 3. Extra/ Unknown 
    $this->log = Utils::getLogger();
  }

  public function prepareTheFile()
  {
    if (file_exists($this->filePath)) {

      return "products_single.json, products_variable.json generated ";
    } else {
      $this->log->putLog('@PrepareJsonLocal =>>  Could not find the file: ' . $this->filePath);
    }
  }

  function separateProducts()
  {
    // Datei laden
    $rawdata = json_decode(file_get_contents($this->filePath), true);
    $data = $rawdata['products_data'];
    $products_variable = [];
    $products_variant = [];
    $products_extra = [];
    $i = 0;
    foreach ($data as $sku => $product) {
      $i++;

      if ($i == 1) {
        $this->log->putLog(print_r($product, true));
      }

      // Bedingung prüfen und Produkt der entsprechenden Liste hinzufügen
      if (strpos($sku, '-M') !== false) {
        $products_variable[$sku] = $product;
      } elseif (strpos($product['freifeld6'], '-M') !== false) {
        $products_variant[$sku] = $product;
      } else {
        $products_extra[$sku] = $product;
      }
    }

    // Daten in separate Dateien speichern
    file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . 'products_variable.json', json_encode($products_variable, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . 'products_variant.json', json_encode($products_variant, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . 'products_extra.json', json_encode($products_extra, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    $this->log->putLog('Produkte wurden erfolgreich in separate Dateien aufgeteilt.');
  }

  public function fileExist() {}
  public function  generateAPI($type_of_products = 'single')
  {
    $this->setEndpoint($type_of_products);
    if ($type_of_products == 'single') {
      // filter only single 
    }
  }

  public function setEndpoint() {}

  public function EndpointUrl($endpoint)
  {
    if ($endpoint != '') return $this->mec_api_url . '/' . $endpoint . '/';
    else return '';
  }
}
