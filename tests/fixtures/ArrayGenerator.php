<?php
namespace Hough\Promise\Tests;

use Hough\Promise\AbstractSimulatedGenerator;

class ArrayGenerator extends AbstractSimulatedGenerator
{
    private $_array;

    public function __construct(array $array)
    {
        $this->_array = $array;
    }

    protected function isValid($position)
    {
        return $position < count($this->_array);
    }

    /**
     * @param int $position
     *
     * @return null|mixed
     */
    protected function executePosition($position)
    {
        return array($this->_array[$position]);
    }
}
