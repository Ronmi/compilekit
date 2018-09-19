<?php

namespace FruitTest\CompileKit;

use Fruit\CompileKit\UserFunction;

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
            ->block(['return $x;']);
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
            ->arg('arg1')
            ->arg('$arg2')
            ->arg('$arg3', 'string')
            ->arg('$arg4', '', 'null')
            ->arg('arg5', 'string', '"test"');
        $expect = 'function ($arg1, $arg2, string $arg3, $arg4 = null, string $arg5 = "test") {}';
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
}
