<?php

include __DIR__ . '/../vendor/autoload.php';

use PHPLegends\Thumb\Thumb;

class StaticInterfaceTest extends PHPUnit_Framework_TestCase
{

	public function testCall()
	{

		Thumb::config([
			'public_path' => __DIR__ . '/../test',
			'thumb_folder' => 'thumb.cache',
			'base_uri'     => 'http://localhost:8000/'
		]);

		$img = Thumb::image('test.png', [
			'height' => 12
		]);

		$url = Thumb::url('test.png', 0, 12);
		
		$this->assertEquals(
			'http://localhost:8000/thumb.cache/b40b2331342f86008097b160820b8526.png',
			$url
		);

	}
}