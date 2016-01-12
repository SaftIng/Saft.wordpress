<?php

/**
 * Plugin Name: Saft.wordpress
 * Plugin URI:  https://github.com/SaftIng/Saft.wordpress
 * Description: This plugin contains the Saft library (+ vendors) and provides a Wordpress integration. The Saft library is the aim to build a collection of components which helps anyone who wants to create applications by using Semantic Web technology.
 * Version:     0.1.0-beta4
 * Author:      Konrad Abicht
 * Author URI:  http://inspirito.de
 * License:     GPL3
 */

use Saft\Addition\ARC2\Store\ARC2;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;
use Saft\Sparql\Query\QueryFactoryImpl;
use Saft\Sparql\Result\ResultFactoryImpl;

// include autoloaders for Saft and related vendors
require_once __DIR__ .'/Saft/vendor/autoload.php';

/*
 * Initialize ARC2-instance and set it up, so that it can use current WordPress
 * database to store its tables.
 */
global $saftdb, $wpdb;
$saftdb = new ARC2(
    new NodeFactoryImpl(),
    new StatementFactoryImpl(),
    new QueryFactoryImpl(),
    new ResultFactoryImpl(),
    new StatementIteratorFactoryImpl(),
    array(
        'username'      => $wpdb->dbuser,
        'password'      => $wpdb->dbpassword,
        'host'          => $wpdb->dbhost,
        'database'      => $wpdb->dbname,
        'table-prefix'  => 'saft_', // prefix of ARC2/Saft related tables
    )
);
