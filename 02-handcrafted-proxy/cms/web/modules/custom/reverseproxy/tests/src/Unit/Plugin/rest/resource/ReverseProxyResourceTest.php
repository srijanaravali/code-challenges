<?php

namespace Drupal\Tests\reverseproxy\Unit;

use \Drupal\Tests\UnitTestCase;
use PHPUnit_Framework_MockObject_MockObject;
use \Drupal\common\CommonService;
use \
Drupal\reverseproxy\Plugin\rest\resource\ReverseProxyResource;

/**
 * Tests the rest api.
 *
 * @group reverseproxy
 */
class ReverseProxyResourceTest extends UnitTestCase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
	$this->mockProxyResource = $this->getMockBuilder(ReverseProxyResource)
	  ->disableOriginalConstructor()
	  ->getMock();
	
	$this->mockProxyResource->expects($this->any())
      ->method('getEndPointData')
      ->willReturn($this->getMockData());
    parent::setUp();
  }

  public function postTest() {
    $data = $this->mockProxyResource->expects($this->any())
      ->method('post')
	  ->with('google_books_ibn', 'get');
	$this->assertEquals(TRUE, isJson($data));
  }
  
  public function getMockData() {
    return [
      'endpoint' => '/google_books_ibn',
      'target' => 'https://www.googleapis.com/books/v1/volumes/?q=ISBN:9780262140874',
      'response_format' => 'json',
      'method_type' => 'get',
      'header' => ''
    ];
  }
}
