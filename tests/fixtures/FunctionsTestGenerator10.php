<?php
namespace GuzzleHttp\Promise\Tests;

use GuzzleHttp\Promise\AbstractSimulatedGenerator;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\RejectedPromise;
use GuzzleHttp\Promise\RejectionException;

class FunctionsTestGenerator10 extends AbstractSimulatedGenerator
{
    private $_value;

    private $_promises;

    private $_promisesIndex;

    private $_loopTop;

    private $_loopMiddle;

    private $_loopWrap;

    private $_assertEquals;

    private $_done;

    private $_reachedLastYield;

    public function __construct(array $promises, $assertEquals)
    {
        $this->_promises = $promises;
        $this->_assertEquals = $assertEquals;
        $this->_done = false;
    }

    public function executePosition($position)
    {
        if ($this->_reachedLastYield) {

            $this->_done = true;
            return array(null);
        }

        if ($position === 0) {

            $this->_value         = null;
            $this->_promisesIndex = 0;

            return array(new FulfilledPromise('skip'));
        }

        $assertEquals = $this->_assertEquals;

        if ($position === 1) {

            $assertEquals('skip', $this->getLastValueSentIn());
            $this->_loopTop = true;
        }

        // inside for loop
        if ($position > 0 && $this->_promisesIndex < count($this->_promises)) {

            if ($this->_loopTop) {

                $this->_loopTop    = false;
                $this->_loopMiddle = true;
                $this->_loopWrap   = false;

                return array($this->_promises[$this->_promisesIndex]);
            }

            if ($this->_loopMiddle) {

                $this->_loopTop    = false;
                $this->_loopMiddle = false;
                $this->_loopWrap   = true;

                $this->_value = $this->getLastValueSentIn();

                $assertEquals($this->_value, $this->_promisesIndex);

                return array(new FulfilledPromise('skip'));
            }

            if ($this->_loopWrap) {

                $assertEquals('skip', $this->getLastValueSentIn());

                $this->_promisesIndex++;

                if ($this->_promisesIndex < count($this->_promises)) {

                    $this->_loopTop    = false;
                    $this->_loopMiddle = true;
                    $this->_loopWrap   = false;

                    return array($this->_promises[$this->_promisesIndex]);
                }

                return array(new FulfilledPromise('skip'));
            }
        }

        $assertEquals('skip', $this->getLastValueSentIn());

        $this->_reachedLastYield = true;

        return array($this->_value);
    }

    protected function isValid($position)
    {
        return !$this->_done;
    }
}

//$value = null;
//$this->assertEquals('skip', (yield new P\FulfilledPromise('skip')));
//foreach ($promises as $idx => $p) {
//    $value = (yield $p);
//    $this->assertEquals($value, $idx);
//    $this->assertEquals('skip', (yield new P\FulfilledPromise('skip')));
//}
//$this->assertEquals('skip', (yield new P\FulfilledPromise('skip')));
//yield $value;