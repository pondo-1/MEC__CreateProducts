<?php

namespace MEC__CreateProducts\API;

use MEC__CreateProducts\Utils\Utils;

//Show products. products overview. variable products mit variant. single products, the rest

class PrepareJsonLocal
{
  private $mec_api_url = MEC__CP_APIURL;
  private $filePath_all = MEC__CP_API_Data_DIR . 'products_all.json';
  private $log = null;

  public function __construct($filePath_all = '')
  {
    //set Default
    if (!$filePath_all == '') {
      $this->filePath_all = $filePath_all;
    }
    $this->log = Utils::getLogger();
  }

  public function prepareTheFile()
  {
    if (file_exists($this->filePath_all)) {

      return "products_single.json, products_variable.json generated ";
    } else {
      $this->log->putLog('@PrepareJsonLocal =>>  Could not find the file: ' . $this->filePath_all);
    }
  }

  function separateProducts()
  {
    // Lädt die 'products_all.json'-Datei
    $rawdata = json_decode(file_get_contents(MEC__CP_API_Data_DIR . 'products_all.json'), true);
    $data = $rawdata['products_data'];

    // Initialisiert Arrays für die verschiedenen Produkttypen
    $products_variable = [];
    $products_variant = [];
    $products_single = [];
    $products_extra = [];
    $i = 0;

    // Durchläuft die Produktdaten und sortiert sie nach Typ
    foreach ($data as $sku => $product) {
      $i++;

      if ($i == 1) {
        // Protokolliert das erste Produkt zur Kontrolle
        $this->log->putLog(print_r($product, true));
      }

      // Fügt das Produkt basierend auf bestimmten Bedingungen zur entsprechenden Liste hinzu
      if (strpos($sku, '-M') !== false) {
        $products_variable[$sku] = $product;
      } elseif (strpos($product['freifeld6'], '-M') !== false) {
        $products_variant[$sku] = $product;
      } elseif ($product['info']['image']) {
        $products_single[$sku] = $product;
      } else {
        $products_extra[$sku] = $product;
      }
    }

    // Speichert die sortierten Daten in separaten JSON-Dateien
    file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . 'products_variable.json', json_encode($products_variable, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . 'products_variant.json', json_encode($products_variant, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . 'products_single.json', json_encode($products_single, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . 'products_extra.json', json_encode($products_extra, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    // Protokolliert, dass die Produkte erfolgreich aufgeteilt wurden
    $this->log->putLog('Produkte wurden erfolgreich in separate Dateien aufgeteilt.');
  }
}
