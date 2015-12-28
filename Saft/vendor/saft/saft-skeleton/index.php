<?php

namespace Saft\Skeleton;

/*
 * Bootstrap the REST interface
 */

require("vendor/autoload.php");

use Saft\Store\Store;
use Saft\Rdf\NodeFactory;
use Saft\Rdf\StatementFactory;
use Saft\Rdf\StatementIteratorFactory;
use Saft\Data\ParserFactory;
use Saft\Data\SerializerFactory;
use Saft\Sparql\Query\QueryFactory;
use Saft\Sparql\Result\ResultFactory;

/*
 * Setup dependencies
 * TODO should be read from a configuration
 */
if (class_exists('\Dice\Rule')) {
    // This is loaded for old dice versions (dev-PHP5.3)
    $rule = new \Dice\Rule();
    $rule->substitutions['Saft\\Rdf\\NodeFactory'] = new \Dice\Instance('Saft\\Rdf\\NodeFactoryImpl');
    $rule->substitutions['Saft\\Rdf\\StatementFactory'] = new \Dice\Instance('Saft\\Rdf\\StatementFactoryImpl');
    $rule->substitutions['Saft\\Rdf\\StatementIteratorFactory'] = new \Dice\Instance('Saft\\Rdf\\StatementIteratorFactoryImpl');
    $rule->substitutions['Saft\\Data\\ParserFactory'] = new \Dice\Instance('Saft\\Addition\\EasyRdf\\Data\\ParserFactoryEasyRdf');
    $rule->substitutions['Saft\\Data\\SerializerFactory'] = new \Dice\Instance('Saft\\Data\\SerializerFactoryImpl');
    $rule->substitutions['Saft\\Sparql\\Query\\QueryFactory'] = new \Dice\Instance('Saft\\Sparql\\Query\\QueryFactoryImpl');
    $rule->substitutions['Saft\\Sparql\\Result\\ResultFactory'] = new \Dice\Instance('Saft\\Sparql\\Result\\ResultFactoryImpl');
} else {
    // This is loaded for new dice versions (after dev-PHP5.3)
    $rule = ['substitutions' => [
        'Saft\\Rdf\\NodeFactory'                => ['instance' => 'Saft\\Rdf\\NodeFactoryImpl'],
        'Saft\\Rdf\\StatementFactory'           => ['instance' => 'Saft\\Rdf\\StatementFactoryImpl'],
        'Saft\\Rdf\\StatementIteratorFactory'   => ['instance' => 'Saft\\Rdf\\StatementIteratorFactoryImpl'],
        'Saft\\Data\\ParserFactory'             => ['instance' => 'Saft\\Addition\\EasyRdf\\Data\\ParserFactoryEasyRdf'],
        'Saft\\Data\\SerializerFactory'         => ['instance' => 'Saft\\Data\\SerializerFactoryImpl'],
        'Saft\\Sparql\\Query\\QueryFactory'     => ['instance' => 'Saft\\Sparql\\Query\\QueryFactoryImpl'],
        'Saft\\Sparql\\Result\\ResultFactory'   => ['instance' => 'Saft\\Sparql\\Result\\ResultFactoryImpl']
    ]];
}

$dice = new \Dice\Dice();
$dice->addRule('*', $rule);

/*
 * Setup store
 * TODO should be read from a configuration
 */
//$params = [['dsn' => 'vos', 'username' => 'dba', 'password' => 'dba']];
//$store = $dice->create("Saft\\Addition\\Virtuoso\\Store\\Virtuoso", $params);
$params = [[
    'username' => "saft",
    'password' => "saft",
    'host' => "localhost",
    'database' => "saft",
    'table-prefix' => "saft",
]];
$store = $dice->create("Saft\\Addition\\ARC2\\Store\\ARC2", $params);

/*
 * Create application to handle the REST requests
 */
$app = $dice->create('Saft\\Skeleton\\Rest\\Application', [$store]);
$app->run();
