<?php
namespace GuzzleHttp\Promise\Tests;

use GuzzleHttp\Promise\AbstractSimulatedGenerator;
use GuzzleHttp\Promise\RejectedPromise;

class FunctionsTestGenerator6 extends AbstractSimulatedGenerator
{
    private $_failCallback;

    public function __construct($failCallback)
    {
        $this->_failCallback = $failCallback;
    }

    public function executePosition($position)
    {
        if ($position === 0) {

            return array(new RejectedPromise('a'));
        }

        $cb = $this->_failCallback;
        $cb('Should have thrown into the coroutine!');

        throw new \RuntimeException('Should never reach position 1');
    }

    protected function isValid($position)
    {
        return $position < 1;
    }

    protected function onExceptionThrownIn(\Exception $e, $position)
    {
        if ($position === 0) {

            throw new \Exception('foo');
        }

        throw $e;
    }
}