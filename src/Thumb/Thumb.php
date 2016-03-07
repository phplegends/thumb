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
        'fallback'     => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNgYPhfDwACggF/yWU3jgAAAABJRU5ErkJggg==',
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

        $this->image = realpath($image);

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
    protected function generateFilename()
    {
        return $this->getOriginDirectory() . '/' . $this->generateBasename();
    }

    protected function generateBasename()
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
    protected function isCacheExpired($destiny)
    {
        $cacheModified = filemtime($destiny);

        if ($this->expiration !== null) {

            return $this->expiration > $cacheModified;
        }

        return filemtime($this->image) > $cacheModified;

    }

    /**
    * @param string $destiny
    * @return void
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
    * Returns the url from thumb based on filename
    * @param string $filename
    * @param float $width
    * @param float $height
    * @return string
    */
    public static function fromFile($filename, $width, $height = 0)
    {
        
        $urlizer = new Urlizer($filename);

        static::configureUrlizer($urlizer);

        // If the filename is not initialized by '/', this is a fullpath
        
        if (strpos($filename, '/') !== 0) {

            $filename = $urlizer->getPublicFilename();
        }

        try {

            $thumb = new static($filename, $width, $height);

        } catch (\InvalidArgumentException $e) {

            if (static::$config['fallback'] === null) {

                throw $e;
            }

            return static::$config['fallback'];
        }

        $basename = $thumb->generateBasename();

        $filename = $urlizer->buildThumbFilename($basename);

        $thumb->save($filename);

        return $urlizer->buildThumbUrl($basename);
    }

    /**
    * Get copy from external file url for make thumb
    * @param string $url
    * @param float $width
    * @param float $height
    */
    public static function fromUrl($url, $width, $height = 0)
    {

        $extension = pathinfo(strtok($url, '?'), PATHINFO_EXTENSION);

        $filename = sprintf('%s/thumb_%s.%s', sys_get_temp_dir(), md5($url), $extension);

        if (! @copy($url, $filename)) {

            return static::$config['fallback'];
        }

        $urlizer = new Urlizer();

        static::configureUrlizer($urlizer);
        
        $thumb = new static($filename, $width, $height);

        $basename = $thumb->generateBasename();

        $filename = $urlizer->buildThumbFilename($basename);

        $thumb->save($filename);

        return $urlizer->buildThumbUrl($basename);
    }

    /**
    * @param string $relative (filename or url)
    * @param float $width
    * @param float $height
    * @return string
    */
    public static function url($relative, $width, $height)
    {

        if (preg_match('/^https?:\/\//i', $relative)) {

            return static::fromUrl($relative, $width, $height);

        } elseif (strlen(parse_url($relative, PHP_URL_QUERY)) > 0) {

            $relative = static::$config['base_uri'] . $relative;

            return static::fromUrl($relative, $width, $height);
        }

        return static::fromFile($relative, $width, $height);
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

    /**
    * @param \PHPLegends\Thumb\Urlizer $urlizer
    * @return void
    */
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