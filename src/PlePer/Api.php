<?php

namespace PlePer;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Exception;

/**
 * Class Api
 *
 * @package PlePer
 */
class Api {

      const ENDPOINT = 'https://pleper.com/api/v1';
      const HTTP_POST = 'post';
      const HTTP_GET = 'get';

      /** @var string */
      protected $endpoint;

      /** @var string */
      protected $apiKey;

      /** @var object */
      protected $guzzleClient;

      /** @var array */
      protected $allowedHttpMethods = array(
          self::HTTP_POST,
          self::HTTP_GET,
      );

      /**
       * @param string $apiKey
       * @param string $apiSecret
       * @param string $endpoint
       */
      public function __construct($apiKey, $endpoint = '') {
            $this->endpoint = empty($endpoint) ? static::ENDPOINT : $endpoint;
            $this->apiKey = $apiKey;
      }

      /**
       * @param string $method
       * @param array $params
       * @param string $httpMethod
       * @throws \Exception
       * @return bool|mixed
       */
      public function call($method, $params = array(), $httpMethod = self::HTTP_POST) {

            if ( !in_array($httpMethod, $this->allowedHttpMethods) ) {
                  throw new \Exception('Invalid HTTP method specified.');
            }
            $params = array_merge(array(
                'api-key' => $this->apiKey,
                    ), $params);

            //Reuse the guzzle client
            if ( isset($this->guzzleClient) && $this->guzzleClient instanceof Client ) {
                  $client = $this->guzzleClient;
            }
            else {
                  $client = new Client();
                  $this->guzzleClient = $client;
            }

            try {
                  if ( $httpMethod === static::HTTP_GET ) {
                        $result = $client->get($this->endpoint . $method, array('query' => $params));
                  }
                  else {
                        $result = $client->$httpMethod($this->endpoint . $method, array('body' => $params));
                  }
            } catch (RequestException $e) {
                  $result = $e->getResponse();
            }
            return $result->json();
      }

      /**
       * @param string $method
       * @param array $params
       * @return bool|mixed
       */
      public function get($method, $params = array()) {
            return $this->call($method, $params, static::HTTP_GET);
      }

      /**
       * @param string $method
       * @param array $params
       * @return bool|mixed
       */
      public function post($method, $params = array()) {
            return $this->call($method, $params, static::HTTP_POST);
      }

      /**
       * @param bool $stopOnJobError
       * @return bool|int
       */
      public function batch_create() {
            $result = $this->call('/batch_create');
            return $result['success'] ? $result['batch-id'] : false;
      }

      /**
       * @param int $batch_id
       * @return bool
       */
      public function batch_commit($batch_id) {
            $result = $this->call('/batch_commit', array(
                'batch-id' => $batch_id
                    ), self::HTTP_POST);
            return $result['success'];
      }

      /**
       * @param int $batch_id
       * @return mixed
       */
      public function batch_get_results($batch_id) {
            return $this->call('/batch_get_results', array(
                        'batch-id' => $batch_id
                            ), self::HTTP_GET);
      }

      /**
       * @param int $batch_id
       * @return bool
       */
      public function batch_delete($batch_id) {
            $results = $this->call('/batch_delete', array(
                'batch-id' => $batch_id
                    ), self::HTTP_GET);
            return $results['success'];
      }

}
