<?php
namespace Hough\Promise\Tests;

use Hough\Promise\AbstractSimulatedGenerator;
use Hough\Promise\Promise;

class CoroutineTestGenerator2 extends AbstractSimulatedGenerator
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
            $promise->reject(new \Exception());
        });

        return array($promise);
    }
}

//yield $promise = new Promise(function () use (&$promise) {
//    $promise->reject(new \Exception());
//});
