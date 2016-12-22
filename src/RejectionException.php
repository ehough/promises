<?php
namespace GuzzleHttp\Promise;

/**
 * A special exception that is thrown when waiting on a rejected promise.
 *
 * The reason value is available via the getReason() method.
 */
class RejectionException extends \RuntimeException
{
    /** @var mixed Rejection reason. */
    private $reason;

    /**
     * @param mixed $reason       Rejection reason.
     * @param string $description Optional description
     */
    public function __construct($reason, $description = null)
    {
        $this->reason = $reason;

        $message = 'The promise was rejected';

        if ($description) {
            $message .= ' with reason: ' . $description;
        } elseif (is_string($reason)
            || (is_object($reason) && method_exists($reason, '__toString'))
        ) {
            $message .= ' with reason: ' . $this->reason;
        } elseif ($this->_isJsonSerializable($reason)) {

            if (version_compare(PHP_VERSION, '5.4', '>=')) {

                $message .= ' with reason: '
                . json_encode($this->reason, JSON_PRETTY_PRINT);

            } else {

                $message .= ' with reason: '
                . json_encode($this->reason);
            }

        }

        parent::__construct($message);
    }

    /**
     * Returns the rejection reason.
     *
     * @return mixed
     */
    public function getReason()
    {
        return $this->reason;
    }

    private function _isJsonSerializable($candidate)
    {
        if (interface_exists('JsonSerializable') && is_subclass_of($candidate, 'JsonSerializable')) {

            return true;
        }

        if (!is_object($candidate)) {

            return false;
        }

        $ref = new \ReflectionClass(get_class($candidate));

        return $ref->hasMethod('jsonSerialize');
    }
}
