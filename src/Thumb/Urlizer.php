<?php

namespace PHPLegends\Thumb;

/**
* @author Wallace de Souza Vizerra <wallacemaxters@gmail.com>
*/

class Urlizer
{

	/**
	* @var string
	*/
	protected $publicPath;

	/**
	* @var string
	*/
	protected $thumbFolder;

	/**
	* @var string
	*/
	protected $relative;


	/**
	* @var string
	*/

	protected $baseUrl = null;

	/**
	* 
	* @param string $relative
	*/
	public function __construct($relative)
	{
		$this->relative = $relative;
	}

	/**
	* @param string $path
	* @return \PHPLegends\Thumb\Urlizer
	*/
	public function setPublicPath($path)
	{
		$this->publicPath = rtrim($path, '/') . '/';

		return $this;
	}

	/**
	* @return string
	*/
	public function getPublicPath()
	{
	    return $this->publicPath;
	}

	/**
	* Build the public filename
	* @param string $image 
	*/
	public function getPublicFilename()
	{
	    return $this->getPublicPath() . $this->relative;
	}

	/**
	* Set directory to use by stores thumb (into public path)
	* @param string $path
	* @return \PHPLegends\ThumbLaravel\Urlizer
	*/
	public function setThumbFolder($path)
	{
	    $this->thumbFolder = trim($path, '/');

	    return $this;
	}

	/**
	* Retrieves the for thumbFolder
	* @return string
	*/
	public function getThumbUrlFolder()
	{
	    return '/' . $this->thumbFolder;
	}

	/**
	* @return string
	*/
	public function getThumbFolder()
	{
		return $this->getPublicPath() . '/' . $this->thumbFolder;
	}

	/**
	* Builds the filename for thumb image
	* 
	* @return string
	*/
	public function buildThumbFilename($filename)
	{
		$extension = pathinfo($filename, PATHINFO_EXTENSION);

		if (! $extension) {

			$filename .= '.' . pathinfo($this->relative, PATHINFO_EXTENSION);
		}

		return $this->getThumbFolder() . '/' . $filename;
	}

	/**
	* Builds the thumb url for image
	* @return string
	*/
	public function buildThumbUrl($filename)
	{
		return $this->getBaseUrl() . $this->getThumbUrlFolder() . '/' . $filename;
	}

	/**
	* @param string $baseUrl
	* @return \PHPLegends\Thumb\Urlizer
	*/
	public function setBaseUrl($baseUrl)
	{
		$this->baseUrl = rtrim($baseUrl, '/');

		return $this;
	}

	/**
	* 
	* @return string
	*/
	public function getBaseUrl()
	{
		return $this->baseUrl;
	}

}