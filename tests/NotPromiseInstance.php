<?php
namespace Hough\Promise\Tests;

use Hough\Promise\Promise;
use Hough\Promise\PromiseInterface;

class NotPromiseInstance extends Thennable implements PromiseInterface
{
    private $nextPromise = null;

    public function __construct()
    {
        $this->nextPromise = new Promise();
    }

    public function then($res = null, $rej = null)
    {
        return $this->nextPromise->then($res, $rej);
    }

    public function otherwise($onRejected)
    {
        return $this->then($onRejected);
    }

    public function resolve($value)
    {
        $this->nextPromise->resolve($value);
    }

    public function reject($reason)
    {
        $this->nextPromise->reject($reason);
    }

    public function wait($unwrap = true, $defaultResolution = null)
    {

    }

    public function cancel()
    {

    }

    public function getState()
    {
        return $this->nextPromise->getState();
    }
}
