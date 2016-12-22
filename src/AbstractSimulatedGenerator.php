<?php
namespace GuzzleHttp\Promise;

/**
 * For PHP 5.4 and below, this class will help you pretend to be an actual generator.
 */
abstract class AbstractSimulatedGenerator implements \Iterator
{
    /**
     * @var null|mixed
     */
    private $_lastValueSentIn;

    /**
     * @var bool
     */
    private $_hasBegunIteration;

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

    public final function current()
    {
        /**
         * Multiple calls to current() should be idempotent
         */
        if ($this->_lastPositionExecuted !== $this->_position) {

            $this->runToNextYieldStatement();
        }

        return $this->getLastYieldedValue();
    }

    /**
     * @return mixed
     */
    public function getReturn()
    {
        //override point
        throw new \RuntimeException('Cannot get return value of a generator that hasn\'t returned');
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public final function key()
    {
        //override point
        return $this->_lastYieldedKey;
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
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
        if ($this->_hasBegunIteration) {

            throw new \RuntimeException('Cannot rewind a generator that was already run');
        }

        $this->runToNextYieldStatement();
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
        $this->_lastValueSentIn = $value;

        if ($this->_hasBegunIteration || $this->_positionsExecutedCount > 0) {

            $this->_position++;

            if ($this->valid()) {

                return $this->current();
            }

            return $this->_lastYieldedValue;
        }

        $this->_hasBegunIteration = true;

        return $this->current();
    }

    public final function __call($name, $args)
    {
        if ($name === 'throw') {

            return $this->onExceptionThrownIn($args[0], $this->_position);
        }

        throw new \RuntimeException('Cannot dynamically invoke method ' . $name . '()');
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public final function valid()
    {
        return $this->isValid($this->_position);
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

    protected abstract function isValid($position);

    /**
     * @param int $position
     *
     * @return null|mixed
     */
    protected abstract function executePosition($position);

    private function runToNextYieldStatement()
    {
        $executionResult             = $this->executePosition($this->_position);
        $this->_lastPositionExecuted = $this->_position;

        $this->_positionsExecutedCount++;

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
