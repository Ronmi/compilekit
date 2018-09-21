<?php

namespace FruitTest\CompileKit;

use Fruit\CompileKit\UserFunction;
use Fruit\CompileKit\Block;
use Fruit\CompileKit\Value;

class UserFunctionTest extends \PHPUnit\Framework\TestCase
{
    public function testAnonymous()
    {
        $f = new UserFunction();
        $expect = 'function () {}';
        $actual = $f->render();
        $this->assertEquals($expect, $actual);
    }

    public function testBody()
    {
        $f = (new Userfunction())
            ->line('$x = 1;')
            ->block(['return $x;']);
        $expect = 'function () {$x = 1;return $x;}';
        $actual = $f->render();
        $this->assertEquals($expect, $actual);
    }

    public function testBodyPrettier()
    {
        $f = (new Userfunction())
            ->line('$x = 1;')
            ->append((new Block)->return(Value::as('$x')));
        $expect = 'function ()
{
    $x = 1;
    return $x;
}';
        $actual = $f->render(true);
        $this->assertEquals($expect, $actual);
    }

    public function testBodyPrettierIdented()
    {
        $f = (new Userfunction())
            ->line('$x = 1;')
            ->block(['return $x;']);
        $expect = '    function ()
    {
        $x = 1;
        return $x;
    }';
        $actual = $f->render(true, 1);
        $this->assertEquals($expect, $actual);
    }

    public function testName()
    {
        $f = new Userfunction('f');
        $expect = 'function f() {}';
        $actual = $f->render();
        $this->assertEquals($expect, $actual);
    }

    public function testArgs()
    {
        $f = (new Userfunction())
            ->rawArg('arg1')
            ->rawArg('$arg2')
            ->rawArg('$arg3', 'string')
            ->rawArg('$arg4', '', 'null')
            ->rawArg('arg5', 'string', '"test"');
        $expect = 'function ($arg1,$arg2,string $arg3,$arg4 = null,string $arg5 = "test") {}';
        $actual = $f->render();
        $this->assertEquals($expect, $actual);
    }

    public function testReturnType()
    {
        $f = (new Userfunction())
            ->return('string');
        $expect = 'function (): string {}';
        $actual = $f->render();
        $this->assertEquals($expect, $actual);
    }

    public function testUseWithAnonymous()
    {
        $f = (new Userfunction())
            ->rawArg('a')
            ->use('b');
        $expect = 'function ($a) use ($b) {}';
        $actual = $f->render();
        $this->assertEquals($expect, $actual, 'no format');

        $expect = 'function (
    $a
) use (
    $b
) {

}';
        $actual = $f->render(true);
        $this->assertEquals($expect, $actual, 'PSR-2');
    }

    public function testUseWithNamed()
    {
        $f = (new Userfunction('a'))
            ->rawArg('a')
            ->use('b');
        $expect = 'function a($a) {}';
        $actual = $f->render();
        $this->assertEquals($expect, $actual, 'no format');

        $expect = 'function a(
    $a
) {

}';
        $actual = $f->render(true);
        $this->assertEquals($expect, $actual, 'PSR-2');
    }
}
