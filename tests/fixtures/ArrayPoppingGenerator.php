<?php
namespace Hough\Promise\Tests;

use Hough\Promise\AbstractSimulatedGenerator;

class ArrayPoppingGenerator extends AbstractSimulatedGenerator
{
    private $_array;

    public function __construct(array &$incoming)
    {
        $this->_array = &$incoming;
    }

    public function executePosition($position)
    {
        if (count($this->_array) === 0) {

            return null;
        }

        return array(array_pop($this->_array));
    }
}
