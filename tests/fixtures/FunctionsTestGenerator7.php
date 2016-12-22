<?php
namespace GuzzleHttp\Promise\Tests;

use GuzzleHttp\Promise\AbstractSimulatedGenerator;
use GuzzleHttp\Promise\RejectedPromise;
use GuzzleHttp\Promise\RejectionException;

class FunctionsTestGenerator7 extends AbstractSimulatedGenerator
{
    private $_failCallback;

    /**
     * @var bool
     */
    private $_rejectionExceptionCaught;

    public function __construct($failCallback)
    {
        $this->_failCallback             = $failCallback;
        $this->_rejectionExceptionCaught = false;
    }

    public function executePosition($position)
    {
        if ($position === 0) {

            return array(new RejectedPromise('a'));
        }

        if ($position === 1) {

            if (!$this->_rejectionExceptionCaught) {

                $cb = $this->_failCallback;
                call_user_func($cb, 'Should have thrown into the coroutine!');

                throw new \RuntimeException('Should never reach position 1 without catching a RejectionException');
            }

            return array(new RejectedPromise('foo'));
        }
    }

    protected function isValid($position)
    {
        return $position < 2;
    }

    protected function onExceptionThrownIn(\Exception $e, $position)
    {
        if ($position === 0 && $e instanceof RejectionException) {

            $this->_rejectionExceptionCaught = true;
            return;
        }

        throw $e;
    }
}
