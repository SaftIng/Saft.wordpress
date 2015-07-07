<?php

namespace Saft\Skeleton\DependencyInjection;

use Dice\Dice;
use Dice\Instance;
use Dice\Rule;

class ContainerDice implements Container
{
    /**
     * @var Dice
     */
    protected $dice;

    /**
     * @var Rule
     */
    protected $rule;

    /**
     * @param array $replacements Array with key-value-pairs. The keys are interfaces you wanna replace
     *                            with the class, which is the according value.
     */
    public function __construct($replacements = array())
    {
        $this->rule = new Rule();

        /*
         * Setup substitutions for all (important) interfaces.
         */
        $replacements = array_merge(array(
            'Saft\Rdf\NodeFactory' => 'Saft\Rdf\NodeFactoryImpl',
            'Saft\Rdf\StatementFactory' => 'Saft\Rdf\StatementFactoryImpl',
            'Saft\Rdf\StatementIteratorFactory' => 'Saft\Rdf\StatementIteratorFactoryImpl',
            'Saft\Sparql\Query\QueryFactory' => 'Saft\Sparql\Query\QueryFactoryImpl',
            'Saft\Store\Result\ResultFactory' => 'Saft\Store\Result\ResultFactoryImpl',
        ), $replacements);

        foreach ($replacements as $interface => $classToUse) {
            $this->rule->substitutions[$interface] = new Instance($classToUse);
        }

        /*
         * Setup Dice container
         */
         $this->dice = new Dice();
         $this->dice->addRule('*', $this->rule);
    }

    /**
     * Creates and returns an instance of a given class name.
     *
     * @param  string $classToInstantiate Name of the class you want to instantiate.
     * @param  array  $parameter          Array which contains all parameter for the class to instantiate.
     *                                    (optional)
     */
    public function createInstanceOf($classToInstantiate, array $parameter = array())
    {
        return $this->dice->create($classToInstantiate, $parameter);
    }
}
