<?php
namespace GuzzleHttp\Promise\Tests;

use GuzzleHttp\Promise\AbstractSimulatedGenerator;
use GuzzleHttp\Promise\FulfilledPromise;

class FunctionsTestGenerator3 extends AbstractSimulatedGenerator
{
    public function executePosition($position)
    {
        if ($position === 0) {

            return array(new FulfilledPromise(0));
        }

        throw new \Exception('a');
    }

    protected function isValid($position)
    {
        return $position < 2;
    }
}
