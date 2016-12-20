<?php
namespace GuzzleHttp\Promise\Tests;

class EachPromiseTestGenerator2 implements \Iterator
{
    private $_promises;

    private $_pending;

    private $_hasStarted;

    private $_index;

    private $_pendingCache;

    public function __construct(array &$promises, array &$pending)
    {
        $this->_promises = &$promises;
        $this->_pending  = &$pending;
    }

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        return $this->_promises[$this->_index];
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        $this->_hasStarted = true;

        $this->_popPending();

        $this->_index++;
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        return $this->_index;
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        return $this->_index < count($this->_promises);
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        if ($this->_hasStarted) {

            throw new \RuntimeException('Cannot rewind a generator after it has started iteration.');
        }

        $this->_index        = 0;
        $this->_pendingCache = array();
        $this->_hasStarted   = false;

        $this->_popPending();
    }

    public function __invoke()
    {
        return $this;
    }

    private function _popPending()
    {
        if (!isset($this->_pendingCache[$this->_index])) {

            // Resolve a promises, which will trigger the then() function,
            // which would cause the EachPromise to try to add more
            // promises to the queue. Without a lock, this would trigger
            // a "Cannot resume an already running generator" fatal error.
            if ($p = array_pop($this->_pending)) {
                $p->wait();
            }

            $this->_pendingCache[$this->_index] = true;
        }
    }
}
