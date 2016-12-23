<?php
namespace Hough\Promise\Tests;

use Hough\Generators\AbstractGenerator;

class EachPromiseTestGenerator1 extends AbstractGenerator
{
    public function resume($position)
    {
        if ($position === 2) {

            return null;
        }

        if ($position === 0) {

            return array(0, 'a');
        }

        throw new \Exception('Failure');
    }
}
