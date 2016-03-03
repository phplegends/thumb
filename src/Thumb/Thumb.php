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
    public static function fromFile($relativeFilename, $width, $height = 0, $fallback = null)
    {
        
        $urlizer = new Urlizer($relativeFilename);

        static::configureUrlizer($urlizer);

        $filename = $urlizer->getPublicFilename();

        try {

            $thumb = new static($filename, $width, $height);

        } catch (\InvalidArgumentException $e) {

            if ($fallback === null) {

                throw $e;
            }

            return $fallback;
        }

        $basename = $thumb->generateBasename();

        $filename = $urlizer->buildThumbFilename($basename);

        $thumb->save($filename);

        return $urlizer->buildThumbUrl($basename);
    }

    /**
    * Get copy from external file url for make thumb
    */
    public static function fromUrl($url, $width, $height, $fallback = null)
    {

        $extension = pathinfo($url, PATHINFO_EXTENSION);

        $filename = sprintf('%s/thumb_%s.%s', sys_get_temp_dir(), md5($url), $extension);

        if (! @copy($url, $filename)) {

            return $fallback;
        }

        $urlizer = new Urlizer();

        static::configureUrlizer($urlizer);
        
        $thumb = new static($filename, $width, $height);

        $basename = $thumb->generateBasename();

        $filename = $urlizer->buildThumbFilename($basename);

        $thumb->save($filename);

        return $urlizer->buildThumbUrl($basename);
    }


    public static function url($relative, $width, $height, $fallback = null)
    {
        if (preg_match('/^https?:\/\//i', $relative)) {

            return static::fromUrl($relative, $width, $height, $fallback);
        }

        return static::fromFile($relative, $width, $height, $fallback);
    }

    /**
    * @param string $relative
    * @param array $attributes
    * @return string
    */
    public static function image($relative, array $attributes = [])
    {
        $attributes += ['alt' => null];

        $height = isset($attributes['height']) ? $attributes['height'] : 0;

        $width = isset($attributes['width']) ? $attributes['width'] : 0;

        $url = static::url($relative, $width, $height);
        
        $attributes['src'] = $url;

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


    protected static function configureUrlizer(Urlizer $urlizer)
    {

        $path = isset(static::$config['public_path']) ? static::$config['public_path'] : $_SERVER['DOCUMENT_ROOT'];

        $urlizer->setPublicPath($path);
    
        if (isset(static::$config['base_uri'])) {
            $urlizer->setBaseUrl(static::$config['base_uri']);
        }

        $urlizer->setThumbFolder(static::$config['thumb_folder']);
    }

}