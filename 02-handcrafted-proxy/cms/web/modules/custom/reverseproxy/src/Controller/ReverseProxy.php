<?php

namespace Drupal\reverseproxy\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Component\Serialization\Json;

class ReverseProxy extends ControllerBase {
  
  /**
   * @return endpoint data
   */
  public function listEndPoints() {
	$database = \Drupal\Core\Database\Database::getConnection();
	$result = $database->query('select * from {reverseproxy_data}');

    $header = [
      'deployment' => t('Web Access Url'),
      'endpoint' => t('API Endpoint'),
	  'target' => t('Target'),
	  'format' => t('Response Format'),
	  'action' => t('Action'),
    ];
	
	$rows = [];
	foreach ($result as $record) { 
	  //Actions.
	  $edit_link = Link::createFromRoute($this->t('Edit'), 'reverseproxy.edit_form', ['id' => $record->id], ['absolute' => TRUE]);
	  $delete_link = Link::createFromRoute($this->t('Delete'),'reverseproxy.delete_form', ['id' => $record->id], ['absolute' => TRUE]);

	  $build_link_action = [
	    'action_edit' => [
		  '#type' => 'html_tag',
		  '#value' => $edit_link->toString(),
		  '#tag' => 'div',
		  '#attributes' => ['class' => ['action-edit']]
		],
		'action_delete' => [
		  '#type' => 'html_tag',
		  '#value' => $delete_link->toString(),
		  '#tag' => 'div',
		  '#attributes' => ['class' => ['action-delete']]
		]
	  ];

       $rows[] = [$record->deployment, $record->endpoint, $record->target, $record->response_format, \Drupal::service('renderer')->render($build_link_action)];	
	}

    return [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows
    ];
  }
  
  /**
   * {@inheritdoc}
   * Function to Delete the Endpoint
   */
  function deleteEndPoint() {
	$current_path = \Drupal::service('path.current')->getPath();
    $path_args = explode('/', $current_path);
	$id = $path_args[5];
	$database = \Drupal\Core\Database\Database::getConnection();
	$database->delete('reverseproxy_data')
	  ->condition('id', $id)
	  ->execute();
	$this->messenger()->addStatus($this->t('Endpoint deleted successfully.'));
	return $this->redirect('reverseproxy.list');
  }
  
  /**
   * {@inheritdoc}
   * Function to Access the Proxy Service Url by WebAccess Path
   */
  function callEndpointService() {
	$database = \Drupal\Core\Database\Database::getConnection();
	$current_path = \Drupal::service('path.current')->getPath();
    $path_args = explode('/', $current_path);
	$apiendpoint = $path_args[3];
	$record = $database->select('reverseproxy_data', 'proxy')->fields('proxy', [
		'endpoint',
		'target',
		'response_format',
		'method_type',
		'header'
	])->condition('proxy.deployment', '/' . $apiendpoint, 'LIKE')->execute()->fetchAssoc();

	// Get target and format value.
	$target = $record['target'];
	$format = $record['response_format'];

	if ($record['method_type'] == 'post') {
	  $options = [
	    'headers' => Json::decode($record['header']),
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
	$response = $client->request($record['method_type'], $target, $options);
    $data = $response->getBody()->getContents();
	
	if (isset($format) && $format == 'json') {
      $requestresponse = $data;
    }
    else {
      $requestresponse = Json::decode($data);
    }
	echo '<pre>';
	print_r($requestresponse);
	echo '</pre>';
  }
}