<?php

namespace Saft\Rdf;

class StatementIteratorFactoryImpl implements StatementIteratorFactory
{
    /**
     * Creates a StatementIterator instance which uses an array to manage their entities.
     *
     * @param  array|\Iterator   $statements List of statements, represented by an array or instance
     *                                       which implements \Iterator interface.
     * @return StatementIterator
     */
    public function createStatementIteratorFromArray(array $statements)
    {
        return new ArrayStatementIteratorImpl($statements);
    }
}
