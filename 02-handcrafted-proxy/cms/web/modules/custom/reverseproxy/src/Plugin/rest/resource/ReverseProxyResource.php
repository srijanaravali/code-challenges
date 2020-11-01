<?php

namespace Drupal\reverseproxy\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\Component\Serialization\Json;

/**
 * Provides a resource to call api endpoint
 *
 * @RestResource(
 *   id = "reverseproxy_resource",
 *   label = @Translation("Reverse Proxy Service"),
 *   uri_paths = {
 *     "create" = "/api/{endpoint}/{format}"
 *   }
 * )
 */
class ReverseProxyResource extends ResourceBase {

  /**
   *
   * @return \Drupal\rest\ResourceResponse
   */
  public function post($endpoint, $format, array $post=[]) {
    $sql = $this->getEndPointData($endpoint);

    // Get target and format value.
    $target = $sql['target'];
    $result_format = $sql['response_format'];

    // Header information
	if ($sql['method_type'] == 'post') {
	  $options = [
	    'headers' => Json::decode($sql['header']),
	    'timeout' => 30,
	  ];
	}
	else {
	  $options = [
	    'timeout' => 30,
	  ];	
	}

    // Get result and convert it to decode and readable.
	$client = \Drupal::httpClient(['base_url' => $target]);
	$response = $client->request($format, $target, $options, $postdata);
    $data = $response->getBody()->getContents();

    // Return result based on format.
    if (isset($format) && $format == 'json') {
      $requestresponse = $data;
    }
    else {
      $requestresponse = Json::decode($data);
    }
	
	return (new ResourceResponse($requestresponse));
  }
  
  public function getEndPointData($endpoint) {
	$target = $endpoint;
	$database = \Drupal\Core\Database\Database::getConnection();
	
	$sql = $database->select('reverseproxy_data', 'proxy')->fields('proxy', [
	  'endpoint',
	  'target',
	  'response_format',
	  'method_type',
	  'header'
	])->condition('proxy.endpoint', '/' . $target, 'LIKE')->execute()->fetchAssoc();
	return $sql;
  }
}
