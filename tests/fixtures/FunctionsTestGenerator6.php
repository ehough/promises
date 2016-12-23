<?php
namespace Hough\Promise\Tests;

use Hough\Generators\AbstractGenerator;
use Hough\Promise\RejectedPromise;

class FunctionsTestGenerator6 extends AbstractGenerator
{
    private $_failCallback;

    public function __construct($failCallback)
    {
        $this->_failCallback = $failCallback;
    }

    public function resume($position)
    {
        if ($position === 1) {

            return null;
        }

        if ($position === 0) {

            return array(new RejectedPromise('a'));
        }

        $cb = $this->_failCallback;
        call_user_func($cb, 'Should have thrown into the coroutine!');

        throw new \RuntimeException('Should never reach position 1');
    }

    protected function onExceptionThrownIn(\Exception $e, $position)
    {
        if ($position === 0) {

            throw new \Exception('foo');
        }

        throw $e;
    }
}