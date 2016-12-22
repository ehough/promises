<?php
namespace GuzzleHttp\Promise\Tests;

use GuzzleHttp\Promise\AbstractSimulatedGenerator;
use GuzzleHttp\Promise\FulfilledPromise;

class FunctionsTestGenerator1 extends AbstractSimulatedGenerator
{
    public function executePosition($position)
    {
        if ($position === 0) {

            return array(new FulfilledPromise('a'));
        }

        return array($this->getLastValueSentIn() . 'b');
    }

    protected function isValid($position)
    {
        return $position < 2;
    }
}
