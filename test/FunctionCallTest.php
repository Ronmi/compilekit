<?php

namespace FruitTest\CompileKit;

use Fruit\CompileKit\FunctionCall;
use Fruit\CompileKit\Block;

class FunctionCallTest extends \PHPUnit\Framework\TestCase
{
    public function testRenderEmpty()
    {
        $f = new FunctionCall('a');
        $expect = 'a()';
        $actual = $f->render();
        $this->assertEquals($expect, $actual, 'no format');

        $expect = 'a()';
        $actual = $f->render(true);
        $this->assertEquals($expect, $actual, 'PSR-2');

        $expect = '    a()';
        $actual = $f->render(true, 1);
        $this->assertEquals($expect, $actual, 'indented');
    }

    public function testRender1Arg()
    {
        $f = (new FunctionCall('a'))->arg(1);
        $expect = 'a(1)';
        $actual = $f->render();
        $this->assertEquals($expect, $actual, 'no format');

        $expect = 'a(1)';
        $actual = $f->render(true);
        $this->assertEquals($expect, $actual, 'PSR-2');

        $expect = '    a(1)';
        $actual = $f->render(true, 1);
        $this->assertEquals($expect, $actual, 'indented');
    }

    public function testRender2Arg()
    {
        $f = (new FunctionCall('a'))->arg(1)->rawArg('"asd"');
        $expect = 'a(1, "asd")';
        $actual = $f->render();
        $this->assertEquals($expect, $actual, 'no format');

        $expect = 'a(1, "asd")';
        $actual = $f->render(true);
        $this->assertEquals($expect, $actual, 'PSR-2');

        $expect = '    a(1, "asd")';
        $actual = $f->render(true, 1);
        $this->assertEquals($expect, $actual, 'indented');

        $expect = '    a(
        1,
        "asd",
        2,
        3,
        4
    )';
        $f->arg(2)->arg(3)->arg(4);
        $actual = $f->render(true, 1);
        $this->assertEquals($expect, $actual, 'indented');
    }

    public function testBind()
    {
        $f = (new FunctionCall('a'))->arg(1)->arg('asd');
        $expect = "a(1, 'asd')";
        $actual = $f->render();
        $this->assertEquals($expect, $actual, 'no format');

        $expect = "a(1, 'asd')";
        $actual = $f->render(true);
        $this->assertEquals($expect, $actual, 'PSR-2');

        $expect = "    a(1, 'asd')";
        $actual = $f->render(true, 1);
        $this->assertEquals($expect, $actual, 'indented');
    }

    public function testRenderable()
    {
        $ve = (new FunctionCall('var_export'))
               ->rawArg('$a')
               ->rawArg('true');
        $f = (new FunctionCall('a'))
               ->arg($ve);
        $expect = 'a(var_export($a, true))';
        $actual = $f->render();
        $this->assertEquals($expect, $actual, 'no format');

        $expect = 'a(var_export($a, true))';
        $actual = $f->render(true);
        $this->assertEquals($expect, $actual, 'PSR-2');

        $expect = '    a(var_export($a, true))';
        $actual = $f->render(true, 1);
        $this->assertEquals($expect, $actual, 'indented');
    }
}
