<?php

include __DIR__ . '/../vendor/autoload.php';

use PHPLegends\Thumb\Thumb;

class StaticInterfaceTest extends PHPUnit_Framework_TestCase
{

	public function __construct()
	{
		Thumb::config([
			'public_path'  => __DIR__ . '/../test',
			'thumb_folder' => 'thumb.cache',
			'base_uri'     => 'http://localhost:8000/',
			'fallback'     => 'img/fallback.png'
		]);
	}

	public function testCall()
	{

		$url = Thumb::url('test.png', 0, 12);
		
		$this->assertEquals(
			'http://localhost:8000/thumb.cache/0dd214c0073fe486ebbe39888039c685.png',
			$url
		);

	}

	public function testFallback()
	{

		$url = Thumb::url('no-no-no/non-exists.png', 0, 12);

		$this->assertEquals(
			'img/fallback.png',
			$url
		);
	}

	public function testFullpath()
	{
		$url = Thumb::url('/var/www/thumb/test/img/test-60.png', 0, 12);

		// If detect that file doesnt exists, return the fallback. In this case, doesn't should be the "fallback" url

		$this->assertNotEquals(
			'img/fallback.png',
			$url
		);
	}

	public function testRelativePath()
	{
		$url = Thumb::url('img/test-60.png', 0, 12);

		$this->assertNotEquals(
			'img/fallback.png',
			$url
		);
	}

	public function testExternalUrl()
	{
		$url = Thumb::url('https://www.google.com.br/images/branding/googlelogo/2x/googlelogo_color_272x92dp.png', 0, 20);

		$this->assertContains('http://localhost:8000/', $url);
	}
}