<?php
namespace GuzzleHttp\Promise\Tests;

use GuzzleHttp\Promise\AbstractSimulatedGenerator;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\RejectedPromise;
use GuzzleHttp\Promise\RejectionException;

class FunctionsTestGenerator13 extends AbstractSimulatedGenerator
{
    private $_promises;

    public function __construct($promises)
    {
        $this->_promises = $promises;
    }

    public function executePosition($position)
    {
        if ($position === 0) {

            return array(new FulfilledPromise('foo!'));
        }

        return array($this->_promises[($position - 1)]);
    }

    protected function isValid($position)
    {
        return $position < count($this->_promises) + 1;
    }
}

//yield new P\FulfilledPromise('foo!');
//foreach ($promises as $promise) {
//    yield $promise;
//}