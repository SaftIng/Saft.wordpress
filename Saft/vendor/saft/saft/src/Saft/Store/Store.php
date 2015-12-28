<?php

namespace Saft\Store;

use Saft\Rdf;
use Saft\Rdf\NamedNode;
use Saft\Rdf\Node;
use Saft\Rdf\Statement;
use Saft\Rdf\StatementIterator;
use Saft\Sparql\ResultIterator;
use Saft\Sparql\Result\Result;

/**
 * Declaration of methods that any Store implementation must have, whether its for a Triple- or Quad store.
 *
 * @api
 * @since 0.1
 */
interface Store
{
    /**
     * Adds multiple Statements to (default-) graph.
     *
     * @param StatementIterator|array $statements StatementList instance must contain Statement instances which are
     *                                            'concret-' and not 'pattern'-statements.
     * @param Node                    $graph      Overrides target graph. If set, all statements will be add to that
     *                                            graph, if it is available. (optional)
     * @param array                   $options    Key-value pairs which provide additional introductions for the store
     *                                            and/or its adapter(s). (optional)
     * @api
     * @since 0.1
     */
    public function addStatements($statements, Node $graph = null, array $options = array());

    /**
     * Removes all statements from a (default-) graph which match with given statement.
     *
     * @param Statement $statement It can be either a concrete or pattern-statement.
     * @param Node      $graph     Overrides target graph. If set, all statements will be delete in that
     *                             graph. (optional)
     * @param array     $options   Key-value pairs which provide additional introductions for the store and/or its
     *                             adapter(s). (optional)
     * @api
     * @since 0.1
     */
    public function deleteMatchingStatements(
        Statement $statement,
        Node $graph = null,
        array $options = array()
    );

    /**
     * It gets all statements of a given graph which match the following conditions:
     * - statement's subject is either equal to the subject of the same statement of the graph or
     *   it is null.
     * - statement's predicate is either equal to the predicate of the same statement of the graph or
     *   it is null.
     * - statement's object is either equal to the object of a statement of the graph or it is null.
     *
     * @param Statement $statement It can be either a concrete or pattern-statement.
     * @param Node      $graph     Overrides target graph. If set, you will get all matching statements of
     *                             that graph. (optional)
     * @param array     $options   It contains key-value pairs and should provide additional introductions for
     *                             the store and/or its adapter(s). (optional)
     * @return StatementIterator It contains Statement instances  of all matching statements of the given graph.
     * @api
     * @since 0.1
     */
    public function getMatchingStatements(Statement $statement, Node $graph = null, array $options = array());

    /**
     * Returns true or false depending on whether or not the statements pattern
     * has any matches in the given graph.
     *
     * @param Statement $statement It can be either a concrete or pattern-statement.
     * @param Node      $graph     Overrides target graph. (optional)
     * @param array     $options   It contains key-value pairs and should provide additional introductions for the
     *                             store and/or its adapter(s). (optional)
     * @return boolean Returns true if at least one match was found, false otherwise.
     * @api
     * @since 0.1
     */
    public function hasMatchingStatement(Statement $statement, Node $graph = null, array $options = array());

    /**
     * This method sends a SPARQL query to the store.
     *
     * @param string $query   The SPARQL query to send to the store.
     * @param array  $options It contains key-value pairs and should provide additional introductions for the
     *                        store and/or its adapter(s). (optional)
     * @return Result Returns result of the query. Its type depends on the type of the query.
     * @throws \Exception If query is no string, is malformed or an execution error occured.
     * @api
     * @since 0.1
     */
    public function query($query, array $options = array());

    /**
     * Get information about the store and its features.
     *
     * @return array Array which contains information about the store and its features.
     * @api
     * @since 0.1
     */
    public function getStoreDescription();

    /**
     * Returns a list of all available graph URIs of the store. It can also respect access control,
     * to only returned available graphs in the current context. But that depends on the implementation
     * and can differ.
     *
     * @return array Array with the graph URI as key and a NamedNode as value for each graph.
     * @api
     * @since 0.1
     */
    public function getGraphs();

    /**
     * Create a new graph with the URI given as Node. If the underlying store implementation doesn't
     * support empty graphs this method will have no effect.
     *
     * @param NamedNode $graph   Instance of NamedNode containing the URI of the graph to create.
     * @param array     $options It contains key-value pairs and should provide additional introductions for
     *                           the store and/or its adapter(s).
     * @throws \Exception if given $graph is not a NamedNode.
     * @throws \Exception if the given graph could not be created.
     * @api
     * @since 0.1
     */
    public function createGraph(NamedNode $graph, array $options = array());

    /**
     * Removes the given graph from the store.
     *
     * @param NamedNode $graph   Instance of NamedNode containing the URI of the graph to drop.
     * @param array     $options It contains key-value pairs and should provide additional introductions for
     *                           the store and/or its adapter(s).
     * @throws \Exception if given $graph is not a NamedNode.
     * @throws \Exception if the given graph could not be droped
     * @api
     * @since 0.1
     */
    public function dropGraph(NamedNode $graph, array $options = array());
}
