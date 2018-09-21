<?php

namespace FruitTest\CompileKit;

use Fruit\CompileKit\Value;
use Fruit\CompileKit\FunctionCall;
use Fruit\CompileKit\Compilable;
use Fruit\CompileKit\Renderable;

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

    public function testBy()
    {
        $v = (new Value)->by(
            new class implements Compilable {
                public function compile(): Renderable
                {
                    return Value::as('exit;');
                }
            }
        );
        $expect = 'exit;';
        $actual = $v->render();
        $this->assertEquals($expect, $actual);
    }

    public function testStmt()
    {
        $f = Value::stmt(Value::as('return'), new FunctionCall('a'));
        $expect = '    return a();';
        $actual = $f->render(true, 1);
        $this->assertEquals($expect, $actual);
    }

    public function testUgly()
    {
        $v = Value::ugly(Value::as('1'));
        $expect = '1';
        $actual = $v->render(true, 1);
        $this->assertEquals($expect, $actual);
    }
}
