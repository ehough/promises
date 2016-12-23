<?php
namespace Hough\Promise\Tests;

use Hough\Generators\AbstractGenerator;
use Hough\Promise\FulfilledPromise;

class FunctionsTestGenerator1 extends AbstractGenerator
{
    public function resume($position)
    {
        if ($position === 2) {

            return null;
        }

        if ($position === 0) {

            return array(new FulfilledPromise('a'));
        }

        return array($this->getLastValueSentIn() . 'b');
    }
}
