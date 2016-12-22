<?php
namespace Hough\Promise\Tests;

use Hough\Promise\AbstractSimulatedGenerator;
use Hough\Promise\FulfilledPromise;
use Hough\Promise\RejectedPromise;
use Hough\Promise\RejectionException;

class FunctionsTestGenerator12 extends AbstractSimulatedGenerator
{
    private $_promises;

    private $_exceptionCaught;

    private $_forLoopIndex;

    private $_topHalf;

    private $_bottomHalf;

    private $_done;

    public function __construct($promises)
    {
        $this->_promises        = $promises;
        $this->_exceptionCaught = false;
        $this->_forLoopIndex    = 0;
        $this->_topHalf         = true;
        $this->_bottomHalf      = false;
        $this->_done            = false;
    }

    public function executePosition($position)
    {
        if ($this->_forLoopIndex === 20) {

            $this->_forLoopIndex++;

            return array($this->getLastYieldedValue());
        }

        if ($this->_topHalf) {

            if ($position % 2 === 0) {

                return array($this->_promises[$this->_forLoopIndex]);
            }

            $this->_topHalf    = false;
            $this->_bottomHalf = true;

            return array($this->_promises[$this->_forLoopIndex + 1]);
        }

        if ($position % 2 === 0) {

            if (!$this->_exceptionCaught) {

                throw new \RuntimeException('Should have thrown and caught exception');
            }

            $this->_exceptionCaught = false;

            return array($this->_promises[$this->_forLoopIndex + 2]);
        }

        $this->_bottomHalf = false;
        $this->_topHalf    = true;
        $index             = $this->_forLoopIndex + 3;

        $this->_forLoopIndex += 4;

        return array($this->_promises[$index]);

    }

    protected function isValid($position)
    {
        return $this->_forLoopIndex <= 20;
    }

    protected function onExceptionThrownIn(\Exception $e, $position)
    {
        if ($this->_bottomHalf && $position % 2 > 0) {

            $this->_exceptionCaught = true;
            $this->next();
            return $this->current();
        }

        throw $e;
    }
}

//for ($i = 0; $i < 20; $i += 4) {
//    try {
//        yield $promises[$i];
//        yield $promises[$i + 1];
//    } catch (\Exception $e) {
//        yield $promises[$i + 2];
//        yield $promises[$i + 3];
//    }
//}