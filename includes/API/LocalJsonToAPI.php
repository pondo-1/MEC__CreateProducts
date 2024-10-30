<?php

namespace MEC__CreateProducts\API;

use MEC__CreateProducts\Utils\Utils;
use WP_Error;

// Klasse zur Bereitstellung von Produktdaten aus JSON-Dateien, die in Local legt, über eine API
class LocalJsonToAPI
{
  // Static property to prevent multiple executions
  private static $is_initialized = false;

  // Überprüft, ob 'products_all.json' existiert und teilt die Produkte auf, wenn nötig
  public static function prepareAPI()
  {
    // Check if the function has already been run
    if (self::$is_initialized) {
      return; // Exit if already initialized
    }

    // Mark as initialized
    self::$is_initialized = true;
    // Prüft, ob die Datei 'products_all.json' existiert
    if (file_exists(MEC__CP_API_Data_DIR . 'products_all.json')) {
      // Definiert die verschiedenen Produkttypen
      $types = ['all', 'variable', 'variant', 'single', 'extra', 'variable_variant'];
      foreach ($types as $index => $product_type) {
        $i = 0;
        // Prüft, ob die Datei für den Produkttyp existiert
        if (file_exists(MEC__CP_API_Data_DIR . 'products_' . $product_type . '.json')) {
          $i++;
          // Falls alle spezifischen Dateien fehlen, erstellt sie die separaten Produktdateien
          if ($i == 5) {
            Utils::putLog('files are not ready');
          }
          // Fügt Endpunkte für die vorhandenen Produkttypen hinzu
          self::setAPI__products_($product_type);
        }
      }
    }
  }

  public static function init_false()
  {
    self::$is_initialized = false;
  }

  // Erstellt einen API-Endpunkt für den angegebenen Produkttyp
  public static function setAPI__products_($product_type)
  {
    if (!$product_type) {
      // Loggt eine Warnung, falls kein gültiger Produkttyp angegeben wurde
      Utils::putLog('setAPI__products_ could not find proper product type');
      return null;
    } else {
      // Registriert eine REST-API-Route für den Produkttyp
      add_action('rest_api_init', function () use ($product_type) {
        register_rest_route('mec-api/v1', '/products/' . $product_type, array(
          'methods' => 'GET',
          'callback' => [self::class, 'getProductsCallback'],
          'args' => ['product_type' => $product_type], // Übergibt den Produkttyp
          'permission_callback' => '__return_true', // Offener Zugriff auf die API, ggf. anpassen
        ));
      });
    }
  }

  // Callback-Funktion, die Produktdaten für den angeforderten Produkttyp zurückgibt
  // Statische Callback-Methode, die Produktdaten für den angeforderten Produkttyp zurückgibt
  public static function getProductsCallback($request)
  {
    // Ruft die Attribute der Anfrage ab
    $attributes = $request->get_attributes();

    // Extrahiert den Produkttyp aus den Attributen
    $product_type = $attributes['args']['product_type'];

    // Definiert den Dateipfad basierend auf dem Produkttyp
    $file_path = MEC__CP_API_Data_DIR . 'products_' . $product_type . '.json';

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
