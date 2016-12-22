<?php
namespace Hough\Promise\Tests;

use Hough\Promise\AbstractSimulatedGenerator;
use Hough\Promise\FulfilledPromise;
use Hough\Promise\RejectedPromise;
use Hough\Promise\RejectionException;

class FunctionsTestGenerator2 extends AbstractSimulatedGenerator
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
    protected function executePosition($position)
    {
        switch ($position) {

            case 0:

                return array(new RejectedPromise('a'));

            case 1:

                return array(new FulfilledPromise($this->_exceptionAtPosition1->getReason()));

            default:

                return array($this->getLastValueSentIn() . 'b');
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

    protected function isValid($position)
    {
        return $position < 3;
    }
}
