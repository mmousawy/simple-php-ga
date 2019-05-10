"Simple PHP Google Analytics" API using the Measurement Protocol
---
This class is intended to make working with the Google Analytics Measurement
Protocol easier. Using this class, you can easily register hits in Google
Analytics by only initialising the class and providing the minimally required
parameters.

For usage examples, see [example.php](https://github.com/mmousawy/simple-php-ga/blob/master/example.php) or below.

---

**Example 1: Send a normal hit (will show up in Google Analytics):**
```php
$simplePhpGa = new MMousawy\SimplePhpGa();

$result = $simplePhpGa->send([
  // Tracking ID (required; http://goo.gl/a8d4RP#tid)
  'tid' => 'UA-XXXXX-Y',
  // Hit type (required; default: 'pageview'; http://goo.gl/a8d4RP#t)
  't' => 'pageview',
  // Document path (required; http://goo.gl/a8d4RP#dp)
  'dp' => 'test-path'
]);

/**
 * Check if hit was successful.
 * Note: this does guarantee that the hit was valid!
 * Use SimplePhpGa->debug() like in example 2 to check if the hit will be accepted.
 */
if ($result) {
  echo 'Success!';
}
```

**Example 2: Send a debug hit (for validating hits, will not show up in Google Analytics):**
```php
$simplePhpGa = new MMousawy\SimplePhpGa();

$response = $simplePhpGa->debug([
  'tid' => 'UA-XXXXX-Y',
  't' => 'pageview',
  'dp' => 'test'
]);

print_r($response);
```
