<?php
namespace Hough\Promise\Tests;

use Hough\Generators\AbstractGenerator;
use Hough\Promise\FulfilledPromise;
use Hough\Promise\RejectedPromise;
use Hough\Promise\RejectionException;

class FunctionsTestGenerator2 extends AbstractGenerator
{
    private $_failCallback;

    private $_exceptionAtPosition1;

    public function __construct($failCallback)
    {
        $this->_failCallback = $failCallback;
    }

    /**
     * @param int $position
     *
     * @return null|mixed
     */
    protected function resume($position)
    {
        switch ($position) {

            case 0:

                return array(new RejectedPromise('a'));

            case 1:

                return array(new FulfilledPromise($this->_exceptionAtPosition1->getReason()));

            case 2:

                return array($this->getLastValueSentIn() . 'b');

            default:

                return null;
        }
    }

    protected function onExceptionThrownIn(\Exception $e, $position)
    {
        if (!($e instanceof RejectionException) || $position !== 0) {

            $cb = $this->_failCallback;
            call_user_func($cb);

        } else {

            $this->_exceptionAtPosition1 = $e;
        }
    }
}
