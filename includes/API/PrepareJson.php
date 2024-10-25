<?php

namespace MEC__CreateProducts\API;

use MEC__CreateProducts\Utils\Utils;

//Show products. products overview. variable products mit variant. single products, the rest

class PrepareJson
{
  private $mec_api_url = '/wp-json/mec-api/v1/products-json/';
  private $filePath = __DIR__ . DIRECTORY_SEPARATOR . 'products_all.json';
  private $log = null;

  public function __construct($filePath = '')
  {
    //set Default
    if (!$filePath = '') {
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
      $this->log->putLog('@PrepareJson =>>  Could not find the file: ' . $this->filePath);
    }
  }
  public function  generateAPI($type_of_products = 'single')
  {
    $this->setEndpoint($type_of_products);
    if ($type_of_products == 'single') {
      // filter only single 
    }
  }
  public function setEndpoint() {}
}
