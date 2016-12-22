<?php
namespace GuzzleHttp\Promise\Tests;

use GuzzleHttp\Promise\AbstractSimulatedGenerator;

class EachPromiseTestGenerator2 extends AbstractSimulatedGenerator
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
    protected function executePosition($position)
    {
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

    protected function isValid($position)
    {
        return $position < count($this->_promises);
    }
}
