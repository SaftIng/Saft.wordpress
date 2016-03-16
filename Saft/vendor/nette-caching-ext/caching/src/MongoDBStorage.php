<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Caching\Storages;

use Nette;
use Nette\Caching\Cache;


/**
 * MongoDB storage.
 */
class MongoDBStorage extends Nette\Object implements Nette\Caching\IStorage
{
	/** @var \MongoClient */
	private $mongo;

	/** @var \MongoDB */
	private $db;

	/**
	 * Checks if MongoDB extension is available.
	 * @return bool
	 */
	public static function isAvailable()
	{
		return extension_loaded('mongo');
	}


	public function __construct($host = 'localhost', $port = 27017)
	{
		if (!static::isAvailable()) {
			throw new Nette\NotSupportedException("PHP extension 'mongo' is not loaded.");
		}

		$this->mongo = new \Mongo("mongodb://".$host.":".$port);
		$this->db = $this->mongo->db;
	}


	/**
	 * @return \Mongo
	 */
	public function getConnection()
	{
		return $this->mongo;
	}


	/**
	 * Read from cache.
	 * @param  string key
	 * @return mixed|NULL
	 */
	public function read($key)
	{
		//$key = urlencode($key);
		$data = $this->db->cache->findOne( array('key' => $key ) );
		// TODO: expire & slide. ...
		return unserialize($data['data']);
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
		
		//$this->collection->insert( [ 'key' => $key, 'data' => serialize($data) ] );
		$this->db->cache->update( 
			array('key' => $key ),
			array(
				'key' => $key,
				'data' => serialize($data),
				'expire' => $expire,
				'slide' => $slide
			) ,
			array('upsert' => true )
		);

		/*
		// TODO
		if (!empty($dependencies[Cache::TAGS])) {
			foreach ((array) $dependencies[Cache::TAGS] as $tag) {
				$arr[] = $key;
				$arr[] = $tag;
			}


			$this->pdo->prepare('INSERT INTO tags (key, tag) SELECT ?, ?' . str_repeat('UNION SELECT ?, ?', count($arr) / 2 - 1))
				->execute($arr);
		}*/
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
