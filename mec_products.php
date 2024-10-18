<?php
/*
Plugin Name: MEC--creates-products plugin 
Description: Generates products for MEC Shop, based on the external data(mec.pe-dev.de)
Version: 1.1
Author: Page-effect

/wp-content/plugins/mec--creates-products
    /assets
        /js
            process-display.js
    /includes
        /Admin
            AdminPage.php
        /API
            APIHandler.php
        /Product
            ProductGenerator.php
        /Log
            Logger.php
    mec--creates-products.php

*/


if (!defined('ABSPATH')) die('No direct access allowed');

define('MEC__CP_DIR', dirname(__FILE__));
define('MEC__CP_URL', plugins_url('', __FILE__));
define('MEC__CP_PLUGIN_SLUG', plugin_basename(__FILE__));

// log: Set global log instance for this plugin
//require_once(MEC__CP_DIR . '/' . 'class_MEC_Products.php');
//$instance_MEC_Products = new MEC_Products();

//Log 
//log class
require_once MEC__CP_DIR . '/Log/logger.class.php';
require_once(MEC__CP_DIR . '/' . 'class_MEC_Products.php');
$instance_MEC_Products = new MEC_Products();
