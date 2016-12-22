<?php
namespace Hough\Promise\Tests;

use Hough\Promise\AbstractSimulatedGenerator;
use Hough\Promise\Promise;

class CoroutineTestGenerator1 extends AbstractSimulatedGenerator
{
    /**
     * @param int $position
     *
     * @return null|mixed
     */
    protected function executePosition($position)
    {
        if ($position === 1) {

            return null;
        }

        $promise = new Promise(function () use (&$promise) {
            $promise->resolve(1);
        });

        return array($promise);
    }
}

//yield $promise = new Promise(function () use (&$promise) {
//    $promise->resolve(1);
//});
