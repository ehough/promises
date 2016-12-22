<?php
namespace Hough\Promise\Tests;

use Hough\Promise\AbstractSimulatedGenerator;
use Hough\Promise\FulfilledPromise;
use Hough\Promise\RejectedPromise;
use Hough\Promise\RejectionException;

class FunctionsTestGenerator13 extends AbstractSimulatedGenerator
{
    private $_promises;

    public function __construct($promises)
    {
        $this->_promises = $promises;
    }

    public function executePosition($position)
    {
        if ($position === count($this->_promises) + 1) {

            return null;
        }

        if ($position === 0) {

            return array(new FulfilledPromise('foo!'));
        }

        return array($this->_promises[($position - 1)]);
    }
}

//yield new P\FulfilledPromise('foo!');
//foreach ($promises as $promise) {
//    yield $promise;
//}