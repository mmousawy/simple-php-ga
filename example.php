<?php

/**
 * Two usage examples of the SimplePhpGa class.
 * See http://goo.gl/a8d4RP for more parameters and options.
 */

require 'SimplePhpGa.php';

/**
 * Init the class
 */
$simplePhpGa = new MMousawy\SimplePhpGa();

/**
 * Example 1: Send a normal hit (will show up in Google Analytics).
 */
$result = $simplePhpGa->send([
  // Tracking ID (required; http://goo.gl/a8d4RP#tid)
  'tid' => 'UA-XXXXX-Y',
  // Hit type (required; default: 'pageview'; http://goo.gl/a8d4RP#t)
  't' => 'pageview',
  // Document path (required; http://goo.gl/a8d4RP#dp)
  'dp' => 'test-path'
]);

/**
 * Check if the hit was successful.
 * Note: this does guarantee that the hit was valid! Use SimplePhpGa->debug()
 * like in example 2 below to check if the hit will be accepted.
 */
if ($result) {
  echo 'Success!' . PHP_EOL;
}

/**
 * Example 2: Send a debug hit (for validating hits, will not show up in
 * Google Analytics).
 */
$response = $simplePhpGa->debug([
  'tid' => 'UA-XXXXX-Y',
  't' => 'pageview',
  'dp' => 'test'
]);

print_r($response);
