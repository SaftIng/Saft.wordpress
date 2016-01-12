# Saft.wordpress

This plugin contains the Saft library (+ vendors) and provides a Wordpress integration. The Saft library is the aim to build a collection of components which helps anyone who wants to create applications by using Semantic Web technology.

The purpose of this plugin, which can also be integrated using composer, is, to help you using Saft inside of WordPress. To do that, a database connection will be setup for instance.

## Getting started

### Installation

#### Manually

1. Download the [zip](https://github.com/SaftIng/Saft.wordpress/archive/master.zip)
2. Extract it
3. Rename the extracted folder from Saft.wordpress-master to Saft.wordpress
4. Move it into your plugins folder of Wordpress (usually wp-content/plugins)
5. Go into the admin area and activate that plugin

#### Composer

Require `saft/saft-wordpress` via composer and it will set it up for you.

### Setup

#### Running as WordPress plugin

If you use it as a standalone WordPress plugin, you have to activate the plugin in the admin area after copying it into `wp-content/plugins`, to be able to use Saft classes inside your application.

#### Integrated via composer in another plugin

You need to include the `Saft.wordpress.php` file into your plugin/project. It contains all integration-related code. You are free to copy the required code from it into another file of yours, make adaptions, ... but keep in mind, that further updated may change essential parts of the code, so you need to keep up. We think a simply `require` of the mentioned file should be fine.

### First steps

We assume you have a running Saft integration. Now, we want to show you a quick example, so that you can see, if your ARC2-store has access to the active WordPress database and you can query it.

The following method contains code to:
* create test graph
* add a triple to it
* query the graph
* var_dump query result

When you call `foo`, it should do everything in the list.

```<?php
function foo()
{
    // important, to make $saftdb know inside the function
    global $saftdb;

    // create test graph inside the store
    $testGraph = new NamedNodeImpl('http://foo/');
    $saftdb->createGraph($testGraph);

    // test triple, create it only in the memory
    $subject = new NamedNodeImpl('http://saft/testtriple/s');
    $predicate = new NamedNodeImpl('http://saft/testtriple/p');
    $object = new NamedNodeImpl('http://saft/testtriple/o');
    $triple = new StatementImpl($subject, $predicate, $object);

    // add test triple to store
    $saftdb->addStatements(array($triple), $testGraph);

    // query our test graph to ask for a list of all triples
    $result = $saftdb->query('SELECT * FROM <'. $testGraph->getUri() .'> WHERE {?s ?p ?o.}');

    var_dump($result);
}
```

The `var_dump` should output something like the following:

```
object(Saft\Sparql\Result\SetResultImpl)[183]

    array (size=3)
      's' =>
        object(Saft\Rdf\NamedNodeImpl)[178]
          protected 'uri' => string 'http://saft/testtriple/s' (length=24)
      'p' =>
        object(Saft\Rdf\NamedNodeImpl)[180]
          protected 'uri' => string 'http://saft/testtriple/p' (length=24)
      'o' =>
        object(Saft\Rdf\NamedNodeImpl)[181]
          protected 'uri' => string 'http://saft/testtriple/o' (length=24)
```

## Misc

### Update Saft components

You can use `Makefile` and call `make update` on the terminal to force an erease of all Saft related files and a new download of the latest stable version of Saft plus related vendors. That is helpful for us as developers to update the plugin for you. But it may also be useful to you, if you have certain requirements how to update Saft.
