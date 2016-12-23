<?php
namespace Hough\Promise\Tests;

use Hough\Generators\AbstractGenerator;
use Hough\Promise\FulfilledPromise;
use Hough\Promise\RejectedPromise;
use Hough\Promise\RejectionException;

class FunctionsTestGenerator8 extends AbstractGenerator
{
    private $_value = 0;

    public function resume($position)
    {
        if ($position > 0) {

            $this->_value = $this->getLastYieldedValue();
        }

        if ($position === 1000) {

            return array($this->_value);
        }

        if ($position === 1001) {

            return null;
        }

        return array(new FulfilledPromise($position));
    }
}
