<?php
namespace Hough\Promise\Tests;

use Hough\Generators\AbstractGenerator;
use Hough\Promise\FulfilledPromise;
use Hough\Promise\RejectedPromise;

class FunctionsTestGenerator5 extends AbstractGenerator
{
    private $_rej;

    public function __construct($rej)
    {
        $this->_rej = $rej;
    }

    public function resume($position)
    {
        if ($position === 2) {

            return null;
        }

        if ($position === 0) {

            return array(new FulfilledPromise(0));
        }

        return array($this->_rej);
    }
}
