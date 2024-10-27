<?php

namespace MEC__CreateProducts\API;

use MEC__CreateProducts\Utils\Utils;
use WP_Error;

// Klasse zur Bereitstellung von Produktdaten aus JSON-Dateien, die in Local legt, über eine API
class LocalJsonToAPI
{
  // Logger-Instanz zur Aufzeichnung von Log-Meldungen
  private $log = null;

  // Konstruktor: Initialisiert die Logger-Instanz und startet den Datei-Überprüfungsprozess
  public function __construct()
  {
    $this->log = Utils::getLogger();
    $this->prepareTheFile();
  }


  // Überprüft, ob 'products_all.json' existiert und teilt die Produkte auf, wenn nötig
  public function prepareTheFile()
  {
    // Prüft, ob die Datei 'products_all.json' existiert
    if (file_exists(MEC__CP_API_Data_DIR . 'products_all.json')) {
      // Definiert die verschiedenen Produkttypen
      $types = ['all', 'variable', 'variant', 'single', 'extra'];
      foreach ($types as $index => $product_type) {
        $i = 0;
        // Prüft, ob die Datei für den Produkttyp existiert
        if (file_exists(MEC__CP_API_Data_DIR . 'products_' . $product_type . '.json')) {
          $i++;
          // Falls alle spezifischen Dateien fehlen, erstellt sie die separaten Produktdateien
          if ($i == 5) {
            $this->log->putLog('files are not ready');
          }
          // Fügt Endpunkte für die vorhandenen Produkttypen hinzu
          $this->log->putLog('product_' . $product_type . '.json is already there and set the endpoint');
          $this->setAPI__products_($product_type);
        }
      }
    }
  }



  // Erstellt einen API-Endpunkt für den angegebenen Produkttyp
  function setAPI__products_($product_type)
  {
    $this->log->putLog('product type: ' . $product_type);

    if (!$product_type) {
      // Loggt eine Warnung, falls kein gültiger Produkttyp angegeben wurde
      $this->log->putLog('setAPI__products_ could not find proper product type');
      return null;
    } else {
      // Registriert eine REST-API-Route für den Produkttyp
      add_action('rest_api_init', function () use ($product_type) {
        register_rest_route('mec-api/v1', '/products/' . $product_type, array(
          'methods' => 'GET',
          'callback' => [$this, 'getProductsCallback'],
          'args' => ['product_type' => $product_type], // Übergibt den Produkttyp
          'permission_callback' => '__return_true', // Offener Zugriff auf die API, ggf. anpassen
        ));
      });
    }
  }

  // Callback-Funktion, die Produktdaten für den angeforderten Produkttyp zurückgibt
  public function getProductsCallback($request)
  {
    // Ruft die Attribute der Anfrage ab
    $attributes = $request->get_attributes();

    // Extrahiert den Produkttyp aus den Attributen
    $product_type = $attributes['args']['product_type'];
    $this->log->putLog(print_r($attributes, true));

    // Definiert den Dateipfad basierend auf dem Produkttyp
    $file_path = MEC__CP_API_Data_DIR . 'products_' . $product_type . '.json';
    $this->log->putLog('getProductsCallback product path: ' . $file_path);

    // Prüft, ob die Datei existiert; falls nicht, gibt es einen Fehler zurück
    if (!file_exists($file_path)) {
      return new WP_Error('no_products', 'No products found for this type', array('status' => 404, 'file' => $file_path));
    }

    // Lädt den Inhalt der Datei und gibt einen Fehler zurück, wenn das Decoding fehlschlägt
    $data = json_decode(file_get_contents($file_path), true);
    if ($data === null) {
      return new WP_Error('invalid_data', 'Failed to decode product data', array('status' => 500));
    }

    // Gibt die Daten im JSON-Format als API-Antwort zurück
    return rest_ensure_response($data);
  }
}
