<?php
namespace Hough\Promise\Tests;

use Hough\Promise\AbstractSimulatedGenerator;
use Hough\Promise\FulfilledPromise;
use Hough\Promise\RejectedPromise;
use Hough\Promise\RejectionException;

class FunctionsTestGenerator8 extends AbstractSimulatedGenerator
{
    private $_value = 0;

    public function executePosition($position)
    {
        if ($position > 0) {

            $this->_value = $this->getLastYieldedValue();
        }

        if ($position === 1000) {

            return array($this->_value);
        }

        return array(new FulfilledPromise($position));
    }

    protected function isValid($position)
    {
        return $position < 1001;
    }
}
