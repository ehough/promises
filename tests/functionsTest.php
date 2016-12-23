<?php
namespace Hough\Promise\Tests;

use Hough\Generators\ArrayGenerator;
use Hough\Promise as P;
use Hough\Promise\FulfilledPromise;
use Hough\Promise\Promise;
use Hough\Promise\RejectedPromise;

class FunctionsTest extends \PHPUnit_Framework_TestCase
{
    public function testCreatesPromiseForValue()
    {
        $p = \Hough\Promise\promise_for('foo');
        $this->assertInstanceOf('Hough\Promise\FulfilledPromise', $p);
    }

    public function testReturnsPromiseForPromise()
    {
        $p = new Promise();
        $this->assertSame($p, \Hough\Promise\promise_for($p));
    }

    public function testReturnsPromiseForThennable()
    {
        $p = new Thennable();
        $wrapped = \Hough\Promise\promise_for($p);
        $this->assertNotSame($p, $wrapped);
        $this->assertInstanceOf('Hough\Promise\PromiseInterface', $wrapped);
        $p->resolve('foo');
        P\queue()->run();
        $this->assertEquals('foo', $wrapped->wait());
    }

    public function testReturnsRejection()
    {
        $p = \Hough\Promise\rejection_for('fail');
        $this->assertInstanceOf('Hough\Promise\RejectedPromise', $p);
        $this->assertEquals('fail', $this->readAttribute($p, 'reason'));
    }

    public function testReturnsPromisesAsIsInRejectionFor()
    {
        $a = new Promise();
        $b = \Hough\Promise\rejection_for($a);
        $this->assertSame($a, $b);
    }

    public function testWaitsOnAllPromisesIntoArray()
    {
        $e = new \Exception();
        $a = new Promise(function () use (&$a) { $a->resolve('a'); });
        $b = new Promise(function () use (&$b) { $b->reject('b'); });
        $c = new Promise(function () use (&$c, $e) { $c->reject($e); });
        $results = \Hough\Promise\inspect_all(array($a, $b, $c));
        $this->assertEquals(array(
            array('state' => 'fulfilled', 'value' => 'a'),
            array('state' => 'rejected', 'reason' => 'b'),
            array('state' => 'rejected', 'reason' => $e)
        ), $results);
    }

    /**
     * @expectedException \Hough\Promise\RejectionException
     */
    public function testUnwrapsPromisesWithNoDefaultAndFailure()
    {
        $promises = array(new FulfilledPromise('a'), new Promise());
        \Hough\Promise\unwrap($promises);
    }

    public function testUnwrapsPromisesWithNoDefault()
    {
        $promises = array(new FulfilledPromise('a'));
        $this->assertEquals(array('a'), \Hough\Promise\unwrap($promises));
    }

    public function testUnwrapsPromisesWithKeys()
    {
        $promises = array(
            'foo' => new FulfilledPromise('a'),
            'bar' => new FulfilledPromise('b'),
        );
        $this->assertEquals(array(
            'foo' => 'a',
            'bar' => 'b'
        ), \Hough\Promise\unwrap($promises));
    }

    public function testAllAggregatesSortedArray()
    {
        $a = new Promise();
        $b = new Promise();
        $c = new Promise();
        $d = \Hough\Promise\all(array($a, $b, $c));
        $b->resolve('b');
        $a->resolve('a');
        $c->resolve('c');
        $d->then(
            function ($value) use (&$result) { $result = $value; },
            function ($reason) use (&$result) { $result = $reason; }
        );
        P\queue()->run();
        $this->assertEquals(array('a', 'b', 'c'), $result);
    }

    public function testAllThrowsWhenAnyRejected()
    {
        $a = new Promise();
        $b = new Promise();
        $c = new Promise();
        $d = \Hough\Promise\all(array($a, $b, $c));
        $b->resolve('b');
        $a->reject('fail');
        $c->resolve('c');
        $d->then(
            function ($value) use (&$result) { $result = $value; },
            function ($reason) use (&$result) { $result = $reason; }
        );
        P\queue()->run();
        $this->assertEquals('fail', $result);
    }

    public function testSomeAggregatesSortedArrayWithMax()
    {
        $a = new Promise();
        $b = new Promise();
        $c = new Promise();
        $d = \Hough\Promise\some(2, array($a, $b, $c));
        $b->resolve('b');
        $c->resolve('c');
        $a->resolve('a');
        $d->then(function ($value) use (&$result) { $result = $value; });
        P\queue()->run();
        $this->assertEquals(array('b', 'c'), $result);
    }

    public function testSomeRejectsWhenTooManyRejections()
    {
        $a = new Promise();
        $b = new Promise();
        $d = \Hough\Promise\some(2, array($a, $b));
        $a->reject('bad');
        $b->resolve('good');
        P\queue()->run();
        $this->assertEquals($a::REJECTED, $d->getState());
        $d->then(null, function ($reason) use (&$called) {
            $called = $reason;
        });
        P\queue()->run();
        $this->assertInstanceOf('Hough\Promise\AggregateException', $called);
        $this->assertContains('bad', $called->getReason());
    }

    public function testCanWaitUntilSomeCountIsSatisfied()
    {
        $a = new Promise(function () use (&$a) { $a->resolve('a'); });
        $b = new Promise(function () use (&$b) { $b->resolve('b'); });
        $c = new Promise(function () use (&$c) { $c->resolve('c'); });
        $d = \Hough\Promise\some(2, array($a, $b, $c));
        $this->assertEquals(array('a', 'b'), $d->wait());
    }

    /**
     * @expectedException \Hough\Promise\AggregateException
     * @expectedExceptionMessage Not enough promises to fulfill count
     */
    public function testThrowsIfImpossibleToWaitForSomeCount()
    {
        $a = new Promise(function () use (&$a) { $a->resolve('a'); });
        $d = \Hough\Promise\some(2, array($a));
        $d->wait();
    }

    /**
     * @expectedException \Hough\Promise\AggregateException
     * @expectedExceptionMessage Not enough promises to fulfill count
     */
    public function testThrowsIfResolvedWithoutCountTotalResults()
    {
        $a = new Promise();
        $b = new Promise();
        $d = \Hough\Promise\some(3, array($a, $b));
        $a->resolve('a');
        $b->resolve('b');
        $d->wait();
    }

    public function testAnyReturnsFirstMatch()
    {
        $a = new Promise();
        $b = new Promise();
        $c = \Hough\Promise\any(array($a, $b));
        $b->resolve('b');
        $a->resolve('a');
        //P\queue()->run();
        //$this->assertEquals('fulfilled', $c->getState());
        $c->then(function ($value) use (&$result) { $result = $value; });
        P\queue()->run();
        $this->assertEquals('b', $result);
    }

    public function testSettleFulfillsWithFulfilledAndRejected()
    {
        $a = new Promise();
        $b = new Promise();
        $c = new Promise();
        $d = \Hough\Promise\settle(array($a, $b, $c));
        $b->resolve('b');
        $c->resolve('c');
        $a->reject('a');
        P\queue()->run();
        $this->assertEquals('fulfilled', $d->getState());
        $d->then(function ($value) use (&$result) { $result = $value; });
        P\queue()->run();
        $this->assertEquals(array(
            array('state' => 'rejected', 'reason' => 'a'),
            array('state' => 'fulfilled', 'value' => 'b'),
            array('state' => 'fulfilled', 'value' => 'c')
        ), $result);
    }

    public function testCanInspectFulfilledPromise()
    {
        $p = new FulfilledPromise('foo');
        $this->assertEquals(array(
            'state' => 'fulfilled',
            'value' => 'foo'
        ), \Hough\Promise\inspect($p));
    }

    public function testCanInspectRejectedPromise()
    {
        $p = new RejectedPromise('foo');
        $this->assertEquals(array(
            'state'  => 'rejected',
            'reason' => 'foo'
        ), \Hough\Promise\inspect($p));
    }

    public function testCanInspectRejectedPromiseWithNormalException()
    {
        $e = new \Exception('foo');
        $p = new RejectedPromise($e);
        $this->assertEquals(array(
            'state'  => 'rejected',
            'reason' => $e
        ), \Hough\Promise\inspect($p));
    }

    public function testCallsEachLimit()
    {
        $p = new Promise();
        $aggregate = \Hough\Promise\each_limit($p, 2);
        $p->resolve('a');
        P\queue()->run();
        $this->assertEquals($p::FULFILLED, $aggregate->getState());
    }

    public function testEachLimitAllRejectsOnFailure()
    {
        $p = array(new FulfilledPromise('a'), new RejectedPromise('b'));
        $aggregate = \Hough\Promise\each_limit_all($p, 2);
        P\queue()->run();
        $this->assertEquals(P\PromiseInterface::REJECTED, $aggregate->getState());
        $result = \Hough\Promise\inspect($aggregate);
        $this->assertEquals('b', $result['reason']);
    }

    public function testIterForReturnsIterator()
    {
        $iter = new \ArrayIterator();
        $this->assertSame($iter, \Hough\Promise\iter_for($iter));
    }

    public function testKnowsIfFulfilled()
    {
        $p = new FulfilledPromise(null);
        $this->assertTrue(P\is_fulfilled($p));
        $this->assertFalse(P\is_rejected($p));
    }

    public function testKnowsIfRejected()
    {
        $p = new RejectedPromise(null);
        $this->assertTrue(P\is_rejected($p));
        $this->assertFalse(P\is_fulfilled($p));
    }

    public function testKnowsIfSettled()
    {
        $p = new RejectedPromise(null);
        $this->assertTrue(P\is_settled($p));
        $p = new Promise();
        $this->assertFalse(P\is_settled($p));
    }

    public function testReturnsTrampoline()
    {
        $this->assertInstanceOf('Hough\Promise\TaskQueue', P\queue());
        $this->assertSame(P\queue(), P\queue());
    }

    public function testCanScheduleThunk()
    {
        $tramp = P\queue();
        $promise = P\task(function () { return 'Hi!'; });
        $c = null;
        $promise->then(function ($v) use (&$c) { $c = $v; });
        $this->assertNull($c);
        $tramp->run();
        $this->assertEquals('Hi!', $c);
    }

    public function testCanScheduleThunkWithRejection()
    {
        $tramp = P\queue();
        $promise = P\task(function () { throw new \Exception('Hi!'); });
        $c = null;
        $promise->otherwise(function ($v) use (&$c) { $c = $v; });
        $this->assertNull($c);
        $tramp->run();
        $this->assertEquals('Hi!', $c->getMessage());
    }

    public function testCanScheduleThunkWithWait()
    {
        $tramp = P\queue();
        $promise = P\task(function () { return 'a'; });
        $this->assertEquals('a', $promise->wait());
        $tramp->run();
    }

    public function testYieldsFromCoroutine()
    {
        $promise = P\coroutine(new FunctionsTestGenerator1());

        $promise->then(function ($value) use (&$result) { $result = $value; });
        P\queue()->run();
        $this->assertEquals('ab', $result);
    }

    public function testCanCatchExceptionsInCoroutine()
    {
        $promise = P\coroutine(new FunctionsTestGenerator2(array($this, 'fail')));
        $promise->then(function ($value) use (&$result) { $result = $value; });
        P\queue()->run();
        $this->assertEquals(P\PromiseInterface::FULFILLED, $promise->getState());
        $this->assertEquals('ab', $result);
    }

    public function testRejectsParentExceptionWhenException()
    {
        $promise = P\coroutine(new FunctionsTestGenerator3());
        $promise->then(
            array($this, 'fail'),
            function ($reason) use (&$result) { $result = $reason; }
        );
        P\queue()->run();
        $this->assertInstanceOf('Exception', $result);
        $this->assertEquals('a', $result->getMessage());
    }

    public function testCanRejectFromRejectionCallback()
    {
        $promise = P\coroutine(new FunctionsTestGenerator4());
        $promise->then(
            array($this, 'fail'),
            function ($reason) use (&$result) { $result = $reason; }
        );
        P\queue()->run();
        $this->assertInstanceOf('Hough\Promise\RejectionException', $result);
        $this->assertEquals('no!', $result->getReason());
    }

    public function testCanAsyncReject()
    {
        $rej = new P\Promise();
        $promise = P\coroutine(new FunctionsTestGenerator5($rej));
        $promise->then(
            array($this, 'fail'),
            function ($reason) use (&$result) { $result = $reason; }
        );
        $rej->reject('no!');
        P\queue()->run();
        $this->assertInstanceOf('Hough\Promise\RejectionException', $result);
        $this->assertEquals('no!', $result->getReason());
    }

    public function testCanCatchAndThrowOtherException()
    {
        $promise = P\coroutine(new FunctionsTestGenerator6(array($this, 'fail')));
        $promise->otherwise(function ($value) use (&$result) { $result = $value; });
        P\queue()->run();
        $this->assertEquals(P\PromiseInterface::REJECTED, $promise->getState());
        $this->assertContains('foo', $result->getMessage());
    }

    public function testCanCatchAndYieldOtherException()
    {
        $promise = P\coroutine(new FunctionsTestGenerator7(array($this, 'fail')));
        $promise->otherwise(function ($value) use (&$result) { $result = $value; });
        P\queue()->run();
        $this->assertEquals(P\PromiseInterface::REJECTED, $promise->getState());
        $this->assertContains('foo', $result->getMessage());
    }

    public function createLotsOfSynchronousPromise()
    {
        return P\coroutine(new FunctionsTestGenerator8());
    }

    public function testLotsOfSynchronousDoesNotBlowStack()
    {
        $promise = $this->createLotsOfSynchronousPromise();
        $promise->then(function ($v) use (&$r) { $r = $v; });
        P\queue()->run();
        $this->assertEquals(999, $r);
    }

    public function testLotsOfSynchronousWaitDoesNotBlowStack()
    {
        $promise = $this->createLotsOfSynchronousPromise();
        $promise->then(function ($v) use (&$r) { $r = $v; });
        $this->assertEquals(999, $promise->wait());
        $this->assertEquals(999, $r);
    }

    private function createLotsOfFlappingPromise()
    {
        return P\coroutine(new FunctionsTestGenerator9());
    }

    public function testLotsOfTryCatchingDoesNotBlowStack()
    {
        $promise = $this->createLotsOfFlappingPromise();
        $promise->then(function ($v) use (&$r) { $r = $v; });
        P\queue()->run();
        $this->assertEquals(999, $r);
    }

    public function testLotsOfTryCatchingWaitingDoesNotBlowStack()
    {
        $promise = $this->createLotsOfFlappingPromise();
        $promise->then(function ($v) use (&$r) { $r = $v; });
        $this->assertEquals(999, $promise->wait());
        $this->assertEquals(999, $r);
    }

    public function testAsyncPromisesWithCorrectlyYieldedValues()
    {
        $promises = array(
            new P\Promise(),
            new P\Promise(),
            new P\Promise()
        );

        $promise = P\coroutine(new FunctionsTestGenerator10($promises, array($this, 'assertEquals')));

        $promises[0]->resolve(0);
        $promises[1]->resolve(1);
        $promises[2]->resolve(2);

        $promise->then(function ($v) use (&$r) { $r = $v; });
        P\queue()->run();
        $this->assertEquals(2, $r);
    }

    public function testYieldFinalWaitablePromise()
    {
        $p1 = new P\Promise(function () use (&$p1) {
            $p1->resolve('skip me');
        });
        $p2 = new P\Promise(function () use (&$p2) {
            $p2->resolve('hello!');
        });
        $co = P\coroutine(new ArrayGenerator(array($p1, $p2)));
        P\queue()->run();
        $this->assertEquals('hello!', $co->wait());
    }

    public function testCanYieldFinalPendingPromise()
    {
        $p1 = new P\Promise();
        $p2 = new P\Promise();
        $co = P\coroutine(new ArrayGenerator(array($p1, $p2)));
        $p1->resolve('a');
        $p2->resolve('b');
        $co->then(function ($value) use (&$result) { $result = $value; });
        P\queue()->run();
        $this->assertEquals('b', $result);
    }

    public function testCanNestYieldsAndFailures()
    {
        $p1 = new P\Promise();
        $p2 = new P\Promise();
        $p3 = new P\Promise();
        $p4 = new P\Promise();
        $p5 = new P\Promise();
        $co = P\coroutine(new FunctionsTestGenerator11($p1, $p2, $p3, $p4, $p5));
        $p1->reject('a');
        $p2->resolve('b');
        $p3->resolve('c');
        $p4->reject('d');
        $p5->resolve('e');
        $co->then(function ($value) use (&$result) {
            $result = $value; });
        P\queue()->run();
        $this->assertEquals('e', $result);
    }

    public function testCanYieldErrorsAndSuccessesWithoutRecursion()
    {
        $promises = array();
        for ($i = 0; $i < 20; $i++) {
            $promises[] = new P\Promise();
        }

        $co = P\coroutine(new FunctionsTestGenerator12($promises));

        for ($i = 0; $i < 20; $i += 4) {
            $promises[$i]->resolve($i);
            $promises[$i + 1]->reject($i + 1);
            $promises[$i + 2]->resolve($i + 2);
            $promises[$i + 3]->resolve($i + 3);
        }

        $co->then(function ($value) use (&$result) { $result = $value; });
        P\queue()->run();
        $this->assertEquals('19', $result);
    }

    public function testCanWaitOnPromiseAfterFulfilled()
    {
        $f = function () {
            static $i = 0;
            $i++;
            return $p = new P\Promise(function () use (&$p, $i) {
                $p->resolve($i . '-bar');
            });
        };

        $promises = array();
        for ($i = 0; $i < 20; $i++) {
            $promises[] = call_user_func($f);
        }

        $p = P\coroutine(new FunctionsTestGenerator13($promises));

        $this->assertEquals('20-bar', $p->wait());
    }

    public function testCanWaitOnErroredPromises()
    {
        $p1 = new P\Promise(function () use (&$p1) { $p1->reject('a'); });
        $p2 = new P\Promise(function () use (&$p2) { $p2->resolve('b'); });
        $p3 = new P\Promise(function () use (&$p3) { $p3->resolve('c'); });
        $p4 = new P\Promise(function () use (&$p4) { $p4->reject('d'); });
        $p5 = new P\Promise(function () use (&$p5) { $p5->resolve('e'); });
        $p6 = new P\Promise(function () use (&$p6) { $p6->reject('f'); });

        $co = P\coroutine(new FunctionsTestGenerator14($p1, $p2, $p3, $p4, $p5, $p6));

        $res = P\inspect($co);
        $this->assertEquals('f', $res['reason']);
    }

    public function testCoroutineOtherwiseIntegrationTest()
    {
        $a = new P\Promise();
        $b = new P\Promise();
        // Execute the pool of commands concurrently, and process errors.
        $promise = P\coroutine(new ArrayGenerator(array($a, $b)))
            ->otherwise(function (\Exception $e) {
            // Throw errors from the operations as a specific Multipart error.
            throw new \OutOfBoundsException('a', 0, $e);
        });
        $a->resolve('a');
        $b->reject('b');
        $inspect = P\inspect($promise);
        $reason = $inspect['reason'];
        $this->assertInstanceOf('OutOfBoundsException', $reason);
        $this->assertInstanceOf('Hough\Promise\RejectionException', $reason->getPrevious());
    }

    private function isAtLeastPhp55()
    {
        return version_compare(PHP_VERSION, '5.5', '>=');
    }
}
