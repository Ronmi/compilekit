<?php

namespace FruitTest\CompileKit;

use Fruit\CompileKit\Value;

class ValueTest extends \PHPUnit\Framework\TestCase
{
    public function testRaw()
    {
        $v = (new Value)->raw('1');
        $expect = '1';
        $actual = $v->render();
        $this->assertEquals($expect, $actual, 'no format');

        $expect = '1';
        $actual = $v->render(true);
        $this->assertEquals($expect, $actual, 'PSR-2');

        $expect = '    1';
        $actual = $v->render(true, 1);
        $this->assertEquals($expect, $actual, 'indented');
    }

    public function testBind()
    {
        $v = (new Value)->bind(1);
        $expect = '1';
        $actual = $v->render();
        $this->assertEquals($expect, $actual, 'no format');

        $expect = '1';
        $actual = $v->render(true);
        $this->assertEquals($expect, $actual, 'PSR-2');

        $expect = '    1';
        $actual = $v->render(true, 1);
        $this->assertEquals($expect, $actual, 'indented');
    }

    public function testSet()
    {
        $v = (new Value)->set((new Value)->bind(1));
        $expect = '1';
        $actual = $v->render();
        $this->assertEquals($expect, $actual, 'no format');

        $expect = '1';
        $actual = $v->render(true);
        $this->assertEquals($expect, $actual, 'PSR-2');

        $expect = '    1';
        $actual = $v->render(true, 1);
        $this->assertEquals($expect, $actual, 'indented');
    }
}
