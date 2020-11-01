<?php

namespace Drupal\reverseproxy\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Configure ReverseproxyForm settings for this site.
 */
class ReverseProxy extends FormBase {

  /** 
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'reverseproxy_admin_settings';
  }

  /** 
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
	$data = ['deployment'=>'', 'endpoint'=>'', 'target'=>'', 'response_format'=>'', 'method_type'=>'', 'header'=>''];
    $current_path = \Drupal::service('path.current')->getPath();
    $path_args = explode('/', $current_path);
	$id = $path_args[5];
	
	if ($id >0 ) {
	  $database = \Drupal\Core\Database\Database::getConnection();
	  $result = $database->query("select * from {reverseproxy_data} where id= :id", [':id' => $id]);
	  $data = $result->fetchAssoc();
	}
	
	$base_path = Url::fromRoute('<front>', [], ['absolute' => TRUE])->toString();
	
	$form['id'] = [
      '#type' => 'hidden',
      '#title' => '',
      '#default_value' => intval($id)
    ]; 
	
    $form['deployment_request'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Proxy Web Access URL'),
	  '#description'   => 'Web Access Url would start with ' . $base_path . 'reverseproxy/accessweb',
	  '#default_value' => $data['deployment'],
	  '#required'      => TRUE,
    ];  

    $form['endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Endpoint'),
	  '#description'   => 'API Url should start with ' . $base_path . 'api',
	  '#default_value' => $data['endpoint']
    ];
	
	$form['target'] = [
	  '#title'         => t('Target URL'),
      '#type'          => 'textfield',
      '#description'   => 'Target value URL Access e.g. www.google.books/api/v1/value',
      '#default_value' => $data['target'],
	  '#required'      => TRUE,
    ];

	$setting_formatter['formatters'] = [
	  'json'    => 'json',
	  'array'   => 'array',
	  'xml'     => 'xml',
	];

	$form['formatters'] = [
      '#type'          => 'radios',
      '#title'         => t('Response formatters'),
      '#required'      => TRUE,
      '#options'       => $setting_formatter['formatters'],
      '#tree'          => TRUE,
      '#description'   => t('Select the response formats you want to enable for the rest server.'),
      '#default_value' => $data['response_format']
    ];

    $form['formatters_method_type'] = [
      '#type'          => 'radios',
      '#title'         => t('Response formatters'),
      '#required'      => TRUE,
      '#options'       => [
        'get'  => t('Get'),
        'post' => t('Post'),
      ],
      '#description'   => t('Select the response formats you want to enable for the rest server.'),
      '#default_value' => $data['method_type'],
    ];

    $form['header'] = [
      '#type'          => 'textarea',
      '#title'         => t('Post Service Header'),
      '#description'   => t('Add Header information in json format e.g. {"username":"ppaliya"}'),
      '#states'        => [
        'visible' => [
          ':input[name="formatters_method_type"]' => ['value' => 'post'],
        ],
      ],
	  '#default_value' => $data['header'],
    ];
	
	$form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#name' => 'delete',
        '#value' => t('Save'),
      ]
    ];
    return $form;
  }

  /** 
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
	$connection = \Drupal\Core\Database\Database::getConnection();
    
	if ($form_state->getValue('id') == 0) {
	  $connection->insert('reverseproxy_data')
		->fields([
			'deployment' => $form_state->getValue('deployment_request'),
			'endpoint' => $form_state->getValue('endpoint'),
			'target' => $form_state->getValue('target'),
			'response_format' => $form_state->getValue('formatters'),
			'method_type' => $form_state->getValue('formatters_method_type'),
			'header' => $form_state->getValue('header')
		])
		->execute();
	  $this->messenger()->addStatus($this->t('Endpoint Added successfully.'));
	}
	else {
	  $connection->update('reverseproxy_data')
	    ->fields([
		  'deployment' => $form_state->getValue('deployment_request'),
		  'endpoint' => $form_state->getValue('endpoint'),
		  'target' => $form_state->getValue('target'),
		  'response_format' => $form_state->getValue('formatters'),
		  'method_type' => $form_state->getValue('formatters_method_type'),
		  'header' => $form_state->getValue('header')
		])
		->condition('id', $form_state->getValue('id'))
		->execute();
	  $this->messenger()->addStatus($this->t('Endpoint Updated successfully.'));
	}

    $form_state->setRedirect('reverseproxy.list');
  }

}