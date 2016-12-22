<?php
namespace Hough\Promise\Tests;

use Hough\Promise\AbstractSimulatedGenerator;

class ArrayGenerator extends AbstractSimulatedGenerator
{
    /**
     * @var array
     */
    private $_array;

    public function __construct(array $array)
    {
        $this->_array = $array;
    }


    /**
     * @param int $position
     *
     * @return null|mixed
     */
    protected function executePosition($position)
    {
        if ($position === count($this->_array)) {

            return null;
        }

        return array($this->_array[$position]);
    }
}
