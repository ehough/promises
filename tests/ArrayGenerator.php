<?php
namespace GuzzleHttp\Promise\Tests;

class ArrayGenerator extends \ArrayIterator
{
    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        throw new \RuntimeException('Iteration has already begun');
    }

    public function send()
    {
        $this->next();
    }
}
