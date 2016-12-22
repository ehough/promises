<?php
namespace Hough\Promise\Tests;

use Hough\Promise\Coroutine;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class CoroutineTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider promiseInterfaceMethodProvider
     *
     * @param string $method
     * @param array $args
     */
    public function testShouldProxyPromiseMethodsToResultPromise($method, $args = array())
    {
        $coroutine = new Coroutine(function () { return new ArrayGenerator(array(0)); });
        $mockPromise = $this->getMockForAbstractClass('\Hough\Promise\PromiseInterface');
        call_user_func_array(array($mockPromise->expects($this->once())->method($method), 'with'), $args);

        $ref = new ReflectionClass('\Hough\Promise\Coroutine');
        $resultPromiseProp = $ref->getProperty('result');
        $resultPromiseProp->setAccessible(true);
        $resultPromiseProp->setValue($coroutine, $mockPromise);

        call_user_func_array(array($coroutine, $method), $args);
    }

    public function promiseInterfaceMethodProvider()
    {
        return array(
            array('then', array(null, null)),
            array('otherwise', array(function () {})),
            array('wait', array(true)),
            array('getState', array()),
            array('resolve', array(null)),
            array('reject', array(null)),
        );
    }

    public function testShouldCancelResultPromiseAndOutsideCurrentPromise()
    {
        $coroutine = new Coroutine(function () { return new ArrayGenerator(array(0)); });

        $mockPromises = array(
            'result' => $this->getMockForAbstractClass('\Hough\Promise\PromiseInterface'),
            'currentPromise' => $this->getMockForAbstractClass('\Hough\Promise\PromiseInterface'),
        );
        foreach ($mockPromises as $propName => $mockPromise) {
            /**
             * @var $mockPromise \PHPUnit_Framework_MockObject_MockObject
             */
            $mockPromise->expects($this->once())
                ->method('cancel')
                ->with();

            $ref = new ReflectionClass('\Hough\Promise\Coroutine');
            $promiseProp = $ref->getProperty($propName);
            $promiseProp->setAccessible(true);
            $promiseProp->setValue($coroutine, $mockPromise);
        }

        $coroutine->cancel();
    }
}
