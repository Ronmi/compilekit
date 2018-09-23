<?php

namespace FruitTest\CompileKit;

use Fruit\CompileKit\UserArray as Arr;

class UserArrayTest extends \PHPUnit\Framework\TestCase
{
    public function testNumeric()
    {
        $arr = new Arr([1, 2, 3]);
        $expect = '[0 => 1,1 => 2,2 => 3]';
        $actual = $arr->render();
        $this->assertEquals($expect, $actual, 'no format');

        $expect = '[
    0 => 1,
    1 => 2,
    2 => 3,
]';
        $actual = $arr->render(true);
        $this->assertEquals($expect, $actual, 'PSR-2');

        $expect = '    [
        0 => 1,
        1 => 2,
        2 => 3,
    ]';
        $actual = $arr->render(true, 1);
        $this->assertEquals($expect, $actual, 'PSR-2');
    }
}
