<?php

include __DIR__ . '/../vendor/autoload.php';

use PHPLegends\Thumb\Thumb;

class ResizerTest extends PHPUnit_Framework_TestCase
{
	public  function __construct()
	{

	}

	public function _testResize()
	{
		$resizer = new Thumb(__DIR__ . '/../test/test.png', 50);

		$resizer->save($destiny = __DIR__ . '/../test/img/test-50.png');

		$this->assertTrue(file_exists($destiny), 'File doesnt exists');
	}

	public function _testCacheExpiration()
	{
		$resizer = new Thumb(__DIR__ . '/../test/test.png', 50);

		$resizer->setCacheExpiration(10);

		$destiny = __DIR__ . '/../test/img/test-50.png';

		var_dump($resizer->getCacheExpiration(), filemtime($destiny));

		$this->assertTrue(
		 	$resizer->isCacheExpired($destiny)
		 );

		$resizer->getCache();
	}


	public function testUrl()
	{


		$url = Thumb::create(__DIR__ . '/../test/test.png', 50)
					->urlize('img/test-60.png', 'http://localhost:8000/test');


		$this->assertEquals(
			'http://localhost:8000/test/img/test-60.png',
			$url
		);

	}
}