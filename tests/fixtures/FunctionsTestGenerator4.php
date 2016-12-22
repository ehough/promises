<?php
namespace Hough\Promise\Tests;

use Hough\Promise\AbstractSimulatedGenerator;
use Hough\Promise\FulfilledPromise;
use Hough\Promise\RejectedPromise;

class FunctionsTestGenerator4 extends AbstractSimulatedGenerator
{
    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function executePosition($position)
    {
        if ($position === 0) {

            return array(new FulfilledPromise(0));
        }

        return array(new RejectedPromise('no!'));
    }

    protected function isValid($position)
    {
        return $position < 2;
    }
}
