<?php
namespace Hough\Promise\Tests;

use Hough\Promise\AbstractSimulatedGenerator;
use Hough\Promise\FulfilledPromise;

class FunctionsTestGenerator3 extends AbstractSimulatedGenerator
{
    public function executePosition($position)
    {
        if ($position === 2) {

            return null;
        }

        if ($position === 0) {

            return array(new FulfilledPromise(0));
        }

        throw new \Exception('a');
    }
}
