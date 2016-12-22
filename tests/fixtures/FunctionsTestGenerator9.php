<?php
namespace GuzzleHttp\Promise\Tests;

use GuzzleHttp\Promise\AbstractSimulatedGenerator;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\RejectedPromise;
use GuzzleHttp\Promise\RejectionException;

class FunctionsTestGenerator9 extends AbstractSimulatedGenerator
{
    private $_value = 0;

    private $_exceptionCaught = false;

    private $_forLoopIndex = 0;

    public function executePosition($position)
    {
        if ($this->_forLoopIndex === 1000) {

            $this->_forLoopIndex++;

            return array($this->_value);
        }

        if ($this->_exceptionCaught) {

            $this->_exceptionCaught = false;

            return array(new FulfilledPromise($this->_forLoopIndex++));
        }

        if ($position > 0) {

            $this->_value = $this->getLastYieldedValue();
        }

        if ($this->_forLoopIndex % 2) {

            return array(new FulfilledPromise($this->_forLoopIndex++));
        }

        return array(new RejectedPromise($this->_forLoopIndex));
    }

    protected function isValid($position)
    {
        return $this->_forLoopIndex < 1001;
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
