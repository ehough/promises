<?php
namespace Hough\Promise\Tests;

use Hough\Promise\AbstractSimulatedGenerator;
use Hough\Promise\FulfilledPromise;
use Hough\Promise\RejectedPromise;

class FunctionsTestGenerator4 extends AbstractSimulatedGenerator
{
    public function executePosition($position)
    {
        if ($position === 2) {

            return null;
        }

        if ($position === 0) {

            return array(new FulfilledPromise(0));
        }

        return array(new RejectedPromise('no!'));
    }
}
