<?php
namespace Hough\Promise\Tests;

use Hough\Promise\AbstractSimulatedGenerator;
use Hough\Promise\Promise;

class CoroutineTestGenerator1 extends AbstractSimulatedGenerator
{
    protected function isValid($position)
    {
        return $position < 1;
    }

    /**
     * @param int $position
     *
     * @return null|mixed
     */
    protected function executePosition($position)
    {
        $promise = new Promise(function () use (&$promise) {
            $promise->resolve(1);
        });

        return array($promise);
    }
}

//yield $promise = new Promise(function () use (&$promise) {
//    $promise->resolve(1);
//});
