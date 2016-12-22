<?php
namespace Hough\Promise;

/**
 * Lets you pretend to be an actual generator. Useful when you need to emulate a generator in PHP 5.4 and below.
 */
abstract class AbstractSimulatedGenerator implements \Iterator
{
    /**
     * @var null|mixed
     */
    private $_lastValueSentIn;

    /**
     * @var int
     */
    private $_position = 0;

    /**
     * @var null|mixed
     */
    private $_lastYieldedValue;

    /**
     * @var null|string|int
     */
    private $_lastYieldedKey;

    /**
     * @var int
     */
    private $_lastPositionExecuted;

    /**
     * @var int
     */
    private $_positionsExecutedCount = 0;

    /**
     * @var bool
     */
    private $_sendInvokedAtLeastOnce = false;

    /**
     * @var bool
     */
    private $_hasMoreToExecute = true;

    /**
     * Get the yielded value.
     *
     * @return mixed|null The yielded value.
     */
    public final function current()
    {
        if (!$this->valid()) {

            return null;
        }

        /*
         * Multiple calls to current() should be idempotent
         */
        if ($this->_lastPositionExecuted !== $this->_position) {

            $this->runToNextYieldStatement();
        }

        return $this->valid() ? $this->getLastYieldedValue() : null;
    }

    /**
     * Get the return value of a generator.
     *
     * @return mixed The generator's return value once it has finished executing.
     */
    public function getReturn()
    {
        //override point
        throw new \RuntimeException('Cannot get return value of a generator that hasn\'t returned');
    }

    /**
     * Get the yielded key.
     *
     * @return mixed The yielded key.
     */
    public final function key()
    {
        /*
         * Run to the first yield statement, if we haven't already.
         */
        $this->current();

        return $this->valid() ? $this->_lastYieldedKey : null;
    }

    /**
     * Resume execution of the generator.
     *
     * @return void
     */
    public final function next()
    {
        $this->send(null);
    }

    /**
     * Rewind the iterator
     *
     * @return void
     */
    public final function rewind()
    {
        if ($this->_sendInvokedAtLeastOnce) {

            throw new \RuntimeException('Cannot rewind a generator that was already run');
        }

        /*
         * Run to the first yield statement, if we haven't already.
         */
        $this->current();
    }

    /**
     * Send a value to the generator.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public final function send($value)
    {
        $this->_lastValueSentIn        = $value;
        $this->_sendInvokedAtLeastOnce = true;

        /*
         * If we've already ran to the first yield statement (from rewind() or key(), for instance), we need
         * to try to move to the next position;
         */
        if ($this->_positionsExecutedCount > 0) {

            $this->_position++;
        }

        return $this->current();
    }

    public final function __call($name, $args)
    {
        if ($name === 'throw') {

            /*
             * If the generator is already closed when this method is invoked, the exception will be thrown in the
             * caller's context instead.
             */
            if (!$this->valid()) {

                throw $args[0];
            }

            return $this->onExceptionThrownIn($args[0], $this->_position);
        }

        throw new \RuntimeException('Cannot dynamically invoke method ' . $name . '()');
    }

    /**
     * Check if the iterator has been closed.
     *
     * @return bool False if the iterator has been closed. Otherwise returns true.
     */
    public final function valid()
    {
        return $this->_hasMoreToExecute;
    }

    public final function __invoke()
    {
        return $this;
    }

    /**
     * @return null|mixed
     */
    protected final function getLastValueSentIn()
    {
        return $this->_lastValueSentIn;
    }

    /**
     * @return null|mixed
     */
    protected final function getLastYieldedValue()
    {
        return $this->_lastYieldedValue;
    }

    protected function onExceptionThrownIn(\Exception $e, $position)
    {
        //override point
        throw $e;
    }

    /**
     * @param int $position
     *
     * @return null|array
     */
    protected abstract function executePosition($position);

    private function runToNextYieldStatement()
    {
        $executionResult             = $this->executePosition($this->_position);
        $this->_lastPositionExecuted = $this->_position;

        $this->_positionsExecutedCount++;

        /*
         * Nothing more to do.
         */
        if ($executionResult === null) {

            $this->_hasMoreToExecute = false;
            $this->_lastYieldedValue = null;
            $this->_lastYieldedKey   = null;

            return;
        }

        if (!is_array($executionResult) || count($executionResult) === 0 || count($executionResult) >= 3) {

            throw new \LogicException('executePosition() must return an array of up to two elements. If two elements, the first is the yielded key and the second is the yielded value. If one element, it is considered to be the yielded value.');
        }

        if (count($executionResult) === 2) {

            $this->_lastYieldedKey   = $executionResult[0];
            $this->_lastYieldedValue = $executionResult[1];

        } else {

            $this->_lastYieldedKey   = $this->_position;
            $this->_lastYieldedValue = $executionResult[0];
        }
    }
}
