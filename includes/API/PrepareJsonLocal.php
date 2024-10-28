<?php

namespace MEC__CreateProducts\API;

use MEC__CreateProducts\Utils\Utils;

//Show products. products overview. variable products mit variant. single products, the rest

class PrepareJsonLocal
{

  private $json_prefix;
  private $json_suffix;
  private $filePath_all;
  private $log = null;
  // $json_prefix = 'products';
  // $json_suffix = ['all', 'variable', 'variant', 'single', 'extra'];


  public function __construct($json_prefix, $json_suffix)
  {
    $this->json_prefix = $json_prefix;
    $this->json_suffix =  $json_suffix;
    $this->filePath_all = MEC__CP_API_Data_DIR . $this->json_prefix . '_all.json';
    $this->log = Utils::getLogger();
  }

  function separate_data()
  {
    // Lädt die 'products_all.json'-Datei
    $rawdata = json_decode(file_get_contents($this->filePath_all), true);
    $data = $rawdata['products_data'];

    $products = [];
    foreach ($this->json_suffix as $product_type) {
      $products[] = [$product_type => []];
    }
    // Initialisiert Arrays für die verschiedenen Produkttypen

    $i = 0;
    // Durchläuft die Produktdaten und sortiert sie nach Typ
    foreach ($data as $sku => $product) {
      $i++;

      if ($i == 1) {
        // Protokolliert das erste Produkt zur Kontrolle
        Utils::putLog(print_r($product, true));
      }

      // Fügt das Produkt basierend auf bestimmten Bedingungen zur entsprechenden Liste hinzu
      if (strpos($sku, '-M') !== false) {
        $products['variable'][$sku] = $product;
      } elseif (strpos($product['freifeld6'], '-M') !== false) {
        $products['variant'][$sku] = $product;
      } elseif ($product['info']['image']) {
        $products['single'][$sku] = $product;
      } else {
        $products['extra'][$sku] = $product;
      }
    }

    // Speichert die sortierten Daten in separaten JSON-Dateien
    foreach ($this->json_suffix as $product_type) {
      file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . 'products_' . $product_type . '.json', json_encode($products[$product_type], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    // Protokolliert, dass die Produkte erfolgreich aufgeteilt wurden
    Utils::putLog('Produkte wurden erfolgreich in separate Dateien aufgeteilt.');
  }

  function delete_separated_data()
  {
    // Löscht die JSON-Dateien für jeden Produkttyp, falls sie existieren
    foreach ($this->json_suffix as $product_type) {
      $file_path = __DIR__ . DIRECTORY_SEPARATOR . 'products_' . $product_type . '.json';

      // Prüfen, ob die Datei existiert, und dann löschen
      if (file_exists($file_path)) {
        unlink($file_path);
      }
    }
  }
}
