have problem with import price
test it with small number of prodCuct. It may possible the problem with language(number by contry setting)??

Install WC again, And check the post_ID
Last edited:28.Dez.2024

## Overview

The [**MEC\_\_CreateProducts**](https://github.com/pondo-1/MEC__CreateProducts) plugin is designed to facilitate the generation of products for the MEC Shop based on external data sourced from Amicron. This plugin automates the process of fetching, organizing, and saving product data in a format compatible with WooCommerce, allowing for efficient management and display of products in an online store.

## Features

1. **Data Retrieval**:
   > **NOTE** This steps need to be modified. At the moment 3 Steps.
   > Amicron --(CTRL)--> mec.pe-dev.de --(mec--generate-products-api, nicht verschlüssel)--> final-mec.pe-dev.de

- The plugin retrieves product data from the external source, specifically from `mec.pe-dev.de`.

  - [More detail](https://rathje-grafik-design.openproject.com/projects/mec-online-shop/wiki/schnittstelle)

- It saves all retrieved articles in a JSON format
  - includes/API/products_all.json

2. **Data Organization**:

- The plugin reorganizes the retrieved data into different product types as products\_{type}.json
- Criteria [Detail](https://rathje-grafik-design.openproject.com/projects/mec-online-shop/wiki/artikel-und-unterartikel#example)

  > **Important**
  > By the Criterial, totoal Products(14773) in to Simple :4814, variable: 1484, variant: 5464 and extra :3011
  > 3011 Products articles are not simple products and Variable/variant products. Es muss abgeklärt werden

3. **Compatibility Filtering**:

- Extract Compatible from Products(simple & Variant) data
  ```diff
  - **Question** Compatible filter for Variable Product or Variant Product??
  For example:
  Variable Product ABCD-M
  Variant Product ABCD-1
  Have these Produces same compatible? if not, what should be show up after filtering
  ```
- Data is not united

  ```diff
  + **Important**  not all the compatible text are same structure.
  M|Yamaha|DT-LC|80|alle|
  vs
  [0] => Failed to insert: A|Yamaha|RMAX 2&amp
  [1] => Failed to insert: 4|1000|2021-2023|
  "29.VSA948-2":M|Yamaha|WR-F|450|2003-2018|,M|Yamaha|YZ-F|450|2003-2018|
  "M|Gas Gas|EC-F|350|2021-2023|Einlass+Auslass": ...
  M|KTM|Supermoto|690|2007-2009|Einlass + Auslass..
  "M|Honda|CRF-R|250|2004-2007|hochverdichtet": [],
  "M|Honda|CRF-R|250|2008-2009|Standard verdichtet": [],...
  "A|Yamaha|YFZ|450|2004-2005|+1,4mm mehr Hub": [],
  "M|Suzuki|T|500|1972-1977|links": [],
  "M|Suzuki|GT|750|1972-1977|links\/mitte": [],....
  "M|Yamaha|RD|500|alle|2x für vordere Zylinder zu verwenden": [],
  "M|Yamaha|RD|500|alle|2x Art.Nr. 8003D-1 für hintere Zylinder zu verwenden": [],

  Soll es eine Liste?
  ```

- The plugin implements a filter function for compatibility, allowing users to filter products based on specific criteria.

4. **API Endpoints**:

- The plugin registers several API endpoints to access the organized product data:
  - `/wp-json/mec-api/v1/products/product_all`
  - `/wp-json/mec-api/v1/products/product_variable`
  - `/wp-json/mec-api/v1/products/product_variant`
  - `/wp-json/mec-api/v1/products/product_simple`
  - `/wp-json/mec-api/v1/products/product_extra`

5. **Admin Interface**:

- Provides an admin option page for managing settings and viewing logs.
- Includes a logger class for tracking actions and errors.

6. **Data Processing**:

- The plugin includes SQL scripts for data processing, ensuring that the data is correctly formatted and stored.

## Directory Structure

The directory structure of the MEC\_\_CreateProducts plugin is as follows:

```
/wp-content/plugins/MEC__CreateProducts
    /assets
        /js
            process-display.js
    /includes
        /Admin
            AdminPage.php
        /API
            SaveToLocal.php
            PrepareJsonLocal.php
            LocalJsonToAPI.php
        /Log
            Logger.php
        /Utils
            Utils.php
            AdminButton.php
            SQLscript.php
    MEC__CreateProducts.php
```

### Directory Breakdown

- **/assets**: Contains static assets such as JavaScript files used for the plugin's frontend functionality.

  - **/js**: JavaScript files for processing and displaying data.

- **/includes**: Contains the core functionality of the plugin.

  - **/Init**: Contains files hooked at early called actions such as Init, Admin_Init
  - `AdminPage.php`: Handles the admin options page.
  - `CLIcommand.php`
  - `CustomDataTabel__Vehicle.php`
  - `Metadata__Compatible.php`
  - `Shortcode__CompatibleTable.php` : Registers Shortcode and prepares for Compatible Table
  - **/API**: Contains files for API interactions and data processing.
  - `SaveToLocal.php`: Saves product data to local JSON files.
  - `PrepareJsonLocal.php`: Prepares the JSON data for use.
  - `LocalJsonToAPI.php`: Registers API endpoints and prepares data for API responses.
  - **/Log**: Contains logging functionality.
  - `Logger.php`: Implements logging for tracking actions and errors.
  - **/Utils**: Contains utility classes and functions.
  - `Utils.php`: Provides shared utility functions.
  - `AdminButton.php`: Helper class for generating buttons in the admin interface.
  - `SQLscript.php`: Contains SQL scripts for data processing.

- **MEC\_\_CreateProducts.php**: The main plugin file that initializes the plugin, sets up constants, and registers hooks and actions.

## Conclusion

The MEC\_\_CreateProducts plugin is a powerful tool for managing product data in WooCommerce, providing a seamless integration with external data sources and a user-friendly interface for administrators. Its structured approach to data organization and API integration makes it an essential component for any MEC Shop looking to enhance its product management capabilities.
