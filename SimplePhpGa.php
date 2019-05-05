<?php

/**
 * "Simple PHP Google Analytics" API using the Measurement Protocol.
 *
 * This class is intended to make working with the Google Analytics Measurement
 * Protocol easier. Using this class, you can easily register hits in Google
 * Analytics by only initialising the class and providing the minimally required
 * parameters.
 *
 * For usage examples, see example.php in the repository.
 *
 * @author    Murtada al Mousawy <info@murtada.nl>
 * @copyright 2019 Murtada al Mousawy
 * @license   MIT
 * @link      https://github.com/mmousawy/simple-php-ga/
 */

namespace MMousawy;

use RuntimeException;
use UnexpectedValueException;

/**
 * "Simple PHP Google Analytics" API class.
 */
class SimplePhpGa
{
  private $genCid;
  private $host = 'https://www.google-analytics.com';
  private $endpoint = '/collect';
  private $debugEndpoint = '/debug/collect';
  private $params = [
    'v' => 1,
    'tid' => null,
    't' => 'pageview'
  ];

  /**
   * Constructor
   *
   * @param bool $genCid Generate and use a Client ID stored as a cookie at
   *                     the client. When false, you must provide a cid or uid
   *                     in the query options (default: true).
   */
  function __construct(bool $genCid = true)
  {
    $this->genCid = $genCid;
    $genCid && $this->generateCid();
  }

  /**
   * Generates a UUID (v4) for anonymous identification.
   * See http://goo.gl/a8d4RP#cid for details.
   *
   * @param string $data A string of 16 random bytes
   * @return string
   */
  private function guidv4(string $data): string {
    assert(strlen($data) == 16);

    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
  }

  /**
   * Checks if there is a cid stored in a cookie. If missing, create one
   * and store it in a cookie for future use.
   */
  private function generateCid()
  {
    if (!isset($_COOKIE['SimplePhpGa-cid'])) {
      $cid = $this->guidv4(random_bytes(16));
      setcookie('SimplePhpGa-cid', $cid, time() + 6.307e+7, '/');
    } else {
      $cid = $_COOKIE['SimplePhpGa-cid'];
    }

    $this->params['cid'] = $cid;
  }

  /**
   * Validates and prepares the hit (params) for execution.
   *
   * @param array $options The provided params for the hit
   * @return string
   */
  private function prepare(array $options = null): string
  {
    if (!isset($options['tid'])) {
      throw new UnexpectedValueException(
        'Parameter "tid" not provided in options. '
        . 'Your tracking ID should look like: UA-XXXXX-Y. '
        . 'Please see http://goo.gl/a8d4RP#tid for details.'
      );
    }

    if ((!isset($options['cid']) && !isset($options['uid']))
        && !$this->genCid) {
      throw new UnexpectedValueException(
        'Parameter "cid" or "uid" not provided in options. '
        . 'Please see http://goo.gl/a8d4RP#cid for details.');
    }

    return http_build_query(array_merge($this->params, $options));
  }

  /**
   * Executes a hit.
   *
   * @param string $payload The provided payload
   * @param string $endpoint Endpoint of the request
   * @return bool|array
   */
  private function exec(string $payload, string $endpoint)
  {
    $opts = [
      'http' =>[
        'method'  => 'POST',
        'header' => 'Content-type: application/x-www-form-urlencoded',
        'content' => $payload
      ]
    ];

    $uri = $this->host . $endpoint;

    $context = stream_context_create($opts);
    $response = file_get_contents($uri, false, $context);

    if (!$http_response_header[0] === 'HTTP/1.0 200 OK') {
      throw new RuntimeException(
        'Invalid response )'
        . $http_response_header[0] . PHP_EOL
        . ') while requesting '
        . $uri
      );
    }

    if ($endpoint === $this->debugEndpoint) {
      return json_decode($response, true);
    }

    return true;
  }

  /**
   * Prepares and executes a hit.
   *
   * Returns true when hit was successful -- this does guarantee that the
   * hit was valid!
   *
   * @param array $options Params provided for hit.
   * @return bool
   */
  function send(array $options = null)
  {
    $hitString = $this->prepare($options);

    return $this->exec($hitString, $this->endpoint);
  }

  /**
   * Prepares and executes a debug hit.
   *
   * @param array $options Params provided for hit.
   * @return array
   */
  function debug($options = null)
  {
    $queryString = $this->prepare($options);
    $trace = debug_backtrace();

    return [
      'payload' => $queryString,
      'caller' => $trace[0]['file'] . ':' . $trace[0]['line'],
      'response' => $this->exec($queryString, $this->debugEndpoint)
    ];
  }
}
