<?php
namespace Hough\Promise\Tests;

use Hough\Generators\AbstractGenerator;
use Hough\Promise\FulfilledPromise;
use Hough\Promise\RejectedPromise;
use Hough\Promise\RejectionException;

class FunctionsTestGenerator9 extends AbstractGenerator
{
    private $_value = 0;

    private $_exceptionCaught = false;

    public function resume($position)
    {
        if ($position === 1002) {

            return null;
        }

        if ($position === 1001) {

            return array($this->_value);
        }

        $forLoopIndex = ($position + 1);

        if ($this->_exceptionCaught) {

            $this->_exceptionCaught = false;

            return array(new FulfilledPromise($forLoopIndex));
        }

        if ($position > 0) {

            $this->_value = $this->getLastYieldedValue();
        }

        if ($forLoopIndex % 2) {

            return array(new FulfilledPromise($forLoopIndex));
        }

        return array(new RejectedPromise($forLoopIndex));
    }

    protected function onExceptionThrownIn(\Exception $e, $position)
    {
        $this->_exceptionCaught = true;
        $this->next();
    }
}
//
//$value = 0;
//for ($i = 0; $i < 1000; $i++) {
//    try {
//        if ($i % 2) {
//            $value = (yield new P\FulfilledPromise($i));
//        } else {
//            $value = (yield new P\RejectedPromise($i));
//        }
//    } catch (\Exception $e) {
//        $value = (yield new P\FulfilledPromise($i));
//    }
//}
//yield $value;
