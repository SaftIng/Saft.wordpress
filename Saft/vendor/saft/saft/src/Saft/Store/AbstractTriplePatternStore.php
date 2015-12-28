<?php

namespace Saft\Store;

use Saft\Rdf\NodeFactory;
use Saft\Rdf\Statement;
use Saft\Rdf\StatementFactory;
use Saft\Rdf\StatementIterator;
use Saft\Rdf\StatementIteratorFactory;
use Saft\Sparql\Query\AbstractQuery;
use Saft\Sparql\Query\Query;
use Saft\Sparql\Query\QueryFactory;
use Saft\Sparql\Query\QueryUtils;

/**
 * Predefined Pattern-statement Store. The Triple-methods need to be implemented in the specific statement-store.
 * The query method is defined in the abstract class and reroute to the triple-methods.
 * @api
 * @since 0.1
 */
abstract class AbstractTriplePatternStore implements Store
{
    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * @var StatementFactory
     */
    private $statementFactory;

    /**
     * @var StatementIteratorFactory
     */
    private $statementIteratorFactory;

    /**
     * Constructor.
     *
     * @param NodeFactory              $nodeFactory Instance of NodeFactory.
     * @param StatementFactory         $statementFactory Instance of StatementFactory.
     * @param QueryFactory             $queryFactory Instance of QueryFactory.
     * @param StatementIteratorFactory $statementIteratorFactory Instance of StatementIteratorFactory.
     * @api
     * @since 0.1
     */
    public function __construct(
        NodeFactory $nodeFactory,
        StatementFactory $statementFactory,
        QueryFactory $queryFactory,
        StatementIteratorFactory $statementIteratorFactory
    ) {
        $this->nodeFactory = $nodeFactory;
        $this->queryFactory = $queryFactory;
        $this->statementFactory = $statementFactory;
        $this->statementIteratorFactory = $statementIteratorFactory;

        $this->queryUtils = new QueryUtils();
    }

    /**
     * This method sends a SPARQL query to the store.
     *
     * @param  string     $query            The SPARQL query to send to the store.
     * @param  array      $options optional It contains key-value pairs and should provide additional
     *                                      introductions for the store and/or its adapter(s).
     * @return Result     Returns result of the query. Its type depends on the type of the query.
     * @throws \Exception If query is no string, is malformed or an execution error occured.
     * @api
     * @since 0.1
     */
    public function query($query, array $options = array())
    {
        $queryObject = $this->queryFactory->createInstanceByQueryString($query);

        if ('updateQuery' == $this->queryUtils->getQueryType($query)) {
            /*
             * INSERT or DELETE query
             */
            $firstPart = substr($queryObject->getSubType(), 0, 6);

            // DELETE DATA query
            if ('delete' == $firstPart) {
                $statements = $this->getStatements($queryObject);

                // if it goes into the loop, there is more than one triple pattern in the DELETE query,
                // which is not allowed.
                $i = 0;
                foreach ($statements as $statement) {
                    if (1 <= $i++) {
                        throw new \Exception(
                            'DELETE query can not contain more than one triple pattern: '. $query
                        );
                    }

                    // if only one Statement is in the list, no exception will be thrown and in
                    // $statement is that Statement later on and can be used.
                }

                return $this->deleteMatchingStatements($statement);

            // INSERT DATA or INSERT INTO query
            } elseif ('insert' == $firstPart) {
                return $this->addStatements($this->getStatements($queryObject));

            // TODO Support
            // WITH ... DELETE ... WHERE queries
            // WITH ... DELETE ... INSERT ... WHERE queries
            } else {
                throw new \Exception(
                    'Not yet implemented: WITH-DELETE-WHERE and WITH-DELETE-INSERT-WHERE queries are not '.
                    'supported yet.'
                );
            }
        } elseif ('askQuery' == $this->queryUtils->getQueryType($query)) {
            /*
             * ASK query
             */
            $statement = $this->getStatement($queryObject);
            return $this->hasMatchingStatement($statement);
        } elseif ('selectQuery' == $this->queryUtils->getQueryType($query)) {
            /*
             * SELECT query
             */
            $statement = $this->getStatement($queryObject);
            return $this->getMatchingStatements($statement);
        } else {
            /*
             * Unsupported query
             */
            throw new \Exception('Unsupported query was given: '. $query);
        }
    }

    /**
     * Create Statement instance based on a given Query instance.
     *
     * @param Query     $queryObject Query object which represents a SPARQL query.
     * @return Statement Statement object itself.
     * @throws \Exception if query contains more than one triple pattern.
     * @throws \Exception if more than one graph was found.
     * @api
     * @since 0.1
     */
    protected function getStatement(Query $queryObject)
    {
        $queryParts = $queryObject->getQueryParts();

        $tupleInformaton = null;
        $tupleType = null;

        /**
         * Use triple pattern
         */
        if (true === isset($queryParts['triple_pattern'])) {
            $tupleInformation = $queryParts['triple_pattern'];
            $tupleType = 'triple';

        /**
         * Use quad pattern
         */
        } elseif (true === isset($queryParts['quad_pattern'])) {
            $tupleInformation = $queryParts['quad_pattern'];
            $tupleType = 'quad';

        /**
         * Neither triple nor quad information
         */
        } else {
            throw new \Exception(
                'Neither triple nor quad information available in given query object: ' . $queryObject->getQuery()
            );
        }

        if (1 > count($tupleInformation)) {
            throw new \Exception('Query contains more than one triple- respectivly quad pattern.');
        }

        /**
         * Triple
         */
        if ('triple' == $tupleType) {
            $subject = $this->createNodeByValueAndType($tupleInformation[0]['s'], $tupleInformation[0]['s_type']);
            $predicate = $this->createNodeByValueAndType($tupleInformation[0]['p'], $tupleInformation[0]['p_type']);
            $object = $this->createNodeByValueAndType($tupleInformation[0]['o'], $tupleInformation[0]['o_type']);
            $graph = null;

        /**
         * Quad
         */
        } elseif ('quad' == $tupleType) {
            $subject = $this->createNodeByValueAndType($tupleInformation[0]['s'], $tupleInformation[0]['s_type']);
            $predicate = $this->createNodeByValueAndType($tupleInformation[0]['p'], $tupleInformation[0]['p_type']);
            $object = $this->createNodeByValueAndType($tupleInformation[0]['o'], $tupleInformation[0]['o_type']);
            $graph = $this->createNodeByValueAndType($tupleInformation[0]['g'], 'uri');
        }

        // no else neccessary, because otherwise the upper exception would be thrown if tupleType is neither
        // quad or triple.

        return $this->statementFactory->createStatement($subject, $predicate, $object, $graph);
    }

    /**
     * Create statements from query.
     *
     * @param Query $queryObject Query object which represents a SPARQL query.
     * @return StatementIterator StatementIterator object
     * @throws \Exception if query contains quads and triples at the same time.
     * @throws \Exception if query contains neither quads nor triples.
     * @api
     * @since 0.1
     */
    protected function getStatements(Query $queryObject)
    {
        $queryParts = $queryObject->getQueryParts();

        $statementArray = array();

        // if only triples, but no quads
        if (true === isset($queryParts['triple_pattern'])
            && false === isset($queryParts['quad_pattern'])) {
            foreach ($queryParts['triple_pattern'] as $pattern) {
                /**
                 * Create Node instances for S, P and O to build a Statement instance later on
                 */
                $s = $this->createNodeByValueAndType($pattern['s'], $pattern['s_type']);
                $p = $this->createNodeByValueAndType($pattern['p'], $pattern['p_type']);
                $o = $this->createNodeByValueAndType($pattern['o'], $pattern['o_type']);
                $g = null;

                $statementArray[] = $this->statementFactory->createStatement($s, $p, $o, $g);
            }

        // if only quads, but not triples
        } elseif (false === isset($queryParts['triple_pattern'])
            && true === isset($queryParts['quad_pattern'])) {
            foreach ($queryParts['quad_pattern'] as $pattern) {
                /**
                 * Create Node instances for S, P and O to build a Statement instance later on
                 */
                $s = $this->createNodeByValueAndType($pattern['s'], $pattern['s_type']);
                $p = $this->createNodeByValueAndType($pattern['p'], $pattern['p_type']);
                $o = $this->createNodeByValueAndType($pattern['o'], $pattern['o_type']);
                $g = $this->createNodeByValueAndType($pattern['g'], $pattern['g_type']);

                $statementArray[] = $this->statementFactory->createStatement($s, $p, $o, $g);
            }

        // found quads and triples
        } elseif (true === isset($queryParts['triple_pattern'])
            && true === isset($queryParts['quad_pattern'])) {
            throw new \Exception('Query contains quads and triples. That is not supported yet.');

        // neither quads nor triples
        } else {
            throw new \Exception('Query contains neither quads nor triples.');
        }

        return $this->statementIteratorFactory->createStatementIteratorFromArray($statementArray);
    }

    /**
     * Creates an instance of Node by given $value and $type.
     *
     * @param mixed  $value
     * @param string $type
     * @return Node Instance of Node interface.
     * @throws \Exception if an unknown type was given.
     * @api
     * @since 0.1
     */
    protected function createNodeByValueAndType($value, $type)
    {
        /**
         * URI
         */
        if ('uri' == $type) {
            return $this->nodeFactory->createNamedNode($value);

        /**
         * Any Pattern
         */
        } elseif ('var' == $type) {
            return $this->nodeFactory->createAnyPattern();

        /**
         * Typed Literal or Literal
         */
        } elseif ('typed-literal' == $type || 'literal' == $type) {
            return $this->nodeFactory->createLiteral($value);

        /**
         * Unknown type
         */
        } else {
            throw new \Exception('Unknown type given: '. $type);
        }
    }
}
