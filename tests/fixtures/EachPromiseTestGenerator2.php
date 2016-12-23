<?php
namespace Hough\Promise\Tests;

use Hough\Generators\AbstractGenerator;

class EachPromiseTestGenerator2 extends AbstractGenerator
{
    private $_promises;

    private $_pending;

    public function __construct(array &$promises, array &$pending)
    {
        $this->_promises = &$promises;
        $this->_pending  = &$pending;
    }

    /**
     * @param int $position
     *
     * @return null|mixed
     */
    protected function resume($position)
    {
        if ($position === count($this->_promises)) {

            return null;
        }

        $toReturn = $this->_promises[$position];

        // Resolve a promises, which will trigger the then() function,
        // which would cause the EachPromise to try to add more
        // promises to the queue. Without a lock, this would trigger
        // a "Cannot resume an already running generator" fatal error.
        if ($p = array_pop($this->_pending)) {
            $p->wait();
        }

        return array($toReturn);
    }
}

//foreach ($promises as $promise) {
//    // Resolve a promises, which will trigger the then() function,
//    // which would cause the EachPromise to try to add more
//    // promises to the queue. Without a lock, this would trigger
//    // a "Cannot resume an already running generator" fatal error.
//    if ($p = array_pop($pending)) {
//        $p->wait();
//    }
//    yield $promise;
//}
