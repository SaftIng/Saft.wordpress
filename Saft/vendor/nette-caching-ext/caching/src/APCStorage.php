<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Caching\Storages;

use Nette;
use Nette\Caching\Cache;


/**
 * APC storage.
 */
class APCStorage extends Nette\Object implements Nette\Caching\IStorage
{
	/** @var String ("apc|apcu") */
	private static $usedExtension;

	public function __construct()
	{
		if (static::isAvailable('apcu')) {
			static::$usedExtension = "apcu";
		} elseif (static::isAvailable('apc')) {
			static::$usedExtension = "apc";
		} else {
			throw new Nette\NotSupportedException("Neither 'apcu' nor 'apc' as PHP extension is available.");
		}
	}

	/**
	 * Checks if MongoDB extension is available.
	 * @param string extension (apc|apcu)
	 * @return bool
	 */
	public static function isAvailable($ext='apc')
	{
		return extension_loaded($ext);
	}

	/**
	 * Get used extension (APCu or APC)
	 * @return string (apcu|apc)
	 */
	public static function getUsedExtension()
	{
		return static::$usedExtension;
	}

	/**
	 * Read from cache.
	 * @param  string key
	 * @return mixed|NULL
	 */
	public function read($key)
	{

		$data = ( static::$usedExtension == "apcu" ) ? apcu_fetch($key) : apc_fetch($key);

		// TODO: expire & slide. ...
		
		return $data['data'];
	}


	/**
	 * Prevents item reading and writing. Lock is released by write() or remove().
	 * @param  string key
	 * @return void
	 */
	public function lock($key)
	{
	}


	/**
	 * Writes item into the cache.
	 * @param  string key
	 * @param  mixed  data
	 * @param  array  dependencies
	 * @return void
	 */
	public function write($key, $data, array $dependencies)
	{
		$expire = isset($dependencies[Cache::EXPIRATION]) ? $dependencies[Cache::EXPIRATION] + time() : NULL;
		$slide = isset($dependencies[Cache::SLIDING]) ? $dependencies[Cache::EXPIRATION] : NULL;

		$data = array(
			'data' => $data,
			'expire' => $expire,
			'slide' => $slide
		);
		if ( static::$usedExtension == "apcu" ) {
			apcu_store($key, $data);
		} else {
			apc_store($key, $data);
		}

		// TODO: tags
	}


	/**
	 * Removes item from the cache.
	 * @param  string key
	 * @return void
	 */
	public function remove($key)
	{
		
	}


	/**
	 * Removes items from the cache by conditions & garbage collector.
	 * @param  array  conditions
	 * @return void
	 */
	public function clean(array $conditions)
	{
		
	}

}
