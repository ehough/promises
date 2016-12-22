<?php
namespace GuzzleHttp\Promise\Tests;

use GuzzleHttp\Promise\AbstractSimulatedGenerator;

class ArrayPoppingGenerator extends AbstractSimulatedGenerator
{
    private $_array;

    public function __construct(array &$incoming)
    {
        $this->_array = &$incoming;
    }

    public function executePosition($position)
    {
        return array(array_pop($this->_array));
    }

    protected function isValid($position)
    {
        return $this->getLastYieldedValue() !== null;
    }
}
