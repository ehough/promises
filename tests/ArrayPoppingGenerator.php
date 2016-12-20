<?php
namespace GuzzleHttp\Promise\Tests;

class ArrayPoppingGenerator implements \Iterator
{
    private $_array;

    private $_lastPopped;

    private $_hasStarted;

    public function __construct(array &$incoming)
    {
        $this->_array      = &$incoming;
        $this->_hasStarted = false;
    }

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        if (!$this->_lastPopped) {

            $this->_lastPopped = array_pop($this->_array);
        }

        return $this->_lastPopped;
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
        $this->_lastPopped = null;
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        return null;
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
        return count($this->_array) > 0;
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
    }

    public function __invoke()
    {
        return $this;
    }
}
