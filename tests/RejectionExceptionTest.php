<?php
namespace Hough\Promise\Tests;

use Hough\Promise\RejectionException;

class Thing1
{
    public function __construct($message)
    {
        $this->message = $message;
    }

    public function __toString()
    {
        return $this->message;
    }
}

class Thing2
{
    public function jsonSerialize()
    {
        return '{}';
    }
}

/**
 * @covers Hough\Promise\RejectionException
 */
class RejectionExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testCanGetReasonFromException()
    {
        $thing = new Thing1('foo');
        $e = new RejectionException($thing);

        $this->assertSame($thing, $e->getReason());
        $this->assertEquals('The promise was rejected with reason: foo', $e->getMessage());
    }

    public function testCanGetReasonMessageFromJson()
    {
        $reason = new Thing2();
        $e = new RejectionException($reason);
        $this->assertContains("{}", $e->getMessage());
    }
}
