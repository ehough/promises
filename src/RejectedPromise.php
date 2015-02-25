<?php
namespace GuzzleHttp\Promise;

/**
 * A promise that has been rejected.
 *
 * Thenning off of this promise will invoke the onRejected callback
 * immediately and ignore other callbacks.
 */
class RejectedPromise implements PromiseInterface
{
    private $reason;

    public function __construct($reason)
    {
        if (method_exists($reason, 'then')) {
            throw new \InvalidArgumentException(
                'You cannot create a RejectedPromise with a promise.');
        }

        $this->reason = $reason;
    }

    public function then(
        callable $onFulfilled = null,
        callable $onRejected = null
    ) {
        // If there's no onRejected callback then just return self.
        if (!$onRejected) {
            return $this;
        }

        try {
            // Return a resolved promise if onRejected does not throw.
            return Promise::promiseFor($onRejected($this->reason));
        } catch (\Exception $e) {
            // onRejected threw, so return a rejected promise.
            return new static($e);
        }
    }

    public function wait($unwrap = true, $defaultDelivery = null)
    {
        if ($unwrap) {
            throw $this->reason instanceof \Exception
                ? $this->reason
                : new \RuntimeException($this->reason);
        }
    }

    public function getState()
    {
        return 'rejected';
    }

    public function resolve($value)
    {
        // pass
    }

    public function reject($reason)
    {
        // pass
    }

    public function cancel()
    {
        // pass
    }
}