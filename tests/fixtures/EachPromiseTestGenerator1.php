<?php
namespace Hough\Promise\Tests;

use Hough\Promise\AbstractSimulatedGenerator;

class EachPromiseTestGenerator1 extends AbstractSimulatedGenerator
{
    public function executePosition($position)
    {
        if ($position === 2) {

            return null;
        }

        if ($position === 0) {

            return array(0, 'a');
        }

        throw new \Exception('Failure');
    }
}
