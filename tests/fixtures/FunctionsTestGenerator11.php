<?php
namespace GuzzleHttp\Promise\Tests;

use GuzzleHttp\Promise\AbstractSimulatedGenerator;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\RejectedPromise;
use GuzzleHttp\Promise\RejectionException;

class FunctionsTestGenerator11 extends AbstractSimulatedGenerator
{
    private $_p1;
    private $_p2;
    private $_p3;
    private $_p4;
    private $_p5;

    private $_exception1Caught;
    private $_exception2Caught;

    public function __construct($p1, $p2, $p3, $p4, $p5)
    {
        $this->_p1 = $p1;
        $this->_p2 = $p2;
        $this->_p3 = $p3;
        $this->_p4 = $p4;
        $this->_p5 = $p5;

        $this->_exception1Caught = false;
        $this->_exception2Caught = false;
    }

    public function executePosition($position)
    {
        if ($position === 0) {

            return array($this->_p1);
        }

        if ($position === 1) {

            if (!$this->_exception1Caught) {

                throw new \RuntimeException('Should have caught exception 1');
            }

            return array($this->_p2);
        }

        if ($position === 2) {

            return array($this->_p3);
        }

        if ($position === 3) {

            return array($this->_p4);
        }

        if (!$this->_exception2Caught) {

            throw new \RuntimeException('Should have caught exception 2');
        }

        return array($this->_p5);
    }

    protected function isValid($position)
    {
        return $position < 5;
    }

    protected function onExceptionThrownIn(\Exception $e, $position)
    {
        if ($position === 0) {

            $this->_exception1Caught = true;
            $this->next();
            return $this->current();
        }

        if ($position === 3) {

            $this->_exception2Caught = true;
            $this->next();
            return $this->current();
        }

        throw $e;
    }
}

//try {
//    yield $p1;
//} catch (\Exception $e) {
//    yield $p2;
//    try {
//        yield $p3;
//        yield $p4;
//    } catch (\Exception $e) {
//        yield $p5;
//    }
//}