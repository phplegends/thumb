<?php

namespace PHPLegends\Thumb;

use Gregwar\Image\Image as GregwarImage;

/**
* @author Wallace de Souza Vizerra <wallacemaxters@gmail.com>
*/
class Thumb
{

    protected static $config = [
        'base_uri'     => null,
        'public_path'  => null,
        'thumb_folder' => '_thumbs',
    ];

    /**
    * Image to resize
    * @var string
    */
    protected $image;

    /**
    * @var float
    */
    protected $height;

    /**
    * @var float
    */
    protected $width;

    /**
    * @var string|null
    */
    protected $expiration = null;


    /**
    * @param string $image
    * @param float $width
    * @param float|null $height
    * @return void
    */
    public function __construct($image, $width, $height = null)
    {
        if (! file_exists($image)) {

            throw new \InvalidArgumentException("The file {$image} does not exists");

        }

        $this->image = $image;

        $this->height = $height;

        $this->width = $width;
    }

    /**
    * @param string|null $destiny
    * @return \SplFileObject
    */
    public function save($destiny = null)
    {

        if ($destiny === null) {

            $destiny = $this->generateFilename();
        }

        $extension = pathinfo($destiny, PATHINFO_EXTENSION);

        $this->prepareDestiny($destiny);

        GregwarImage::open($this->image)
                    ->resize($this->width, $this->height, 0XFFFFFF, true)
                    ->save($destiny, $extension);

        return new \SplFileObject($destiny, 'r');
    }

    /**
    * @param string|null $filename
    * @return \SplFileObject
    */
    public function getCache($destiny = null)
    {

        if (! file_exists($destiny) || $this->isCacheExpired($destiny)) {

            return $this->save($destiny);
        }

        return new \SplFileObject($destiny, 'r');
    }

    /**
    * Set cache expiration in seconds
    * @param string $seconds
    * @return \PHPLegends\Thumb\Thumb
    */
    public function setCacheExpiration($seconds)
    {
        $this->expiration = strtotime(sprintf('- %d seconds', $seconds));

        return $this;
    }

    /**
    * @return int|null
    */
    public function getCacheExpiration()
    {
        return $this->expiration;
    }

    /**
    * Generate a filename
    * @return string
    */
    public function generateFilename()
    {
        return $this->getOriginDirectory() . '/' . $this->generateBasename();
    }

    public function generateBasename()
    {
        $filename = md5($this->image . $this->height . $this->width);

        $extension = pathinfo($this->image, PATHINFO_EXTENSION);

        return $filename . '.' . $extension;   
    }

    /**
    * Is Expired cache of image?
    * @param string $destiny
    * @return boolean
    */
    public function isCacheExpired($destiny)
    {
        $cacheModified = filemtime($destiny);

        if ($this->expiration !== null) {

            return $this->expiration > $cacheModified;
        }

        return filemtime($this->image) > $cacheModified;

    }

    /**
    * @param string $destiny
    */
    protected function prepareDestiny($destiny)
    {
    	$directory = dirname($destiny);

    	if (! is_dir($directory)) {
    		
    		if (! @mkdir($directory, 0777, true)) {

    			throw new \RuntimeException(
                    "Unable to make directory {$directory}"
                );
    		}
    	}
    }

    /**
    * Get origin directory of image
    * @return string
    */
    protected function getOriginDirectory()
    {
        return dirname($this->image);
    }

    /**
    * @param string $image
    * @param float $width
    * @param float|null $height
    * @return \PHPLegends\Assets\ImageResizer
    */
    public static function create($file, $width, $height = null)
    {
        return new self($file, $width, $height);
    }

    /**
    * 
    * @param string $relative
    * @param float $width
    * @param float $height
    * @return string
    */
    public static function url($relative, $width, $height = 0)
    {
       
        $urlizer = new Urlizer($relative);

        if (isset(static::$config['public_path'])) {
            $urlizer->setPublicPath(static::$config['public_path']);
        }

        if (isset(static::$config['base_uri'])) {
            $urlizer->setBaseUrl(static::$config['base_uri']);
        }

        $urlizer->setThumbFolder(static::$config['thumb_folder']);

        $thumb = new static($urlizer->getPublicFilename(), $width, $height);

        $basename = $thumb->generateBasename();

        $filename = $urlizer->buildThumbFilename($basename);

        $thumb->save($filename);

        return $urlizer->buildThumbUrl($basename);
    }

    /**
    * @param string $relative
    * @param array $attributes
    * @return string
    */
    public static function image($relative, array $attributes = [])
    {
        $attributes += ['alt' => null, 'height' => 0, 'width' => 0];

        $height = $attributes['height'];

        $width = $attributes['width'];

        $attributes['src'] = static::url($relative, $width, $height);

        $attrs = [];

        foreach ($attributes as $name => $attr) {

            $attrs[] = "$name=\"{$attr}\"";
        }

        $attrs = implode(' ', $attrs);

        return "<img {$attrs} />";

    }

    /**
    * @static
    * @param array $config
    * @return void
    */
    public static function config(array $config)
    {
        static::$config = array_merge(static::$config, $config);
    }

}