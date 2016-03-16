<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Caching\Storages;

use Nette;
use Nette\Caching\Cache;


/**
 * Elasticsearch storage.
 */
class ElasticsearchStorage extends Nette\Object implements Nette\Caching\IStorage
{

	private $client;

	private $index;

	/**
	 * Checks if MongoDB extension is available.
	 * @return bool
	 */
	public static function isAvailable()
	{
		//return extension_loaded('apc');
		return true;
	}


	public function __construct( $host = "localhost", $port = 9200 )
	{
		/*if (!static::isAvailable()) {
			throw new Nette\NotSupportedException("PHP extension 'apc' is not loaded.");
		}*/
		$this->client = new \Elastica\Client(array(
		    'host' => $host,
		    'port' => $port
		));

		$this->index = $this->client->getIndex('cache');

		if ( ! $this->index->exists() ) {

			$this->index->create(array(
		    /*"mappings" => array(
		        "url" => array(
		            "properties" => array(
		                "key" => array(
		                    "type" => "string",
		                    "index" => "not_analyzed"
		                )
		            )
		        )
		    )*/
		    ), true);

		}

		
	}

	/**
	 * Read from cache.
	 * @param  string key
	 * @return mixed|NULL
	 */
	public function read($key)
	{

		// TODO: expire & slide. ...

		$query = array(
		    'query' => array(
		        'term' => array(
		            '_id' => $key
		        )
		    )
		);
		
		$type = $this->index->getType('cache');
		$path = $this->index->getName() . '/' . $type->getName() . '/_search';

		$response = $this->client->request($path, \Elastica\Request::GET, $query);
		$responseArray = $response->getData();

		if ( $responseArray["hits"]["total"] == 0 ) {
		    $data = NULL;
		} else {
			$data = $responseArray["hits"]["hits"][0]["_source"]["data"];
		}
		//var_dump( urlencode($key) );
		
		return $data;
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

		$type = $this->index->getType('cache');

		$type->addDocument(new \Elastica\Document($key, array('data' => $data, 'expire' => $expire, 'slide' => $slide )));
		$this->index->refresh();


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
