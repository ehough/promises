<?php
namespace GuzzleHttp\Promise;

/**
 * A promise that has been fulfilled.
 *
 * Thenning off of this promise will invoke the onFulfilled callback
 * immediately and ignore other callbacks.
 */
class FulfilledPromise implements PromiseInterface
{
    private $value;

    public function __construct($value)
    {
        if (method_exists($value, 'then')) {
            throw new \InvalidArgumentException(
                'You cannot create a FulfilledPromise with a promise.');
        }

        $this->value = $value;
    }

    public function then(
        $onFulfilled = null,
        $onRejected = null
    ) {
        // Return itself if there is no onFulfilled function.
        if (!$onFulfilled) {
            return $this;
        }

        $queue = queue();
        $p = new Promise(array($queue, 'run'));
        $value = $this->value;
        $pendingState = self::PENDING;
        $queue->add(function () use ($p, $value, $onFulfilled, $pendingState) {
            if ($p->getState() === $pendingState) {
                try {
                    $p->resolve(call_user_func($onFulfilled, $value));
                } catch (\Throwable $e) {
                    $p->reject($e);
                } catch (\Exception $e) {
                    $p->reject($e);
                }
            }
        });

        return $p;
    }

    public function otherwise($onRejected)
    {
        return $this->then(null, $onRejected);
    }

    public function wait($unwrap = true, $defaultDelivery = null)
    {
        return $unwrap ? $this->value : null;
    }

    public function getState()
    {
        return self::FULFILLED;
    }

    public function resolve($value)
    {
        if ($value !== $this->value) {
            throw new \LogicException("Cannot resolve a fulfilled promise");
        }
    }

    public function reject($reason)
    {
        throw new \LogicException("Cannot reject a fulfilled promise");
    }

    public function cancel()
    {
        // pass
    }
}
