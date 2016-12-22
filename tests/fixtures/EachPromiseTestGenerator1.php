<?php
namespace Hough\Promise\Tests;

use Hough\Promise\AbstractSimulatedGenerator;

class EachPromiseTestGenerator1 extends AbstractSimulatedGenerator
{
    public function executePosition($position)
    {
        if ($position === 0) {

            return array('a');
        }

        throw new \Exception('Failure');
    }

    protected function isValid($position)
    {
        return $position < 2;
    }
}
