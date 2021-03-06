<?php
namespace Hough\Promise\Tests;

use Hough\Generators\AbstractGenerator;
use Hough\Promise\Promise;

class CoroutineTestGenerator1 extends AbstractGenerator
{
    /**
     * @param int $position
     *
     * @return null|mixed
     */
    protected function resume($position)
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
