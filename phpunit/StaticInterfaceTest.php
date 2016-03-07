<?php

include __DIR__ . '/../vendor/autoload.php';

use PHPLegends\Thumb\Thumb;

class StaticInterfaceTest extends PHPUnit_Framework_TestCase
{

	public function __construct()
	{

		Thumb::config([
			'public_path' => __DIR__ . '/../test',
			'thumb_folder' => 'thumb.cache',
			'base_uri'     => 'http://localhost:8000/'
		]);

	}

	public function testFullpath()
	{
		$url = Thumb::url(__DIR__ . '/../test/img/test-50.png', 0, 20);

		$this->assertNotEquals('img/fallback.png', $url, 'Falhou. É igual ao "fallback"');
	}

	public function testRelativePath()
	{
		$url = Thumb::url('img/test-50.png', 0, 20);
		
		$this->assertNotEquals('img/fallback.png', $url, 'Falhou. É igual ao "fallback"');	
	}

	public function testFallback()
	{
		$url = Thumb::url('non-exists/non-exists.png', 0, 20, '/img/fallback.png');
		
		$this->assertEquals('/img/fallback.png', $url, 'Falhou. Deveria ser ambos iguais');	
	}

	public function testUrlQuery()
	{
		$url = Thumb::url('https://i.ytimg.com/vi/PAKCgvprpQ8/maxresdefault.jpg', 0, 12);
	}
}